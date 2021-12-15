<?php
if(bridge_core_is_installed('qode-tours') || bridge_core_is_installed('qode-listing') || bridge_core_is_installed('qode-lms')) {
	include_once BRIDGE_CORE_MODULES_PATH . '/reviews/reviews-functions.php';
}