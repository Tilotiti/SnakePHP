<?php
function smarty_function_title($params, $template) {
	if(isset($params['code'])):
		if(isset($params['var'])):
			return lang::title($params['code'], $params['var']);
		else:
			return lang::title($params['code']);
		endif;
	endif;
}

?>
