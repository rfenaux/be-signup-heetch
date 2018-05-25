<?php
/*
Plugin Name: SiteOrigin Call-To-Action
Description: Additional settings and styles for the Widgets Bundle Call-To-Action widget.
Version: 1.0.0
Author: SiteOrigin
Author URI: https://siteorigin.com
License: GPL3
License URI: https://www.gnu.org/licenses/gpl-3.0.txt
Documentation: https://siteorigin.com/premium-documentation/plugin-addons/call-to-action/
Tags: Widgets Bundle
Video: cb4nttsjzt
*/

class SiteOrigin_Premium_Plugin_Cta {

	function __construct() {
		add_action( 'init', array( $this, 'init_addon' ) );
	}

	static function single() {
		static $single;
		return empty( $single ) ? $single = new self() : $single;
	}
	
	/**
	 * Do any required intialization.
	 */
	public function init_addon() {
		$this->add_filters();
	}
	
	/**
	 * Add filters for modifying various widget related properties and configuration.
	 */
	public function add_filters() {
		if ( class_exists( 'SiteOrigin_Widget_Cta_Widget' ) ) {
			add_filter( 'siteorigin_widgets_form_options_sow-cta', array( $this, 'admin_form_options' ), 10, 2 );

			add_filter( 'siteorigin_widgets_less_variables_sow-cta', array( $this, 'add_less_variables' ), 10, 3 );
			add_filter( 'siteorigin_widgets_google_font_fields_sow-cta', array( $this, 'add_google_font_fields' ), 10, 3 );
		}
	}
	
	/**
	 * Filters the admin form for the call-to-action widget to add Premium fields.
	 *
	 * @param $form_options array The Call-To-Action Widget's form options.
	 * @param $widget SiteOrigin_Widget_Cta_Widget The widget object.
	 *
	 * @return mixed The updated form options array containing the new and modified fields.
	 */
	public function admin_form_options( $form_options, $widget ) {
		if ( empty( $form_options ) ) {
			return $form_options;
		}
		
		$design_fields = $form_options['design']['fields'];
		
		if ( array_key_exists( 'button_align', $design_fields ) ) {
			$position = 'button_align';
		} else {
			$position = count( $design_fields );
		}
		siteorigin_widgets_array_insert( $design_fields, $position, array(
			'fonts' => array(
				'type' => 'section',
				'label' => __( 'Fonts', 'siteorigin-premium' ),
                'fields' => array(
                    'title_font_family' => array(
                        'type' => 'font',
                        'label' => __( 'Title font family', 'siteorigin-premium' ),
                    ),
                    'title_font_size' => array(
                        'type' => 'measurement',
                        'label' => __( 'Title font size', 'siteorigin-premium' ),
                    ),
                    'subtitle_font_family' => array(
                        'type' => 'font',
                        'label' => __( 'Subtitle font family', 'siteorigin-premium' ),
                    ),
                    'subtitle_font_size' => array(
                        'type' => 'measurement',
                        'label' => __( 'Subtitle font size', 'siteorigin-premium' ),
                    ),
                ),
			),
		) );
		
		$form_options['design']['fields'] = $design_fields;

		return $form_options;
	}
	
	/**
	 * Filters the new design related fields into the LESS variables used for the LESS stylesheet.
	 *
	 * @param $less_variables array LESS variable values to be used in the LESS stylesheet.
	 * @param $instance array The widget instance containing possible values to be used in the LESS stylesheet.
	 * @param $widget SiteOrigin_Widget_Cta_Widget The widget object.
	 *
	 * @return mixed The updated LESS variables containing the new and modified variables.
	 *
	 */
	public function add_less_variables( $less_variables, $instance, $widget ) {
		$fonts = empty( $instance['design']['fonts'] ) ? array() : $instance['design']['fonts'];

		if ( ! empty( $fonts['title_font_family'] ) ) {
			
			$font = siteorigin_widget_get_font( $fonts['title_font_family'] );
			$less_variables['title_font_family'] = $font['family'];
			if ( ! empty( $font['weight'] ) ) {
				$less_variables['title_font_weight'] = $font['weight'];
			}
		}
		
		if ( ! empty( $fonts['title_font_size'] ) ) {
			$less_variables['title_font_size'] = $fonts['title_font_size'];
		}

		if ( ! empty( $fonts['subtitle_font_family'] ) ) {

			$font = siteorigin_widget_get_font( $fonts['subtitle_font_family'] );
			$less_variables['subtitle_font_family'] = $font['family'];
			if ( ! empty( $font['weight'] ) ) {
				$less_variables['subtitle_font_weight'] = $font['weight'];
			}
		}

		if ( ! empty( $fonts['subtitle_font_size'] ) ) {
			$less_variables['subtitle_font_size'] = $fonts['subtitle_font_size'];
		}
		
		return $less_variables;
	}
	
	/**
	 * Filters the additional fields used for google fonts.
	 *
	 * @param $fields array Fields containing google font values.
	 * @param $instance array The widget instance.
	 * @param $widget SiteOrigin_Widget_Cta_Widget The widget object.
	 *
	 * @return array The modified google font fields array.
	 */
	public function add_google_font_fields( $fields, $instance, $widget ) {
		if ( ! empty( $instance['design']['fonts']['title_font_family'] ) ) {
			$fields[] = $instance['design']['fonts']['title_font_family'];
		}
		if ( ! empty( $instance['design']['fonts']['subtitle_font_family'] ) ) {
			$fields[] = $instance['design']['fonts']['subtitle_font_family'];
		}
		
		return $fields;
	}

}
