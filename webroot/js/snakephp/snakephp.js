$(function() {
	var navBar = ["Error", "SQL", "Dump", "Global", "Timer"];
	
	$.each(navBar, function(i, e) {
		$('#debug'+e).bind('click', function() {
			
			// La case est déjà sélectionnée, on ferme tout
			if($(this).hasClass('active')) {
				$(this).removeClass('active');
				$("#debugContent").hide();
				
			// La case n'est pas sélectionnée, on ferme tout puis on ouvre la bonne
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