<?php
/*
  Plugin Name: Myfatoorah - API Ver2.0
  Plugin URI: https://www.myfatoorah.com
  Description: Myfatoorah Payment gateway for woocommerce- API Ver2.0
  Version: 2.0
  Author: MyFatoorah Plugins Support
  Author URI:
 */

add_action('plugins_loaded', 'woocommerce_myfatoorah_v2_init', 0);

if (!defined('ABSPATH')) {
    exit;
}

function woocommerce_myfatoorah_v2_init() {
    if (!class_exists('WC_Payment_Gateway'))
        return;

    class WC_Myfatoorah_V2 extends WC_Payment_Gateway {

        public $api_username;
        public $success_url;

        /** @var bool Whether or not logging is enabled */
        public static $log_enabled = false;

        /** @var WC_Logger Logger instance */
        public static $log = false;

        /**
         * Constructor for the gateway.
         */
        public function __construct() {
            $this->id = 'myfatoorah_v2';
            $this->has_fields = false;
            $this->lang = get_bloginfo("language");

            $this->method_title = __('Myfatoorah', 'woocommerce');
            $this->method_description = sprintf(__('Myfatoorah standard sends customers to Myfatoorah to enter their payment information.', 'woocommerce'), '<a href="' . admin_url('admin.php?page=wc-status') . '">', '</a>');
            $this->supports = array(
                'products',
                'refunds'
            );

            // Load the settings.
            $this->getVendorGateway();
            $this->init_form_fields();
            $this->init_settings();

            // Define user set variables.
            $this->title = $this->get_option('title');
            $this->order_button_text = __("Pay Now", 'woocommerce');
            if ($this->lang == 'ar') {
                $this->order_button_text = __(' استكمال الدفع', 'woocommerce');
            }
            $this->desc = $this->get_option('desc') ? $this->get_option('desc') : ' ';
            $this->logo_url = $this->get_option('logo_url');
            $this->testmode = $this->get_option('testmode');
            $this->api_url = 'https://apitest.myfatoorah.com';
            if ($this->testmode == 'no') {
                $this->api_url = 'https://api.myfatoorah.com';
            }

            $this->debug = 'yes' === $this->get_option('debug', 'no');
            $this->token = $this->get_option('api_token');
            $this->api_gateway_payment = $this->get_option('api_gateway_payment');
            $this->failed_url = $this->get_option('fail_url');

            $this->pluginlog = plugin_dir_path(__FILE__) . 'myfatoorah.log';

            self::$log_enabled = $this->debug;
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        }

        /**
         * Logging method.
         * @param string $message
         */
        public static function log($message) {
            if (self::$log_enabled) {
                if (empty(self::$log)) {
                    self::$log = new WC_Logger();
                }
                self::$log->add('myfatoorah_v2', $message);
            }
        }

        /**
         * list avail Payment Gateways.
         */
        function getVendorGateway() {
            $this->paymentGateways = array('myfatoorah' => 'MyFatoorah',
                'md' => 'Mada KSA',
                'kn' => 'Knet',
                'vm' => 'Visa / Master',
                'b' => 'Benefit',
                'np' => 'Qatar Debit Card - NAPS',
                'uaecc' => 'Debit Cards UAE - VISA UAE',
                's' => 'Sadad',
                'ae' => 'AMEX',
                'ap' => 'Apple Pay',
                'af' => 'AFS',
                'kf' => 'KFast',
                'stc' => 'STC Pay');
        }

        /**
         * Initialise Gateway Settings Form Fields.
         */
        public function init_form_fields() {
            $this->form_fields = include( 'includes/settings-myfatoorah-v2.php' );
        }

        /**
         * Get the transaction URL.
         * @param  WC_Order $order
         * @return string
         */
        public function get_transaction_url($order) {
            return parent::get_transaction_url($order);
        }

        /**
         * Process the payment and return the result.
         * @param  int $order_id
         * @return array
         */
        public function process_payment($order_id) {
            $order = new WC_Order($order_id);
            $fName = version_compare(WC_VERSION, '3.0.0', '<') ? $order->shipping_first_name : $order->get_shipping_first_name();
            $lname = version_compare(WC_VERSION, '3.0.0', '<') ? $order->shipping_last_name : $order->get_shipping_last_name();
            //$address = version_compare(WC_VERSION, '3.0.0', '<') ? $order->shipping_address_1 : $order->get_shipping_address_1();
            //$city = version_compare(WC_VERSION, '3.0.0', '<') ? $order->shipping_city : $order->get_shipping_city();
            //$country = version_compare(WC_VERSION, '3.0.0', '<') ? $order->shipping_country : $order->get_shipping_country();
            // phone & email are not exist in shipping address!!
            $phone = version_compare(WC_VERSION, '3.0.0', '<') ? $order->billing_phone : $order->get_billing_phone();
            $email = version_compare(WC_VERSION, '3.0.0', '<') ? $order->billing_email : $order->get_billing_email();

            date_default_timezone_set('Asia/Kuwait');
            //get $expiryDate
            $date = new DateTime(date('Y-m-d\TH:i:s'), new DateTimeZone('Asia/Kuwait'));
            $currentDate = $date->format('Y-m-d\TH:i:s');
            $woocommerce_hold_stock_minutes = WC_Admin_Settings::get_option('woocommerce_hold_stock_minutes') ? WC_Admin_Settings::get_option('woocommerce_hold_stock_minutes') : 60;

            $expires = strtotime("$currentDate + $woocommerce_hold_stock_minutes minutes");
            $expiryDate = date('Y-m-d\TH:i:s', $expires);

            //set multiused vars
            $sucess_url = $order->get_checkout_order_received_url();
            $currencyIso = $order->get_currency();

            $amount = version_compare(WC_VERSION, '3.0.0', '<') ? $order->order_total : $order->get_total();
            $phoneArr = $this->splitPhone($phone);

            $payment_data['CurlData'] = [
                'CustomerName' => "$fName $lname",
                'DisplayCurrencyIso' => $currencyIso,
                'MobileCountryCode' => trim($phoneArr[0]),
                'CustomerMobile' => trim($phoneArr[1]),
                'CustomerEmail' => $email,
                'InvoiceValue' => $amount,
                'CallBackUrl' => $sucess_url,
                'ErrorUrl' => wc_get_checkout_url(),
                'Language' => ($this->lang == 'ar') ? 'ar' : 'en', //$this->lang may be en-US not en
                'CustomerReference' => $order_id,
                'CustomerCivilId' => $order_id,
                'UserDefinedField' => $order_id,
                'ExpiryDate' => $expiryDate,
                'SourceInfo' => WC_VERSION . ' - API Ver 2.0 Direct Payment',
                'CustomerAddress' => ['Block' => '', 'Street' => '', 'HouseBuildingNo' => '', 'Address' => '', 'AddressInstructions' => ''],
                'InvoiceItems' => [['ItemName' => 'Total amount', 'Quantity' => 1, 'UnitPrice' => $amount]] //must be inside two array due to API specification
            ];

            $this->payment_data = $payment_data;


            error_log(PHP_EOL . '----------------------------------------------------------------------------------------------------------------------------------------------------------------', 3, $this->pluginlog);

            error_log(PHP_EOL . date('d.m.Y h:i:s') . ' | CURL DATA  ------ Order# ' . $order_id . ' ' . json_encode($payment_data['CurlData']), 3, $this->pluginlog);

            $this->gateway = isset($_POST['mf_gateway']) ? $_POST['mf_gateway'] : 'myfatoorah';
            if ($this->gateway == 'myfatoorah') {
                $return = $this->sendPayment($order_id);
            } else {
                $return = $this->initiatePayment($order_id, $amount, $currencyIso, $this->gateway);
            }
            error_log(PHP_EOL . date('d.m.Y h:i:s') . ' | Return  ------ Order# ' . $order->get_id() . ' ' . json_encode($return), 3, $this->pluginlog);
            // print_r($return->Data->ErrorMessage);
            // die;
            if (isset($return->InvoiceURL)) {
                return array(
                    'result' => 'success',
                    'redirect' => $return->InvoiceURL
                );
            } else {

                throw new Exception(__($return->Data->ErrorMessage, 'woo'));
            }
        }

        function initiatePayment($orderId, $invoiceAmount, $currencyIso, $cardType) {

            $url = "$this->api_url/v2/InitiatePayment";
            $postFields = array('InvoiceAmount' => $invoiceAmount, 'CurrencyIso' => $currencyIso);
            $json = $this->callAPI($url, $postFields, $orderId, 'Initiate Payment'); //__FUNCTION__
            if ($json->IsSuccess == false) {
                return $json;
            }
            $PaymentMethodId = null;
            foreach ($json->Data->PaymentMethods as $value) {
                if ($value->PaymentMethodCode == $cardType) {
                    $PaymentMethodId = $value->PaymentMethodId;
                    break;
                }
            }

            if ($PaymentMethodId == null) {
                return (object) array('IsSuccess' => false, 'Data' => (object) array('ErrorMessage' => 'Please contact Account Manager to enable Payment method in your account'));
            }

            return $this->excutePayment($orderId, $PaymentMethodId);
        }

        //-----------------------------------------------------------------------------------------------------------------------------------------
        public function excutePayment($orderId, $PaymentMethodId) {
            $this->payment_data['CurlData']['PaymentMethodId'] = $PaymentMethodId;
            $url = "$this->api_url/v2/ExecutePayment";
            $json = $this->callAPI($url, $this->payment_data['CurlData'], $orderId, 'Excute Payment'); //__FUNCTION__
            if ($json->IsSuccess == false) {
                return $json;
            }
            return (object) array('Success' => true, 'InvoiceURL' => $json->Data->PaymentURL);
        }

        public function sendPayment($orderId) {

            $this->payment_data['CurlData']['NotificationOption'] = 'Lnk';

            $json = $this->callAPI("$this->api_url/v2/SendPayment", $this->payment_data['CurlData'], $orderId, 'Send Payment');
            return (object) array('Success' => true, 'InvoiceURL' => $json->Data->InvoiceURL);
        }

        //-----------------------------------------------------------------------------------------------------------------------------------------
        public function callAPI($url, $postFields, $orderId, $function) {

            $fields = json_encode($postFields);
            $msgLog = PHP_EOL . date('d.m.Y h:i:s') . " | Order #$orderId ----- $function";

            error_log("$msgLog - Request: $fields", 3, $this->pluginlog);


            //***************************************
            //call url
            //***************************************
            $curl = curl_init($url);

            curl_setopt_array($curl, array(
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $fields,
                CURLOPT_HTTPHEADER => array("Authorization: Bearer $this->token", 'Content-Type: application/json'),
                CURLOPT_RETURNTRANSFER => true,
            ));

            $res = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);


            //***************************************
            //check for errors
            //***************************************
            error_log("$msgLog - Response: $res", 3, $this->pluginlog);

            if ($err) {
                error_log("$msgLog - cURL Error: $err", 3, $this->pluginlog);
                return (object) array('IsSuccess' => false, 'Data' => (object) array('ErrorMessage' => $function . ' - cURL Error #:' . $err));
            }

            $json = json_decode($res);
            if (!isset($json->IsSuccess) || $json->IsSuccess == null || $json->IsSuccess == false) {

                //check for the error insde the object Please tell the exact postion and dont use else
                if (isset($json->ValidationErrors)) {
                    $err = implode(', ', array_column($json->ValidationErrors, 'Error'));
                } else if (isset($json->Data->ErrorMessage)) {
                    $err = $json->Data->ErrorMessage;
                }

                //if not get the message. this is due that sometimes errors with ValidationErrors has Error value null so either get the "Name" key or get the "Message"
                //example {"IsSuccess":false,"Message":"Invalid data","ValidationErrors":[{"Name":"invoiceCreate.InvoiceItems","Error":""}],"Data":null}
                //example {"Message":"No HTTP resource was found that matches the request URI 'https://apitest.myfatoorah.com/v2/SendPayment222'.","MessageDetail":"No route providing a controller name was found to match request URI 'https://apitest.myfatoorah.com/v2/SendPayment222'"}
                if (empty($err)) {
                    $err = (isset($json->Message)) ? $json->Message : __('Transaction failed with unknown error.');
                }

                error_log("$msgLog - Error: $err", 3, $this->pluginlog);
                return (object) array('IsSuccess' => false, 'Data' => (object) array('ErrorMessage' => $err));
            }
            update_post_meta($orderId, 'InvoiceId', $json->Data->InvoiceId);
            //***************************************
            //Success 
            //***************************************
            return $json;
        }

//-----------------------------------------------------------------------------------------------------------------------------------------

        /**
         * Process a refund if supported
         *
         * @param  int $order_id
         * @param  float $amount
         * @param  string $reason
         * @return  bool|wp_error True or false based on success, or a WP_Error object
         */
        public function process_refund($order_id, $amount = null, $reason = '') {

            if (!$paymentId = get_post_meta($order_id, 'PaymentId', true)) {
                return new WP_Error('mfMakeRefund', __('Please Refund Manually for this order', 'woocommerce-gateway-myfatoorah'));
            }

            $url = "$this->api_url/v2/MakeRefund";
            $postFields = array(
                'KeyType' => 'PaymentId',
                'Key' => $paymentId,
                'RefundChargeOnCustomer' => false,
                'ServiceChargeOnCustomer' => false,
                'Amount' => $amount,
                'Comment' => $reason,
            );
            /////////????????????????????? problem with currency ?? how to solve :0)
            // Send request and get response from server
            $json = $this->callAPI($url, $postFields, $order_id, 'Make Refund'); //__FUNCTION__
            // Check response
            $order = wc_get_order($order_id);
            if ($json->IsSuccess) {
                // Success
                update_post_meta($order_id, 'RefundReference', $json->Data->RefundReference);
                update_post_meta($order_id, 'RefundAmount', $json->Data->Amount);
                $order->add_order_note(__('Myfatoorah refund completed. Refund Reference ID: ', 'woocommerce-gateway-myfatoorah') . $json->Data->RefundReference);
                return true;
            } else {
                // Failure
                $msg = __('Myfatoorah refund error. Response: ', 'woocommerce-gateway-myfatoorah') . $json->Data->ErrorMessage;
                $order->add_order_note($msg);
                return new WP_Error('mfMakeRefund', $msg);
            }
        }

        public function splitPhone($phone) {
            if (strlen($phone) > 8) {
                $phone = intval(trim($phone));
                if (strpos($phone, '+') === false) {
                    $phone = '+' . $phone;
                }
                return sscanf($phone, '%4c%20c');
            } else {
                return sscanf($phone, '%0c%11c');
            }
        }

        /**
         * Get gateway icon.
         *
         * @access public
         * @return string
         */
        public function get_icon() {
            $icon = '';
            if (empty($this->logo_url) && empty($this->title)) {
                $icon = 'MyFatoorah';
            }
            if (!empty($this->logo_url)) {
                $icon = '<img src="' . $this->logo_url . '" class="myfatoorah_v2_logo" alt="MyFatoorah_logo" />';
            }
            return apply_filters('woocommerce_gateway_icon', $icon, $this->id);
        }

        public function get_description() {
            return apply_filters('woocommerce_gateway_description', $this->desc, $this->id);
        }

        /**
         * UI - Payment page fields for myfatoorah.
         */
        function payment_fields() {
            if ($this->get_description()) {
                ?>
                <p><?php echo $this->get_description(); ?></p>
                <?php
            }
            if (count($this->api_gateway_payment) == 1 && $this->api_gateway_payment[0] == 'myfatoorah') {
                
            } else {
                foreach ($this->api_gateway_payment as $key => $gateway) {
                    if ($gateway != 'myfatoorah') {
                        $checked = '';
                        if ($key == 0) {
                            $checked = 'checked';
                        }
                        ?>

                        <input class="mf-in-<?php echo $gateway; ?>" <?php echo $checked; ?> type="radio" id="<?php echo $gateway; ?>" name="mf_gateway" value="<?php echo $gateway; ?>">
                        <label for="<?php echo $gateway; ?>"><img class="mf-img-<?php echo $gateway; ?>" id="<?php echo $gateway; ?>" src="<?php echo "https://sa.myfatoorah.com/imgs/payment-methods/" . $gateway . ".png"; ?>" alt="<?php echo $gateway; ?>" width="75px"></label><br>
                        <?php
                    }
                }
            }
        }

        public function myfatoorah_v2_sucess() {
            echo "in myfatoorah sucess";
        }
        
        public function updatePostMeta($orderId, $json){
            $InvoiceTransactions = count($json->Data->InvoiceTransactions);
            $InvoiceTransactionsArr = $json->Data->InvoiceTransactions[$InvoiceTransactions - 1];
                update_post_meta($orderId, 'InvoiceValue', $json->Data->InvoiceValue);
                update_post_meta($orderId, 'CreatedDate', $json->Data->CreatedDate);
                update_post_meta($orderId, 'InvoiceDisplayValue', $json->Data->InvoiceDisplayValue);
                update_post_meta($orderId, 'PaymentGateway', $InvoiceTransactionsArr->PaymentGateway);
                update_post_meta($orderId, 'PaidCurrency', $InvoiceTransactionsArr->PaidCurrency);
                update_post_meta($orderId, 'PaidCurrencyValue', $InvoiceTransactionsArr->PaidCurrencyValue);
                update_post_meta($orderId, 'PaymentId', $InvoiceTransactionsArr->PaymentId);
        }

    }

    /**
     * Add the Gateway to WooCommerce
     * */
    function woocommerce_add_myfatoorah_v2_gateway($methods) {
        $methods[] = 'WC_Myfatoorah_V2';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'woocommerce_add_myfatoorah_v2_gateway');

    add_action('template_redirect', 'conditional_redirection_after_payment');

    function conditional_redirection_after_payment() {
        //set var and check if exist
        if (!isset($_GET['paymentId']) || !$paymentId = $_GET['paymentId']) {
            global $wp;
            if (is_checkout() && !empty($wp->query_vars['order-received'])) {
                $orderId = $wp->query_vars['order-received'];
                if (!$invoiceId = get_post_meta($orderId, 'InvoiceId', true)) {
                    return;
                }
            }
        }

        // When "thankyou" order-received page is reached …
        $mf = new WC_Myfatoorah_V2;
        $apiurl = 'https://apitest.myfatoorah.com';

        if ($mf->get_option('testmode') == 'no') {
            $apiurl = 'https://api.myfatoorah.com';
        }
        $url = "$apiurl/v2/getPaymentStatus";
        if(isset($paymentId)){
            $postFields = array(
                'KeyType' => 'paymentId',
                'Key' => $paymentId
            );
            $json = $mf->callAPI($url, $postFields, 'Payment ID '.$paymentId, 'MyFatoorah CallBack - Check Payment Status');

        }
        if(isset($invoiceId)){
            $postFields = array(
                'KeyType' => 'invoiceId',
                'Key' => $invoiceId
            );
            $json = $mf->callAPI($url, $postFields, 'Invoice ID '.$invoiceId, 'MyFatoorah CallBack - Check Payment Status');

        }

        if (!$json->IsSuccess) {
            return array('Success' => false, 'error' => $json->Data->ErrorMessage);
        }
        //set var and check if exist
        if (!$orderId = $json->Data->UserDefinedField) {
            return false;
        }
        
        $order = new WC_Order($orderId);
        if ($order->get_payment_method() != 'myfatoorah_v2') {
            return;
        }

        //processing    completed
        $status = $order->get_status();
        if ($status == 'processing' || $status == 'completed') {
            return;
        }

        
        //pending    failed    onHold    canceled    refunded    authentication
        if ($json->Data->InvoiceStatus == 'Paid' && is_wc_endpoint_url('order-received')) {

            $mfStatus = $mf->get_option('orderstatus');
            $order->update_status($mfStatus);
            $mf->updatePostMeta($orderId, $json);

            $msg = "Order #$orderId status is changed to $mfStatus";
            if ($mf->get_option('success_url')) {
                    wp_redirect($mf->get_option('success_url'));
                    exit;
                }
        } else {

            //very important due to user change his mind with the payment
            //$errorArrCount = count($json->Data->InvoiceTransactions);
            //end($json->Data->InvoiceTransactions)->Error
            $msg = 'Order #' . $orderId . ' is failed with error : ' . $json->Data->InvoiceTransactions[0]->Error;

            wc_add_notice($msg, 'error');
            $order->update_status('failed', $msg);
            if ($mf->get_option('fail_url')) {
                    wp_redirect($mf->get_option('fail_url'));
                    exit;
                }
        }

        error_log(PHP_EOL . date('d.m.Y h:i:s') . ' | Get Payment Status ----- ' . $msg, 3, $mf->pluginlog);
            $url = get_site_url();
        }
    }


   add_filter( 'cron_schedules', 'myfatoorah_add_cron_interval' );
    function myfatoorah_add_cron_interval( $schedules ) {
        $mins = 10;
        $woocommerce_hold_stock_minutes = WC_Admin_Settings::get_option('woocommerce_hold_stock_minutes') ? WC_Admin_Settings::get_option('woocommerce_hold_stock_minutes') : $mins;
        
        if($woocommerce_hold_stock_minutes < $mins)
            $mins = $woocommerce_hold_stock_minutes;
            
        $schedules['myfatoorah_check_pending_payments'] = array(
             'interval' => $mins*60,
             'display' => esc_html__( 'Every '. $mins .' Mins' ),
        );
        
        return $schedules;
    }
     
    
    add_action( 'wpb_custom_cron', 'check_pending_payments' );
    function check_pending_payments() {
        $statuses = array( 'wc-pending','wc-failed' );
        $result = wc_get_orders( array(
            'status' => $statuses,
        ) );
        
        $mf = new WC_Myfatoorah_V2;
        $apiurl = 'https://apitest.myfatoorah.com';
        if ($mf->get_option('testmode') == 'no') {
            $apiurl = 'https://api.myfatoorah.com';
        }
        foreach ($result as $results) {
            $orderId = $results->get_id();
            $order = new WC_Order($orderId);

            if (!$invoiceId = get_post_meta($orderId, 'InvoiceId', true)){
                exit;
            }
            if($order->get_payment_method() != 'myfatoorah_v2') {
                exit;
            }
            $url = "$apiurl/v2/getPaymentStatus";
            $postFields = array(
                'KeyType' => 'InvoiceId',
                'Key' => $invoiceId
            );
            $json = $mf->callAPI($url, $postFields, $orderId, 'Re-check Payment Status'); 
            if($json->Data->InvoiceStatus == 'Paid'){
                if (!empty($order)) {
                    $mf->updatePostMeta($orderId, $json);
                    $mfStatus = $mf->get_option('orderstatus');
                    $order->update_status($mfStatus);
                }
            }
            
        }
    }


