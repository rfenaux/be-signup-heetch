<?php
/**
 * License handler for Easy Digital Downloads
 *
 * This class should simplify the process of adding license information
 * to new EDD extensions.
 *
 * @version 4.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WIDGETOPTS_License' ) ) :

/**
 * WIDGETOPTS_License Class
 */
class WIDGETOPTS_License {
	private $file;
	private $license;
	private $item_name;
	private $item_id;
	private $item_shortname;
	private $version;
	private $author;
	private $api_url = 'https://phpbits.net/edd-sl-api/';

	/**
	 * Class constructor
	 *
	 * @param string  $_file
	 * @param string  $_item
	 * @param string  $_version
	 * @param string  $_author
	 * @param string  $_optname
	 * @param string  $_api_url
	 */
	function __construct( $_file, $_item, $_version, $_author, $_optname = null, $_api_url = null ) {

		$this->file           = $_file;

		if( is_numeric( $_item ) ) {
			$this->item_id    = absint( $_item );
		} else {
			$this->item_name  = $_item;
		}

		$this->item_shortname = 'widgetopts_' . preg_replace( '/[^a-zA-Z0-9_\s]/', '', str_replace( ' ', '_', strtolower( $this->item_name ) ) );
		$this->version        = $_version;
		$this->license        = trim( get_option( $this->item_shortname . '_license_key', '' ) );
		$this->author         = $_author;
		$this->api_url        = is_null( $_api_url ) ? $this->api_url : $_api_url;

		/**
		 * Allows for backwards compatibility with old license options,
		 * i.e. if the plugins had license key fields previously, the license
		 * handler will automatically pick these up and use those in lieu of the
		 * user having to reactive their license.
		 */
		if ( ! empty( $_optname ) ) {
			$opt = get_option( $_optname, false );

			if( isset( $opt ) && empty( $this->license ) ) {
				$this->license = trim( $opt );
			}
		}

		// Setup hooks
		$this->includes();
		$this->hooks();

	}

	/**
	 * Include the updater class
	 *
	 * @access  private
	 * @return  void
	 */
	private function includes() {
		if ( ! class_exists( 'EDD_SL_Plugin_Updater' ) )  {
			require_once 'EDD_SL_Plugin_Updater.php';
		}
	}

	/**
	 * Setup hooks
	 *
	 * @access  private
	 * @return  void
	 */
	private function hooks() {

		// Check that license is valid once per week
		add_action( 'edd_weekly_scheduled_events', array( $this, 'weekly_license_check' ) );

		// For testing license notices, uncomment this line to force checks on every page load
		// add_action( 'admin_init', array( $this, 'weekly_license_check' ) );

		// Updater
		add_action( 'admin_init', array( $this, 'auto_updater' ), 0 );

		// Display notices to admins
		add_action( 'admin_notices', array( $this, 'notices' ) );

		add_action( 'after_plugin_row_' . plugin_basename( $this->file ), array( $this, 'plugin_row_license_missing' ), 10, 2 );

	}

	/**
	 * Auto updater
	 *
	 * @access  private
	 * @return  void
	 */
	public function auto_updater() {
		$betas = widgetopts_get_option( 'enabled_betas', array() );

		$args = array(
			'version'   => $this->version,
			'license'   => $this->license,
			'author'    => $this->author
		);

		if( ! empty( $this->item_id ) ) {
			$args['item_id']   = $this->item_id;
		} else {
			$args['item_name'] = $this->item_name;
		}

		// Setup the updater
		$edd_updater = new EDD_SL_Plugin_Updater(
			$this->api_url,
			$this->file,
			$args
		);
	}


	/**
	 * Activate the license key
	 *
	 * @access  public
	 * @return  void
	 */
	public function activate_license( $license = '', $nonce = '' ) {

		if ( empty( $license ) ) {
			return;
		}

		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'widgetopts_license_nonce' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$details = get_option( $this->item_shortname . '_license_active' );

		if ( is_object( $details ) && 'valid' === $details->license ) {
			return;
		}

		$license = sanitize_text_field( $license );

		if( empty( $license ) ) {
			return;
		}

		// Data to send to the API
		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => $license,
			'item_name'  => urlencode( $this->item_name ),
			'url'        => home_url()
		);

