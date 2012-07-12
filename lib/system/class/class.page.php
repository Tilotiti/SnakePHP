<?php
class page {
    private
        $site        = '',
        $mail        = '',
        $name        = '',
        $cat         = '',
        $templateTPL = '',
        $ariane      = '',
        $meta        = '',
        $year        = 0,
        $template    = false,
        $sidebar     = false,
        $JS          = false,
        $CSS         = false,
        $time        = array();
   
    public function __construct() {
        debug::timer('timer:start', true);
        
        $this->sidebar = array();
        $this->JS      = array();
        $this->CSS     = array();
        
         // initiation de smarty
	    $this->template               = new smarty();
	    $this->template->template_dir = TEMPLATE;
	    $this->template->compile_dir  = CACHE;
	    
	    // Définition des sessions
	    isset($_SESSION['error'])   or $_SESSION['error']   = false;
	    isset($_SESSION['save'])    or $_SESSION['save']    = false;
	    isset($_SESSION['message']) or $_SESSION['message'] = false;
        
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
            header("HTTP/1.0 404 Not Found");
        endif;
        
        // On redirige en cas d'erreur (La page TPL n'existe pas)
        if(!file_exists(TEMPLATE.$path.$page.'.tpl')):
            $page = "index";
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
        
        
        // Merci d'aider au développement d'EdenPHP en gardant cette mention apparaître dans le code source de votre site
        // Thank you for helping to develop EdenPHP keeping this statement appear in the source code of your site
        echo "\n"."<!--"."\n";
        echo "##############################################################"."\n";
        echo "######## Developped with EdenPHP                      ########"."\n";
        echo "######## Web :    http://www.edenphp.net              ########"."\n";
        echo "######## Github : https://github.com/Tilotiti/EdenPHP ########"."\n";
        echo "##############################################################"."\n";
        echo "-->"."\n";
    }

