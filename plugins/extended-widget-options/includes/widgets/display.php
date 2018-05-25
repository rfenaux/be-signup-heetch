<?php
/**
 * Handles Front-end Display
 *
 * @copyright   Copyright (c) 2015, Jeffrey Carandang
 * @since       1.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Handles widget_display_callback filter
 *
 * @since 1.0
 * @global $widget_options
 * @return $instance
 */

//check if function exists
if( !function_exists( 'widgetopts_display_callback' ) ):
    function widgetopts_display_callback( $instance, $widget, $args ){
        global $widget_options, $current_user;

        $hidden     = false;
        $opts       = ( isset( $instance[ 'extended_widget_opts-'. $widget->id ] ) ) ? $instance[ 'extended_widget_opts-'. $widget->id ] : array();
        $visibility = array( 'show' => array(), 'hide' => array() );
        $tax_opts   = ( isset( $widget_options['settings'] ) && isset( $widget_options['settings']['taxonomies_keys'] ) ) ? $widget_options['settings']['taxonomies_keys'] : array();

        // if( !empty( $tax_opts ) ){
        //     $new_tax_opts = array();
        //     foreach ( $tax_opts as $tax_k => $tax_v ) {
        //         $new_tax_opts[] = $tax_k;
        //     }
        //     $tax_opts = $new_tax_opts;
        // }

        //fix issue for widgets coming from shortcodes
        if( isset( $instance['from_shortcode'] ) && $instance['from_shortcode'] && isset( $instance['sc_id_base'] ) ){
            if( empty( $opts ) && isset( $instance[ 'extended_widget_opts-'. $instance['sc_id_base'] ] ) ){
                $opts = $instance[ 'extended_widget_opts-'. $instance['sc_id_base'] ];
            }
        }
        //roles
        if( isset( $widget_options['roles'] ) && 'activate' == $widget_options['roles'] && isset( $opts['roles'] ) ){
            $roles              = isset( $opts['roles'] ) ? $opts['roles'] : '';
            unset($roles['options']);
            $current_user->role = (isset( $current_user->caps ) && !empty( $current_user->caps )) ? array_keys( $current_user->caps ) : array( 0 => 'guests' );

            $roles_opts = isset( $opts['roles']['options'] ) ? $opts['roles']['options'] : 'hide';
            if( $roles_opts == 'hide' && array_key_exists( $current_user->role[0] , $roles) ){
                $hidden = true; //hide if exists on hidden roles
            }elseif( $roles_opts == 'show' && !array_key_exists( $current_user->role[0] , $roles) ){
                $hidden = true; //hide if doesn't exists on visible roles
            }

            //do return to bypass other tabs conditions
            $hidden = apply_filters( 'widget_options_visibility_roles', $hidden );
            if( $hidden ){
                return false;
            }
        }
        //end roles

        //wordpress pages
        $visibility         = isset( $opts['visibility'] ) ? $opts['visibility'] : array();
        $visibility_opts    = isset( $opts['visibility']['options'] ) ? $opts['visibility']['options'] : 'hide';

        $is_misc    = ( 'activate' == $widget_options['visibility'] && isset( $widget_options['settings']['visibility'] ) && isset( $widget_options['settings']['visibility']['misc'] ) ) ? true : false;
        $is_types   = ( 'activate' == $widget_options['visibility'] && isset( $widget_options['settings']['visibility'] ) && isset( $widget_options['settings']['visibility']['post_type'] ) ) ? true : false;
        $is_tax     = ( 'activate' == $widget_options['visibility'] && isset( $widget_options['settings']['visibility'] ) && isset( $widget_options['settings']['visibility']['taxonomies'] ) ) ? true : false;
        $is_inherit = ( 'activate' == $widget_options['visibility'] && isset( $widget_options['settings']['visibility'] ) && isset( $widget_options['settings']['visibility']['inherit'] ) ) ? true : false;

        if ( $is_misc && ( ( is_home() && is_front_page() ) || is_front_page() ) ) {
            if( isset( $visibility['misc']['home'] ) && $visibility_opts == 'hide' ){
                $hidden = true; //hide if checked on hidden pages
            }elseif( !isset( $visibility['misc']['home'] ) && $visibility_opts == 'show' ){
                $hidden = true; //hide if not checked on visible pages
            }

            //do return to bypass other conditions
            $hidden = apply_filters( 'widget_options_visibility_home', $hidden );
            if( $hidden ){
                return false;
            }
        }elseif ( $is_misc && is_home() ) { //filter for blog page
            if( isset( $visibility['misc']['blog'] ) && $visibility_opts == 'hide' ){
                $hidden = true; //hide if checked on hidden pages
            }elseif( !isset( $visibility['misc']['blog'] ) && $visibility_opts == 'show' ){
                $hidden = true; //hide if not checked on visible pages
            }

            //do return to bypass other conditions
            $hidden = apply_filters( 'widget_options_visibility_blog', $hidden );
            if( $hidden ){
                return false;
            }

        }elseif ( $is_tax && is_category() && is_array( $tax_opts ) && in_array( 'category', $tax_opts ) ) {
            if( !isset( $visibility['categories'] ) ){
                $visibility['categories'] = array();
            }

            $cat_lists = array();

            if( isset( $visibility['tax_terms']['category'] ) ){
                $cat_lists = $visibility['tax_terms']['category'];
            }elseif( isset( $visibility['categories'] ) ){
                $cat_lists = $visibility['categories'];
            }

            if( !isset( $visibility['taxonomies']['category'] ) && $visibility_opts == 'hide' && array_key_exists( get_query_var('cat') , $cat_lists ) ){
                $hidden = true; //hide if exists on hidden pages
            }elseif( !isset( $visibility['taxonomies']['category'] ) && $visibility_opts == 'show' && !array_key_exists( get_query_var('cat') , $cat_lists ) ){
                $hidden = true; //hide if doesn't exists on visible pages
            }elseif( isset( $visibility['taxonomies']['category'] ) && $visibility_opts == 'hide' ){
                $hidden = true; //hide to all categories
            }elseif( isset( $visibility['taxonomies']['category'] ) && $visibility_opts == 'show' ){
                $hidden = false; //hide to all categories
            }

            //do return to bypass other conditions
            $hidden = apply_filters( 'widget_options_visibility_categories', $hidden );
            if( $hidden ){
                return false;
            }
        }elseif ( $is_tax && is_tag() && is_array( $tax_opts ) && in_array( 'post_tag', $tax_opts ) ) {
            if( !isset( $visibility['tags'] ) ){
                $visibility['tags'] = array();
            }

            $tag_lists = ( isset( $visibility['tax_terms']['post_tag'] ) ) ? $visibility['tax_terms']['post_tag'] : array();

            if( !isset( $visibility['taxonomies']['post_tag'] ) && $visibility_opts == 'hide' && array_key_exists( get_query_var('tag_id') , $tag_lists ) ){
                $hidden = true; //hide if exists on hidden pages
            }elseif( !isset( $visibility['taxonomies']['post_tag'] ) && $visibility_opts == 'show' && !array_key_exists( get_query_var('tag_id') , $tag_lists ) ){
                $hidden = true; //hide if doesn't exists on visible pages
            }elseif( isset( $visibility['taxonomies']['post_tag'] ) && $visibility_opts == 'hide' ){
                $hidden = true; //hide to all tags
            }elseif( isset( $visibility['taxonomies']['post_tag'] ) && $visibility_opts == 'show' ){
                $hidden = false; //hide to all tags
            }

            //do return to bypass other conditions
            $hidden = apply_filters( 'widget_options_visibility_tags', $hidden );
            if( $hidden ){
                return false;
            }
        }elseif ( $is_tax && is_tax() ) {
            $term = get_queried_object();
            $term_lists = array();

            if( isset( $visibility['tax_terms'] ) && isset( $visibility['tax_terms'][ $term->taxonomy ] ) ){
                $term_lists = $visibility['tax_terms'][ $term->taxonomy ];
            }

            if( isset( $visibility['taxonomies'] ) && !isset( $visibility['taxonomies'][ $term->taxonomy ] ) && $visibility_opts == 'hide' && array_key_exists( $term->term_id , $term_lists ) ){
                $hidden = true; //hide if exists on hidden pages
            }elseif( isset( $visibility['taxonomies'] ) && !isset( $visibility['taxonomies'][ $term->taxonomy ] ) && $visibility_opts == 'show' && !array_key_exists( $term->term_id , $term_lists ) ){
                $hidden = true; //hide if doesn't exists on visible pages
            }elseif( isset( $visibility['taxonomies'] ) &&  isset( $visibility['taxonomies'][ $term->taxonomy ] ) && $visibility_opts == 'hide' ){
                $hidden = true; //hide to all tags
            }elseif( isset( $visibility['taxonomies'] ) && isset( $visibility['taxonomies'][ $term->taxonomy ] ) && $visibility_opts == 'show' ){
                $hidden = false; //hide to all tags
            }elseif( isset( $visibility['tax_terms'] ) && isset( $visibility['tax_terms'][ $term->taxonomy ] ) && $visibility_opts == 'hide' && array_key_exists( $term->term_id , $term_lists ) ){
                $hidden = true; //hide if exists on hidden pages
            }elseif( isset( $visibility['tax_terms'] ) && isset( $visibility['tax_terms'][ $term->taxonomy ] ) && $visibility_opts == 'show' && array_key_exists( $term->term_id , $term_lists ) ){
                $hidden = false; //hide if doesn't exists on visible pages
            }elseif( !isset( $visibility['taxonomies'] ) && $visibility_opts == 'show' ){
                $hidden = true; //hide if checked on hidden pages
            }

            //do return to bypass other conditions
            $hidden = apply_filters( 'widget_options_visibility_taxonomies', $hidden );
            if( $hidden ){
                return false;
            }
        }elseif ( $is_misc && is_archive() ) {
            if( isset( $visibility['misc']['archives'] ) && $visibility_opts == 'hide' ){
                $hidden = true; //hide if checked on hidden pages
            }elseif( !isset( $visibility['misc']['archives'] ) && $visibility_opts == 'show' ){
                $hidden = true; //hide if not checked on visible pages
            }

            //do return to bypass other conditions
            $hidden = apply_filters( 'widget_options_visibility_archives', $hidden );
            if( $hidden ){
                return false;
            }
        }elseif ( is_post_type_archive() ) {
            $current_type_archive = get_post_type();
            if( !isset( $visibility['types'] ) || ( $is_types && !isset( $visibility['types'] ) ) ){
                $visibility['types'] = array();
            }
            if( $visibility_opts == 'hide' && array_key_exists( $current_type_archive , $visibility['types']) ){
                $hidden = true; //hide if exists on hidden pages
            }elseif( $visibility_opts == 'show' && !array_key_exists( $current_type_archive , $visibility['types'] ) ){
                $hidden = true; //hide if doesn't exists on visible pages
            }

            //do return to bypass other conditions
            $hidden = apply_filters( 'widget_options_visibility_post_type_archive', $hidden );
            if( $hidden ){
                return false;
            }

        }elseif ( $is_misc && is_404() ) {
            if( isset( $visibility['misc']['404'] ) && $visibility_opts == 'hide' ){
                $hidden = true; //hide if checked on hidden pages
            }elseif( !isset( $visibility['misc']['404'] ) && $visibility_opts == 'show' ){
                $hidden = true; //hide if not checked on visible pages
            }

            //do return to bypass other conditions
            $hidden = apply_filters( 'widget_options_visibility_404', $hidden );
            if( $hidden ){
                return false;
            }
        }elseif ( $is_misc && is_search() ) {
            if( isset( $visibility['misc']['search'] ) && $visibility_opts == 'hide' ){
                $hidden = true; //hide if checked on hidden pages
            }elseif( !isset( $visibility['misc']['search'] ) && $visibility_opts == 'show' ){
                $hidden = true; //hide if not checked on visible pages
            }

            //do return to bypass other conditions
            $hidden = apply_filters( 'widget_options_visibility_search', $hidden );
            if( $hidden ){
                return false;
            }
        }elseif ( is_single() && !is_page() ) {
            global $post;
            if( !isset( $visibility['types'] ) || ( $is_types && !isset( $visibility['types'] ) ) ){
                $visibility['types'] = array();
            }
            if( $visibility_opts == 'hide' && array_key_exists( $post->post_type , $visibility['types']) ){
                $hidden = true; //hide if exists on hidden pages
            }elseif( $visibility_opts == 'show' && !array_key_exists( $post->post_type , $visibility['types']) ){
                $hidden = true; //hide if doesn't exists on visible pages
            }

            // do return to bypass other conditions
            $hidden = apply_filters( 'widget_options_visibility_types', $hidden );


            $taxonomy_names  = get_post_taxonomies();
            $array_intersect = array_intersect( $tax_opts, $taxonomy_names );
            // print_r( $tax_opts );
            if( !isset( $visibility['tax_terms']['category'] ) && isset( $visibility['categories'] ) ){
                $visibility['tax_terms']['category'] = $visibility['categories'];
            }

            if( !empty( $array_intersect ) ){
                foreach ( $array_intersect  as $tax_key => $tax_value ) {
                    if( in_array( $tax_value, $tax_opts ) && isset( $visibility['tax_terms'] ) && isset( $visibility['tax_terms'][ $tax_value ] ) && !empty( $visibility['tax_terms'][ $tax_value ] ) ){
                        $term_list = wp_get_post_terms( $post->ID, $tax_value, array("fields" => "ids"));

                        if( is_array( $term_list ) && !empty( $term_list ) ){
                            $checked_terms   = array_keys( $visibility['tax_terms'][ $tax_value ] );
                            $intersect      = array_intersect( $term_list , $checked_terms );
                            if( !empty( $intersect ) && $visibility_opts == 'hide' ){
                                $hidden = true;
                            }elseif( !empty( $intersect ) && $visibility_opts == 'show' ){
                                $hidden = false;
                            }
                        }
                    }
                    // do return to bypass other conditions
                    $hidden = apply_filters( 'widget_options_visibility_single_'. $tax_value, $hidden );
                }
            }


            if( $hidden ){
                return false;
            }
            // echo $type;
        }elseif ( $is_types && is_page() ) {
            global $post;

            //do post type condition first
            if( isset( $visibility['types'] ) && isset( $visibility['types']['page'] ) ){
                if( $visibility_opts == 'hide' && array_key_exists( 'page' , $visibility['types']) ){
                    $hidden = true; //hide if exists on hidden pages
                }elseif( $visibility_opts == 'show' && !array_key_exists( 'page' , $visibility['types']) ){
                    $hidden = true; //hide if doesn't exists on visible pages
                }
            }else{
                //do per pages condition
                if( !isset( $visibility['pages'] ) ){
                    $visibility['pages'] = array();
                }

                //add parent inherit option
                if( $is_inherit && $post->post_parent && array_key_exists( $post->post_parent , $visibility['pages'] ) ){
                    $visibility['pages'][ $post->ID ] = '1';
                    // print_r( $visibility['pages'] );
                }

                if( $visibility_opts == 'hide' && array_key_exists( $post->ID , $visibility['pages']) ){
                    $hidden = true; //hide if exists on hidden pages
                }elseif( $visibility_opts == 'show' && !array_key_exists( $post->ID , $visibility['pages']) ){
                    $hidden = true; //hide if doesn't exists on visible pages
                }
            }

            //do return to bypass other conditions
            $hidden = apply_filters( 'widget_options_visibility_page', $hidden );
            if( $hidden ){
                return false;
            }
        }

        //end wordpress pages

        if( isset( $widget_options['dates'] ) && 'activate' == $widget_options['dates'] ){

            if( isset( $widget_options['settings']['dates'] ) && isset( $widget_options['settings']['dates']['days'] ) ){
                //days
                $today      = date('l');
                $today      = strtolower( $today );
                $days       = isset( $opts['days'] ) ? $opts['days'] : array();
                unset( $days['options'] );
                $days_opts  = isset( $opts['days']['options'] ) ? $opts['days']['options'] : 'hide';

                if( $days_opts == 'hide' && array_key_exists( $today , $days) ){
                    $hidden = true; //hide if exists on hidden days
                }elseif( $days_opts == 'show' && !array_key_exists( $today , $days) ){
                    $hidden = true; //hide if doesn't exists on visible days
                }

                //do return to bypass other conditions
                $hidden = apply_filters( 'widget_options_visibility_days', $hidden );
                if( $hidden ){
                    return false;
                }
                //end days
            }

            if( isset( $widget_options['settings']['dates'] ) && isset( $widget_options['settings']['dates']['date_range'] ) ){
                //date
                $todate         = date('m/d/Y');
                $dates          = isset( $opts['dates'] ) ? $opts['dates'] : array();
                $dates_opts     = isset( $opts['dates']['options'] ) ? $opts['dates']['options'] : 'hide';
                if( isset( $dates['from'] ) && isset( $dates['to'] ) ){
                    $valid_range = widgetopts_date_in_range( $dates['from'], $dates['to'], $todate  );

                    if( $dates_opts == 'hide' && $valid_range ){
                        $hidden = true; //hide if exists on hidden days
                    }elseif( $dates_opts == 'show' && !$valid_range ){
                        $hidden = true; //hide if doesn't exists on visible days
                    }

                    //do return to bypass other conditions
                    $hidden = apply_filters( 'widget_options_visibility_dates', $hidden );
                    if( $hidden ){
                        return false;
                    }

                }
                //end dates
            }
        }

        if( 'activate' == $widget_options['logic'] ){
            // display widget logic
            if( isset( $opts['class'] ) && isset( $opts['class']['logic'] ) && !empty( $opts['class']['logic'] ) ){
                $display_logic = stripslashes( trim( $opts['class']['logic'] ) );
                $display_logic = apply_filters( 'extended_widget_options_logic_override', $display_logic );
                $display_logic = apply_filters( 'widget_options_logic_override', $display_logic );
                if ( $display_logic === false ){
                    return false;
                }
                if ( $display_logic === true ){
                    return true;
                }
                if ( stristr($display_logic,"return")===false ){
                    $display_logic="return (" . $display_logic . ");";
                }
                if ( !eval( $display_logic ) ){
                    return false;
                }
            }
        }

        //check URLs and wildcards
        if( isset( $widget_options['urls'] ) && 'activate' == $widget_options['urls'] ){
            if( isset( $opts['class'] ) && isset( $opts['class']['urls'] ) && !empty( $opts['class']['urls'] ) ){
                $is_url = '';
                if( isset( $opts['class'] ) && isset( $opts['class']['is_url'] ) && !empty( $opts['class']['is_url'] ) ){
                    $is_url = $opts['class']['is_url'] ;
                }
                $checked_urls = widgetopts_checkurl( $opts['class']['urls'] );

                if( !empty( $is_url ) ){
                    if( 'hide' == $is_url && $checked_urls ){
                        $hidden = true;
                    }
                    if( 'show' == $is_url && !$checked_urls ){
                        $hidden = true;
                    }

                    //do return to bypass other conditions
                    $hidden = apply_filters( 'widget_options_visibility_urls', $hidden );
                    if( $hidden ){
                        return false;
                    }
                }
            }
        }

        if( 'activate' == $widget_options['hide_title'] ){
            //hide widget title
            if( isset( $instance['title'] ) && isset( $opts['class'] ) && isset( $opts['class']['title'] ) && '1' == $opts['class']['title'] ){
                $instance['title'] = '';
            }
        }

        /*
        * Add Widget Caching
        * @since 4.1
        */

        if( isset( $widget_options['cache'] ) && 'activate' == $widget_options['cache'] ){
            //skip cache on preview
            if( method_exists( $widget , 'is_preview') ){
                if ( $widget->is_preview() ){
                    return $instance;
                }
            }

            //check if is_active_sidebar_check
            if( isset( $widget->widgetopts_is_active_sidebar ) ){
                return $instance;
            }

            //check if we need to cache this widget?
            if( isset( $opts['class'] ) && isset( $opts['class']['nocache'] ) && $opts['class']['nocache'] == '1' ){
                return $instance;
            }

            if( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ){
                //start clock to print loading time when debugging
                $timer_start = microtime(true);
            }
            $expiration     = ( isset( $widget_options['settings']['cache']['expiration'] ) ) ? intval( $widget_options['settings']['cache']['expiration'] ) : 0;
            $transient_name = 'widgetopts-cache_'. $widget->id;

            //get cache if exists
            if ( false === ( $cached = get_transient( $transient_name ) ) ){
                ob_start();
                //this renders the widget
                $widget->widget( $args, $instance );
                //get rendered widget from buffer
                $cached = ob_get_clean();

                //save cached widget output as a transient
                set_transient( $transient_name, $cached, $expiration );
            }
            //output the widget
            if( isset( $args['on_menu'] ) && $args['on_menu'] ){
                return apply_filters( 'widgetopts_cached_display', $cached, $instance, $widget->id );
            }else if( isset( $instance['from_shortcode'] ) && $instance['from_shortcode'] && isset( $instance['sc_id_base'] ) ){
                return apply_filters( 'widgetopts_cached_display', $cached, $instance, $widget->id );
            }else{
                echo apply_filters( 'widgetopts_cached_display', $cached, $instance, $widget->id );
            }


            //log time taken
            if( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ){
                //output rendering time as an html comment on debug only
                echo '<!-- From widget options cache in '.number_format( microtime(true) - $timer_start, 5 ).' seconds -->';
            }
            return false;
        } //end caching options

        return $instance;
    }
    add_filter( 'widget_display_callback', 'widgetopts_display_callback', 50, 3 );
