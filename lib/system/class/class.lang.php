<?php
class lang {
    static
        $lang = false,
        $pays = false,
        $type = false;
    
    public function __construct($pays) {
        self::$pays = $pays;
        
        self::$type[] = "text";
        self::$type[] = "success";
        self::$type[] = "error";
        self::$type[] = "title";
        self::$type[] = "warning";
        self::$type[] = "flux";
        
        foreach(self::$type as $t):
            /*if(file_exists(LANG.'/'.$pays.'/lang.'.$t.'.php')):
                $lang = array();
                require_once LANG.'/'.$pays.'/lang.'.$t.'.php';
                self::$lang[$t] = $lang;
            endif;*/
            if(file_exists(LANG.'/'.$pays.'/lang.'.$t.'.xml')):
                $xml = simplexml_load_file(LANG.'/'.$pays.'/lang.'.$t.'.xml', 'SimpleXMLElement', LIBXML_NOCDATA);
                foreach($xml->lang as $line):
                    $id = (string) $line['id'];
                    self::$lang[$t][$id] = $line;
                endforeach;
            endif;
        endforeach;
        
    }
    
    static function error($code, $arg = false) {
        return self::find('error', $code, $arg);
    }
    
    static function success($code, $arg = false) {
        return self::find('success', $code, $arg);
    }
    
    static function text($code, $arg = false) {
        return self::find('text', $code, $arg);
    }
    
    static function title($code, $arg = false) {
        return self::find('title', $code, $arg);
    }
    
    static function warning($code, $arg = false) {
        return self::find('warning', $code, $arg);
    }
    
    static function mail($file, $arg = false) {
        $template = new smarty();
        $template->template_dir = LANG.'/'.self::$pays.'/mail/';
        $template->compile_dir  = CACHE.'/mail/'.self::$pays.'/';
        $template->assign('mail', $arg);
        if(file_exists(LANG.'/'.self::$pays.'/mail/'.$file.'.tpl')):
            return $template->fetch($file.'.tpl');
        else:
            return false;
        endif;
    }
    
    static function find($type, $code, $arg = false) {
        
        if(isset(self::$lang[$type][$code])):
            $return = self::$lang[$type][$code];
            if(is_array($arg)):
                foreach($arg as $key => $value):
                    $return = str_replace("[".$key."]", $value, $return);
                endforeach;
            elseif(is_string($arg) || is_int($arg)):
                $return = str_replace("[]", $arg, $return);
            endif;
            return $return;
        else:
            /*$file = file(LANG.'/'.self::$pays.'/lang.'.$type.'.php');
            $file_nb = count($file);
            $file[$file_nb-1] = '$lang["'.$code.'"] = "['.$code.']"; // From : '.get()."\n";
            $file[$file_nb]   = "?>";
            $string = implode("", $file);
            $fp = fopen(LANG.'/'.self::$pays.'/lang.'.$type.'.php', "w");
            fputs($fp, $string);
            fclose($fp); */
            $xml = simplexml_load_file(LANG.'/'.self::$pays.'/lang.'.$type.'.xml', 'SimpleXMLElement', LIBXML_NOCDATA);
            $lang = $xml->addChild('lang', '['.$code.']');
            $lang->addAttribute('id', $code);
            $lang->addAttribute('context', "");
            $lang->addAttribute('get', get());
            
            $xml->asXML(LANG.'/'.self::$pays.'/lang.'.$type.'.xml');
            
            self::$lang[$type][$code] = '['.$code.']';
            return '['.$code.']';
        endif;
    }
}
?>
