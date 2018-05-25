<?php

/**
 * This file is only ever loaded when this plugin is accidently installed as a theme.
 */

function siteorigin_premium_theme_admin_notice() {
	$instructions_url = 'https://siteorigin.com/premium-documentation/install-siteorigin-premium/';
	?>
	<div class="error">
		<p>
			<?php _e( "<strong>Warning</strong>: You're trying to use <strong>SiteOrigin Premium</strong> as a theme. You need to deactivate it and reinstall it as a plugin. Please reactivate the theme you were using previously.", 'siteorigin-premium' ); ?>
			<a href="<?php echo esc_url($instructions_url) ?>"><?php _e('Read More', 'siteorigin-premium') ?></a>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'siteorigin_premium_theme_admin_notice' );