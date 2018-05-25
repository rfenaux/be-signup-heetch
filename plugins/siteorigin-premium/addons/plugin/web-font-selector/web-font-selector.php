<?php
/*
Plugin Name: SiteOrigin Web Font Selector
Description: Lets you choose web fonts inside SiteOrigin CSS.
Version: 1.0.0
Author: SiteOrigin
Author URI: https://siteorigin.com
License: GPL3
License URI: https://www.gnu.org/licenses/gpl-3.0.txt
Documentation: https://siteorigin.com/premium-documentation/plugin-addons/web-font-selector/
Tags: CSS
*/

class SiteOrigin_Premium_Plugin_Web_Font_Selector {

	function __construct() {
		add_filter( 'siteorigin_css_property_controllers', array( $this, 'modify_font_controls' ) );
		add_action( 'admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'), 20 );
	}

	static function single() {
		static $single;
		return empty( $single ) ? $single = new self() : $single;
	}

	function modify_font_controls($controls) {
		static $font_modules;

		$fonts = array();
		if ( empty( $font_modules ) ) {
			$font_modules = include dirname(__FILE__) . '/fonts/font_modules.php';
		}
		foreach ( $font_modules as $module_name => $module ) {
			$module['fonts'] = include dirname(__FILE__) . '/fonts/' . $module_name . '.php';
			$fonts[ $module_name ] = $module;
		}

		$ctrls = $controls['text']['controllers'];

		foreach ( $ctrls as $key => $ctrl ) {
			if ( $ctrl['type'] == 'font_select' ) {
				$ctrl['args']['modules'] = $fonts;
				$ctrls[ $key ] = $ctrl;
			}
		}
		$controls['text']['controllers'] = $ctrls;

		return $controls;
	}

	function enqueue_admin_scripts( $page ) {
		if ( $page != 'appearance_page_so_custom_css' ) {
			return;
		}

		// We'll use chosen for the font selector
		wp_enqueue_script(
			'siteorigin-premium-chosen',
			plugin_dir_url(__FILE__) . 'js/lib/chosen/chosen.jquery' . SITEORIGIN_PREMIUM_JS_SUFFIX . '.js',
			array('jquery'),
			'1.4.2'
		);
		wp_enqueue_style(
			'siteorigin-premium-chosen',
			plugin_dir_url(__FILE__) . 'js/lib/chosen/chosen.min.css',
			array(),
			'1.4.2'
		);

		wp_enqueue_script( 'web-font-loader', '//ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js', array(), null, true );

		wp_enqueue_script(
			'web-font-selector',
			plugin_dir_url(__FILE__) . 'js/web-font-selector' . ( defined( 'SITEORIGIN_PREMIUM_JS_SUFFIX' ) ? SITEORIGIN_PREMIUM_JS_SUFFIX : '' ) . '.js',
			array( 'jquery' ),
			SITEORIGIN_PREMIUM_VERSION,
			true
		);

		wp_enqueue_script(
			'font-select-control',
			plugin_dir_url(__FILE__) . 'js/font-select-control' . ( defined( 'SITEORIGIN_PREMIUM_JS_SUFFIX' ) ? SITEORIGIN_PREMIUM_JS_SUFFIX : '' ) . '.js',
			array( 'siteorigin-custom-css' ),
			SITEORIGIN_PREMIUM_VERSION,
			true
		);
	}
}

SiteOrigin_Premium_Plugin_Web_Font_Selector::single();
