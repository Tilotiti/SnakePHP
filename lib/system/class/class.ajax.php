<?php
class ajax {
	private 
		$header = array(),
		$type   = false;
		
	public function __construct() {
		$this->type('text');
	}
	
	public function header($key, $value) {
		$this->header[$key] = $value;
	}
	
	public function allowCrossDomain($domain = '*') {
		$this->header('Access-Control-Allow-Origin', $domain);
	}
	
	public function type($type) {
		switch($type):
			case "text":
				$this->header('Content-Type', 'text/html; charset='.CHARSET);
				break;
			case "json":
				$this->header('Content-Type', 'application/json');
				break;
			default:
				fatalError('<b>Ajax Error</b> : <code>ajax::$type</code> can\'t be set at <code>'.$type.'</code>');
				break;
		endswitch;
		$this->type = $type;
	}
	
	public function dispatcher($path) {
		$g = explode('/', $path);
        $nb = count($g);
                
        // Redirection vers l'index
        if(get($nb) == "false" || get($nb) == "" || get($nb) == false || is_int(get($nb))):
            $page = "index";
        else: 
            $page = get($nb);
        endif;

        // On inclue la page demandée si elle existe
        if(!file_exists(WEBROOT.'/ajax/'.$path.$page.'.php')):
            $page = "index";
        endif;
        
        return WEBROOT.'/ajax/'.$path.$page.'.php';
	}
	
	public function send($var = false) {
		$this->header('Generator', 'SnakePHP');
		
		foreach($this->header as $key => $value):
			header($key.':'.$value);
		endforeach;
		
		switch($this->type):
			case "json":
				exit(json_encode($var));
				break;
			default:
				exit($var);	
				break;
		endswitch;
	}
}