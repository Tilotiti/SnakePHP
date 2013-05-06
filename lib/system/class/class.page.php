<?php
class page {
    private
        $site        = '',
        $mail        = '',
        $name        = '',
        $cat         = '',
        $templateTPL = '',
        $ariane      = '',
        $title       = '',
        $description = '',
        $keywords    = '',
        $year        = 0,
        $template    = false,
        $notFound    = false,
        $sidebar     = array(),
        $JS          = array(),
        $CSS         = array(),
        $sitemap     = array(),
        $time        = array();
   
    public function __construct() {

        debug::timer('Initialization', true);
        
        // Set a title by default
        $this->title = get(1);
        
        // initiation de smarty
        $this->template               = new smarty();
        $this->template->template_dir = TEMPLATE;
        $this->template->compile_dir  = CACHE;
	    
        // Définition des sessions
        isset($_SESSION['error'])   or $_SESSION['error']   = false;
        isset($_SESSION['save'])    or $_SESSION['save']    = false;
        isset($_SESSION['message']) or $_SESSION['message'] = false;
	    
        // Génération du siteMap
        if(file_exists(WEBROOT.'/sitemap.xml')):
	        $xml = simplexml_load_file(WEBROOT.'/sitemap.xml', 'SimpleXMLElement', LIBXML_NOCDATA);
	        foreach($xml->url as $url):
	            $this->sitemap[(string)$url->loc] = array(
	                'lastmod'     => (string) $url->lasmod,
	                'changefreq' => (string) $url->changefreq,
	                'priority'   => (string) $url->priority,
	            );
	        endforeach;
        endif;
        
    }
    public function dispatcher($path, $cat = '') {

        $g = explode('/', $path);
        $nb = count($g)-1;
        
        // Redirection vers l'index
        if(get($nb) == "false" || get($nb) == "" || get($nb) == false || is_int(get($nb))):
            $page = "index";
        else: 
            $page = get($nb);
        endif;
        
        header("HTTP/1.0 200 OK");

        // On inclue la page demandée si elle existe
        if(!file_exists(SOURCE.'/'.$path.$page.'.php')):
            $page = "index";
            $this->notFound = true;
            header("HTTP/1.0 404 Not Found");
        endif;
        
        // On redirige en cas d'erreur (La page TPL n'existe pas)
        if(!file_exists(TEMPLATE.$path.$page.'.tpl')):
            $page = "index";
            $this->notFound = true;
            header("HTTP/1.0 404 Not Found");
        endif;

        $this->templateTPL = substr($path.$page, 1);
        $this->cat         = $cat;
        $this->name        = get($nb);
        
        return SOURCE.'/'.$path.$page.'.php';
    }

    public function copyright() {
        if(YEAR == date('Y')):
            echo '&copy; <a href="'.URL.'">'.SITE.'</a> | '.date('Y');
        else:
            echo '&copy; <a href="'.URL.'">'.SITE.'</a> | '.YEAR.' - '.date('Y');
        endif;
    }

    public function clear() {
        $_SESSION['message'] = false;
        $_SESSION['error']   = false;
        $_SESSION['debug']   = false;
        
        
        // Merci d'aider au développement d'SnakePHP en gardant cette mention apparaître dans le code source de votre site
        // Thank you for helping to develop SnakePHP keeping this statement appear in the source code of your site
        echo "\n"."<!--"."\n";
        echo "##############################################################"."\n";
        echo "######## Developped with SnakePHP                      #######"."\n";
        echo "######## Web :    http://www.SnakePHP.net              #######"."\n";
        echo "######## Github : https://github.com/Tilotiti/SnakePHP #######"."\n";
        echo "##############################################################"."\n";
        echo "-->"."\n";
    }

    public function pushAriane($name, $url = "") {
        $array = array("name" => $name, "url" => $url);
        $this->ariane[] = $array;
    }

    public function ariane($url = "") {
        
        echo '<a href="/">'.lang::text("ariane").'</a>';
        echo lang::text('ariane:separator');
        echo '<a href="/">'.lang::title("index").'</a>';

        if(is_array($this->ariane)):
            foreach($this->ariane as $fil):
                if(empty($fil['url'])):
                    echo lang::text('ariane:separator').' '.lang::title($fil['name']);
                else:
                    echo lang::text('ariane:separator').' <a href="'.$fil['url'].'">'.lang::title($fil['name']).'</a>';
                endif;
            endforeach;
        endif;

        if(get(1) != "index"):
            echo lang::text('ariane:separator').' <a href="'.get().'" class="current">'.$this->title.'</a>';
        endif;
    }
    
    public function title($title, $array = false) {
        $this->title = lang::title($title, $array);
    }
    
    public function getTitle() {
        return $this->title;
    }
    
    public function pushSidebar($file, $id = false, $params = false) {
        if(file_exists(TEMPLATE.'/sidebar/'.$file.'.tpl')):
            if(!is_array($this->sidebar)):
                $this->sidebar = array();
            endif;
            if(!$id):
                $this->sidebar[] = $file;
            else:
                if(!isset($this->sidebar[$id]) || !is_array($this->sidebar[$id])):
                    $this->sidebar[$id] = array();
                endif;
                $this->sidebar[$id][] = $file;
            endif;
            
            if(file_exists(SOURCE.'/sidebar/'.$file.'.php')):
                global $template, $user;
                include_once SOURCE.'/sidebar/'.$file.'.php';
                return true;
            else:
                return false;
            endif;
        endif;
    }
    
    public function getSidebar($id = false) {
        if(!$id):
            return $this->sidebar;
        else:
            if(isset($this->sidebar[$id])):
                return $this->sidebar[$id];
            else:
                return $this->sidebar;
            endif;
        endif;
    }
    
