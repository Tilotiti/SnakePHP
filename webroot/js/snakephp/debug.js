$(function() {
	var navBar = ["Error", "SQL", "Dump", "Global", "Timer"];
	
	$.each(navBar, function(i, e) {
		$('#debug'+e).bind('click', function() {
			
			if($(this).hasClass('active')) {
				$(this).removeClass('active');
				$("#debugContent").hide();
				
			} else {
				$("#debug li").removeClass('active');
				$(".debugContent").hide();
				$("#debug"+e+"Content").show();
				$("#debugContent").show();
				$(this).addClass('active');
			}
			
		 	return false;
		});
	});
});