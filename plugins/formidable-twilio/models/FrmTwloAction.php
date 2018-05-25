<?php

class FrmTwloAction extends FrmFormAction {

	function __construct() {
		$action_ops = array(
		    'classes'   => 'frm_sms_icon frm_icon_font',
            'limit'     => 99,
            'active'    => true,
            'priority'  => 41,
			'event'     => array( 'create', 'update', 'delete' ),
		);
		
		$this->FrmFormAction( 'twilio', __( 'Send Twilio SMS', 'frmtwlo' ), $action_ops );
	}

	function form( $form_action, $args = array() ) {
	    extract($args);
	    $frm_twlo_settings = new FrmTwloSettings();
	    
	    include(FrmTwloAppController::path() .'/views/_twilio_action.php');
	}
	
	function get_defaults() {
	    return array(
            'to'   => '',
            'from'      => '',
            'message'   => '',
        );
	}
}
