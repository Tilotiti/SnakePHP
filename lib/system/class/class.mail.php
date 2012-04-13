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
        $cc       = false;

    public function __construct() {
        $this->Header("Mime-Version", "1.0");
        $this->Header("Content-type", "text/html; charset=".CHARSET);
    }

    public function To($mail) {
        $this->to[] = $mail;
        return $this;
    }

    public function From($mail) {
        $this->Header("From", $mail);
        return $this;
    }

    public function Bcc($mail, $name = '') {
        $this->bcc[$mail] = $name;
        return $this;
    }

    public function Confirm($mail, $name = '') {
        $this->confirm[$mail] = $name;
        return $this;
    }

    public function ReplyTo($mail, $name = '') {
        $this->replyto[$mail] = $name;
        return $this;
    }

    public function Subject($str, $lang = true) {
        if($lang){
            $this->subject = lang::title($str);
        }
        else{
            $this->subject = $str;
        }
        return $this;
    }

    public function Priority($text) {
        $text = strtolower($text);
        if($text == "normal" || $text == "non-urgent" || $text == "urgent"):
            $this->Header("Priority", $text);
        endif;
        return $this;
    }

    public function Message($str, $arg = false, $lang = true) {
        if($lang){
            $this->message = lang::mail($str, $arg);
        }
        else{
            $this->message = $str;
        }
        return $this;
    }

    public function Header($header, $content) {
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


    public function Send() {
        $this->Header("Bcc", $this->convertmail($this->bcc));
        $this->Header("Cc", $this->convertmail($this->cc));
        $this->Header("Reply-To", $this->convertmail($this->replyto));
        $this->Header("X-Confirm-Reading-To", $this->convertmail($this->confirm));

        $headers = $this->convertheader($this->header);

        foreach($this->to as $to):
            mail($to, $this->subject, $this->message, $headers);
        endforeach;
    }
}

?>
