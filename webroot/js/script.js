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

$(window).load(function () {
	/*
	 * Automatic image croping - TODO test
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

var Message = function(){};

/**
 * push(type[string], content[string], cfg[object])
 *
 * @var type : type de message (success, alert, ...)
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
Message.prototype.push = function(type, content, cfg){
	
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

 	if(typeof cfg == 'object'){
 		delay 	= typeof cfg.delay  == 'undefined' ? delay 	: cfg.delay;
 		fade 	= typeof cfg.fade   == 'undefined' ? fade 	: cfg.fade;
 		target 	= typeof cfg.target == 'undefined' ? target : cfg.target;
 	}


 	var newItem = $(alert).insertAfter(target);

 	if(newItem){
 		$(newItem).delay(delay).fadeOut(fade);
 	}
}

/**
 * Message.success(content[string], cfg[object])
 * Simple alias de la fonction Message.push avec la variable type égale à 'success'
 * 
 * @var content : le message en lui-même
 * @var cfg : tableau de configuration (optionel) permetant de gérer :
 * 	- la cible (pour l'integration du message)
 * 	- le delai avant la disparition du message
 * 	- la durée de l'effet de fondue 
 */
Message.prototype.success = function(content, cfg){
	this.push('success', content, cfg);
}

/**
 * Message.success(content[string], cfg[object])
 * Simple alias de la fonction Message.push avec la variable type égale à 'error'
 */
Message.prototype.error = function(content, cfg){
	this.push('error', content, cfg);
}

message = new Message();
