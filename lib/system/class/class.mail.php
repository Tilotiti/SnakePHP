<?php

class mail {
    public
        $to       = false,
        $from	  = false,
        $bcc      = false,
        $header   = false,
        $replyto  = false,
        $subject  = false,
        $confirm  = false,
        $message  = false,
        $cc       = false,
        $limite   = false;

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

    public function from($mail) {
        $this->Header("From", $mail);
		$this->from[] = $mail;
        return $this;
    }

    public function bcc($mail, $name = '') {
        $this->bcc[$mail] = $name;
        return $this;
    }

    public function confirm($mail, $name = '') {
        $this->confirm[$mail] = $name;
        return $this;
    }

    public function replyTo($mail, $name = '') {
        $this->replyto[$mail] = $name;
        return $this;
    }

    public function subject($str, $lang = true, $encode = false) {
        if($lang):
            $this->subject = lang::title($str, $lang);
        else:
            $this->subject = $str;
        endif;
        return $this;
    }

    public function priority($text) {
        $text = strtolower($text);
        if($text == "normal" || $text == "non-urgent" || $text == "urgent"):
            $this->Header("Priority", $text);
        endif;
        return $this;
    }

    public function message($str, $arg = false, $lang = true) {
        if($lang):
            $this->message = lang::mail($str, $arg);
        else:
            $this->message = $str;
        endif;
        return $this;
    }

    public function header($header, $content) {
        $this->header[$header] = $content;
        return $this;
    }

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

    public function send() {
    	if (!is_file('/usr/sbin/sendmail')) {
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
		else {
			return $this->swiftSend();
		}
    }
	
	private function swiftSend() {
		$message = Swift_Message::newInstance();
		
		$message->setSubject($this->subject)
	  			->setFrom($this->from)
	  			->setTo($this->to)
	  			->setBody($this->message,'text/html',CHARSET);
	  			
		if ($this->bcc && count((array) $this->bcc)>0) {
			$message->setBcc((array) $this->bcc);
		}
		if ($this->cc && count((array) $this->cc)>0) {
			$message->setCc((array) $this->cc);
		}
		if ($this->replyTo && count((array) $this->replyTo)>0) {
			$message->setReplyTo((array) $this->replyTo);
		}
		if ($this->confirm && count((array) $this->confirm)>0) {
			$message->setReadReceiptTo($this->confirm);
		}
		// use MTA
		$transport = Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -t');
		$mailer = Swift_Mailer::newInstance($transport);
		
		return $mailer->send($message);
	}
	
}
?>
