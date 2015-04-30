//add sentry redirection
$(document).bind('em_booking_gateway_add_sentry', function(event, response){ 
	// called by EM if return JSON contains gateway key, notifications messages are shown by now.
	if(response.result){
		var ppForm = $('<form action="'+response.sentry_url+'" method="post" id="em-sentry-redirect-form"></form>');
		$.each( response.sentry_vars, function(index,value){
			ppForm.append('<input type="hidden" name="'+index+'" value="'+value+'" />');
		});
		ppForm.append('<input id="em-sentry-submit" type="submit" style="display:none" />');
		ppForm.appendTo('body').trigger('submit');
	}
});