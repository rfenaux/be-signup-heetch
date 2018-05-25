<?php
/*
Plugin Name: SiteOrigin Lightbox
Description: Enable a lightbox on various image widgets.
Version: 1.0.0
Author: SiteOrigin
Author URI: https://siteorigin.com
License: GPL3
License URI: https://www.gnu.org/licenses/gpl-3.0.txt
Documentation: https://siteorigin.com/premium-documentation/plugin-addons/lightbox/
Tags: Widgets Bundle
Video: 1ko8hks81p
*/

class SiteOrigin_Premium_Plugin_Lightbox {

	const SO_IMAGE_ID_BASE = 'sow-image';
	const SO_IMAGE_GRID_ID_BASE = 'sow-image-grid';
	const SO_SIMPLE_MASONRY_ID_BASE = 'sow-simple-masonry';
	const SO_IMAGE_SLIDER_ID_BASE = 'sow-slider';

	function __construct() {
		$widget_ids = array(
			self::SO_IMAGE_ID_BASE,
			self::SO_IMAGE_GRID_ID_BASE,
			self::SO_SIMPLE_MASONRY_ID_BASE,
			self::SO_IMAGE_SLIDER_ID_BASE,
		);

		foreach ( $widget_ids as $widget_id ) {
			add_filter( 'siteorigin_widgets_form_options_' . $widget_id, array( $this, 'admin_form_options' ) );
			add_filter( 'siteorigin_widgets_form_instance_' . $widget_id, array(
				$this,
				'admin_form_instance'
			), 10, 2 );
			add_filter( 'siteorigin_widgets_template_variables_' . $widget_id, array(
				$this,
				'update_template_vars'
			), 10, 4 );
		}

		// Some special handling for the slider wrapper
		add_filter( 'siteorigin_widgets_slider_wrapper_attributes', array( $this, 'update_slider_wrapper' ), 10, 3 );
	}

	public static function single() {
		static $single;

		return empty( $single ) ? $single = new self() : $single;
	}

	public function get_settings_form() {
		return new SiteOrigin_Premium_Form(
			'so-addon-lightbox-settings',
			array(
				'always_show_nav_on_touch_devices' => array(
					'type'    => 'checkbox',
					'label'   => __( 'Always show navigation controls on touch devices.', 'siteorigin-premium' ),
					'default' => false,
				),
				// This option is documented here: http://www.lokeshdhakar.com/projects/lightbox2/#options
				// but doesn't appear to have any effect.
//				'disable_scrolling' => array(
//					'type' => 'checkbox',
//					'label' => __( 'Disable scrolling while the lightbox is open.', 'siteorigin-premium' ),
//					'default' => false,
//				),
				'overlay_color'                    => array(
					'type'    => 'color',
					'label'   => __( 'Overlay color', 'siteorigin-premium' ),
					'default' => '#000'
				),
				'overlay_opacity'                  => array(
					'type'    => 'slider',
					'label'   => __( 'Overlay opacity', 'siteorigin-premium' ),
					'min'     => 0,
					'max'     => 1,
					'step'    => 0.01,
					'default' => 0.8,
				),
				'fade_duration'                    => array(
					'type'        => 'number',
					'label'       => __( 'Fade duration', 'siteorigin-premium' ),
					'default'     => 600,
					'description' => __( 'Time, in milliseconds, for the lightbox container and overlay to fade in/out.', 'siteorigin-premium' )
				),
				'fit_images_in_viewport'           => array(
					'type'        => 'checkbox',
					'label'       => __( 'Fit images in viewport.', 'siteorigin-premium' ),
					'default'     => true,
					'description' => __( 'Resize images that are too large to fit in the current viewport.', 'siteorigin-premium' ),
				),
				'image_fade_duration'              => array(
					'type'        => 'number',
					'label'       => __( 'Image fade duration', 'siteorigin-premium' ),
					'default'     => 600,
					'description' => __( 'Time, in milliseconds, for an image to fade in, once loaded.', 'siteorigin-premium' )
				),
				'max_height'                       => array(
					'type'  => 'number',
					'label' => __( 'Max image height (in pixels)', 'siteorigin-premium' ),
				),
				'max_width'                        => array(
					'type'  => 'number',
					'label' => __( 'Max image width (in pixels)', 'siteorigin-premium' ),
				),
				'position_from_top'                => array(
					'type'        => 'number',
					'label'       => __( 'Top offset', 'siteorigin-premium' ),
					'default'     => 50,
					'description' => __( 'Position, in pixels, of the lightbox from the top of the viewport.', 'siteorigin-premium' ),
				),
				'resize_duration'                  => array(
					'type'    => 'number',
					'label'   => __( 'Resize duration', 'siteorigin-premium' ),
					'default' => 700,
				),
				'show_image_number_label'          => array(
					'type'        => 'checkbox',
					'label'       => __( 'Show image number label', 'siteorigin-premium' ),
					'default'     => true,
					'description' => __( 'Show text indicating the current image number and the total number of images in the set.', 'siteorigin-premium' ),
				),
				'disable_caption'                  => array(
					'type'  => 'checkbox',
					'label' => __( 'Disable captions for all lightboxes.', 'siteorigin-premium' ),
				),
				'wrap_around'                      => array(
					'type'        => 'checkbox',
					'label'       => __( 'Wrap around', 'siteorigin-premium' ),
					'default'     => true,
					'description' => __( 'Go back to the first image when the last image is reached.', 'siteorigin-premium' ),
				),
			)
		);
	}

