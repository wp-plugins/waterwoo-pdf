<?php
/**
 * Functions used by plugins
 */
if ( ! class_exists( 'WC_Dependencies' ) )
	require_once 'class-wc-dependencies.php';

/**
 * WC Detection
 */
if ( ! function_exists( 'is_woocommerce_active' ) ) {
	function is_woocommerce_active() {
		$dependency = new WC_Dependencies();
		return $dependency->woocommerce_active_check();
	}
}

/**
 * WC Version
 */
if ( ! function_exists( 'get_woocommerce_version' ) ) {
	function get_woocommerce_version() {
		if ($dependency) {
			return $dependency->get_woo_version_number();
		} else {
			$woo_version = new WC_Dependencies();
			return $woo_version->get_woo_version_number();
		}
	}
}