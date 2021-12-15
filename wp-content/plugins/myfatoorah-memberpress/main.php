<?php

/**
 * Plugin Name: MemberPress myFatoorah
 * Plugin URI: https://www.facebook.com/xplomate
 * Description: myFatoorah Payment Gateway integration for MemberPress.
 * Version: 1.2.0
 * Author: Xplomate Developers
 * Author URI: https://www.facebook.com/xplomate
 * Text Domain: myfatoorah-memberpress
 * License: GPLv2 or later
 * Copyright: 2021, Xplomate.
 */

if (!defined('ABSPATH')) {
    die('You are not allowed to call this page directly.');
}

include_once(ABSPATH . 'wp-admin/includes/plugin.php');

if (is_plugin_active('memberpress/memberpress.php')) {
    define('MP_MYFATOORAH_PLUGIN_SLUG', 'myfatoorah-memberpress/main.php');
    define('MP_MYFATOORAH_PLUGIN_NAME', 'myfatoorah-memberpress');
    define('MP_MYFATOORAH_EDITION', MP_MYFATOORAH_PLUGIN_NAME);
    define('MP_MYFATOORAH_PATH', WP_PLUGIN_DIR . '/' . MP_MYFATOORAH_PLUGIN_NAME);

    $mp_myfatoorah_url_protocol = (is_ssl()) ? 'https' : 'http'; // Make all of our URLS protocol agnostic
    define('MP_MYFATOORAH_URL', preg_replace('/^https?:/', "{$mp_myfatoorah_url_protocol}:", plugins_url('/' . MP_MYFATOORAH_PLUGIN_NAME)));
    define('MP_MYFATOORAH_JS_URL', MP_MYFATOORAH_URL . '/js');
    define('MP_MYFATOORAH_IMAGES_URL', MP_MYFATOORAH_URL . '/images');

    // Load Memberpress Base Gateway
    require_once(MP_MYFATOORAH_PATH . '/../memberpress/app/lib/MeprBaseGateway.php');
    require_once(MP_MYFATOORAH_PATH . '/../memberpress/app/lib/MeprBaseRealGateway.php');

    // Load Memberpress MpMyFatoorah API
    require_once(MP_MYFATOORAH_PATH . '/MeprMyFatoorahAPI.php');

    // Load Memberpress MpMyFatoorah Addon
    require_once(MP_MYFATOORAH_PATH . '/MpMyFatoorah.php');

    new MpMyFatoorah;
}
