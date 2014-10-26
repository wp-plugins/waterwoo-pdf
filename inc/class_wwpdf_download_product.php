<?php
class WWPDFDownloadHandler {

		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'wwpdf_download_product' ) );
		}

	/**
	 * Product download, replaces download_product()
	 */
	public function wwpdf_download_product() {

		if ( isset( $_GET['download_file'] ) && isset( $_GET['order'] ) && isset( $_GET['email'] ) ) {

			global $wpdb;

			$product_id           = (int) $_GET['download_file'];
			$order_key			  = $_GET['order'];
			$email				  = sanitize_email( str_replace( ' ', '+', $_GET['email'] ) );
			$download_id		  = isset( $_GET['key'] ) ? preg_replace( '/\s+/', ' ', $_GET['key'] ) : '';
			$_product			  = get_product( $product_id );

			if ( ! is_email( $email) ) {
				wp_die( __( 'Invalid email address.', 'woocommerce' ) . ' <a href="' . esc_url( home_url() ) . '" class="wc-forward">' . __( 'Go to homepage', 'woocommerce' ) . '</a>', '', array( 'response' => 403 ) );
			}

			$query = "
				  SELECT order_id,downloads_remaining,user_id,download_count,access_expires,download_id
				  FROM " . $wpdb->prefix . "woocommerce_downloadable_product_permissions
				  WHERE user_email = %s
				  AND order_key = %s
				  AND product_id = %s";

			$args = array(
				  $email,
				  $order_key,
				  $product_id
			);

			if ( $download_id ) {
				  // backwards compatibility for existing download URLs
				  $query .= " AND download_id = %s";
				  $args[] = $download_id;
			}

			$download_result = $wpdb->get_row( $wpdb->prepare( $query, $args ) );

			if ( ! $download_result ) {
				wp_die( __( 'Invalid download.', 'woocommerce' ) . ' <a href="' . esc_url( home_url() ) . '" class="wc-forward">' . __( 'Go to homepage', 'woocommerce' ) . '</a>', '', array( 'response' => 404 ) );
			}

			$download_id      		= $download_result->download_id;
			$order_id         		= $download_result->order_id;
			$downloads_remaining  	= $download_result->downloads_remaining;
			$download_count     	= $download_result->download_count;
			$user_id        		= $download_result->user_id;
			$access_expires     	= $download_result->access_expires;

			if ( $user_id && get_option( 'woocommerce_downloads_require_login' ) == 'yes' ) {

				if ( ! is_user_logged_in() ) {
					wp_die( __( 'You must be logged in to download files.', 'woocommerce' ) . ' <a href="' . esc_url( wp_login_url( get_permalink( wc_get_page_id( 'myaccount' ) ) ) ) . '" class="wc-forward">' . __( 'Login', 'woocommerce' ) . '</a>', __( 'Log in to Download Files', 'woocommerce' ), '', array( 'response' => 403 ) );
				} elseif ( ! current_user_can( 'download_file', $download_result ) ) {
					wp_die( __( 'This is not your download link.', 'woocommerce' ), '', array( 'response' => 403 ) );
				}

			}

			if ( ! get_post( $product_id ) ) {
				wp_die( __( 'Product no longer exists.', 'woocommerce' ) . ' <a href="' . esc_url( home_url() ) . '" class="wc-forward">' . __( 'Go to homepage', 'woocommerce' ) . '</a>', '', array( 'response' => 404 ) );
			}

			if ( $order_id ) {
				$order = wc_get_order( $order_id );

				if ( ! $order->is_download_permitted() ) {
					wp_die( __( 'Invalid order.', 'woocommerce' ) . ' <a href="' . esc_url( home_url() ) . '" class="wc-forward">' . __( 'Go to homepage', 'woocommerce' ) . '</a>', '', array( 'response' => 404 ) );
				}
			}

			if ( $downloads_remaining == '0' ) {
				wp_die( __( 'Sorry, you have reached your download limit for this file', 'woocommerce' ) . ' <a href="' . esc_url( home_url() ) . '" class="wc-forward">' . __( 'Go to homepage', 'woocommerce' ) . '</a>', '', array( 'response' => 403 ) );
			}

			if ( $access_expires > 0 && strtotime( $access_expires) < current_time( 'timestamp' ) ) {
				wp_die( __( 'Sorry, this download has expired', 'woocommerce' ) . ' <a href="' . esc_url( home_url() ) . '" class="wc-forward">' . __( 'Go to homepage', 'woocommerce' ) . '</a>', '', array( 'response' => 403 ) );
			}

			if ( $downloads_remaining > 0 ) {
				$wpdb->update( $wpdb->prefix . "woocommerce_downloadable_product_permissions", array(
					'downloads_remaining' => $downloads_remaining - 1,
				), array(
					'user_email' 	=> $email,
					'order_key' 	=> $order_key,
					'product_id' 	=> $product_id,
					'download_id' 	=> $download_id
				), array( '%d' ), array( '%s', '%s', '%d', '%s' ) );
			}

			// Count the download
			$wpdb->update( $wpdb->prefix . "woocommerce_downloadable_product_permissions", array(
				'download_count' => $download_count + 1,
			), array(
				'user_email' 	=> $email,
				'order_key' 	=> $order_key,
				'product_id' 	=> $product_id,
				'download_id' 	=> $download_id
			), array( '%d' ), array( '%s', '%s', '%d', '%s' ) );

			// Trigger action
			do_action( 'woocommerce_download_product', $email, $order_key, $product_id, $user_id, $download_id, $order_id );

			// Get the download URL and try to replace the url with a path
			$file_path = $_product->get_file_download_path( $download_id );

			// Download it!
			WWPDFDownloadHandler::wwpdf_download( $file_path, $product_id, $order_key, $email);
		}
	}

	/*
	 * Download a file - hook into init function.
	 */
	private function wwpdf_download( $file_path, $product_id, $order_key, $email ) {
		global $wpdb, $is_IE;

		$file_download_method = apply_filters( 'woocommerce_file_download_method', get_option( 'woocommerce_file_download_method' ), $product_id );

		if ( ! $file_path ) {
			wp_die( __( 'No file defined', 'woocommerce' ) . ' <a href="' . esc_url( home_url() ) . '" class="wc-forward">' . __( 'Go to homepage', 'woocommerce' ) . '</a>', '', array( 'response' => 404 ) );
		}

		// Redirect to the file...
		if ( $file_download_method == "redirect" ) {
			header( 'Location: ' . $file_path );
			exit;
		}

		// ...or serve it
		$remote_file      = true;
		$parsed_file_path = parse_url( $file_path );

		$wp_uploads       = wp_upload_dir();
		$wp_uploads_dir   = $wp_uploads['basedir'];
		$wp_uploads_url   = $wp_uploads['baseurl'];

		if ( ( ! isset( $parsed_file_path['scheme'] ) || ! in_array( $parsed_file_path['scheme'], array( 'http', 'https', 'ftp' ) ) ) && isset( $parsed_file_path['path'] ) && file_exists( $parsed_file_path['path'] ) ) {

			/** This is an absolute path */
			$remote_file  = false;

		} elseif( strpos( $file_path, $wp_uploads_url ) !== false ) {

			/** This is a local file given by URL so we need to figure out the path */
			$remote_file  = false;
			$file_path    = str_replace( $wp_uploads_url, $wp_uploads_dir, $file_path );

		} elseif( is_multisite() && ( strpos( $file_path, network_site_url( '/', 'http' ) ) !== false || strpos( $file_path, network_site_url( '/', 'https' ) ) !== false ) ) {

			/** This is a local file outside of wp-content so figure out the path */
			$remote_file = false;
			// Try to replace network url
				  $file_path   = str_replace( network_site_url( '/', 'https' ), ABSPATH, $file_path );
				  $file_path   = str_replace( network_site_url( '/', 'http' ), ABSPATH, $file_path );
				  // Try to replace upload URL
				  $file_path   = str_replace( $wp_uploads_url, $wp_uploads_dir, $file_path );

		} elseif( strpos( $file_path, site_url( '/', 'http' ) ) !== false || strpos( $file_path, site_url( '/', 'https' ) ) !== false ) {

			/** This is a local file outside of wp-content so figure out the path */
			$remote_file = false;
			$file_path   = str_replace( site_url( '/', 'https' ), ABSPATH, $file_path );
			$file_path   = str_replace( site_url( '/', 'http' ), ABSPATH, $file_path );

		} elseif ( file_exists( ABSPATH . $file_path ) ) {

			/** Path needs an abspath to work */
			$remote_file = false;
			$file_path   = ABSPATH . $file_path;
		}

		if ( ! $remote_file ) {
			// Remove Query String
			if ( strstr( $file_path, '?' ) ) {
				$file_path = current( explode( '?', $file_path ) );
			}

			// Run realpath
			$file_path = realpath( $file_path );
		}

		// Get extension and type
		$file_extension = strtolower( substr( strrchr( $file_path, "." ), 1 ) );    

		/* WWPDF */ 

		$query = "
			SELECT order_id
			FROM " . $wpdb->prefix . "woocommerce_downloadable_product_permissions
			WHERE user_email = %s
			AND order_key = %s
			AND product_id = %s";

		$args = array(
			$email,
			$order_key,
			$product_id
		);

		if ( $download_id ) {
			$query .= " AND download_id = %s";
			$args[] = $download_id;
		}

		$download_result = $wpdb->get_row( $wpdb->prepare( $query, $args ) );

		if ( ! $download_result ) {
			wp_die( __( 'Invalid download.', 'woocommerce' ) . ' <a href="' . esc_url( home_url() ) . '" class="wc-forward">' . __( 'Go to homepage', 'woocommerce' ) . '</a>', '', array( 'response' => 404 ) );
		}

		$order_id = $download_result->order_id;

		$wwpdf_enabled = $wpdb->get_var( "SELECT option_value FROM " . $wpdb->prefix . "options WHERE option_name = 'enable_wwpdf'");

		if ( ( $wwpdf_enabled == "yes" ) && ( $file_extension == "pdf") ) {

			$wwpdf_files = $wpdb->get_var( "SELECT option_value FROM " . $wpdb->prefix . "options WHERE option_name = 'pdf_files'");

			// get files listed by client:
			$wwpdf_file_list = array_filter( array_map( 'trim', explode( PHP_EOL, $wwpdf_files ) ) );
	
			$file_req = basename($file_path);

			if (in_array($file_req, $wwpdf_file_list) || ($wwpdf_files == ''))  {

				require_once( 'fpdf/fpdf.php' );
				require_once( 'fpdi/fpdi.php' );

				$first_name = "_billing_first_name";      
				$watermark_first_name = $wpdb->get_row( $wpdb->prepare("
					SELECT meta_value
					FROM ".$wpdb->prefix."postmeta
					WHERE post_id = %s
					AND meta_key = %s
					;", $order_id, $first_name) );
				$first_name = $watermark_first_name->meta_value;

				$last_name = "_billing_last_name";      
				$watermark_last_name = $wpdb->get_row( $wpdb->prepare("
					SELECT meta_value
					FROM ".$wpdb->prefix."postmeta
					WHERE post_id = %s
					AND meta_key = %s
					;", $order_id, $last_name) );
				$last_name = $watermark_last_name->meta_value;

				if ( (!$first_name) || (!$last_name) || (!$email) ) {
					wp_die( __('PDF downloads require a first name, last name, and email in the order information. If you have not provided these, contact the site owner to have them added. After they are added to your order, your instant download link will work.', 'water-woo-pdf') . ' <a href="'.home_url().'">' . __('Go to homepage &rarr;', 'water-woo-pdf') . '</a>' );
				}
		
				$wwpdf_file_path = str_replace( '.pdf', '', $file_path ) . '_' . time() . '_' . $order_key . '.' . $file_extension; // customized file path

				$wwpdf_footer_input = $wpdb->get_var( "SELECT option_value FROM " . $wpdb->prefix . "options WHERE option_name = 'footer_input'");

				$wwpdf_footer_input = preg_replace( array( '/\[FIRSTNAME\]/','/\[LASTNAME\]/','/\[EMAIL\]/' ), array( $first_name, $last_name, $email ), $wwpdf_footer_input );

				WWPDFWatermark::apply_and_spit($file_path, $wwpdf_file_path, $wwpdf_footer_input);

			}

		}

		/* End WWPDF */ 

		$ctype = "application/force-download";

		foreach ( get_allowed_mime_types() as $mime => $type ) {
			$mimes = explode( '|', $mime );
			if ( in_array( $file_extension, $mimes ) ) {
				$ctype = $type;
				break;
			}
		}

		// Start setting headers
		if ( ! ini_get('safe_mode') ) {
			@set_time_limit(0);
		}
		if ( function_exists( 'get_magic_quotes_runtime' ) && get_magic_quotes_runtime() ) {
			@set_magic_quotes_runtime(0);
		}

		if( function_exists( 'apache_setenv' ) ) {
			@apache_setenv( 'no-gzip', 1 );
		}
	
		@session_write_close();
		@ini_set( 'zlib.output_compression', 'Off' );

		/**
		* Prevents errors, for example: transfer closed with 3 bytes remaining to read
		*/
		if ( ob_get_length() ) {

			if ( ob_get_level() ) {

				$levels = ob_get_level();

				for ( $i = 0; $i < $levels; $i++ ) {
					ob_end_clean(); // Zip corruption fix
				}

			} else {
				ob_end_clean(); // Clear the output buffer
			}
		}

		if ( $is_IE && is_ssl() ) {
			// IE bug prevents download via SSL when Cache Control and Pragma no-cache headers set.
			header( 'Expires: Wed, 11 Jan 1984 05:00:00 GMT' );
			header( 'Cache-Control: private' );
		} else {
			nocache_headers();
		}

		if ( ( ( $wwpdf_enabled == "yes" ) && ( $file_extension == "pdf") ) && (in_array($file_req, $wwpdf_file_list) || ($wwpdf_files == '') ) ) {

			$filename = basename( $wwpdf_file_path );
		} else {
			$filename = basename( $file_path );
		}

		if ( strstr( $filename, '?' ) ) {
			$filename = current( explode( '?', $filename ) );
		}

		$filename = apply_filters( 'woocommerce_file_download_filename', $filename, $product_id );

		header( "X-Robots-Tag: noindex, nofollow", true );
		header( "Content-Type: " . $ctype );
		header( "Content-Description: File Transfer" );
		header( "Content-Disposition: attachment; filename=\"" . $filename . "\";" );
		header( "Content-Transfer-Encoding: binary" );

		if ( ( ( $wwpdf_enabled == "yes" ) && ( $file_extension == "pdf") ) && (in_array($file_req, $wwpdf_file_list) || ($wwpdf_files == '') ) ) {

			if ( $size = @filesize( $wwpdf_file_path ) ) {
				header( "Content-Length: " . $size );
			}

			if ( $file_download_method == 'xsendfile' ) {

				// Path fix - kudos to Jason Judge
				if ( getcwd() ) {
					$wwpdf_file_path = trim( preg_replace( '`^' . str_replace( '\\', '/', getcwd() ) . '`' , '', $wwpdf_file_path ), '/' );
				}

				header( "Content-Disposition: attachment; filename=\"" . $filename . "\";" );

				if ( function_exists( 'apache_get_modules' ) && in_array( 'mod_xsendfile', apache_get_modules() ) ) {

					header("X-Sendfile: $wwpdf_file_path");
					exit;

				} elseif ( stristr( getenv( 'SERVER_SOFTWARE' ), 'lighttpd' ) ) {

					header( "X-Lighttpd-Sendfile: $wwpdf_file_path" );
				exit;

				} elseif ( stristr( getenv( 'SERVER_SOFTWARE' ), 'nginx' ) || stristr( getenv( 'SERVER_SOFTWARE' ), 'cherokee' ) ) {

					header( "X-Accel-Redirect: /$wwpdf_file_path" );
					exit;
				}
			}

			if ( $remote_file ) {
				WC_Download_Handler::readfile_chunked( $wwpdf_file_path ) or header( 'Location: ' . $wwpdf_file_path );
			} else {
				WC_Download_Handler::readfile_chunked( $wwpdf_file_path ) or wp_die( __( 'File not found', 'woocommerce' ) . ' <a href="' . esc_url( home_url() ) . '" class="wc-forward">' . __( 'Go to homepage', 'woocommerce' ) . '</a>' );
			}

			exit;

		} else {

			if ( $size = @filesize( $file_path ) ) {
				header( "Content-Length: " . $size );
			}

			if ( $file_download_method == 'xsendfile' ) {

				// Path fix - kudos to Jason Judge
				if ( getcwd() ) {
					$file_path = trim( preg_replace( '`^' . str_replace( '\\', '/', getcwd() ) . '`' , '', $file_path ), '/' );
				}

				header( "Content-Disposition: attachment; filename=\"" . $filename . "\";" );

				if ( function_exists( 'apache_get_modules' ) && in_array( 'mod_xsendfile', apache_get_modules() ) ) {

					header("X-Sendfile: $file_path");
					exit;

				} elseif ( stristr( getenv( 'SERVER_SOFTWARE' ), 'lighttpd' ) ) {

					header( "X-Lighttpd-Sendfile: $file_path" );
					exit;

				} elseif ( stristr( getenv( 'SERVER_SOFTWARE' ), 'nginx' ) || stristr( getenv( 'SERVER_SOFTWARE' ), 'cherokee' ) ) {

					header( "X-Accel-Redirect: /$file_path" );
					exit;
				}
			}

			if ( $remote_file ) {
				WC_Download_Handler::readfile_chunked( $file_path ) || header( 'Location: ' . $file_path );
			} else {
				WC_Download_Handler::readfile_chunked( $file_path ) || wp_die( __( 'File not found', 'woocommerce' ) . ' <a href="' . esc_url( home_url() ) . '" class="wc-forward">' . __( 'Go to homepage', 'woocommerce' ) . '</a>', '', array( 'response' => 404 ) );
			}

			exit;
		}
	}
}
new WWPDFDownloadHandler();
?>