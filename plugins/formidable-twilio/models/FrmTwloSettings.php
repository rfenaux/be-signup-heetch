<?php

class FrmTwloSettings{
    private $option_name = 'frm_twlo_options';
    public $settings;

    function __construct() {
        $settings = get_option($this->option_name);
        
        if ( is_object($settings) || is_array($settings) ) {
            $this->settings = (array) $settings;
        }
        
        $this->set_default_options(); // Sets defaults for unset options
    }
    
    function default_options(){
        return array(
            'account_sid'   => '',
            'auth_token'    => '',
        );
    }
    
    function set_default_options(){
        $settings = $this->default_options();
        
        foreach ( $settings as $setting => $default ) {
            if ( ! isset($this->settings[$setting]) ) {
                $this->settings[$setting] = $default;
            }
        }
    }
    
    function update($params){
        $settings = $this->default_options();
        
        foreach ( $settings as $setting => $default ) {
			if ( isset( $params['frm_twlo_' . $setting ] ) ) {
				$this->settings[ $setting ] = sanitize_text_field( $params[ 'frm_twlo_' . $setting ] );
			}
        }
    }

    function store(){
        // Save the posted value in the database
        update_option( $this->option_name, $this->settings );
    }

}