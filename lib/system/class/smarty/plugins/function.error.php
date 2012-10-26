<?
function smarty_function_error($params, $template) {
	if(isset($params['code'])):
		return lang::error($params['code']);
	endif;
}

?>