	public function admin_form_options( $form_options ) {
		if ( empty( $form_options ) ) {
			return $form_options;
		}

		$form_options['enable_lightbox'] = array(
			'type'          => 'checkbox',
			'label'         => __( 'Enable Lightbox', 'siteorigin-premium' ),
			'description'   => __( 'This will override the destination URL.', 'siteorigin-premium' ),
			'state_emitter' => array(
				'callback' => 'conditional',
				'args'     => array(
					'enable_lightbox[show]: val',
					'enable_lightbox[hide]: ! val',
				),
			),
		);
		$form_options['image_set_slug'] = array(
			'type'          => 'text',
			'label'         => __( 'Album name', 'siteorigin-premium' ),
			'description'   => __( 'Images with the same album name will be displayed together.', 'siteorigin-premium' ),
			'sanitize'      => 'sanitize_title_with_dashes',
			'state_handler' => array(
				'enable_lightbox[show]' => array( 'slideDown' ),
				'enable_lightbox[hide]' => array( 'slideUp' ),
			),
		);
		$form_options['disable_caption'] = array(
			'type'          => 'checkbox',
			'label'         => __( 'Disable caption for this lightbox', 'siteorigin-premium' ),
			'state_handler' => array(
				'enable_lightbox[show]' => array( 'slideDown' ),
				'enable_lightbox[hide]' => array( 'slideUp' ),
			),
		);

		return $form_options;
	}

	public function admin_form_instance( $instance, $widget ) {
		$instance['image_set_slug'] = $this->unslugify( $instance['image_set_slug'] );

		return $instance;
	}

	private function unslugify( $name ) {
		return implode( ' ', array_map( 'ucfirst', explode( '-', $name ) ) );
	}

