<?php
/**
 * Add form options for adding custom network to the social media widget.
 *
 * @param $form_options
 *
 * @return array
 */
function sow_social_media_add_custom_network_form_options( $form_options ) {
	$form_options = array_merge(
		$form_options, array(
			// Add field to allow user to add custom social media networks
			'custom_networks' => array(
				'type'        => 'repeater',
				'label'       => 'Custom Networks',
				'description' => __( 'Add your own social networks.', 'siteorigin-premium' ),
				'item_name'   => __( 'New Network', 'siteorigin-premium' ),
				'item_label'  => array(
					'selector'     => "[id*='custom_networks-name']",
					'update_event' => 'change',
					'value_method' => 'val'
				),
				'fields'      => array(
					'name'         => array(
						'type'  => 'text',
						'label' => __( 'Name', 'siteorigin-premium' )
					),
					'url'          => array(
						'type'  => 'text',
						'label' => __( 'URL', 'siteorigin-premium' )
					),
					'icon_title' => array(
						'type' => 'text',
						'label' => __( 'Icon title', 'siteorigin-premium' ),
					),
					'icon_name'    => array(
						'type'  => 'icon',
						'label' => __( 'Icon', 'siteorigin-premium' ),
					),
					'icon_color'   => array(
						'type'    => 'color',
						'default' => '#FFFFFF',
						'label'   => __( 'Icon color', 'siteorigin-premium' )
					),
					'button_color' => array(
						'type'    => 'color',
						'default' => '#000000',
						'label'   => __( 'Background color', 'siteorigin-premium' )
					),
					'icon_image'   => array(
						'type'  => 'media',
						'label' => __( 'Icon image', 'siteorigin-premium' ),
					),
				)
			)
		)
	);

	return $form_options;
}

add_filter( 'sow_social_media_buttons_form_options', 'sow_social_media_add_custom_network_form_options' );


/**
 * Merge custom networks with default networks so they're displayed in the widget.
 *
 * @param $networks
 * @param $instance
 *
 * @return array
 */
function sow_social_media_merge_custom_networks( $networks, $instance ) {
	if ( ! empty( $instance['custom_networks'] ) ) {
		$custom_networks = $instance['custom_networks'];
		foreach ( $custom_networks as $key => $custom ) {
			$name                                 = preg_replace( '/\s/', '_', $custom['name'] );
			$name                                 = preg_replace( '/[^\w-]/', '', $name );
			$custom_networks[ $key ]['name']      = $name;
			$custom_networks[ $key ]['is_custom'] = true;
		}
		$networks = array_merge( $networks, $custom_networks );
	}

	return $networks;
}

add_filter( 'sow_social_media_buttons_networks', 'sow_social_media_merge_custom_networks', 10, 2 );


/**
 * Replace template or parts of template with premium content.
 *
 * @param $template_html
 * @param $widget_class
 * @param $instance
 * @param $widget
 *
 * @return string
 */
function sow_social_media_premium_template( $template_html, $instance, $widget ) {
	if ( empty( $instance['custom_networks'] ) ) {
		return $template_html;
	}

	foreach ( $instance['custom_networks'] as $custom ) {
		//replace spaces with underscores
		$custom_name = preg_replace( '/\s/', '_', $custom['name'] );
		//remove anything that isn't a word character or a hyphen
		$custom_name      = preg_replace( '/[^\w-]/', '', $custom_name );
		$custom_icon_html = '';
		if ( ! empty( $custom['icon_image'] ) ) {
			$attachment = wp_get_attachment_image_src( $custom['icon_image'] );
			if ( ! empty( $attachment ) ) {
				$icon_styles[] = 'background-image: url(' . esc_url( $attachment[0] ) . ')';
				$custom_icon_html .= '<div class="sow-icon-image" style="' . implode( '; ', $icon_styles ) . '"></div>';
			}
			$premium_regex = '/<!--\s*premium-' . $custom_name . '\s*-->[\s\S]*?<!--\s*endpremium\s*-->/';
			$template_html = preg_replace( $premium_regex, $custom_icon_html, $template_html );
		}
	}

	return $template_html;
}

add_filter( 'siteorigin_widgets_template_html_sow-social-media-buttons', 'sow_social_media_premium_template', 10, 4 );


/**
 * Replace LESS or parts of LESS with premium LESS styles.
 *
 * @param $less
 * @param $instance
 * @param $widget
 *
 * @return string
 */
function sow_social_media_premium_styles( $less, $instance, $widget ) {
	$less .= "
	a {
		.sow-icon-image {
			width: 1em;
			height: 1em;
			background-size: cover;
		}
	}";

	return $less;
}

add_filter( 'siteorigin_widgets_less_sow-social-media-buttons', 'sow_social_media_premium_styles', 10, 3 );
