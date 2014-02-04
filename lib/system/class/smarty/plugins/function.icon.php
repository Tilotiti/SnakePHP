<?php
function smarty_function_icon($params) {
	$type = $params['type'];
	return '<i class="glyphicon glyphicon-'.$type.'"></i>';
}