<?
function smarty_function_success($params, $template) {
	if(isset($params['code'])):
		if(isset($params['var'])):
			return lang::success($params['code'], $params['var']);
		else:
			return lang::success($params['code']);
		endif;
	endif;
}

?>