		// Call the API
		$response = wp_remote_post(
			$this->api_url,
			array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params
			)
		);

		// Make sure there are no errors
		if ( is_wp_error( $response ) ) {
			return;
		}

		// Tell WordPress to look for updates
		set_site_transient( 'update_plugins', null );

		// Decode license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		if( function_exists( 'is_multisite' ) && is_multisite() && is_object( $license_data ) && 'valid' === $license_data->license ){
			if( $license_data->license_limit > 0 ){
				$license_data = (object) array(
					'success' 		=> '',
					'license' 		=> 'invalid',
					'item_name' 	=> urlencode( $this->item_name ),
					'error'			=> 'multisite'
				);
			}
			
		}
		// print_r( $license_data );
		update_option( $this->item_shortname . '_license_active', $license_data );

	}


	/**
	 * Deactivate the license key
	 *
	 * @access  public
	 * @return  void
	 */
	public function deactivate_license( $license = '', $nonce = '' ) {

		if( empty( $license ) ){
			return;
		}

		if( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Data to send to the API
		$api_params = array(
			'edd_action' => 'deactivate_license',
			'license'    => $license,
			'item_name'  => urlencode( $this->item_name ),
			'url'        => home_url()
		);

		// Call the API
		$response = wp_remote_post(
			$this->api_url,
			array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params
			)
		);

		// Make sure there are no errors
		if ( is_wp_error( $response ) ) {
			return;
		}

		// Decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );
		if( is_object( $license_data ) && isset( $license_data->license ) && 'deactivated' == $license_data->license ){
			delete_option( $this->item_shortname . '_license_active' );
		}

		return $license_data->license;
	}


	/**
	 * Check if license key is valid once per week
	 *
	 * @access  public
	 * @since   2.5
	 * @return  void
	 */
	public function weekly_license_check() {

		if( empty( $this->license ) ) {
			return;
		}

		// data to send in our API request
		$api_params = array(
			'edd_action'=> 'check_license',
			'license' 	=> $this->license,
			'item_name' => urlencode( $this->item_name ),
			'url'       => home_url()
		);

		// Call the API
		$response = wp_remote_post(
			$this->api_url,
			array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params
			)
		);

		// make sure the response came back okay
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		update_option( $this->item_shortname . '_license_active', $license_data );

	}


	/**
	 * Admin notices for errors
	 *
	 * @access  public
	 * @return  void
	 */
	public function notices() {

		static $showed_invalid_message;

		if( empty( $this->license ) ) {
			return;
		}

		if( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$messages = array();

		$license = get_option( $this->item_shortname . '_license_active' );

		if( function_exists( 'is_multisite' ) && is_multisite() && is_object( $license ) && 'valid' !== $license->license && empty( $showed_invalid_message ) && isset( $license->error ) && $license->error == 'multisite' ){

			$messages[] = sprintf(
				__( '<strong>You have incompatible license key for Multisite installation</strong>. Please <a href="%s">contact support</a> to upgrade your license.', 'widget-options' ),
				esc_url( 'https://widget-options.com/contact/' )
			);

			$showed_invalid_message = true;

		}else if( is_object( $license ) && 'valid' !== $license->license && empty( $showed_invalid_message ) ) {

			$messages[] = sprintf(
				__( 'You have invalid or expired license keys for Extended Widget Options. Please go to the <a href="%s">Licenses page</a> to correct this issue.', 'widget-options' ),
				esc_url( admin_url( 'options-general.php?page=widgetopts_plugin_settings' ) )
			);

			$showed_invalid_message = true;

		}

		if( ! empty( $messages ) ) {

			foreach( $messages as $message ) {

				echo '<div class="error">';
					echo '<p>' . $message . '</p>';
				echo '</div>';

			}

		}

	}

	/**
	 * Displays message inline on plugin row that the license key is missing
	 *
	 * @access  public
	 * @since   4.1
	 * @return  void
	 */
	public function plugin_row_license_missing( $plugin_data, $version_info ) {

		static $showed_imissing_key_message;

		$license = get_option( $this->item_shortname . '_license_active' );

		if( ( ! is_object( $license ) || 'valid' !== $license->license ) && empty( $showed_imissing_key_message[ $this->item_shortname ] ) ) {
			$message = sprintf( esc_html__( '%sRegister%s your copy of Extended Widget options to receive access to automatic upgrades and support. Need a license key? %sPurchase one now%s.', 'widget-options' ), '<a href="'. esc_url( admin_url( 'options-general.php?page=widgetopts_plugin_settings' ) ) .'">', '</a>', '<a href="https://widget-options.com/" target="_blank">', '</a>' );
            echo '</tr><tr class="plugin-update-tr"><td colspan="5" class="plugin-update"><div class="update-message notice inline notice-warning notice-alt"><p>' . $message . '</p></div></td>';
		}

	}

	/**
	 * Adds this plugin to the beta page
	 *
	 * @access  public
	 * @param   array $products
	 * @since   2.6.11
	 * @return  void
	 */
	public function register_beta_support( $products ) {
		$products[ $this->item_shortname ] = $this->item_name;

		return $products;
	}
}

endif; // end class_exists check
