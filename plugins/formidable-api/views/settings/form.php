<?php wp_nonce_field('frm_save_api_nonce', 'frm_save_api'); ?>

<p>
<label><?php _e('Formidable Hook', 'frmapi'); ?> <span class="frm_required">*</span> <span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php _e('When should data be sent?', 'frmapi') ?>" ></span></label><br/>
<select name="post_title">
    <?php foreach ( $hooks as $hook => $label ) { ?>
    <option value="<?php echo $hook ?>" <?php selected($post->post_title, $hook) ?>><?php echo $label ?></option>
    <?php } ?>
</select>
</p>

<p>
<label><?php _e('Form', 'formidable') ?> <span class="frm_required">*</span></label><br/>
<?php FrmFormsHelper::forms_dropdown('frm_form_id', $post->frm_form_id); ?>
</p>

<?php if ( function_exists('get_json_url') ) { ?>
<p>
<label><?php _e('Notification URL', 'frmapi'); ?> <span class="frm_required">*</span> <span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php _e(sprintf('An API URL on another site would look like %s', get_json_url()), 'frmapi') ?>" ></span></label>
<a class="frmapi_test_connection button-secondary alignright" style="margin-bottom:4px;margin-left:5px;"><?php _e('Test Connection', 'frmapi') ?></a>
<span class="spinner"></span>
<span class="frmapi_test_resp frm_required alignright"></span>
<br/>
<input type="text" name="post_excerpt" value="<?php echo esc_attr($post->post_excerpt) ?>" class="widefat" />
<span class="howto"><?php _e('Notify this URL when the hook selected above is triggered.', 'frmapi') ?></span>
</p>
<?php } else { ?>
<div id="message" class="error"><p><?php _e('Warning: You are missing the WP REST API plugin.', 'formidable') ?></p></div>
<?php } ?>

<p>
<label><?php _e('Handshake Key', 'frmapi'); ?> <span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php _e('This key will be provided by the service you are connecting to if it is required.', 'frmapi') ?>" ></span></label><br/>
<input type="text" name="frm_api_key" value="<?php echo esc_attr($post->frm_api_key) ?>" class="widefat" />
</p>

<p>
<label><?php _e('Data Format', 'frmapi'); ?></label>
<br/>
<textarea name="content" class="widefat code" rows="5"><?php echo esc_html($post->post_content) ?></textarea>
<span class="howto"><?php _e('Leave blank for the default format.', 'frmapi') ?></span>
</p>

<script type="text/javascript">
jQuery(document).ready(function($){
$('.frmapi_test_connection').click(frmapi_test_connection);
});

function frmapi_test_connection(){
    var url = jQuery('input[name="post_excerpt"]').val();
    var key = jQuery('input[name="frm_api_key"]').val();
    
    if (url == '') {
        jQuery('.frmapi_test_connection').html('Please enter a URL');
        return;
    }
    
    jQuery('.frmapi_test_resp').html('').addClass('spinner').show();
    
    jQuery.ajax({
        type:'POST',url:ajaxurl,
        data:'action=frmapi_test_connection&url='+url+'&key='+key,
        success:function(html){
            jQuery('.frmapi_test_resp').removeClass('spinner').html(html);
        }
    });
}
</script>
