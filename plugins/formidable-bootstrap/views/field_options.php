<?php if ( in_array( $display['type'], $field_types ) || ( isset( $field['data_type'] ) && in_array( $field['data_type'], $field_types ) ) ) { ?>
<tr><td><label><?php _e('Prepend and Append', 'frmbtsp')?></label></td>
    <td><?php _e('Include before input:') ?>
        <input type="text" name="field_options[btsp_<?php echo $field['id'] ?>][prepend]" value="<?php echo esc_attr($field['btsp']['prepend']) ?>" size="3" />
        <?php _e('Include after input:') ?>
        <input type="text" name="field_options[btsp_<?php echo $field['id'] ?>][append]" value="<?php echo esc_attr($field['btsp']['append']) ?>" size="3" />
    </td>
</tr>
<?php } ?>