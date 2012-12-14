<?php
function save($form, $key = true, $value = false) {
  if(is_array($key)):
		$_SESSION['save'][$form] = $key;
	elseif(is_string($key)):
		if(!$value):
			if(isset($_SESSION['save'][$form][$key])):
				return $_SESSION['save'][$form][$key];
			else:
				return false;
			endif;
		else:
			$_SESSION['save'][$form][$key] = $value;
		endif;
	elseif(!$key):
		unset($_SESSION['save'][$form]);
	else:
		return $_SESSION['save'][$form];
	endif;
}
