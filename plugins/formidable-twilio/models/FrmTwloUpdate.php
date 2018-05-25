<?php

class FrmTwloUpdate extends FrmAddon {
	public $plugin_file;
	public $plugin_name = 'Twilio';
	public $download_id = 168460;
	public $version = '1.08';

	public function __construct() {
		$this->plugin_file = dirname( dirname( __FILE__ ) ) . '/formidable-twilio.php';
		parent::__construct();
	}

	public static function load_hooks() {
		add_filter( 'frm_include_addon_page', '__return_true' );
		new FrmTwloUpdate();
	}

}
