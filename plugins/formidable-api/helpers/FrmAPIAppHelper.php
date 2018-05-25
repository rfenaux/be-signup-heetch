<?php

class FrmAPIAppHelper{

    public static function generate($chars = 4, $num_segments = 4) {
        $tokens = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $key_string = '';

        for ($i = 0; $i < $num_segments; $i++){
            $segment = '';

            for ($j = 0; $j < $chars; $j++){
                $segment .= $tokens[rand(0, 35)];
            }

            $key_string .= $segment;

            if ($i < ($num_segments - 1))
                $key_string .= '-';
        }

        return $key_string;
    }

    public static function is_frm_route() {
        return ( strpos( $_SERVER['REQUEST_URI'], '/frm/' ) === false ) ? false : true;
    }

	/**
	 * @since 1.02
	 * @return string
	 */
	public static function path(){
		return dirname(dirname(__FILE__));
	}

	/**
	 * @since 1.02
	 * @return string
	 */
	public static function folder_name(){
		return basename( self::path() );
	}

	/**
	 * @since 1.02
	 * @return string
	 */
	public static function plugin_url() {
		return plugins_url( '', self::path() . '/formidable-api.php' );
	}

	/**
	 * FrmProXMLHelper::get_date is deprecated
	 * @since 1.04
	 */
	public static function format_date( $field, &$date ) {
		if ( is_callable( 'FrmFieldFactory::get_field_object' ) ) {
			$field_obj = FrmFieldFactory::get_field_object( $field );
			$date = $field_obj->get_import_value( $date );
		} else {
			$date = FrmProXMLHelper::get_date( $date );
		}
	}
}
