<?
function smarty_function_text($params, $template) {
	if(isset($params['code'])):
		if(isset($params['var'])):
			return lang::text($params['code'], $params['var']);
		else:
			return lang::text($params['code']);
		endif;
	endif;
}

?>