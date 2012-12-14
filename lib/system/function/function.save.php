<?php
function save($form, $key = true, $value = false) {
  if(is_array($key)):
		$_SESSION[$form] = $key;
	elseif(is_string($key)):
		if(!$value):
			if(isset($_SESSION[$form][$key])):
				return $_SESSION[$form][$key];
			else:
				return false;
			endif;
		else:
			$_SESSION[$form][$key] = $value;
		endif;
	elseif(!$key):
		unset($_SESSION[$form]);
	else:
		return $_SESSION[$form];
	endif;
}
