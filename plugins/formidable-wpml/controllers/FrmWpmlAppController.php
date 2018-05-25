<?php

class FrmWpmlAppController {

	// TODO: improve naming

	public static function load_lang() {
		$plugin_folder = FrmWpmlAppHelper::plugin_folder();
		load_plugin_textdomain( 'frmwpml', false, $plugin_folder . '/languages/' );
	}

	public static function include_updater() {
		if ( class_exists( 'FrmAddon' ) ) {
			FrmWpmlUpdate::load_hooks();
		}
	}

	public static function get_translatable_types( $types ) {
		// Tell WPML that we want formidable forms translated
		$types['formidable'] = 'Formidable';
		return $types;
	}

	private static function get_translatable_strings_for_form( $form ) {
		if ( ! is_object( $form ) ) {
			$form = FrmForm::getOne( $form );
		}

		$string_data = array();

		self::get_translatable_strings_from_form_settings( $form, $string_data );

		// TODO: maybe create form or field object to cut down on variables
		$is_repeating = false;
		$fields = FrmField::getAll( array( 'fi.form_id' => $form->id ), 'field_order' );
		foreach ( $fields as $field ) {
			if ( FrmField::is_repeating_field( $field ) ) {
				$is_repeating = true;
			}

			self::get_translatable_strings_for_single_field( $field, $form->id, $is_repeating, $string_data );

			if ( $field->type == 'end_divider' && $is_repeating ) {
				$is_repeating = false;
			}
		}

		return $string_data;

	}

	/**
	 * Get the strings that need translation from a form's Settings
	 *
	 * @since 1.04
	 * @param object $form
	 * @param array $string_data
	 */
	private static function get_translatable_strings_from_form_settings( $form, &$string_data ) {
		$form_keys = array(
			'name', 'description', 'submit_value', 'submit_msg', 'success_msg',
			'email_subject', 'email_message', 'ar_email_subject', 'ar_email_message',
		);

		// Add edit and delete options
		if ( $form->editable ) {
			$form_keys[] = 'edit_value';
			$form_keys[] = 'edit_msg';
			$string_data['delete_msg'] = __( 'Your entry was successfully deleted', 'formidable' );
		}

		foreach ( $form_keys as $key ) {
			if ( isset( $form->{$key} ) && $form->{$key} != '' ) {
				$string_data[ $key ] = $form->{$key};
			} else if ( isset( $form->options[ $key ] ) && $form->options[ $key ] != '' && $form->options[ $key ] != '[default-message]' ) {
				$string_data[ $key ] = $form->options[ $key ];
			}
		}

		// Add draft translations
		if ( isset( $form->options['save_draft'] ) && $form->options['save_draft'] ) {
			if ( isset( $form->options['draft_msg'] ) ) {
				$string_data['draft_msg'] = $form->options['draft_msg'];
			}

			$string_data['draft_label'] = __( 'Save Draft', 'formidable' );
		}

		global $frm_settings;
		$string_data['invalid_msg'] = $frm_settings->invalid_msg;
	}

	/**
	 * Get the strings that need translation for a single field
	 *
	 * @since 1.04
	 * @param object $field
	 * @param int $form_id
	 * @param boolean $is_repeating
	 * @param array $string_data
	 */
	private static function get_translatable_strings_for_single_field( $field, $form_id, $is_repeating, &$string_data ) {
		if ( $field->form_id != $form_id ) {
			// this field is in a repeating section
			return;
		}

		if ( $is_repeating && $field->type == 'end_divider' ) {
			self::add_translations_for_end_divider( $field, $string_data );
			return;
		}

		$keys = array(
			'name', 'description', 'default_value', 'required_indicator', 'blank', 'unique_msg'
		);

		foreach ( $keys as $key ) {
			$string_name = 'field-' . $field->id . '-' . $key;

			if ( $key == 'default_value' ) {
				$default_value = self::get_default_value_for_translation( $field );
				if ( $default_value !== '' ) {
					$string_data[ $string_name ] = $default_value;
				}
			} else if ( isset( $field->{$key} ) && $field->{$key} != '' && $field->{$key} != '*' && ! is_array( $field->{$key} ) ) {
				$string_data[ $string_name ] = $field->{$key};
			} else if ( isset( $field->field_options[ $key ] ) && $field->field_options[ $key ] != '' && $field->field_options[ $key ] != '*' && ! is_array( $field->field_options{$key} ) ) {
				$string_data[ $string_name ] = $field->field_options[ $key ];
			}
		}

		if ( ! $field->required ) {
			unset( $string_data['field-' . $field->id . '-blank'] );
		}

		switch ( $field->type ) {
			case 'break':
				if ( ! isset( $string_data['back_label'] ) ) {
					$string_data['back_label'] = __( 'Previous', 'formidable' );
				}
				break;
			case 'date':
				if ( isset( $field->field_options['locale'] ) && $field->field_options['locale'] != '' ) {
					$string_data['field-' . $field->id . '-locale'] = $field->field_options['locale'];
				}
				break;
			case 'email':
			case 'url':
			case 'website':
			case 'phone':
			case 'image':
			case 'number':
			case 'file':
				if ( isset( $field->field_options['invalid'] ) && $field->field_options['invalid'] != '' ) {
					$string_data['field-' . $field->id . '-invalid'] = $field->field_options['invalid'];
				}
				break;
			case 'select':
			case 'checkbox':
			case 'radio':

				if ( is_array( $field->options ) && ! isset( $field->options['label'] ) ) {
					foreach ( $field->options as $index => $choice ) {
						if ( is_array( $choice ) )
							$choice = isset( $choice['label'] ) ? $choice['label'] : reset( $choice );
						$string_data['field-' . $field->id . '-choice-' . $choice ] = $choice;
					}
				} else {
					if ( is_array( $field->options ) ) {
						$field->options = isset( $field->options['label'] ) ? $field->options['label'] : reset( $field->options );
					}

					$string_data['field-' . $field->id . '-choice-' . $field->options ] = $field->options;
				}
				break;
		}
	}

