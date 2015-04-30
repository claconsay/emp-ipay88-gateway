//add ipay88 redirection
$(document).bind('em_booking_gateway_add_ipay88', function(event, response){ 
	// called by EM if return JSON contains gateway key, notifications messages are shown by now.
	if(response.result){
		var ppForm = $('<form action="'+response.ipay88_url+'" method="post" id="em-ipay88-redirect-form"></form>');
		$.each( response.ipay88_vars, function(index,value){
			ppForm.append('<input type="hidden" name="'+index+'" value="'+value+'" />');
		});
		ppForm.append('<input id="em-ipay88-submit" type="submit" style="display:none" />');
		ppForm.appendTo('body').trigger('submit');
	}
});