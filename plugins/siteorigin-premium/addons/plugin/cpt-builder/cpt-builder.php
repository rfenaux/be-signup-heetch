<?php
/*
Plugin Name: SiteOrigin Custom Post Type Builder
Description: Build custom post types with reusable Page Builder layouts.
Version: 1.0.0
Author: SiteOrigin
Author URI: https://siteorigin.com
License: GPL3
License URI: https://www.gnu.org/licenses/gpl-3.0.txt
Documentation: https://siteorigin.com/premium-documentation/plugin-addons/custom-post-type-builder/
Tags: Page Builder
Video: 5d5badzbqc
*/

/**
 * Class SiteOrigin_Premium_Plugin_Cpt_Builder
 */
class SiteOrigin_Premium_Plugin_Cpt_Builder {

	const POST_TYPE = 'so_custom_post_type';

	function __construct(){
		add_action( 'widgets_init', array( $this, 'register_post_type' ) );
		add_action( 'widgets_init', array( $this, 'register_custom_post_types' ) );

		add_filter( 'siteorigin_panels_settings', array( $this, 'enable_page_builder' ) );
		add_filter( 'siteorigin_panels_settings_enabled_post_types', array( $this, 'remove_custom_post_types' ) );

		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		
		add_action( 'admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'), 20 );

		add_filter( 'siteorigin_panels_post_builder_type', array( $this, 'post_builder_type' ), 15, 2 );
		add_filter( 'siteorigin_panels_builder_supports', array( $this, 'builder_supports' ), 10, 3 );

