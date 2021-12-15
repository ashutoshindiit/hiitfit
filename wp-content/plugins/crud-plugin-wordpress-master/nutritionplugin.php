<?php

/*
  Plugin Name: nutrition plugin
  Description: Plugin for testing purpose
  Version: 1
  Author: Naveen 
  Author URI: http://sahilgulati.com
 */

global $jal_db_version;
$jal_db_version = '1.0';

function jal_install() {
    global $wpdb;
    global $jal_db_version;

    $table_name = $wpdb->prefix . '';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		name tinytext NOT NULL,
		address text NOT NULL,
		role text NOT NULL,
		contact bigint(12), 
		PRIMARY KEY  (id)
	) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    add_option( 'jal_db_version', $jal_db_version );
}
register_activation_hook( __FILE__, 'jal_install' );
//adding in menu
add_action('admin_menu', 'nutrition');

function nutrition() {
    //adding plugin in menu
    add_menu_page('nutrition_list', //page title
        'Nutrition Listing', //menu title
        'manage_options', //capabilities
        'Nutrition_Listing', //menu slug
        nutrition_list //function
    );
    //adding submenu to a menu
    add_submenu_page('Nutrition_Listing',//parent page slug
        'nutrition_insert',//page title
        'Nutrition Insert',//menu titel
        'manage_options',//manage optios
        'Nutrition_Insert',//slug
        'nutrition_insert'//function
    );
    add_submenu_page( null,//parent page slug
        'nutrition_update',//$page_title
        'Nutrition Update',// $menu_title
        'manage_options',// $capability
        'Nutrition_Update',// $menu_slug,
        'nutrition_update'// $function
    );
    add_submenu_page( null,//parent page slug
        'nutrition_delete',//$page_title
        'Nutrition Delete',// $menu_title
        'manage_options',// $capability
        'Nutrition_Delete',// $menu_slug,
        'nutrition_delete'// $function
    );
}


// returns the root directory path of particular plugin
define('ROOTDIR', plugin_dir_path(__FILE__));
require_once(ROOTDIR . 'nutrition_list.php');
require_once (ROOTDIR.'nutrition_insert.php');
require_once (ROOTDIR.'nutrition_update.php');
require_once (ROOTDIR.'nutrition_delete.php');
?>