	/**
	 * Add translations for an end divider field
	 *
	 * @since 1.04
	 * @param object $field
	 * @param array $string_data
	 */
	private static function add_translations_for_end_divider( $field, &$string_data ) {
		$keys = array( 'add_label', 'remove_label' );

		foreach ( $keys as $key ) {
			$string_name = 'field-' . $field->id . '-' . $key;
			$string_data[ $string_name ] = $field->field_options[ $key ];
		}
	}

	//filter the form description and title before displaying
	public static function setup_frm_wpml_form( $form ) {

		$form_keys = self::get_form_option_keys_for_translations();

		foreach ( $form_keys as $key ) {
			$string_name = $form->id . '_' . $key;
			if ( isset( $form->{$key} ) && $form->{$key} != '' ) {
				$form->{$key} = self::get_single_translated_string( $string_name, $form->{$key} );
			} else if ( isset( $form->options[ $key ] ) && $form->options[ $key ] != '' ) {
				$form->options[ $key ] = self::get_single_translated_string( $string_name, $form->options[ $key ] );
			}
		}

		return $form;
	}

	/**
	 * Retrieve a single translated (or untranslated) string
	 *
	 * @since 1.04
	 * @param string $name
	 * @param string $value
	 * @return string
	 */
	private static function get_single_translated_string( $name, $value ) {
		$value = apply_filters( 'wpml_translate_single_string', $value, 'formidable', $name );
		return stripslashes_deep( $value );
	}

	// filter form last, after button name may have been changed
	public static function setup_frm_wpml_form_vars( $values, $entry ) {
		$form = FrmForm::getOne( $entry->form_id );

		if ( isset( $form->options['edit_value'] ) && $values['edit_value'] == $form->options['edit_value'] ) {
			$values['edit_value'] = self::get_single_translated_string( $entry->form_id . '_edit_value', $values['edit_value'] );
		}

		return $values;
	}

	/*
	* If a term is excludd in the settings, exclude it for all languages
	*/
	public static function filter_taxonomies( $exclude, $field ) {
		if ( empty( $exclude ) ) {
			// don't continue if there is nothing to exclude
			return $exclude;
		}

		global $sitepress;

		$default_language = apply_filters( 'wpml_default_language', NULL );
		$current_lang = apply_filters( 'wpml_current_language', NULL );

		if ( $current_lang == $default_language ) {
			// don't check if the excluded options are the correct ones to exclude
			return $exclude;
		}

		$post_type = FrmProFormsHelper::post_type( $field['form_id'] );
		$taxonomy = FrmProAppHelper::get_custom_taxonomy( $post_type, $field );

		$excluded_ids = explode( ',', $exclude );
		foreach ( $excluded_ids as $id ) {
			$trid = $sitepress->get_element_trid( $id, 'tax_' . $taxonomy );
			$translations = $sitepress->get_element_translations( $trid, 'tax_' . $taxonomy );

			if ( isset( $translations[ $current_lang ] ) ) {
				$excluded_ids[] = $translations[ $current_lang ]->term_id;
			}
		}

		$exclude = implode( ',', $excluded_ids );

		return $exclude;
	}

	public static function captcha_lang( $lang ) {
		$current_lang = apply_filters( 'wpml_current_language', NULL );
		$allowed = array(
			'en', 'nl', 'fr', 'de', 'pt', 'ru', 'es', 'tr',
		);
		if ( in_array( $current_lang, $allowed ) ) {
			$lang = $current_lang;
		}

		return $lang;
	}