		// This is to modify Page Builder style sections for the custom post type interface
		add_filter( 'siteorigin_panels_widget_style_groups', array($this, 'widget_style_groups'), 10, 3 );
		add_filter( 'siteorigin_panels_widget_style_fields', array($this, 'widget_style_fields'), 10, 3 );

		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );

		// Merge panels data from the
		add_filter( 'siteorigin_panels_data_pre_save', array( $this, 'panels_data_pre_save_filter' ), 10, 2 );
		add_filter( 'siteorigin_panels_data', array( $this, 'panels_data_filter' ), 10, 2 );

		// Integrate with SiteOrigin Page Settings
		add_filter( 'siteorigin_page_settings_values', array( $this, 'siteorigin_page_settings' ), 10, 3 );
	}

	/**
	 * Get the single instance
	 *
	 * @return SiteOrigin_Premium_Plugin_Cpt_Builder
	 */
	static function single(){
		static $single;
		return empty( $single ) ? $single = new self() : $single;
	}

	/**
	 * Register the post type that represents the custom post types. Very meta.
	 */
	function register_post_type(){
		if( current_user_can( 'manage_options' ) ) {
			register_post_type( self::POST_TYPE, array(
				'label' => __( 'Post Types', 'siteorigin-premium' ),
				'public' => false,
				'publicly_queryable' => false,
				'show_ui' => true,
				'supports' => array( 'title', 'editor', 'so-page-settings' ),
				'show_in_menu' => 'tools.php',
			) );
		}
	}

	/**
	 * Get all the registered post types
	 *
	 * @return array|bool|mixed|null|object
	 */
	function get_post_types(){
		$post_types = wp_cache_get( 'post_types', 'so_post_type_builder' );

		if( $post_types === false ) {
			global $wpdb;
			$post_types = $wpdb->get_results( "
				SELECT ID, post_title, post_name
				FROM {$wpdb->posts}
				WHERE
					post_type = '" . self::POST_TYPE . "'
					AND post_status = 'publish'
			" );

			$return = array();
			foreach( $post_types as &$post_type ) {
				$post_type_settings = get_post_meta( $post_type->ID, 'siteorigin_post_type_settings', true );
				if ( empty( $post_type_settings ) ) {
					continue;
				}
				$post_type->post_type_settings = $post_type_settings;
				$slug = $post_type->post_type_settings['slug'];

				// Skip if this slug exists already
				if( empty( $slug ) || isset( $return[ $slug ] ) ) continue;

				$return[ $slug ] = $post_type;
			}
			$post_types = $return;

			if( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
				wp_cache_set( 'post_types', $post_types, 'so_post_type_builder', 86400 );
			}
		}

		return $post_types;
	}

	/**
	 * Register all the custom post types we're using
	 */
	function register_custom_post_types(){
		// Now we can process the post types
		$post_types = $this->get_post_types();

		if( !empty( $post_types ) && is_array( $post_types ) ) {
			foreach( $post_types as $slug => $type ) {
				global $wp_post_types, $wp_taxonomies;

				$settings = $type->post_type_settings;

				// Skip if this already exists.
				if( !empty( $wp_post_types[ $slug ] ) ) continue;

				// Register the post types
				register_post_type( $slug, array(
					'labels' => array(
						'name' => empty( $settings['labels']['plural']) ? $type->post_title : $settings['labels']['plural'],
						'singular_name' => empty( $settings['labels']['single']) ? $type->post_title : $settings['labels']['single'],
					),
					'public' => true,
					'has_archive' => $settings['has_archive'],
					'exclude_from_search' => ! empty( $settings['exclude_from_search'] ) ? $settings['exclude_from_search'] : false,
					'supports' => array_merge( $settings['supports'], array( 'editor', 'so-cpt-builder' ) ),
					'menu_icon' => $settings['icon'],
				) );

				// Now add the taxonomies

				if( !empty( $settings['taxonomy'] ) ) {

					foreach( $settings['taxonomy'] as $taxonomy ) {
						if( empty( $taxonomy['slug'] ) ) continue;
						if( empty( $wp_taxonomies[ $taxonomy['slug'] ] ) ) {
							// We'll register a new taxonomy
							register_taxonomy(
								$taxonomy['slug'],
								$slug,
								array(
									'label' => $taxonomy['label'],
									'hierarchical' => $taxonomy[ 'hierarchical' ]
								)
							);
						}
						else {
							// We'll just use the existing taxonomy
							register_taxonomy_for_object_type( $taxonomy['slug'], $slug );
						}
					}
				}

			}
		}

	}
	
	function remove_custom_post_types( $post_types ) {
		$custom_post_types = array_keys( $this->get_post_types() );
		$custom_post_types[] = self::POST_TYPE;
		foreach ( $custom_post_types as $custom_post_type ) {
			if ( isset( $post_types[ $custom_post_type ] ) ) {
				unset( $post_types[ $custom_post_type ] );
			}
		}
		return $post_types;
	}

	/**
	 * Enable Page Builder for custom post types.
	 *
	 * @param $settings
	 *
	 * @return mixed
	 */
	function enable_page_builder( $settings ){
		if( empty( $settings['post-types'] ) ) {
			$settings['post-types'] = array();
		}

		$slugs = array();
		foreach( $this->get_post_types() as $slug => $type ) {
			$slugs[] = $slug;
		}

		$settings['post-types'] = array_unique(
			array_merge( $settings['post-types'], $slugs, array( self::POST_TYPE ) )
		);

		return $settings;
	}
	
	function enqueue_admin_scripts( $page ) {
		$cur_screen = get_current_screen();
		if ( $page !== 'post.php' || get_post_status() === 'draft' || $cur_screen->post_type !== self::POST_TYPE ) {
			return;
		}
		
		wp_enqueue_script(
		    'siteorigin-premium-cpt-builder-admin',
		    plugin_dir_url(__FILE__) . 'js/so-cpt-builder' . SITEORIGIN_PREMIUM_JS_SUFFIX . '.js',
		    array( 'jquery' ),
			SITEORIGIN_PREMIUM_VERSION
		);
		
		$settings = $this->get_post_settings( get_the_ID() );
		$existing_posts = get_posts( array( 'post_type' => $settings['slug'] ) );
		$existing_post_count = count( $existing_posts );
		
		wp_localize_script(
			'siteorigin-premium-cpt-builder-admin',
			'soCptBuilderAdminOptions',
		    array(
				'loc' => array(
					'confirm_edit_post_type' => sprintf(
						__( 'Warning! Editing the post type layout will affect %s existing posts of this type.', 'siteorigin-premium' ),
						$existing_post_count
					),
					'confirm_disable_editable' => sprintf(
						__( 'Warning! Disabling editable layout will cause problems with %s existing posts of this type.', 'siteorigin-premium' ),
						$existing_post_count
					),
					'confirm_edit_slug' => sprintf(
						__( 'Warning! Editing the post type slug will cause problems with %s existing posts of this type!', 'siteorigin-premium' ),
						$existing_post_count
					),
				),
				'existing_post_count' => intval( $existing_post_count ),
			)
		);
	}

	function post_builder_type( $type, $post ){
		if( $post->post_type == self::POST_TYPE ) {
			$type = 'post_type_builder';
		}

		return $type;
	}

	/**
	 * Add the style groups required for CPT
	 *
	 * @param $groups
	 *
	 * @return mixed
	 */
	function widget_style_groups( $groups, $post_id, $args ) {
		// Ignore this when not displaying the Post Type Builder
		if( ( !empty( $args['builderType'] ) && $args['builderType'] === 'post_type_builder' ) || $args === false ) {
			$groups['so_cpt'] = array(
				'name' => __('Custom Post Type', 'siteorigin-premium'),
				'priority' => 1
			);
		}

		return $groups;
	}

	/**
	 * Add the styles fields required for the CPT interface
	 *
	 * @param $fields
	 *
	 * @return mixed
	 */
	function widget_style_fields($fields, $post_id, $args ) {
		// Ignore this when not displaying the Post Type Builder
		if( ( !empty( $args['builderType'] ) && $args['builderType'] === 'post_type_builder' ) || $args === false ) {

			$fields['so_cpt_readonly'] = array(
				'name' => __( 'Read Only Widget', 'siteorigin-premium' ),
				'label' => __( 'Read Only', 'siteorigin-premium' ),
				'type' => 'checkbox',
				'group' => 'so_cpt',
				'description' => __('This widget will be the same across all instances of this post type.', 'siteorigin-premium'),
				'priority' => 10,
			);

		}
		return $fields;
	}

	function builder_supports( $supports, $post, $panels_data ){

		$post_types = $this->get_post_types();

		if(
			!empty( $post_types ) && is_array( $post_types ) &&
			isset( $post->post_title ) && in_array( $post->post_type, array_keys( $post_types ) )
		) {
			
			$is_layout_editable = $this->is_post_type_layout_editable( $post->post_type );
			
			$supports = array(
				'addRow' => $is_layout_editable,
				'editRow' => $is_layout_editable,
				'deleteRow' => $is_layout_editable,
				'moveRow' => $is_layout_editable,

				'addWidget' => $is_layout_editable,
				'editWidget' => true,
				'deleteWidget' => $is_layout_editable,
				'moveWidget' => $is_layout_editable,

				'prebuilt' => $is_layout_editable,
				'history' => true,
				'liveEditor' => true,
				'revertToEditor' => empty( $panels_data ),
			);
			
		} else if( $post->post_type == self::POST_TYPE ) {
			
			// Allow `revertToEditor` for existing posts which weren't using Page Builder.
			$supports = array(
				'revertToEditor' => empty( $panels_data ) && $post->post_status != 'auto-draft',
			);
			
		}

		return $supports;
	}
	
	function is_post_type_layout_editable( $post_type ) {
		
		if ( is_string( $post_type ) ) {
			$post_types = $this->get_post_types();
			$post_type = $post_types[ $post_type ];
		}
		
		if ( empty( $post_type ) ) {
			return false;
		}
		
		$post_type_settings = $post_type->post_type_settings;
		
		// `editable_layout` should default to false.
		if ( ! empty( $post_type_settings['editable_layout'] ) ) {
			return true;
		}
		
		$page_builder_supports = isset( $post_type_settings['page_builder_supports'] ) ? $post_type_settings[ 'page_builder_supports' ] : array();
		
		// If any of the Page Builder supports features to edit layouts were set, then the layout is editable.
		if ( ! empty( $page_builder_supports ) ) {
			$editable_layout_actions = array(
				'addRow', 'editRow', 'deleteRow', 'moveRow', 'addWidget', 'deleteWidget', 'moveWidget', 'prebuilt'
			);
			foreach ( $page_builder_supports as $feature ) {
				if ( in_array( $feature, $editable_layout_actions ) ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Handle saving the post type data
	 *
	 * @param $post_id
	 * @param $post
	 */
	function save_post( $post_id, $post ){
		if(
			$post->post_type == self::POST_TYPE &&
			! empty( $_POST['so_post_type_settings'] ) &&
			! empty( $_POST['_so_cpt_nonce'] ) &&
			wp_verify_nonce( $_POST['_so_cpt_nonce'], 'save_post_type_settings' )
		) {
			$form = $this->get_form();

			$old_settings = get_post_meta( $post_id, 'siteorigin_post_type_settings', true );
			$settings = stripslashes_deep( $_POST['so_post_type_settings'] );
			unset( $settings['_sow_form_id'] );

			$settings = $form->update( $settings, $old_settings );
			
			$slug = $settings['slug'];
			if( empty( $slug ) ) {
				$slug = $this->sanitize_reserved_post_types( $post->post_name, $post_id );
			}
			$slug = wp_unique_post_slug( sanitize_title( $slug ), $post_id, 'publish', self::POST_TYPE, false );
			$settings['slug'] = substr( $slug, 0, 20 );

			update_post_meta( $post_id, 'siteorigin_post_type_settings', $settings );

			// Clear all the caches
			wp_cache_delete( 'post_types', 'so_post_type_builder' );
			global $wp_rewrite;
			$wp_rewrite->flush_rules( true );
		}
	}

	/**
	 * Process the post type widgets before storing them in the database.
	 *
	 * @param $panels_data
	 * @param $post
	 * @return mixed
	 */
	function panels_data_pre_save_filter( $panels_data, $post ){
		$post_types = $this->get_post_types();
		
		if( $post->post_type == self::POST_TYPE && !empty( $panels_data['widgets'] ) && is_array( $panels_data['widgets'] ) ) {
			$read_only = array();

			foreach( $panels_data['widgets'] as & $widget ) {
				if( !empty( $widget['panels_info']['style']['so_cpt_readonly'] ) && !empty( $widget['panels_info']['widget_id'] ) ) {
					$read_only[ $widget['panels_info']['widget_id'] ] = $widget;
				}
			}

			if( !empty($read_only) ) {
				update_post_meta( $post->ID, 'panels_read_only_widgets', $read_only );
			}
		} else if( post_type_supports( $post->post_type, 'so-cpt-builder' ) && ! empty( $post_types[ $post->post_type ] ) ) {
			// If the layout is not editable we check if widgets' content has been updated and set a flag,
			// so we know not to overwrite in future changes to the post type widgets.
			if ( ! $this->is_post_type_layout_editable( $post->post_type ) ) {
				$post_type = $post_types[ $post->post_type ];
				$cpt_panels_data = get_post_meta( $post_type->ID, 'panels_data', true );
				$cpt_widgets = $this->get_widgets_by_id( $cpt_panels_data );
				foreach ( $panels_data['widgets'] as &$widget ) {
					if ( ! empty( $widget['panels_info']['widget_id'] ) &&
						 isset( $cpt_widgets[ $widget['panels_info']['widget_id'] ]) ) {
						$cpt_widget = $cpt_widgets[ $widget['panels_info']['widget_id'] ];
						$widget['cpt_widget_edited'] = ! empty( $this->widget_diff( $widget, $cpt_widget ) );
					}
				}
			}
			
		}
			
		return $panels_data;
	}
	
	function widget_diff( $widget1, $widget2 ) {
		
		$diff_widget = array();
		$ignore_keys = array( '_sow_form_id', '_sow_form_timestamp', 'panels_info' );
		foreach ( $widget1 as $key => $value ) {
			if ( in_array( $key, $ignore_keys ) ) {
				continue;
			}
			if ( ! isset( $widget2[ $key ] ) ) {
				$diff_widget[ $key ] = $value;
			} else if ( is_array( $value ) ) {
				$sub_diff = $this->widget_diff( $widget1[ $key ], $widget2[ $key ] );
				if ( ! empty( $sub_diff ) ) {
					$diff_widget[ $key ] = $sub_diff;
				}
			} else if ( $widget2[ $key ] !== $value ) {
				$diff_widget[ $key ] = $value;
			}
		}
		
		return $diff_widget;
	}

	/**
	 * Filter the panels_data to add in the data from the post type
	 */
	function panels_data_filter( $panels_data, $post ){
		if ( empty( $post ) ) {
			return $panels_data;
		}

		$post = get_post( $post );
		if( empty( $post ) ) {
			return $panels_data;
		}

		$post_types = $this->get_post_types();
		
		if( post_type_supports( $post->post_type, 'so-cpt-builder' ) && !empty( $post_types[ $post->post_type ] ) ) {
			$post_type = $post_types[ $post->post_type ];
			$cpt_panels_data = get_post_meta( $post_type->ID, 'panels_data', true );
			
			if ( ! empty( $cpt_panels_data ) ) {
				
				foreach ( $cpt_panels_data['widgets'] as &$widget ) {
					if ( isset( $widget['panels_info']['style']['so_cpt_readonly'] ) ) {
						$widget['panels_info']['read_only'] = $widget['panels_info']['style']['so_cpt_readonly'];
					}
				}
				
				if ( empty( $panels_data ) ) {
					return $cpt_panels_data;
				}
			}
			
			if ( empty( $cpt_panels_data ) ) {
				return $panels_data;
			}
			
			$layout_editable = $this->is_post_type_layout_editable( $post_type );
			
			if ( $layout_editable ) {
				return $panels_data;
			}
			
			$post_widgets = $this->get_widgets_by_id( $panels_data );
			
			foreach ( $cpt_panels_data['widgets'] as &$widget ) {
				if( isset( $widget['panels_info']['style']['so_cpt_readonly'] )  ) {
					$widget['panels_info']['read_only'] = $widget['panels_info']['style']['so_cpt_readonly'];
				}
				
				if(
					empty( $widget['panels_info']['read_only'] ) &&
					! empty( $widget['panels_info']['widget_id'] ) &&
					isset( $post_widgets[ $widget['panels_info']['widget_id'] ] )
				) {
					$post_widget = $post_widgets[ $widget['panels_info']['widget_id'] ];
					if ( ! empty( $post_widget['cpt_widget_edited'] ) ) {
						// Replace this with the widget from the post
						$old_panels_info = $widget['panels_info'];
						$widget = $post_widget;
						$widget[ 'panels_info' ] = $old_panels_info;
					}
				}
			}
			return $cpt_panels_data;
		}
		
		return $panels_data;
	}
	
	/**
	 * Method to transform panels_data widgets indexed array to an associative array with widget_id as key.
	 *
	 * @param $panels_data array The panels data from which to extract and transform widgets
	 *
	 * @return array The transformed widgets array by id.
	 */
	private function get_widgets_by_id( $panels_data ) {
		
		$widgets_by_id = array();
		
		if( ! empty( $panels_data['widgets'] ) ) {
			foreach( $panels_data['widgets'] as $widget ) {
				if ( empty( $widget['panels_info']['widget_id'] ) ) {
					continue;
				}
				$widgets_by_id[ $widget['panels_info']['widget_id'] ] = $widget;
			}
		}
		return $widgets_by_id;
	}

	/**
	 * Register the meta boxes for the post
	 */
	function add_meta_boxes( ){
		add_meta_box(
			'so-post-type-settings',
			__( 'Post Type Settings', 'siteorigin-premium' ),
			array( $this, 'meta_box_callback' ),
			self::POST_TYPE,
			'normal',
			'default'
		);
	}

	/**
	 * Get the settings form.
	 *
	 * @return SiteOrigin_Premium_Form
	 */
	function get_form(){
		return SiteOrigin_Premium::single()->get_form(
			'so_post_type_settings',
			array(
				'supports' => array(
					'type' => 'checkboxes',
					'label' => __( 'Post Supports', 'siteorigin-premium' ),
					'multiple' => true,
					'options' => array(
						'title' => __( 'Title', 'siteorigin-premium' ),
						'author' => __( 'Author', 'siteorigin-premium' ),
						'thumbnail' => __( 'Thumbnail', 'siteorigin-premium' ),
						'excerpt' => __( 'Excerpt', 'siteorigin-premium' ),
						'trackbacks' => __( 'Trackbacks', 'siteorigin-premium' ),
						'custom-fields' => __( 'Custom Fields', 'siteorigin-premium' ),
						'comments' => __( 'Comments', 'siteorigin-premium' ),
						'revisions' => __( 'Revisions', 'siteorigin-premium' ),
						'page-attributes' => __( 'Page Attributes', 'siteorigin-premium' ),
						'post-formats' => __( 'Post Formats', 'siteorigin-premium' ),
					),
					'default' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' )
				),
				'editable_layout' => array(
					'type' => 'checkbox',
					'label' => __( 'Editable layout', 'siteorigin-premium' ),
					'description' => __( 'Whether rows and widgets may be added, moved or deleted. Do not disable this after creating the post type.', 'siteorigin-premium')
				),
				'slug' => array(
					'type' => 'text',
					'label' => __( 'Slug', 'siteorigin-premium' ),
					'description' => __( 'Used in the post type URL. This must be unique across all post types. Do not change this after creating the post type.', 'siteorigin-premium' ),
					'sanitize' => array( $this, 'sanitize_reserved_post_types' ),
				),
				'has_archive' => array(
					'type' => 'checkbox',
					'label' => __( 'Has archive pages', 'siteorigin-premium' ),
					'default' => true,
				),
				'exclude_from_search' => array(
					'type' => 'checkbox',
					'label' => __( 'Exclude from search results', 'siteorigin-premium' ),
					'default' => false,
				),
				'icon' => array(
					'type' => 'icon',
					'label' => __( 'Admin Icon', 'siteorigin-premium' ),
					'icons_callback' => array( $this, 'dashicons_callback' ),
				),
				'labels' => array(
					'type' => 'section',
					'label' => __( 'Labels', 'siteorigin-premium' ),
					'fields' => array(
						'single' => array(
							'type' => 'text',
							'label' => __( 'Singular', 'siteorigin-premium' ),
						),
						'plural' => array(
							'type' => 'text',
							'label' => __( 'Plural', 'siteorigin-premium' ),
						),
					),
				),
				'description' => array(
					'type' => 'textarea',
					'label' => __( 'Description', 'siteorigin-premium' )
				),
				'taxonomy' => array(
					'type' => 'repeater',
					'label' => __( 'Taxonomies', 'siteorigin-premium' ),
					'item_label'  => array(
						'selector'     => "[id*='taxonomy-label']",
						'update_event' => 'change',
						'value_method' => 'val',
					),
					'fields' => array(
						'label' => array(
							'type' => 'text',
							'label' => __( 'Label', 'siteorigin-premium' ),
						),
						'slug' => array(
							'type' => 'text',
							'label' => __( 'Slug', 'siteorigin-premium' ),
						),
						'hierarchical' => array(
							'type' => 'checkbox',
							'description' => __( 'Taxonomies are tags by default. Hierarchical taxonomies are categories.', 'siteorigin-premium' ),
							'label' => __( 'Hierarchical', 'siteorigin-premium' ),
						),
					),
				)
			)
		);
	}

	/**
	 * Display the meta box callback.
	 *
	 * @param $post
	 * @param $args
	 */
	function meta_box_callback( $post, $args ){
		$form = $this->get_form();
		$settings = $this->get_post_settings( $post->ID );
		
		$settings['editable_layout'] = $this->is_post_type_layout_editable( $settings['slug'] );

		$form->form( $settings );
		wp_nonce_field( 'save_post_type_settings', '_so_cpt_nonce' );
	}

	/**
	 * Get the post settings for this post
	 *
	 * @param $post_id
	 *
	 * @return array
	 */
	function get_post_settings( $post_id ){
		$settings = get_post_meta( $post_id, 'siteorigin_post_type_settings', true );
		if( empty( $settings ) ) {
			$settings = array();
		}
		return $settings;
	}

	function dashicons_callback( ) {
		return array(
			'dashicons' => array(
				'name' => __( 'Dashicons', 'siteorigin-premium' ),
				'style_uri' => plugin_dir_url(__FILE__) . 'dashicons/style.css',
				'icons' => include plugin_dir_path( __FILE__ ) . 'dashicons/icons.php'
			)
		);
	}

	function sanitize_reserved_post_types( $post_type, $old_value ) {
		static $reserved = array(
			'post',
			'page',
			'attachment',
			'revision',
			'nav_menu_item',
			'action',
			'author',
			'order',
			'theme',
		);

		return ! in_array( $post_type, $reserved ) ? $post_type : $old_value;
	}

	/**
	 * @param $values
	 * @param $type
	 * @param $id
	 *
	 * @return array|mixed
	 */
	function siteorigin_page_settings( $values, $type, $id ){
		if( $type !== 'post' ) return $values;

		$post = get_post( $id );
		$post_types = $this->get_post_types();

		if( isset( $post_types[ $post->post_type ] ) ) {
			$values = get_post_meta( $post_types[ $post->post_type ]->ID, 'siteorigin_page_settings', true );
			if( empty($values) ) $values = array();
		}

		return $values;
	}

}

SiteOrigin_Premium_Plugin_Cpt_Builder::single();
