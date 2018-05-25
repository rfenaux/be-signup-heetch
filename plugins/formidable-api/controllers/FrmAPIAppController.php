<?php
if(!defined('ABSPATH')) die('You are not allowed to call this page directly.');

class FrmAPIAppController {
    public static $timeout = 15;
	public static $v2_base = 'frm/v2';

    public static function load_hooks() {
		add_action( 'admin_init', 'FrmAPIAppController::include_updater' );
		register_activation_hook( FrmAPIAppHelper::folder_name() . '/formidable-api.php', 'FrmAPIAppController::install' );
		add_action( 'rest_api_init', 'FrmAPIAppController::create_initial_rest_routes', 0 );
		add_shortcode( 'frm-api', 'FrmAPIAppController::show_api_object' );

		if ( false !== strpos( $_SERVER['REQUEST_URI'], '/wp-json/frm/forms' ) || false !== strpos( $_SERVER['REQUEST_URI'], '/wp-json/frm/entries' ) ) {
			FrmAPIv1Controller::load_hooks();
			add_filter( 'frm_create_cookies', '__return_false' );
		}
    }

    public static function path(){
        return FrmAPIAppHelper::path();
    }

    public static function folder_name(){
        return FrmAPIAppHelper::folder_name();
    }

    public static function install() {
        $frmdb = new FrmAPIDb();
        $frmdb->upgrade();
    }

    public static function include_updater() {
		if ( class_exists( 'FrmAddon' ) ) {
			include( FrmAPIAppHelper::path() .'/models/FrmAPIUpdate.php' );
			FrmAPIUpdate::load_hooks();
		}
    }

	public static function create_initial_rest_routes() {
		add_filter( 'determine_current_user', 'FrmAPIAppController::set_current_user', 40 );
		add_filter( 'rest_authentication_errors', 'FrmAPIAppController::check_authentication', 50 );
		self::force_reauthentication();

		if ( ! class_exists('WP_REST_Controller') ) {
			include_once( FrmAPIAppHelper::path() . '/controllers/FrmAPITempController.php' );
		}

		$controller = new FrmAPIFieldsController();
		$controller->register_routes();

		$controller = new FrmAPIFormsController();
		$controller->register_routes();

		$controller = new FrmAPIEntriesController();
		$controller->register_routes();

		if ( class_exists('WP_REST_Posts_Controller') && FrmAppHelper::pro_is_installed() ) {
			$controller = new FrmAPIViewsController( 'frm_display' );
			$controller->register_routes();
		}
	}

	/**
    * Force reauthentication after we've registered our handler
    */
    public static function force_reauthentication() {
        if ( is_user_logged_in() ) {
            // Another handler has already worked successfully, no need to reauthenticate.
            return;
        }

        // Force reauthentication
        if ( defined('REST_REQUEST') && REST_REQUEST ) {
            $user_id = apply_filters( 'determine_current_user', false );
        	if ( $user_id ) {
        		wp_set_current_user( $user_id );
        	}
        }
    }

