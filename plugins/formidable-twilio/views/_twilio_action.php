<table class="form-table frm-no-margin">
<tr>
	<th><label><?php _e( 'To', 'frmtwlo' ) ?></label>
    </th>
	<td><input type="text" name="<?php echo esc_attr( $this->get_field_name( 'to' ) ) ?>" value="<?php echo esc_attr( $form_action->post_content['to'] ); ?>" class="frm_not_email_to frm_email_blur large-text" id="<?php echo esc_attr( $this->get_field_id( 'to' ) ) ?>" />
    </td>
</tr>
<tr>
	<th><label><?php _e( 'From', 'frmtwlo' ) ?></label>
    </th>
	<td><input type="text" name="<?php echo esc_attr( $this->get_field_name( 'from' ) ) ?>" value="<?php echo esc_attr( $form_action->post_content['from'] ); ?>" class="frm_not_email_to frm_email_blur large-text" id="<?php echo esc_attr( $this->get_field_id( 'from' ) ) ?>" /></td>
</tr>
<tr>
	<td colspan="2" style="padding-top:0">
		<p><label><?php _e( 'Message', 'frmtwlo' ) ?> </label><br/>
		<textarea name="<?php echo esc_attr( $this->get_field_name( 'message' ) ) ?>" class="frm_not_email_message large-text" id="<?php echo esc_attr( $this->get_field_id( 'message' ) ) ?>" cols="50" rows="5"><?php echo FrmAppHelper::esc_textarea( $form_action->post_content['message'] ) ?></textarea></p>

    </td>
</tr>
</table>