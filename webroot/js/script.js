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
});

/**
 * pushMessage(type[string], content[string], cfg[object])
 * @var type : type de message (cussess, alert, ...)
 * @var content : le message en lui-même
 * @var cfg : tableau de configuration (optionel) permetant de gérer :
 * 	- la cible (pour l'integration du message)
 * 	- le delai avant la disparition du message
 * 	- la durée de l'effet de fondue
 * 
 * example d'utilisation simple :
 * 	pushMessage('success', 'Modification réalisée avec succès');
 * 
 * example d'utilisation avec configuration :
 * 	pushMessage('success', 'Modification réalisée avec succès', {
 * 		delay : 2000, // 2sec
 * 		fade : 750
 * 	});
 */
function pushMessage(type, content, cfg){
	
	if(typeof type == 'undefined'){
		return false;
	}
	if(typeof content == 'undefined' || content == ''){
		return false;
	}

	var alert 	= '<div class="alert '+type+'">'+content+'</div>';
 	var target 	= $('#module_content h2:eq(0)');
 	var delay 	= 10000;
 	var fade 	= 2500;

 	if(typeof cfg == 'array' || typeof cfg == 'object'){
 		delay 	= typeof cfg.delay 	== 'undefined' ? delay 	: cfg.delay;
 		fade 	= typeof cfg.fade 	== 'undefined' ? fade 	: cfg.fade;
 		target 	= typeof cfg.target == 'undefined' ? target : cfg.target;
 	}


 	var newItem = $(alert).insertAfter(target);

 	if(newItem){
 		$(newItem).delay(delay).fadeOut(fade);
 	}
}
