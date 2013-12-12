<?php
class option {
    private
        $option = false,
        $owner  = false,
        $change = false,
        $create = false,
        $add    = false;

    public function __construct($owner) {
        $this->owner = $owner;

        $query = new query();
        $query->Select("name", "value")
              ->From("option")
              ->Where("site", "=", $owner)
              ->exec("all");

        while($query->next()):
            switch($query->get('value')):
                case "true":
                    $this->option[$query->get('name')] = true;
                break;
                case "false":
                case "":
                    $this->option[$query->get('name')] = false;
                break;
                default:
                    $this->option[$query->get('name')] = $query->get('value');
                break;
            endswitch;
        endwhile;
    }

    public function get($name) {
        if(empty($name)):
            return $this->option;
        elseif(isset($this->option[$name])):
            return $this->option[$name];
        else:
            $this->set($name);
            $this->save();
            return false;
        endif;
    }

    public function set($name, $value = "") {     
        if(isset($this->option[$name])):
            $this->change[]  = $name;
        else:
            $this->create[]  = $name;
        endif;
        $this->option[$name] = $value;
        
        return $this;
    }

    public function putArray($option, $value) {
        if($this->get($option)):
            if($this->get($option) == ""):
                $this->set($option, $value);
            else:
                $explode = explode("|", $this->get($option));
                if(!in_array($value, $explode)):
                    $explode[] = $value;
                    $this->set($option, implode('|', $explode));
                endif;
                return true;
            endif;
        else:
            return false;
        endif;
    }

    public function delArray($option, $value) {
        if($this->get($option)):
            foreach(explode('|', $this->get($option)) as $line):
                if($line != $value):
                    $array[] = $line;
                endif;
            endforeach;
            $this->set($option, implode('|', $array));
        else:
            return false;
        endif;
    }

    public function save() {
        if(is_array($this->change)):
            foreach($this->change as $change):
                $query = new query();
                $query->Update('option')
                      ->Set('value', $this->option[$change])
                      ->Where('site', '=', $this->owner)
                      ->Where("name", "=", $change)
                      ->exec();
            endforeach;
        endif;

        if(is_array($this->create)):
            foreach($this->create as $create):
                $query = new query();
                $query->Insert('option')
                      ->Set('value', $this->option[$create])
                      ->Set('site', $this->owner)
                      ->Set('name', $create)
                      ->exec();
            endforeach;
        endif;

        $this->change = array();
        $this->create = array();
    }
}