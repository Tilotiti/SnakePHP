<?php
/**
 * Page manager : dispatcher, css and js, template, breadcrumb, SEO, etc. 
 * @author Tilotiti
 */
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
	/**
	 * Constructor - initialise :
	 * - page title (@see get)
	 * - main smarty template
	 * - session variables
	 * - sitemap
	 */
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
	
	/**
	 * Dispatcher function : parse URI to find matching php file
	 * @param String $path relative URI to parse
	 */
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

	/**
	 * Copyright generator
	 * Generates and output a copyright line according to config constant URL SITE and YEAR
	 * @return void
	 */
    public function copyright() {
        if(YEAR == date('Y')):
            echo '&copy; <a href="'.URL.'">'.SITE.'</a> | '.date('Y');
        else:
            echo '&copy; <a href="'.URL.'">'.SITE.'</a> | '.YEAR.' - '.date('Y');
        endif;
    }

	/**
	 * Clean session variables and output SnakePHP mention
	 * Should be called at the bottom of the page
	 * @return void
	 */
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

	/**
	 * Adds a breadcrumb item
	 * @param String $name text to show
	 * @param String[optional] $url target URI - default: current
	 * @return void
	 */
    public function pushAriane($name, $url = "") {
        $array = array("name" => $name, "url" => $url);
        $this->ariane[] = $array;
    }

	/**
	 * Outputs breadcrumb
	 * @remark Current element (understand current page) is automatically included at the end of the breadcrumb
	 * @return void
	 */
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
    
	/**
	 * Set title for current page (will be output in <title>)
	 * @param String $title lang code for title
	 * @param Array[optional] 
	 */
    public function title($title, $array = false) {
        $this->title = lang::title($title, $array);
    }
    /**
	 * Returns the page title 
	 * @return String title
	 */
    public function getTitle() {
        return $this->title;
    }
    /**
	 * Include sidebar PHP file and adds matching template to sidebar
	 * Sidebar source/template must be in /app/{source|template}/sidebar/ 
	 * 
	 * @remark if you want to assign a variable to the template from a sidebar source file, use $this->template
	 * instead of $page->template. Scope would be the main smarty object, so be careful with variable names.
	 * 
	 * @param String $file name of sidebar (without extension)
	 * @param String|Integer[optional] $id allow you to set a custom id to the sidebar - default: none
	 * @return true if sidebar added, false otherwise (missing source or template file)
	 */
    public function pushSidebar($file, $id = false, $params = false) {
    	// XXX params should appear somewhere
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
    
	/**
	 * Returns path to sidebar template(s), for smarty inclusion
	 * If id not specified, will return all sidebars
	 * @param Integer[optional] $id id of the sidebar to get path of - default: none
	 * @return String path to the sidebar
	 */
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
    /**
	 * Adds a CSS file. CSS path is relative to /webroot/css/
	 * @param String[optional] $file css file path (without extension)
	 * @return void
	 */
    public function pushCSS($file) {
        if(file_exists(WEBROOT.'/css/'.$file.'.css')):
             if(!is_array($this->CSS)):
                $this->CSS = array();
            endif;
            $this->CSS[] = $file;
        endif;
    }
    /**
	 * Returns the list of all included CSS files
	 * @return Array list of CSS files
	 */
    public function getCSS() {
        return $this->CSS;
    }
     
	/**
	 * Adds a JS file. JS path is relative to /webroot/js/
	 * @param String[optional] $file js file path (without extension)
	 * @return void
	 */
    public function pushJS($file) {
        if(file_exists(WEBROOT.'/js/'.$file.'.js')):
            if(!is_array($this->JS)):
                $this->JS = array();
            endif;
            $this->JS[] = $file;
        endif;
    }
    /**
	 * Returns the list of all included JS files
	 * @return Array list of JS files
	 */
    public function getJS() {
        return $this->JS;
    }
    
	/**
	 * Check if given url is active (~ current URL) and generate a custom output if so
	 * example of use : <a href="/user/" class="{$page->active('user')}">Users</a>
	 * @remark if given uri is user and current uri is /user/edit/, user will be considered "active" because it's a
	 * first-level dispatcher. If current URL is /user/edit/15/, /user/edit/ will NOT be considered active.
	 * 
	 * @param $get URI (beginning with '/') or first-level dispatcher (without slash)
	 * @param String[optional] $class output in case of active URI - default: "active"
	 */
    public function active($get, $class = 'active') {
        $get = preg_replace("`\%`isU", "(.+)?", $get);
        if(preg_match("`^".$get."$`isU", get()) || $get == get(1)):
            echo $class;
        endif;
    }
    
    /**
     * Show up debug bar on the page
     * @see debug
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
    /**
	 * Assign a variable to the main template
	 * @param String $assign variable name
	 * @param mixed $var variable value
	 * @return void
	 */
    public function template($assign, $var) {
	    $this->template->assign($assign, $var);
    }

	/**
	 * 
	 * @return void
	 */
    public function setTemplate($file) { // is this useful/relevant ?
	    $this->templateTPL = $file;
    }
    /**
	 * Display page according to a template. Path is relative to /app/template/
	 * @param String[optional] $template path to template - default: template
	 * @return void 
	 */
    public function display($template = "template") {
	    debug::timer('Loading templates');
	    $this->template->display($template.".tpl");
    }
    /**
	 * Set the page description. You can use this as meta description, but also for openGraph data, etc.
	 * @param String $text description content
	 * @return void
	 */
    public function description($text) {
	    $this->description = $text;
    }
    
	/**
	 * Add a keyword. No longer relevant to SEO, but you could find another use.
	 * @param String $keyword keyword to add
	 * @return void
	 */
    public function pushKeyword($keyword) {
    	if(!empty($this->keywords)):
	    	$keywords       = explode(', ', $this->keywords);
	    else:
	    	$keywords = array();
	    endif;
	    $keywords[]     = $keyword;
	    $this->keywords = implode(', ', $keywords);
    }
    
	/**
	 * Adds current URL to sitemap
	 * @param String[optional] $changefreq a literal frequency (daily, weekly...) - default: monthly
	 * @param Float[optional] $priority a number between 0 and 1 indicating priority of indexation
	 * @return void
	 */
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
	
	/**
	 * Generic getter
	 * @param String page attribute name
	 * @return mixed page attribute value
	 */
    public function get($key) {
        return $this->$key;
    }
	/**
	 * Magic setter
	 * @param String page attribute name
	 * @param mixed page attribute value
	 * @return Boolean true
	 */
    public function __set($key, $value) {
		$this->$key = $value;
        return true;
    }
	/**
	 * Magic checker
	 * @param String page attribute name
	 * @return Boolean true if $this->key exists, false otherwise
	 */
    public function __isset($key) {
        return isset($this->$key);
    }
	
}