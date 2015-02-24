<?php
/**
 * Functions used by plugins
 */
if ( ! class_exists( 'WC_Dependencies' ) )
	require_once 'class-wc-dependencies.php';

/**
 * WC Detection
 */
function is_woocommerce_active() {
	return WC_Dependencies::woocommerce_active_check();

}

/**
 * WC Version
 */
function get_woocommerce_version() {
	
	return WC_Dependencies::get_woo_version_number();
	
}