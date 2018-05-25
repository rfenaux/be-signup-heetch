<?php

if( class_exists( 'SiteOrigin_Widget' ) ) {

	/**
	 * A form builder based on the SiteOrigin widgets bundle form base. This is not used as an actual widget.
	 *
	 * Class SiteOrigin_Premium_Form
	 */
	class SiteOrigin_Premium_Form extends SiteOrigin_Widget {

		private $name_prefix;
		private $modify_callback;

		/**
		 * @param string $name_prefix
		 * @param string $form_options
		 * @param bool|callable $modify_callback
		 */
		function __construct( $name_prefix, $form_options, $modify_callback = false ) {
			parent::__construct(
				'siteorigin-premium-form',
				__( 'SiteOrigin Premium Form', 'siteorigin-premium' ),
				array(
					'has_preview' => false,
				),
				array(),
				$form_options,
				SiteOrigin_Premium::dir_path(__FILE__)
			);

			$this->name_prefix = $name_prefix;
			$this->modify_callback = $modify_callback;

			static $form_number = 1;
			$this->number = $form_number++;
		}

		/**
		 * Get a specially prefixed name
		 *
		 * @param string $field_name
		 *
		 * @return string
		 */
		function get_field_name( $field_name ){
			return $this->name_prefix . '[' . $field_name . ']';
		}

		/**
		 * Modify the form instance using $modify_callback arg from the constructor.
		 *
		 * @param $form_options
		 *
		 * @return mixed
		 */
		function modify_form( $form_options ) {
			if( !empty( $this->modify_callback ) ) {
				$form_options = call_user_func( $this->modify_callback, $form_options );
			}

			return $form_options;
		}

		/**
		 * Chance the message displayed while loading the form.
		 */
		function scripts_loading_message(){
			?>
			<p><strong><?php _e('Scripts and styles for this form are loading.', 'siteorigin-premium') ?></strong></p>
			<?php
		}

		/**
		 * This widget will never be rendered on the frontend, so add a noop.
		 *
		 * @param array $args
		 * @param array $instance
		 */
		function widget( $args, $instance ) { }


		protected function get_version(){
			return defined( 'SITEORIGIN_PREMIUM_VERSION' ) ? SITEORIGIN_PREMIUM_VERSION : 'dev';
		}

		protected function get_js_suffix(){
			return defined( 'SITEORIGIN_PREMIUM_JS_SUFFIX' ) ? SITEORIGIN_PREMIUM_JS_SUFFIX : '';
		}
	}

}
else {

	class SiteOrigin_Premium_Form {
		function __construct( $name_prefix, $form_options, $modify_callback = false ) {
		}

		function form( $instance ) {
			?><p><?php
			printf(
				__( 'Install the %sWidgets Bundle%s to use this field.' ),
				'<a href="https://wordpress.org/plugins/so-widgets-bundle/" target="_blank" rel="noopener noreferrer">',
				'</a>'
			);
			?></p><?php
		}

		function update( $new_instance, $old_instance ){
			return $old_instance;
		}
	}

}
