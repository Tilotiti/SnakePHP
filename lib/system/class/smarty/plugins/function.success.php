<?
function smarty_function_success($params, $template) {
	if(isset($params['code'])):
		return lang::success($params['code']);
	endif;
}

?>