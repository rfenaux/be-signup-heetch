<?php
/*
Plugin Name: SiteOrigin Premium
Description: Advanced functionality for SiteOrigin themes and plugins.
Version: 1.3.2
Author: SiteOrigin
Author URI: https://siteorigin.com
Plugin URI: https://siteorigin.com/premium/
License: GPL3
License URI: https://www.gnu.org/licenses/gpl-3.0.txt
*/

define( 'SITEORIGIN_PREMIUM_VERSION', '1.3.2' );
define( 'SITEORIGIN_PREMIUM_JS_SUFFIX', '.min' );

if( ! class_exists( 'SiteOrigin_Premium' ) ) :

	class SiteOrigin_Premium {

		const STORE_URL = 'https://siteorigin.com/';
		const STORE_ITEM_ID = 23323;
		const REPLACE_TEASERS = true;

		static $js_suffix;

		static $default_active = array(
		);

		/**
		 * @var SiteOrigin_Premium_Updater
		 */
		private $updater;

		function __construct(){
			// Register the autoloader
			spl_autoload_register( array( $this, 'autoloader' ) );

			add_action( 'plugins_loaded', array( $this, 'load_plugin_addons' ), 15 );
			add_action( 'after_setup_theme', array( $this, 'load_theme_addons' ), 15 );

			add_action( 'wp_enqueue_scripts', array( $this, 'register_common_scripts' ), 4 );

			if( self::REPLACE_TEASERS  ) {
				// This removes teaser fields from the settings
				add_filter( 'siteorigin_settings_display_teaser', '__return_false' );

				// And we create a new handler to add the field in the case of teasers
				add_action( 'siteorigin_settings_add_teaser_field', array($this, 'handle_teaser_field'), 10, 6 );
			}

			if( ! self::is_theme_mode() ) {
				// Initialize all the extra components
				SiteOrigin_Premium_Admin_Notices::single();
				SiteOrigin_Premium_Options::single();

				// Setup the EDD updater
				$key = get_option( 'siteorigin_premium_key' );
				$this->updater = new SiteOrigin_Premium_Updater( self::STORE_URL, __FILE__, array(
					'version' => SITEORIGIN_PREMIUM_VERSION,
					'license' => !empty( $key ) ? trim( $key ) : false,
					'item_id' => self::STORE_ITEM_ID,
					'author' => 'SiteOrigin',
					'url' => home_url()
				) );
			}

			add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'add_action_links' ) );
			add_filter( 'edd_sl_api_request_verify_ssl', '__return_false' );
		}

		/**
		 * Create the singleton of SiteOrigin Premium
		 *
		 * @return SiteOrigin_Premium
		 */
		static function single(){
			static $single;
			return empty( $single ) ? $single = new self() : $single;
		}

		/**
		 * @param $classname
		 */
		public static function autoloader( $classname ){
			if( preg_match( '/^SiteOrigin_Premium_(Theme_|Plugin_)?([A-Za-z_]*)/', $classname, $matches ) ){

				if( ! empty( $matches[1] ) ) {
					$addon_type = strtolower( trim( $matches[1], '_' ) );
					$filename = dirname( __FILE__ ) . '/addons/' . $addon_type . '/';
					$addon_id = strtolower( str_replace( '_', '-', $matches[2] ) );
					$filename .= $addon_id . '/' . $addon_id . '.php';
				}
				elseif( $matches[2] == 'Options' ) {
					$filename = dirname( __FILE__ ) . '/admin/options.php';
				}
				else {
					$filename = dirname( __FILE__ ) . '/inc/';
					$filename .= strtolower( str_replace( '_', '-', $matches[2] ) ) . '.php';
				}

				if( file_exists( $filename ) ) {
					include $filename;
				}
			}
		}

		/**
		 * Load any addons for the widgets bundle.
		 */
		public function load_plugin_addons(){
			$active = $this->get_active_addons();

			foreach( $active as $id => $status ) {
				if( ! $status ) continue;

				$this->load_addon( $id );
			}
		}

		/**
		 * Load supported and activated addons for themes
		 */
		public function load_theme_addons(){
			global $_wp_theme_features;
			if( empty( $_wp_theme_features ) || !is_array( $_wp_theme_features ) ) return;

			foreach( array_keys( $_wp_theme_features ) as $feature ) {

				if( !preg_match( '/siteorigin-premium-(.+)/', $feature, $matches ) ) continue;
				if( ! isset( $_wp_theme_features[$feature][0] ) ) continue;

				$feature_args = $_wp_theme_features[$feature][0];
				if( empty( $feature_args['enabled'] ) ) continue;

				$feature_name = $matches[1];
				$this->load_addon( 'theme/' . $feature_name );
			}
		}


		public function load_addon( $id ){
			// Attempt to autoload the class
			$classname = 'SiteOrigin_Premium_';
			list( $addon_type, $addon_id ) = explode( '/', $id, 2 );
			$classname .= ucfirst( $addon_type ) . '_';
			$classname .= implode( '_', array_map( 'ucfirst', explode( '-', $addon_id ) ) );

			if( class_exists( $classname, true ) ) {
				// Initialize the addon by creating a single
				return call_user_func( array( $classname, 'single' ) );
			}
			else {
				$this->log_error( sprintf( __( 'Plugin addon %s does not exist.', 'siteorigin-premium' ), $classname ) );
				return false;
			}
		}

		/**
		 * Handle the teaser field
		 *
		 * @param SiteOrigin_Settings $settings
		 * @param string $section
		 * @param string $id
		 * @param string $type
		 * @param string $label
		 * @param array $args
		 */
		public function handle_teaser_field( $settings, $section, $id, $type, $label, $args ){
			if( method_exists( $settings, 'add_field' ) ) {
				$settings->add_field( $section, $id, $type, $label, $args );
			}
		}

		/**
		 * Send a request to the SiteOrigin Premium servers to activate this site.
		 *
		 * @param string $license_key The license key.
		 *
		 * @return string The status
		 */
		public function activate_license( $license_key ){
			$license_key = trim( $license_key );
			if( empty($license_key) ) return false;

			// Request to send over to the activation servers
			$api_params = array(
				'edd_action'=> 'activate_license',
				'license' 	=> $license_key,
				'item_id' => intval( self::STORE_ITEM_ID ),
				'url'       => home_url()
			);

			// lets send the request to the server
			$response = wp_remote_post( self::STORE_URL, array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params
			) );

			if ( is_wp_error( $response ) ) return false;

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
			return $license_data;
		}

		/**
		 * Check if a license key is valid.
		 *
		 * @param $license_key
		 *
		 * @return bool Is the key valid.
		 */
		public function check_license_key( $license_key ){

			// Request to send over to the activation servers
			$api_params = array(
				'edd_action'=> 'check_license',
				'license' 	=> $license_key,
				'item_name' => urlencode( self::STORE_ITEM_ID ),
				'url'       => home_url( )
			);

			// lets send the request to the server
			$response = wp_remote_post( self::STORE_URL, array(
				'timeout' => 15,
				'sslverify' => false,
				'body' => $api_params
			) );

			if ( is_wp_error( $response ) ) return 'inactive';
			$license_data = @ json_decode( wp_remote_retrieve_body( $response ) );

			return isset( $license_data->license ) ? $license_data->license : 'invalid';
		}

		/**
		 * Get the mode we're using. Either development or production.
		 */
		public function get_mode( ){
			return get_option( 'siteorigin_premium_license_status' == 'active' ) ? 'production' : 'development';
		}

		/**
		 * Do a database check to see if the license has been activated.
		 *
		 * @param bool|false $license_key
		 *
		 * @return bool
		 */
		static function is_license_active( $license_key = false ){
			if( empty( $license_key ) ) $license_key = get_option( 'siteorigin_premium_key' );
			return get_option( 'siteorigin_premium_license_status' ) == 'active' && get_option( 'siteorigin_premium_key' ) == $license_key;
		}

		/**
		 * Get all the active addons
		 *
		 * @return mixed|void
		 */
		public function get_active_addons(){
			$active_addons = get_option( 'siteorigin_premium_active', array() );
			$active_addons = wp_parse_args( $active_addons, self::$default_active );
			return $active_addons;
		}

		/**
		 * Set the addon active state
		 *
		 * @param $id
		 * @param bool|true $active
		 */
		public function set_addon_active( $id, $active = true ){
			// Check that the addon exists
			list( $addon_section, $addon_id ) = explode( '/', $id, 2 );

			$active_addons = $this->get_active_addons();
			$filename = SiteOrigin_Premium::dir_path(__FILE__) . 'addons/' . $addon_section . '/' . $addon_id . '/' . $addon_id . '.php';

			if( $addon_section !== 'theme' && file_exists( $filename ) ) {
				$active_addons[ $id ] = $active;
			} else {
				unset( $active_addons[ $id ] );
			}

			update_option( 'siteorigin_premium_active', $active_addons );
		}

		/**
		 * Check if the addon is active
		 *
		 * @param $addon_id
		 *
		 * @return bool
		 */
		public function is_addon_active( $addon_id ){
			$active_addons = $this->get_active_addons();
			return !empty( $active_addons[$addon_id] );
		}

		public function register_common_scripts(){

			if( ! wp_script_is( 'siteorigin-parallax', 'registered' ) ) {
				// Page Builder and SiteOrigin Premium use the same parallax library.
				wp_register_script(
					'siteorigin-parallax',
					SiteOrigin_Premium::dir_url( __FILE__ ) . 'js/siteorigin-parallax' . SITEORIGIN_PREMIUM_JS_SUFFIX . '.js',
					array( 'jquery' ),
					SITEORIGIN_PREMIUM_VERSION
				);
			}
			
			wp_register_script(
				'on-screen',
				SiteOrigin_Premium::dir_url( __FILE__ ) . 'js/on-screen.umd' . SITEORIGIN_PREMIUM_JS_SUFFIX . '.js',
				array(),
				SITEORIGIN_PREMIUM_VERSION
			);

			wp_register_style(
				'siteorigin-premium-animate',
				SiteOrigin_Premium::dir_url( __FILE__ ) . 'css/animate' . SITEORIGIN_PREMIUM_JS_SUFFIX . '.css',
				array( ),
				SITEORIGIN_PREMIUM_VERSION
			);
			
			wp_register_script(
				'siteorigin-premium-animate',
				SiteOrigin_Premium::dir_url( __FILE__ ) . 'js/animate' . SITEORIGIN_PREMIUM_JS_SUFFIX . '.js',
				array( 'jquery', 'on-screen' ),
				SITEORIGIN_PREMIUM_VERSION
			);
		}

		/**
		 * Get a form instance.
		 *
		 * @param $name_prefix
		 * @param $form_options
		 *
		 * @return SiteOrigin_Premium_Form
		 */
		public function get_form( $name_prefix, $form_options ){
			return new SiteOrigin_Premium_Form(
				$name_prefix,
				$form_options
			);
		}

		/**
		 * @param $links
		 *
		 * @return $links
		 */
		public function add_action_links( $links ){
			unset( $links['edit'] );
			$links['addons'] = '<a href="' . esc_url( admin_url( 'admin.php?page=siteorigin-premium-addons' ) ) . '">' . __( 'Addons', 'siteorigin-premium' ) . '</a>';
			$links['license'] = '<a href="' . esc_url( admin_url( 'admin.php?page=siteorigin-premium-license' ) ) . '">' . __( 'License', 'siteorigin-premium' ) . '</a>';

			return $links;
		}

		/**
		 * @param $error
		 */
		private function log_error( $error ) {
			// Add any development logging here
		}

		/**
		 * Check if SiteOrigin Premium is in a theme.
		 *
		 * @return string False if not in theme mode or template/stylesheet if in theme mode.
		 */
		public static function is_theme_mode(){
			static $theme_mode = null;
			if( ! is_null( $theme_mode ) ) return $theme_mode;

			$theme_mode = false;
			if( strpos( __FILE__, get_template_directory() ) === 0 ) {
				$theme_mode = 'template';
			}
			else if( strpos( __FILE__, get_stylesheet_directory() ) === 0 ) {
				$theme_mode = 'stylesheet';
			}

			return $theme_mode;
		}

		/**
		 * Get the directory URL of a file.
		 *
		 * @param $filename
		 *
		 * @return string
		 */
		public static function dir_url( $filename = false ){
			if( $filename === false ) $filename = __FILE__;

			switch( self::is_theme_mode() ) {
				case 'template':
					$url = str_replace( get_template_directory(), get_template_directory_uri(), dirname( $filename ) );
					break;
				case 'stylesheet':
					$url = str_replace( get_template_directory(), get_template_directory_uri(), dirname( $filename ) );
					break;
				default:
					$url = plugin_dir_url( $filename );
					break;
			}

			$url = rtrim( $url, '/' ) . '/';
			return $url;
		}

		/**
		 * Get the directory path of a file.
		 *
		 * @param $filename
		 *
		 * @return string
		 */
		public static function dir_path( $filename = false ){
			if( $filename === false ) $filename = __FILE__;

			switch( self::is_theme_mode() ) {
				case 'template':
				case 'stylesheet':
					$dir = dirname( $filename );
					break;
				default:
					$dir = plugin_dir_path( $filename );
					break;
			}

			$dir = rtrim( $dir, '/' ) . '/';
			return $dir;
		}

	}

endif;

SiteOrigin_Premium::single();
