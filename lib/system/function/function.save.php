<?php
/**
 * Save/retrieve an array (like data sent by form) to/from session
 * 
 * You can :
 * - set up your data : set a data ID as first argument and your array as second argument
 * - change the value of an entry : set data ID, key (string), new value
 * - retrieve all your data : set only data ID
 * - retrieve only one value : set data ID and key (string)
 * - erase all data : set data ID as first argument and second argument to false 
 * 
 * @param String $form identifier for your data
 * @param String|Boolean|Array[optional] $key value identifier OR data array OR false (erase whole data) - default: tells the function to return whole data
 * @param String[optional] $value new value - default: will return or erase data according to previous argument 
 * @return mixed depends on parameters
 */
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