	public static function submit_button_label( $submit, $form ) {
		global $frm_vars;

		//check if next button needs to be translated
		if ( ! isset( $frm_vars['next_page'][ $form->id ] ) || empty( $frm_vars['next_page'][ $form->id ] ) ) {
			return $submit;
		}

		$field = $frm_vars['next_page'][ $form->id ];

		if ( ! is_object( $field ) || $submit != $field->name ) {
			return $submit;
		}

		$submit = self::get_single_translated_string( $form->id . '_field-' . $field->id . '-name', $submit );

		return $submit;
	}

	public static function setup_frm_wpml( $values, $field ) {
		_deprecated_function( __FUNCTION__, '1.04', 'FrmWpmlAppController::setup_translated_field' );
		return FrmWpmlAppController::setup_translated_field( $values, $field );
	}

	/**
	 * Switch out the translated options/values in a field
	 *
	 * @param array $values
	 * @param object $field
	 * @return array
	 */
	public static function setup_translated_field( $values, $field ) {
		//don't interfere with the form builder page
		if ( is_admin() && ! defined( 'DOING_AJAX' ) && ( ! isset( $_GET ) || ! isset( $_GET['page'] ) || $_GET['page'] != 'formidable' || ! isset( $_GET['frm_action'] ) || $_GET['frm_action'] != 'translate' ) ) {
			return $values;
		}

		$keys = self::get_field_option_keys_for_translations( $field->type );

		$prev_default = $values['default_value'];
		foreach ( $keys as $key ) {
			if ( self::field_option_needs_translation( $values, $key ) ) {
				$name = $values['form_id'] . '_field-' . $values['id'] . '-' . $key;
				$values[ $key ] = self::get_single_translated_string( $name, $values[ $key ] );

				if ( class_exists( 'FrmProFieldsHelper' ) ) {
					$values[ $key ] = FrmProFieldsHelper::get_default_value( $values[ $key ], $field, false, ( 'default_value' == $key ) );
				}
			}
		}

		if ( $values['value'] == $prev_default ) {
			$values['value'] = $values['default_value'];
		}

		if ( ! in_array( $values['type'], array( 'select', 'checkbox', 'radio', 'data' ) ) || $field->type == 'user_id' ) {
			return $values;
		}

		// TODO: should Lookup fields be included in this?
		$sep_val = isset( $values['separate_value'] ) ? $values['separate_value'] : 0;
		if ( is_array( $values['options'] ) && ! isset( $values['options']['label'] ) ) {
			foreach ( $values['options'] as $index => $choice ) {
				if ( is_array( $choice ) ) {
					$choice = isset( $choice['label'] ) ? $choice['label'] : reset( $choice );

					// limit to 160 chars
					$string_name = substr( $values['form_id'] . '_field-' . $values['id'] . '-choice-' . $choice, 0, 160 );
					$values['options'][ $index ]['label'] = self::get_single_translated_string( $string_name, $choice );

					if ( ! $sep_val && isset( $values['options'][ $index ]['value'] ) ) {
						$values['options'][ $index ]['value'] = $choice;
					}
				} else {
					// limit to 160 chars
					$string_name = substr( $values['form_id'] . '_field-' . $values['id'] . '-choice-' . $choice, 0, 160 );

					if ( ( isset( $values['use_key'] ) && $values['use_key'] ) || $sep_val || 'data' == $values['type'] ) {
						$values['options'][ $index ] = self::get_single_translated_string( $string_name, $choice );
					} else {
						$values['options'][ $index ] = array(
							'label' => self::get_single_translated_string( $string_name, $choice ),
							'value' => $choice
						);

						$values['separate_value'] = true;
					}
				}
			}
		} else {
			if ( is_array( $values['options'] ) ) {
				$string_name = substr( $values['form_id'] . '_field-' . $values['id'] . '-choice-' . $values['options']['label'], 0, 160 );
				$values['options']['label'] = self::get_single_translated_string( $string_name, $values['options']['label'] );
			} else {
				$string_name = substr( $values['form_id'] . '_field-' . $values['id'] . '-choice-' . $values['options'], 0, 160 );
				$values['options'] = self::get_single_translated_string( $string_name, $values['options'] );
			}
		}

		return $values;
	}

	/**
	 * Check if a specific field option needs translating
	 *
	 * @since 1.04
	 * @param array $values
	 * @param string $key
	 * @return bool
	 */
	private static function field_option_needs_translation( $values, $key ) {
		$needs_translation = true;

		if ( ! isset( $values[ $key ] ) ) {
			$needs_translation = false;
		} else if ( is_array( $values[ $key ] ) ) {
			$needs_translation = false;
		} else if ( in_array( $values[ $key ], array( '', '*' ) ) ) {
			$needs_translation = false;
		} else if ( $key == 'default_value' && self::default_value_needs_translation( $values ) === false ) {
			$needs_translation = false;
		}

		return $needs_translation;
	}

