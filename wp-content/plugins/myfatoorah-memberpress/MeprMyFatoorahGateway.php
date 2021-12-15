<?php
if (!defined('ABSPATH')) {
    die('You are not allowed to call this page directly.');
}

class MeprMyFatoorahGateway extends MeprBaseRealGateway
{
    /** This will be where the gateway api will interacted from */
    public $gateway;

    /** Required Keys */
    private $secretKey = '';

    /** Used in the view to identify the gateway */
    public function __construct()
    {
        $this->name = __("myFatoorah", 'memberpress');
        $this->icon = MP_MYFATOORAH_IMAGES_URL . '/logo.svg';
        $this->desc = __('Pay via myFatoorah', 'memberpress');
        $this->set_defaults();

        $this->capabilities = array(
            'process-payments',
            'create-subscriptions',
            'cancel-subscriptions',
            'suspend-subscriptions',
            'send-cc-expirations'
        );

        $this->notifiers = array(
            'whk' => 'callback_handler'
        );

        $this->message_pages = array('subscription' => 'subscription_message');
    }

    public function load($settings)
    {
        $this->settings = (object) $settings;
        $this->set_defaults();
        $this->gateway = new MeprMyFatoorahAPI($this->settings);
    }

    protected function set_defaults()
    {
        if (!isset($this->settings)) {
            $this->settings = array();
        }

        $this->settings = (object) array_merge(
            array(
                'gateway' => 'MeprMyFatoorahGateway',
                'id' => $this->generate_id(),
                'label' => '',
                'use_label' => true,
                'icon' => MP_MYFATOORAH_IMAGES_URL . '/logo.svg',
                'use_icon' => true,
                'use_desc' => true,
                'email' => '',
                'sandbox' => false,
                'force_ssl' => false,
                'debug' => false,
                'test_mode' => false,
                'payment_method' => '',
                'apiUrl' => '',
                'api_keys' => array(
                    'live' => array(
                        'public' => '',
                        'secret' => ''
                    )
                )
            ),
            (array) $this->settings
        );

        $this->id = $this->settings->id;
        $this->label = $this->settings->label;
        $this->use_label = $this->settings->use_label;
        $this->use_icon = $this->settings->use_icon;
        $this->use_desc = $this->settings->use_desc;

        $this->settings->paymentMethod = $this->settings->payment_method;

        if ($this->is_test_mode()) {
            $this->settings->apiUrl = 'https://apitest.myfatoorah.com';
        } else {
            $this->settings->apiUrl = 'https://api.myfatoorah.com';
        }

        $this->settings->apiKey = trim($this->settings->api_keys['live']['public']);
        $this->secretKey = trim($this->settings->api_keys['live']['secret']);
    }

    /** 
     * Used to send data to a given payment gateway. In gateways which redirect
     * before this step is necessary this method should just be left blank.
     */
    public function process_payment($txn, $trial = false)
    {
        if (isset($txn) and $txn instanceof MeprTransaction) {
            $usr = $txn->user();
            $prd = $txn->product();
        } else {
            throw new MeprGatewayException(__('Payment transaction intialization was unsuccessful, please try again.', 'memberpress'));
        }

        $mepr_options = MeprOptions::fetch();

        //$amount = (MeprUtils::is_zero_decimal_currency()) ? MeprUtils::format_float(($txn->total), 0) : MeprUtils::format_float(($txn->total * 100), 0);
        $amount = MeprUtils::format_float(($txn->total), 0);

        // Initiate Payment -> Get payment id
        $ipPostFields = ['InvoiceAmount' => $amount, 'CurrencyIso' => $mepr_options->currency_code];
        $paymentMethodId = $this->gateway->getPaymentId($ipPostFields);

        $thankYouPage = $mepr_options->thankyou_page_url();
        $cancelPage = $mepr_options->account_page_url('action=subscriptions');

        // $thankYouPage = $cancelPage = "https://e616-113-59-209-231.ngrok.io/wordpress";

        // Execute Payment
        $postFields = [
            //Fill required data
            'PaymentMethodId'    => $paymentMethodId,
            'InvoiceValue'       => $amount,
            'CallBackUrl'        => $thankYouPage,
            'ErrorUrl'           => $cancelPage,
            'CustomerName'       => $usr->first_name,
            'DisplayCurrencyIso' => $mepr_options->currency_code,
            'CustomerEmail'      => $usr->user_email,
            'SourceInfo'         => 'Memberpress'
        ];

        $data = $this->gateway->executePayment($postFields);

        $invoiceId   = $data->InvoiceId;
        $paymentLink = $data->PaymentURL;

        $txn->trans_num = $invoiceId;
        $txn->store();

        return MeprUtils::wp_redirect($paymentLink);
    }

