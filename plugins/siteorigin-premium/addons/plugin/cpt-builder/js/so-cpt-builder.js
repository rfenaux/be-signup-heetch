/* globals jQuery, soCptBuilderAdminOptions */

jQuery( function ( $ ) {
	$(document).on( 'sowsetupform', '.siteorigin-widget-form[data-class="SiteOrigin_Premium_Form"]', function() {

		var $form = $(this);

		if ( typeof $form.data('initialised') === 'undefined' ) {
			if ( parseInt( soCptBuilderAdminOptions.existing_post_count ) > 0 ) {
				var $editableCheckbox = $form.find('[name="so_post_type_settings[editable_layout]"]');
				var isEditable = $editableCheckbox.is( ':checked' );
				if ( isEditable ) {
					$editableCheckbox.on( 'change', function () {
						if ( ! $editableCheckbox.prop( 'checked' ) && !confirm( soCptBuilderAdminOptions.loc.confirm_disable_editable ) ) {
							$editableCheckbox.prop( 'checked', true );
						}
					} );
				} else {
					$( 'form#post' ).on( 'submit', function ( event ) {
						if ( !confirm( soCptBuilderAdminOptions.loc.confirm_edit_post_type ) ) {
							event.preventDefault();
						}
					} );
				}
				
				var $slugInput = $form.find('[name="so_post_type_settings[slug]"]');
				var oldValue = $slugInput.val();
				$slugInput.css('color', '#aaaaaa');
				$slugInput.on( 'change',
					function () {
						if ( !confirm( soCptBuilderAdminOptions.loc.confirm_edit_slug ) ) {
							$slugInput.val( oldValue );
						}
					}
				);
			}

			$form.data('initialised', true);
		}
	});
} );
