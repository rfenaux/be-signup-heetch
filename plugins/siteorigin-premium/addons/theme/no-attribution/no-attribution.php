<?php
/*
Plugin Name: SiteOrigin No Attribution
Description: Removes SiteOrigin attribution from your website footer.
Version: 1.0.0
Author: SiteOrigin
Author URI: https://siteorigin.com
License: GPL3
License URI: https://www.gnu.org/licenses/gpl-3.0.txt
Documentation: https://siteorigin.com/premium-documentation/theme-addons/no-attribution/
Tags: Theme
Video: jszuqjwc2u
*/

class SiteOrigin_Premium_Theme_No_Attribution {
	function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	public static function single() {
		static $single;
		return empty( $single ) ? $single = new self() : $single;
	}

	public function init(){
		$support = get_theme_support('siteorigin-premium-no-attribution');
		if( !empty($support) ) {
			$support = $support[0];
			if( $support['enabled'] && !empty( $support['filter'] ) ) {
				add_filter( $support['filter'], '__return_false' );
			}
		}
	}
}

SiteOrigin_Premium_Theme_No_Attribution::single();
