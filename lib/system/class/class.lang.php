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
        
        // Automatic creation of language folder
        if(!is_dir(LANG.'/'.$pays)):
        	mkdir(LANG.'/'.$pays);
        	mkdir(LANG.'/'.$pays.'/mail');
        endif;
        
        foreach(self::$type as $t):
        	if(!file_exists(LANG.'/'.$pays.'/lang.'.$t.'.xml')):
        		// Automatic creation of language files
        		$template = new smarty();
		        $template->template_dir = SYSTEM.'/template/';
		        $template->compile_dir  = CACHE;
		        $template->assign('lang', $pays);
		        $content = $template->fetch('lang.tpl');
		        $file = fopen(LANG.'/'.$pays.'/lang.'.$t.'.xml', "w+");
			    fputs($file, $content);
			    fclose($file);
        	endif;
        	
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
    
    static function mail($file, $arg = false) {
        $template = new smarty();
        $template->template_dir = LANG.'/'.self::$pays.'/mail/';
        $template->compile_dir  = CACHE.'/mail/'.self::$pays.'/';
        $template->assign('mail', $arg);
		
		$file = ROOT . '/lang/' .self::$pays.'/mail/'.$file.'.tpl';
		if (!file_exists($file)):
			$file = LANG.'/'.self::$pays.'/mail/'.$file.'.tpl';
		endif;
        if(file_exists($file)):
            return $template->fetch($file);
        else:
            return false;
        endif;
    }
    
    static function find($type, $code, $arg = false) {
        
        if(isset(self::$lang[$type][$code])):
            $return = self::$lang[$type][$code];
            if(is_array($arg)):
                foreach($arg as $key => $value):
                    if(!is_array($value)):
                        $return = str_replace("[".$key."]", $value, $return);
                    endif;
                endforeach;
            elseif(is_string($arg) || is_int($arg)):
                $return = str_replace("[]", $arg, $return);
            endif;
            return (string) $return;
        else:
            $xml = simplexml_load_file(LANG.'/'.self::$pays.'/lang.'.$type.'.xml', 'SimpleXMLElement', LIBXML_NOCDATA);
            $lang = $xml->addChild('lang', '['.$code.']');
            $lang->addAttribute('id', $code);
            $lang->addAttribute('get', get());
            
            $var = array();
            if($arg):
            	if(is_array($arg)):
            		foreach($arg as $key => $value):
	                    if(!is_array($value)):
	                        $var[] = "[".$key."]";
	                    endif;
                endforeach;
            	else:
            		$var = array('[]');
            	endif;
            endif;
            $lang->addAttribute('var', implode('|', $var));
            
            $dom = new DOMDocument('1.0');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($xml->asXML());
            
            $dom->save(LANG.'/'.self::$pays.'/lang.'.$type.'.xml');
            
            self::$lang[$type][$code] = '['.$code.']';
            return '['.$code.']';
        endif;
    }
}