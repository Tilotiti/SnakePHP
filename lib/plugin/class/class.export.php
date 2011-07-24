<?php
class export {
    public
        $get = false;

    public function __construct($id) {
        $query = new query();
        $query->Select()
              ->From('export')
              ->Where('id', '=', $id)
              ->exec('FIRST');

        if(!$query->ok()):
            return false;
        else:
            $this->get = $query->get();
            return true;
        endif;
    }
    
    public function exec() {
        
        $smarty = new smarty();
        $smarty->template_dir = TEMPLATE.'/flux/';
        $smarty->compile_dir  = CACHE.'/flux/';
        
        $stock = new query();
        $stock->Select()
              ->From('auto');
        
        $del = new query();
        $del->Select('id')
            ->From('auto');
        
        foreach(explode('|', $this->get('site')) as $id):
            $site = new query();
            $site->Select('code', 'flux')
                 ->From('site')
                 ->Where('id', '=', $id)
                 ->exec('FIRST');
            
            if($site->ok()):
                $siteCode[] = $site->get('flux').'_'.$site->get('code');
            endif;
        endforeach;
        
        $del->Where('end', '>', $this->get['time'])
            ->Where('site', 'IN', $siteCode);
        
        $stock->Where('end', '=', 0)
              ->Where('site', 'IN', $siteCode)
              ->exec();
        
        if(file_exists(PLUGIN.'/class/export/class.'.$this->get('template').'.php')):
            require_once PLUGIN.'/class/export/class.'.$this->get('template').'.php';
            $templateClass = $this->get('template');
            $template = new $templateClass();
            $stats    = $template->export($stock, $this);
        else:
            return array('error', lang::error("export:template"));
        endif;
        
        switch($this->get('type')):
            case 'put':
                $this->FTPput();
            break;
            case 'get':
                $this->FTPget();
            break;
            default:
                return array('error', lang::error("export:type"));
            break;
        endswitch;
        
        $dir = opendir(FILE.'/export/tmp/');
        while(false !== ($file = readdir($dir))):
            if ($file != "." && $file != ".."):
                unlink(FILE.'/export/tmp/'.$file);
            endif;
        endwhile;
        closedir($dir);
        
        // Histo
        $del->exec();
        $stats['del'] = $del->count;
        
        $query = new query();
        $query->Select()
              ->From('histo')
              ->Where('time', '>', mktime(0, 0, 0))
              ->Where('export', '=', $this->get('id'))
              ->exec('FIRST');
        
        if(!$query->ok()):

            $histo = new query();
            $histo->Insert('histo')
                  ->Set('add', $stats['add'])
                  ->Set('maj', $stats['maj'])
                  ->Set('del', $stats['del'])
                  ->Set('tot', $stock->count)
                  ->Set('time', time())
                  ->Set('export', $this->get('id'))
                  ->exec();
        else:
            $histo = new query();
            $histo->Update('histo')
                  ->Set('add', $query->get('add')+$stats['add'])
                  ->Set('maj', $stats['maj'])
                  ->Set('del', $query->get('del')+$stats['del'])
                  ->Set('tot', $stock->count)
                  ->Set('time', time())
                  ->Where('id', '=', $this->get('id'))
                  ->exec();
        endif;
        
        $this->set('time', time());
        $this->set('auto', $stock->count);
        $this->save();
        return array("add" => $stats['add'], "maj" => $stats['maj'], "del" => $stats['del']);
    }
    
    public function FTPput() {
        $ftp = ftp_connect($this->get('server'));
        if(!$ftp):
            return array('error', lang::error("export:ftpServer"));
        endif;

        if(!ftp_login($ftp, $this->get('login'), $this->get('password'))):
            return array('error', lang::error("export:ftpLogin"));
        endif;
        
        $dir = opendir(FILE.'/export/tmp/');
        while(false !== ($file = readdir($dir))):
            if ($file != "." && $file != ".."):
                if(!ftp_put($ftp, $file, FILE.'/export/tmp/'.$file, FTP_BINARY)):
                    return array('error', lang::error("export:ftpPut"));
                endif;
            endif;
        endwhile;
        closedir($dir);

        return true;
    }
    
    public function FTPget() {
        $username = str_replace("@radarvo.com", "", $this->get('login'));
        $dir = opendir(FILE.'/export/tmp/');
        while($file = readdir($dir)):
            if ($file != "." && $file != ".."):
                @chmod(FILE.'/export/tmp/'.$file, 0777);
                @chmod(FILE.'/export/'.$username.'/'.$file, 0777);
                if(!is_dir(FILE.'/export/'.$username)):
                    mkdir(FILE.'/export/'.$username);
                    @chmod(FILE.'/export/'.$username, 0777);
                endif;
                rename(FILE.'/export/tmp/'.$file, FILE.'/export/'.$username.'/'.$file);
            endif;
        endwhile;
        closedir($dir);

        return true;
    }
    
    public function get($key = false) {
        if(!$key):
            return $this->get;
        elseif(isset($this->get[$key])):
            return $this->get[$key];
        else:
            return false;
        endif;
    }
    
    public function set($key, $value) {
        if(isset($this->get[$key])):
            $this->get[$key] = $value;
            return true;
        else:
            return false;
        endif;
    }
    
    public function save() {
        $query = new query();
        $query->Update('export');
        foreach($this->get as $key => $value):
            if($key != 'id'):
                $query->Set($key, $value);
            endif;
        endforeach;
        $query->Where('id', '=', $this->get('id'))
              ->exec();
    }
}
?>