    /** 
     * Used to record a successful payment by the given gateway. It should have
     * the ability to record a successful payment or a failure. It is this method
     * that should be used when receiving a myfatoorah Webhook.
     */
    public function record_payment()
    {
        $body = (file_get_contents("php://input"));
        $data = json_decode($body, true);

        if (!empty($data['Event'])) {
            $status = $data['Data']['TransactionStatus'];
            $invoiceId = $data['Data']['InvoiceId'];

            // Record Failed Tx - Just to be safe
            if ($status != 'SUCCESS') {
                return $this->record_payment_failure();
            }

            $obj = MeprTransaction::get_one_by_trans_num($invoiceId);

            if (is_object($obj) and isset($obj->id)) {
                $txn = new MeprTransaction;
                $txn->load_data($obj);

                $usr = $txn->user();

                // Just short circuit if the txn has already completed
                if ($txn->status == MeprTransaction::$complete_str)
                    return $txn;

                $txn->status  = MeprTransaction::$complete_str;
                // This will only work before maybe_cancel_old_sub is run
                $upgrade = $txn->is_upgrade();
                $downgrade = $txn->is_downgrade();

                $event_txn = $txn->maybe_cancel_old_sub();
                $txn->store();

                $prd = $txn->product();

                if ($prd->period_type == 'lifetime') {
                    if ($upgrade) {
                        $this->upgraded_sub($txn, $event_txn);
                    } else if ($downgrade) {
                        $this->downgraded_sub($txn, $event_txn);
                    } else {
                        $this->new_sub($txn);
                    }

                    MeprUtils::send_signup_notices($txn);
                }

                MeprUtils::send_transaction_receipt_notices($txn);
                return $txn;
            }
        }

        return false;
    }

    /** 
     * Used to send subscription data to a given payment gateway. In gateways
     * which redirect before this step is necessary this method should just be
     * left blank.
     */
    public function process_create_subscription($txn)
    {
        if (isset($txn) and $txn instanceof MeprTransaction) {
            $usr = $txn->user();
            $prd = $txn->product();
        } else {
            throw new MeprGatewayException(__('Payment was unsuccessful, please check your payment details and try again.', 'memberpress'));
        }

        $mepr_options = MeprOptions::fetch();
        $sub = $txn->subscription();



        // Handle Free Trial period stuff
        if ($sub->trial) {
            //Prepare the $txn for the process_payment method
            $txn->set_subtotal($sub->trial_amount);
            $txn->status = MeprTransaction::$pending_str;
            $this->record_trial_payment($txn);
            return $txn;
        }

        // Handle zero decimal currencies in pg
        //$amount = (MeprUtils::is_zero_decimal_currency()) ? MeprUtils::format_float(($txn->total), 0) : MeprUtils::format_float(($txn->total * 100), 0);
        $amount = MeprUtils::format_float(($txn->total), 0);

        //Reload the subscription now that it should have a token set
        $sub = new MeprSubscription($sub->id);

        // Default to 0 for infinite occurrences
        $total_occurrences = $sub->limit_cycles ? $sub->limit_cycles_num : 0;

        // Initiate Payment -> Get payment id
        $ipPostFields = ['InvoiceAmount' => $amount, 'CurrencyIso' => $mepr_options->currency_code];
        $paymentMethodId = $this->gateway->getPaymentId($ipPostFields);

        $thankYouPage = $mepr_options->thankyou_page_url();
        $cancelPage = $mepr_options->account_page_url('action=subscriptions');

        // $thankYouPage = $cancelPage = "https://e616-113-59-209-231.ngrok.io/wordpress";

        // Execute Payment
        $postFields = [
            //Fill required data
            'PaymentMethodId' => $paymentMethodId,
            'InvoiceValue'    => $amount,
            'CallBackUrl'     => $thankYouPage,
            'ErrorUrl'        => $cancelPage,
            'CustomerName'       => $usr->first_name,
            'DisplayCurrencyIso' => $mepr_options->currency_code,
            'CustomerEmail'      => $usr->user_email,
            'SourceInfo'         => 'Memberpress',
            'RecurringModel'  => [
                'RecurringType' => 'Monthly',
                'Iteration'     => $total_occurrences
            ],
        ];

        $data = $this->gateway->executePayment($postFields);

        $invoiceId   = $data->InvoiceId;
        $recurringId = $data->RecurringId;
        $paymentURL  = $data->PaymentURL;

        $cardHolder = $_REQUEST['card-name'];
        $cardNo = $_REQUEST['card-no'];
        $expireMonth = $_REQUEST['card-expire-month'];
        $expireYear = $_REQUEST['card-expire-year'];
        $cardCvv = $_REQUEST['card-cvv'];

        $cardInfo = [
            'PaymentType' => 'card',
            'Bypass3DS'   => false,
            'Card'        => [
                'Number'         => $cardNo,
                'ExpiryMonth'    => $expireMonth,
                'ExpiryYear'     => $expireYear,
                'SecurityCode'   => $cardCvv,
                'CardHolderName' => $cardHolder
            ]
        ];
        
        //Call endpoint
        $directData = $this->gateway->directPayment($paymentURL, $cardInfo);
        
        $paymentId   = $directData->PaymentId;
        $paymentLink = $directData->PaymentURL;

        // Save Tx
        $txn->user_id    = $sub->user_id;
        $txn->product_id = $sub->product_id;
        $txn->gateway    = $this->id;
        $txn->subscription_id = $sub->id;
        $txn->trans_num = $invoiceId;
        $txn->store();

        // Save Recurring Ids
        $sub->subscr_id = $recurringId;
        $sub->store();

        return MeprUtils::wp_redirect($paymentLink);
    }

