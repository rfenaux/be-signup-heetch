<?php

class FrmLocAppController{

	public static $min_version = '2.01';

	public static function min_version_notice(){
		$frm_version = is_callable('FrmAppHelper::plugin_version') ? FrmAppHelper::plugin_version() : 0;

		// check if Formidable meets minimum requirements
		if ( version_compare($frm_version, self::$min_version, '>=') ) {
			return;
		}

		$wp_list_table = _get_list_table('WP_Plugins_List_Table');
		echo '<tr class="plugin-update-tr active"><th colspan="' . $wp_list_table->get_column_count() . '" class="check-column plugin-update colspanchange"><div class="update-message">'.
			__('You are running an outdated version of Formidable. This plugin may not work correctly if you do not update Formidable.', 'formidable') .
				'</div></td></tr>';

	}

	public static function include_updater() {
		if ( class_exists( 'FrmAddon' ) ) {
			FrmLocUpdate::load_hooks();
		}
	}

	public static function menu(){
		if ( is_callable('FrmAppHelper::get_settings') ) {
			$frm_settings = FrmAppHelper::get_settings();
		} else {
			return;
		}

		add_submenu_page('formidable', $frm_settings->menu .' | Locations', 'Locations', 'frm_create_entries', 'formidable-locations', 'FrmLocAppController::route');
	}

	public static function route(){
		$action = isset( $_REQUEST['frm_action'] ) ? 'frm_action' : 'action';
		$action = FrmAppHelper::get_param( $action, '', 'get', 'sanitize_title' );

		if ( $action == 'frm_import_locations' ) {
			return FrmLocImport::import_locations();
		} else {
			return self::get_submenu_page();
		}
	}

	public static function get_submenu_page(){
		$import_options = array(
			'countries_states' => __( 'Countries and States/Provinces', 'formidable' ),
			'states_cities'	   => __( 'U.S. States, Counties, and Cities', 'formidable' ),
		);

		$reset_link = wp_nonce_url( add_query_arg( 'reset', 'all' ), 'reset_loc', 'loc_nonce' );
		if ( isset( $_GET['reset'] ) && $_GET['reset'] == 'all' ) {
			FrmLocImport::reset_import();
		}

		include_once( dirname( dirname( __FILE__ ) ) . '/views/submenu_page.php' );
	}

}