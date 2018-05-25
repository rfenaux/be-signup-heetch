jQuery(document).ready(function($){
	var $formActions = jQuery(document.getElementById('frm_notification_settings'));
	$formActions.on('click', '.frmapi_test_connection', frmapi_test_connection);
	$formActions.on('click', '.frmapi_insert_default_json', frmapi_insert_json);
	$formActions.on('click', '.frmapi_add_data_row', frmapi_add_data_row );
	$formActions.on('change', '.frmapi_data_format', frmapi_toggle_options );
});

function frmapi_test_connection(){
	var settings = jQuery(this).closest('.frm_single_api_settings');
	var key = settings.data('actionkey');
	var baseName = 'frm_api_action['+key+'][post_content]';

	var url = jQuery('input[name="'+baseName+'[url]"]').val();
	var key = jQuery('input[name="'+baseName+'[api_key]"]').val();
	var testResponse = settings.find('.frmapi_test_resp');

	if (url == '') {
		settings.find('.frmapi_test_connection').html('Please enter a URL');
		return;
	} else if ( url.indexOf('[') != '-1' ) {
		testResponse.html('Sorry, Dynamic URLs cannot be tested');
		return;
	}

	testResponse.html('').addClass('spinner').show();

	jQuery.ajax({
		type:'POST',url:ajaxurl,
		data:{action:'frmapi_test_connection',url:url,key:key},
		success:function(html){
			testResponse.removeClass('spinner').html(html);
		}
	});
}

function frmapi_insert_json(){
	var form_id = jQuery('input[name="id"]').val();
	var settings = jQuery(this).closest('.frm_single_api_settings');
	var key = settings.data('actionkey');
	var baseName = 'frm_api_action['+key+'][post_content]';

	if (form_id == '') {
		jQuery('textarea[name="'+baseName+'[data_format]"]').val('');
		return;
	}

	jQuery.ajax({
		type:'POST',url:ajaxurl,
		data:'action=frmapi_insert_json&form_id='+form_id,
		success:function(html){
			jQuery('textarea[name="'+baseName+'[data_format]"]').val(html);
		}
	});
}

function frmapi_add_data_row(){
	var table = jQuery(this).closest('.frmapi_data_rows');
	var rowNum = table.find('.frm_postmeta_row:last').attr('id').replace('frm_api_data_', '');
	var key = jQuery(this).closest('.frm_form_action_settings').data('actionkey');

	jQuery.ajax({
		type:'POST',url:ajaxurl,
		data:{ action:'frmapi_add_data_row', 'key':key, 'row':rowNum},
		success:function(html){
			table.append(html);
		}
	});
}

function frmapi_toggle_options(){
	var val = this.value;
	var settings = jQuery(this).closest('.frm_single_api_settings');
	if ( val == 'raw' ) {
		settings.find('.frm_data_raw').show();
		settings.find('.frm_data_json').hide();
	} else {
		settings.find('.frm_data_raw').hide();
		settings.find('.frm_data_json').show();
	}
}
