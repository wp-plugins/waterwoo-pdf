<?php
/*
 * Plugin Name: WaterWoo PDF
 * Plugin URI: http://cap.little-package.com/waterwoo-pdf
 * Description: Custom watermark your PDFs upon WooCommerce sale. Works with WooCommerce version <2.3 - see settings page for more information.
 * Version: 1.0.5
 * Author: Caroline Paquette 
 * Author URI: http://cap.little-package.com/waterwoo-pdf
 * Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=PB2CFX8H4V49L
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * 
 * Text Domain: waterwoo-pdf
 * Domain path: /lang
 *
 * Copyright 2013-2014 Caroline Paquette 
 *		
 *     This file is part of WaterWoo PDF, a plugin for WordPress. If
 * 	   it benefits you, please consider donating and/or leaving a review at
 * 	   Wordpress. Thank you.
 *
 *     WaterWoo PDF is free software: You can redistribute
 *     it and/or modify it under the terms of the GNU General Public
 *     License as published by the Free Software Foundation, either
 *     version 3 of the License, or (at your option) any later version.
 *     
 *     WaterWoo PDF is distributed in the hope that it will
 *     be useful, but WITHOUT ANY WARRANTY; without even the
 *     implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
 *     PURPOSE. See the GNU General Public License for more details.
 *
 *     You should have received a copy of the GNU General Public License
 *     along with WordPress. If not, see <http://www.gnu.org/licenses/>.
 *
 */

if ( ! defined('ABSPATH') ) { exit; }
if ( ! class_exists( 'WaterWooPDF' ) ) :

class WaterWooPDF {

	/**
     * @var string
     */
    public $version = '1.0.5';


    /**
     * Init
     */
    public static function init() {

        $wwpdf = new self();

    }
    	