    /** 
     * This method should be used by the class to record a successful subscription transaction from
     * the gateway. This method should also be used by a Silent Posts.
     */
    public function record_transaction_for_subscription()
    {
        $body = (file_get_contents("php://input"));
        $data = json_decode($body, true);

        if (!empty($data['Event'])) {
            $status = $data['Data']['TransactionStatus'];
            $invoiceId = $data['Data']['InvoiceId'];

            // Record Failed Tx - Just to be safe
            if ($status != 'SUCCESS') {
                return $this->record_payment_failure();
            }

            $obj = MeprTransaction::get_one_by_trans_num($invoiceId);

            if (is_object($obj) and isset($obj->id)) {
                $txn = new MeprTransaction;
                $txn->load_data($obj);

                $usr = $txn->user();

                // Just short circuit if the txn has already completed
                if ($txn->status == MeprTransaction::$complete_str) {
                    return $txn;
                }

                $txn->status  = MeprTransaction::$complete_str;

                // This will only work before maybe_cancel_old_sub is run
                $upgrade = $txn->is_upgrade();
                $downgrade = $txn->is_downgrade();

                $event_txn = $txn->maybe_cancel_old_sub();

                $sub = $txn->subscription();
                $txn->user_id    = $sub->user_id;
                $txn->product_id = $sub->product_id;
                $txn->gateway    = $this->id;
                $txn->subscription_id = $sub->id;

                $txn->store(true);

                $prd = $txn->product();

                if ($prd->period_type == 'lifetime') {
                    if ($upgrade) {
                        $this->upgraded_sub($txn, $event_txn);
                    } else if ($downgrade) {
                        $this->downgraded_sub($txn, $event_txn);
                    } else {
                        $this->new_sub($txn);
                    }

                    MeprUtils::send_signup_notices($txn);
                }

                MeprUtils::send_transaction_receipt_notices($txn);
                return $txn;
            }
        }

        return false;
    }

    /** 
     * Used to record a successful subscription by the given gateway. It should have
     * the ability to record a successful subscription or a failure. 
     */
    public function record_create_subscription()
    {
        $body = (file_get_contents("php://input"));
        $data = json_decode($body, true);
        $mepr_options = MeprOptions::fetch();

        if (!empty($data['Event'])) {
            $status = $data['Data']['TransactionStatus'];
            $invoiceId = $data['Data']['InvoiceId'];

            // Record Failed Tx - Just to be safe
            if ($status != 'SUCCESS') {
                return $this->record_payment_failure();
            }

            $obj = MeprTransaction::get_one_by_trans_num($invoiceId);

            if (is_object($obj) and isset($obj->id)) {
                $txn = new MeprTransaction;
                $txn->load_data($obj);

                $sub = $txn->subscription();

                $sub->status    = MeprSubscription::$active_str;
                $sub->created_at = gmdate('c');
                $sub->store();

                // This will only work before maybe_cancel_old_sub is run
                $upgrade   = $sub->is_upgrade();
                $downgrade = $sub->is_downgrade();

                $event_txn = $sub->maybe_cancel_old_sub();

                $txn = $sub->first_txn();

                if ($txn == false || !($txn instanceof MeprTransaction)) {
                    $txn = new MeprTransaction();
                    $txn->user_id = $sub->user_id;
                    $txn->product_id = $sub->product_id;
                }

                $old_total = $txn->total;

                // If no trial or trial amount is zero then we've got to make
                // sure the confirmation txn lasts through the trial
                // if (!$sub->trial || ($sub->trial && $sub->trial_amount <= 0.00)) {
                //     $trial_days      = ($sub->trial) ? $sub->trial_days : $mepr_options->grace_init_days;
                //     $txn->status     = MeprTransaction::$confirmed_str;
                //     $txn->txn_type   = MeprTransaction::$subscription_confirmation_str;
                //     $txn->expires_at = MeprUtils::ts_to_mysql_date(time() + MeprUtils::days($trial_days), 'Y-m-d 23:59:59');
                //     $txn->set_subtotal(0.00); // Just a confirmation txn
                //     $txn->store();
                // }

                $txn->set_gross($old_total); // Artificially set the subscription amount

                if ($upgrade) {
                    $this->upgraded_sub($sub, $event_txn);
                } else if ($downgrade) {
                    $this->downgraded_sub($sub, $event_txn);
                } else {
                    $this->new_sub($sub, true);
                }

                //Reload the txn now that it should have a proper trans_num set
                $txn = new MeprTransaction($txn->id);

                MeprUtils::send_signup_notices($txn);

                return array('subscription' => $sub, 'transaction' => $txn);
            }
        }

        return false;
    }