	/**
	 * @param $template_vars array
	 * @param $instance array
	 * @param $args array
	 * @param $widget WP_Widget
	 *
	 * @return mixed
	 */
	public function update_template_vars( $template_vars, $instance, $args, $widget ) {
		if ( ! empty( $instance['enable_lightbox'] ) ) {
			wp_enqueue_script(
				'sow-lightbox',
				plugin_dir_url( __FILE__ ) . 'js/lib/lightbox/js/lightbox' . SITEORIGIN_PREMIUM_JS_SUFFIX . '.js',
				array( 'jquery' ),
				'2.9.0',
				true
			);
			wp_enqueue_style(
				'sow-lightbox',
				plugin_dir_url( __FILE__ ) . 'js/lib/lightbox/css/lightbox' . SITEORIGIN_PREMIUM_JS_SUFFIX . '.css',
				array(),
				'2.9.0'
			);
			wp_enqueue_script(
				'sow-lightbox-options',
				plugin_dir_url( __FILE__ ) . 'js/lightbox-options' . SITEORIGIN_PREMIUM_JS_SUFFIX . '.js',
				array( 'jquery', 'sow-lightbox' ),
				SITEORIGIN_PREMIUM_VERSION,
				true
			);

			$premium_options = SiteOrigin_Premium_Options::single();

			$lightbox_settings = $premium_options->get_settings( 'plugin/lightbox' );

			$lightbox_settings['album_label'] = $this->unslugify( $instance['image_set_slug'] ) . ' ' . __( '%1 of %2', 'siteorigin-premium' );
			if ( ! empty( $lightbox_settings['disable_caption'] ) || ! empty( $instance['disable_caption'] ) ) {
				$lightbox_settings['show_image_number_label'] = false;
			}

			switch ( $widget->id_base ) {
				case self::SO_IMAGE_ID_BASE:
					$template_vars = $this->update_image_vars( $template_vars, $instance, $lightbox_settings );
					break;
				case self::SO_IMAGE_GRID_ID_BASE:
					$template_vars = $this->update_image_grid_vars( $template_vars, $instance, $lightbox_settings );
					break;
				case self::SO_SIMPLE_MASONRY_ID_BASE:
					$template_vars = $this->update_simple_masonry_vars( $template_vars, $instance, $lightbox_settings );
					break;
				case self::SO_IMAGE_SLIDER_ID_BASE:
					$template_vars = $this->update_image_slider_vars( $template_vars, $instance, $lightbox_settings );
					break;
			}
		}

		return $template_vars;
	}

	private function update_image_vars( $template_vars, $instance, $lightbox_settings ) {

		if ( ! empty( $instance['image'] ) ) {
			$src = wp_get_attachment_image_src( $instance['image'], 'full' );
			$template_vars['url'] = ! empty( $src ) ? $src[0] : '';
		} else {
			$template_vars['url'] = '';
		}

		$link_atts = empty( $template_vars['link_attributes'] ) ? array() : $template_vars['link_attributes'];

		$link_atts['data-lightbox'] = empty( $instance['image_set_slug'] ) ? $template_vars['title'] : $instance['image_set_slug'];

		$link_atts['data-lightbox-options'] = json_encode( siteorigin_widgets_underscores_to_camel_case( $lightbox_settings ) );

		if ( empty( $lightbox_settings['disable_caption'] ) && empty( $instance['disable_caption'] ) ) {
			$link_atts['data-title'] = $template_vars['title'];
		}

		if ( empty( $link_atts['data-lightbox'] ) ) {
			if ( ! empty( $instance['_sow_form_id'] ) ) {
				$link_atts['data-lightbox'] = $instance['_sow_form_id'];
			} else {
				$link_atts['data-lightbox'] = $template_vars['url'];
			}
		}

		$template_vars['link_attributes'] = $link_atts;

		return $template_vars;
	}

