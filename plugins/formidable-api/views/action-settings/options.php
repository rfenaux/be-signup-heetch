<p>
	<label for="<?php echo esc_attr( $action_control->get_field_id('frm_api_url') ) ?>">
		<?php _e( 'Notification URL', 'frmapi' ); ?>
		<span class="frm_required">*</span>
	</label>
	<a class="frmapi_test_connection button-secondary alignright" style="margin-bottom:4px;margin-left:5px;"><?php _e( 'Test Connection', 'frmapi' ) ?></a>
	<span class="spinner"></span>
	<span class="frmapi_test_resp frm_required alignright"></span>
	<br/>
	<input type="text" name="<?php echo esc_attr( $action_control->get_field_name('url') ) ?>" value="<?php echo esc_attr( $form_action->post_content['url'] ); ?>" class="frm_not_email_message widefat" id="<?php echo esc_attr( $action_control->get_field_id('frm_api_url') ) ?>" />
	<span class="howto"><?php esc_html_e( 'Notify this URL when the hook selected above is triggered.', 'frmapi' ) ?></span>
</p>

<p>
	<label for="<?php echo esc_attr( $action_control->get_field_id('frm_api_basic_auth') ) ?>">
		<?php _e( 'Basic Auth', 'frmapi' ); ?>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'A colon (:) separated username, password combo for standard HTTP authentication. This key will be provided by the service you are connecting to if it is required.', 'frmapi' ) ?>" ></span>
	</label><br/>
	<input type="text" name="<?php echo esc_attr( $action_control->get_field_name('api_key') ) ?>" value="<?php echo esc_attr( $api_key ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'Username:Password', 'frmapi' ) ?>" id="<?php echo esc_attr( $action_control->get_field_id('frm_api_basic_auth') ) ?>" />
</p>

<p>
	<label class="frm_left_label" for="<?php echo esc_attr( $action_control->get_field_id('frm_api_data_format') ) ?>">
		<?php _e( 'Data Format', 'frmapi' ); ?>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'JSON is a standard format for most REST APIs. The Form option will submit a form on another page with any name value pairs of your choosing. If you select Form, there must be a form displayed on the page you submit to.', 'frmapi' ) ?>" ></span>
	</label>
	<select name="<?php echo esc_attr( $action_control->get_field_name('format') ) ?>" id="<?php echo esc_attr( $action_control->get_field_id('frm_api_data_format') ) ?>" class="frmapi_data_format">
		<?php foreach ( array( 'json', 'form', 'raw' ) as $format ) { ?>
			<option value="<?php echo esc_attr( $format ) ?>" <?php selected( $format, $form_action->post_content['format'] ) ?>>
				<?php echo esc_html( $format ); ?>
			</option>
		<?php } ?>
	</select>
</p>

<p class="frm_data_raw <?php echo esc_attr( 'raw' == $form_action->post_content['format'] ? '' : 'frm_hidden' ) ?>" >
	<label for="<?php echo esc_attr( $action_control->get_field_id('frm_raw_format') ) ?>">
		<?php _e('Raw Data', 'frmapi'); ?>
	</label>
	<a class="frmapi_insert_default_json button-secondary alignright" style="margin-bottom:4px;">
		<?php _e( 'Insert Default', 'frmapi' ) ?>
	</a>
	<br/>
	<textarea name="<?php echo esc_attr( $action_control->get_field_name('data_format') ) ?>" class="frm_not_email_message large-text" rows="5" id="<?php echo esc_attr( $action_control->get_field_id('frm_raw_format') ) ?>"><?php echo esc_html( $form_action->post_content['data_format'] ); ?></textarea>
</p>

<div id="postcustomstuff" class="frm_data_json frm_data_form <?php echo esc_attr( 'raw' == $form_action->post_content['format'] ? 'frm_hidden' : '' ) ?>">
	<table id="list-table">
		<thead>
			<tr>
				<th class="left">
					<?php _e( 'Key', 'formidable' ) ?>
					<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Use a pipe (|) to create a nested keys. item_meta|25 will be changed to item_meta[25].', 'frmapi' ) ?>" ></span>
				</th>
				<th><?php _e( 'Value', 'formidable' ) ?></th>
				<th style="width:35px;"></th>
			</tr>
		</thead>

		<tbody class="frmapi_data_rows" data-wp-lists="list:meta">

			<?php
			foreach ( $data_fields as $row_num => $data ) {
				if ( ( isset( $data['key'] ) && ! empty( $data['key'] ) ) || $row_num == 0 ) {
					include( dirname( __FILE__ ) . '/_data_row.php' );
				}
				unset( $row_num, $data );
			}
			?>
		</tbody>
	</table>
</div>

<p>
	<label class="frm_left_label" for="<?php echo esc_attr( $action_control->get_field_id('frm_api_method') ) ?>">
		<?php _e( 'Method', 'frmapi' ); ?>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'The method determines how the data is handled on the receiving end. Generally, POST = create, GET = read, PUT/PATCH = update, DELETE = delete.', 'frmapi' ) ?>" ></span>
	</label>
	<select name="<?php echo esc_attr( $action_control->get_field_name('method') ) ?>" id="<?php echo esc_attr( $action_control->get_field_id('frm_api_method') ) ?>">
		<?php foreach ( array( 'POST', 'GET', 'PUT', 'PATCH', 'DELETE' ) as $method ) { ?>
			<option value="<?php echo esc_attr( $method ) ?>" <?php selected( $method, $form_action->post_content['method'] ) ?>>
				<?php echo esc_html( $method ); ?>
			</option>
		<?php } ?>
	</select>
</p>