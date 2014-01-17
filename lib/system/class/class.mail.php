<?php
/**
 * Mailer : send mail via php function mail()
 * @see lang::mail
 * @author Tilotiti
 */
class mail {
    public
    	/**
		 * Addressee(s)
		 * @var Array
		 */
        $to       = false,
        /**
		 * Sender(s)
		 * @var Array
		 */
        $from	  = false,
        /**
		 * BCC addressee(s)
		 * @var Array
		 */
        $bcc      = false,
        /**
		 * Mail headers
		 * @var Array
		 */
        $header   = false,
        /**
		 * Reply-to mail field
		 * @var Array
		 */
        $replyto  = false,
        /**
		 * Mail subject
		 * @var String
		 */
        $subject  = false,
        /**
		 * Confirm mail to ?
		 * @var Array
		 */
        $confirm  = false,
        /**
		 * Mail body (html)
		 * @var String
		 */
        $message  = false,
        /**
		 * CC addressee(s)
		 * @var Array
		 */
        $cc       = false,
        /**
		 * Bounds parts of the mail
		 * @var String
		 */
        $limite   = false;

	/**
	 * Adds a regular addressee
	 * @param String $mail e-mail address
	 * @return mail $this for chaining
	 */
    public function to($mail) {
        if(is_array($mail)):
    		foreach($mail as $email):
    			$this->to[] = $email;
    		endforeach;
    	else:
        	$this->to[] = $mail;
        endif;
        return $this;
    }

	/**
	 * Adds a sender
	 * @param String $mail sender e-mail address
	 * @return mail $this for chaining
	 */
    public function from($mail) {
        $this->Header("From", $mail);
		$this->from[] = $mail;
        return $this;
    }

	/**
	 * Adds a BCC addressee
	 * @param String $mail e-mail address
	 * @param String $name[optional] addressee name
	 * @return mail $this for chaining
	 */
    public function bcc($mail, $name = '') {
        $this->bcc[$mail] = $name;
        return $this;
    }
	
	/**
	 * Adds a confirm address
	 * @param String $mail e-mail address
	 * @param String $name[optional] confirm name
	 * @return mail $this for chaining
	 */
    public function confirm($mail, $name = '') {
        $this->confirm[$mail] = $name;
        return $this;
    }

	/**
	 * Adds a reply to address (if different from sender)
	 * @param String $mail e-mail address
	 * @param String $name[optional] reply-to name
	 * @return mail $this for chaining
	 */
    public function replyTo($mail, $name = '') {
        $this->replyto[$mail] = $name;
        return $this;
    }

	/**
	 * Set the mail subject
	 * @param String $str Subject (or lang code)
	 * @param Boolean $lang[optional] set to false if you don't want to use lang - default: true
	 * @return mail $this for chaining
	 */
    public function subject($str, $lang = true, $encode = false) {
    	// FIXME Why can't we put arguments in subject title ?
        if($lang):
            $this->subject = lang::title($str, $lang);
        else:
            $this->subject = $str;
        endif;
        return $this;
    }

	/**
	 * Set the mail priority
	 * Accepted values are : normal, non-urgent, urgent
	 * @param String $text mail priority
	 * @return mail $this for chaining
	 */
    public function priority($text) {
        $text = strtolower($text);
        if($text == "normal" || $text == "non-urgent" || $text == "urgent"):
            $this->Header("Priority", $text);
        endif;
        return $this;
    }

	/**
	 * Set the mail body
	 * If template used, arguments are accessed through $mail smarty variable.
	 * @param String $str mail body OR mail template name (@see lang::mail)
	 * @param Array $arg[optional] arguments for the template - default: none
	 * @param Boolean $lang[optional] use templating - default: false
	 * @return mail $this for chaining
	 */
    public function message($str, $arg = false, $lang = true) {
        if($lang):
            $this->message = lang::mail($str, $arg);
        else:
            $this->message = $str;
        endif;
        return $this;
    }

	/**
	 * Adds a mail header
	 * @param String $header header name
	 * @param String $content header value
	 * @return mail $this for chaining
	 */
    public function header($header, $content) {
        $this->header[$header] = $content;
        return $this;
    }
	
	/**
	 * Converts e-mail array into mail list of e-mails
	 * @param Array $array an array of e-mails
	 * @return Boolean|String list of e-mails (false if $array isn't an array)
	 */
    public function convertmail($array) {
        if(!$array || !is_array($array)):
            return false;
        else:
            $return    = '';
            $separator = '';
            $i = 0;
            foreach($array as $key => $value):
                if($i > 0):
                    $separator = ', ';
                endif;
                if(!empty($value)):
                    $return .=  $separator.$key.' <'.$value.'>';
                else:
                    $return .=  $separator.$key;
                endif;
                $i++;
            endforeach;
            return $return;
        endif;
    }

	/**
	 * Converts mail object headers into mail headers
	 * @param Array $array
	 * @return Boolean|String correct mail headers (false if $array isn't an array)
	 */
    public function convertheader($array) {
        if(!$array):
            return false;
        else:
            $return = '';
            foreach($array as $key => $value):
                if(!empty($value)):
                    $return .=  $key.': '.$value."\n";
                endif;
            endforeach;
            return $return;
        endif;
    }

	/**
	 * Send the mail
	 * @return number of mails sent
	 */
    public function send() {
        $this->header("Bcc", $this->convertmail($this->bcc));
        $this->header("Cc", $this->convertmail($this->cc));
        $this->header("Reply-To", $this->convertmail($this->replyto));
        $this->header("X-Confirm-Reading-To", $this->convertmail($this->confirm));
        $this->header("MIME-Version", "1.0");
        $this->header("Content-type", 'text/html; charset="'.CHARSET.'"');

        $headers = $this->convertheader($this->header);

        $this->subject = mb_encode_mimeheader($this->subject, CHARSET, "Q");

        $return = 0;

        foreach($this->to as $to):
            $return = $return + mail($to, $this->subject, $this->message, $headers);
        endforeach;
		
        return $return;
    }
	
}