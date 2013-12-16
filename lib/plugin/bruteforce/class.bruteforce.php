<?php
/**
 * Bruteforce counter and alerter
 * You should use this class inside the session::login() method 
 * @author Pierre de Vesian
 */
class Bruteforce {

    /**
	 * Bruteforce class constructor
	 */
    public function __construct() {
        // Let the serveer sleep for 0.15s on this task, decreases possible attacks/minute
		usleep(150000);
	}

	/**
	 * Counts, increases and returns bruteforce attempts 
	 * @param Boolean $count[optional] if set to false, the counter won't increase and just return the number - default: true
	 * @return Integer	number bruteforce attempts
	 */
	public function count($count = true) {
		// Increase the counter
		if ($count):
			if (isset($_SESSION['bfc']) && $_SESSION['bfc'] >= 0):
				$_SESSION['bfc']++;
			else:
				$_SESSION['bfc'] = 1;
			endif;
		endif;
		return intval($_SESSION['bfc']);
	}

	/**
	 * Checks if too many bruteforce attempts
	 * @return Boolean true if there are more attempts than allowed, false otherwise
	 */
	public function alert() {
		return ($this->count(false) >= BRUTEFORCEMAX);
	}
}