<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );}

class MpmcCurrencyCtrl extends MeprBaseCtrl {

	private static $init_currency_code;
	private static $init_currency_symbol = '';
	public static $did_fetch_options            = false;


	public function load_hooks() {
		// DB upgrades will happen here, as a non-blocking process hopefully
		add_action( 'mepr-process-options', array( $this, 'process_options' ) );
		add_action( 'mepr-product-save-meta', array( $this, 'save_product_meta' ) );
		add_action( 'mepr-membership-save-meta', array( $this, 'save_product_meta' ) );
		$this->load_switcher_dropdown();
		add_action( 'wp_ajax_nopriv_update_product_price', array( $this, 'ajax_update_product_price' ) );
		add_action( 'wp_ajax_update_product_price', array( $this, 'ajax_update_product_price' ) );
		add_action( 'wp_ajax_nopriv_mpmc_display_spc_invoice', array( $this, 'ajax_update_spc_invoice_table' ) );
		add_action( 'wp_ajax_mpmc_display_spc_invoice', array( $this, 'ajax_update_spc_invoice_table' ) );
		add_filter( 'mepr_transaction_product', array( $this, 'maybe_change_price' ) );
		add_filter( 'mepr_subscription_product', array( $this, 'maybe_change_price' ) );
		add_filter( 'mepr_transaction_product', array( $this, 'maybe_change_price_spc' ) );
		add_filter( 'mepr_subscription_product', array( $this, 'maybe_change_price_spc' ) );
		add_action( 'mepr-signup', array( $this, 'create_record' ), 1 );
		add_filter( 'mepr_fetch_options', array( $this, 'maybe_change_currency' ) );
		add_action( 'mepr-txn-status-complete', array( $this, 'record_currency_change' ) );
		add_filter( 'mepr_transaction_email_params', array( $this, 'change_currency_in_email' ), 10, 2 );
		// return MeprHooks::apply_filters( 'mepr_transaction_email_params', $params, $txn );

		// MeprHooks::do_action('mepr-txn-status-'.$this->status, $this);
	}


	public function maybe_change_price( $product ) {

		// Get product
		if ( ! isset( $_POST['mepr_transaction_id'] ) ) {
			return $product;
		}

		// Get transaction
		$txn = new MeprTransaction( $_POST['mepr_transaction_id'] );

		if ( ! $txn->id ) {
			return $product;
		}

		$product->price = $txn->amount;

		return $product;
	}


	public function maybe_change_price_spc( $product ) {
		// Get selected currency
		if ( ! isset( $_POST['mpmc_currency'] ) || empty( $_POST['mpmc_currency'] ) || empty( ( $currency = MpmcHelper::get_currency_by_code( $_POST['mpmc_currency'] ) ) ) ) {
			return $product;
		}

		$mepr_options  = MeprOptions::fetch();
		$mpmc_options  = get_option( 'mpmc_options' );
		$currency_code = sanitize_text_field( $_POST['mpmc_currency'] );

		// Check if currency is different from base currency
		if ( MpmcHelper::$base_currency == $currency_code ) {
			return $product;
		}

		// Get exchanged price - manual or API
		$currency_price        = MpmcHelper::get_exchanged_price( $product, $currency_code, MpmcHelper::$base_currency );
		$currency_trial_amount = MpmcHelper::get_exchanged_trial_amount( $product, $currency_code, MpmcHelper::$base_currency );

		if ( $currency_price ) {
			$product->price        = $currency_price;
			$product->trial_amount = $currency_trial_amount;
		}
		add_filter( 'mpmc-spc-currency-exchange', '__return_true' );
		return $product;
	}

