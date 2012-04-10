<?php
class page {
    private
        $site        = '',
	$mail        = '',
	$name        = '',
	$cat         = '',
        $ariane      = '',
	$year        = 0 ,
	$time        = 0 ,
	$start       = 0 ,
	$template    = '',
        $sidebar     = false,
        $JS          = false,
        $CSS         = false,
        $meta        = '';

    static
        $sql         = 0;
   
    public function __construct() {
        
        $this->start   = $this->micro_time();
        $this->sidebar = array();
        $this->JS      = array();
        $this->CSS     = array();
        
    }
	
    public function micro_time() {
	$time = explode(" ", microtime());
        return ($time[1] + $time[0]);
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

        $this->template = substr($path.$page,1);
        $this->cat      = $cat;
        $this->name     = get($nb);
        
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
        echo "#############################################################"."\n";
        echo "######## Developped with EdenPHP                     ########"."\n";
        echo "######## Web :    http://www.edenphp.net             ########"."\n";
        echo "######## Github : https://github.com/EdenPHP/EdenPHP ########"."\n";
        echo "#############################################################"."\n";
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
		
    public function get($key) {
        $this->time = round(($this->micro_time() - $this->start),4);
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