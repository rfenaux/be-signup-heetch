<?php
/*
Plugin Name: SiteOrigin Accordion
Description: Additional settings and styles for the Widgets Bundle Accordion widget.
Version: 1.0.0
Author: SiteOrigin
Author URI: https://siteorigin.com
License: GPL3
License URI: https://www.gnu.org/licenses/gpl-3.0.txt
Documentation: https://siteorigin.com/widgets-bundle/accordion-widget/
Tags: Widgets Bundle
Video: 7u5rmbxcev
*/

class SiteOrigin_Premium_Plugin_Accordion {

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
		if ( class_exists( 'SiteOrigin_Widget_Accordion_Widget' ) ) {
			add_filter( 'siteorigin_widgets_form_options_sow-accordion', array( $this, 'admin_form_options' ), 10, 2 );
			add_filter( 'siteorigin_widgets_less_variables_sow-accordion', array( $this, 'add_less_variables' ), 10, 3 );
			add_filter( 'siteorigin_widgets_template_variables_sow-accordion', array( $this, 'add_template_variables' ), 10, 4 );
			add_filter( 'siteorigin_widgets_google_font_fields_sow-accordion', array( $this, 'add_google_font_fields' ), 10, 3 );
			add_filter( 'siteorigin_widgets_wrapper_data_sow-accordion', array( $this, 'add_front_end_params' ), 10, 3 );
			add_filter( 'siteorigin_widgets_accordion_render_panel_content', array( $this, 'render_panel_content' ), 10, 3 );
			add_filter( 'siteorigin_widgets_form_instance_sow-accordion', array( $this, 'modify_instance' ), 10, 2 );
			add_filter( 'siteorigin_widgets_instance_sow-accordion', array( $this, 'modify_instance' ), 10, 2 );
		}
	}

	public function admin_form_options( $form_options, $widget ) {
		if ( empty( $form_options ) ) {
			return $form_options;
		}
		
		$accordion_presets = json_decode( file_get_contents( plugin_dir_path( __FILE__ ) . 'data/presets.json' ), true );

		siteorigin_widgets_array_insert( $form_options, 'title', array(
			'presets' => array(
				'type' => 'presets',
				'label' => __( 'Presets', 'siteorigin-premium'),
				'options' => $accordion_presets,
			),
		) );
		
		$panels_fields = $form_options['panels']['fields'];
		
		if ( array_key_exists( 'content_text', $panels_fields ) ) {
			$position = 'content_text';
			$panels_fields['content_text']['state_handler'] = array(
				'content_type_{$repeater}[text]' => array( 'show' ),
				'_else[content_type_{$repeater}]' => array( 'hide' ),
			);
		} else {
			$position = count( $panels_fields );
		}
		siteorigin_widgets_array_insert( $panels_fields, $position, array(
			'title_icon' => array(
				'type' => 'icon',
				'label' => __( 'Title icon', 'so-widgets-bundle' ),
			),
			'content_type' => array(
				'type'          => 'radio',
				'label'         => __( 'Content type', 'so-widgets-bundle' ),
				'options'       => array(
					'text'   => __( 'Text', 'so-widgets-bundle' ),
					'layout' => __( 'Layout builder', 'so-widgets-bundle' ),
				),
				'default'       => 'text',
				'state_emitter' => array(
					'callback' => 'select',
					'args'     => array( 'content_type_{$repeater}' )
				),
			),
			'content_layout' => array(
				'type'  => 'builder',
				'label' => __( 'Content', 'so-widgets-bundle' ),
				'builder_type' => 'accordion_panel_builder',
				'state_handler' => array(
					'content_type_{$repeater}[layout]' => array( 'show' ),
					'_else[content_type_{$repeater}]' => array( 'hide' ),
				),
			),
		) );
		
		$form_options['panels']['fields'] = $panels_fields;
		
		$position = array_key_exists( 'design', $form_options ) ? 'design' : count( $form_options );
		siteorigin_widgets_array_insert( $form_options, $position, array(
			'max_open_panels' => array(
				'type' => 'number',
				'label' => __( 'Maximum number of simultaneous open panels', 'so-widgets-bundle' ),
			),
			'use_anchor_tags' => array(
				'type' => 'checkbox',
				'label' => __( 'Use anchor tags in the URL.', 'so-widgets-bundle' ),
			),
			'initial_scroll_panel' => array(
				'type' => 'number',
				'label' => __( 'Initially scroll to panel', 'so-widgets-bundle' ),
				'description' => __( 'The number of the panel, starting at 1, to which to scroll when the page first loads.', 'so-widgets-bundle' ),
			),
		) );
		
		$border_width_units = array();
		foreach ( siteorigin_widgets_get_measurements_list() as $unit ) {
			if ( $unit != '%' ) {
				$border_width_units[] = $unit;
			}
		}
		
		$heading_design_fields = $form_options['design']['fields']['heading']['fields'];
		if ( array_key_exists( 'border_width', $heading_design_fields ) ) {
			$heading_design_fields['border_width']['type'] = 'multi-measurement';
			$heading_design_fields['border_width']['autofill'] = true;
			
			$heading_design_fields['border_width']['measurements'] = array(
				'top' => array(
					'label' => __( 'Top', 'siteorigin-premium' ),
					'units' => $border_width_units,
				),
				'right' => array(
					'label' => __( 'Right', 'siteorigin-premium' ),
					'units' => $border_width_units,
				),
				'bottom' => array(
					'label' => __( 'Bottom', 'siteorigin-premium' ),
					'units' => $border_width_units,
				),
				'left' => array(
					'label' => __( 'Left', 'siteorigin-premium' ),
					'units' => $border_width_units,
				),
			);
		}
		
		$position = array_key_exists( 'border_color', $heading_design_fields ) ? 'border_color' : count( $heading_design_fields );
		siteorigin_widgets_array_insert( $heading_design_fields, $position, array(
			'title_align' => array(
				'type' => 'radio',
				'label' => __( 'Title alignment', 'so-widgets-bundle' ),
				'options' => array(
					'left' => __( 'Left', 'so-widgets-bundle' ),
					'right' => __( 'Right', 'so-widgets-bundle' ),
					'center' => __( 'Center', 'so-widgets-bundle' ),
				),
				'default' => 'left',
			),
			'title_icon_location' => array(
				'type' => 'radio',
				'label' => __( 'Title icon location', 'so-widgets-bundle' ),
				'options' => array(
					'left' => __( 'Left', 'so-widgets-bundle' ),
					'right' => __( 'Right', 'so-widgets-bundle' ),
				),
				'default' => 'left',
			),
			'title_font_family' => array(
				'type' => 'font',
				'label' => __( 'Title font', 'so-widgets-bundle' ),
			),
			'title_font_size' => array(
				'type' => 'measurement',
				'label' => __( 'Title font size', 'so-widgets-bundle' ),
			),
			'title_text_transform' => array(
				'type' => 'select',
				'label' => __( 'Title text transform', 'siteorigin-premium' ),
				'options' => array(
					'none' => __( 'None', 'siteorigin-premium' ),
					'capitalize' => __( 'Capitalize', 'siteorigin-premium' ),
					'uppercase' => __( 'Uppercase', 'siteorigin-premium' ),
					'lowercase' => __( 'Lowercase', 'siteorigin-premium' ),
				),
			),
		) );
		$heading_design_fields = array_merge( $heading_design_fields, array(
			'border_radius' => array(
				'type' => 'multi-measurement',
				'label' => __( 'Border radius', 'so-widgets-bundle' ),
				'autofill' => true,
				'measurements' => array(
					'top_left' => __( 'Top left', 'so-widgets-bundle' ),
					'top_right' => __( 'Top right', 'so-widgets-bundle' ),
					'bottom_right' => __( 'Bottom right', 'so-widgets-bundle' ),
					'bottom_left' => __( 'Bottom left', 'so-widgets-bundle' ),
				),
			),
			'padding' => array(
				'type' => 'multi-measurement',
				'label' => __( 'Padding', 'so-widgets-bundle' ),
				'autofill' => true,
				'default' => '15px 30px 15px 30px',
				'measurements' => array(
					'top' => __( 'Top', 'so-widgets-bundle' ),
					'right' => __( 'Right', 'so-widgets-bundle' ),
					'bottom' => __( 'Bottom', 'so-widgets-bundle' ),
					'left' => __( 'Left', 'so-widgets-bundle' ),
				),
			),
			'show_open_close_icon' => array(
				'type' => 'checkbox',
				'label' => __( 'Display open/close icon', 'so-widgets-bundle' ),
				'default' => true,
				'state_emitter' => array(
					'callback' => 'conditional',
					'args' => array(
						'show_open_close[show]: val',
						'show_open_close[hide]: ! val',
					)
				),
			),
			'icon_open' => array(
				'type' => 'icon',
				'label' => __( 'Open icon', 'so-widgets-bundle' ),
				'default' => 'fontawesome-plus',
				'state_handler' => array(
					'show_open_close[show]' => array( 'slideDown' ),
					'show_open_close[hide]' => array( 'slideUp' ),
				)
			),
			'icon_close' => array(
				'type' => 'icon',
				'label' => __( 'Close icon', 'so-widgets-bundle' ),
				'default' => 'fontawesome-minus',
				'state_handler' => array(
					'show_open_close[show]' => array( 'slideDown' ),
					'show_open_close[hide]' => array( 'slideUp' ),
				)
			),
			'open_close_icon_location' => array(
				'type' => 'radio',
				'label' => __( 'Open/close icon location', 'so-widgets-bundle' ),
				'options' => array(
					'left' => __( 'Left', 'so-widgets-bundle' ),
					'right' => __( 'Right', 'so-widgets-bundle' ),
				),
				'default' => 'right',
				'state_handler' => array(
					'show_open_close[show]' => array( 'slideDown' ),
					'show_open_close[hide]' => array( 'slideUp' ),
				)
			),
		) );
		$form_options['design']['fields']['heading']['fields'] = $heading_design_fields;
		
		$panels_design_fields = $form_options['design']['fields']['panels']['fields'];
		
		if ( array_key_exists( 'border_width', $panels_design_fields ) ) {
			$panels_design_fields['border_width']['type'] = 'multi-measurement';
			$panels_design_fields['border_width']['autofill'] = true;
			$panels_design_fields['border_width']['measurements'] = array(
				'top' => array(
					'label' => __( 'Top', 'siteorigin-premium' ),
					'units' => $border_width_units,
				),
				'right' => array(
					'label' => __( 'Right', 'siteorigin-premium' ),
					'units' => $border_width_units,
				),
				'bottom' => array(
					'label' => __( 'Bottom', 'siteorigin-premium' ),
					'units' => $border_width_units,
				),
				'left' => array(
					'label' => __( 'Left', 'siteorigin-premium' ),
					'units' => $border_width_units,
				),
			);
		}
		
		$position = array_key_exists( 'font_color', $panels_design_fields ) ? 'font_color' : count( $panels_design_fields );
		siteorigin_widgets_array_insert( $panels_design_fields, $position, array(
			'font_family' => array(
				'type' => 'font',
				'label' => __( 'Font', 'siteorigin-premium' ),
			),
			'font_size' => array(
				'type' => 'measurement',
				'label' => __( 'Font size', 'siteorigin-premium' ),
			),
		) );
		
		$position = array_key_exists( 'margin_bottom', $panels_design_fields ) ? 'margin_bottom' : count( $panels_design_fields );
		siteorigin_widgets_array_insert( $panels_design_fields, $position, array(
			'border_radius' => array(
				'type' => 'multi-measurement',
				'label' => __( 'Border radius', 'so-widgets-bundle' ),
				'autofill' => true,
				'measurements' => array(
					'top_left' => __( 'Top left', 'so-widgets-bundle' ),
					'top_right' => __( 'Top right', 'so-widgets-bundle' ),
					'bottom_right' => __( 'Bottom right', 'so-widgets-bundle' ),
					'bottom_left' => __( 'Bottom left', 'so-widgets-bundle' ),
				),
			),
			'padding' => array(
				'type' => 'multi-measurement',
				'label' => __( 'Padding', 'so-widgets-bundle' ),
				'autofill' => true,
				'default' => '15px 30px 15px 30px',
				'measurements' => array(
					'top' => __( 'Top', 'so-widgets-bundle' ),
					'right' => __( 'Right', 'so-widgets-bundle' ),
					'bottom' => __( 'Bottom', 'so-widgets-bundle' ),
					'left' => __( 'Left', 'so-widgets-bundle' ),
				),
			),
		) );
		
		$form_options['design']['fields']['panels']['fields'] = $panels_design_fields;

		return $form_options;
	}
	
	public function add_less_variables( $less_variables, $instance, $widget ) {
		$heading = $instance['design']['heading'];
		$panels = $instance['design']['panels'];
		
		if ( ! empty( $heading['title_align'] ) ) {
			$less_variables['heading_title_align'] = $heading['title_align'];
		}
		
		if ( ! empty( $heading['title_font_family'] ) ) {
			$font = siteorigin_widget_get_font( $heading['title_font_family'] );
			$less_variables['heading_title_font_family'] = $font['family'];
			if ( ! empty( $font['weight'] ) ) {
				$less_variables['heading_title_font_weight'] = $font['weight'];
			}
		}
		
		if ( ! empty( $heading['title_font_size'] ) ) {
			$less_variables['heading_title_font_size'] = $heading['title_font_size'];
		}
		
		if ( ! empty( $heading['title_text_transform'] ) ) {
			$less_variables['heading_title_text_transform'] = $heading['title_text_transform'];
		}
		
		if ( isset( $heading['show_open_close_icon'] ) ) {
			$less_variables['show_open_close_icon'] = empty( $heading['show_open_close_icon'] ) ? 'false' : 'true';
		}
		
		if ( ! empty( $heading['open_close_icon_location'] ) ) {
			$less_variables['open_close_location'] = $heading['open_close_icon_location'];
		}
		
		if ( ! empty( $heading['border_radius'] ) ) {
			$less_variables['heading_border_radius'] = $heading['border_radius'];
		}
		
		if ( ! empty( $heading['padding'] ) ) {
			$less_variables['heading_padding'] = $heading['padding'];
		}
		
		if ( ! empty( $panels['font_family'] ) ) {
			$less_variables['panels_font_family'] = $panels['font_family'];
			
			$font = siteorigin_widget_get_font( $panels['font_family'] );
			$less_variables['panels_font_family'] = $font['family'];
			if ( ! empty( $font['weight'] ) ) {
				$less_variables['panels_font_weight'] = $font['weight'];
			}
		}
		
		if ( ! empty( $panels['font_size'] ) ) {
			$less_variables['panels_font_size'] = $panels['font_size'];
		}
		
		if ( ! empty( $panels['border_radius'] ) ) {
			$less_variables['panels_border_radius'] = $panels['border_radius'];
		}
		
		if ( ! empty( $panels['padding'] ) ) {
			$less_variables['panels_padding'] = $panels['padding'];
		}
		
		return $less_variables;
	}
	
	public function add_template_variables( $template_variables, $instance, $args, $widget ) {
		$heading = $instance['design']['heading'];
		$position_key = null;
		if ( ! empty( $heading['title_icon_location'] ) ) {
			$position_key = 'before_title';
			if ( $heading['title_icon_location'] == 'right' ) {
				$position_key = 'after_title';
			}
		}
		
		foreach ( $template_variables['panels'] as &$panel ) {
			if ( ! empty( $panel['title_icon'] ) ) {
				$panel[ $position_key ] = siteorigin_widget_get_icon( $panel['title_icon'] );
			}
		}
		
		return $template_variables;
	}
	
	public function add_google_font_fields( $fields, $instance, $widget ) {
		if ( ! empty( $instance['design']['heading']['title_font_family'] ) ) {
			$fields[] = $instance['design']['heading']['title_font_family'];
		}
		if ( ! empty( $instance['design']['panels']['font_family'] ) ) {
			$fields[] = $instance['design']['panels']['font_family'];
		}
		
		return $fields;
	}
	
	public function add_front_end_params( $params, $instance, $widget ) {
		
		$params['max-open-panels'] = empty( $instance['max_open_panels'] ) ? 0 : $instance['max_open_panels'];
		$params['use-anchor-tags'] = empty( $instance['use_anchor_tags'] ) ? 'false' : 'true';
		$params['initial-scroll-panel'] = empty( $instance['initial_scroll_panel'] ) ? 0 : $instance['initial_scroll_panel'];
		
		return $params;
	}
	
	public function render_panel_content( $content, $panel, $instance ) {
		
		if ( ! empty( $panel['content_type'] ) && $panel['content_type'] === 'layout' ) {
			if( function_exists( 'siteorigin_panels_render' ) ) {
				$content_builder_id = substr( md5( json_encode( $panel['content_layout'] ) ), 0, 8 );
				$content =  siteorigin_panels_render( 'w' . $content_builder_id, true, $panel['content_layout'] );
			}
			else {
				$content = __( 'This field requires Page Builder.', 'siteorigin-premium' );
			}
		}
		
		return $content;
	}

	public function modify_instance( $instance, $widget ) {
		if ( ! empty( $instance['design']['heading']['title_icon'] ) ) {
			foreach ( $instance['panels'] as &$panel ) {
				$panel['title_icon'] = $instance['design']['heading']['title_icon'];
			}
			unset( $instance['design']['heading']['title_icon'] );
		}

		return $instance;
	}
}

SiteOrigin_Premium_Plugin_Accordion::single();