endif;

//Don't show widget title
if( !function_exists( 'widgetopts_remove_title' ) ):
    function widgetopts_remove_title( $widget_title, $instance = array(), $widget_id = '' ){
        global $widget_options;
        if ( 'activate' == $widget_options['hide_title'] && is_array( $instance ) && !empty( $instance ) ){
            foreach ( $instance as $key => $value) {
                if( substr( $key, 0, 20 ) == 'extended_widget_opts' ){
                    $opts       = ( isset( $instance[ $key ] ) ) ? $instance[ $key ] : array();

                    if( isset( $opts['class'] ) && isset( $opts['class']['title'] ) && '1' == $opts['class']['title'] ){
                        return;
                    }

                    break;
                }
            }
            return $widget_title;
        }else{
            return ( $widget_title );
        }
    }
    add_filter( 'widget_title', 'widgetopts_remove_title', 10, 4 );
endif;

/*
 * Add custom classes on dynamic_sidebar_params filter
 */
 if( !function_exists( 'widgetopts_add_classes' ) ):
    function widgetopts_add_classes( $params ){
        global $widget_options, $wp_registered_widget_controls;
        $classe_to_add  = '';
        $id_base        = $wp_registered_widget_controls[ $params[0]['widget_id'] ]['id_base'];
        $instance       = get_option( 'widget_' . $id_base );

        if( isset( $wp_registered_widget_controls[ $params[0]['widget_id'] ]['params'][0]['number'] ) ){
            $num = $wp_registered_widget_controls[ $params[0]['widget_id'] ]['params'][0]['number'];
        }elseif( isset( $wp_registered_widget_controls[ $params[0]['widget_id'] ]['callback'][0]->number ) ){
            $num = $wp_registered_widget_controls[ $params[0]['widget_id'] ]['callback'][0]->number;
        }else{
            $num = substr( $params[0]['widget_id'], -1 );
        }
        if( isset( $instance[ $num ] ) ){
            $opts           = ( isset( $instance[ $num ][ 'extended_widget_opts-'. $params[0]['widget_id'] ] ) ) ? $instance[ $num ][ 'extended_widget_opts-'. $params[0]['widget_id'] ] : array();
        }else{
            $opts = array();
        }

        $custom_class   = isset( $opts['class'] ) ? $opts['class'] : '';
        $widget_id_set  = $params[0]['widget_id'];

        if( 'activate' == $widget_options['classes'] && isset( $widget_options['settings']['classes'] ) ){
            //don't add the IDs when the setting is set to NO
            if( isset( $widget_options['settings']['classes']['id'] ) ){
                if( is_array( $custom_class ) && isset( $custom_class['id'] ) && !empty( $custom_class['id'] ) ){
                    $params[0]['before_widget'] = preg_replace( '/id="[^"]*/', "id=\"{$custom_class['id']}", $params[0]['before_widget'], 1 );
                    $widget_id_set = $custom_class['id'];
                }
            }

        }

        //add custom styling to widget
        echo widgetopts_styles_generator( $widget_id_set, $opts, $widget_options, $widget_options['settings'] );

        if( isset( $widget_options['animation'] ) && 'activate' == $widget_options['animation'] ){
            //add custom data attributes
            $data_attr = '';
            if( isset( $custom_class['animation'] ) && !empty( $custom_class['animation'] ) ){
                $data_attr .= ' data-animation-type="'. $custom_class['animation'] .'" ';
            }
            if( isset( $custom_class['event'] ) && !empty( $custom_class['event'] ) ){
                $data_attr .= ' data-animation-event="'. $custom_class['event'] .'" ';
            }
            if( isset( $custom_class['speed'] ) && !empty( $custom_class['speed'] ) ){
                $data_attr .= ' data-animation-speed="'. $custom_class['speed'] .'" ';
            }
            if( isset( $custom_class['offset'] ) && !empty( $custom_class['offset'] ) ){
                $data_attr .= ' data-animation-offset="'. $custom_class['offset'] .'" ';
            }
            if( isset( $custom_class['delay'] ) && !empty( $custom_class['delay'] ) ){
                $data_attr .= ' data-animation-delay="'. $custom_class['delay'] .'" ';
            }
            $params[0]['before_widget'] = str_replace('class="', $data_attr  . ' class="', $params[0]['before_widget'] );
        }

        $get_classes = widgetopts_classes_generator( $opts, $widget_options, $widget_options['settings'] );

        //double check array
        if( !is_array( $get_classes ) ){
            $get_classes = array();
        }

        //check if widget class exists
        if ( ( strpos( $params[0]['before_widget'], '"widget ' ) !== false ) ||
             ( strpos( $params[0]['before_widget'], ' widget ' ) !== false ) ||
             ( strpos( $params[0]['before_widget'], ' widget"' ) !== false)
            ) {
            //do nothing
        }else{
            $get_classes[] = 'widget';
        }

        if( !empty( $get_classes ) ){
            $classes        = 'class="'. ( implode( ' ', $get_classes ) ) . ' ';
            $params[0]['before_widget'] = str_replace('class="', $classes, $params[0]['before_widget']);
        }

        // $params[0]['before_widget'] = str_replace('class="', ' data-animation="asdf" class="', $params[0]['before_widget']);


        if( isset( $widget_options['links'] ) && 'activate' == $widget_options['links'] ){
            if( isset( $custom_class['link'] )  && !empty( $custom_class['link'] ) ){
                if( isset( $custom_class['link_title'] ) && '1' == $custom_class['link_title'] ){
                    $params[0]['before_title'] = $params[0]['before_title'] . '<a href="'. $custom_class['link'] .'" '. ( ( isset( $custom_class['target'] ) ) ? 'target="_blank"' : '' ) .' '. ( ( isset( $custom_class['nofollow'] ) ) ? 'rel="nofollow"' : '' ) .' class="widgetopts-custom-tlink">';
                    $params[0]['after_title']  = '</a>' . $params[0]['after_title'];
                }else{
                    $params[0]['before_widget'] = $params[0]['before_widget'] . '<a href="'. $custom_class['link'] .'" '. ( ( isset( $custom_class['target'] ) ) ? 'target="_blank"' : '' ) .' '. ( ( isset( $custom_class['nofollow'] ) ) ? 'rel="nofollow"' : '' ) .' class="widgetopts-custom-wlink"></a>';
                }
            }
        }

        return $params;
    }
    add_filter( 'dynamic_sidebar_params', 'widgetopts_add_classes' );