	private function update_image_grid_vars( $template_vars, $instance, $lightbox_settings ) {

		foreach ( $template_vars['images'] as &$image ) {
			$src = wp_get_attachment_image_src( $image['image'], 'full' );
			$image['url'] = ! empty( $src ) ? $src[0] : '';

			$link_atts = empty( $image['link_attributes'] ) ? array() : $image['link_attributes'];

			$link_atts['data-lightbox'] = empty( $instance['image_set_slug'] ) ? $image['title'] : $instance['image_set_slug'];

			$link_atts['data-lightbox-options'] = json_encode( siteorigin_widgets_underscores_to_camel_case( $lightbox_settings ) );

			if ( empty( $lightbox_settings['disable_caption'] ) && empty( $instance['disable_caption'] ) ) {
				$link_atts['data-title'] = $image['title'];
			}

			if ( empty( $link_atts['data-lightbox'] ) ) {
				if ( ! empty( $instance['_sow_form_id'] ) ) {
					$link_atts['data-lightbox'] = $instance['_sow_form_id'];
				} else {
					$link_atts['data-lightbox'] = $image['url'];
				}
			}

			$image['link_attributes'] = $link_atts;
		}

		return $template_vars;
	}

	private function update_simple_masonry_vars( $template_vars, $instance, $lightbox_settings ) {

		foreach ( $template_vars['items'] as &$item ) {
			$src = wp_get_attachment_image_src( $item['image'], 'full' );
			$item['url'] = ! empty( $src ) ? $src[0] : '';

			$link_atts = empty( $item['link_attributes'] ) ? array() : $item['link_attributes'];

			$link_atts['data-lightbox'] = empty( $instance['image_set_slug'] ) ? $item['title'] : $instance['image_set_slug'];

			$link_atts['data-lightbox-options'] = json_encode( siteorigin_widgets_underscores_to_camel_case( $lightbox_settings ) );

			if ( empty( $lightbox_settings['disable_caption'] ) && empty( $instance['disable_caption'] ) ) {
				$link_atts['data-title'] = $item['title'];
			}

			if ( empty( $link_atts['data-lightbox'] ) ) {
				if ( ! empty( $instance['_sow_form_id'] ) ) {
					$link_atts['data-lightbox'] = $instance['_sow_form_id'];
				} else {
					$link_atts['data-lightbox'] = $item['url'];
				}
			}

			$item['link_attributes'] = $link_atts;
		}

		return $template_vars;
	}

	private function update_image_slider_vars( $template_vars, $instance, $lightbox_settings ) {

		foreach ( $template_vars['frames'] as &$frame ) {
			$foreground_src = siteorigin_widgets_get_attachment_image_src(
				$frame['foreground_image'],
				'full',
				! empty( $frame['foreground_image_fallback'] ) ? $frame['foreground_image_fallback'] : ''
			);

			if ( ! empty( $foreground_src ) ) {
				$frame['url'] = $foreground_src[0];
			} else if ( empty( $frame['background_videos'] ) ) {
				$background_src = siteorigin_widgets_get_attachment_image_src(
					$frame['background_image'],
					'full',
					! empty( $frame['background_image_fallback'] ) ? $frame['background_image_fallback'] : ''
				);
				$frame['url'] = ! empty( $background_src ) ? $background_src[0] : '';
			} else {
				$frame['url'] = '';
			}

			$link_atts = empty( $frame['link_attributes'] ) ? array() : $frame['link_attributes'];

			if ( ! empty( $instance['image_set_slug'] ) ) {
				$link_atts['data-lightbox'] = $instance['image_set_slug'];
			} else if ( ! empty( $instance['_sow_form_id'] ) ) {
				$link_atts['data-lightbox'] = $instance['_sow_form_id'];
			} else {
				$link_atts['data-lightbox'] = $frame['url'];
			}

			$link_atts['data-lightbox-options'] = json_encode( siteorigin_widgets_underscores_to_camel_case( $lightbox_settings ) );

			$frame['link_attributes'] = $link_atts;
		}

		return $template_vars;
	}

	public function update_slider_wrapper( $wrapper_attributes, $frame, $background ) {
		if ( ! empty( $frame['link_attributes'] ) && ! empty( $frame['link_attributes']['data-lightbox'] ) ) {
			// Prevent slider JS handling clicks when we're using lightbox.
			unset( $wrapper_attributes['data-url'] );
		}

		return $wrapper_attributes;
	}
}

SiteOrigin_Premium_Plugin_Lightbox::single();
