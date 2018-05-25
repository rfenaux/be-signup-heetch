<?php

class FrmSigHooksController{

	public static function load_hooks() {
		add_action( 'plugins_loaded', 'FrmSigAppController::load_lang' );

		add_filter( 'frm_pre_display_form', 'FrmSigAppController::register_scripts' );
		add_action( 'wp_footer', 'FrmSigAppController::footer_js', 20 );

		add_filter( 'frm_setup_new_fields_vars', 'FrmSigAppController::check_signature_fields', 20, 2 );
		add_filter( 'frm_setup_edit_fields_vars', 'FrmSigAppController::check_signature_fields', 20, 3 );
		add_action( 'frm_form_fields', 'FrmSigAppController::front_field', 10, 2 );

		add_filter( 'frm_validate_field_entry', 'FrmSigAppController::validate', 9, 3 );
		add_action( 'frm_after_create_entry', 'FrmSigAppController::maybe_create_signature_files', 10, 2 );

		// Value in default-message
		add_filter( 'frm_email_value', 'FrmSigAppController::email_value', 8, 2 );

		// Field ID shortcode
		add_filter( 'frm_get_signature_display_value', 'FrmSigAppController::display_signature', 10, 3 );
		add_filter( 'frmpro_fields_replace_shortcodes', 'FrmSigAppController::custom_display_signature', 10, 4 );

		// Value in entries tab
		add_filter( 'frm_display_value', 'FrmSigAppController::display_signature', 10, 3 );

		add_filter( 'frm_csv_value', 'FrmSigAppController::csv_value', 10, 2 );
		add_filter( 'frm_graph_value', 'FrmSigAppController::graph_value', 10, 2 );

		add_action( 'frm_before_destroy_entry', 'FrmSigAppController::delete_images' );

		self::load_admin_hooks();
	}

	public static function load_admin_hooks() {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_init', 'FrmSigAppController::include_updater', 1 );

		add_action( 'admin_footer', 'FrmSigAppController::footer_js', 20 );

		add_filter( 'frm_available_fields', 'FrmSigAppController::maybe_add_field' );
		add_filter( 'frm_before_field_created', 'FrmSigAppController::set_defaults' );

		add_action( 'frm_display_added_fields', 'FrmSigAppController::admin_field' );
		add_action( 'frm_field_options_form', 'FrmSigAppController::options_form', 10, 1 );
		add_filter( 'frm_update_field_options', 'FrmSigAppController::update', 10, 3 );
	}

}