<?php
/*
 *	Bruteforce counter and alerter
*/
class Bruteforce {

    /*
	 * Method       : __construct
	 * Description  : Bruteforce class constructor
	 * Parameters   : (void)
	 * Return 		: (void)
	 */
    public function __construct() {
        // Let the serveer sleep for 0.15s on this task, decreases possible attacks/minute
		usleep(150000);
	}

	/*
	 * Method       : count
	 * Description  : Counts, increases and returns bruteforce attempts
	 * Parameters   : 
	 * 		[$count] - (bool) [default=true]. If set to false, the counter won't 
	 *						increase and just return the number
	 * Return 		: (int) : number bruteforce attempts
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

	/*
	 * Method       : alert
	 * Description  : Alerts in case of too many bruteforce attempts
	 * Parameters   : (void)
	 * Return 		:
	 * 		(bool) 	- true: there are more attempts than allowed
	 *				- false: there are less attempts than allowed (what we expect)
	 */
	public function alert() {
		return ($this->count(false) >= BRUTEFORCEMAX);
	}
}