<?php
/*
Plugin Name: SiteOrigin Contact Form
Description: Additional settings and styles for the Widgets Bundle Contact Form widget.
Version: 1.0.0
Author: SiteOrigin
Author URI: https://siteorigin.com
License: GPL3
License URI: https://www.gnu.org/licenses/gpl-3.0.txt
Documentation: https://siteorigin.com/premium-documentation/plugin-addons/contact-form-fields/
Tags: Widgets Bundle
Video: b4t5hae1lz
*/

class SiteOrigin_Premium_Plugin_Contact_Form_Fields {
	
	function __construct() {
		add_action( 'init', array( $this, 'init_addon' ) );
	}
	
	static function single() {
		static $single;
		return empty( $single ) ? $single = new self() : $single;
	}
	
	public function init_addon() {
		$this->add_filters();
	}
	
	public function add_filters() {
		if ( class_exists( 'SiteOrigin_Widgets_ContactForm_Widget' ) ) {
			add_filter( 'siteorigin_widgets_form_options_sow-contact-form', array( $this, 'admin_form_options' ), 10, 2 );
			add_filter( 'siteorigin_widgets_contact_form_field_class_paths', array( $this, 'premium_contactform_fields_class_paths' ) );
			add_action( 'siteorigin_widgets_contact_sent', array( $this, 'contact_email_sent_success' ), 10, 2 );
		}
	}

	/**
	 * Tell the autoloader where to look for premium contact form field classes.
	 *
	 * @param $class_paths
	 *
	 * @return array
	 */
	function premium_contactform_fields_class_paths( $class_paths ) {
		$class_paths[] = plugin_dir_path( __FILE__ ) . 'fields/';
		return $class_paths;
	}

