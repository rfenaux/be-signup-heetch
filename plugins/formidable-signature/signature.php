<?php
/*
Plugin Name: Formidable Signature field
Description: Add a signature field to your forms
Version: 1.11
Plugin URI: http://formidablepro.com
Author URI: http://strategy11.com
Author: Strategy11
Text domain: frmsig
*/

function frm_sig_autoloader($class_name) {
	$path = dirname(__FILE__);

	// Only load Frm classes here
	if ( ! preg_match('/^FrmSig.+$/', $class_name) ) {
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
spl_autoload_register('frm_sig_autoloader');

// Load hooks
FrmSigHooksController::load_hooks();
