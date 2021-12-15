<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );}

class MpmcHelper extends MeprOptionsHelper {
	public static $base_currency;
	public static $base_symbol;

	/**
	 * mpmc_payment_currencies_dropdown
	 *
	 * @param  mixed $field_name
	 * @param  mixed $code
	 *
	 * @return void
	 */
	static function payment_multicurrencies_dropdown( $field_name, $code ) {
		$codes = MeprHooks::apply_filters( 'mepr-currency-codes', array( 'USD', 'AED', 'AUD', 'AWG', 'BGN', 'BRL', 'BWP', 'CAD', 'CHF', 'CNY', 'COP', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HRK', 'HUF', 'HUN', 'IDR', 'ILS', 'INR', 'ISK', 'JOD', 'JPY', 'KES', 'KRW', 'MXN', 'MYR', 'NGN', 'NOK', 'NZD', 'PEN', 'PHP', 'PKR', 'PLN', 'RON', 'RUB', 'SAR', 'SEK', 'SGD', 'THB', 'TRY', 'TWN', 'VND', 'ZAR' ) );

		$field_value = isset( $_POST[ $field_name ] ) ? $_POST[ $field_name ] : null;

		?>
		<select name="mpmc_currencies[]" class="mepr-dropdown mepr-payment-formats-dropdown" id="mpmc_currencies" multiple="multiple" style="width:50%;max-width:10em;">
		<?php
		foreach ( $codes as $curr_code ) {
			?>
			<?php $selected = ( in_array( $curr_code, $code ) ) ? ' selected="selected"' : ''; ?>
			<option value="<?php echo $curr_code; ?>" <?php echo $selected; ?>><?php echo $curr_code; ?>&nbsp;</option>
			<?php
		}
		?>
		</select>
		<?php
	}

	/**
	 * Change MEPR currency
	 *
	 * @param  mixed $txn
	 *
	 * @return void
	 */
	public static function change_mepr_currency( $txn, $restore_to_default = false ) {
		$mepr_options = MeprOptions::fetch();

		if ( $restore_to_default ) {
			$currency = self::$base_currency;
			$symbol   = self::$base_symbol;

			$mepr_options->currency_code   = $currency;
			$mepr_options->currency_symbol = $symbol;
			return;
		}

		$base_currency = $mepr_options->currency_code;
		$base_symbol   = $mepr_options->currency_symbol;
		$currency_code = self::get_subtxn_curr_by_id( $txn->id, 'transaction' );

		if ( ! empty( $currency_code ) ) {

			$base_currency = $mepr_options->currency_code;
			$base_symbol   = $mepr_options->currency_symbol;
			$mepr_options  = MeprOptions::fetch();

			if ( $mepr_options->currency_code != $currency_code ) {

				// Set Currency
				$currency = self::get_currency_by_code( $currency_code );

				$mepr_options->currency_code   = $currency_code;
				$mepr_options->currency_symbol = $currency['symbol'];
			}
		} else {
			$mepr_options->currency_code   = $base_currency;
			$mepr_options->currency_symbol = $base_symbol;
		}
	}

	/**
	 * get_symbol_by_code
	 *
	 * @param  mixed $code
	 *
	 * @return void
	 */
	function get_symbol_by_code( $code ) {
		$currency = self::get_currency_by_code( $code );
		if ( $currency ) {
			return $currency['symbol'];
		}
		// return $found;
	}

	/**
	 * get_currency_by_code
	 *
	 * @param  mixed $code
	 *
	 * @return void
	 */
	public static function get_currency_by_code( $code ) {
		$currencies = self::get_all_currencies();
		if ( $code ) {
			$found = $currencies[ $code ];
			return $found;
		}
	}

	/**
	 * get_base_symbol
	 *
	 * @param  mixed $code
	 *
	 * @return void
	 */
	function get_base_symbol() {
		$mepr_options = MeprOptions::fetch();
		$base_symbol  = $mepr_options->currency_symbol;
		return $base_symbol;
	}


	/**
	 * mpmc_get_all_currencies
	 *
	 * @return void
	 */
	static function get_all_currencies() {

		$currencies = apply_filters(
			'mpmc-currency-symbols',
			array(
				'ALL' => array(
					'symbol' => 'Lek',
					'code'   => 'ALL',
					'name'   => 'Albania Lek',
				),
				'AED' => array(
					'symbol' => 'AED',
					'code'   => 'AED',
					'name'   => 'United Arab Emirates Dirham',
				),
				'AFN' => array(
					'symbol' => 'Af',
					'code'   => 'AFN',
					'name'   => 'Afghanistan Afghani',
				),
				'ARS' => array(
					'symbol' => '$',
					'code'   => 'ARS',
					'name'   => 'Argentina Peso',
				),
				'AWG' => array(
					'symbol' => 'ƒ',
					'code'   => 'AWG',
					'name'   => 'Aruba Guilder',
				),
				'AUD' => array(
					'symbol' => '$',
					'code'   => 'AUD',
					'name'   => 'Australia Dollar',
				),
				'AZN' => array(
					'symbol' => 'ман',
					'code'   => 'AZN',
					'name'   => 'Azerbaijan New Manat',
				),
				'BSD' => array(
					'symbol' => '$',
					'code'   => 'BSD',
					'name'   => 'Bahamas Dollar',
				),
				'BBD' => array(
					'symbol' => '$',
					'code'   => 'BBD',
					'name'   => 'Barbados Dollar',
				),
				'BDT' => array(
					'symbol' => '৳',
					'code'   => 'BDT',
					'name'   => 'Bangladeshi taka',
				),
				'BYR' => array(
					'symbol' => 'p.',
					'code'   => 'BYR',
					'name'   => 'Belarus Ruble',
				),
				'BZD' => array(
					'symbol' => 'BZ$',
					'code'   => 'BZD',
					'name'   => 'Belize Dollar',
				),
				'BMD' => array(
					'symbol' => '$',
					'code'   => 'BMD',
					'name'   => 'Bermuda Dollar',
				),
				'BOB' => array(
					'symbol' => '$b',
					'code'   => 'BOB',
					'name'   => 'Bolivia Boliviano',
				),
				'BAM' => array(
					'symbol' => 'KM',
					'code'   => 'BAM',
					'name'   => 'Bosnia and Herzegovina Convertible Marka',
				),
				'BWP' => array(
					'symbol' => 'P',
					'code'   => 'BWP',
					'name'   => 'Botswana Pula',
				),
				'BGN' => array(
					'symbol' => 'лв',
					'code'   => 'BGN',
					'name'   => 'Bulgaria Lev',
				),
				'BRL' => array(
					'symbol' => 'R$',
					'code'   => 'BRL',
					'name'   => 'Brazil Real',
				),
				'BND' => array(
					'symbol' => '$',
					'code'   => 'BND',
					'name'   => 'Brunei Darussalam Dollar',
				),
				'KHR' => array(
					'symbol' => '៛',
					'code'   => 'KHR',
					'name'   => 'Cambodia Riel',
				),
				'CAD' => array(
					'symbol' => '$',
					'code'   => 'CAD',
					'name'   => 'Canada Dollar',
				),
				'KYD' => array(
					'symbol' => '$',
					'code'   => 'KYD',
					'name'   => 'Cayman Islands Dollar',
				),
				'CLP' => array(
					'symbol' => '$',
					'code'   => 'CLP',
					'name'   => 'Chile Peso',
				),
				'CNY' => array(
					'symbol' => '¥',
					'code'   => 'CNY',
					'name'   => 'China Yuan Renminbi',
				),
				'COP' => array(
					'symbol' => '$',
					'code'   => 'COP',
					'name'   => 'Colombia Peso',
				),
				'CRC' => array(
					'symbol' => '₡',
					'code'   => 'CRC',
					'name'   => 'Costa Rica Colon',
				),
				'HRK' => array(
					'symbol' => 'kn',
					'code'   => 'HRK',
					'name'   => 'Croatia Kuna',
				),
				'CUP' => array(
					'symbol' => '⃌',
					'code'   => 'CUP',
					'name'   => 'Cuba Peso',
				),
				'CZK' => array(
					'symbol' => 'Kč',
					'code'   => 'CZK',
					'name'   => 'Czech Republic Koruna',
				),
				'DKK' => array(
					'symbol' => 'kr',
					'code'   => 'DKK',
					'name'   => 'Denmark Krone',
				),
				'DOP' => array(
					'symbol' => 'RD$',
					'code'   => 'DOP',
					'name'   => 'Dominican Republic Peso',
				),
				'XCD' => array(
					'symbol' => '$',
					'code'   => 'XCD',
					'name'   => 'East Caribbean Dollar',
				),
				'EGP' => array(
					'symbol' => '£',
					'code'   => 'EGP',
					'name'   => 'Egypt Pound',
				),
				'SVC' => array(
					'symbol' => '$',
					'code'   => 'SVC',
					'name'   => 'El Salvador Colon',
				),
				'EEK' => array(
					'symbol' => '',
					'code'   => 'EEK',
					'name'   => 'Estonia Kroon',
				),
				'EUR' => array(
					'symbol' => '€',
					'code'   => 'EUR',
					'name'   => 'Euro Member Countries',
				),
				'FKP' => array(
					'symbol' => '£',
					'code'   => 'FKP',
					'name'   => 'Falkland Islands (Malvinas) Pound',
				),
				'FJD' => array(
					'symbol' => '$',
					'code'   => 'FJD',
					'name'   => 'Fiji Dollar',
				),
				'GHC' => array(
					'symbol' => '',
					'code'   => 'GHC',
					'name'   => 'Ghana Cedis',
				),
				'GIP' => array(
					'symbol' => '£',
					'code'   => 'GIP',
					'name'   => 'Gibraltar Pound',
				),
				'GTQ' => array(
					'symbol' => 'Q',
					'code'   => 'GTQ',
					'name'   => 'Guatemala Quetzal',
				),
				'GGP' => array(
					'symbol' => '',
					'code'   => 'GGP',
					'name'   => 'Guernsey Pound',
				),
				'GYD' => array(
					'symbol' => '$',
					'code'   => 'GYD',
					'name'   => 'Guyana Dollar',
				),
				'HNL' => array(
					'symbol' => 'L',
					'code'   => 'HNL',
					'name'   => 'Honduras Lempira',
				),
				'HKD' => array(
					'symbol' => '$',
					'code'   => 'HKD',
					'name'   => 'Hong Kong Dollar',
				),
				'HUF' => array(
					'symbol' => 'Ft',
					'code'   => 'HUF',
					'name'   => 'Hungary Forint',
				),
				'ISK' => array(
					'symbol' => 'kr',
					'code'   => 'ISK',
					'name'   => 'Iceland Krona',
				),
				'INR' => array(
					'symbol' => '₹',
					'code'   => 'INR',
					'name'   => 'India Rupee',
				),
				'IDR' => array(
					'symbol' => 'Rp',
					'code'   => 'IDR',
					'name'   => 'Indonesia Rupiah',
				),
				'IRR' => array(
					'symbol' => '﷼',
					'code'   => 'IRR',
					'name'   => 'Iran Rial',
				),
				'IMP' => array(
					'symbol' => '',
					'code'   => 'IMP',
					'name'   => 'Isle of Man Pound',
				),
				'ILS' => array(
					'symbol' => '₪',
					'code'   => 'ILS',
					'name'   => 'Israel Shekel',
				),
				'JMD' => array(
					'symbol' => 'J$',
					'code'   => 'JMD',
					'name'   => 'Jamaica Dollar',
				),
				'JPY' => array(
					'symbol' => '¥',
					'code'   => 'JPY',
					'name'   => 'Japan Yen',
				),
				'JEP' => array(
					'symbol' => '£',
					'code'   => 'JEP',
					'name'   => 'Jersey Pound',
				),
				'KZT' => array(
					'symbol' => 'лв',
					'code'   => 'KZT',
					'name'   => 'Kazakhstan Tenge',
				),
				'KPW' => array(
					'symbol' => '₩',
					'code'   => 'KPW',
					'name'   => 'Korea (North) Won',
				),
				'KRW' => array(
					'symbol' => '₩',
					'code'   => 'KRW',
					'name'   => 'Korea (South) Won',
				),
				'KGS' => array(
					'symbol' => 'лв',
					'code'   => 'KGS',
					'name'   => 'Kyrgyzstan Som',
				),
				'LAK' => array(
					'symbol' => '₭',
					'code'   => 'LAK',
					'name'   => 'Laos Kip',
				),
				'LVL' => array(
					'symbol' => 'Ls',
					'code'   => 'LVL',
					'name'   => 'Latvia Lat',
				),
				'LBP' => array(
					'symbol' => '£',
					'code'   => 'LBP',
					'name'   => 'Lebanon Pound',
				),
				'LRD' => array(
					'symbol' => '$',
					'code'   => 'LRD',
					'name'   => 'Liberia Dollar',
				),
				'LTL' => array(
					'symbol' => 'Lt',
					'code'   => 'LTL',
					'name'   => 'Lithuania Litas',
				),
				'MKD' => array(
					'symbol' => 'ден',
					'code'   => 'MKD',
					'name'   => 'Macedonia Denar',
				),
				'MYR' => array(
					'symbol' => 'RM',
					'code'   => 'MYR',
					'name'   => 'Malaysia Ringgit',
				),
				'MUR' => array(
					'symbol' => '₨',
					'code'   => 'MUR',
					'name'   => 'Mauritius Rupee',
				),
				'MXN' => array(
					'symbol' => '$',
					'code'   => 'MXN',
					'name'   => 'Mexico Peso',
				),
				'MNT' => array(
					'symbol' => '₮',
					'code'   => 'MNT',
					'name'   => 'Mongolia Tughrik',
				),
				'MZN' => array(
					'symbol' => 'MT',
					'code'   => 'MZN',
					'name'   => 'Mozambique Metical',
				),
				'NAD' => array(
					'symbol' => '$',
					'code'   => 'NAD',
					'name'   => 'Namibia Dollar',
				),
				'NPR' => array(
					'symbol' => '₨',
					'code'   => 'NPR',
					'name'   => 'Nepal Rupee',
				),
				'ANG' => array(
					'symbol' => 'ƒ',
					'code'   => 'ANG',
					'name'   => 'Netherlands Antilles Guilder',
				),
				'NZD' => array(
					'symbol' => '$',
					'code'   => 'NZD',
					'name'   => 'New Zealand Dollar',
				),
				'NIO' => array(
					'symbol' => 'C$',
					'code'   => 'NIO',
					'name'   => 'Nicaragua Cordoba',
				),
				'NGN' => array(
					'symbol' => '₦',
					'code'   => 'NGN',
					'name'   => 'Nigeria Naira',
				),
				'NOK' => array(
					'symbol' => 'kr',
					'code'   => 'NOK',
					'name'   => 'Norway Krone',
				),
				'OMR' => array(
					'symbol' => '﷼',
					'code'   => 'OMR',
					'name'   => 'Oman Rial',
				),
				'PKR' => array(
					'symbol' => '₨',
					'code'   => 'PKR',
					'name'   => 'Pakistan Rupee',
				),
				'PAB' => array(
					'symbol' => 'B/.',
					'code'   => 'PAB',
					'name'   => 'Panama Balboa',
				),
				'PYG' => array(
					'symbol' => 'Gs',
					'code'   => 'PYG',
					'name'   => 'Paraguay Guarani',
				),
				'PEN' => array(
					'symbol' => 'S/.',
					'code'   => 'PEN',
					'name'   => 'Peru Nuevo Sol',
				),
				'PHP' => array(
					'symbol' => '₱',
					'code'   => 'PHP',
					'name'   => 'Philippines Peso',
				),
				'PLN' => array(
					'symbol' => 'zł',
					'code'   => 'PLN',
					'name'   => 'Poland Zloty',
				),
				'QAR' => array(
					'symbol' => '﷼',
					'code'   => 'QAR',
					'name'   => 'Qatar Riyal',
				),
				'RON' => array(
					'symbol' => 'lei',
					'code'   => 'RON',
					'name'   => 'Romania New Leu',
				),
				'RUB' => array(
					'symbol' => 'руб',
					'code'   => 'RUB',
					'name'   => 'Russia Ruble',
				),
				'SHP' => array(
					'symbol' => '£',
					'code'   => 'SHP',
					'name'   => 'Saint Helena Pound',
				),
				'SAR' => array(
					'symbol' => '﷼',
					'code'   => 'SAR',
					'name'   => 'Saudi Arabia Riyal',
				),
				'RSD' => array(
					'symbol' => 'Дин.',
					'code'   => 'RSD',
					'name'   => 'Serbia Dinar',
				),
				'SCR' => array(
					'symbol' => '₨',
					'code'   => 'SCR',
					'name'   => 'Seychelles Rupee',
				),
				'SGD' => array(
					'symbol' => '$',
					'code'   => 'SGD',
					'name'   => 'Singapore Dollar',
				),
				'SBD' => array(
					'symbol' => '$',
					'code'   => 'SBD',
					'name'   => 'Solomon Islands Dollar',
				),
				'SOS' => array(
					'symbol' => 'S',
					'code'   => 'SOS',
					'name'   => 'Somalia Shilling',
				),
				'ZAR' => array(
					'symbol' => 'R',
					'code'   => 'ZAR',
					'name'   => 'South Africa Rand',
				),
				'LKR' => array(
					'symbol' => '₨',
					'code'   => 'LKR',
					'name'   => 'Sri Lanka Rupee',
				),
				'SEK' => array(
					'symbol' => 'kr',
					'code'   => 'SEK',
					'name'   => 'Sweden Krona',
				),
				'CHF' => array(
					'symbol' => 'CHF',
					'code'   => 'CHF',
					'name'   => 'Switzerland Franc',
				),
				'SRD' => array(
					'symbol' => '$',
					'code'   => 'SRD',
					'name'   => 'Suriname Dollar',
				),
				'SYP' => array(
					'symbol' => '£',
					'code'   => 'SYP',
					'name'   => 'Syria Pound',
				),
				'TWD' => array(
					'symbol' => 'NT$',
					'code'   => 'TWD',
					'name'   => 'Taiwan New Dollar',
				),
				'TWN' => array(
					'symbol' => 'NT$',
					'code'   => 'TWD',
					'name'   => 'Taiwan New Dollar',
				),
				'THB' => array(
					'symbol' => '฿',
					'code'   => 'THB',
					'name'   => 'Thailand Baht',
				),
				'TTD' => array(
					'symbol' => '$',
					'code'   => 'TTD',
					'name'   => 'Trinidad and Tobago Dollar',
				),
				'TRY' => array(
					'symbol' => '₺',
					'code'   => 'TRY',
					'name'   => 'Turkey Lira',
				),
				'TRL' => array(
					'symbol' => '₺',
					'code'   => 'TRL',
					'name'   => 'Turkey Lira',
				),
				'TVD' => array(
					'symbol' => '',
					'code'   => 'TVD',
					'name'   => 'Tuvalu Dollar',
				),
				'UAH' => array(
					'symbol' => '₴',
					'code'   => 'UAH',
					'name'   => 'Ukraine Hryvna',
				),
				'GBP' => array(
					'symbol' => '£',
					'code'   => 'GBP',
					'name'   => 'United Kingdom Pound',
				),
				'USD' => array(
					'symbol' => '$',
					'code'   => 'USD',
					'name'   => 'United States Dollar',
				),
				'UYU' => array(
					'symbol' => '$U',
					'code'   => 'UYU',
					'name'   => 'Uruguay Peso',
				),
				'UZS' => array(
					'symbol' => 'лв',
					'code'   => 'UZS',
					'name'   => 'Uzbekistan Som',
				),
				'VEF' => array(
					'symbol' => 'Bs',
					'code'   => 'VEF',
					'name'   => 'Venezuela Bolivar',
				),
				'VND' => array(
					'symbol' => '₫',
					'code'   => 'VND',
					'name'   => 'Viet Nam Dong',
				),
				'YER' => array(
					'symbol' => '﷼',
					'code'   => 'YER',
					'name'   => 'Yemen Rial',
				),
				'ZWD' => array(
					'symbol' => '',
					'code'   => 'ZWD',
					'name'   => 'Zimbabwe Dollar',
				),
			)
		);

		return $currencies;
	}

	/**
	 * Get currency of subscription or trasaction
	 *
	 * @param  mixed $some_parameter
	 *
	 * @return void
	 */
	static function get_subtxn_curr_by_id( $id, $type ) {

		$mpmc = MpmcCurrency::get_one_by_type( $id, $type );

		if ( empty( $mpmc ) || $mpmc->id <= 0 || false == $mpmc instanceof MpmcCurrency ) {
			return false;
		}
		return $mpmc->currency;
	}

	/**
	 * Grab Openx rates
	 *
	 * @param  mixed $base
	 * @param  mixed $to
	 * @param  mixed $amount
	 * @param  mixed $convert
	 *
	 * @return void
	 */
	public static function get_latest_rates_openx( $base = '', $to, $amount, $convert = false ) {
		$options = self::fetch_options();
		$api     = $options['mpmc_openx_api'];

		if ( empty( $api ) ) {
			return;
		}

		// $mepr_options = MeprOptions::fetch();
		$base = self::$base_currency;

		// Retrieve the currency exchange rates
		$request = wp_remote_get( "https://openexchangerates.org/latest.json?app_id=$api&base=$base" );

		// If the remote request didn't return an error
		if ( ! is_wp_error( $request ) && 200 == $request['response']['code'] ) {

			// Response is json format, so decode it into an array
			$rates = json_decode( $request['body'], true );
			// If we're not converting, return json
			$convert = (bool) $convert;
			if ( false === $convert ) {
				return $rates;
			}

			// If the json decoding was successful
			if ( false !== $rates ) {

				// Make sure the $base and $to parameter are strings
				$base = (string) $base;
				$to   = (string) $to;

				// Make sure the $amount parameter is a float
				$amount = (float) $amount;

				// If the $base and $to currencies exist in the array, work out the $amount in the $to currency and return it
				if ( isset( $rates['rates'][ $base ] ) && isset( $rates['rates'][ $to ] ) ) {
					return $amount * ( $rates['rates'][ $to ] * ( 1 / $rates['rates'][ $base ] ) );
				} else {
					return false; // One or both of the specified currencies don't exist in the array
				}
			} else {
				return false; // json_decode failed, perhaps invalid json
			}
		} else {
			// return $request['response']['message'] .': '. esc_html__('API Error', 'mpmc'); //An error occurred when retrieving the data
			return false; // An error occurred when retrieving the data
		}
	}

	/**
	 * Grab Openx rates
	 *
	 * @param  mixed $base
	 * @param  mixed $to
	 * @param  mixed $amount
	 * @param  mixed $convert
	 *
	 * @return void
	 */
	public static function get_latest_rates_exrate( $base = '', $to, $amount, $convert = false ) {
		$options = self::fetch_options();
		$api     = $options['mpmc_exrate_api'];

		if ( empty( $api ) ) {
			return;
		}

		$mepr_options = MeprOptions::fetch();
		$base         = $mepr_options->currency_code;

		// Retrieve the currency exchange rates
		$request = wp_remote_get( "https://prime.exchangerate-api.com/v5/$api/latest/$base" );

		// If the remote request didn't return an error
		if ( ! is_wp_error( $request ) && 200 == $request['response']['code'] ) {

			// Response is json format, so decode it into an array
			$rates = json_decode( $request['body'], true );

			// Is there something wrong?
			if ( 'error' == $rates['result'] ) {
				return false;
			}

			// If we're not converting, return json
			$convert = (bool) $convert;
			if ( false === $convert ) {
				$rates['rates'] = $rates['conversion_rates'];
				return $rates;
			}

			// If the json decoding was successful
			if ( false !== $rates ) {

				// Make sure the $base and $to parameter are strings
				$base = (string) $base;
				$to   = (string) $to;

				// Make sure the $amount parameter is a float
				$amount = (float) $amount;

				// If the $base and $to currencies exist in the array, work out the $amount in the $to currency and return it
				if ( isset( $rates['conversion_rates'][ $base ] ) && isset( $rates['conversion_rates'][ $to ] ) ) {
					return $amount * ( $rates['conversion_rates'][ $to ] * ( 1 / $rates['conversion_rates'][ $base ] ) );
				} else {
					return false; // One or both of the specified currencies don't exist in the array
				}
			} else {
				return false; // json_decode failed, perhaps invalid json
			}
		} else {
			// return $request['response']['message'] .': '. esc_html__('API Error', 'mpmc'); //An error occurred when retrieving the data
			return false; // An error occurred when retrieving the data
		}
	}


	/**
	 * Exchanges the price and returns new price
	 *
	 * @param  mixed $product_id
	 * @param  mixed $code
	 *
	 * @return void
	 */
	public static function get_exchanged_price( $product, $code = '', $currency_code ) {
		$price = '';

		// Get exchanged price - manual or API
		$mpmc_options = self::fetch_options();

		if ( 'manual' == $mpmc_options['mpmc_service_provider'] ) {
			$price = get_post_meta( $product->ID, MeprProduct::$price_str . '_' . $code, true );
		} elseif ( in_array( $mpmc_options['mpmc_service_provider'], array( 'openx', 'exrate' ) ) ) {
			$latest_currencies = self::get_latest_currencies( $mpmc_options['mpmc_service_provider'] );

			if ( is_array( $latest_currencies['rates'] ) && isset( $latest_currencies['rates'] ) ) {

				// Can we find the rate?
				$rates = $latest_currencies['rates'];

				if ( $rates[ $code ] ) {

					$base_code = $currency_code;
					$price     = $product->price;
					$price     = $price * ( $rates[ $code ] * ( 1 / $rates[ $base_code ] ) );
					$price     = round( $price );
				}
			}
		}

		return $price;
	}

	/**
	 * Get exchanged trial amount
	 *
	 * @param  mixed $product_id
	 * @param  mixed $code
	 *
	 * @return mixed
	 */
	public static function get_exchanged_trial_amount( $product, $code = '', $currency_code ) {
		$trial_amount = '';
		// Get exchanged trial_amount - manual or API
		$mpmc_options = self::fetch_options();
		if ( 'manual' == $mpmc_options['mpmc_service_provider'] ) {
			$trial_amount = get_post_meta( $product->ID, MeprProduct::$trial_amount_str . '_' . $code, true );
		} elseif ( in_array( $mpmc_options['mpmc_service_provider'], array( 'openx', 'exrate' ) ) ) {
			$latest_currencies = self::get_latest_currencies( $mpmc_options['mpmc_service_provider'] );
			if ( $rates = $latest_currencies['rates'] ) {
				// Can we find the rate?
				if ( $rates[ $code ] ) {
					$base_code    = $currency_code;
					$trial_amount = $product->trial_amount;
					$trial_amount = $trial_amount * ( $rates[ $code ] * ( 1 / $rates[ $base_code ] ) );
					$trial_amount = round( $trial_amount );
				}
			}
		}
		return $trial_amount;
	}

	/**
	 * Creates ttransient from latest currencies and returns...
	 *
	 * @return object The HTTP response that comes as a result of a wp_remote_get().
	 */
	public static function get_latest_currencies( $provider ) {
		// Do we have this information in our transients already?
		$transient = json_decode( get_transient( 'mpmc_currency_transient_' . $provider ), true );
		// $transient = '';
		// Nope!  We gotta make a call.
		if ( ! is_array( $transient ) || empty( $transient ) ) {
			if ( 'openx' == $provider ) {
				$latest_json = self::get_latest_rates_openx( '', null, null, false );
			} elseif ( 'exrate' == $provider ) {
				$latest_json = self::get_latest_rates_exrate( '', null, null, false );
			}

			set_transient( 'mpmc_currency_transient_' . $provider, json_encode( $latest_json ), 24 * HOUR_IN_SECONDS );
			return $latest_json;
		} else {
			// Yep!  Just return it and we're done.
			// $transient = json_decode( $transient, true );
			return $transient;
		}
	}


	/**
	 * Creates transient from latest currencies and returns...
	 *
	 * @return object The HTTP response that comes as a result of a wp_remote_get().
	 */
	public static function fetch_options() {
		$default = array(
			'mpmc_exrate_api'        => '',
			'mpmc_openx_api'         => '',
			'mpmc_currencies'        => array(),
			'mpmc_switcher_position' => '',
			'mpmc_service_provider'  => 'manual',
		);
		$option  = get_option( 'mpmc_options', array() );
		return array_merge( $default, $option );
	}

}
