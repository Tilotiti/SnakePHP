<?php

class mail {
    public
        $to       = false,
        $bcc      = false,
        $header   = false,
        $replyto  = false,
        $subject  = false,
        $confirm  = false,
        $message  = false,
        $cc       = false,
        $limite   = false;

    public function __construct() {
        $this->Header("Mime-Version", "1.0");
        
        // used to mark out html and text parts of the body
        $this->limite = "_----------=_parties_".md5(uniqid (rand()));
        // now charset is defined in message headers (@see convertMessage)
        $this->Header("Content-type", 'multipart/alternative; boundary="'.$this->limite.'"');
    }

    public function to($mail) {
        $this->to[] = $mail;
        return $this;
    }

    public function from($mail) {
        $this->Header("From", $mail);
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

    public function subject($str, $lang = true) {
        if($lang){
            $this->subject = lang::title($str);
        }
        else{
            $this->subject = $str;
        }
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
        if($lang){
            $this->message = lang::mail($str, $arg);
        }
        else{
            $this->message = $str;
        }
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
                    $return .=  $key.': '.$value."\r\n";
                endif;
            endforeach;
            return $return;
        endif;
    }
    
    /**
     * Converts the body message into multipart/alternative message.
     * Replace links with footnotes.
     * @return the multipart/alternative mail body
     */
    public function convertMessage() {
        
        // replace all <br(?)> with nl and enclose each <p></p> with nl
        $texte = preg_replace('#<br[^>]*>#', "\r\n", $this->message);
        $texte = preg_replace('#<p[^>]*>#', "\r\n", $texte);
        $texte = preg_replace('#</p[^>]*>#', "\r\n", $texte);
        // get all href="???"
        $hrefs = array();
        preg_match_all('#<a [^>]*href="?([^">]+)"?>#', $texte, $hrefs);
        $hrefs = $hrefs[1];
        // replace all <axxx> and </axxx>
        $texte = preg_replace('#<a[^>]*>#', '', $texte);

        // add a mark for each end of link
        $texte = preg_replace('#</a[[:space:]]*>#', '[href] ', $texte);

        $texte = strip_tags($texte);

        $i = 1;
        while ( ($pos=strpos($texte, '[href]'))!==false ) { // give a note number
            $texte = substr($texte, 0, $pos) . '['. $i++ .']' . substr($texte, $pos+6);
        }
        foreach($hrefs as $i => $link) { // adding footnotes
            $texte .= '[' . (1+$i++) . '] ' . $link."\r\n";
        }
        // --> mail body in plain text is now ready <--
        
        $message = "";
        
        // adding plain text message header
        $message .= "--".$this->limite."\n";
        $message .= "Content-Type: text/plain\n";
        $message .= "charset=\"" . CHARSET . "\"\n";
        $message .= "Content-Transfer-Encoding: 8bit\n\n";
        $message .= $texte;
        
        // html message header
        $message .= "\n\n--".$this->limite."\n";
        $message .= "Content-Type: text/html; ";
        $message .= "charset=\"" . CHARSET . "\"; ";
        $message .= "Content-Transfer-Encoding: 8bit;\n\n";
        $message .= $this->message;
        
        return $message;
    }


    public function send() {
        $this->header("Bcc", $this->convertmail($this->bcc));
        $this->header("Cc", $this->convertmail($this->cc));
        $this->header("Reply-To", $this->convertmail($this->replyto));
        $this->header("X-Confirm-Reading-To", $this->convertmail($this->confirm));

        $headers = $this->convertheader($this->header);
        
        $message = $this->convertMessage();
        
        foreach($this->to as $to):
            mail($to, $this->subject, $message, $headers);
        endforeach;
    }
}
?>