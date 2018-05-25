<?php 
/*
Plugin Name: Formidable Multilingual
Description: Add multilingual support for Formidable
Plugin URI: http://formidablepro.com/
Author: Strategy11
Author URI: http://formidablepro.com
Version: 1.04
*/


function frm_wpml_autoloader($class_name) {
	$path = dirname(__FILE__);

	// Only load Frm classes here
	if ( $class_name != 'FormidableWPML' && ! preg_match('/^FrmWpml.+$/', $class_name) ) {
		return;
	}

	if ( preg_match('/^.+Helper$/', $class_name) ) {
		$path .= '/helpers/' . $class_name . '.php';
	} else if ( preg_match('/^.+Controller$/', $class_name) ) {
		$path .= '/controllers/'. $class_name .'.php';
	} else {
		$path .= '/models/'. $class_name .'.php';
	}

	if ( file_exists($path) ) {
		include($path);
	}
}

// Add the autoloader
spl_autoload_register('frm_wpml_autoloader');

// Load hooks
add_action( 'plugins_loaded', 'FrmWpmlHooksController::load_hooks', 10 );