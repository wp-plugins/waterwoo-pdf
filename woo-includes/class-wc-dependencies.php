<?php
/**
 * WC Dependency Checker
 *
 * Checks if WooCommerce is enabled, and version
 *
 */
class WC_Dependencies {

	private static $active_plugins;

	public function init() {

		self::$active_plugins = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() )
			self::$active_plugins = array_merge( self::$active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
	}

	public function woocommerce_active_check() {

		if ( ! self::$active_plugins ) self::init();

		return in_array( 'woocommerce/woocommerce.php', self::$active_plugins ) || array_key_exists( 'woocommerce/woocommerce.php', self::$active_plugins );

	}
	
	/**
	* Get current Woo version
	*/
	public function get_woo_version_number() {
       	 // If get_plugins() isn't available, require it
		if ( ! function_exists( 'get_plugins' ) ) require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	
       	// Create the plugins folder and file variables
		$plugin_folder = get_plugins( '/' . 'woocommerce' );
		$plugin_file = 'woocommerce.php';
	
		// If the plugin version number is set, return it 
		if ( isset( $plugin_folder[$plugin_file]['Version'] ) ) {
			return $plugin_folder[$plugin_file]['Version'];
		} else {
			return NULL;
		}
	}

}


