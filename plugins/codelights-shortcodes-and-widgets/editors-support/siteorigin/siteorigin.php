<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

add_filter( 'siteorigin_panels_widgets', 'cl_siteorigin_panels_widgets' );
function cl_siteorigin_panels_widgets( $widgets ) {
	$config = cl_config( 'elements', array() );
	foreach ( $config as $name => $elm ) {
		if ( ! isset( $elm['widget_php_class'] ) OR empty( $elm['widget_php_class'] ) ) {
			$elm['widget_php_class'] = 'CL_Widget_' . ucfirst( preg_replace( '~^cl\-~', '', $name ) );
		}
		if ( empty( $widgets[ $elm['widget_php_class'] ] ) ) {
			continue;
		}
		$widgets[ $elm['widget_php_class'] ]['groups'] = array( 'codelights' );
		$widgets[ $elm['widget_php_class'] ]['icon'] = 'icon-' . $name;
	}

	return $widgets;
}

function cl_siteorigin_icons_style() {
	echo '<style type="text/css" id="cl_siteorigin_icons_style">';
	foreach ( cl_config( 'elements', array() ) as $name => $elm ) {
		if ( isset( $elm['icon'] ) AND ! empty( $elm['icon'] ) ) {
			echo '.so-panels-dialog .widget-icon.icon-' . $name . ' {';
			echo '-webkit-background-size: 20px 20px;';
			echo 'background-size: 20px 20px;';
			echo 'background-image: url(' . $elm['icon'] . ');';
			echo '}';
		}
	}
	echo '}';
	echo '</style>';
}

add_filter( 'siteorigin_panels_widget_dialog_tabs', 'cl_siteorigin_panels_widget_dialog_tabs', 20 );
function cl_siteorigin_panels_widget_dialog_tabs( $tabs ) {
	$tabs[] = array(
		'title' => 'CodeLights',
		'filter' => array(
			'groups' => array( 'codelights' ),
		),
	);

	return $tabs;
}

add_action( 'admin_enqueue_scripts', 'cl_admin_enqueue_siteorigin_scripts' );
function cl_admin_enqueue_siteorigin_scripts() {
	if ( ! function_exists( 'siteorigin_panels_setting' ) ) {
		return;
	}
	// Embedding the file only where siteorigin editor can be used: post types with wysiwyg / widgets page
	global $post_type, $cl_uri, $cl_version;
	$screen = get_current_screen();
	$is_widgets = ( $screen->base == 'widgets' );
	$is_customizer = ( $screen->base == 'customize' );
	$siteorigin_post_types = siteorigin_panels_setting( 'post-types' );
	$is_siteorigin_editor = is_array( $siteorigin_post_types ) AND in_array( $post_type, $siteorigin_post_types );

	if ( $is_widgets OR $is_customizer OR $is_siteorigin_editor ) {
		wp_enqueue_script( 'cl-siteorigin', $cl_uri . '/editors-support/siteorigin/siteorigin.js', array( 'jquery' ), $cl_version, TRUE );
		// Icons
		add_action( 'admin_head', 'cl_siteorigin_icons_style' );
	}
}

// Disabling SiteOrigin Panels cache, as it doesn't work well with CodeLights
// I did my best in order to keep it, but due to limited time I cannot keep debugging SiteOrigin_Panels_Cache_Renderer further. If you know how to fix this issue in a proper way, plz let me know!
add_filter( 'siteorigin_panels_use_cached', '__return_false' );
add_filter( 'siteorigin_panels_settings_fields', 'cl_hide_siteorigin_cache_field', 21 );
function cl_hide_siteorigin_cache_field( $fields ) {
	if ( isset( $fields['content'] ) AND isset( $fields['content']['fields'] ) AND isset( $fields['content']['fields']['cache-content'] ) ) {
		unset( $fields['content']['fields']['cache-content'] );
	}

	return $fields;
}