<?php
/**
 * (Multi-)language manager.
 * This manager use XML files (/lang/{lang_code}/ directories), where language variables are injected.
 * A SnakePHP translator is available to edit these files.
 *  
 * @author Tilotiti
 * @see https://github.com/Tilotiti/SnakePHP-Translator
 */
class lang {
    static
		/**
		 * Labels and translations from XML files
		 * @var Array
		 */
        $lang = false,
        /**
		 * Language code - "en", "fr", "de", "es", etc.
		 * @var String
		 */
        $pays = false,
        /**
		 * Label types - "text", "title", "error", "success"
		 */
        $type = false;
    
	/**
	 * Initialize language manager and set up current page language
	 * @param String $pays language code - must be a subdirectory of /lang
	 */
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
    
	/**
	 * Returns the error message that fits given code for current language.
	 * 
	 * @static
	 * @param String $code label of the error
	 * @param Array $arg[optional] arguments for the text - @see lang::find
	 * @return String translation matching code and language
	 */
    static function error($code, $arg = false) {
        return self::find('error', $code, $arg);
    }
    
	/**
	 * Returns the success message that fits given code for current language.
	 * 
	 * @static
	 * @param String $code label of the message
	 * @param Array $arg[optional] arguments for the text - @see lang::find
	 * @return String translation matching code and language
	 */
    static function success($code, $arg = false) {
        return self::find('success', $code, $arg);
    }
    
	/**
	 * Returns the text that fits given code for current language.
	 * 
	 * @static
	 * @param String $code label of the text
	 * @param Array $arg[optional] arguments for the text - @see lang::find
	 * @return String translation matching code and language
	 */
    static function text($code, $arg = false) {
        return self::find('text', $code, $arg);
    }
    
	/**
	 * Returns the title that fits given code for current language.
	 * 
	 * @static
	 * @param String $code label of the title
	 * @param Array $arg[optional] arguments for the title - @see lang::find
	 * @return String translation matching code and language
	 */
    static function title($code, $arg = false) {
        return self::find('title', $code, $arg);
    }
    
	/**
	 * Language directories contains a "mail" subdir, with smarty templates in it. These are used by mail class,
	 * to provide mail translations.
	 * This method returns the correct rendered mail template.
	 * @remark Arguments are accessed in the template via a $mail smarty variable.
	 * 
	 * @param String $file name of the template (without .tpl extension)
	 * @param Array $arg[optional] arguments for the mail
	 * 
	 * @return String rendered e-mail 
	 * 
	 */
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
    
	/**
	 * Returns the text that fits given code and type for current language.
	 * Arguments can be added as an associative array, where key is the variable name to use in the XML file.
	 * XML variables are written between square braces : [variable]
	 * 
	 * @static
	 * @param String $type type of text
	 * @param String $code label of the title
	 * @param Array $arg[optional] arguments for the title
	 * 
	 * @return String translation matching type, code and language
	 */
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