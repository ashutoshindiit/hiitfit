<?php
/**
 * Class WOOMULTI_CURRENCY_F_Plugin_Yith_Dynamic_Pricing_And_Discount
 * Author: Yith
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WOOMULTI_CURRENCY_F_Plugin_Yith_Dynamic_Pricing_And_Discount {
	public function __construct() {
		add_filter( 'ywdpd_change_dynamic_price', array( $this, 'ywdpd_change_dynamic_price' ) );
		add_filter( 'ywdpd_maybe_should_be_converted', array( $this, 'ywdpd_maybe_should_be_converted' ) );
	}

	public function ywdpd_change_dynamic_price( $price ) {
		return wmc_revert_price( $price );
	}

	public function ywdpd_maybe_should_be_converted( $price ) {
		return wmc_get_price( $price );
	}
}