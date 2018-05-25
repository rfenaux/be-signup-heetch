<?php

class FrmTwloSettingsController{
    function __construct() {
		add_action( 'frm_add_settings_section', 'FrmTwloSettingsController::add_settings_section' );
        
        // 2.0 hooks
		add_action( 'frm_registered_form_actions', 'FrmTwloSettingsController::register_actions' );
		add_action( 'frm_create_twilio_action', 'FrmTwloSettingsController::migrate_to_2', 10, 2 );
        
        // < 2.0 fallback
		add_action( 'init', 'FrmTwloSettingsController::load_form_settings_hooks' );
    }
    
	/* Global Form settings */
    public static function add_settings_section($sections){
        $sections['twilio'] = array('class' => __CLASS__, 'function' => 'route');
        return $sections;
    }
    
    private static function display_form($errors=array(), $message=''){
        $frm_twlo_settings = new FrmTwloSettings();
        require(FrmTwloAppController::path() . '/views/form.php');
    }

    private static function process_form(){
        $frm_twlo_settings = new FrmTwloSettings();

        $errors = array();
        $frm_twlo_settings->update($_POST);

        if ( empty($errors) ) {
            $frm_twlo_settings->store();
			$message = __( 'Settings Saved', 'frmtwlo' );
        }

        self::display_form($errors, $message);
    }

    public static function route(){
		$action = FrmAppHelper::get_param( 'action', '', 'get', 'sanitize_title' );
		if ( $action == 'process-form' ) {
			return self::process_form();
		} else {
			return self::display_form();
		}
    }
    /* End Global form settings */
    
    public static function register_actions($actions) {
        $actions['twilio'] = 'FrmTwloAction';
        
        include_once(FrmTwloAppController::path() . '/models/FrmTwloAction.php');
        
        return $actions;
    }

	/**
	 * Migrate old settings into 2.0 form actions
	 */
	public static function migrate_to_2( $atts, $notification ) {
		$settings = array(
			'to'      => $atts['email_to'],
			'from'    => $notification['twfrom'],
			'message' => isset( $notification['email_message'] ) ? $notification['email_message'] : '',
			'event'   => $atts['event'],
		);

		$new_action = array(
			'post_type'    => FrmFormActionsController::$action_post_type,
			'post_excerpt' => 'twilio',
			'post_name'    => $atts['form_id'] .'_twilio_'. $atts['email_key'],
			'post_title'   => __( 'Send Twilio SMS', 'frmtwlo' ),
			'menu_order'   => $atts['form_id'],
			'post_status'  => 'publish',
			'post_content' => $settings,
		);

		// Switch field IDs and keys, if needed
		$new_action['post_content'] = FrmFieldsHelper::switch_field_ids( $new_action['post_content'] );
		$new_action['post_content']   = FrmAppHelper::prepare_and_encode( $new_action['post_content'] );

		$exists = get_posts( array(
			'name'          => $new_action['post_name'],
			'post_type'     => $new_action['post_type'],
			'post_status'   => $new_action['post_status'],
			'numberposts'   => 1,
		) );

		if ( empty( $exists ) ) {
			wp_insert_post( $new_action );
		}
	}

    /* Start v2.0 fallback */
    public static function load_form_settings_hooks() {
        $frm_version = is_callable('FrmAppHelper::plugin_version') ? FrmAppHelper::plugin_version() : 0;
        
        if ( version_compare($frm_version, '1.07.20', '>') ) {
            return;
        }
        
        // load hooks for < v2.0
		add_action( 'frm_add_form_option_section', 'FrmTwloSettingsController::options_js' );
		add_action( 'frm_additional_form_notification_options', 'FrmTwloSettingsController::options', 20, 2 );
    }

    public static function options_js(){
		_deprecated_function( __FUNCTION__, '1.02', 'Update your Formidable version' );
		include( FrmTwloAppController::path() . '/views/options_js.php' );
    }

    public static function options($values, $atts) {
		_deprecated_function( __FUNCTION__, '1.02', 'Update your Formidable version' );

        extract($atts); //$notification, $email_key
        $defaults = array(
            'twilio' => 0,
            'twfrom' => ''
        );
        
        $notification = array_merge($defaults, $notification);
        require(FrmTwloAppController::path() . '/views/options.php');
    }
    /* End v2.0 fallback */
    
}