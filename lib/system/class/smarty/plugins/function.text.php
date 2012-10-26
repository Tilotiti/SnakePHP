<?
function smarty_function_text($params, $template) {
	if(isset($params['code'])):
		return lang::text($params['code']);
	endif;
}

?>