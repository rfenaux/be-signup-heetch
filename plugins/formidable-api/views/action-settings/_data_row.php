
<tr id="<?php echo esc_attr( $action_control->get_field_id('frm_api_data_' . $row_num ) ) ?>" class="frm_postmeta_row alternate">
    <td class="left">
		<label class="screen-reader-text" for="<?php echo esc_attr( $action_control->get_field_id( 'frm_api_data_key' . $row_num ) ) ?>">
			<?php _e( 'Name') ?>
		</label>

		<input type="text" value="<?php echo esc_attr( $data['key'] ) ?>" name="<?php echo esc_attr( $action_control->get_field_name( 'data_fields' ) ) ?>[<?php echo absint( $row_num ) ?>][key]" id="<?php echo esc_attr( $action_control->get_field_id( 'frm_api_data_key' . $row_num ) ) ?>" class="frm_not_email_message" />
    </td>
    <td>
		<label class="screen-reader-text" for="<?php echo esc_attr( $action_control->get_field_id( 'frm_api_data_value' . $row_num ) ) ?>">
			<?php _e( 'Value', 'formidable' ) ?>
		</label>
		<input type="text" name="<?php echo esc_attr( $action_control->get_field_name( 'data_fields' ) ) ?>[<?php echo absint( $row_num ) ?>][value]" value="<?php echo esc_attr( $data['value'] ) ?>" id="<?php echo esc_attr( $action_control->get_field_id( 'frm_api_data_value' . $row_num ) ) ?>" class="frm_not_email_message" />
    </td>
    <td>
        <div style="padding-top:12px">
			<?php if ( $row_num ) { ?>
			<a href="javascript:void(0)" class="frm_remove_tag frm_icon_font" data-removeid="<?php echo esc_attr( $action_control->get_field_id('frm_api_data_' . $row_num ) ) ?>"></a>
			<?php } ?>
            <a href="javascript:void(0)" class="frm_add_tag frm_icon_font frmapi_add_data_row"></a>
        </div>
    </td>
</tr>
