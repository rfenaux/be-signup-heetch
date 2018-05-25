<?php

class FrmWpmlUpdate extends FrmAddon {
	public $plugin_file;
	public $plugin_name = 'WP Multilingual';
	public $download_id = 169998;
	public $version = '1.04';

	public function __construct() {
		$this->plugin_file = FrmWpmlAppHelper::plugin_path() . '/formidable-wpml.php';
		parent::__construct();
	}

	public static function load_hooks() {
		add_filter( 'frm_include_addon_page', '__return_true' );
		new FrmWpmlUpdate();
	}
}
