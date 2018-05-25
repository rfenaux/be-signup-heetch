<?php
/**
 * Extends funtionality to Elementor Pagebuilder
 *
 *
 * @copyright   Copyright (c) 2017, Jeffrey Carandang
 * @since       4.3
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if( !function_exists( 'widgetopts_elementor_section' ) ){
    //Add "Widget Options" section to every Elementor Widgets
    add_action( 'elementor/element/after_section_end', 'widgetopts_elementor_section', 10, 3 );
    function widgetopts_elementor_section( $element, $section_id, $args ){
        if ( Elementor\Plugin::$instance->editor->is_edit_mode() ) {
            global $widget_options;

            //filter the elements first to avoid conflicts that can cause pagebuilder not to load
            if ( !in_array( $element->get_name(), array( 'global-settings', 'section', 'page-settings', 'oew-blog-grid'  ) ) ) {

                //create array of section_id to set Widget Options Section to single section to avoid issues
                $widgetopts_elementor_section_id = apply_filters( 'widgetopts_elementor_section_id', array(
                    'section_image',
                    'section_advanced',
                    'section_title',
                    'section_editor',
                    'section_video',
                    'section_button',
                    'section_divider',
                    'section_spacer',
                    'section_map',
                    'section_icon',
                    'section_gallery',
                    'section_image_carousel',
                    'section_icon_list',
                    'section_counter',
                    'section_testimonial',
                    'section_tabs',
                    'section_toggle',
                    'section_social_icon',
                    'section_alert',
                    'section_audio',
                    'section_shortcode',
                    'section_anchor',
                    'section_sidebar',
                    'section_layout',
                    'section_slides',
                    'section_form_fields',
                    'section_list',
                    'section_header',
                    'section_pricing',
                    'section_countdown',
                    'section_buttons_content',
                    'section_blockquote_content',
                    'section_content',
                    'section_login_content',
                    'text_elements',
                    'section_side_a_content',
                    'section_side_b_content',
                    '_section_style'
                    )
                );

                //filter by the section_ids above
                if(  in_array( $section_id, $widgetopts_elementor_section_id ) ){
                    $element->start_controls_section(
                        'widgetopts_section',
                        [
                            'tab'   => Elementor\Controls_Manager::TAB_ADVANCED,
                            'label' => __( 'Widget Options', 'widget-options' ),
                        ],
                        [
                            'overwrite'         => true
                        ]
                    );

                    $element->start_controls_tabs( 'widgetopts_content_tabs',[
                        'overwrite'         => true
                    ] );
                        if( isset( $widget_options['visibility'] ) && 'activate' == $widget_options['visibility'] ){
                            widgetopts_elementor_tab_visibility( $element, $section_id, $args );
                        }

                        if( isset( $widget_options['roles'] ) && 'activate' == $widget_options['roles'] ){
                            widgetopts_elementor_tab_roles( $element, $section_id, $args );
                        }

                        if( isset( $widget_options['dates'] ) && 'activate' == $widget_options['dates'] ){
                            widgetopts_elementor_tab_dates( $element, $section_id, $args );
                        }

                        if( 'activate' == $widget_options['logic'] || 'activate' == $widget_options['links'] || ( isset( $widget_options['sliding'] ) && 'activate' == $widget_options['sliding'] && in_array( $element->get_name(), array( 'button', 'button_plus', 'eael-creative-button', 'cta' ) ) ) ){
                            widgetopts_elementor_tab_settings( $element, $section_id, $args );
                        }

                        //upsell pro
                        if( !is_plugin_active( 'extended-widget-options/plugin.php' ) ){
                            $element->start_controls_tab( 'widgetopts_tab_upsell', [ 'label' => __( '<i class="fa fa-plus"></i>', 'widget-options' ) ] );
                            $element->add_control(
                                'widgetopts_pro_html',
                                    [
                                        'type'      => Elementor\Controls_Manager::RAW_HTML,
                                        'raw'       => __( '<p><strong>Unlock All Options</strong></p>', 'widget-options' ),
                                        'separator' => 'none',
                                    ],
                                    [
                                        'overwrite'         => true
                                    ]
                                );
                            $element->end_controls_tab();
                        }
                    $element->end_controls_tabs();

                    $element->end_controls_section();
                }
            }
        }
    }
}

if( !function_exists( 'widgetopts_elementor_tab_visibility' ) ){
    function widgetopts_elementor_tab_visibility( $element, $section_id, $args ){
        global $widget_options, $widgetopts_taxonomies, $widgetopts_pages, $widgetopts_types, $widgetopts_categories;

        $pages      = ( !empty( $widgetopts_pages ) )       ? $widgetopts_pages         : array();
        $taxonomies = ( !empty( $widgetopts_taxonomies ) )  ? $widgetopts_taxonomies    : array();
        $types      = ( !empty( $widgetopts_types ) )       ? $widgetopts_types         : array();
        $categories = ( !empty( $widgetopts_categories ) )  ? $widgetopts_categories    : array();

        $get_terms = array();
        if( !empty( $widget_options['settings']['taxonomies'] ) && is_array( $widget_options['settings']['taxonomies'] ) ){
            foreach ( $widget_options['settings']['taxonomies'] as $tax_opt => $vall ) {
                $tax_name = 'widgetopts_taxonomy_'. str_replace( '-', '__', $tax_opt );
                // global $$tax_name;
                $get_terms[ $tax_opt ] = $GLOBALS[ $tax_name ];
            }
        }

        // print_r( $get_terms['community-category'] );

        $element->start_controls_tab(
            'widgetopts_tab_visibility',
            [
                'label' => __( '<i class="fa fa-eye"></i>', 'widget-options' )
            ],
            [
                'overwrite'         => true
            ]
        );

        $element->add_control(
            'widgetopts_visibility',
                [
                    'label'         => __( 'Show/Hide', 'widget-options' ),
                    'type'          => Elementor\Controls_Manager::SELECT,
                    'default'       => 'hide',
                    'options'       => [
                                            'show' => __( 'Show on Selected Pages' ),
                                            'hide' => __( 'Hide on Selected Pages' )
                                        ],
                    // 'separator'     => 'none'
                ],
                [
                    'overwrite'         => true
                ]
            );

            if( isset( $widget_options['settings']['visibility'] ) && isset( $widget_options['settings']['visibility']['post_type'] ) && '1' == $widget_options['settings']['visibility']['post_type'] ){
                $pages_array = array();
                if( !empty( $pages ) ){
                    foreach ( $pages as $page ) {
                        $pages_array[ $page->ID ] = $page->post_title;
                    }

                    $element->add_control(
                        'widgetopts_pages',
                        [
                            'label'             => __( 'Pages', 'widget-options' ),
                            'type'              => Elementor\Controls_Manager::SELECT2,
                            'multiple'          => true,
                            'label_block'       => true,
                            'separator'         => 'before',
                            'options'           => $pages_array,
                            'render_type'       => 'none',
                            'description'       => __( 'Click on the field to search and select pages', 'widget-options' )
                        ],
                        [
                            'overwrite'         => true
                        ]
                    );
                }

                if( !empty( $types ) ){
                    $types_array = array();
                    foreach ( $types as $ptype => $type ) {
                        $types_array[ $ptype ] = $type->labels->name;
                    }

                    $element->add_control(
                        'widgetopts_types',
                        [
                            'label'             => __( 'Post Types', 'widget-options' ),
                            'type'              => Elementor\Controls_Manager::SELECT2,
                            'multiple'          => true,
                            'label_block'       => true,
                            'separator'         => 'before',
                            'options'           => $types_array,
                            'render_type'       => 'none',
                            'description'       => __( 'Click on the field to search and select custom post types', 'widget-options' )
                        ],
                        [
                            'overwrite'         => true
                        ]
                    );
                }

                // if( !empty( $categories ) ){
                //     $cat_array = array();
                //     foreach ( $categories as $cat ) {
                //         $cat_array[ $cat->cat_ID ] = $cat->cat_name;
                //     }
                //
                //     $element->add_control(
                //         'widgetopts_categories',
                //         [
                //             'label'             => __( 'Categories', 'widget-options' ),
                //             'type'              => Elementor\Controls_Manager::SELECT2,
                //             'multiple'          => true,
                //             'label_block'       => true,
                //             'separator'         => 'before',
                //             'options'           => $cat_array,
                //             'render_type'       => 'none',
                //             'description'       => __( 'Click on the field to search and select categories', 'widget-options' )
                //         ]
                //     );
                // }
            }

            if( isset( $widget_options['settings']['visibility'] ) && isset( $widget_options['settings']['visibility']['taxonomies'] ) && '1' == $widget_options['settings']['visibility']['taxonomies'] ){
                if( !empty( $widget_options['settings']['taxonomies'] ) && is_array( $widget_options['settings']['taxonomies'] ) ){
                    foreach ( $widget_options['settings']['taxonomies'] as $tax_opt => $vallue ) {
                        $term_array = array();
                        foreach ( $get_terms[ $tax_opt ] as $get_term ) {
                            $term_array[ $get_term->term_id ] = $get_term->name;
                        }
                        $element->add_control(
                            'widgetopts_tax_' . $tax_opt,
                            [
                                'label'             => $taxonomies[ $tax_opt ]->label,
                                'type'              => Elementor\Controls_Manager::SELECT2,
                                'multiple'          => true,
                                'label_block'       => true,
                                'separator'         => 'before',
                                'options'           => $term_array,
                                'render_type'       => 'none',
                                'description'       => __( 'Click on the field to search and select '. $taxonomies[ $tax_opt ]->label, 'widget-options' )
                            ],
                            [
                                'overwrite'         => true
                            ]
                        );
                    }
                }

                if( !empty( $taxonomies ) ){
                    $tax_array = array();
                    foreach ( $taxonomies as $taxonomy ) {
                        $tax_array[ $taxonomy->name ] = $taxonomy->label;
                    }

                    $element->add_control(
                        'widgetopts_taxonomies',
                        [
                            'label'             => __( 'Taxonomies', 'widget-options' ),
                            'type'              => Elementor\Controls_Manager::SELECT2,
                            'multiple'          => true,
                            'label_block'       => true,
                            'separator'         => 'before',
                            'options'           => $tax_array,
                            'render_type'       => 'none',
                            'description'       => __( 'Click on the field to search and select taxonomies', 'widget-options' )
                        ],
                        [
                            'overwrite'         => true
                        ]
                    );
                }
            }

            if( isset( $widget_options['settings']['visibility'] ) && isset( $widget_options['settings']['visibility']['misc'] ) && '1' == $widget_options['settings']['visibility']['misc'] ){
                $element->add_control(
                    'widgetopts_misc',
                    [
                        'label'             => __( 'Miscellaneous', 'widget-options' ),
                        'type'              => Elementor\Controls_Manager::SELECT2,
                        'multiple'          => true,
                        'label_block'       => true,
                        'separator'         => 'before',
                        'options'           => [
                            'home'      =>  __( 'Home/Front', 'widget-options' ),
                            'blog'      =>  __( 'Blog', 'widget-options' ),
                            'archives'  =>  __( 'Archives', 'widget-options' ),
                            '404'       =>  __( '404', 'widget-options' ),
                            'search'    =>  __( 'Search', 'widget-options' )
                        ],
                        'render_type'       => 'none',
                        'description'       => __( 'Click on the field to search and select miscellaneous pages', 'widget-options' )
                    ],
                    [
                        'overwrite'         => true
                    ]
                );
            }
        $element->end_controls_tab();
    }
}

if( !function_exists( 'widgetopts_elementor_tab_settings' ) ){
    function widgetopts_elementor_tab_settings( $element, $section_id, $args ){
        global $widget_options;

        $element->start_controls_tab(
            'widgetopts_tab_settings',
            [
                 'label' => __( '<i class="fa fa-gear"></i>', 'widget-options' )
            ],
            [
                'overwrite'         => true
            ]
        );

        if( isset( $widget_options['fixed']  ) && 'activate' == $widget_options['fixed'] ){
            $element->add_control(
                'widgetopts_fixed',
                [
                    'label'          => __( 'Fixed Widget', 'widget-options' ),
                    'type'           => Elementor\Controls_Manager::SWITCHER,
                    'return_value'   => 'yes',
                    'label_on'       => __( 'Yes', 'widget-options' ),
                    'label_off'      => __( 'No', 'widget-options' ),
                ],
                [
                    'overwrite'         => true
                ]
            );
        }

        if( 'activate' == $widget_options['links'] ){

            $element->add_control(
        		'widgetopts_links',
        		[
        			'label'          => __( 'Link Widget', 'widget-options' ),
        			'type'           => Elementor\Controls_Manager::SWITCHER,
        			'return_value'   => 'yes',
                    'label_on'       => __( 'Yes', 'widget-options' ),
        			'label_off'      => __( 'No', 'widget-options' ),
        		],
                [
                    'overwrite'         => true
                ]
        	);

            $element->add_control(
        		'widgetopts_links_target',
        		[
        			'label'          => '',
        			'type'           => Elementor\Controls_Manager::TEXT,
                    'label_block'    => true,
                    'placeholder'    => __( 'http://your-link.com', 'widget-options' ),
                    'separator'      => 'none',
                    'condition' => [
        				'widgetopts_links!' => '',
        			]
        		],
                [
                    'overwrite'         => true
                ]
        	);

            $element->add_control(
        		'widgetopts_links_newtab',
        		[
        			'label'          => __( 'Open in new window', 'widget-options' ),
        			'type'           => Elementor\Controls_Manager::SWITCHER,
        			'return_value'   => 'on',
                    'separator'      => 'none',
                    'condition' => [
        				'widgetopts_links!' => '',
        			]
        		],
                [
                    'overwrite'         => true
                ]
        	);

            $element->add_control(
        		'widgetopts_links_nofollow',
        		[
        			'label'          => __( 'Add nofollow', 'widget-options' ),
        			'type'           => Elementor\Controls_Manager::SWITCHER,
        			'return_value'   => 'on',
                    'separator'      => 'none',
                    'condition' => [
        				'widgetopts_links!' => '',
        			]
        		],
                [
                    'overwrite'         => true
                ]
        	);

            $element->add_control(
        		'widgetopts_links_http',
        		[
        			'label'          => __( 'Do not add http/https', 'widget-options' ),
        			'type'           => Elementor\Controls_Manager::SWITCHER,
        			'return_value'   => 'on',
                    'separator'      => 'none',
                    'condition' => [
        				'widgetopts_links!' => '',
        			]
        		],
                [
                    'overwrite'         => true
                ]
        	);
        }

        if( is_plugin_active( 'sliding-widget-options/plugin.php' ) && 'activate' == $widget_options['sliding'] && in_array( $element->get_name(), array( 'button', 'button_plus', 'eael-creative-button', 'cta' ) ) ){
            $element->add_control(
                'widgetopts_open_sliding',
                [
                    'label'          => __( 'Open Pop-up or Sliding Widgets', 'widget-options' ),
                    'type'           => Elementor\Controls_Manager::SWITCHER,
                    'return_value'   => 'on',
                    'default'        => '',
                    'separator'      => 'none'
                ],
                [
                    'overwrite'         => true
                ]
            );
        }

        if( 'activate' == $widget_options['logic'] ){
            $element->add_control(
                'widgetopts_logic',
                [
                    'type'          => Elementor\Controls_Manager::TEXTAREA,
                    'label'         => __( 'Display Widget Logic', 'widget-options' ),
                    'description'   => __( 'Add your PHP Conditional Tags. Please note that this will be EVAL\'d directly.', 'widget-options' ),
                    // 'separator'     => 'none',
                ],
                [
                    'overwrite'         => true
                ]
            );
        }
        $element->end_controls_tab();
    }
}

if( !function_exists( 'widgetopts_elementor_tab_roles' ) ){
    function widgetopts_elementor_tab_roles( $element, $section_id, $args ){
        global $widget_options;

        $roles  = get_editable_roles();

        $element->start_controls_tab(
            'widgetopts_tab_roles',
            [
                 'label' => __( '<i class="fa fa-user"></i>', 'widget-options' )
            ],
            [
                'overwrite'         => true
            ]
        );

        $user_array = array();
        foreach ( $roles as $role_name => $role_info ) {
            $user_array[ $role_name ] = $role_info['name'];
        }
        $user_array['guests'] = __( 'Guests', 'widget-options' );

        $element->add_control(
            'widgetopts_visibility_roles',
                [
                    'label'         => __( 'Show/Hide', 'widget-options' ),
                    'type'          => Elementor\Controls_Manager::SELECT,
                    'default'       => 'hide',
                    'options'       => [
                                            'show' => __( 'Show on Selected Roles' ),
                                            'hide' => __( 'Hide on Selected Roles' )
                                        ],
                    // 'separator'     => 'none'
                ],
                [
                    'overwrite'         => true
                ]
            );

        $element->add_control(
            'widgetopts_roles',
            [
                'label'             => __( 'Select User Roles', 'widget-options' ),
                'type'              => Elementor\Controls_Manager::SELECT2,
                'multiple'          => true,
                'label_block'       => true,
                'separator'         => 'none',
                'options'           => $user_array,
                'render_type'       => 'none',
                'description'       => __( 'Click on the field to search and select user roles', 'widget-options' )
            ],
            [
                'overwrite'         => true
            ]
        );

        $element->end_controls_tab();
    }
}

if( !function_exists( 'widgetopts_elementor_tab_dates' ) ){
    function widgetopts_elementor_tab_dates( $element, $section_id, $args ){
        global $widget_options;

        $element->start_controls_tab(
            'widgetopts_tab_dates',
            [
                 'label' => __( '<i class="fa fa-calendar"></i>', 'widget-options' )
            ],
            [
                'overwrite'         => true
            ]
        );

        if( isset( $widget_options['settings']['dates'] ) && isset( $widget_options['settings']['dates']['days'] ) ){
            $element->add_control(
        		'widgetopts_days',
        		[
        			'label'          => __( 'Days Restriction', 'widget-options' ),
        			'type'           => Elementor\Controls_Manager::SWITCHER,
        			'return_value'   => 'yes',
                    'label_on'       => __( 'Yes', 'widget-options' ),
        			'label_off'      => __( 'No', 'widget-options' ),
        		],
                [
                    'overwrite'         => true
                ]
        	);
            $element->add_control(
                'widgetopts_visibility_days',
                    [
                        'label'         => __( 'Show/Hide', 'widget-options' ),
                        'type'          => Elementor\Controls_Manager::SELECT,
                        'default'       => 'hide',
                        'options'       => [
                                                'show' => __( 'Show on Selected Days' ),
                                                'hide' => __( 'Hide on Selected Days' )
                                            ],
                        'condition'     =>  [
                                				'widgetopts_days!' => '',
                                			]
                        // 'separator'     => 'none'
                    ],
                    [
                        'overwrite'         => true
                    ]
            );

	        $element->add_control(
	            'widgetopts_days_list',
	            [
	                'label'             => __( 'Days', 'widget-options' ),
	                'type'              => Elementor\Controls_Manager::SELECT2,
	                'multiple'          => true,
	                'label_block'       => true,
	                'separator'         => 'before',
	                'options'           => [
	                    'monday'     =>  __( 'Monday', 'widget-options' ),
	                    'tuesday'    =>  __( 'Tuesday', 'widget-options' ),
	                    'wednesday'  =>  __( 'Wednesday', 'widget-options' ),
	                    'thursday'   =>  __( 'Thursday', 'widget-options' ),
	                    'friday'     =>  __( 'Friday', 'widget-options' ),
	                    'saturday'   =>  __( 'Saturday', 'widget-options' ),
	                    'sunday'     =>  __( 'Sunday', 'widget-options' )
	                ],
	                'render_type'       => 'none',
	                'description'       => __( 'Click on the field to search and select specific days', 'widget-options' ),
                    'condition'         =>  [
                                                'widgetopts_days!' => '',
                                            ],
                    'separator'     => 'none'
	            ],
                [
                    'overwrite'         => true
                ]
	        );
		}

        if( isset( $widget_options['settings']['dates'] ) && isset( $widget_options['settings']['dates']['date_range'] ) ){

            $element->add_control(
        		'widgetopts_dates',
        		[
        			'label'          => __( 'Dates Restriction', 'widget-options' ),
        			'type'           => Elementor\Controls_Manager::SWITCHER,
        			'return_value'   => 'yes',
        			// 'default'        => 'yes',
                    'label_on'       => __( 'Yes', 'widget-options' ),
        			'label_off'      => __( 'No', 'widget-options' ),
        		],
                [
                    'overwrite'         => true
                ]
        	);

            $element->add_control(
                'widgetopts_visibility_dates',
                    [
                        'label'         => __( 'Show/Hide', 'widget-options' ),
                        'type'          => Elementor\Controls_Manager::SELECT,
                        'default'       => 'hide',
                        'options'       => [
                                                'show' => __( 'Show on Selected Dates' ),
                                                'hide' => __( 'Hide on Selected Dates' )
                                            ],
                        'condition'     =>  [
                                				'widgetopts_dates!' => '',
                                			]
                    ],
                    [
                        'overwrite'         => true
                    ]
                );

            $element->add_control(
    			'widgetopts_dates_start',
    			[
    				'label' => __( 'Start Date', 'widget-options' ),
    				'type' => Elementor\Controls_Manager::DATE_TIME,
    				'default' => date( 'Y-m-d H:i', strtotime( '-3 day' ) ),
    				'description' => __( 'Select starting date', 'widget-options' ),
                    'condition' => [
        				'widgetopts_dates!' => '',
        			]
    			],
                [
                    'overwrite'         => true
                ]
    		);

            $element->add_control(
    			'widgetopts_dates_end',
    			[
    				'label' => __( 'End Date', 'widget-options' ),
    				'type' => Elementor\Controls_Manager::DATE_TIME,
    				'default' => date( 'Y-m-d H:i', strtotime( '-2 day' ) ),
    				'description' => __( 'Select ending date', 'widget-options' ),
                    'condition' => [
        				'widgetopts_dates!' => '',
        			]
    			],
                [
                    'overwrite'         => true
                ]
    		);
        }

        $element->end_controls_tab();
    }
}

?>
