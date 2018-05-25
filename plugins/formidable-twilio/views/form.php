<table class="form-table">
    <tr class="form-field">
		<th><label><?php _e( 'Account SID', 'frmtwlo' ) ?></label></th>
        <td>
			<input type="text" name="frm_twlo_account_sid" id="frm_twlo_account_sid" value="<?php echo esc_attr( $frm_twlo_settings->settings['account_sid'] ) ?>" class="frm_long_input" />
        </td>
    </tr>
    <tr class="form-field">
		<th><label><?php _e( 'Auth Token', 'frmtwlo' ) ?></label></th>
        <td>
			<input type="text" name="frm_twlo_auth_token" id="frm_twlo_auth_token" value="<?php echo esc_attr( $frm_twlo_settings->settings['auth_token'] ) ?>" class="frm_long_input" />
        </td>
    </tr>
</table>