	/**
	 * Is Woocommerce activated?
	 */
	public function is_woocommerce_activated() {
		if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			return true;
		} else {
			return false;
		}	
	}


	/**
	 * Constructor
	 */
	public function __construct() {

		if ( $this->is_woocommerce_activated() ) {
	
			$this->tab_name = 'waterwoo-pdf';			
			$this->define_constants();
			$this->includes();
			$this->load_wwpdf_hooks();	


		}
		
	}

	
	/**
	 * Define constants
	 */
	private function define_constants() {

		define( 'WWPDF_BASE_URL', trailingslashit( plugins_url( 'waterwoo-pdf' ) ) );
		define( 'WWPDF_PATH', plugin_dir_path( __FILE__ ) );
		define( 'WWPDF_VERSION', $this->version );

	}
		

	/**
     * All classes
     */
    private function plugin_classes() {

        return array(
            'wwpdfwatermark'		=> WWPDF_PATH . 'inc/class_wwpdf_watermark.php',
	        'wwpdfdownloadhandler'	=> WWPDF_PATH . 'inc/class_wwpdf_download_product.php'
		);

	}


    /**
     * Load required classes
     */
    private function includes() {

        $autoload_is_disabled = defined( 'WWPDF_AUTOLOAD_CLASSES' ) && WWPDF_AUTOLOAD_CLASSES === false;

        if ( function_exists( "spl_autoload_register" ) && ! ( $autoload_is_disabled ) ) {

           // >= PHP 5.2 - Use auto loading

            if ( function_exists( "__autoload" ) ) {

                spl_autoload_register( "__autoload" ); 
            }

            spl_autoload_register( array( $this, 'autoload' ) );

        } else {

            // < PHP5.2 - Require all classes

            foreach ( $this->plugin_classes() as $id => $path ) {

                if ( is_readable( $path ) && ! class_exists( $id ) ) {

                    require_once( $path );

                }

            }

        }

    }


    /**
     * Autoload classes to reduce memory consumption
     */

    public function autoload( $class ) {

        $classes = $this->plugin_classes();

        $class_name = strtolower( $class );

        if ( isset( $classes[$class_name] ) && is_readable( $classes[$class_name] ) ) {

 			require_once( $classes[$class_name] );

        }

    }


	/**
	 * Remove woocommerce_download_product action hook and replace
	 */
	public function load_wwpdf_hooks() {

		add_action( 'admin_init', array( $this, 'load_admin_hooks' ) );
		add_action( 'admin_init', array( $this, 'nag_ignore' ) );
		add_action( 'admin_notices', array( $this, 'admin_notice' ) );

		if ( class_exists( 'WC_Download_Handler' ) ) {

			remove_action('init', array( 'WC_Download_Handler', 'download_product'));
			add_action('init', array( 'WWPDFDownloadHandler', 'wwpdf_download_product'));

		}

		else { 

			e_( 'Your WooCommerce installation may be incomplete, altered, or damaged. Check it out and try again.', 'waterwoo-pdf' );

		}

	}


	/**
	* Load the localization 
	*/
	public function load_localization() {

		load_plugin_textdomain( 'waterwoo-pdf', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );	

	}

		
	/**
	* Load the admin hooks
	*/
	public function load_admin_hooks() {

		if ( is_admin() ) {

			$this->load_localization();

			add_filter( 'plugin_row_meta', array( $this, 'add_support_links' ), 10, 2 );			
			add_filter( 'plugin_action_links_' . WWPDF_BASE_URL, array( $this, 'add_settings_link') );
			add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_tab' ), 101  );
			add_action( 'woocommerce_settings_tabs_' . $this->tab_name, array( $this, 'create_settings_page' ) ); 
			add_action( 'woocommerce_update_options_' . $this->tab_name, array( $this, 'save_settings_page' ) );
			add_action( 'current_screen', array( $this, 'load_screen_hooks' ) );

			$plugin = plugin_basename( __FILE__ );

        	add_filter( "plugin_action_links_{$plugin}", array( $this, 'upgrade_to_premium_link' ) );

		}
	}	

	/**
	* Add various support links to plugin page
	*/
	public function add_support_links( $links, $file ) {

		if ( !current_user_can( 'install_plugins' ) ) {

			return $links;

		}
		
		if ( $file == WWPDF_BASE_URL ) {
				$links[] = '<a href="http://wordpress.org/extend/plugins/waterwoo-pdf/faq/" target="_blank" title="' . __( 'FAQ', 'waterwoo-pdf' ) . '">' . __( 'FAQ', 'waterwoo-pdf' ) . '</a>';
				$links[] = '<a href="http://wordpress.org/support/plugin/waterwoo-pdf" target="_blank" title="' . __( 'Support', 'waterwoo-pdf' ) . '">' . __( 'Support', 'waterwoo-pdf' ) . '</a>';
			}

			return $links;

		}
		

	/**
	* Add settings link to plugin page
	*/
	public function add_settings_link( $links ) {

		$settings = sprintf( '<a href="%s" title="%s">%s</a>' , admin_url( 'admin.php?page=woocommerce&tab=' . $this->tab_name ) , __( 'Go to the settings page', 'waterwoo-pdf' ) , __( 'Settings', 'waterwoo-pdf' ) );
		array_unshift( $links, $settings );
		return $links;

	}


	/**
	* Check if we are on settings page
	*/
	public function is_settings_page() {

		if( isset( $_GET['page'] ) && isset( $_GET['tab'] ) && $_GET['tab'] == $this->tab_name ) {

			return true;

		} else {

			return false;

		}

	}


	/**
	 * Load screen hooks
	 */
	public function load_screen_hooks() {

		$screen = get_current_screen();

		if( $this->is_settings_page() ) {

			add_action( 'admin_print_scripts', array( $this, 'add_scripts' ) );
			add_action( 'load-' . $screen->id, array( $this, 'add_help_tabs' ) );

		}
		
	}


	/**
	 * Add the scripts	
	 */
	public function add_scripts() {

		wp_enqueue_script( 'wwpdf-scripts', WWPDF_BASE_URL . '/assets/js/wwpdf_admin.js', 'jquery', WWPDF_VERSION );

	}


	/**
	 * Add the help tabs	
	 */
	public function add_help_tabs() {

		// Check current admin screen
		$screen = get_current_screen();

		// Remove all existing tabs
		$screen->remove_help_tabs();
			
		// Create arrays with help tab titles
		$screen->add_help_tab(array(
			'id' => 'waterwoo-pdf-usage',
			'title' => __( 'About the Plugin', 'waterwoo-pdf' ),
			'content' => 
				'<h3>' . __( 'About WaterWoo PDF', 'waterwoo-pdf' ) . '</h3>' .
				'<p>' . __( 'Protect your intellectual property! WaterWoo PDF allows WooCommerce site administrators to apply custom watermarks to PDFs upon sale.' ). '</p>' . 
				'<p>' . __( 'WaterWoo PDF is a plugin that adds a watermark to every page of your PDF file(s). The watermark is customizable with font face, font color, font size, placement, and text. Not only that, but since the watermark is added when the download button is clicked (either on the customer\'s order confirmation page or email), the watermark can include customer-specifc data such as the customer\'s first name, last name, and email. Your watermark is highly customizable and manipulatable.', 'waterwoo-pdf' ) . '</p>' .
'<p>' . __( 'Consider upgrading to the Premium version if you need more functionality. The premium version adds the ability to start watermarking on a specified page, password protect and copy/print/modify protect your document, a watermark overlay, HTML input, and UTF-8 support for most foreign language characters. Requests for additional features are welcome, as are your requests for support. Thanks again, and enjoy!', 'waterwoo-pdf' ) . '</p>'

			) );

		// Create help sidebar
		$screen->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'waterwoo-pdf' ) . '</strong></p>'.
			'<p><a href="http://wordpress.org/extend/plugins/waterwoo-pdf/faq/" target="_blank">' . __( 'More Frequently Asked Questions', 'waterwoo-pdf' ) . '</a></p>' .
			'<p><a href="http://wordpress.org/extend/plugins/waterwoo-pdf/" target="_blank">' . __( 'Project on WordPress.org', 'waterwoo-pdf' ) . '</a></p>'
		);

	}


	/**
     * Add settings link on plugin page
     */

    public function upgrade_to_premium_link( $links ) {

        if ( function_exists( 'is_plugin_active' ) && ! is_plugin_active( 'waterwoo-pdf-premium/waterwoo-pdf-premium.php' ) ) {

            $links[] = '<a href="http://cap.little-package.com/shop/waterwoo-pdf-premium" target="_blank">' . __( 'Upgrade to Premium', 'waterwoo-pdf' ) . '</a>';

        }
        
        return $links;

    }


    /**
     * Upgrade CTA
     */

    public function get_premium_cta() {

        if ( function_exists( 'is_plugin_active' ) && ! is_plugin_active( 'waterwoo-pdf-premium/waterwoo-pdf-premium.php' ) ) {

            $link = 'http://cap.little-package.com/shop/waterwoo-pdf-premium';

            $getPremium = "<div style='display: none;' id='wwpdf-screen-options-link-wrap'><div id='contextual-help-link-wrap' class='hide-if-no-js screen-meta-toggle'><a target='_blank' class='show-settings' href='{$link}'>WaterWoo PDF v" . WWPDF_VERSION . " - " .

                __( 'Upgrade to Premium - $45', 'waterwoo-pdf' ) .

                "</a></div></div>";

            echo $getPremium;

        }

    }


	/**
	 * Add a tab to the settings page	
	 */
	public function add_settings_tab($tabs) {

		$tabs[$this->tab_name] = __( 'Watermark', 'waterwoo-pdf' );
		return $tabs;

	}


	/**
	 * Display a notice that can be dismissed
	 */ 
	public function admin_notice() {
		global $current_user, $pagenow;
        $user_id = $current_user->ID;
        /* Check that the user hasn't already clicked to ignore the message */
		if ( ! get_user_meta($user_id, 'wwpdf_ignore_notice') ) {
		
			$currentscreen = get_current_screen();
			if ( $pagenow == 'admin.php' && $currentscreen->id == 'woocommerce_page_wc-settings' ) {
        		echo '<div class="updated"><p>'; 
        		printf(__('<strong>Attention!</strong> The free WaterWoo plugin will break with the upcoming <a href="http://develop.woothemes.com/woocommerce/tag/woocommerce-2-3/" target="_blank" title="Woocommerce 2.3">WooCommerce 2.3</a> major update.<br />I will make sure the WaterWoo Premium version continues to work. Free version users will have to hang in there with Woo versions <2.3 until I have time to fix it -- <a href="http://cap.little-package.com/shop/pdf-watermark-plugin-waterwoo" title="WaterWood watermark PDF plugin" target="_blank">or you can upgrade</a>.<br />I apologize for the inconvenience. If this plugin has been useful to you, please consider <a href="http://cap.little-package.com/shop/pdf-watermark-plugin-waterwoo" title="WaterWood watermark PDF plugin" target="_blank">upgrading</a> or <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=PB2CFX8H4V49L" title="Little Package PayPal" target="_blank">donating</a>. <strong>Thank you</strong> for using WaterWoo!<br /><a href="%1$s">Hide This Notice</a>'), '?page=wc-settings&tab=waterwoo-pdf&nag_ignore=0');
        		echo "</p></div>";
			}

		}

	}


	/**
	 * Display a notice that can be dismissed
	 */ 
	public function nag_ignore() {
		global $current_user;
        $user_id = $current_user->ID;
        /* If user clicks to ignore the notice, add that to their user meta */
        if ( isset($_GET['nag_ignore']) && '0' == $_GET['nag_ignore'] ) {
             add_user_meta($user_id, 'wwpdf_ignore_notice', 'true', true);
		}
	}


	/**
	 * Clean old pre-1.0.5 settings from options table
	 */
	private function clean_options_table() {

		delete_site_option( 'enable_wwpdf' );
		delete_site_option( 'pdf_files' );
		delete_site_option( 'footer_input' );
		delete_site_option( 'footer_size' );
		delete_site_option( 'footer_color' );
		delete_site_option( 'footer_finetune_Y' );
		delete_site_option( 'wwpdf_settings' );

	}


	/**
	 * Settings array
	 */
	private function settings_array() {

		$wwpdf_enable_default = get_site_option( 'enable_wwpdf', 'no' );
		$wwpdf_files_default = get_site_option( 'pdf_files', sprintf( '', PHP_EOL ) );
		$wwpdf_footer_input_default = get_site_option( 'footer_input', 'no' );
		$wwpdf_footer_size_default = get_site_option( 'footer_size', '12' );
		$wwpdf_footer_color_default = get_site_option( 'footer_color', '#000000' );
		$wwpdf_footer_y_default = get_site_option( 'footer_finetune_y', '270' );
	
		return array(

			array(
				'id' 		=> 'wwpdf_general_options',
				'type' 		=> 'title',
				'title' 	=> __( 'WaterWoo General Options', 'waterwoo-pdf' ),
				'desc' 		=> '',
			),
	
			array(	
				'id' 		=> 'wwpdf_enable',
				'type' 		=> 'checkbox', 
				'title' 	=> __( 'Enable Watermarking', 'water_woo_pdf' ), 
				'desc' 	=> __( 'Check to enable PDF watermarking', 'waterwoo-pdf' ), 
				'default' 	=> '$wwpdf_enable_default',
			),

			array(
				'id' 		=> 'wwpdf_files',
				'type' 		=> 'textarea', 
				'title' 	=> __( 'File(s) to watermark', 'waterwoo-pdf' ), 
				'desc' 		=> __( 'List file name(s) of PDF(s) to watermark, one per line, e.g., <code>upload.pdf</code> or <code>my_pdf.pdf</code> .<br />If left blank, WaterWoo PDF will watermark all PDFs sold through WooCommerce.', 'waterwoo-pdf' ), 
				'default' 	=>  $wwpdf_files_default,
				'css' 		=> 'min-width:600px;',
			),

			array(
				'id' 		=> 'wwpdf_footer_input',
				'type'		=> 'textarea',
				'title' 	=> __( 'Custom text for footer watermark', 'waterwoo-pdf' ),
				'desc' 		=> __( 'Shortcodes available, all caps, in brackets: <code>[FIRSTNAME]</code> <code>[LASTNAME]</code> <code>[EMAIL]</code>', 'waterwoo-pdf' ),
				'default' 	=> '$wwpdf_footer_input_default',
				'css' 		=> 'min-width:600px;',
			),
	
			array(	
				'id' 		=> 'wwpdf_font',
				'type' 		=> 'select',
				'title' 	=> __( 'Font face', 'waterwoo_pdf' ),
				'desc' 	=> __( 'Select a font for watermarks.', 'waterwoo-pdf' ),
				'css' 		=> 'min-width:300px;',
				'class'		=> 'chosen_select',
				'default'	=> 'helvetica',
				'options' => array(
						'helvetica'  => 'Helvetica',
						'times'   => 'Times New Roman',				
						'courier' => 'Courier',
						),
				'desc_tip' 	=> true,

			),

			array(
				'id' 		=> 'wwpdf_footer_size',
				'type' 		=> 'number',
				'title' 	=> __( 'Font size', 'waterwoo-pdf' ),
				'css' 		=> 'width:50px;',
				'desc' 		=> __( 'Provide a number (suggested 10-20) for the footer watermark font size', 'waterwoo-pdf' ),
				'default' 	=> '$wwpdf_footer_size_default',
				'custom_attributes' => array(
								'min' 	=> 8,
								'max' => 22,
								'step' 	=> 1,
							),
				'desc_tip' 	=> true,
			),

			array(
				'id' 		=> 'wwpdf_footer_color',
				'type' 		=> 'color',
				'title' 	=> __( 'Watermark color', 'waterwoo-pdf' ),
				'desc' 		=> __( 'Color of the footer watermark. Default is black: <code>#000000</code>.', 'waterwoo-pdf' ),
				'css' 		=> 'width:6em;',
				'default'	=> '$wwpdf_footer_color_default',
				'desc_tip' 	=> true,
			),

	
			array(		
				'id' 		=> 'wwpdf_footer_y',
				'type' 		=> 'number',
				'css' 		=> 'width:50px;',
				'title' 	=> __( 'Y Fine Tuning', 'waterwoo-pdf' ),
				'desc' 	=> __( 'Move the footer watermark up and down on the page by adjusting this number. Default is 270 (bottom of page).', 'waterwoo-pdf' ),
				'default' 	=> $wwpdf_footer_y_default,
				'custom_attributes' => array(
								'min' 	=> 0,
								'max' => 1000,
								'step' 	=> 1,
							),
				'desc_tip' 	=> true,
			),

			array( 'id' => 'wwpdf_general_options', 'type' => 'sectionend' ),

		);

	}


	/**
	 * Include and display the settings page.
	 */
	public function create_settings_page() {

		$this->get_premium_cta();
		do_action( 'wwpdf_admin_notices' );
		$waterwoopdf_settings = $this->settings_array();
		woocommerce_admin_fields( $waterwoopdf_settings );		

	}


	/**
	 * Save the settings page.
	 */
	public function save_settings_page() {

		$waterwoopdf_settings = $this->settings_array();		

		if ( is_admin() ) {

			if ( isset( $waterwoopdf_settings ) ) {
				woocommerce_update_options( $waterwoopdf_settings );
			}

			if ( version_compare( WWPDF_VERSION, '1.0.5', '>=') ) {
				// Test if a old value is in database
				$wwpdf_check = get_site_option( 'enable_wwpdf', '', false );

				if ( $wwpdf_check != false ) {
					$this->clean_options_table();
				}
			}

		}

	}

}

endif;

add_action( 'plugins_loaded', array( 'WaterWooPDF', 'init' ), 10 );

?>