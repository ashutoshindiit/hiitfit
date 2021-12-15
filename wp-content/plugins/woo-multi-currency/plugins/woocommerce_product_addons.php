<?php

/**
 * Class WOOMULTI_CURRENCY_F_Plugin_Woocommerce_Product_Addons
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WOOMULTI_CURRENCY_F_Plugin_Woocommerce_Product_Addons {
	protected $settings;

	public function __construct() {
		if ( is_plugin_active( 'woocommerce-product-addons/woocommerce-product-addons.php' ) ) {
			add_action( 'init', array(
				$this,
				'remove_default_addons_price_in_cart'
			) );
			add_filter( 'woocommerce_product_addons_price_raw', array(
				$this,
				'woocommerce_product_addons_price_raw'
			), 10, 2 );
			add_filter( 'woocommerce_get_item_data', array( $this, 'woocommerce_get_item_data' ), 11, 2 );
			add_filter( 'woocommerce_product_addon_cart_item_data', array(
				$this,
				'woocommerce_product_addon_cart_item_data'
			), 10, 4 );
		}
	}

	public function woocommerce_product_addon_cart_item_data( $data, $addon, $product_id, $post_data ) {
		if ( count( $data ) ) {
			foreach ( $data as $key => $value ) {
				if ( isset( $value['field_type'] ) && $value['field_type'] === 'custom_price' ) {
					$data[ $key ]['price'] = wmc_revert_price( $value['price'] );
					$data[ $key ]['value'] = $data[ $key ]['price'];
				}
			}
		}

		return $data;
	}

	public function remove_default_addons_price_in_cart() {
		if ( isset( $GLOBALS['Product_Addon_Cart'] ) ) {
			remove_filter( 'woocommerce_get_item_data', array( $GLOBALS['Product_Addon_Cart'], 'get_item_data' ) );
		}
	}

	public function woocommerce_product_addons_price_raw( $addon_price, $addon ) {
		return wmc_get_price( $addon_price );
	}

	public function woocommerce_get_item_data( $other_data, $cart_item ) {
		if ( ! empty( $cart_item['addons'] ) ) {
			foreach ( $cart_item['addons'] as $addon ) {
				$price = isset( $cart_item['addons_price_before_calc'] ) ? $cart_item['addons_price_before_calc'] : $addon['price'];
				$name  = $addon['name'];

				if ( 0 == $addon['price'] ) {
					$name .= '';
				} elseif ( 'percentage_based' === $addon['price_type'] && 0 == $price ) {
					$name .= '';
				} elseif ( 'percentage_based' !== $addon['price_type'] && $addon['price'] && apply_filters( 'woocommerce_addons_add_price_to_name', '__return_true' ) ) {

					$name .= ' (' . wc_price( wmc_get_price( WC_Product_Addons_Helper::get_product_addon_price_for_display( $addon['price'], $cart_item['data'], true ) ) ) . ')';

				} else {
					$_product = wc_get_product( $cart_item['product_id'] );
					$_product->set_price( $price * ( $addon['price'] / 100 ) );
					$name .= ' (' . WC()->cart->get_product_price( $_product ) . ')';
				}

				$other_data[] = array(
					'name'    => $name,
					'value'   => $addon['value'],
					'display' => $addon['field_type'] === 'custom_price' ? wc_price( wmc_get_price( $addon['price'] ) ) : ( isset( $addon['display'] ) ? $addon['display'] : '' ),
				);
			}
		}

		return $other_data;
	}
}
