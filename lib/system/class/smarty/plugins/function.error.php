<?
function smarty_function_error($params, $template) {
	if(isset($params['code'])):
		if(isset($params['var'])):
			return lang::error($params['code'], $params['var']);
		else:
			return lang::error($params['code']);
		endif;
	endif;
}

?>