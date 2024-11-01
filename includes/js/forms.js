jQuery(document).ready(function($) {
	$('.wb-form-tab').removeClass('current');
	$('.wb-form-tab').first().addClass('current');
	
	$('.wb-form-panel').hide();
	$('.wb-form-panel').first().show();
	
	$('.wb-form-tab').each(function(index, object) {
		$(object).click(function(event) {
			$('.wb-form-tab').removeClass('current');
			$('.wb-form-panel').hide();
			
			$(this).addClass('current');
			$(this.href.match(/#.*$/)[0]).show();
			
			return false;
		});
	});
});