<?php
/*
Plugin Name: SiteOrigin Parallax Sliders
Description: Adds parallax background option to slider widgets.
Version: 1.0.0
Author: SiteOrigin
Author URI: https://siteorigin.com
License: GPL3
License URI: https://www.gnu.org/licenses/gpl-3.0.txt
Documentation: https://siteorigin.com/premium-documentation/plugin-addons/parallax-sliders/
Tags: Widgets Bundle
Video: x1rhgjlc7p
*/

class SiteOrigin_Premium_Plugin_Parallax_Sliders {

	function __construct(){
		add_filter( 'siteorigin_widgets_form_options_sow-slider', array( $this, 'widget_forms' ), 10, 2 );
		add_filter( 'siteorigin_widgets_form_options_sow-hero', array( $this, 'widget_forms' ), 10, 2 );
		add_filter( 'siteorigin_widgets_form_options_sow-layout-slider', array( $this, 'widget_forms' ), 10, 2 );

		add_filter( 'siteorigin_widgets_slider_wrapper_attributes', array( $this, 'slider_wrapper_attributes' ), 10, 3 );
		add_filter( 'siteorigin_widgets_slider_overlay_attributes', array( $this, 'slider_overlay_attributes' ), 10, 3 );
	}

	static function single(){
		static $single;
		return empty( $single ) ? $single = new self() : $single;
	}

	function slider_wrapper_attributes( $attributes, $frame, $background ){
		if( isset( $background['opacity'] ) && $background['opacity'] != 1 ) return $attributes;
		if( isset( $background['image-sizing'] ) && $background['image-sizing'] !== 'parallax' ) return $attributes;
		if( empty( $background['image'] ) || empty( $background['image-width'] ) || empty( $background['image-height'] ) ) return $attributes;

		$attributes['data-siteorigin-parallax'] = json_encode( array(
			'backgroundUrl' => $background['image'],
			'backgroundSize' => array(
				$background['image-width'],
				$background['image-height'],
			),
			'backgroundSizing' => 'scaled',
		) );
		wp_enqueue_script( 'siteorigin-parallax' );

		return $attributes;
	}

	function slider_overlay_attributes( $attributes, $frame, $background ){
		if( isset( $background['image-sizing'] ) && $background['image-sizing'] !== 'parallax' ) return $attributes;
		if( empty( $background['image'] ) || empty( $background['image-width'] ) || empty( $background['image-height'] ) ) return $attributes;

		$attributes['data-siteorigin-parallax'] = json_encode( array(
			'backgroundUrl' => $background['image'],
			'backgroundSize' => array(
				$background['image-width'],
				$background['image-height'],
			),
			'backgroundSizing' => 'scaled',
		) );
		wp_enqueue_script( 'siteorigin-parallax' );

		return $attributes;
	}

	function widget_forms( $form, $widget ){
		switch( get_class( $widget ) ) {
			case 'SiteOrigin_Widget_Hero_Widget':
			case 'SiteOrigin_Widget_LayoutSlider_Widget':
				if( isset( $form['frames']['fields']['background']['fields']['image_type']['options'] ) ) {
					$form['frames']['fields']['background']['fields']['image_type']['options']['parallax'] = __( 'Parallax', 'siteorigin-premium' );
				}
				break;

			case 'SiteOrigin_Widget_Slider_Widget' :
				if( isset( $form['frames']['fields']['background_image_type']['options'] ) ) {
					$form['frames']['fields']['background_image_type']['options']['parallax'] = __( 'Parallax', 'siteorigin-premium' );
				}
				break;
		}

		return $form;
	}

}

SiteOrigin_Premium_Plugin_Parallax_Sliders::single();