    public function addAriane($name, $url = "") {
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
    
    public function active($get) {
        if($get == get(1)):
            echo 'active';
        endif;
    }
    
    /**
     * Affiche la barre de debug sur le site
     * 
     * @return void
     */
    public function debug() {
    	//debug::clear();
    	
    	// Si l'adresse IP est définie
	    if($_SERVER["REMOTE_ADDR"] == IPADMIN && DEV):
	    	$bar     = ""; // Affichage de la barre
	    	$content = ""; // Affichage du contenu
	    	
	    	// On affiche le debugContent
	    	$content .= '<div id="debugContent">';
	    	
	    	// On affiche la barre de debug
	    	$bar .= '<div id="debug" class="navbar navbar-fixed-bottom"><div class="navbar-inner"> <div class="container">';
	    	$bar .= '<span class="brand" href="#">Debug</span>';
	    	$bar .= '<ul class="nav">';
	    	
	    	
	    	/*
	    	 * Erreurs
	    	 */
	    	 
	    	$bar .= '<li id="debugError">';
	    	
	    	// On compte le nombre d'erreur
	    	if(count(debug::$error) > 0):
	    		$bar     .= '<a href="#">'.lang::text('debug:error').' <span class="badge badge-important">'.count(debug::$error).'</span></a>';
	    		$content .= '<div id="debugErrorContent" class="debugContent">';
	    		
	    		// On affiche les erreurs dans le debugContent
	    		foreach(debug::$error as $error):
                    $content .= "<ul>";
                    $content .= "    <li><b>".lang::text('debug:error:type')."</b> : ".$error['type']."</li>";
                    $content .= "    <li><b>".lang::text('debug:error:file')."</b> : ".$error['file']."</li>";
                    $content .= "    <li><b>".lang::text('debug:error:line')."</b> : ".$error['line']."</li>";
                    $content .= "    <li><b>".lang::text('debug:error:str') ."</b> : ".$error['str'] ."</li>";
                    $content .= "</ul>";
	    		endforeach;
	    		
	    		$content .= '</div>';
	    	else:
	    		// Aucune erreur
	    		$bar .= '<a href="#">'.lang::text('debug:error').' <span class="badge">0</span></a>';
	    		$content .= '<div id="debugErrorContent" class="debugContent empty">'.lang::text('debug:noError').'</div>';
	    	endif;
	    	
	    	$bar .= '</li>';
	    	
	    	/*
	    	 * Requêtes SQL
	    	 */
	    	 
	    	$bar .= '<li id="debugSQL">';
	    	
	    	// On compte le nombre de requête SQL effectuée
	    	if(count(debug::$sql) > 0):
	    		$bar     .= '<a href="#">'.lang::text('debug:SQL').' <span class="badge badge-important">'.count(debug::$sql).'</span></a>';
	    		$content .= '<div id="debugSQLContent" class="debugContent">';
	    		
	    		// On affiche les requêtes dans le debugContent
	    		foreach(debug::$sql as $sql):
                    $content .= "<ul>";
                    $content .= "    <li><b>".lang::text('debug:sql:req')  ."</b> : ".$sql['req']  ."</li>";
                    $content .= "    <li><b>".lang::text('debug:sql:count')."</b> : ".$sql['count']."</li>";
                    $content .= "</ul>";
	    		endforeach;
	    		$content .= '</div>';
	    	else:
	    		$bar .= '<a href="#">'.lang::text('debug:SQL').' <span class="badge">0</span></a>';
	    		$content .= '<div id="debugSQLContent" class="debugContent empty">'.lang::text('debug:noSQL').'</div>';
	    	endif;
	    	
	    	$bar .= '</li>';

	    	/*
	    	 * Variables
	    	 */
	    	 
	    	$bar .= '<li id="debugDump">';
	    	
	    	// On compte le nombre de débug de variable
	    	if(count(debug::$dump) > 0):
	    		$bar     .= '<a href="#">'.lang::text('debug:dump').' <span class="badge badge-important">'.count(debug::$dump).'</span></a>';
	    		$content .= '<div id="debugDumpContent" class="debugContent">';
	    		
	    		// On affiche les requêtes dans le debugContent
	    		foreach(debug::$dump as $dump):
	    		
	    			// On enregistre le var_dump();
	    			ob_start();
	    			var_dump($dump['array']);
	    			$dump['array'] = ob_get_clean();
	    			
	    			// On met un titre pas default
	    			if(!$dump['title']):
	    				$dump['title'] = lang::text('debug:dump:default');
	    			endif;
	    			
                    $content .= "<ul>";
                    $content .= "    <li><b>".lang::text('debug:dump:title')  ."</b> : ".$dump['title']  ."</li>";
                    $content .= "    <li><b>".lang::text('debug:dump:content')."</b> : <br /><pre>".$dump['array']."</pre></li>";
                    $content .= "</ul>";
	    		endforeach;
	    		$content .= '</div>';
	    	else:
	    		$bar .= '<a href="#">'.lang::text('debug:dump').' <span class="badge">0</span></a>';
	    		$content .= '<div id="debugDumpContent" class="debugContent empty">'.lang::text('debug:noDump').'</div>';
	    	endif;
	    	
	    	$bar .= '</li>';
	    	
	    	/*
	    	 * Globales
	    	 */
	    	 
	    	$bar .= '<li id="debugGlobal">';
    		$bar     .= '<a href="#">'.lang::text('debug:global').' <span class="badge badge-info">3</a>';
    		$content .= '<div id="debugGlobalContent" class="debugContent">';
    		
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
    		
    		// On affiche les globales dans le debugContent
    		foreach($global as $glob):
    		
    			// On enregistre le var_dump();
    			ob_start();
    			var_dump($glob['var']);
    			$glob['var'] = ob_get_clean();
    			
                $content .= "<ul>";
                $content .= "    <li><b>".lang::text('debug:global:title')  ."</b> : ".$glob['title']  ."</li>";
                $content .= "    <li><b>".lang::text('debug:global:content')."</b> : <br /><pre>".$glob['var']."</pre></li>";
                $content .= "</ul>";
    		endforeach;
    		$content .= '</div>';
	    	$bar .= '</li>';
	    	
	    	
	    	
	    	/*
	    	 * Timer
	    	 */
	    	 
	    	debug::timer(lang::text('timer:end'));
	    	
	    	$bar .= '<li id="debugTimer">';
    		$bar     .= '<a href="#">'.lang::text('debug:timer').' <span class="badge badge-info">'.count(debug::$timer).'</a>';
    		$content .= '<div id="debugTimerContent" class="debugContent">';
    		
    		// Calcul du temp total
    		$total = debug::$timer[count(debug::$timer) -1]['time'];
    		
    		$content .= "<ul>";
    		// On affiche les timers dans le debugContent
    		foreach(debug::$timer as $timer):
    			$pourcent = ($timer['time'] / $total)*100;
                
                $content .= '<li>
                				<span class="timerTitle"><b>'.$timer['title'].'</b></span>
                				<div class="progress"><div class="bar" style="width: '.$pourcent.'%"></div></div>
                				<span class="timerSeconde">'.$timer['time'].' secondes</span>
                			</li>';
                
    		endforeach;
    		$content .= "</ul>";
    		$content .= '</div>';
	    	$bar .= '</li>';
	   
	    	
	    	// Affichage de la barre et de son contenu
	    	$bar .= '</ul></div></div></div>';
	    	$content .= '</div>';
	    	
	    	echo $content;
	    	echo $bar;
	    endif;
    }
    
    public function template($assign, $var) {
	    $this->template->assign($assign, $var);
    }
    
    public function setTemplate($file) {
	    $this->templateTPL = $file;
    }
    
    public function display() {
	    debug::timer(lang::text('timer:template'));
	    $this->template->display("template.tpl");
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