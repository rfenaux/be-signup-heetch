<?php
/*
Plugin Name: Formidable Twilio
Description: Accept SMS votes for a poll or send texts from forms
Version: 1.08
Plugin URI: http://formidablepro.com/
Author URI: http://strategy11.com
Author: Strategy11
*/


// Settings
require_once(dirname(__FILE__) .'/models/FrmTwloSettings.php');

//Controllers
require_once(dirname(__FILE__) .'/controllers/FrmTwloAppController.php');
require_once(dirname(__FILE__) .'/controllers/FrmTwloSettingsController.php');

$obj = new FrmTwloAppController();
$obj = new FrmTwloSettingsController();