    public function pushCSS($file) {
        if(file_exists(WEBROOT.'/css/'.$file.'.css')):
             if(!is_array($this->CSS)):
                $this->CSS = array();
            endif;
            $this->CSS[] = $file;
        endif;
    }
    
    public function getCSS() {
        return $this->CSS;
    }
    
     public function pushJS($file) {
        if(file_exists(WEBROOT.'/js/'.$file.'.js')):
            if(!is_array($this->JS)):
                $this->JS = array();
            endif;
            $this->JS[] = $file;
        endif;
    }
    
    public function getJS() {
        return $this->JS;
    }
    
    public function active($get, $class = 'active') {
        $get = preg_replace("#\%#isU", "(.+)?", $get);
        if(preg_match("#^".$get."$#isU", get()) || $get == get(1)):
            echo $class;
        endif;
    }
    
    /**
     * Affiche la barre de debug sur le site
     * 
     * @return void
     */
    public function debug() {
    	
	    if(($_SERVER["REMOTE_ADDR"] == IPADMIN || in_array($_SERVER["REMOTE_ADDR"], explode('|', IPADMIN))) && DEV):
	    	$bar     = ""; // Affichage de la barre
	    	$content = ""; // Affichage du contenu
	    	
	    	$template = new smarty();
	        $template->template_dir = SYSTEM.'/template/';
	        $template->compile_dir  = CACHE;
	        
	        $badge = array();
	        
	    	// Errors
	    	$badge['error'] = array(
	        	'count' => count(debug::$error),
	        	'type'  => (count(debug::$error) > 0) ? 'badge-important' : ''
	        );
	    	$template->assign('listError', debug::$error);
	    	
	    	// SQL
	    	$badge['sql'] = array(
	        	'count' => count(debug::$sql),
	        	'type'  => (count(debug::$sql) > 0) ? 'badge-info' : ''
	        );
	    	$template->assign('listSQL', debug::$sql);

	    	// Dump
	    	$badge['dump'] = array(
	        	'count' => count(debug::$dump),
	        	'type'  => (count(debug::$dump) > 0) ? 'badge-info' : ''
	        );
	        
	        $listDump = array();
	        if(is_array(debug::$dump)):
	    		foreach(debug::$dump as $dump):
	    			ob_start();
	    			var_dump($dump['array']);
	    			$dump['array'] = ob_get_clean();
	    			$dump['title'] = ($dump['title'])? $dump['title'] : 'Dump';
	                $listDump[] = $dump;
	    		endforeach;
    		endif;
    		$template->assign('listDump', $listDump);
	    	
	    	// Globals
    		$global = array();
    		$global[] = array(
    			'title' => '$_SERVER',
    			'var'   => $_SERVER
    		);
    		$global[] = array(
    			'title' => '$_SESSION',
    			'var'   => $_SESSION
    		);
    		$global[] = array(
    			'title' => '$_POST',
    			'var'   => $_POST
    		);
    		    		
    		$listGlobal = array();
    		foreach($global as $glob):
    			ob_start();
    			var_dump($glob['var']);
    			$glob['var'] = ob_get_clean();
                $listGlobal[] = $glob;
    		endforeach;
    		
    		$template->assign('listGlobal', $listGlobal);
	    	
	    	// Timer
	    	debug::timer("Loaded");
	    	$badge['timer'] = array(
	        	'count' => count(debug::$timer),
	        	'type'  => (count(debug::$timer) > 0) ? 'badge-info' : ''
	        );
	    	    		
    		$total = debug::$timer[count(debug::$timer) -1]['time'];
    		$listTimer = array();
    		foreach(debug::$timer as $timer):
    			$timer['pourcent'] = ($timer['time'] / $total)*100;
                $listTimer[] = $timer;
    		endforeach;
	    	$template->assign('listTimer', $listTimer);
	    	
	    	$template->assign('badge', $badge);
	        return $template->fetch('debug.tpl');
	    	
	    endif;
    }
    
    public function template($assign, $var) {
	    $this->template->assign($assign, $var);
    }
    
    public function setTemplate($file) {
	    $this->templateTPL = $file;
    }
    
    public function display($template = "template") {
	    debug::timer('Loading templates');
	    $this->template->display($template.".tpl");
    }
    
    public function description($text) {
	    $this->description = $text;
    }
    
    public function pushKeyword($keyword) {
    	if(!empty($this->keywords)):
	    	$keywords       = explode(', ', $this->keywords);
	    else:
	    	$keywords = array();
	    endif;
	    $keywords[]     = $keyword;
	    $this->keywords = implode(', ', $keywords);
    }
    
    public function sitemap($changefreq = "monthly", $priority = 0.5) {
    	if(!isset($this->sitemap[URL.get()]) && !$this->notFound && file_exists(WEBROOT.'/sitemap.xml') && is_writable(WEBROOT.'/sitemap.xml')):
	    	$this->sitemap[URL.get()] = array(
        		'lastmod'    => date('Y-m-d'),
        		'changefreq' => $changefreq,
        		'priority'   => $priority
        	);
        	   	
        	$xml = simplexml_load_file(WEBROOT.'/sitemap.xml');
            $url = $xml->addChild('url');
            
            $url->addChild('loc',        URL.get());
            $url->addChild('lastmod',    date('Y-m-d'));
            $url->addChild('changefreq', $changefreq);
            $url->addChild('priority',   $priority);
            
            $dom = new DOMDocument('1.0');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($xml->asXML());
            
            $dom->save(WEBROOT.'/sitemap.xml');
        endif;
    }
		
    public function get($key) {
        return $this->$key;
    }
	
    public function __set($key, $value) {
		$this->$key = $value;
        return true;
    }
		
    public function __isset($key) {
        return isset ($this->$key);
    }
	
}
?>
