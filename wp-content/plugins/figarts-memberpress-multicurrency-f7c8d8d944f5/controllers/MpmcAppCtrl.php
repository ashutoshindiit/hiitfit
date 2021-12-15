<?php
if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

class MpmcAppCtrl extends MeprBaseCtrl {

  public function load_hooks() {
		add_filter( 'mepr_view_paths', array( $this, 'view_path' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'public_scripts' ) );
		add_action( 'mepr_activate_license_page', array( $this, 'activate_form' ), 10, 0 );
		add_action( 'admin_init', array( $this, 'activate_license' ) );
		add_action( 'admin_init', array( $this, 'deactivate_license' ) );
		add_action( 'admin_init', array( $this, 'plugin_updater' ) );
  }

	/**
	 * Add plugin path to memberpress view path
	 *
	 * @param  mixed $paths MemberPress paths
	 *
	 * @return mixed
	 */
	public function view_path( $paths ) {
		array_splice( $paths, 1, 0, MPMC_DIRPATH . 'views' );
		return $paths;
	}


	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    0.1
	 * @access   public
	 */
	public function admin_scripts() {

		wp_enqueue_style( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css' );
		wp_enqueue_script( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js', array( 'jquery' ), '', true );

		// please create also an empty JS file in your theme directory and include it too
		wp_enqueue_script( 'mpmcJS', MPMC_DIRURI . 'assets/admin.js', array( 'jquery', 'select2' ), '', true );
	}


	/**
	 * Register all of the hooks related to the public area functionality
	 * of the plugin.
	 *
	 * @since    0.1
	 * @access   public
	 */
	public function public_scripts() {
		wp_enqueue_script( 'mpmc-public', MPMC_DIRURI . 'assets/public.js', array( 'jquery' ), '', true );
		wp_localize_script(
			'mpmc-public',
			'mpmc_ajax',
			array(
				'ajaxurl'  => admin_url( 'admin-ajax.php' ),
				'security' => wp_create_nonce( 'mpmc_currency' ),
			)
		);

	}


	public function activate_form() {
		$license = get_option( 'mpmulticurrency_license_key' );
		$status  = get_option( 'mpmulticurrency_license_status' );
		?>

		<div class="mepr-page-title"></div>
		<h3><?php _e( 'Multicurrency Addon License', 'memberpress' ); ?></h3>


		<?php if ( ( ! isset( $license ) || empty( $license ) ) || ! $status ) : ?>

			<p class="description"><?php printf( __( 'MemberPress Multicurrency is a third party addon and requires a valid license to make it spin. <br/> If you don\'t have a License please go to %1$s to get one.', 'memberpress' ), '<a href="http://pluginette.com">Pluginette</a>' ); ?></p>

			<?php echo $this->show_message(); ?>

			<table class="form-table">
				<tr class="form-field">
					<th valign="top"><?php esc_html_e('MultiCurrency License Key:', 'memberpress'); ?></th>
					<td>
						<input type="text" id="mpmulticurrency-license-key" style="width: 400px; max-width: 100%;" name="mpmulticurrency_license_key" value="<?php echo ( isset( $_POST['mpmulticurrency_license_key'] ) ? $_POST['mpmulticurrency_license_key'] : $license ); ?>"/>

						<button type="button" data-url="<?php esc_url( menu_page_url('memberpress-options') ) ?>" data-action="mpmc-activate" id="mpmc-activate-key" class="button button-primary"><?php esc_html_e('Activate', 'memberpress'); ?></button>
					</td>
				</tr>
			</table>
		
		<?php else : ?>

			<div class="mepr-license-active">
				<div><h4><?php esc_html_e('Active License Key Information:', 'memberpress'); ?></h4></div>
				<table>
					<tr>
						<td><?php esc_html_e('License Key:', 'memberpress'); ?></td>
						<td>********-****-****-****-<?php echo substr( $license, -12 ); ?></td>
					</tr>
					<tr>
						<td><?php _e( 'Status:', 'memberpress' ); ?></td>
						<td><?php printf( __( '<b>Active on %s</b>', 'memberpress' ), MeprUtils::site_domain() ); ?></td>
					</tr>
					<tr>
						<td><?php _e( 'Product:', 'memberpress' ); ?></td>
						<td><?php echo MPMC_NAME; ?></td>
					</tr>
					<tr>

					</tr>
					<tr>

					</tr>
				</table>
				<div class="mepr-deactivate-button">
					<a href="#0" data-url="<?php esc_url( menu_page_url('memberpress-options') ) ?>" data-action="mpmc-deactivate" id="mpmc-deactivate-key" class="button button-primary" onclick="return confirm('<?php printf( __( 'Are you sure? MemberPress Multicurrency will not be fully functional on %s if this License Key is deactivated.', 'memberpress' ), MeprUtils::site_domain() ); ?>');"><?php esc_html_e('Deactivate', 'memberpress'); ?></a>
				</div>
			</div>

			<br/>
			<br/>

		<?php endif; ?>

		<?php
	}



	/**
	 * Initialize plugin updater
	 */
	public function plugin_updater() {
		// retrieve our license key from the DB.
		$license_key = trim( get_option( 'mpmulticurrency_license_key' ) );

		// setup the updater.
		new MpmcUpdater(
			MPMC_STORE_URL,
			MPMC_BASENAME,
			array(
				'version' => MPMC_VERSION, // current version number.
				'license' => $license_key, // license key (used get_option above to retrieve from DB).
				'item_id' => MPMC_ITEM_ID, // id of this product in EDD.
				'author'  => 'David Towoju', // author of this plugin.
				'url'     => home_url(),
			)
		);
	}

	/**
	 * Sanitize license
	 *
	 * @param mixed $new license key.
	 *
	 * @return string
	 */
	public function sanitize_license( $new ) {
		$old = get_option( 'mpmulticurrency_license_key' );
		if ( $old && $old !== $new ) {
			delete_option( 'mpmulticurrency_license_status' ); // new license has been entered, so must reactivate.
		}
		return $new;
	}


	/**
	 * Activate License Key
	 *
	 * @return void
	 */
	public function activate_license() {

		// listen for our activate button to be clicked.
		if ( isset( $_GET['action'] ) && $_GET['action'] == 'mpmc-activate' ) {

			// retrieve the license from the database.
			// $license = trim( get_option( 'mpmulticurrency_license_key' ) );
			$license = sanitize_text_field( $_GET['key'] );

			// data to send in our API request.
			$api_params = array(
				'edd_action' => 'activate_license',
				'license'    => $license,
				'item_name'  => rawurlencode( MPMC_NAME ), // the name of our product in EDD.
				'url'        => home_url(),
			);

			// Call the custom API.
			$response = wp_remote_post(
				MPMC_STORE_URL,
				array(
					'timeout'   => 15,
					'sslverify' => false,
					'body'      => $api_params,
				)
			);
			// make sure the response came back okay.
			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
				if ( is_wp_error( $response ) ) {
					$notif = $response->get_error_notif();
				} else {
					$notif = __( 'An error occurred, please try again.' );
				}
			} else {
				$license_data = json_decode( wp_remote_retrieve_body( $response ) );
				if ( false === $license_data->success ) {
					switch ( $license_data->error ) {
						case 'expired':
							$notif = sprintf(
								/* translators: %s: expiration date */
								esc_html__( 'Your license key expired on %s.', 'dsd' ),
								date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
							);
							break;
						case 'disabled':
						case 'revoked':
							$notif = __( 'Your license key has been disabled.' );
							break;
						case 'missing':
							$notif = __( 'Invalid license.' );
							break;
						case 'invalid':
						case 'site_inactive':
							$notif = __( 'Your license is not active for this URL.' );
							break;
						case 'item_name_mismatch':
							/* translators: %s: plugin name */
							$notif = sprintf( __( 'This appears to be an invalid license key for %s.' ), MPMC_NAME );
							break;
						case 'no_activations_left':
							$notif = __( 'Your license key has reached its activation limit.' );
							break;
						default:
							$notif = __( 'An error occurred, please try again.' );
							break;
					}
				}
			}

			// Check if anything passed on a notif constituting a failure.
			if ( ! empty( $notif ) ) {
				$base_url = menu_page_url('memberpress-options');
				$redirect = add_query_arg(
					array(
						'sl_activation' => 'false',
						'notif'         => rawurlencode( $notif ),
					),
					$base_url
				);
				wp_safe_redirect( $redirect );
				exit();
			}

			// $license_data->license will be either "valid" or "invalid"
			update_option( 'mpmulticurrency_license_key', $license );
			update_option( 'mpmulticurrency_license_status', $license_data->license );
			wp_safe_redirect( menu_page_url('memberpress-options') );
			exit();
		}
	}

	/**
	 * Illustrates how to deactivate a license key.
	 * This will decrease the site count
	 */
	public function deactivate_license() {
		// listen for our activate button to be clicked.
		if ( isset( $_GET['action'] ) && $_GET['action'] == 'mpmc-deactivate' && isset( $_GET['page'] ) && $_GET['page'] == 'memberpress-options' ) {

			// retrieve the license from the database.
			$license = trim( get_option( 'mpmulticurrency_license_key' ) );

			// data to send in our API request.
			$api_params = array(
				'edd_action' => 'deactivate_license',
				'license'    => $license,
				'item_name'  => rawurlencode( MPMC_NAME ), // the name of our product in EDD.
				'url'        => home_url(),
			);

			// Call the custom API.
			$response = wp_remote_post(
				MPMC_STORE_URL,
				array(
					'timeout'   => 15,
					'sslverify' => false,
					'body'      => $api_params,
				)
			);

			// make sure the response came back okay.
			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
				if ( is_wp_error( $response ) ) {
					$notif = $response->get_error_notif();
				} else {
					$notif = __( 'An error occurred, please try again.' );
				}
				$base_url = admin_url( 'admin.php?page=memberpress-updates' );
				$redirect = add_query_arg(
					array(
						'sl_activation' => 'false',
						'notif'         => rawurlencode( $notif ),
					),
					$base_url
				);
				wp_safe_redirect( $redirect );
				exit();
			}
			// decode the license data.
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
			// $license_data->license will be either "deactivated" or "failed".
			if ( 'deactivated' === $license_data->license ) {
				delete_option( 'mpmulticurrency_license_status' );
			}
			wp_safe_redirect( menu_page_url('memberpress-options') );
			exit();
		}
	}

	/**
	 * This is a means of catching errors from the activation method above and displaying it to the customer
	 */
	public function show_message() {
		if ( isset( $_GET['sl_activation'] ) && ! empty( $_GET['notif'] ) ) {
			$html = '';
			if ( 'false' == $_GET['sl_activation'] ) {
				$notif = urldecode( sanitize_text_field( wp_unslash( $_GET['notif'] ) ) );
				$html  = '<div class="error"> <p>' . esc_html( $notif ) . '</p> </div>';
			}
			return $html;
		}
	}


}
