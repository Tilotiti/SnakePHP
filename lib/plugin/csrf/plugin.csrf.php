<?php
/*
 * Plugin : CSRF
 * Description : Protects website from "Cross-site request forgery" attacks
 * Autor : Thibault HENRY
 * Site : http://www.tiloweb.com
*/

class csrf {
	/**
	 * Generate
	 * Generating a unique CSRF token
	 * @param string $name Form name (optional)
	 */
	static function generate($name = 'default') {
		if(!isset($_SESSION['csrf'])):
			$_SESSION['csrf'] = array();
		endif;
		
		$_SESSION['csrf'][$name] = sha1($name.time().rand(1,999).$_SERVER['REMOTE_ADDR']);
	}
	
	/**
	 * Display
	 * Display the token input in form
	 * @param string $name Form name (optional)
	 */
	static function display($name = 'default') {
		if(!isset($_SESSION['csrf'][$name])):	
			return false;
		endif;
		
		return '<input type="hidden" value="'.$_SESSION['csrf'][$name].'" name="CSRFtoken_'.$name.'">';
	}
	
	/**
	 * Check
	 * Check the token send by post
	 * @param string $name Form name (optional)
	 * @return bool Correct token or not
	 */
	static function check($name = 'default') {
		if(!isset($_SESSION['csrf'][$name])):	
			return false;
		endif;
		
		if(!isset($_POST['CSRFtoken_'.$name]) || $_POST['CSRFtoken_'.$name] != $_SESSION['csrf'][$name]):	
			return false;
		endif;
		
		return true;
	}
}
