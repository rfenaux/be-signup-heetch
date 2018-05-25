<?php
/*
Plugin Name: SiteOrigin Hero
Description: Additional settings and styles for the Widgets Bundle Hero widget.
Version: 1.0.0
Author: SiteOrigin
Author URI: https://siteorigin.com
License: GPL3
License URI: https://www.gnu.org/licenses/gpl-3.0.txt
Documentation: https://siteorigin.com/premium-documentation/plugin-addons/hero/
Tags: Widgets Bundle
Video: hgpxwwkynm
*/

class SiteOrigin_Premium_Plugin_Hero {
	
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
	
	
	/**
	 * Adds required filters for this addon.
	 */
	public function add_filters() {
		if ( class_exists( 'SiteOrigin_Widget_Hero_Widget' ) ) {
			add_filter( 'siteorigin_widgets_form_options_sow-hero', array( $this, 'admin_form_options' ), 10, 2 );
			add_filter( 'siteorigin_hero_frame_content', array( $this, 'apply_frame_animation' ), 10, 2 );
			add_action( 'siteorigin_widgets_render_preview_sow-hero', array( $this, 'register_scripts_for_preview' ) );
		}
	}
	
	/**
	 * Add form fields required by this addon.
	 *
	 * @param $form_options
	 * @param $widget
	 *
	 * @return mixed
	 */
	public function admin_form_options( $form_options, $widget ) {
		if ( empty( $form_options ) ) {
			return $form_options;
		}
		
		$frames_fields = $form_options['frames']['fields'];
		
		$position = array_key_exists( 'background', $frames_fields ) ? 'background' : count( $frames_fields );
		
		siteorigin_widgets_array_insert( $frames_fields, $position, array(
			'content_animation' => array(
				'type' => 'section',
				'label' => __( 'Content animation', 'siteorigin-premium' ),
				'fields' => array(
					'type' => array(
						'type' => 'select',
						'label' => __( 'Animation', 'siteorigin-premium' ),
						'options' => include plugin_dir_path( __FILE__ ) . 'inc/animation-types.php',
					),
					'event' => array(
						'type' => 'select',
						'label' => __( 'Animation event', 'siteorigin-premium' ),
						'options' => array(
							'enter' => __( 'Element enters screen', 'siteorigin-premium' ),
							'in'    => __( 'Element in screen', 'siteorigin-premium' ),
							'load'  => __( 'Page load', 'siteorigin-premium' ),
							'hover' => __( 'On hover', 'siteorigin-premium' ),
							'slide_display' => __( 'Frame display', 'siteorigin-premium' ),
						),
						'default' => 'slide_display',
					),
					'screen_offset' => array(
						'type' => 'number',
						'label' => __( 'Screen offset', 'siteorigin-premium' ),
						'default' => 0,
						'description' => __( 'Distance, in pixels, the content must be above the bottom of the screen before animating in.', 'siteorigin-premium' ),
					),
					'duration' => array(
						'type' => 'number',
						'label' => __( 'Animation speed', 'siteorigin-premium' ),
						'default' => 1,
						'description' => __( 'Time, in seconds, that the incoming animation lasts.', 'siteorigin-premium' ),
					),
					'hide' => array(
						'label' => __( 'Hide before animation', 'siteorigin-premium' ),
						'type' => 'checkbox',
						'default' => true,
						'description' => __( 'Hide the element before animating.', 'siteorigin-premium' ),
					),
					'delay' => array(
						'type' => 'number',
						'label' => __( 'Animation delay', 'siteorigin-premium' ),
						'default' => 0,
						'description' => __( 'Time, in seconds, after the event to start the animation.', 'siteorigin-premium' ),
					),
				),
			),
		) );
		
		$form_options['frames']['fields'] = $frames_fields;
		
		return $form_options;
	}
	
	/**
	 * Filter function to output HTML, JS and CSS required for animating content.
	 *
	 * @param $content
	 * @param $frame
	 *
	 * @return string
	 */
	function apply_frame_animation( $content, $frame ) {
		$animation = $frame['content_animation'];
		$animation_wrapper = $content;
		if ( ! empty( $animation['type'] ) ) {
			$animation_wrapper = '<div data-so-animation="' . esc_attr( json_encode( array(
				'animation' => $animation[ 'type' ],
				'duration' => isset( $animation[ 'duration' ] ) ? floatval( $animation[ 'duration' ] ) : 1 ,
				'hide' => !empty( $animation[ 'hide' ] ) ? 1 : 0,
				'delay' => isset( $animation[ 'delay' ] ) ? floatval( $animation[ 'delay' ] ) : 0 ,
				'event' => isset( $animation[ 'event' ] ) ? $animation[ 'event' ] : 'enter' ,
				'offset' => isset( $animation[ 'screen_offset' ] ) ? intval( $animation[ 'screen_offset' ] ) : 0 ,
			) ) ) . '"';
			
			$animation_wrapper .= '>' . $content . '</div>';
			
			wp_enqueue_script( 'siteorigin-premium-animate' );
			wp_enqueue_style( 'siteorigin-premium-animate' );
		}
		
		return $animation_wrapper;
	}
	
	function register_scripts_for_preview( $widget ) {
		$so_premium = SiteOrigin_Premium::single();
		$so_premium->register_common_scripts();
	}

}

SiteOrigin_Premium_Plugin_Parallax_Sliders::single();
