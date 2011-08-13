<?php
class user {
    public
        $field  = false,
        $change = array(),
        $create = array(),
        $option = false;

    public
        $connect     = false;
    
    /*
     * Function : __construct()
     */

    public function __construct() {

        if($this->get('id')):
            $user = new query();
            $user->Select('id')
                 ->From('user')
                 ->Where('id', '=', $this->get('id'))
                 ->exec("FIRST");
            
            if(!$user->ok()):
                $this->logout();
                message::error('login:accountDeleted');
            endif;
            $this->connect = true;
        elseif(isset($_COOKIE['hash'])):
            $user = new query();
            $user->Select()
                 ->From('user')
                 ->Where('hash', "=", $_COOKIE['hash'])
                 ->exec('FIRST');
            
            if($user->ok()):
                $this->login($user);
                $this->connect = true;
            else:
                $this->connect = false;
            endif;
        else:
            $this->connect = false;
        endif;
    }
    
    /*
    * Function : update($lvl)
    * @desc    : Update the user account
    * @return  : (bool) success
    */

    public function update() {
        
        cookie("hash", $this->get('hash'), 60*60*24*365);
        
        $user = new query();
        $user->Update('user');

        foreach($_SESSION['user'] as $key => $value):
             $user->Set($key, $value);
        endforeach;

        $user->Where('id', '=', $this->get('id'));
        $user->exec();

        return true;

    }
    
    /*
     * Function : sign()
     * @desc    : Create the user in DB
     */

    public function sign() {
        
        cookie("hash", $this->get('hash'), 60*60*24*365);
        $this->set('hash', $_SERVER['UNIQUE_ID']);
        $this->set('rank', 'user');
        $this->set('connect', time());

        $user = new query();
        $user->Insert('user')
             ->Set('username', $this->get('username'))
             ->exec();

        $this->set('id', $user);
        $this->connect = true;
        $this->update();

    }
    
    /*
     * Function : login($query)
     * @desc    : login the user in session
     * @param   : $query(query) A complete user query
     * @return  : (bool) success
     */

    public function login($query) {

	if(!$query->ok() || count($query->get()) == 0):
            return false;
        endif;

        $_SESSION['user'] = $query->get();
        $this->set('hash', $_SERVER['UNIQUE_ID']);
        $this->set('connect', time());
	$this->connect = true;
        cookie("hash", $this->get('hash'), 60*60*24*365);
        $this->update();
        return true;
    }

    /*
     * Function : logout()
     * @desc    : Logout the user
     */
    public function logout() {
        $this->set('hash', $_SERVER['UNIQUE_ID']);
        $this->update();

        unset($_SESSION['user']);
        cookie("hash", "", 0);
        $this->connect = false;
    }

    public function option($key, $value = '') {
        if(!$this->option):
            $req = new query();
            $req->Select('name','value')
                ->From("option")
                ->Where('type', '=', "user")
                ->Where('owner', '=', $this->get('id'))
                ->exec("ALL");

            while($result = $req->next()):
                $this->option[$req->get("name")] = $req->get("value");
            endwhile;
        endif;

        if(empty($value)):
            if(isset($this->option[$key])):
                return $this->option[$key];
            else:
                return false;
            endif;
        else:
            if(!isset($this->option[$key])):
                $this->create[] = $key;
                $this->option[$key] = $value;
                return true;
            elseif($this->option[$key] != $value):
                $this->change[] = $key;
                $this->option[$key] = $value;
                return true;
            else:
                return false;
            endif;
        endif;
    }
    
    public function connect() {
        return $this->connect;
    }

    public function get($key) {
        if(isset($_SESSION['user'][$key])):
            return $_SESSION['user'][$key];
        else:
            return false;
        endif;
    }

    public function set($key, $value) {
        $_SESSION['user'][$key] = $value;
        return true;
    }
    
    public function rank($rank = "admin") {
        if($this->get('rank') == $rank):
            return true;
        endif;
        
        return false;
    }
    
    public function allow($path) {
        switch($path):
            case 'admin':
                if($this->rank('admin')):
                    return true;
                endif;
            break;
        endswitch;
        return false;
    }

}
?>