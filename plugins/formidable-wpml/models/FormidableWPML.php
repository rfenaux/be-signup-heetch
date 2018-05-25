<?php

class FormidableWPML extends FrmWpmlAppController{

	function __construct() {
		_deprecated_function( 'The FormidableWPML class', '1.05', 'the FrmWpmlAppController class' );
	}

	public static function setup() {
		_deprecated_function( __FUNCTION__, '1.04', 'FrmWpmlAppController::include_updater' );
		FrmWpmlAppController::include_updater();
	}
}
