<?php

/**
 * Class WOOMULTI_CURRENCY_F_Plugin_Visual_Product_Builder
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WOOMULTI_CURRENCY_F_Plugin_Visual_Product_Builder {
	protected $settings;

	public function __construct() {
		$this->settings = WOOMULTI_CURRENCY_F_Data::get_ins();
		if ( $this->settings->get_enable() ) {
			if ( class_exists( 'Vpc' ) ) {
				add_filter( 'vpc_options_price', array( $this, 'vpc_options_price' ) );
				global $wp_filter;

				if ( isset( $wp_filter['woocommerce_before_calculate_totals']->callbacks[10] ) ) {
					$hooks = $wp_filter['woocommerce_before_calculate_totals']->callbacks[10];
					foreach ( $hooks as $k => $hook ) {
						if ( strpos( $k, 'get_cart_item_price' ) === false ) {

						} else {
							if ( isset( $hook['function'][0] ) && is_object( $hook['function'][0] ) ) {
								$class_name = get_class( $hook['function'][0] );
								if ( strtoupper( 'VPC_Public' ) == strtoupper( $class_name ) ) {
									unset( $wp_filter['woocommerce_before_calculate_totals']->callbacks[10][$k] );
									break;
								}
							}
						}
					}
				}

				add_action( 'woocommerce_before_calculate_totals', array( $this, 'get_cart_item_price' ) );
			}
		}
	}

	function get_cart_item_price( $cart ) {
		if ( ! get_option( 'vpc-license-key' ) ) {
			return;
		}
		// This is necessary for WC 3.0+
		if ( is_admin() && ! defined( 'DOING_AJAX' ) )
			return;

		// Avoiding hook repetition (when using price calculations for example)
		if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 )
			return;
		global $vpc_settings;
		$hide_secondary_product_in_cart = get_proper_value( $vpc_settings, 'hide-wc-secondary-product-in-cart', 'Yes' );

		if ( is_array( $cart->cart_contents ) ) {
			foreach ( $cart->cart_contents as $cart_item_key => $cart_item ) {
				if ( $cart_item['variation_id'] ) {
					$product_id = $cart_item['variation_id'];
				} else {
					$product_id = $cart_item['product_id'];
				}

				$recap = get_recap_from_cart_item( $cart_item );
				if ( isset( $cart_item['vpc-is-secondary-product'] ) && $cart_item['vpc-is-secondary-product'] && $hide_secondary_product_in_cart == 'Yes' ) {
					if ( vpc_woocommerce_version_check() ) {
						$cart_item['data']->price = 0;
					} else {
						$cart_item['data']->set_price( 0 );
					}
				}
				$product = wc_get_product( $product_id );

				if ( vpc_woocommerce_version_check() ) {
					$price = $cart_item['data']->price;
				} else {
					$price = $cart_item['data']->get_price();
				}

				if($this->settings->get_current_currency()!==$this->settings->get_default_currency()){
					$price = wmc_revert_price($price);
				}

				if ( vpc_woocommerce_version_check() ) {
					$tax_status = $cart_item['data']->tax_status;
				} else {
					$tax_status = $cart_item['data']->get_tax_status();
				}

				$a_price = 0;
				if ( ! empty( $recap ) ) {
					$a_price = self::get_config_price( $product_id, $recap, $cart_item );
					if ( isset( $tax_status ) && $tax_status != 'taxable' ) {
						$a_price = vpc_apply_taxes_on_price_if_needed( $a_price, $cart_item['data'] );
					}
				}
				if ( class_exists( 'Ofb' ) ) {
					if ( isset( $cart_item['form_data'] ) && ! empty( $cart_item['form_data'] ) ) {
						$form_data = $cart_item['form_data'];
						if(isset($form_data['id_ofb']))
							$a_price  += get_form_data( $form_data['id_ofb'], $form_data );
					}
				}
				$total = $price + $a_price;
				if ( vpc_woocommerce_version_check() ) {
					$cart_item['data']->price = $total;
				} else {
					$cart_item['data']->set_price( $total );
				}
			}
		}
	}

	private static function get_config_price( $product_id, $config, $cart_item ) {
		if ( ! get_option( 'vpc-license-key' ) ) {
			return;
		}
		$original_config = get_product_config( $product_id );
		$total_price     = 0;
		$product         = wc_get_product( $product_id );
		if ( is_array( $config ) ) {
			foreach ( $config as $component => $raw_options ) {
				$options_arr = $raw_options;
				if ( ! is_array( $raw_options ) ) {
					$options_arr = array( $raw_options );
				}
				foreach ( $options_arr as $option ) {
					$linked_product = self::extract_option_field_from_config( $option, $component, $original_config->settings, 'product' );
					$option_price   = self::extract_option_field_from_config( $option, $component, $original_config->settings, 'price' );

					if ( strpos( $option_price, ',' ) ) {
						$option_price = floatval( str_replace( ',', '.', $option_price ) );
					}
					if ( $linked_product ) {
						$option_price = self::get_product_linked_price( $linked_product );
					}

					// We make sure we're not handling any empty priced option
					if ( empty( $option_price ) ) {
						$option_price = 0;
					}

					$total_price += $option_price;
				}
			}
		}
		return apply_filters( 'vpc_config_price', $total_price, $product_id, $config, $cart_item );
	}

	private static function get_product_linked_price( $linked_product ) {
		global $vpc_settings;
		$hide_secondary_product_in_cart = get_proper_value( $vpc_settings, 'hide-wc-secondary-product-in-cart', 'Yes' );
		if ( $hide_secondary_product_in_cart == 'Yes' ) {
			$_product = wc_get_product( $linked_product );
			if ( function_exists( 'wad_get_product_price' ) ) {
				$option_price = wad_get_product_price( $_product );
			} else {
				$option_price = $_product->get_price();
				if ( strpos( $option_price, ',' ) ) {
					$option_price = floatval( str_replace( ',', '.', $option_price ) );
				}
			}
		} else {
			$option_price = 0;
		}
		return $option_price;
	}

	public static function extract_option_field_from_config( $searched_option, $searched_component, $config, $field = "icon" ) {
		$unslashed_searched_option    = vpc_remove_special_characters( $searched_option );
		$unslashed_searched_component = vpc_remove_special_characters( $searched_component );
		$field                        = apply_filters( 'extracted_option_field_from_config', $field, $config );
		if ( ! is_array( $config ) ) {
			$config = unserialize( $config );
		}
		if ( isset( $config['components'] ) ) {
			foreach ( $config['components'] as $i => $component ) {
				if ( vpc_remove_special_characters($component['cname'],'"' ) == $unslashed_searched_component ) {
					foreach ( $component['options'] as $component_option ) {
						if ( vpc_remove_special_characters($component_option['name'],'"')  == $unslashed_searched_option ) {
							if ( isset( $component_option[ $field ] ) ) {
								return $component_option[ $field ];
							}
						}
					}
				}
			}
		}
		return false;
	}

	/**
	 * Change price in Build page
	 *
	 * @param $data
	 *
	 * @return mixed
	 */

	public function vpc_options_price( $data ) {
		return wmc_get_price( $data );
	}

}