    /** 
     * Used to record a successful recurring payment by the given gateway. It
     * should have the ability to record a successful payment or a failure. 
     */
    public function record_subscription_payment()
    {
        $subscr_id = $_REQUEST['recurring_id'] ?? $_REQUEST['data']->subscription_code;
        $sub = MeprSubscription::get_one_by_subscr_id($subscr_id);

        if (!$sub) {
            return false;
        }

        if(!($sub instanceof MeprSubscription) || MeprTransaction::txn_exists($_REQUEST['recurring_payment_id'])) {
            return false;
        }

        //If this isn't for us, bail
        if($sub->gateway != $this->id) { return false; }

        $first_txn = $sub->first_txn();

        if($first_txn == false || !($first_txn instanceof MeprTransaction)) {
            $coupon_id = $sub->coupon_id;
        }
        else {
            $coupon_id = $first_txn->coupon_id;
        }

        $txn = new MeprTransaction();
        $txn->user_id    = $sub->user_id;
        $txn->product_id = $sub->product_id;
        $txn->status     = MeprTransaction::$complete_str;
        $txn->coupon_id  = $coupon_id;
        $txn->trans_num  = $_REQUEST['recurring_payment_id'];
        $txn->gateway    = $this->id;
        $txn->subscription_id = $sub->id;

        $txn->set_gross((float) $_REQUEST['recurring_payment_amount']);
        
        // if (MeprUtils::is_zero_decimal_currency()) {
        //     $txn->set_gross((float) $_REQUEST['recurring_payment_amount']);
        // } else {
        //     $txn->set_gross((float) $_REQUEST['recurring_payment_amount'] / 100);
        // }

        $txn->store();

        $sub->status = MeprSubscription::$active_str;
        $sub->store();

        // If a limit was set on the recurring cycles we need
        // to cancel the subscr if the txn_count >= limit_cycles_num
        // This is not possible natively with myFatoorah so we
        // just cancel the subscr when limit_cycles_num is hit
        $sub->limit_payment_cycles();

        $this->email_status(
        "Subscription Transaction\n" .
            MeprUtils::object_to_string($txn->rec, true),
        $this->settings->debug
        );

        //Reload the txn
        $txn = new MeprTransaction($txn->id);

        MeprUtils::send_transaction_receipt_notices($txn);
        MeprUtils::send_cc_expiration_notices($txn);

        return $txn;
    }

    /** 
     * Used to cancel a subscription by the given gateway. This method should be used
     * by the class to record a successful cancellation from the gateway. This method
     * should also be used by any IPN requests or Silent Posts.
     *
     * We bill the outstanding amount of the previous subscription,
     * cancel the previous subscription and create a new subscription
     */
    public function process_update_subscription($sub_id)
    {
    }

    /** This method should be used by the class to record a successful cancellation
     * from the gateway. This method should also be used by any IPN requests or
     * Silent Posts.
     */
    public function record_update_subscription()
    {
        // No need for this one
    }

    /** Used to suspend a subscription by the given gateway.
     */
    public function process_suspend_subscription($sub_id)
    {
        $sub = new MeprSubscription($sub_id);

        $args = MeprHooks::apply_filters('mepr_myfatoorah_suspend_subscription_args', array(
            'code' => $sub->subscr_id,
            'token' => $sub->get_meta('myfatoorah_email_token'),
        ), $sub);

        $data = $this->gateway->cancelSubscription($sub->subscr_id);

        $_REQUEST['recurring_payment_id'] = $sub->subscr_id;

        return $this->record_suspend_subscription();
    }

    /** This method should be used by the class to record a successful suspension
     * from the gateway.
     */
    public function record_suspend_subscription()
    {
        $subscr_id = sanitize_text_field($_REQUEST['recurring_payment_id']);
        $sub = MeprSubscription::get_one_by_subscr_id($subscr_id);

        if (!$sub) {
            return false;
        }

        // Seriously ... if sub was already suspended what are we doing here?
        if ($sub->status == MeprSubscription::$suspended_str) {
            return $sub;
        }

        $sub->status = MeprSubscription::$suspended_str;
        $sub->store();

        MeprUtils::send_suspended_sub_notices($sub);

        return $sub;
    }

    /** 
     * Used to suspend a subscription by the given gateway.
     */
    public function process_resume_subscription($sub_id)
    {
        throw new MeprGatewayException(__('Subscription resume not supported by myFatoorah. Please contact the system administrator to resume subscription.', 'memberpress'));
    }

    /** This method should be used by the class to record a successful resuming of
     * as subscription from the gateway.
     */
    public function record_resume_subscription()
    {
        throw new MeprGatewayException(__('Subscription resume not supported by myFatoorah. Please contact the system administrator to resume subscription.', 'memberpress'));
    }