	/**
	 * Check if a field needs it default value translated
	 *
	 * @since 1.04
	 * @param object|array $field
	 * @return bool
	 */
	private static function default_value_needs_translation( $field ) {
		if ( is_object( $field ) ) {
			$field_type = $field->type;
		} else {
			$field_type = isset( $field['original_type'] ) ? $field['original_type'] : $field['type'];
		}

		return ! in_array( $field_type, array( 'radio', 'checkbox', 'select', 'scale', 'data' ) );
	}

	/**
	 * Update the saved ICL strings
	 *
	 * @since 1.04
	 * @param int $form_id
	 * @param array $values
	 */
	public static function update_saved_wpml_strings( $form_id, $values ) {
		if ( isset( $values['field_options'] ) && ! empty( $values['field_options'] ) ) {
			self::update_saved_field_strings( $form_id );
		}

		if ( isset( $values['options'] ) && ! empty( $values['options'] ) ) {
			self::update_saved_form_strings( $form_id, $values['options'] );
		}
	}

	/**
	 * Update the ICL strings saved for all fields in a form
	 *
	 * @since 1.04
	 * @param int $form_id
	 */
	private static function update_saved_field_strings( $form_id ) {
		$fields = FrmField::get_all_for_form( $form_id );

		foreach ( $fields as $field ) {
			self::update_saved_strings_for_single_field( $form_id, $field );
		}

	}

	/**
	 * Update the ICL strings for a single field
	 *
	 * @since 1.04
	 * @param int $form_id
	 * @param object $field
	 */
	private static function update_saved_strings_for_single_field( $form_id, $field ) {
		$keys = self::get_field_option_keys_for_translations( $field->type );

		foreach ( $keys as $key ) {

			$name = $form_id . '_field-' . $field->id . '-' . $key;

			if ( in_array( $key, array( 'name', 'description' ) ) ) {
				$value = $field->{$key};
			} else if ( $key === 'default_value' ) {
				$value = self::get_default_value_for_translation( $field );
			} else {
				$value = isset( $field->field_options[ $key ] ) ? $field->field_options[ $key ] : '';
			}

			self::update_single_wpml_string( $name, $value );
		}
	}

	/**
	 * Get the default value for translating
	 * Do not translate default value for certain field types
	 * Do not translate shortcodes
	 *
	 * @since 1.04
	 * @param object $field
	 * @return string
	 */
	private static function get_default_value_for_translation( $field ) {
		if ( self::default_value_needs_translation( $field ) ) {
			$default_value = self::get_default_value( $field );
		} else {
			$default_value = '';
		}

		return $default_value;
	}

	/**
	 * Get the default value for a field
	 *
	 * @since 1.04
	 * @param object $field
	 * @return mixed
	 */
	private static function get_default_value( $field ) {
		if ( FrmField::is_option_true_in_object( $field, 'dyn_default_value' ) ) {
			$default_value = $field->field_options['dyn_default_value'];
		} else if ( isset( $field->default_value ) && $field->default_value ) {
			$default_value = $field->default_value;
		} else {
			$default_value = '';
		}

		return $default_value;
	}

	/**
	 * Update the ICL strings saved for a form's Settings
	 *
	 * @since 1.04
	 * @param int $form_id
	 * @param array $posted_form_options
	 */
	private static function update_saved_form_strings( $form_id, $posted_form_options ) {
		$form_option_keys = self::get_form_option_keys_for_translations();

		foreach ( $form_option_keys as $option_key ) {
			if ( isset( $posted_form_options[ $option_key ] ) ) {
				$value = $posted_form_options[ $option_key ];
				$name = $form_id . '_' . $option_key;

				self::update_single_wpml_string( $name, $value );
			}
		}
	}

	/**
	 * Update a single ICL string
	 *
	 * @since 1.04
	 * @param string $name
	 * @param mixed $value
	 */
	private static function update_single_wpml_string( $name, $value ) {
		if ( is_array( $value ) || $value === '' || $value === '*' ) {
			self::unregister_single_wpml_string( $name );
		} else {
			self::register_single_wpml_string( $name, $value );
		}
	}

	/**
	 * Unregister a single ICL string
	 *
	 * @since 1.04
	 * @param string $name
	 */
	public static function unregister_single_wpml_string( $name ) {
		icl_unregister_string( 'formidable', $name );
	}

	/**
	 * Register a single ICL string
	 *
	 * @since 1.04
	 * @param string $name
	 * @param string $value
	 */
	private static function register_single_wpml_string( $name, $value ) {
		do_action( 'wpml_register_single_string', 'formidable', $name, $value );
	}

