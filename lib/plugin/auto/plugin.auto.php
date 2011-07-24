<?php
class auto {
    private
        $get = false;

    public function __construct($id = "") {

        if(!empty($id)):
            if(!is_object($id)):

                $auto = new query();
                $auto->Select()
                     ->From("auto")
                     ->Where("id", "=", $id)
                     ->exec('FIRST');
                if(!$auto->ok()):
                    return false;
                endif;

            else:
                $auto = $id;
            endif;

            $this->get = $auto->get();

            return $this;
        endif;
    }
    
    public function getSite() {
        if(!$this->get('site')):
            return false;
        endif;
        
        $site = new query();
        $site->Select()
             ->From('site')
             ->LeftJoin('user')
             ->On('user', 'id')
             ->Where('flux', '=', $this->get('flux'))
             ->Where('code', '=', str_replace($this->get('flux').'_', '', $this->get('site')))
             ->exec('FIRST');
        
        if($site->ok()):
            foreach ($site->get() as $key => $value):
                $this->set('_'.$key, $value);
            endforeach;
            return true;
        else:
            return false;
        endif;
    }

    public function get($key = false) {
        if(!$key):
            return $this->get;
        else:
            if(isset($this->get[$key])):
                if(empty($this->get[$key])):
                    return false;
                else:
                    if(preg_match('#\|#', $this->get[$key])):
                        return explode('|', $this->get[$key]);
                    else:
                        return $this->get[$key];
                    endif;
                endif;
            else:
                return false;
            endif;
        endif;
        
    }

    public function save() {
        $save = new query();
        $save->Insert("auto");
        foreach($this->get() as $key => $value):
            if(!preg_match('#^_#', $key)):
                $save->Set($key, $value);
            endif;
        endforeach;
        $save->Set('time', time());
        
        foreach($this->get() as $key => $value):
            $save->OnDuplicateKeyUpdate($key, $value);
        endforeach;

        $save->exec();
        
    }

    public function set($key, $value) {
        if(is_array($value)):
            $this->get[$key] = implode('|', $value);
        elseif($key == "modele"):
            $this->get["modele"] = str_replace('.', ' ', $value);
        else:
            $this->get[$key] = $value;
        endif;
    }
}

?>