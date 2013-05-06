<?php
function fatalError($html = false) {
	$template = file_get_contents(SYSTEM.'/template/error.tpl');
	$content = '';
	
	if(!$html):
		 $error = error_get_last();
		 if($error):
			 $content .= '<p>A <b>Fatal Error</b> has occured.</p>';
			 $content .= '<dl>';
			 $content .= '<dt>'.$error['message'].'</dt>';
			 $content .= '<dd>'.$error['file'].' ('.$error['line'].')</dd>';
			 $content .= '</dl>';
	     else:
	     	return false;
		 endif;
	else:
		$content = $html;
	endif;
	
	exit(str_replace('<!--[content]-->', $content, $template));
	
	return false; 
	
}