    /** Used to cancel a subscription by the given gateway. This method should be used
     * by the class to record a successful cancellation from the gateway. This method
     * should also be used by any IPN requests or Silent Posts.
     */
    public function process_cancel_subscription($sub_id)
    {
        $sub = new MeprSubscription($sub_id);

        if (!isset($sub->id) || (int) $sub->id <= 0)
            throw new MeprGatewayException(__('This subscription is invalid.', 'memberpress'));

        $args = MeprHooks::apply_filters('mepr_myfatoorah_cancel_subscription_args', array(
            'code' => $sub->subscr_id,
            'token' => $sub->get_meta('myfatoorah_email_token'),
        ), $sub);

        $data = $this->gateway->cancelSubscription($sub->subscr_id);

        $_REQUEST['recurring_payment_id'] = $sub->subscr_id;

        return $this->record_cancel_subscription();
    }

    /** This method should be used by the class to record a successful cancellation
     * from the gateway. This method should also be used by any IPN requests or
     * Silent Posts.
     */
    public function record_cancel_subscription()
    {
        $subscr_id = $_REQUEST['recurring_payment_id'] ?? $_REQUEST['data']->subscription_code;
        $sub = MeprSubscription::get_one_by_subscr_id($subscr_id);

        if (!$sub) {
            return false;
        }

        // Seriously ... if sub was already cancelled what are we doing here?
        if ($sub->status == MeprSubscription::$cancelled_str) {
            return $sub;
        }

        $sub->status = MeprSubscription::$cancelled_str;
        $sub->store();

        $sub->limit_reached_actions();

        MeprUtils::send_cancelled_sub_notices($sub);

        return $sub;
    }

    /**
     * Not implemented
     */
    public function process_trial_payment($txn)
    {
        
    }

    /**
     * Not implemented
     */
    public function record_trial_payment($txn)
    {
        
    }

    /** 
     * Used to record a declined payment. 
     */
    public function record_payment_failure()
    {
        $body = (file_get_contents("php://input"));
        $data = json_decode($body, true);

        if (!empty($data['Event'])) {
            $status = $data['Data']['TransactionStatus'];
            $invoiceId = $data['Data']['InvoiceId'];

            $txn_res = MeprTransaction::get_one_by_trans_num($invoiceId);
            
            if (is_object($txn_res) and isset($txn_res->id)) {
                $txn = new MeprTransaction($txn_res->id);
                $txn->status = MeprTransaction::$failed_str;
                $txn->store();

                if (!empty ($txn->subscription())) {
                    $sub = $txn->subscription();
                
                    $first_txn = $sub->first_txn();

                    if ($first_txn == false || !($first_txn instanceof MeprTransaction)) {
                        $coupon_id = $sub->coupon_id;
                    } else {
                        $coupon_id = $first_txn->coupon_id;
                    }

                    //If first payment fails, myfatoorah will not set up the subscription, so we need to mark it as cancelled in MP
                    if ($sub->txn_count == 0) {
                        $sub->status = MeprSubscription::$cancelled_str;
                    } else {
                        $sub->status = MeprSubscription::$active_str;
                    }
                    $sub->gateway = $this->id;
                    $sub->expire_txns(); //Expire associated transactions for the old subscription
                    $sub->store();
                }
            } else {
                return false; // Nothing we can do here ... so we outta here
            }

            MeprUtils::send_failed_txn_notices($txn);

            return $txn;
        }

        return false;
    }

    /** 
     * This method should be used by the class to push a refund request to to the gateway.
     * Not implemented
     */
    public function process_refund(MeprTransaction $txn)
    {
        
    }

    /** 
     * This method should be used by the class to record a successful refund from
     * the gateway. This method should also be used by any IPN requests or Silent Posts.
     */
    public function record_refund()
    {
        
    }

    /** 
     * This gets called on the 'init' hook when the signup form is processed ...
     * this is in place so that payment solutions like paypal can redirect
     * before any content is rendered.
     */
    public function process_signup_form($txn)
    {
    }

    public function display_payment_page($txn)
    {}

    /** 
     * This gets called on wp_enqueue_script and enqueues a set of
     * scripts for use on the page containing the payment form
     */
    public function enqueue_payment_form_scripts()
    {
    }

