<?php
/**
 * Extra Functions
 *
 * Collections of extra functions to avoid repeatition
 *
 * @copyright   Copyright (c) 2016, Jeffrey Carandang
 * @since       4.0
 */

function widgetopts_sanitize_array( &$array ) {
    foreach ($array as &$value) {
        if( !is_array($value) ) {
			// sanitize if value is not an array
            $value = sanitize_text_field( $value );
		}else{
			// go inside this function again
            widgetopts_sanitize_array($value);
		}
    }

    return $array;
}

function widgetopts_is_checked( $array, $key ){
	return ( isset( $array[$key] ) && '1' == $array[$key] ) ? 'checked="checked"' : '';
}

/*
 * Check if http or https available on link
 */
function widgetopts_addhttp($url) {
    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
        $url = "http://" . $url;
    }
    return $url;
}

//check if date is on the given range
function widgetopts_date_in_range($start, $end, $date){
    // Convert to timestamp
    $start_ts   = strtotime($start);
    $end_ts     = strtotime($end);
    $user_ts    = strtotime($date);

    // Check that user date is between start & end
    return (($user_ts >= $start_ts) && ($user_ts <= $end_ts));
}

//map widgets
function widgetopts_map_widgets() {
	$sidebars_widgets = wp_get_sidebars_widgets();
	$widgets_map = array();
	if ( ! empty( $sidebars_widgets ) )
		foreach( $sidebars_widgets as $position => $widgets )
			if( ! empty( $widgets) )
				foreach( $widgets as $widget )
					$widgets_map[$widget] = $position;

	return $widgets_map;
}

/**
 * Register Global Variables for easier access
 *
 *
 * @since 5.0
 * @return array
 */

function widgetopts_global_taxonomies() {
	$taxonomies = get_option( 'widgetopts_global_taxonomies' );

	if( empty( $taxonomies ) ) {

        $tax_args = array(
          'public'   => true
        );
        $tax_output     = 'objects'; // or objects
        $tax_operator   = 'and'; // 'and' or 'or'
        $taxonomies     = get_taxonomies( $tax_args, $tax_output, $tax_operator );
        unset( $taxonomies['post_format'] );

        // Let's let devs alter that value coming in
        $taxonomies = apply_filters( 'widgetopts_update_global_taxonomies', $taxonomies );
        update_option( 'widgetopts_global_taxonomies', $taxonomies );

	}

	return apply_filters( 'widgetopts_get_global_taxonomies', $taxonomies );
}

function widgetopts_global_types() {
	$types = get_option( 'widgetopts_global_types' );

	if( empty( $types ) ) {

        $types  = get_post_types( array(
                               'public' => true,
                           ), 'object' );

       //remove default types
       foreach ( array( 'revision', 'attachment', 'nav_menu_item' ) as $unset ) {
           unset( $types[ $unset ] );
       }

       // Let's let devs alter that value coming in
       $types = apply_filters( 'widgetopts_update_global_types', $types );
       update_option( 'widgetopts_global_types', $types );

	}

	return apply_filters( 'widgetopts_get_global_types', $types );
}

function widgetopts_global_pages() {
	$pages = get_option( 'widgetopts_global_all_pages' );

    //old pages object
	// if( empty( $pages ) ) {
    //     $pages  = get_posts( array(
    //                             'post_type'     => 'page',
    //                             'post_status'   => 'publish',
    //                             'numberposts'   => -1,
    //                             'orderby'       => 'title',
    //                             'order'         => 'ASC',
    //                             'fields'        => array('ID', 'name')
    //                         ));
    //
    //     // Let's let devs alter that value coming in
    //     $pages = apply_filters( 'widgetopts_update_global_pages', $pages );
    //     update_option( 'widgetopts_global_pages', $pages );
	// }

    //create new pages object
    if( empty( $pages ) ) {
        global $wpdb;

        $pages  = $wpdb->get_results("SELECT ID, post_title, post_parent FROM $wpdb->posts WHERE post_type = 'page' AND post_status = 'publish' ORDER BY post_title ASC ");

        // Let's let devs alter that value coming in
        $pages = apply_filters( 'widgetopts_update_global_pages', $pages );
        update_option( 'widgetopts_global_all_pages', $pages );
	}

	return apply_filters( 'widgetopts_get_global_pages', $pages );
}

/*
Page Walker Class
*/
if( !class_exists( 'WidgetOpts_Pages_Checkboxes' ) ):
    class WidgetOpts_Pages_Checkboxes extends Walker_Page {

        function start_lvl( &$output, $depth = 0, $args = array() ) {
            $output .= "\n<div class='widgetopts-chldrn'>\n";
        }

        function end_lvl( &$output, $depth = 0, $args = array() ) {
            $output .= "</div>\n";
        }

        function start_el( &$output, $page, $depth = 0, $args = array(), $current_page = 0 ) {
            if ( $depth ){
                $indent = str_repeat( '&mdash; ', $depth );
            }else{
                $indent = '';
            }
                
        

            if ( '' === $page->post_title ) {
                $page->post_title = sprintf( __( '#%d (no title)', 'widget-options' ), $page->ID );
            }

            $pages_values = array();
            if( isset( $args['params']['visibility']['pages'] ) ){
                $pages_values = $args['params']['visibility']['pages'];
            }

            if( isset( $pages_values[ $page->ID ] ) && $pages_values[ $page->ID ] == '1' ){
                $checked = 'checked="checked"';
            }else{
                $checked = '';
            }

            $output .= '<p>' . $indent;

            $output .= '<input type="checkbox" name="'. $args['namespace'] .'[extended_widget_opts][visibility][pages]['. $page->ID .']" id="'. $args['id'] .'-opts-pages-'. $page->ID .'" value="1" '. $checked .'/>';

            $output .= '<label for="'. $args['id'] .'-opts-pages-'. $page->ID .'">'. $page->post_title .'</label>';
        }

        function end_el( &$output, $page, $depth = 0, $args = array() ) {
            $output .= "</p>\n";
        }

    }
endif;
?>
