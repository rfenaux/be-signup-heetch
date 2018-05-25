<?php

/**
 * Class SiteOrigin_Premium_Admin_Notices
 */
class SiteOrigin_Premium_Admin_Notices {

	/**
	 * Create the instance of the Premium Admin notices
	 */
	function __construct(){
		add_action( 'admin_notices', array($this, 'display_admin_notices') );
		add_action( 'wp_ajax_so_premium_dismiss', array($this, 'dismiss_action') );
	}

	static function single(){
		static $single;
		return empty( $single ) ? $single = new self() : $single;
	}

	function display_admin_notices(){
		$displayed = $this->get_displayed_notices();
		if( !empty($displayed) ) {
			$notices = include( SiteOrigin_Premium::dir_path(__FILE__) . 'notices.php' );
			foreach( $displayed as $id ) {
				if( empty($notices[$id]) ) continue;

				$dismiss_url = wp_nonce_url( add_query_arg( array(
					'action' => 'so_premium_dismiss',
					'id' => $id,
				), admin_url('admin-ajax.php') ), 'so_premium_dismiss');

				$notice = str_replace(
					array(
						'%renew%',
						'%purchase%',
					),
					array(
						SiteOrigin_Premium_Updater::single()->get_renew_url(),
						'https://siteorigin.com/premium/'
					),
					$notices[$id]
				);

				?>
				<div id="siteorigin-premium-notice" class="updated settings-error notice">
					<p><strong><?php echo $notice ?></strong></p>
					<a href="<?php echo $dismiss_url ?>" class="siteorigin-notice-dismiss"></a>
				</div>
				<?php

				wp_enqueue_script( 'siteorigin-premium-notice', SiteOrigin_Premium::dir_url( ) . 'js/notices' . SITEORIGIN_PREMIUM_JS_SUFFIX . '.js', array('jquery'), SITEORIGIN_PREMIUM_VERSION );
				wp_enqueue_style( 'siteorigin-premium-notice', SiteOrigin_Premium::dir_url( ) . 'css/notices.css' );

				break;
			}
		}
	}

	function activate_message( $id ){
		$active = get_option( 'siteorigin_premium_active_notices', array() );
		$active[$id] = true;
		update_option( 'siteorigin_premium_active_notices', $active );
	}

	function dismiss_action(){
		check_ajax_referer('so_premium_dismiss');

		$dismissed = get_option( 'siteorigin_premium_dismissed_notices', array() );
		$id = sanitize_text_field( $_GET['id'] );
		$dismissed[$id] = array(
			'expires' => 365*86400 + time()
		);
		update_option( 'siteorigin_premium_dismissed_notices', $dismissed );

		exit();
	}

	/**
	 * Get a list of notices that we should be displaying
	 *
	 * @return array
	 */
	function get_displayed_notices(){
		$active = get_option( 'siteorigin_premium_active_notices', array() );
		$dismissed = get_option( 'siteorigin_premium_dismissed_notices', array() );

		foreach( $dismissed as $id => $attr ) {
			if( $attr['expires'] > 0 && $attr['expires'] < time() ) {
				unset($dismissed[$id]);
				update_option( 'siteorigin_premium_dismissed_notices', $dismissed );
			}
			else {
				unset($active[$id]);
			}
		}

		return array_keys($active);
	}

}

SiteOrigin_Premium_Admin_Notices::single();
