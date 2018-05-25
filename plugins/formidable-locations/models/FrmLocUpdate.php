<?php

class FrmLocUpdate extends FrmAddon {

	public $plugin_file;
	public $plugin_name = 'Locations';
	public $version = '2.02';

	public function __construct() {
		$this->plugin_file = dirname( dirname( __FILE__ ) ) . '/us_locations.php';
		parent::__construct();
	}

	public static function load_hooks() {
		add_filter( 'frm_include_addon_page', '__return_true' );
		new FrmLocUpdate();
	}

}
