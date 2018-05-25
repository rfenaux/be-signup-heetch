<script type="text/javascript">
jQuery(document).ready(function($){
if( $('.frm_twilio_notification').is(':checked')) $(this).closest('.twilio_notification').find('.hide_twilio').show();
$('.frm_twilio_notification').change(function(){
    if( $(this).is(':checked')) $(this).closest('.twilio_notification').find('.hide_twilio').fadeIn('slow'); 
    else $(this).closest('.twilio_notification').find('.hide_twilio').fadeOut('slow');
});
});
</script>