	public function admin_form_options( $form_options, $widget ) {
		if ( empty( $form_options ) ) {
			return $form_options;
		}
		
		$position = array_key_exists( 'spam', $form_options ) ? 'spam' : count( $form_options );
		
		$current_user = wp_get_current_user();
		
		siteorigin_widgets_array_insert( $form_options, $position, array(
			'use_auto_responder' => array(
				'type' => 'checkbox',
				'label' => __( 'Use autoresponder.', 'siteorigin-premium' ),
				'default' => false,
				'state_emitter' => array(
					'callback' => 'conditional',
					'args' => array(
						'use_auto_responder[show]: val',
						'use_auto_responder[hide]: ! val',
					)
				),
			),
			'auto_responder' => array(
				'type' => 'section',
				'label' => __( 'Autoresponder', 'siteorigin-premium' ),
				'hide' => true,
				'fields' => array(
					'from_name' => array(
						'type' => 'text',
						'label' => __( 'From name', 'siteorigin-premium' ),
						'default' => $current_user->display_name,
						'description' => __( 'The name of the person the automatic response email will come from.', 'siteorigin-premium' ),
					),
					'from_email' => array(
						'type' => 'text',
						'label' => __( 'From email', 'siteorigin-premium' ),
						'description' => __( 'The email address the automatic response email will come from.', 'siteorigin-premium' ),
					),
					'subject' => array(
						'type' => 'text',
						'label' => __( 'Response email subject', 'siteorigin-premium' ),
						'default' => __( 'Message received!', 'siteorigin-premium' ),
					),
					'message' => array(
						'type' => 'tinymce',
						'label' => __( 'Response email message', 'siteorigin-premium' ),
						'default' => __( 'Thanks for contacting us. We\'ll get back to you shortly.', 'siteorigin-premium' ),
					),
				),
				'state_handler' => array(
					'use_auto_responder[show]' => array( 'slideDown' ),
					'use_auto_responder[hide]' => array( 'slideUp' ),
				),
			)
		) );

		$fields = $form_options['fields']['fields'];
		$field_types = $fields['type']['options'];

		$field_types = array_merge( $field_types, array(
			'datepicker' => __( 'Datetime picker', 'siteorigin-premium' ),
			'location' => __( 'Location', 'siteorigin-premium' ),
		) );

		$fields = array_merge( $fields, array(

			// For location fields
			// These are only required when location is selected
			'location_options' => array(
				'type' => 'section',
				'label' => __( 'Location Options', 'siteorigin-premium' ),
				'fields' => array(
					'show_map' => array(
						'type' => 'checkbox',
						'label' => __( 'Show Google Map', 'siteorigin-premium' ),
						'default' => true,
						'description' => __( 'Clicking on the map will guess the closest address and the map will try to display the address entered into the text input', 'siteorigin-premium' ),
					),
					'default_location' => array(
						'type' => 'text',
						'label' => __( 'Default location', 'siteorigin-premium' ),
					),
					'gmaps_api_key' => array(
						'type'        => 'text',
						'label'       => __( 'Google Maps API Key', 'siteorigin-premium' ),
						'required'    => true,
						'description' => sprintf(
							__( 'Enter your %sAPI key%s. Your map may not function correctly without one.', 'siteorigin-premium' ),
							'<a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank" rel="noopener noreferrer">',
							'</a>'
						)
					),
				),

				'state_handler' => array(
					'field_type_{$repeater}[location]' => array('show'),
					'_else[field_type_{$repeater}]' => array( 'hide' ),
				),
			),

			// For datetime picker fields
			'datetime_options' => array(
				'type' => 'section',
				'label' => __( 'Datetime Options', 'siteorigin-premium' ),
				'fields' => array(
					'show_datepicker' => array(
						'type' => 'checkbox',
						'label' => __( 'Show Date Picker', 'siteorigin-premium' ),
						'default' => true,
					),
					'datepicker_label' => array(
						'type' => 'text',
						'label' => __( 'Date Picker Label', 'siteorigin-premium' ),
						'default' => __( 'Date', 'siteorigin-premium' ),
					),
					'disable_days' => array(
						'type' => 'radio',
						'label' => __( 'Disable', 'siteorigin-premium' ),
						'default' => 'none',
						'options' => array(
							'none' => __( 'None', 'siteorigin-premium' ),
							'weekends' => __( 'Weekends', 'siteorigin-premium' ),
							'weekdays' => __( 'Weekdays', 'siteorigin-premium' ),
						),
					),
					'disabled_dates' => array(
						'type' => 'text',
						'label' => __( 'Disable Dates', 'siteorigin-premium' ),
						'description' => __( 'Specify specific dates, date ranges or weekdays to disable. Dates should be of the format YYYYMMDD e.g. 20160806 and date ranges should be separated by a hyphen e.g. 20160902-20160918. Week days should be the first three letters of the day e.g. Mon,Wed,Fri. Multiple dates, ranges and days should separated by a comma e.g. 20160825,20160902-20160918,Sun,Fri.', 'siteorigin-premium' )
					),
					'show_timepicker' => array(
						'type' => 'checkbox',
						'label' => __( 'Show Time Picker', 'siteorigin-premium' ),
						'default' => false,
					),
					'timepicker_label' => array(
						'type' => 'text',
						'label' => __( 'Time Picker Label', 'siteorigin-premium' ),
						'default' => __( 'Time', 'siteorigin-premium' ),
					),
					'timepicker_custom_format' => array(
						'type' => 'checkbox',
						'label' => __( 'Use 24h format.', 'siteorigin-premium' ),
						'default' => false,
					),
					'disabled_times' => array(
						'type' => 'text',
						'label' => __( 'Disabled Times', 'siteorigin-premium' ),
						'description' => __( 'Specify disabled times. Ranges should be specified with a dash and multiple ranges should be separated with a comma. E.g. 5:00pm-8:00am,1pm-14:30', 'siteorigin-premium' ),
					),
				),

				'state_handler' => array(
					'field_type_{$repeater}[datepicker]' => array('show'),
					'_else[field_type_{$repeater}]' => array( 'hide' ),
				),
			),
		) );

		$fields['type']['options'] = $field_types;
		$form_options['fields']['fields'] = $fields;

		return $form_options;
	}
	
	function contact_email_sent_success( $instance, $email_fields ) {
		if ( ! empty( $instance['use_auto_responder'] ) ) {
			$this->send_auto_response( $email_fields, $instance );
		}
	}
	
	
	function send_auto_response( $email_fields, $instance ) {
		if ( empty( $instance['auto_responder'] ) ) {
			return;
		}
		
		$auto_responder = $instance['auto_responder'];
		
		// Need to have an email address to which to send the auto response.
		if ( empty( $email_fields[ 'email' ] ) ) {
			return;
		}
		
		$response_email_address = sanitize_email( $email_fields[ 'email' ] );
		
		$subject = empty( $auto_responder['subject'] ) ?
			__( 'Message received!', 'siteorigin-premium' ) :
			$auto_responder['subject'];
		
		$body = empty( $auto_responder['message'] ) ?
			__( 'Thanks for contacting us. We\'ll get back to you shortly.', 'siteorigin-premium' ) :
			$auto_responder['message'];
		
		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . SiteOrigin_Widgets_ContactForm_Widget::sanitize_header( $auto_responder['from_name'] ) . ' <' . sanitize_email( $auto_responder['from_email'] ) . '>',
			'Reply-To: ' . SiteOrigin_Widgets_ContactForm_Widget::sanitize_header( $auto_responder['from_name'] ) . ' <' . sanitize_email( $auto_responder['from_email'] ) . '>',
		);
		
		$mail_success = wp_mail( $response_email_address, $subject, $body, $headers );
		
		return $mail_success;
	}
	
}