    /** 
     * This gets called on the_content and just renders the payment form
     */
    public function display_payment_form($amount, $user, $product_id, $txn_id)
    {
        $mepr_options = MeprOptions::fetch();
        $prd = new MeprProduct($product_id);
        $coupon = false;

        $txn = new MeprTransaction($txn_id);

        //Artifically set the price of the $prd in case a coupon was used
        if ($prd->price != $amount) {
            $coupon = true;
            $prd->price = $amount;
        }

        $invoice = MeprTransactionsHelper::get_invoice($txn);
        echo $invoice;

        $sub = $txn->subscription();

        $fields = '';
        if (!empty($sub)){
            $fields = '<div class="mp-form-row">
                <div class="mp-form-label">
                    <label for="mepr_myfatoorah_card_name">Name on the Card:*</label>
                    <span class="cc-error">Name on the card is required</span>
                </div>
                <input type="text" name="card-name" id="mepr_myfatoorah_card_name" class="mepr-form-input myfatoorah-card-name" required value="" />
            </div>
            <div class="mp-form-row">
                <div class="mp-form-label">
                    <label for="mepr_myfatoorah_card_no">Card No:*</label>
                    <span class="cc-error">Card no is required.</span>
                </div>
                <input type="text" name="card-no" id="mepr_myfatoorah_card_no" maxlength="16" class="mepr-form-input myfatoorah_card_no" required value="" />
            </div>
            <table>
                <tr>
                    <td style="border: 0px; padding: 0px;">
                    <div class="mp-form-row">
                        <div class="mp-form-label">
                            <label for="mepr_myfatoorah_card_expire_month">Expire Month:*</label>
                            <span class="cc-error">Card expire date is required.</span>
                        </div>
                        <input type="text" name="card-expire-month" id="mepr_myfatoorah_card_expire_month" maxlength="2" class="mepr-form-input myfatoorah_card_expire_month" required value="" />
                    </div>
                    </td>
                    <td style="border: 0px; padding: 0px;">
                    <div class="mp-form-row">
                        <div class="mp-form-label">
                            <label for="mepr_myfatoorah_card_expire_year">Expire Year:*</label>
                            <span class="cc-error">Card expire date is required.</span>
                        </div>
                        <input type="text" name="card-expire-year" id="mepr_myfatoorah_card_expire_year" maxlength="2" class="mepr-form-input myfatoorah_card_expire_year" required value="" />
                    </div>
                    </td>
                    <td style="border: 0px; padding: 0px;">
                    <div class="mp-form-row">
                        <div class="mp-form-label">
                            <label for="mepr_myfatoorah_card_cvv">CVV:*</label>
                            <span class="cc-error">Card cvv is required.</span>
                        </div>
                        <input type="text" name="card-cvv" id="mepr_myfatoorah_card_cvv" maxlength="3" class="mepr-form-input myfatoorah_card_cvv" required value="" />
                    </div>
                    </td>
                </tr>
            </table>';
        }

        ?>
        <div class="mp_wrapper mp_payment_form_wrapper">
            <div class="mp_wrapper mp_payment_form_wrapper">
                <?php MeprView::render('/shared/errors', get_defined_vars()); ?>
                <form action="" method="post" id="mepr_myfatoorah_payment_form" class="mepr-checkout-form mepr-form mepr-card-form" novalidate>
                    <input type="hidden" name="mepr_process_payment_form" value="Y" />
                    <input type="hidden" name="mepr_transaction_id" value="<?php echo $txn->id; ?>" />
                    <input type="hidden" name="mepr_subscription_id" value="<?php echo (!empty($sub) ? $sub->id : 0); ?>" />

                    <?php MeprHooks::do_action('mepr-myfatoorah-payment-form', $txn); ?>
                    <div class="mepr_spacer">&nbsp;</div>


                    <?php echo $fields; ?>

                    <input type="submit" class="mepr-submit" value="<?php _e('Pay Now', 'memberpress'); ?>" />
                    <img src="<?php echo admin_url('images/loading.gif'); ?>" style="display: none;" class="mepr-loading-gif" />
                    <?php MeprView::render('/shared/has_errors', get_defined_vars()); ?>
                </form>
            </div>
        </div>
        <?php
    }

    /** 
     * Validates the payment form before a payment is processed 
     */
    public function validate_payment_form($errors)
    {
        if ($_REQUEST['mepr_subscription_id'] != 0) {
            if (empty($_REQUEST['card-no']) || empty($_REQUEST['card-name']) || empty($_REQUEST['card-expire-month']) || empty($_REQUEST['card-expire-year']) || empty($_REQUEST['card-cvv'])) {
                array_push($errors, "Please fill all required data");
            }else{
                if (!is_numeric($_REQUEST['card-no']) || !is_numeric($_REQUEST['card-expire-month']) || !is_numeric($_REQUEST['card-expire-year']) || !is_numeric($_REQUEST['card-cvv'])) {
                    array_push($errors, "Invalid data found. Please enter numbers for card no, expire date and cvv");
                }else{
                    if (strlen($_REQUEST['card-no']) != 16) {
                        array_push($errors, "Card no should be length of 16");
                    }
                    if (strlen($_REQUEST['card-cvv']) != 3) {
                        array_push($errors, "Card cvv should be length of 3");
                    }
                    if (strlen($_REQUEST['card-expire-month']) != 2) {
                        array_push($errors, "Card expire month should be length of 2");
                    }
                    if (strlen($_REQUEST['card-expire-year']) != 2) {
                        array_push($errors, "Card expire year should be length of 2");
                    }
                    if ($_REQUEST['card-expire-month'] < 1 || $_REQUEST['card-expire-month'] > 12) {
                        array_push($errors, "Card expire month is invalid");
                    }
                }
            }
        }

        return $errors;
    }

