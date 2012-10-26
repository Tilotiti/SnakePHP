<?
function smarty_function_title($params, $template) {
	if(isset($params['code'])):
		return lang::title($params['code']);
	endif;
}

?>