	public static function set_current_user($user_id) {
	    if ( !empty( $user_id) ) {
	        return $user_id;
	    }

	    global $frm_api_error;

	    if ( !isset( $_SERVER['PHP_AUTH_USER'] ) ){
            /*
            * php-cgi under Apache does not pass HTTP Basic user/pass to PHP by default
            * For this workaround to work, add this line to your .htaccess file:
            * RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
            */

			if ( isset( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ) && ! isset( $_SERVER['HTTP_AUTHORIZATION'] ) ) {
				$_SERVER['HTTP_AUTHORIZATION'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
			}

            if ( isset($_SERVER['HTTP_AUTHORIZATION']) && strlen($_SERVER['HTTP_AUTHORIZATION']) > 0 ) {
                list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
                if ( strlen($_SERVER['PHP_AUTH_USER']) == 0 || strlen($_SERVER['PHP_AUTH_PW']) == 0 ) {
                    unset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
                }
            }

            if ( !isset( $_SERVER['PHP_AUTH_USER'] ) ) {
                //$frm_api_error = array( 'code' => 'frm_missing_api', 'message' => __('You are missing an API key', 'frmapi') );
                return $user_id;
            }
		}

		// check if using api key
		$api_key = get_option('frm_api_key');
        $check_key = $_SERVER['PHP_AUTH_USER'];

        if ( $api_key != $check_key ) {
            $frm_api_error = array( 'code' => 'frm_incorrect_api', 'message'  => __('Your API key is incorrect', 'frmapi') );
            return $user_id;
        }

		$admins = new WP_User_Query( array( 'role' => 'Administrator', 'number' => 1, 'fields' => 'ID' ) );
		if ( empty($admins) ) {
		    $frm_api_error = array( 'code' => 'frm_missing_admin', 'message' => __('You do not have an administrator on this site', 'frmapi') );
		    return $user_id;
		}

		$user_ids = $admins->results;
		$user_id = reset($user_ids);

		$frm_api_error = 'success';

        return $user_id;
	}

	public static function check_authentication($result) {
    	if ( ! empty( $result ) ) {
    		return $result;
    	}

        // only return error if this is an frm route
        if ( ! FrmAPIAppHelper::is_frm_route() ) {
            return $result;
        }

    	global $frm_api_error;
	    if ( $frm_api_error && is_array($frm_api_error) ) {
	        return new WP_Error( $frm_api_error['code'], $frm_api_error['message'], array( 'status' => 403 ));
	    }

        if ( 'success' == $frm_api_error || is_user_logged_in() ) {
		    return true;
		}

		return $result;
	}

	public static function show_api_object( $atts ) {
		if ( ! isset( $atts['id'] ) || ! isset( $atts['url'] ) ) {
			return __( 'Please include id=# and url="yoururl.com" in your shortcode', 'frmapi' );
		}
		$atts['id'] = sanitize_title( $atts['id'] );
		$atts['type'] = sanitize_title( isset( $atts['type'] ) ? $atts['type'] : 'form' ) . 's';

		$container_id = 'frmapi-' . $atts['id'] . rand( 1000, 9999 );
		$url = trailingslashit( $atts['url'] ) . 'wp-json/frm/v2/' . $atts['type'] . '/' . $atts['id'];

		$get_params = $atts;
		if ( isset( $get_params['get'] ) ) {
			$pass_params = explode( ',', $get_params['get'] );
			foreach ( $pass_params as $pass_param ) {
				if ( isset( $_GET[ $pass_param ] ) ) {
					$get_params[ $pass_param ] = sanitize_text_field( $_GET[ $pass_param ] );
				}
			}
			unset( $get_params['get'] );
		}
		unset( $get_params['id'], $get_params['type'], $get_params['url'] );

		if ( $atts['type'] == 'forms' ) {
			$get_params['return'] = 'html';
		} else {
			$pass_params = array( 'frm-page-'. $atts['id'], 'frmcal-month', 'frmcal-year' );
			foreach ( $pass_params as $pass_param ) {
				$url_value = filter_input( INPUT_GET, $pass_param );
				if ( ! empty( $url_value ) ) {
					$get_params[ $pass_param ] = sanitize_text_field( $url_value );
				}
			}
		}

		if ( ! empty( $get_params ) ) {
			$url .= '?' . http_build_query( $get_params );
		}

		$form = '<div id="' . esc_attr( $container_id ) . '" class="frmapi-form" data-url="' . esc_url( $url ) . '"></div>';
		add_action( 'wp_footer', 'FrmAPIAppController::load_form_scripts');

		return $form;
	}

	public static function load_form_scripts() {
		$script = "jQuery(document).ready(function($){
var frmapi=$('.frmapi-form');
if(frmapi.length){
	for(var frmi=0,frmlen=frmapi.length;frmi<frmlen;frmi++){
		frmapiGetData($(frmapi[frmi]));
	}
}
});
function frmapiGetData(frmcont){
	jQuery.ajax({
		dataType:'json',
		url:frmcont.data('url'),
		success:function(json){
			frmcont.html(json.renderedHtml);
		}
	});
}";
		$script = str_replace( array( "\r\n", "\r", "\n", "\t", '' ), '', $script );
		echo '<script type="text/javascript">' . $script .'</script>';
	}

    public static function send_webhooks( $entry, $hook, $type = 'live' ) {
        if ( ! is_object( $entry ) ) {
            $entry = FrmEntry::getOne( $entry );
        }

        add_filter('frm_use_wpautop', '__return_false');

		$args = self::prepare_args( $entry, $hook, $type );
		$response = self::send_request( $args );
		$processed = self::process_response( $response );

		$log_args = array(
			'url' => $args['url'],
			'body' => $args['body'],
			'headers' => $args['headers'],
			'processed' => $processed,
			'entry' => $entry,
			'hook' => $hook,
			'response' => $response,
		);
		self::log_results( $log_args );
		do_action( 'frmapi_post_response', $response, $entry, $hook, array(
			'processed' => $processed, 'request' => $args['body'], 'url' => $args['url'],
		) );

        add_filter('frm_use_wpautop', '__return_true');
    }

	private static function get_body( $atts ) {
		$body = trim( $atts['hook']->post_content['data_format'] );
		$format = $atts['hook']->post_content['format'];

		if ( empty( $body ) && 'raw' != $format  ) {
			self::get_body_settings( $atts, $body );
		} elseif ( strpos( $body, '{' ) === 0 ) {
			// allow for non-json formats
			$body = FrmAppHelper::maybe_json_decode( $body );
		}

		return $body;
	}

	private static function get_body_settings( $atts, &$body ) {
		$body = $atts['hook']->post_content['data_fields'];
		$has_data = ( count( $body ) > 1 || $body[0]['key'] != '' );
		if ( $has_data ) {
			self::prepare_data( $body );
		} else {
			$body = self::get_entries_array( array( $atts['entry']->id ) );
		}
	}

	private static function prepare_data( &$body ) {
		$values = array();
		foreach ( $body as $value ) {
			if ( strpos( $value['key'], '|' ) ) {
				$keys = explode( '|', $value['key'] );
				self::unflatten_array( $keys, $value['value'], $values );
			} else {
				$values[ $value['key'] ] = $value['value'];
			}
		}
		$body = $values;
	}

	/**
	 * Turn piped key into nested array
	 * fields|name => array( 'fields' => array( 'name' => '' ) )
	 */
	private static function unflatten_array( $keys, $value, &$unflattened ) {
		$name = $keys;
		$name = reset( $name );

		if ( count( $keys ) == 1 ) {
			$unflattened[ $name ] = $value;
		} else {
			if ( ! isset( $unflattened[ $name ] ) ) {
				$unflattened[ $name ] = array();
			}

			$pos = array_search( $name, $keys );
			unset( $keys[ $pos ] );
			if ( ! empty( $keys ) ) {
				self::unflatten_array( $keys, $value, $unflattened[ $name ] );
			}
		}
	}

	private static function encode_data( &$body, $atts ) {
		if ( 'form' == $atts['format'] ) {
			$body = self::filter_shortcodes( $body, $atts );
			$body = http_build_query( $body );
		} else {
			if ( is_array( $body ) ) {
				$body = self::filter_shortcodes( $body, $atts );
				$body = json_encode( $body );
			} else {
				self::filter_shortcodes_in_json( $body, $atts[ 'entry' ] );
			}
		}
	}

	/**
	 * @since 1.03
	 *
	 * @param mixed $value
	 * @param array $atts
	 *
	 * @return mixed
	 */
	private static function filter_shortcodes( $value, $atts ) {
		if ( is_array( $value ) ) {
			foreach ( $value as $key => $single_value ) {
				$value[ $key ] = self::filter_shortcodes( $single_value, $atts );
			}
		} else {
			if ( strpos( $value, '[' ) === false ) {
				return $value;
			}

			$value = apply_filters( 'frm_content', $value, $atts['entry']->form_id, $atts['entry'] );
			$value = do_shortcode( $value );
		}

		return $value;
	}


	private static function filter_shortcodes_in_json( &$value, $entry ) {
		if ( strpos( $value, '[' ) === false ) {
			return;
		}

		add_filter( 'frmpro_fields_replace_shortcodes', 'FrmAPIAppController::replace_double_quotes', 99, 4 );

		$value = str_replace('[\/', '[/',$value); // allow end shortcodes to be processed
		$value = apply_filters( 'frm_content', $value, $entry->form_id, $entry );
		$value = do_shortcode( $value );
		$value = str_replace('[/', '[\/', $value); // if the end shortcodes are still present, escape them
		$value = str_replace( ' & ', ' %26 ', $value ); // escape &

		// Remove surrounding quotes from JSON arrays
		$value = str_replace( array( '"[{', '}]"' ), array( '[{', '}]' ), $value );

		remove_filter( 'frmpro_fields_replace_shortcodes', 'FrmAPIAppController::replace_double_quotes' );
	}

	/**
	 * Replace double quotes with single quotes to keep valid JSON
	 *
	 * @since 1.0rc4
	 * @param mixed $value
	 * @param string $tag
	 * @param array $atts
	 * @param object $field
	 * @return mixed
	 */
    public static function replace_double_quotes( $value, $tag, $atts, $field ) {
		if ( is_string( $value ) ) {
			$value = str_replace( '"', '\'', $value );

			//Double encode line breaks in paragraph fields
			$value = str_replace( "\r\n", "\\r\\n", $value );
		}

		return $value;
	}

	private static function content_type_header( $format ) {
		$content_types = array(
			'form' => 'application/x-www-form-urlencoded',
			'json' => 'application/json',
			'raw'  => 'application/json',
		);
		return $content_types[ $format ];
	}

	/**
	 * Prepare the arguments for an API request
	 *
	 * @since 1.03
	 *
	 * @param stdClass $entry
	 * @param stdClass $hook
	 * @param string $type
	 *
	 * @return array
	 */
	private static function prepare_args( $entry, $hook, $type ) {
		$body = self::get_body( compact( 'hook', 'entry' ) );
		self::encode_data( $body, array( 'format' => $hook->post_content['format'], 'entry' => $entry ) );

		// Prepare headers
		$headers = array(
			'Content-type' => self::content_type_header( $hook->post_content['format'] ),
		);
		if ( $type == 'test' ) {
			$headers['X-Hook-Test'] = 'true';
		}
		if ( ! empty( $hook->post_content['api_key'] ) ) {
			$api_key = self::prepare_basic_auth_key( $hook->post_content['api_key'] );
			$headers['Authorization'] = 'Basic ' . base64_encode( $api_key );
		}

		$url = self::filter_shortcodes( $hook->post_content['url'], compact('entry') );
		$method = empty( $hook->post_content['method'] ) ? 'POST' : $hook->post_content['method'];

		$args = array(
			'url' => $url,
			'headers' => $headers,
			'body' => $body,
			'method' => $method,
			'timeout'   => self::$timeout,
			'sslverify' => false,
		);

		// Second argument is for reverse compatibility
		return apply_filters( 'frm_api_request_args', $args, $args );
	}

	public static function send_request( $args ) {
		if ( ! isset( $args['url'] ) ) {
			return false;
		}

		$url = esc_url_raw( trim( $args['url'] ) );
		unset( $args['url'] );

		return wp_remote_post( $url, $args );
	}

	private static function process_response( $response ) {
		$body = wp_remote_retrieve_body( $response );
		$processed = array( 'message' => '', 'code' => 'FAIL' );
		if ( is_wp_error( $response ) ) {
			$processed['message'] = $response->get_error_message();
		} elseif ( $body == 'error' || is_wp_error( $body ) ) {
			$processed['message'] = __( 'You had an HTTP connection error', 'formidable-api' );
		} elseif ( isset( $response['response'] ) && isset( $response['response']['code'] ) ) {
			$processed['code'] = $response['response']['code'];
			$processed['message'] = $response['body'];
		}

		return $processed;
	}

	public static function log_results( $atts ) {
		if ( ! class_exists( 'FrmLog' ) ) {
			return;
		}

		$content = $atts['processed'];
		$message = isset( $content['message'] )  ? $content['message'] : '';

		$headers = '';
		self::array_to_list( $atts['headers'], $headers );

		$log = new FrmLog();
		$log->add( array(
			'title'   => __( 'API:', 'frmapi' ) . ' ' . $atts['hook']->post_title,
			'content' => (array) $atts['response'],
			'fields'  => array(
				'entry'   => $atts['entry']->id,
				'action'  => $atts['hook']->ID,
				'code'    => isset( $content['code'] ) ? $content['code'] : '',
				'message' => $message,
				'url'     => $atts['url'],
				'request' => $atts['body'],
				'headers' => $headers,
			),
		) );
	}

	private static function array_to_list( $array, &$list ) {
		foreach ( $array as $k => $v ) {
			$list .= "\r\n" . $k . ': ' . $v;
		}
	}

    public static function get_entries_array($ids) {
	    global $wpdb;

        $entry_array = array();

        // fetch 20 posts at a time rather than loading the entire table into memory
        while ( $next_set = array_splice( $ids, 0, 20 ) ) {
            $where = 'WHERE id IN (' . join( ',', $next_set ) . ')';
            $entries = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}frm_items $where" );
            unset($where);

            foreach ( $entries as $entry ) {
                $meta = FrmEntriesController::show_entry_shortcode(array(
                    'format' => 'array', 'include_blank' => true, 'id' => $entry->id,
                    'user_info' => false, //'entry' => $entry
                ));

                $entry_array[] = array_merge((array) $entry, $meta);

                unset($entry);
            }
        }

        return $entry_array;
	}

	public static function prepare_basic_auth_key( $api_key ) {
		$api_key = trim( $api_key );
		if ( ! empty( $api_key ) ) {
			$api_key = ( strpos( $api_key, ':' ) === false ) ? $api_key . ':x' : $api_key;
		}
		return $api_key;
	}
}