    /** 
     * Displays the form for the given payment gateway on the MemberPress Options page 
     */
    public function display_options_form()
    {
        $mepr_options = MeprOptions::fetch();

        $live_secret_key      = trim($this->settings->api_keys['live']['secret']);
        $live_public_key      = trim($this->settings->api_keys['live']['public']);
        $payment_method      = trim($this->settings->payment_method);
        $force_ssl            = ($this->settings->force_ssl == 'on' or $this->settings->force_ssl == true);
        $test_mode            = ($this->settings->test_mode == 'on' or $this->settings->test_mode == true);

        $live_secret_key_str      = "{$mepr_options->integrations_str}[{$this->id}][api_keys][live][secret]";
        $live_public_key_str      = "{$mepr_options->integrations_str}[{$this->id}][api_keys][live][public]";
        $payment_method_str       = "{$mepr_options->integrations_str}[{$this->id}][payment_method]";
        $force_ssl_str            = "{$mepr_options->integrations_str}[{$this->id}][force_ssl]";
        $test_mode_str            = "{$mepr_options->integrations_str}[{$this->id}][test_mode]";
        ?>
        <table id="mepr-myfatoorah-live-keys-<?php echo $this->id; ?>" class="form-table mepr-myfatoorah-live-keys">
            <tbody>
                <tr valign="top">
                    <th scope="row"><label for="<?php echo $live_public_key_str; ?>"><?php _e('API Key*:', 'memberpress'); ?></label></th>
                    <td><input type="text" class="mepr-auto-trim" name="<?php echo $live_public_key_str; ?>" value="<?php echo $live_public_key; ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="<?php echo $live_secret_key_str; ?>"><?php _e('Webhook Secret*:', 'memberpress'); ?></label></th>
                    <td><input type="text" class="mepr-auto-trim" name="<?php echo $live_secret_key_str; ?>" value="<?php echo $live_secret_key; ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="<?php echo $payment_method_str; ?>"><?php _e('Payment Method*:', 'memberpress'); ?></label></th>
                    <td><input type="text" class="mepr-auto-trim" name="<?php echo $payment_method_str; ?>" value="<?php echo $payment_method; ?>" /></td>
                </tr>
            </tbody>
        </table>
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row"><label for="<?php echo $test_mode_str; ?>"><?php _e('Test Mode', 'memberpress'); ?></label></th>
                    <td><input class="mepr-myfatoorah-testmode" data-integration="<?php echo $this->id; ?>" type="checkbox" name="<?php echo $test_mode_str; ?>" <?php echo checked($test_mode); ?> /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="<?php echo $force_ssl_str; ?>"><?php _e('Force SSL', 'memberpress'); ?></label></th>
                    <td><input type="checkbox" name="<?php echo $force_ssl_str; ?>" <?php echo checked($force_ssl); ?> /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label><?php _e('myFatoorah Webhook URL:', 'memberpress'); ?></label></th>
                    <td><?php MeprAppHelper::clipboard_input($this->notify_url('whk')); ?></td>
                </tr>
            </tbody>
        </table>
        <?php
    }

    /** 
     * Validates the form for the given payment gateway on the MemberPress Options page 
     */
    public function validate_options_form($errors)
    {
        $mepr_options = MeprOptions::fetch();

        if (
            !isset($_REQUEST[$mepr_options->integrations_str][$this->id]['api_keys']['live']['secret']) ||
            !isset($_REQUEST[$mepr_options->integrations_str][$this->id]['api_keys']['live']['public']) ||
            empty($_REQUEST[$mepr_options->integrations_str][$this->id]['payment_method'])
        ) {
            $errors[] = __("All MyFatoorah keys must be filled in.", 'memberpress');
        }

        return $errors;
    }

    /** 
     * This gets called on wp_enqueue_script and enqueues a set of
     * scripts for use on the front end user account page.
     */
    public function enqueue_user_account_scripts()
    {
    }

    /** Displays the update account form on the subscription account page **/
    public function display_update_account_form($subscription_id, $errors = array(), $message = "")
    {
        ?>
        <div>
            <div class="mepr_update_account_table">
                <div><strong><?php _e('Update your Credit Card information below', 'memberpress'); ?></strong></div>
                <div class="mp-form-row">
                    <p>MyFatoorah currently doesn't support changing Credit Card for recurring subscription. To change your card details, cancel the current subscription and subscribe again.</p>
                </div>
            </div>
        </div>

        </div>
        <?php
    }

    /** Validates the payment form before a payment is processed */
    public function validate_update_account_form($errors = array())
    {
    }

    /** Actually pushes the account update to the payment processor */
    public function process_update_account_form($subscription_id)
    {
    }

    /** Returns boolean ... whether or not we should be sending in test mode or not */
    public function is_test_mode()
    {
        return (isset($this->settings->test_mode) and $this->settings->test_mode);
    }

    public function force_ssl()
    {
        return (isset($this->settings->force_ssl) and ($this->settings->force_ssl == 'on' or $this->settings->force_ssl == true));
    }

