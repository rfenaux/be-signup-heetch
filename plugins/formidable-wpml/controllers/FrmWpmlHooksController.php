<?php

class FrmWpmlHooksController{

	public static function load_hooks() {
		if ( ! function_exists( 'icl_t' ) ) {
			return;
		}

		//add_action( 'plugins_loaded', 'FrmWpmlAppController::load_lang', 11 );

		add_filter('WPML_get_translatable_types', 'FrmWpmlAppController::get_translatable_types');
		add_filter('WPML_get_translatable_items', 'FrmWpmlAppController::get_translatable_items', 10, 3);
		add_filter('WPML_get_translatable_item', 'FrmWpmlAppController::get_translatable_item', 10, 2);
		add_filter('WPML_get_link', 'FrmWpmlAppController::get_link', 10, 4);

		add_filter( 'frm_ajax_url', 'FrmWpmlAppController::set_ajax_language' );

		add_filter('frm_pre_display_form', 'FrmWpmlAppController::setup_frm_wpml_form');
		add_filter('frm_setup_edit_entry_vars', 'FrmWpmlAppController::setup_frm_wpml_form_vars', 20, 2);
		add_filter('frm_setup_new_fields_vars', 'FrmWpmlAppController::setup_translated_field', 20, 2);
		add_filter('frm_setup_edit_fields_vars', 'FrmWpmlAppController::setup_translated_field', 20, 2);
		add_filter('frm_exclude_cats', 'FrmWpmlAppController::filter_taxonomies', 10, 2);
		add_filter('frm_form_replace_shortcodes', 'FrmWpmlAppController::replace_form_shortcodes', 9, 3);
		add_filter('frm_recaptcha_lang', 'FrmWpmlAppController::captcha_lang');
		add_filter('frm_submit_button', 'FrmWpmlAppController::submit_button_label', 20, 2);
		add_filter('frm_validate_field_entry', 'FrmWpmlAppController::setup_frm_wpml_validation', 30, 2);

		add_filter( 'frm_delete_message', 'FrmWpmlAppController::delete_message', 10, 2 );
		add_filter( 'frm_invalid_error_message', 'FrmWpmlAppController::translate_invalid_error_message', 10, 2 );

		//add_filter('frmpro_fields_replace_shortcodes', 'FrmWpmlAppController::views_to_wpml', 9, 4);

		self::load_admin_hooks();
	}

	public static function load_admin_hooks() {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_init', 'FrmWpmlAppController::include_updater', 1 );

		add_action('frm_settings_buttons', 'FrmWpmlAppController::add_translate_button');

		add_action( 'frm_form_action_translate', 'FrmWpmlAppController::translate' );
		add_action('frm_form_action_update_translate', 'FrmWpmlAppController::update_translate');
		add_filter('frm_update_form', 'FrmWpmlAppController::update_saved_wpml_strings', 10, 2 );

		add_filter('frm_form_stop_action_translate', 'FrmWpmlAppController::translated');
		add_filter('frm_form_stop_action_update_translate', 'FrmWpmlAppController::translated');

		add_action('frm_before_destroy_field', 'FrmWpmlAppController::delete_frm_wpml');
	}

}