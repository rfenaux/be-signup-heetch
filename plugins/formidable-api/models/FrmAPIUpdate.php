<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmAPIUpdate extends FrmAddon {

	public $plugin_file;
	public $plugin_name = 'Formidable API';
	public $download_id = 168072;
	public $version = '1.04';

	public function __construct() {
		$this->plugin_file = FrmAPIAppHelper::path() . '/formidable-api.php';
		parent::__construct();
	}

	public static function load_hooks() {
		add_filter( 'frm_include_addon_page', '__return_true' );
		new FrmAPIUpdate();
	}
}