	/**
	 * Get the keys for all field options that can be translated
	 *
	 * @since 1.04
	 * @param string $field_type
	 * @return array
	 */
	private static function get_field_option_keys_for_translations( $field_type ) {
		switch ( $field_type ) {
			case 'end_divider':
				$keys = array( 'add_label', 'remove_label' );
				break;
			case 'divider':
				$keys = array( 'name', 'description' );
				break;
			case 'break':
				$keys = array( 'name' );
				break;
			case 'date':
				$keys = array(
					'name', 'description', 'default_value',
					'required_indicator', 'invalid', 'locale',
					'blank', 'unique_msg',
				);
				break;
			default:
				$keys = array(
					'name', 'description', 'default_value',
					'required_indicator', 'invalid', 'blank',
					'unique_msg',
				);
		}

		return $keys;
	}

	/**
	 * Get the keys for all form options that can be translated
	 *
	 * @since 1.04
	 * @return array
	 */
	private static function get_form_option_keys_for_translations() {
		$keys = array(
			'name', 'description',
			'submit_value', 'submit_msg', 'success_msg',
			'edit_value', 'edit_msg', 'email_subject', 'email_message',
			'ar_email_subject', 'ar_email_message', 'draft_msg',
		);

		return $keys;
	}

	/*
	* Filter out text values before main Formidable plugin does
	*
	* @return string of HTML
	*/
	public static function replace_form_shortcodes( $html, $form, $values = array() ) {
		preg_match_all( "/\[(if )?(back_label|draft_label)\b(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?/s", $html, $shortcodes, PREG_PATTERN_ORDER );

		if ( empty( $shortcodes[ 0 ] ) ) {
			return $html;
		}

		foreach ( $shortcodes[ 0 ] as $short_key => $tag ) {
			$replace_with = '';
			$atts = shortcode_parse_atts( $shortcodes[ 3 ][ $short_key ] );

			switch ( $shortcodes[ 2 ][ $short_key ] ) {
				case 'back_label':
				case 'draft_label':
					$translation = self::get_single_translated_string( $form->id . '_' . $shortcodes[ 2 ][ $short_key ], '' );

					if ( ! empty( $translation ) ) {
						$html = str_replace( $shortcodes[ 0 ][ $short_key ], $translation, $html );
					}
					break;
			}

			unset( $short_key, $tag, $replace_with );
		}

		return $html;
	}

	public static function setup_frm_wpml_validation( $errors, $field ) {

		$field->field_options = maybe_unserialize( $field->field_options );

		self::maybe_add_required_field_error_message( $field, $errors );

		//there are no errors to translate
		if ( ! isset( $errors['field' . $field->id ] ) ) {
			return $errors;
		}

		$key = false;
		if ( $errors['field' . $field->id ] == $field->field_options['blank'] ) {
			$key = 'blank';
		} else if ( isset( $field->field_options['invalid'] ) && $errors['field' . $field->id ] == $field->field_options['invalid'] ) {
			$key = 'invalid';
		} else if ( isset( $field->field_options['unique_msg'] ) && $errors['field' . $field->id ] == $field->field_options['unique_msg'] ) {
			$key = 'unique_msg';
		}

		if ( $key ) {
			$string_name = $field->form_id . '_field-' . $field->id . '-' . $key;
			$errors['field' . $field->id ] = self::get_single_translated_string( $string_name, $errors['field' . $field->id ] );

		}

		return $errors;
	}

	/**
	 * Add the required field error message if default value should not pass validation and default value is in field
	 *
	 * @since 1.04
	 * @param object $field
	 * @param array $errors
	 */
	private static function maybe_add_required_field_error_message( $field, &$errors ) {
		if ( isset( $errors['field' . $field->id ] ) && $errors['field' . $field->id ] == $field->field_options['blank'] ) {
			return;
		}

		if ( $field->required && FrmField::is_option_true_in_object( $field, 'default_blank' ) &&
			isset( $_POST['item_meta'][ $field->id ] ) && $_POST['item_meta'][ $field->id ] != '' &&
			self::default_value_needs_translation( $field )
		) {
			$string_name = $field->form_id . '_field-' . $field->id . '-default_value';
			$default_value = self::get_single_translated_string( $string_name, $field->default_value );

			if ( class_exists( 'FrmProFieldsHelper' ) ) {
				$default_value = FrmProFieldsHelper::get_default_value( $default_value, $field, false, true );
			}

			if ( $_POST['item_meta'][ $field->id ] == $default_value && ! isset( $errors['field' . $field->id ] ) ) {
				$errors['field' . $field->id ] = $field->field_options['blank'];
			}
		}
	}

