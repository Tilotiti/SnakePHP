<?php
function smarty_function_icon($params) {
	$type = $params['src'];
	return '<i class="glyphicon glyphicon-'.$type.'"></i>';
}