	/**
	 * process signup
	 * €
	 *
	 * @param  mixed $amount
	 * @param  mixed $user
	 * @param  mixed $product_id
	 * @param  mixed $txn_id
	 *
	 * @return void
	 */
	public function create_record( $txn ) {

		// Get selected currency
		$currency_code = sanitize_text_field( $_POST['mpmc_currency'] );
		if ( empty( $currency_code ) || empty( ( $currency = MpmcHelper::get_currency_by_code( $currency_code ) ) ) ) {
			return;
		}

		// Bail if PM is Authorize.Net - https://community.developer.authorize.net/t5/Integration-and-Testing/Multi-currency-support/td-p/34758
		$pm = $txn->payment_method();
		if ( 'Authorize.net' == $pm->name ) {
			return;
		}

		// Bail if currency is different from base currency
		$mepr_options = MeprOptions::fetch();
		if ( $mepr_options->currency_code == $currency_code && false == $mepr_options->enable_spc ) {
			return;
		}

		$mpmc            = new MpmcCurrency();
		$mpmc->subtxn_id = $txn->id;
		$mpmc->type      = 'transaction';
		$mpmc->currency  = $currency['code'];
		$mpmc->store();

		if ( $txn->subscription() instanceof MeprSubscription ) {
			$mpmc            = new MpmcCurrency();
			$mpmc->subtxn_id = $txn->subscription()->id;
			$mpmc->currency  = $currency['code'];
			$mpmc->type      = 'subscription';
			$mpmc->store();
		}

	}

	/**
	 * Change Currency
	 *
	 * @param  mixed $mepr_options
	 * @return void
	 */
	public function maybe_change_currency( $mepr_options ) {

		// if((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || defined('DOING_AJAX')) {
    //   return $mepr_options;
		// }

		// if(!isset())

		if(empty($_REQUEST)) return $mepr_options;

		if ( false == self::$did_fetch_options ) {
			// this code only runs the first time the "mepr_fetch_options" hook is fired
			MpmcHelper::$base_currency = $mepr_options->currency_code;
			MpmcHelper::$base_symbol   = $mepr_options->currency_symbol;
		}

		self::$did_fetch_options = true;
		// write_log($_REQUEST);

		if ( $mepr_options->enable_spc && apply_filters( 'mpmc-spc-currency-exchange', false ) ) {
			$currency_code = sanitize_text_field( $_POST['mpmc_currency'] );
			// Set Currency
			$currency = MpmcHelper::get_currency_by_code( $currency_code );

			$mepr_options->currency_code   = $currency_code;
			$mepr_options->currency_symbol = $currency['symbol'];
			return $mepr_options;
		}
		// write_log(print_r($_REQUEST));

		if ( isset( $_REQUEST['txn'] ) ) {
			$txn_id = MeprUtils::base36_decode( $_REQUEST['txn'] );
			if ( empty( $txn_id ) ) {
				return $mepr_options;
			}
			$txn = new MeprTransaction( $txn_id );
		} elseif ( isset( $_REQUEST['mepr_transaction_id'] ) ) {
			$txn_id = sanitize_text_field( $_REQUEST['mepr_transaction_id'] );
			$txn    = new MeprTransaction( $txn_id );
		} elseif ( ( isset( $_REQUEST['token'] ) && ( $token = $_REQUEST['token'] ) ) ||
		( isset( $_REQUEST['TOKEN'] ) && ( $token = $_REQUEST['TOKEN'] ) ) ) {
			$obj = MeprTransaction::get_one_by_trans_num( $token );
			$txn = new MeprTransaction();
			$txn->load_data( $obj );
		}
		elseif(isset( $_REQUEST['id'] )){
			$txn = new MeprTransaction( $_REQUEST['id'] );
		}
		elseif( isset($_REQUEST['data']) ){ // Stripe webhook
			$charge = (object) $_REQUEST['data'];
			$obj = MeprTransaction::get_one_by_trans_num( $charge->id );
			$txn = new MeprTransaction();
			$txn->load_data( $obj );
			// write_log( MeprTransaction::txn_exists($charge->id) );
		}

		if ( ! isset( $txn ) || 0 == $txn->id || null == $txn->id ) {
			return $mepr_options;
		}

		$currency_row = MpmcCurrency::get_one_by_type( $txn->id, 'transaction' );

		if ( $txn->subscription() instanceof MeprSubscription ) {
			$currency_row = MpmcCurrency::get_one_by_type( $txn->subscription()->id, 'subscription' );
		}

		if ( empty( $currency_row ) || $currency_row->id <= 0 || false == $currency_row instanceof MpmcCurrency ) {
			return $mepr_options;
		}

		$currency_code = $currency_row->currency;

		// Is it base currency?
		if ( MpmcHelper::$base_currency == $currency_code ) { //todo
			return $mepr_options;
		}

		// Set Currency
		$currency = MpmcHelper::get_currency_by_code( $currency_code );

		$mepr_options->currency_code   = $currency_code;
		$mepr_options->currency_symbol = $currency['symbol'];
		return $mepr_options;
	}

