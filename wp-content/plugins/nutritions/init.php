<?php
/*
Plugin Name: Nutritions
Plugin URI: http://www.example.com/adminpagearranger
Description: A plugin to adminster admin pages
Version: 0.1
Author: Tim Smith
Author URI: http://www.example.com/
License: GPL2
*/

add_action("admin_menu", "nutrition");

function nutrition() {
    add_menu_page("Nutrition", "Nutrition", "edit_posts",
        "nutrition", "displayPage", null, 2);
}