    /** 
     * Get the renewal base date for a given subscription. This is the date MemberPress will use to calculate expiration dates.
     * Of course this method is meant to be overridden when a gateway requires it.
     */
    public function get_renewal_base_date(MeprSubscription $sub)
    {
        global $wpdb;
        $mepr_db = MeprDb::fetch();

        $q = $wpdb->prepare("SELECT e.created_at FROM {$mepr_db->events} AS e WHERE e.event='subscription-resumed' AND e.evt_id_type='subscriptions' AND e.evt_id=%d ORDER BY e.created_at DESC LIMIT 1",$sub->id);

        $renewal_base_date = $wpdb->get_var($q);
        if (!empty($renewal_base_date)) {
            return $renewal_base_date;
        }

        return $sub->created_at;
    }

    /** 
     * This method should be used by the class to verify a successful payment by the given
     * the gateway. This method should also be used by any IPN requests or Silent Posts.
     */
    public function callback_handler()
    {
        $this->email_status("Callback Just Came In (" . $_SERVER['REQUEST_METHOD'] . "):\n" . MeprUtils::object_to_string($_REQUEST, true) . "\n", $this->settings->debug);
        
        $request_headers      = apache_request_headers();
        $MyFatoorah_Signature = $request_headers['Myfatoorah-Signature'];

        $body = (file_get_contents("php://input"));
        $data = json_decode($body, true);

        $this->validateSignature($data, $this->secretKey, $MyFatoorah_Signature);

        if (empty($data['Event'])) {
            echo 'EVENT NOT AVAILABLE';
            exit();
        }

        $event = $data['Event'];
        $status = $data['Data']['TransactionStatus'];
        $invoiceId = $data['Data']['InvoiceId'];

        if (!isset($invoiceId)) {
            echo 'INVOICE ID NOT AVAILABLE';
            exit();
        }

        // *** Record Successful Transaction *** //

        // Handle Payments
        if ($event == "TransactionsStatusChanged") {
            // Record Failed Tx
            if ($status != 'SUCCESS') {
                return $this->record_payment_failure();
            }

            $obj = MeprTransaction::get_one_by_trans_num($invoiceId);
            if (is_object($obj) and isset($obj->id)) {
                $txn = new MeprTransaction;
                $txn->load_data($obj);

                if (empty($txn->subscription())) {
                    echo 'ONETIME';
                    $this->record_payment();
                }else{
                    echo 'SUBSCRIPTION';
                    $this->record_transaction_for_subscription();
                    $this->record_create_subscription();
                }
            }
        }

        // Handle Subscriptions
        if ($event == "RecurringStatusChanged") {
            $recurringId = $data['Data']['RecurringId'];
            $recurringStatus = $data['Data']['RecurringStatus'];

            if ($recurringStatus == 'Canceled' || $recurringStatus == 'Uncompleted') {
                $_REQUEST['recurring_payment_id'] = $recurringId;
                $this->record_cancel_subscription();
            }

            if ($recurringStatus == 'Completed') {
                $_REQUEST['recurring_id'] = $recurringId;
                $_REQUEST['recurring_payment_id'] = $invoiceId;

                if (isset($data['Data']['InvoiceValueInDisplayCurreny'])) {
                    $_REQUEST['recurring_payment_amount'] = $data['Data']['InvoiceValueInDisplayCurreny'];
                }else {
                    $_REQUEST['recurring_payment_amount'] = $data['Data']['InvoiceValueInBaseCurrency'];
                }
                
                $this->record_subscription_payment();
            }
        }
    }

    /**
     * Validate the webhook request from myfatoorh gateway
     */
    private function validateSignature($body, $secret, $MyFatoorah_Signature)
    {

        if ($body['Event'] == 'RefundStatusChanged') {
            unset($body['Data']['GatewayReference']);
        }
        $data = $body['Data'];

        //1- Order all data properties in alphabetic and case insensitive.
        uksort($data, 'strcasecmp');

        //2- Create one string from the data after ordering it to be like that key=value,key2=value2 ...
        $orderedData = implode(
            ',',
            array_map(
                function ($v, $k) {
                    return sprintf("%s=%s", $k, $v);
                },
                $data,
                array_keys($data)
            )
        );


        //4- Encrypt the string using HMAC SHA-256 with the secret key from the portal in binary mode.
        //Generate hash string
        $result = hash_hmac('sha256', $orderedData, $secret, true);


        //5- Encode the result from the previous point with base64.
        $hash = base64_encode($result);

        error_log(PHP_EOL . date('d.m.Y h:i:s') . ' - Generated Signature  - ' . $hash, 3, './webhook.log');
        error_log(PHP_EOL . date('d.m.Y h:i:s') . ' - MyFatoorah-Signature - ' . $MyFatoorah_Signature, 3, './webhook.log');


        //6- Compare the signature header with the encrypted hash string. If they are equal, then the request is valid and from the MyFatoorah side.
        if ($MyFatoorah_Signature == $hash) {
            error_log(PHP_EOL . date('d.m.Y h:i:s') . ' - Signature is valid ', 3, './webhook.log');
            return true;
        } else {
            error_log(PHP_EOL . date('d.m.Y h:i:s') . ' - Signature is not valid ', 3, './webhook.log');
            exit;
        }
    }
}