	public static function views_to_wpml( $replace_with, $tag, $atts, $field ) {
		if ( ! in_array( $field->type, array( 'select', 'radio', 'checkbox' ) ) || ( isset( $atts['show'] ) && $atts['show'] == 'value' ) ) {
			return $replace_with;
		}

		if ( is_array( $replace_with ) ) {
			foreach ( $replace_with as $k => $v ) {
				$string_name = substr( $field->form_id . '_field-' . $field->id . '-choice-' . $v, 0, 160 );
				$replace_with[ $k ] = self::get_single_translated_string( $string_name, $v );
			}
			unset( $k, $v );

		} else {
			$string_name = substr( $field->form_id . '_field-' . $field->id . '-choice-' . $replace_with, 0, 160 );
			$replace_with = self::get_single_translated_string( $string_name, $replace_with );
		}

		return $replace_with;
	}

	public static function delete_frm_wpml( $id ) {
		global $wpdb;

		//delete strings before a field is deleted
		$strings = $wpdb->get_col( $wpdb->prepare( "SELECT name FROM {$wpdb->prefix}icl_strings
            WHERE context=%s AND name LIKE %s", 'formidable', "%_field-{$id}-%" ) );

		if ( $strings ) {
			foreach ( $strings as $string ) {
				self::unregister_single_wpml_string( $string );
				unset( $string );
			}
		}
	}

	/*
	* Translate the message after an entry is deleted
	* @return string The translated value
	*/
	public static function delete_message( $message, $entry ) {
		$translation = self::get_single_translated_string( $entry->form_id . '_delete_msg', '' );
		if ( ! empty( $translation ) ) {
			$message = $translation;
		}
		return $message;
	}

	/**
	 * Translate the incorrect field message
	 *
	 * @since 1.04
	 * @param string $message
	 * @param array $args
	 * @return string
	 */
	public static function translate_invalid_error_message( $message, $args ) {
		if ( isset( $args['form'] ) && is_object( $args['form'] ) ) {
			$message = self::get_single_translated_string( $args['form']->id . '_invalid_msg', $message );
		}

		return $message;
	}

	public static function get_translatable_items( $items, $type, $filter ) {
		// Only return items if string translation is available, and if type is for formidable.
		if ( $type != 'formidable' ) {
			return $items;
		}

		global $sitepress, $wpdb;

		$default_lang = $sitepress->get_default_language();
		$languages = $sitepress->get_active_languages();

		if ( isset( $_GET ) && isset( $_GET['frm_action'] ) && $_GET['frm_action'] == 'translate' && isset( $_GET['id'] ) && is_numeric( $_GET['id'] ) ) {
			$form_id = absint( $_GET['id'] );
			$forms = FrmForm::getAll( $wpdb->prepare( 'parent_form_id=%d or id=%d', $form_id, $form_id ) );
			$form_array = array();
			foreach ( $forms as $form ) {
				$form_array[ $form->id ] = $form;
			}
			$forms = $form_array;
			unset( $form_array );
		} else {
			$forms = FrmForm::getAll( "is_template=0 AND (status is NULL OR status = '' OR status = 'published')", ' ORDER BY name' );
		}

		foreach ( $forms as $k => $v ) {
			$new_item = new stdClass();

			$new_item->external_type = true;
			$new_item->type = 'formidable';
			$new_item->id = $v->id;
			$new_item->post_type = 'formidable';
			$new_item->post_id = 'external_' . $new_item->post_type . '_' . $v->id;
			$new_item->post_date = $v->created_at;
			$new_item->post_status = $v->status == 'draft' ? __( 'Inactive', 'frmwpml' ) : __( 'Active', 'frmwpml' );
			$new_item->post_title = $v->name;
			$new_item->is_translation = false;

			$new_item->string_data = self::get_translatable_strings_for_form( $v );

			// add to the translation table if required
			$post_trid = $sitepress->get_element_trid( $new_item->id, 'post_' . $new_item->post_type );
			if ( ! $post_trid )
				$sitepress->set_element_language_details( $new_item->id, 'post_' . $new_item->post_type, false, $default_lang, null, false );

			// register the strings with WPML

			foreach ( $new_item->string_data as $key => $value ) {
				$key = ( function_exists( 'mb_substr' ) ) ? mb_substr( $new_item->id . '_' . $key, 0, 160 ) : substr( $new_item->id . '_' . $key, 0, 160 );
				if ( ! icl_st_is_registered_string( 'formidable', $key ) ) {
					self::register_single_wpml_string( $key, $value );
				}

				unset( $key, $value );
			}

			$post_trid = $sitepress->get_element_trid( $new_item->id, 'post_' . $new_item->post_type );
			$post_translations = $sitepress->get_element_translations( $post_trid, 'post_' . $new_item->post_type );

			global $iclTranslationManagement;

			$md5 = $iclTranslationManagement->post_md5( $new_item );

			foreach ( $post_translations as $lang => $translation ) {
				$res = $wpdb->get_row( "SELECT status, needs_update, md5 FROM {$wpdb->prefix}icl_translation_status WHERE translation_id={$translation->translation_id}" );
				if ( $res ) {
					if ( ! $res->needs_update ) {
						// see if the md5 has changed.
						if ( $md5 != $res->md5 ) {
							$res->needs_update = 1;
							$wpdb->update( $wpdb->prefix . 'icl_translation_status', array( 'needs_update' => 1 ), array( 'translation_id' => $translation->translation_id ) );
						}
					}
					$_suffix = str_replace( '-', '_', $lang );
					$index = 'status_' . $_suffix;
					$new_item->$index = $res->status;
					$index = 'needs_update_' . $_suffix;
					$new_item->$index = $res->needs_update;
				}
			}

			$items[] = $new_item;

		}

		return $items;
	}

	public static function get_translatable_item( $item, $id ) {
		if ( $item != null )
			return $item;

		$parts = explode( '_', $id );
		if ( $parts[ 0 ] != 'external' )
			return $item;

		$id = array_pop( $parts );

		unset( $parts[ 0 ] );

		$type = implode( '_', $parts );

		// this is not ours.
		if ( $type != 'formidable' )
			return $item;

		$form = FrmForm::getOne( $id );

		$item = new stdClass();

		$item->external_type = true;
		$item->type = 'formidable';
		$item->id = $form->id;
		$item->ID = $form->id;
		$item->post_type = 'formidable';
		$item->post_id = 'external_' . $item->post_type . '_' . $item->id;
		$item->post_date = $form->created_at;
		$item->post_status = ( $form->status == 'draft' ) ? __( 'Inactive', 'frmwpml' ) : __( 'Active', 'frmwpml' );
		$item->post_title = $form->name;
		$item->is_translation = false;

		$item->string_data = self::get_translatable_strings_for_form( $form );

		return $item;

	}

	public static function get_link( $item, $id, $anchor, $hide_empty ) {
		if ( $item != '' )
			return $item;

		$parts = explode( '_', $id );
		if ( $parts[ 0 ] != 'external' )
			return $item;

		$id = array_pop( $parts );

		unset( $parts[ 0 ] );

		$type = implode( '_', $parts );

		if ( $type != 'formidable' )
			return $item; // this is ours.

		if ( false === $anchor ) {
			$form = FrmForm::getOne( $id );

			if ( ! $form )
				return $item;

			$anchor = stripslashes( $form->name );
		}

		$item = sprintf( '<a href="%s">%s</a>', 'admin.php?page=formidable&frm_action=translate&id=' . $id, $anchor );

		return $item;
	}

	public static function set_ajax_language( $url ) {
		global $sitepress;
		if ( is_object( $sitepress ) ) {
			$url = add_query_arg( array( 'lang' => $sitepress->get_current_language() ), $url );
		}
		return $url;
	}

	public static function add_translate_button( $values ) {
		?>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable&frm_action=translate&id=' . $values['id'] ) ) ?>"
		   class="button-secondary"><?php _e( 'Translate Form', 'formidable' ) ?></a>
		<?php
	}

	public static function translate( $message = '' ) {
		if ( ! function_exists( 'icl_t' ) ) {
			_e( 'You do not have WPML installed', 'formidable' );
			return;
		}

		global $wpdb, $sitepress, $sitepress_settings;

		$id = FrmAppHelper::get_param( 'id', false, 'get', 'absint' );

		$form = FrmForm::getOne( $id );
		$fields_array = FrmField::getAll( array( 'fi.form_id' => $id ), 'field_order' );

		$fields = $form_ids = array();
		foreach ( $fields_array as $f ) {
			$fields[ $f->id ] = $f;
			$form_ids[ $f->form_id ] = absint( $f->form_id );
			unset( $f );
		}
		unset( $fields_array );

		$langs = $sitepress->get_active_languages();
		$default_language = self::get_string_language();
		ksort( $langs );

		$col_order = array( $default_language );
		foreach ( $langs as $lang ) {
			if ( $lang['code'] == $default_language ) {
				continue;
			}

			$col_order[] = $lang['code'];
		}

		$lang_count = ( count( $langs ) - 1 );

		self::get_translatable_items( array(), 'formidable', '' );

		$query_args = array( 'formidable' );
		$like = '';
		foreach ( $form_ids as $form_id ) {
			if ( ! empty( $like ) ) {
				$like .= ' OR ';
			}
			$like .= 'name LIKE %s';
			// if this is a child form, only get the field values
			$query_args[] = $form_id . ( $form_id == $id ? '' : '_field-' ) . '_%';
		}

		$strings = $wpdb->get_results( $wpdb->prepare( "SELECT id, name, value, language FROM {$wpdb->prefix}icl_strings WHERE context=%s AND (" . $like . ") ORDER BY name DESC", $query_args ), OBJECT_K
		);

		if ( $strings ) {
			$translations = $wpdb->get_results( "SELECT id, string_id, value, status, language 
                FROM {$wpdb->prefix}icl_string_translations WHERE string_id in (" . implode( ',', array_keys( $strings ) ) . ") 
                ORDER BY language ASC"
			);
		}

		$path = method_exists( 'FrmAppHelper', 'plugin_path' ) ? FrmAppHelper::plugin_path() : FRM_PATH;

		include( FrmWpmlAppHelper::plugin_path() . '/views/translate.php' );
	}

	public static function get_string_language() {
		global $sitepress;

		$string_version = defined( 'WPML_ST_VERSION' ) ? WPML_ST_VERSION : 1;
		if ( class_exists( 'WPML_Language_Of_Domain' ) ) {
			$lang_of_domain = new WPML_Language_Of_Domain( $sitepress );
			$default_language = $lang_of_domain->get_language( 'formidable' );
			if ( ! $default_language ) {
				$default_language = apply_filters( 'wpml_default_language', NULL );
			}
		} elseif ( version_compare( $string_version, '2.2.5', '>' ) ) {
			$default_language = 'en';
		} else {
			global $sitepress_settings;
			$default_language = ! empty( $sitepress_settings['st']['strings_language'] ) ? $sitepress_settings['st']['strings_language'] : apply_filters( 'wpml_default_language', NULL );
		}

		return $default_language;
	}

	public static function maybe_register_string( $string, $atts ) {
		if ( is_array( $string->value ) ) {
			return $string->value;
		}

		$new_val = false;
		if ( strpos( $string->name, $atts['id'] . '_field-' ) === 0 ) {
			$fid = explode( '-', str_replace( $atts['id'] . '_field-', '', $string->name ), 2 );
			$fields = $atts['fields'];

			if ( isset( $fields[ $fid[ 0 ] ]->{$fid[ 1 ]} ) && $string->value != $fields[ $fid[ 0 ] ]->{$fid[ 1 ]} ) {
				$string->value = $fields[ $fid[ 0 ] ]->{$fid[ 1 ]};
				$new_val = true;
			} else if ( isset( $fields[ $fid[ 0 ] ]->field_options[ $fid[ 1 ] ] ) && $string->value != $fields[ $fid[ 0 ] ]->field_options[ $fid[ 1 ] ] ) {
				$string->value = $fields[ $fid[ 0 ] ]->field_options[ $fid[ 1 ] ];
				$new_val = true;
			}
		} else {
			$form_option_name = str_replace( $atts['id'] . '_', '', $string->name );

			if ( isset( $atts['form']->{$form_option_name} ) && $string->value != $atts['form']->{$form_option_name} ) {
				$string->value = $atts['form']->{$form_option_name};
				$new_val = true;
			} else if ( isset( $atts['form']->options[ $form_option_name ] ) && $string->value != $atts['form']->options[ $form_option_name ] ) {
				$string->value = $atts['form']->options[ $form_option_name ];
				$new_val = true;
			}
		}

		if ( $new_val && $string->value != '' && ! is_array( $string->value ) ) {
			$str_name = ( function_exists( 'mb_substr' ) ) ? mb_substr( $string->name, 0, 160 ) : substr( $string->name, 0, 160 );
			self::register_single_wpml_string( $str_name, $string->value );
		}

		return $string->value;
	}

	public static function update_translate() {
		if ( ! isset( $_POST['frm_wpml'] ) || ! is_array( $_POST['frm_wpml'] ) ) {
			self::translate();
			return;
		}

		global $wpdb;

		if ( ! isset( $_POST['frm_translate_form'] ) || ! wp_verify_nonce( $_POST['frm_translate_form'], 'frm_translate_form_nonce' ) ) {
			global $frm_settings;
			wp_die( $frm_settings->admin_permission );
		}

		foreach ( $_POST['frm_wpml'] as $tkey => $t ) {
			$st = array( 'value' => $t['value'] );
			$st['status'] = ( isset( $t['status'] ) ) ? $t['status'] : ICL_STRING_TRANSLATION_NOT_TRANSLATED;

			if ( is_numeric( $tkey ) ) {
				$wpdb->update( $wpdb->prefix . 'icl_string_translations', $st, array( 'id' => $tkey ) );
			} else if ( ! empty( $t['value'] ) ) {
				$info = explode( '_', $tkey );
				if ( ! is_numeric( $info[ 0 ] ) )
					continue;

				$st['string_id'] = $info[ 0 ];
				$st['language'] = $info[ 1 ];
				$st['translator_id'] = get_current_user_id();
				$st['translation_date'] = current_time( 'mysql' );

				$wpdb->insert( $wpdb->prefix . 'icl_string_translations', $st );
			}
			unset( $t );
			unset( $tkey );
		}

		$message = __( 'Settings Successfully Updated', 'formidable' );
		self::translate( $message );
	}

	public static function translated() {
		//don't continue an other action
		return true;
	}

}