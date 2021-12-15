<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings for MyFatoorah Gateway.
 */
return array(
	'enabled' => array(
		'title'   => __( 'Enable/Disable', 'woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable MyFatoorah', 'woocommerce' ),
		'default' => 'yes'
	),
	'title' => array(
		'title'       => __( 'Title', 'woocommerce' ),
		'type'        => 'text',
		'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
		'default'     => __( 'MyFatoorah', 'woocommerce' ),
		'desc_tip'    => true,
	),
	'desc' => array(
		'title'       => __( 'Description', 'woocommerce' ),
		'type'        => 'textarea',
		'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
		'default'     => __( 'Checkout with MyFatoorah payment gateway', 'woocommerce' ),
		'desc_tip'    => true,
	),
	'logo_url' => array(
		'title'       => __( 'MyFatoorah Logo URL', 'woocommerce' ),
		'type'        => 'text',
		'description' => __( 'Please insert your logo url which the user sees during checkout.', 'woocommerce' ),
		'default'     =>  __( '', 'woocommerce' ),
		'desc_tip'    => true,
		'placeholder'=>'https://www.exampleurl.com',
	),
	'debug' => array(
		'title'       => __( 'Debug Log', 'woocommerce' ),
		'type'        => 'checkbox',
		'label'       => __( 'Enable logging', 'woocommerce' ),
		'default'     => 'no',
		'description' => sprintf( __( 'Log MyFatoorah events, ', 'woocommerce' ), wc_get_log_file_path( 'myfatoorah' ) )
	),
	'api_token' => array(
		'title'       => __( 'API Key', 'woocommerce' ),
		'type'        => 'textarea',
		'description' => __( 'Get your API KEY from MyFatoorah.', 'woocommerce' ),
		'desc_tip'    => true
		
	),
		'testmode' => array(
		'title'       => __( 'Test Mode', 'woocommerce' ),
		'type'        => 'checkbox',
		'label'       => __( 'Enable / Disable Test Mode', 'woocommerce' ),
		'default'     => 'Yes',
		'description' => sprintf( __( 'Enable test / sandbox Mode, ', 'woocommerce' ))
	),
	'api_gateway_payment' => array(
		'title'       => __( 'Checkout Payment Gateway', 'woocommerce' ),
		'type'        => 'multiselect',
		'description' => __( 'MyFatoorah is default gateway. You can select one of below payment gateway  which the user can checkout. ', 'woocommerce' ),
		'options' => $this->paymentGateways,
		'default'     => 'mf',
		'desc_tip'    => true
		
	),'orderstatus' => array(
        'title' => __('Order Status', 'woocommerce'),
        'type' => 'select',
        'options' => array(
            'processing' => 'Processing',
            'completed' => 'Completed',
        ),
        'default' => 'processing',
        'description' => __('How to mark the successful payment in the Admin Orders Page.', 'woocommerce-gateway-myfatoorah'),
    ),
	'success_url' => array(
		'title'       => __( 'Payment Success URL', 'woocommerce' ),
		'type'        => 'text',
		'description' => __( 'Please insert your Success url.', 'woocommerce' ),
		'default'     =>  __( '', 'woocommerce' ),
		'desc_tip'    => true,
		'placeholder'=>'https://www.exampleurl.com/success',
	),
	'fail_url' => array(
		'title'       => __( 'Payment Fail URL', 'woocommerce' ),
		'type'        => 'text',
		'description' => __( 'Please insert your Fail url.', 'woocommerce' ),
		'default'     =>  __( '', 'woocommerce' ),
		'desc_tip'    => true,
		'placeholder'=>'https://www.exampleurl.com/failed',
	)
);


