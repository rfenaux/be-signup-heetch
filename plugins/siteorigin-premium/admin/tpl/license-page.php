<div class="wrap siteorigin-premium-wrap" id="siteorigin-premium-license">

	<div class="page-header">
		<div class="so-premium-icon-wrapper">
			<img src="<?php echo SiteOrigin_Premium::dir_url( __FILE__ ) ?>../img/page-icon.png" class="so-premium-icon" />
		</div>
		<h1><?php _e( 'SiteOrigin Premium License', 'siteorigin-premium' ) ?></h1>
	</div>

	<?php $this->display_key_message( $key ) ?>

	<div class="page-main">
		<form action="<?php echo esc_url( add_query_arg( 'action', 'save' ) ) ?>" method="post">

			<label for="siteorigin-premium-key" class="license-key-label">
				<?php if( SiteOrigin_Premium::is_license_active( $key ) ) : ?>
					<span class="dashicons dashicons-yes"></span>
				<?php endif; ?>

				<?php _e( 'License Key', 'siteorigin-premium' ) ?>

				<?php if( SiteOrigin_Premium::is_license_active( $key ) ) : ?>
					<span class="license-status">
						<?php _e( 'This license is valid and active', 'siteorigin-premium' ) ?>
					</span>
				<?php endif ?>
			</label>
			<div class="key-entry-field">
				<div class="field-wrapper">

					<?php if( !SiteOrigin_Premium::is_license_active( $key ) ) : ?>
						<input type="submit" class="button-primary" name="activate" value="<?php esc_attr_e('Activate', 'siteorigin-premium') ?>">
					<?php else : ?>
						<input type="submit" class="button-secondary" value="<?php esc_attr_e('Save', 'siteorigin-premium') ?>">
					<?php endif ?>

					<div class="input-wrapper">
						<input type="text" name="siteorigin_premium[key]" id="siteorigin-premium-key" value="<?php echo esc_attr( $key ) ?>" />
					</div>
				</div>
			</div>

			<?php wp_nonce_field( 'save_siteorigin_premium' ) ?>
		</form>
	</div>

	<div class="siteorigin-logo">
		<p>
			<?php _e( 'Proudly Created By', 'siteorigin' ) ?>
		</p>
		<a href="https://siteorigin.com/" target="_blank" rel="noopener noreferrer">
			<img src="<?php echo SiteOrigin_Premium::dir_url( __FILE__ ) ?>../img/siteorigin.png" />
		</a>
	</div>

</div>
