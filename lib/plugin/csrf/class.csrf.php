<?php
/*
 *	CSRF-token generator and checker
*/
class CSRF {
	public
		$name		= sha1('CSRF-token');

	/*
	 * Method       : __construct
	 * Description  : CSRF class constructor
	 * Parameters   : 
	 * 		[$name] -(string) : CSRF token-name, default: empty
	 * Return 		: (void)
	*/
    public function __construct($name = '') {
        if (!empty($name)):
        	$this->name = $name;
	}

	/*
	 * Method       : generate
	 * Description  : Generates a CSRF token and stores it in the session
	 * Parameters   : (void)
	 * Return 		: (void)
	*/
	public function generate() {
		$_SESSION[$this->name] = sha1(uniqid(rand()));
	}

	/*
	 * Method       : check
	 * Description  : Checks the passed value against the generated token
	 * Parameters   : 
	 * 		[$value] -(string) : token value from form to check
	 * Return 		:
	 *		(bool)	- true: the token is ok
	 * 				- false: the token doesn't exist
	*/
	public function check($value) {
		// The token is in the session
		if (isset($_SESSION[$this->name]) && !empty($_SESSION[$this->name])):
			// The passed token equals the generated one
			return ($value === $_SESSION[$this->name]) ? true : false;
		
		// The token isn't in the session
		else:
			return false;
		endif;
	}

	/*
	 * Method       : value
	 * Description  : Returns the CSRF token value
	 * Parameters   : (void)
	 * Return 		: (string): CSRF token
	*/
	public function value() {
		return $_SESSION[$this->name];
	}
}