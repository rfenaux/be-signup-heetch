<?php
/*
Plugin Name: SiteOrigin Social Widgets
Description: Adds ability to set custom networks for the SiteOrigin Social Media Buttons widget.
Version: 1.0.0
Author: SiteOrigin
Author URI: https://siteorigin.com
License: GPL3
License URI: https://www.gnu.org/licenses/gpl-3.0.txt
Documentation: https://siteorigin.com/premium-documentation/plugin-addons/social-widgets/
Tags: Widgets Bundle
*/

class SiteOrigin_Premium_Plugin_Social_Widgets {

	function __construct() {
		include plugin_dir_path( __FILE__ ) . '/social-media-buttons/social-media-buttons.php';
	}

	public static function single(){
		static $single;
		return empty( $single ) ? $single = new self() : $single;
	}
}

SiteOrigin_Premium_Plugin_Social_Widgets::single();
