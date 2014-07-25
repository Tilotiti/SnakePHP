<?php
class session {
	/**
	 * Login
	 * Load user information in session
	 * @param int $id User ID
	 * @return bool User found
	 */
	public static function login($id) {
		$user = new user((int)$id);
		if($user->ok()):
			$_SESSION['user']  = $user->get();
			$_SESSION['user']['login'] = time();
			self::save();
			return true;
		else:
			return false;
		endif;
	}
	
	/*
	 * Ok
	 * Return if the session is connected
	 * @return bool session connected
	 */
	public static function ok() {
		return !empty($_SESSION['user']['id']);
	}
	
	/**
	 * Get
	 * Find a session field
	 * @param string $key Field's name
	 * @return mixed Field's value
	 */
	public static function get($key) {
		if(!empty($_SESSION['user'][$key])):			
			return $_SESSION['user'][$key];
		else:
			return false;
		endif;
	}
	
	/**
	 * Set
	 * Set a new value to a field
	 * @param string $key Field's name
	 * @param mixed $value Field's new value
	 */
	public static function set($key, $value) {
		$_SESSION['user'][$key] = $value;
	}
	
	/**
	 * Save
	 * Save the session in the database
	 */
	public static function save() {
		$user = new user($_SESSION['user']);
		$user->save();
	}
	
	/*
	 * Logout
	 * Disconnect the session
	 */
	public static function logout() {
		unset($_SESSION['user']);
	}
}
