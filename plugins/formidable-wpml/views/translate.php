<div id="form_settings_page" class="wrap">
    <div class="frmicon icon32"><br/></div>
    <h2><?php _e('Translate Form', 'formidable') ?></h2>
	<?php include($path .'/classes/views/shared/errors.php'); ?>

    <?php FrmAppController::get_form_nav($id, true); ?>
    
<form method="post">
    <p style="clear:left;">        
        <input type="submit" value="<?php _e('Update', 'formidable') ?>" class="button-primary" />
        <?php _e('or', 'formidable') ?>
        <a class="button-secondary cancel" href="<?php echo admin_url('admin.php?page=formidable') ?>&amp;frm_action=settings&amp;id=<?php echo $id ?>"><?php _e('Cancel', 'formidable') ?></a>
    </p>
    
    <div class="clear"></div> 

    <div id="poststuff" class="metabox-holder">
    <div id="post-body">
    
        <input type="hidden" name="id" value="<?php echo esc_attr( $id ); ?>" />
        <input type="hidden" name="frm_action" value="update_translate" />
        <?php wp_nonce_field('frm_translate_form_nonce', 'frm_translate_form'); ?>

        <table class="widefat fixed">
        <thead>
            <tr>
            <th class="manage-column" width="170px"> </th>
            <?php foreach ( $langs as $lang ) {
                if ( $lang['code'] == $default_language ) {
                    continue;
                }

                $col_order[] = $lang['code'];
                ?>
			<th class="manage-column frm_lang_<?php echo esc_attr( $lang['code'] ) ?>"><?php echo wp_kses_post( $lang['display_name'] ); ?></th>
            <?php } ?>
            </tr>
        </thead>
        <tbody>
        <?php
        $alternate = false;
		foreach ( $strings as $string ) { 
			$name = preg_replace( '/^' . $id . '_/', '', $string->name, 1 ); 
            $alternate = ($alternate == '') ? 'alternate' : '';
            $col = 0;

			$string->value = FrmWpmlAppController::maybe_register_string( $string, compact( 'id', 'fields', 'form' ) );
            
            if(is_array($string->value)) 
                continue;
              
            if ( $string->value == '' || $string->value == '*') {
                FrmWpmlAppController::unregister_single_wpml_string( $string->name );
                continue;
            }

        ?>
		<tr class="<?php echo esc_attr( $alternate ); ?>">
			<td><?php echo wp_kses_post( htmlspecialchars( stripslashes( $string->value) ) ); ?></td>
        <?php
            foreach ( $translations as $trans ) {
				if ( $trans->string_id != $string->id || ! in_array( $trans->language, $col_order ) || $trans->language == $default_language ) {
                    continue;
                }

                $col++; 
                $next_col = array_search($trans->language, $col_order);
				for ( $col; $col < $next_col; $col++ ) { ?>
        <td>
			<?php if ( strlen( $string->value ) > 80 ) { ?>
			<textarea name="frm_wpml[<?php echo esc_attr( $string->id . '_' . $col_order[ $col ] ) ?>][value]" class="large-text"></textarea>
            <?php }else{ ?>
			<input type="text" value="" name="frm_wpml[<?php echo esc_attr( $string->id . '_' . $col_order[ $col ] ) ?>][value]" class="large-text" />
            <?php } ?>
            <input type="checkbox" value="<?php echo esc_attr( ICL_STRING_TRANSLATION_COMPLETE ) ?>" id="<?php echo esc_attr( $string->id . '_' . $col_order[ $col ] ) ?>_status" name="frm_wpml[<?php echo esc_attr( $string->id . '_' . $col_order[ $col ] ) ?>][status]" />
			<label for="<?php echo esc_attr( $string->id . '_' . $col_order[ $col ] ) ?>_status"><?php _e( 'Complete', 'formidable' ) ?></label>
        </td>
        <?php
                }
         ?>
        <td>
			<?php if ( strlen($string->value) > 80 ) { ?>
            <textarea name="frm_wpml[<?php echo esc_attr( $trans->id ) ?>][value]" class="large-text"><?php echo FrmAppHelper::esc_textarea( stripslashes( $trans->value ) ) ?></textarea>
            <?php }else{ ?>
            <input type="text" value="<?php echo esc_attr( stripslashes( $trans->value ) ) ?>" name="frm_wpml[<?php echo esc_attr( $trans->id ) ?>][value]" class="large-text" />
            <?php } ?>
			<input type="checkbox" value="<?php echo esc_attr( ICL_STRING_TRANSLATION_COMPLETE ) ?>" id="<?php echo esc_attr( $trans->id ) ?>_status" name="frm_wpml[<?php echo esc_attr( $trans->id ) ?>][status]" <?php checked( $trans->status, ICL_STRING_TRANSLATION_COMPLETE ) ?>/>
			<label for="<?php echo esc_attr( $trans->id ) ?>_status"><?php _e( 'Complete', 'formidable' ) ?></label>
        </td>
        <?php
                unset($trans);
            }

            if($col < $lang_count){
                $col++; 
                for($col; $col<=$lang_count; $col++){ ?>
        <td>
            <?php if ( strlen( $string->value ) > 80 ) { ?>
            <textarea name="frm_wpml[<?php echo esc_attr( $string->id .'_'. $col_order[ $col ] ) ?>][value]" class="large-text"></textarea>
            <?php }else{ ?>
            <input type="text" value="" name="frm_wpml[<?php echo esc_attr( $string->id .'_'. $col_order[ $col ] ) ?>][value]" class="large-text" />
            <?php } ?>
			<input type="checkbox" value="<?php echo esc_attr( ICL_STRING_TRANSLATION_COMPLETE ) ?>" id="<?php echo esc_attr( $string->id .'_'. $col_order[ $col ] ) ?>_status" name="frm_wpml[<?php echo esc_attr( $string->id . '_' . $col_order[ $col ] ) ?>][status]" />
			<label for="<?php echo esc_attr( $string->id . '_' . $col_order[ $col ] ) ?>_status"><?php _e( 'Complete', 'formidable' ) ?></label>
        </td>
        <?php
                }
            }
            unset($string);
        ?>
        </tr>
        <?php
        }
        ?> 
        </tr>
        </tbody>
        </table>
        <p class="howto"><?php printf(__('If you are missing parts of the form that need translation, please visit the %1$sWPML Translation Management%2$s page then return.', 'formidable'), '<a href="'.  admin_url('admin.php?page=wpml-translation-management/menu/main.php') .'">', '</a>'); ?></p>

    </div>

    </div>
    <p>        
        <input type="submit" value="<?php _e('Update', 'formidable') ?>" class="button-primary" />
        <?php _e('or', 'formidable') ?>
        <a class="button-secondary cancel" href="<?php echo admin_url('admin.php?page=formidable') ?>&amp;frm_action=settings&amp;id=<?php echo $id ?>"><?php _e('Cancel', 'formidable') ?></a>
    </p>
    </form>

</div>

<script type="text/javascript">
jQuery(document).ready(function($){
$('input[name^="frm_wpml"]:not([type=checkbox])').change(frmWPMLComplete);
})
function frmWPMLComplete(){
    if(jQuery(this).val() != ''){
        jQuery(this).next('input[type=checkbox]').prop('checked', true);
    }
}
</script>