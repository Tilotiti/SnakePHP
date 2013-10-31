$(function() {
	/*
	 * Debug Bar
	 */
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
	
	/*
	 * Automatic image croping
	 */
	$('.cropbox').each(function(i, e) {
		
		var cropbox = $(e);
		var img     = $(e).find('img').first();
		img.css('opacity', '0');
		
		if (img.width()/img.height() > cropbox.width()/cropbox.height()) {
			img.css('height', '100%');img.css('width', 'auto');
			img.css("margin-left", (cropbox.width() - img.width()) / 2);
		}
		else {
			img.css('width', '100%');img.css('height', 'auto');
			img.css("margin-top", (cropbox.height() - img.height()) / 2);
		}
		img.css('opacity', '');
	});
});

/*
 * Icone simplifier
 */
function icone(str) {
	return '<i class="icon-'+str+'"></i>'
}

/*
 * Session auto-save
 */
 
setInterval(sessionSave, 600000);
function sessionSave() {
	$.get('/ajax/session.php');
}