	public function record_currency_change( $txn ) {
		// Does txn have subscription
		if ( ! $txn->subscription() ) {
			return;
		}

		// Get currency code from subscription
		$sub                   = $txn->subscription();
		$currency_subscription = MpmcCurrency::get_one_by_type( $sub->id, 'subscription' );

		if ( ! $currency_subscription ) {
			return;
		}

		$code = $currency_subscription->currency;

		$mpmc            = new MpmcCurrency();
		$mpmc->subtxn_id = $txn->id;
		$mpmc->currency  = $code;
		$mpmc->type      = 'transaction';
		$mpmc->store();
	}

	public function change_currency_in_email( $params, $txn ) {

		return $params;
	}

	/**
	 * Update Price Terms on the checkout page,
	 * before invoice is displayed
	 *
	 * @return void
	 */
	public static function ajax_update_product_price() {

    if((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) {
      return;
    }

		extract($_POST, EXTR_SKIP);

    if(!isset($prd_id) || empty($prd_id)) {
      echo 'false';
      die();
    }

    if(isset($code) && !empty($code)){
      check_ajax_referer('mepr_coupons', 'coupon_nonce');
    }

    $currency = sanitize_text_field(wp_unslash($currency));
    $payment_required = true;
    $product = new MeprProduct(sanitize_key(wp_unslash($prd_id)));

		$mepr_options = MeprOptions::fetch();

		// Is it base currency?
		if ( MpmcHelper::$base_currency !== $currency ) {
			// Different currency, so change it
			$currency_arr  = MpmcHelper::get_currency_by_code( $currency );
			$currency      = $currency_arr['code'];
			$xprice        = MpmcHelper::get_exchanged_price( $product, $currency, MpmcHelper::$base_currency );
			$xtrial_amount = MpmcHelper::get_exchanged_trial_amount( $product, $currency, MpmcHelper::$base_currency );

			if ( $xprice ) {
				$product->price                = $xprice;
				$product->trial_amount         = $xtrial_amount;
				$mepr_options->currency_code   = $currency;
				$mepr_options->currency_symbol = $currency_arr['symbol'];
			}
		}

    ob_start();
    MeprProductsHelper::display_invoice( $product, $code, $payment_required );
    $price_string = ob_get_clean();

    wp_send_json(array(
      'status' => 'success',
      'price_string' => $price_string,
      'payment_required' => $payment_required
    ));
  }

  // Called in the 'the_content' hook ... used to display invoice on single page checkout forms
  public function ajax_update_spc_invoice_table() {
    check_ajax_referer('mepr_coupons', 'coupon_nonce'); // Security check

    if(!isset($_POST['prd_id']) || empty($_POST['prd_id'])          ) {
      wp_send_json_error(esc_html__('Invalid Product ID or Payment Method', 'memberpress'));
    }

    // This code is pretty similar to MeprCheckoutCtrl::process_signup_form with minor modifications
    $is_existing_user = MeprUtils::is_user_logged_in(); // Check if the user is logged in already

    if($is_existing_user) {
      $usr = MeprUtils::get_currentuserinfo();
    }

    // Create a new transaction and set our new membership details
    $txn = new MeprTransaction();
    $txn->user_id = $is_existing_user ? $usr->ID : 0;

    // Get the membership in place
    $txn->product_id = sanitize_text_field($_POST['prd_id']);
    $product = $txn->product();

		// Set default price, adjust it later if coupon applies
    $price = $product->adjusted_price();

    // Default coupon object
    $cpn = (object)array('ID' => 0, 'post_title' => null);

    // Adjust membership price from the coupon code
    if(isset($_POST['code']) && !empty($_POST['code'])) {
      $code = sanitize_text_field($_POST['code']);

      if( MeprCoupon::is_valid_coupon_code($code, $product->ID) ){
        // Coupon object has to be loaded here or else txn create will record a 0 for coupon_id
        $cpn = MeprCoupon::get_one_from_code($code);

        if(($cpn !== false) || ($cpn instanceof MeprCoupon) && (false !== $is_valid_coupon) ) {
          $price = $product->adjusted_price($cpn->post_title);
        }
      }
    }

    $txn->set_subtotal($price);

    // Set the coupon id of the transaction
    $txn->coupon_id = $cpn->ID;

    // Figure out the Payment Method
    if(isset($_POST['mepr_payment_method']) && !empty($_POST['mepr_payment_method'])) {
      $txn->gateway = sanitize_text_field($_POST['mepr_payment_method']);
    }

    $pm = $txn->payment_method();

    // Create a new temporary subscription
    if($product->is_one_time_payment()) {
      $signup_type = 'non-recurring';
    }
    else {
      $signup_type = 'recurring';

      $sub              = new MeprSubscription();
      $sub->subscr_id   = 'tmp' . uniqid();
      $sub->user_id     = ( $is_existing_user ) ? $usr->ID : '';
      $sub->gateway     = $pm->id;

      $sub->load_product_vars( $product, $cpn->post_title, true );
      $sub->maybe_prorate(); // sub to sub
    }

    $invoice_html = MeprTransactionsHelper::get_invoice($txn, $sub);

    wp_send_json(array(
      'status' => 'success',
      'invoice' => $invoice_html,
    ));
  }

	public function load_switcher_dropdown() {
		// Load Position
		$mpmc_options = MpmcHelper::fetch_options();;
		$position     = $mpmc_options['mpmc_switcher_position'];

		switch ( $position ) {
			case 'before_form':
				add_action( 'mepr-above-checkout-form', array( $this, 'add_switcher_dropdown' ) );
				break;

			case 'before_name':
				add_action( 'mepr-checkout-before-name', array( $this, 'add_switcher_dropdown' ) );
				break;

			case 'after_email':
				add_action( 'mepr-checkout-after-email-field', array( $this, 'add_switcher_dropdown' ) );
				break;

			case 'after_password':
				add_action( 'mepr-checkout-after-password-fields', array( $this, 'add_switcher_dropdown' ) );
				break;

			case 'before_coupon':
				add_action( 'mepr-checkout-before-coupon-field', array( $this, 'add_switcher_dropdown' ) );
				break;

			case 'before_submit':
				add_action( 'mepr-checkout-before-submit', array( $this, 'add_switcher_dropdown' ) );
				break;

			default:
				// code...
				break;
		}
		// Add hidden field
		add_action( 'mepr-checkout-before-coupon-field', array( $this, 'switcher_hidden_field' ) );

	}

	/**
	 * Add hidden field
	 *
	 * @param  mixed $product_id
	 *
	 * @return void
	 */
	public function switcher_hidden_field( $product_id ) {
		/* CodeFusion80 start*/;
		if (!isset( $_POST['mpmc_currency'] )) {
			$force_currency	= ! empty( get_post_meta( $product_id, MeprProduct::$price_str . '_force_currency' , true ) ) ? get_post_meta( $product_id, MeprProduct::$price_str . '_force_currency', true ) : '';
			if ( !empty($force_currency) && MpmcHelper::$base_currency !== $force_currency ) {
				// Different currency, so change it
				$currency_arr  = MpmcHelper::get_currency_by_code( $force_currency );
				$_POST['mpmc_currency'] = $currency_arr['code'];
			}
		}
		/* CodeFusion80 end*/;
		?>
	<input type="hidden" id="mpmc_currency-<?php echo $product_id; ?>" name="mpmc_currency" value="<?php echo ( isset( $_POST['mpmc_currency'] ) ) ? esc_attr( stripslashes( $_POST['mpmc_currency'] ) ) : ''; ?>" />
		<?php
	}


	/**
	 * Currency Switcher select form
	 *
	 * @param  mixed $product_id
	 *
	 * @return void
	 */
	public function add_switcher_dropdown( $product_id ) {
		$mpmc_options       = get_option( 'mpmc_options' );
		$mepr_options       = MeprOptions::fetch();
		$product_currencies = $mpmc_options['mpmc_currencies'];
		$base_currency      = MpmcHelper::get_currency_by_code( $mepr_options->currency_code );
		$position           = $mpmc_options['mpmc_switcher_position'];

		MeprView::render( '/checkout/switcher_dropdown', get_defined_vars() );
	}

	/**
	 * process signup
	 *
	 * @param  mixed $amount
	 * @param  mixed $user
	 * @param  mixed $product_id
	 * @param  mixed $txn_id
	 *
	 * @return void
	 */
	public function save_transaction_currency( $txn ) {
		// Get selected currency
		$currency_code = sanitize_text_field( $_POST['mpmc_currency'] );

		if ( empty( $currency_code ) || empty( ( $currency = MpmcHelper::get_currency_by_code( $currency_code ) ) ) ) {
			return;
		}

		// Check if currency is different from base currency
		$mepr_options = MeprOptions::fetch();
		if ( $mepr_options->currency_code == $currency_code ) {
			return;
		}

		// Get exchanged price - manual or API
		$mpmc_options = get_option( 'mpmc_options' );
		$product      = $txn->product();
	}

	/**
	 * Runs after Options page is updated
	 *
	 * @param  mixed $post
	 *
	 * @return void
	 */
	public function process_options( $post ) {
		$options = array(
			'mpmc_currencies'        => isset( $post['mpmc_currencies'] ) ? $post['mpmc_currencies'] : '',
			'mpmc_service_provider'  => isset( $post['mpmc_service_provider'] ) ? $post['mpmc_service_provider'] : '',
			'mpmc_openx_api'         => isset( $post['mpmc_openx_api'] ) ? $post['mpmc_openx_api'] : '',
			'mpmc_exrate_api'        => isset( $post['mpmc_exrate_api'] ) ? $post['mpmc_exrate_api'] : '',
			'mpmc_switcher_position' => isset( $post['mpmc_switcher_position'] ) ? $post['mpmc_switcher_position'] : '',
		);
		update_option( 'mpmc_options', $options );

		// Get latest rates if Openx is selected
		if ( 'openx' === $post['mpmc_service_provider'] ) {
			$latest_json = MpmcHelper::get_latest_rates_openx( '', null, null, false );
			set_transient( 'mpmc_currency_transient', json_encode( $latest_json ), 24 * HOUR_IN_SECONDS );
			// Save timestamp
			$options['mpmc_openx_timestamp'] = $latest_json['timestamp'];
			update_option( 'mpmc_options', $options );
		}
	}

	/**
	 * Save product prices
	 * Runs when membership product (post) is saved
	 *
	 * @param  mixed $product
	 *
	 * @return void
	 */
	public function save_product_meta( $product ) {
		$mpmc_options = get_option( 'mpmc_options' );

		// Save each selected currency if manual is selected
		if ( 'manual' == $mpmc_options['mpmc_service_provider'] ) {
			$mpmc_options       = get_option( 'mpmc_options' );
			$product_currencies = $mpmc_options['mpmc_currencies'];

			if ( ! $product_currencies ) {
				return;
			}

			foreach ( $product_currencies as $curr_code ) {
				// Price
				$curr_string = sanitize_text_field( MeprProduct::$price_str . '_' . $curr_code );
				if ( isset( $_POST[ $curr_string ] ) ) {
					update_post_meta( $product->ID, $curr_string, $_POST[ $curr_string ] );
				}

				// Trial Amount
				$curr_string = sanitize_text_field( MeprProduct::$trial_amount_str . '_' . $curr_code );
				if ( isset( $_POST[ $curr_string ] ) ) {
					update_post_meta( $product->ID, $curr_string, $_POST[ $curr_string ] );
				}
			}
		}
		/* CodeFusion80 start*/;
		$curr_string = sanitize_text_field( MeprProduct::$price_str . '_force_currency' );
		if ( isset( $_POST[ $curr_string ] ) ) {
			update_post_meta( $product->ID, $curr_string, $_POST[ $curr_string ] );
		}
		/* CodeFusion80 end*/;
	}

}
new MpmcCurrencyCtrl();
