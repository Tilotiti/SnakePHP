<?php
/**
 * CSRF-token generator and checker
 * Please @see SessionedCsrf
 * @author Pierre de Vesian
 */
class csrf {
	/**
	 * Token identifier
	 * @var String
	 */
	private $name = '';
	
	/**
	 * @param String $name[optional] default: empty string
	 */
	public function __construct($name = '') {
		if (!empty($name)):
			$this->name = $name;
		else:
			$this->name = sha1('CSRF-token');
		endif;
	}
	
	/**
	 * Returned a session token
	 * @author Fabrice Lazzarotto
	 * @param String|Boolean $name[optional] name of the token - if false just returns token - default: "token"
	 * @return SessionnedCsrf|void void if $name given, token if not
	 */
	public static final function generic($name='token') {
		$csrf = new SessionnedCsrf();
		if ($name!==false) {
			$csrf->initCsrfToken()->saveCurrentCsrfToken($name);
		}
		else {
			return $csrf;
		}
	}
	
	/**
	 * Checks if the client token matches the session-saved one
	 * @author Fabrice Lazzarotto
	 * @return Boolean
	 */
	public static final function checkCsrfToken() {
		return SessionnedCsrf::checkCsrfToken();
	}
	
	/**
	 * Generates a CSRF token and stores it in the session
	 */
	public function generate() {
		$_SESSION[$this->name] = sha1(uniqid(rand()));
	}
	
	/**
	* Compare the passed value with the generated token before deleting the token.
	* Returns  true: the token is ok / false: the token doesn't exist
	* @param String $value : token value from form to check
	* @return Boolean
	*/
	public function check($value) {
		// The token is in the session
		if (isset($_SESSION[$this->name]) && strlen(trim($_SESSION[$this->name]))>0):
		// The passed token equals the generated one
			if ($value === $_SESSION[$this->name]) {
				if (DEV) {
					debug('-------------------------------------------------', 'csrf');
					debug('LOG CSRF-check', 'csrf');
					debug('Requested from: ' . $_SERVER['REQUEST_URI'], 'csrf');
					debug('Received: ' . $value, 'csrf');
					debug("Attended: " . $_SESSION[$this->name], 'csrf');
					debug("-------------------------------------------------", 'csrf');
				}
				return true;
			}
			else {
				return false;
			}
		// The token isn't in the session
		else:
			return false;
		endif;
	}
	
	
	
	/**
	 * Returns the CSRF token value
	 * @return String CSRF token
	 */
	public function value() {
		return $_SESSION[$this->name];
	}

}

/**
 * Automatise the csrf behaviour
 * Recommended use :
 * ///// before displaying form
 * $csrf = csrf::generic();
 * ///// inside form
 * {$token}
 * ///// when receiving form data
 * if (!csrf::checkCsrfToken()) { // should be first thing to do
 * 		// abort
 * }
 * // regular process here
 * 
 * This example assumes that you don't have any <input name="token" /> in your form.
 * 
 * @author Fabrice Lazzarotto
 */
class SessionnedCsrf {
	/**
	 * Counter-CSRF attack token to use in forms
	 * @var CSRF
	 */
	private $token = false;
	
	/**
	 * Save the current CSRF-token value to the template (included in a hidden input) before returning it.
	 * To inject in a form : {${$name}}, default: {$token}
	 * @param String $name the name of the smarty variable which will contain the token - default: token
	 * @return String
	 */
	public function saveCurrentCsrfToken($htmlReturn=false, $name='token') {
		global $page;
		$val = $this->token->value();
		$html = '<input type="hidden" name="' . $name . '" value="' . $val . '" />';
		$page->template($name, $html);
		return $htmlReturn ? $html : $val;
	}
	/**
	 * Checks if the given value is the same as saved value for the token so then returns true, else returns false
	 * @param String $value the value to test - default $_POST['token']
	 * @return Boolean
	 */
	public static final function checkCsrfToken($value=false) {
		$_SESSION['token_generated'] = false;
		$value = $value===false ? (isset($_POST['token']) ? $_POST['token'] : "0") : $value;
		$token = new CSRF();
		return $token->check($value);
	}

	/**
	 * Initialises the CSRF token generator and generates a new token
	 * Do not forget to check the current token before using this method !
	 * Returns the page object for chaining reliability.
	 * 
	 * @return SessionnedCsrf
	 */
	public function initCsrfToken() {
		//We don't generate any token until the current has been checked
		if (DEV) {
			debug('-------------------------------------------------', 'csrf');
			debug('LOG CSRF-initializing', 'csrf');
			debug((!isset($_SESSION['token_generated']) || $_SESSION['token_generated']==false) ? "Doing a new generation" : "Keeping old token", 'csrf');
			debug("-------------------------------------------------", 'csrf');
		}
		$this->token = new CSRF();
		if (!isset($_SESSION['token_generated']) || !$_SESSION['token_generated']) {
			$this->token->generate();
			$_SESSION['token_generated'] = true;
		}
		return $this;
	}
}