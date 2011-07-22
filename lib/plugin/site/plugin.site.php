<?php
class site {
    public
        $get   = false,
        $isset = false;

    public function __construct($code, $flux) {
       $this->set('code', $code);
       $this->set('flux', $flux);
    }

    public function get($key = false) {
        if(!$key):
            return $this->get;
        else:
            if(isset($this->get[$key])):
                if(empty($this->get[$key])):
                    return false;
                else:
                    return $this->get[$key];
                endif;
            else:
                return false;
            endif;
        endif;
    }
    
    public function set($key, $value) {
        $this->get[$key] = $value;
    }
    
    public function save() {
       $query = new query();
       $query->Select()
             ->From('site')
             ->Where('code', '=', $this->get('code'))
             ->Where('flux', '=', $this->get('flux'))
             ->exec();
       
       if($query->ok()):
            $query = new query();
            $query->Update('site')
                  ->Set('title', $this->get('title'))
                  ->Set('marque', $this->get('marque'))
                  ->Set('mail', $this->get('mail'))
                  ->Set('adresse', $this->get('adresse'))
                  ->Set('adresse2', $this->get('adresse2'))
                  ->Set('zipcode', $this->get('zipcode'))
                  ->Set('city', $this->get('city'))
                  ->Set('phone', $this->get('phone'))
                  ->Set('time', time())
                  ->Where('code', '=', $this->get('code'))
                  ->Where('flux', '=', $this->get('flux'))
                  ->exec();
        else:
            $insert = new query();
            $insert->Insert('site')
                   ->Set('code', $this->get('code'))
                   ->Set('flux', $this->get('flux'))
                   ->Set('title', $this->get('title'))
                   ->Set('marque', $this->get('marque'))
                   ->Set('mail', $this->get('mail'))
                   ->Set('adresse', $this->get('adresse'))
                   ->Set('adresse2', $this->get('adresse2'))
                   ->Set('zipcode', $this->get('zipcode'))
                   ->Set('city', $this->get('city'))
                   ->Set('phone', $this->get('phone'))
                   ->Set('time', time())
                   ->exec();
        endif;
        
        // Historisation
    }
}
?>
