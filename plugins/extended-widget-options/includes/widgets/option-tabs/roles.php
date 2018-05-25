<?php
/**
 * Roles Widget Options
 *
 * @copyright   Copyright (c) 2015, Jeffrey Carandang
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add Roles Widget Options Tab
 *
 * @since 1.0
 * @return void
 */

 /**
 * Called on 'extended_widget_opts_tabs'
 * create new tab navigation for alignment options
 */
function widgetopts_tab_roles( $args ){ ?>
    <li class="extended-widget-opts-tab-roles">
        <a href="#extended-widget-opts-tab-<?php echo $args['id'];?>-roles" title="<?php _e( 'Roles', 'widget-options' );?>" ><span class="dashicons dashicons-admin-users"></span> <span class="tabtitle"><?php _e( 'Roles', 'widget-options' );?></span></a>
    </li>
<?php
}
add_action( 'extended_widget_opts_tabs', 'widgetopts_tab_roles' );

/**
 * Called on 'extended_widget_opts_tabcontent'
 * create new tab content options for alignment options
 */
function widgetopts_tabcontent_roles( $args ){
    $roles          = get_editable_roles();
    $options_role   = '';
    if( isset( $args['params']['roles'][ 'options' ] ) ){
        $options_role = $args['params']['roles'][ 'options' ];
    }
    ?>
    <div id="extended-widget-opts-tab-<?php echo $args['id'];?>-roles" class="extended-widget-opts-tabcontent extended-widget-opts-tabcontent-roles">
        <p>
            <strong><?php _e( 'Hide/Show', 'widget-options' );?></strong>
            <select class="widefat" name="<?php echo $args['namespace'];?>[extended_widget_opts][roles][options]">
                <option value="hide" <?php if( $options_role == 'hide' ){ echo 'selected="selected"'; }?> ><?php _e( 'Hide on checked roles', 'widget-options' );?></option>
                <option value="show" <?php if( $options_role == 'show' ){ echo 'selected="selected"'; }?>><?php _e( 'Show on checked roles', 'widget-options' );?></option>
            </select>
        </p>
        <div class="extended-widget-opts-inner-roles" style="max-height: 230px;padding: 5px;overflow:auto;">
            <table class="form-table">
                <tbody>
                     <tr valign="top">
                        <td scope="row"><strong><?php _e( 'Roles', 'widget-options' );?></strong></td>
                        <td>&nbsp;</td>
                    </tr>
                    <?php foreach ( $roles as $role_name => $role_info ) {
                        if( isset( $args['params'] ) && isset( $args['params']['roles'] ) ){
                            if( isset( $args['params']['roles'][ $role_name ] ) ){
                                $checked = 'checked="checked"';
                            }else{
                                $checked = '';
                            }
                        }else{
                            $checked = '';
                        }
                        ?>
                        <tr valign="top">
                            <td scope="row"><label for="extended_widget_opts-<?php echo $args['id'];?>-role-<?php echo $role_name;?>"><?php echo $role_info['name'];?></label></td>
                            <td>
                                <input type="checkbox" name="<?php echo $args['namespace'];?>[extended_widget_opts][roles][<?php echo $role_name;?>]" id="extended_widget_opts-<?php echo $args['id'];?>-role-<?php echo $role_name;?>" value="1" <?php echo $checked;?> />
                            </td>
                        </tr>
                    <?php } ?>
                    <tr valign="top">
                        <td scope="row"><?php _e( 'Guests', 'widget-options' );?></td>
                        <td>
                            <input type="checkbox" name="<?php echo $args['namespace'];?>[extended_widget_opts][roles][guests]" value="1" <?php if( isset( $args['params']['roles'][ 'guests' ] ) ){ echo 'checked="checked"'; };?> />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
<?php
}
add_action( 'extended_widget_opts_tabcontent', 'widgetopts_tabcontent_roles'); ?>