endif;

if( !function_exists( 'widgetopts_checkurl' ) ):
    function widgetopts_checkurl( $urls, $explode = ' ' ){
        $safe_url       = array();
        $host           = @parse_url( esc_url( home_url() ) );

        // Get the current request URI similar to self_link() for XSS safe way
        $url_request = esc_url( apply_filters( 'widgetopts_self_link', set_url_scheme( 'http://' . $host['host'] . wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) );
        
        //remove host again
        $url_request = str_replace( 'http://', '', $url_request );
        $url_request = str_replace( 'https://', '', $url_request );
        $url_request = str_replace( $host['host'], '', $url_request );

        //remove beginning slash
        $url_request = trim( $url_request, '/' );

        //remove trailing index.php for staging installations
        if( substr( $url_request, 0, 10 ) === 'index.php/' ){
            $url_request    = str_replace( 'index.php/', '', $url_request );
        }

        //let devs tweak urls
        $urls           = apply_filters( 'widgetopts_visibility_urls', $urls );
        $patterns       = explode( $explode, $urls );
        foreach ( $patterns as $pattern ) {
            $pattern    = trim( trim( $pattern ), '/' );
            $pattern    = preg_quote( $pattern, '/' );
            $pattern    = str_replace( '\*', '.*', $pattern );
            $safe_url[] = $pattern;
        }

        //clean array
        $safe_url       = array_filter( $safe_url );

        $regexps = sprintf(
                '/^(%s)$/i',
                implode( '|', $safe_url )
            );

        return preg_match( $regexps, $url_request );
    }
endif;

?>
