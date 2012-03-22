<?php

class query {
    public
        $prepare_request = '',
        $result          = '',
        $values          = '',
        $table           = '',
        $duplicate       = '',
        $which           = 'all',
        $alias           = array(),
        $line            = array(),
        $content         = array(),
        $fields          = array();

    public function __construct() {
        $this->reset();
    }

    public function getField($params) {
        if($params == "*"):
            return '*';
        endif;
            
        if($this->content['select']):
            $table = DBPREF.$this->table['select'];
            $pref  = $this->table['select'];
        elseif($this->content['delete']):
            $table = DBPREF.$this->table['delete'];
            $pref  = $this->table['delete'];
        else:
            $table = DBPREF.$this->table['set'];
            $pref  = $this->table['set'];
        endif;

        if(is_array($params)):
            // Si le champ demandé est un array
            $field = "";
            $as    = "";
            foreach($params as $key => $value):
                switch($key):
                    case "AS":
                        $this->alias[] = $value;
                        // Création d'un alias
                        if(is_array($value) && count($value) == 2 ):
                            // Si l'allias est simple
                            return $this->getField($value[0]).' AS '.$value[1];
                        else:
                            // Si l'allias vient avec une fonction
                            $as = " AS ".$value;
                        endif;
                    break;
                    case "ALLIAS":
                        return $value;
                    break;
                    default:
                        if(strtoupper($key) == $key):
                            // Si l'identification se fait par une fonction
                            if(is_array($value)):
                                $field  = $key."(";     
                                $var = array();
                                foreach($value as $val):
                                    if(is_numeric($val)):
                                        $var[] = $val;
                                    else:
                                        $var[] = $this->getField($val);
                                    endif;
                                endforeach;
                                $field .= implode(',', $var);
                                $field .= ")";
                            else:
                                $field = $key."(".$this->getField($value).")";
                            endif;
                        else:
                            // Si l'identification est simple (simple Join)
                            if($value != "*"):
                                $field = $key.'_'.$value;
                            else:
                                $field = DBPREF.$key.'.*';
                            endif;
                        endif;
                    break;
                endswitch;
            endforeach;

            return $field.$as;

        else:
            // Si le champs demandé est simple
            if(in_array($params, $this->alias)):
                // Si le champs est reconnu comme un alias
                return $params;
            else:
                // Si c'est un champs simple
                return $pref.'_'.$params;
            endif;
        endif;
    }

    public function select() {
        $this->reset();
        $this->fields = func_get_args();
        $this->content['select'] = true;
        return $this;
    }

    public function update($table) {
        $this->reset();
        $table_name                = DBPREF.$table;
        $this->prepare_request    .= ' UPDATE '.$table_name;
        $this->content['update']   = true;
        $this->table['set']        = $table;
        return $this;
    }

    public function insert($table) {
        $this->reset();
        if(empty($table)):
            debug::error("sql", "TABLE argument must be valid in INSERT method.", __FILE__, __LINE__);
        endif;
        $table_name               = DBPREF.$table;
        $this->prepare_request   .= ' INSERT INTO '.$table_name;
        $this->content['insert']  = true;
        $this->table['set']       = $table;
        return $this;
    }

    public function delete($table) {
        $this->reset();
        if(empty($table)):
            debug::error("sql", "TABLE argument must be valid in DELETE method.", __FILE__, __LINE__);
        endif;
        $table_name               = DBPREF.$table;
        $this->prepare_request   .= ' DELETE FROM '.$table_name;
        $this->content['delete']  = true;
        $this->table['delete']    = $table;
        return $this;
    }

    public function set($field, $value = '') {
        if(empty($field)):
            debug::error("sql", "FIELD argument must be valid in SET method.", __FILE__, __LINE__);
        endif;
        if($this->content['select']):
            debug::error("sql", "SET method can't be requested with the SELECT method.", __FILE__, __LINE__);
        endif;
        if($this->content['update']):
            if($this->content['set']):
                $this->prepare_request .= ', ';
            else:
                $this->prepare_request .= ' SET';
            endif;
            if(preg_match("#^\+([0-9]{1,11})$#", $value)):
                $this->prepare_request .= ' '.$this->table['set'].'_'.$field.' = '.$this->table['set'].'_'.$field.' + '.parseInt($value).'';
            else:
                $this->prepare_request .= ' '.$this->table['set'].'_'.$field.' = "'.mysql_real_escape_string($value).'"';
            endif;
            $this->content['set']   = true;
        elseif($this->content['insert']):
            $this->fields[]       = $this->table['set'].'_'.$field;
            $this->values[]       = mysql_real_escape_string($value);
            $this->content['set'] = true;
        else:
            debug::error("sql", "SET method can't be requested before UPDATE method.", __FILE__, __LINE__);
        endif;
        return $this;
    }

    public function from($table) {
        if(empty($table)):
            debug::error("sql", "TABLE argument must be valid in FROM method.", __FILE__, __LINE__);
        endif;
        if(!$this->content['select']):
            debug::error("sql", "FROM method can't be requested before SELECT method.", __FILE__, __LINE__);
        endif;
        if($this->content['from']):
            debug::error("sql", "FROM method has been already requested.", __FILE__, __LINE__);
        endif;
        $this->table['select']  = $table;
        if(count($this->fields)==0):
            $this->prepare_request  .= ' SELECT * ';
        else:
            $this->prepare_request  .= ' SELECT ';
            $array = array();
            foreach($this->fields as $field):
               $array[] = $this->getField($field);
            endforeach;
            $this->prepare_request .= implode(', ', $array);
        endif;
        $table_name             = DBPREF.$table;
        $this->prepare_request .= ' FROM '.$table_name;
        $this->content['from']  = true;
        return $this;
    }

    public function leftJoin($table) {
        if(empty($table)):
            debug::error("sql", "TABLE argument must be valid in LEFT JOIN method.", __FILE__, __LINE__);
        endif;
        if(!$this->content['select']):
            debug::error("sql", "LEFT JOIN method can't be requested before SELECT method.", __FILE__, __LINE__);
        endif;
        if(!$this->content['from']):
            debug::error("sql", "LEFT JOIN method can't be requested before FROM method.", __FILE__, __LINE__);
        endif;
        $table_name                 = DBPREF.$table;
        $this->prepare_request     .= ' LEFT JOIN '.$table_name;
        $this->content['leftJoin']  = true;
        $this->table['leftJoin'][]  = $table;
        return $this;
    }

    public function on($value1, $value2) {

        $this->content['countOn']++;
        
        if(empty($value1)):
            debug::error("sql", "VALUE1 argument must be valid in ON method.", __FILE__, __LINE__);
        endif;
        if(empty($value2)):
            debug::error("sql", "VALUE2 argument must be valid in ON method.", __FILE__, __LINE__);
        endif;
        if(!$this->content['select']):
            debug::error("sql", "ON method can't be requested before SELECT method.", __FILE__, __LINE__);
        endif;
        if(!$this->content['from']):
            debug::error("sql", "ON method can't be requested before FROM method.", __FILE__, __LINE__);
        endif;
        if(($this->content['countOn'] > count($this->table['leftJoin']))):
            debug::error("sql", "ON method can't be requested before LEFT JOIN method.", __FILE__, __LINE__);
        endif;
        
        if(is_string($value1) && is_string($value2)):
            // On gère la rétrocompatibilité
            if(empty($value3)):
                $value3 = $this->table['select'];
            endif;
            $this->prepare_request .= ' ON '.DBPREF.$value3.'.'.$value3.'_'.$value1.' = '.DBPREF.$this->table['leftJoin'][count($this->table['leftJoin'])-1].'.'.$this->table['leftJoin'][count($this->table['leftJoin'])-1].'_'.$value2;
        else:
            $this->prepare_request .= ' ON '.$this->getField($value1).' = '.$this->getField($value2);
        endif;
        $this->content['on']    = true;
        return $this;
    }

    public function where($field, $calculator, $value, $calcul = "where") {
        if(!$this->content['select'] && !$this->content['update'] && !$this->content['delete']):
            debug::error("sql", $calcul." method can't be requested before SELECT, DELETE or UPDATE method.", __FILE__, __LINE__);
        endif;
        if(!$this->content['from'] && !$this->content['update'] && !$this->content['delete']):
            debug::error("sql", $calcul." method can't be requested before FROM or UPDATE method.", __FILE__, __LINE__);
        endif;
        if($this->content['leftJoin'] && !$this->content['on']):
            debug::error("sql", $calcul." method can't be requested before ON method when LEFT JOIN method has been requested.", __FILE__, __LINE__);
        endif;
        if($this->content['orderBy']):
            debug::error("sql", $calcul." method can't be requested after ORDER BY method.", __FILE__, __LINE__);
        endif;
        if($this->content['groupBy']):
            debug::error("sql", $calcul." method can't be requested after GROUP BY method.", __FILE__, __LINE__);
        endif;
        if($this->content['limit']):
            debug::error("sql", $calcul." method can't be requested after LIMIT method.", __FILE__, __LINE__);
        endif;
        if($this->content['where']):
            if(strtolower($calcul) == 'where'):
                $this->prepare_request .= ' AND';
            else:
                $this->prepare_request .= ' OR';
            endif;
        else:
            $this->content['where'] = true;
            $this->prepare_request .= ' WHERE';
        endif;

        $field = $this->getField($field); 

        if(is_array($value)):
            if(array_key_exists('FUNCTION', $value)):
                $this->prepare_request .= ' '.$field.' '.$calculator.' '.$value['FUNCTION'].'()';
            else:
                $this->prepare_request .= ' '.$field.' '.$calculator.' ("'.implode('", "', $value).'")';
            endif;
        elseif(is_object($value)):
            $name = get_class($value);
            if($name == "query"):
                $this->prepare_request .= ' '.$field.' '.$calculator.' ( '.$value->getRequest().')';
            else:
                $this->prepare_request .= ' '.$field.' '.$calculator.' "'.mysql_real_escape_string($value).'"';
            endif;
        else:
            if(is_numeric($value)):
                $this->prepare_request .= ' '.$field.' '.$calculator.' '.mysql_real_escape_string($value).'';
            else:
                $this->prepare_request .= ' '.$field.' '.$calculator.' "'.mysql_real_escape_string($value).'"';
            endif;
        endif;

        return $this;
    }

    public function ou($field, $calculator, $value) {
        $this->where($field, $calculator, $value, 'OR');
        return $this;
    }

    public function onDuplicateKeyUpdate($field, $value) {
        if(!$this->content['insert']):
            debug::error("sql", "ON DUPLICATE KEY UPDATE method can't be requested before INSERT method.", __FILE__, __LINE__);
        endif;
        if(!$this->content['set']):
            debug::error("sql", "ON DUPLICATE KEY UPDATE method can't be requested before SET method.", __FILE__, __LINE__);
        endif;
        if($this->content['onDuplicateKeyUpdate']):
            $this->duplicate .= ',';
        else:
            $this->content['onDuplicateKeyUpdate'] = true;
            $this->duplicate = 'ON DUPLICATE KEY UPDATE';
        endif;
        $this->duplicate .= ' '.$this->getField($field).' = "'.mysql_real_escape_string($value).'"';
        return $this;
    }

    public function orderBy($field = "", $order = 'ASC') {
        $order = strtoupper($order);
        if(!$this->content['select']):
            debug::error("sql", "ORDER BY method can't be requested before SELECT method.", __FILE__, __LINE__);
        endif;
        if(!$this->content['from']):
            debug::error("sql", "ORDER BY method can't be requested before FROM method.", __FILE__, __LINE__);
        endif;
        if(($this->content['countOn'] > count($this->table['leftJoin']))):
            debug::error("sql", "ORDER BY method can't be requested before ON method when LEFT JOIN method has been requested.", __FILE__, __LINE__);
        endif;
        if($order != 'ASC' && $order != 'DESC'):
            debug::error("sql", "ORDER BY method only accept blank, DESC or ASC for second argument.", __FILE__, __LINE__);
        endif;
        if($this->content['limit']):
            debug::error("sql", "ORDER BY method can't be requested after LIMIT method.", __FILE__, __LINE__);
        endif;
        if(!$this->content['orderBy']):
            $this->prepare_request .= ' ORDER BY';
            $this->content['orderBy'] = true;
        else:
            $this->prepare_request .= ' ,';
        endif;
        if(empty($field)):
            $field = "RAND()";
        else:
            $field = $this->getField($field);
        endif;
        
        $this->prepare_request .= ' '.$field.' '.$order.'';
        
        return $this;
    }

    public function groupBy($field) {
        if(!$this->content['select']):
            debug::error("sql", "GROUP BY method can't be requested before SELECT method.", __FILE__, __LINE__);
        endif;
        if(!$this->content['from']):
            debug::error("sql", "GROUP BY method can't be requested before FROM method.", __FILE__, __LINE__);
        endif;
        if(($this->content['countOn'] > count($this->table['leftJoin']))):
            debug::error("sql", "GROUP BY method can't be requested before ON method when LEFT JOIN method has been requested.", __FILE__, __LINE__);
        endif;
        if($this->content['limit']):
            debug::error("sql", "GROUP BY method can't be requested after LIMIT method.", __FILE__, __LINE__);
        endif;
        if(!$this->content['groupBy']):
            $this->prepare_request .= ' GROUP BY ';
            $this->content['groupBy'] = true;
        else:
            $this->prepare_request .= ', ';
        endif;
        $this->prepare_request .= $this->getField($field);
        return $this;
    }

    public function limit($limit1, $limit2 = false) {
        if(!$this->content['select']):
            debug::error("sql", "LIMIT method can't be requested before SELECT method.", __FILE__, __LINE__);
        endif;
        if(!$this->content['from']):
            debug::error("sql", "LIMIT method can't be requested before FROM method.", __FILE__, __LINE__);
        endif;
        if(($this->content['countOn'] > count($this->table['leftJoin']))):
            debug::error("sql", "LIMIT method can't be requested before ON method when LEFT JOIN method has been requested.", __FILE__, __LINE__);
        endif;
        if(empty($limit2)):
            $this->prepare_request .= ' LIMIT '.$limit1;
        else:
            $this->prepare_request .= ' LIMIT '.$limit1.', '.$limit2;
        endif;
        $this->content['limit'] = true;
        return $this;
    }

    public function exec($which = "ALL") {
        if(($this->content['select'] && $this->content['update'] && $this->content['insert'])
        || ($this->content['update'] && $this->content['insert'])
        || ($this->content['select'] && $this->content['update'])
        || ($this->content['select'] && $this->content['insert'])):
            debug::error("sql", "SELECT, UPDATE and INSERT methods can't be requested in the same time.", __FILE__, __LINE__);
        endif;

        if($this->content['select']):

            if(!$this->content['from']):
                debug::error("sql", "EXEC method can't be requested before FROM method.", __FILE__, __LINE__);
            endif;
            $which        = strtoupper($which);
            $this->result = $this->sql($this->prepare_request);
            $this->which  = $which;

            if(mysql_num_rows($this->result)=='0'):
                return false;
            endif;

            if($this->which != 'ALL' && $this->which != 'FIRST'):
                debug::error("sql", 'EXEC method only accept blank, "ALL" or "FIRST" for argument.', __FILE__, __LINE__);
            endif;
            if($this->which == "FIRST"):
                $this->line = mysql_fetch_assoc($this->result);
            endif;

            return $this->result;
        elseif($this->content['update']):
            if(!$this->content['set']):
                debug::error("sql", "EXEC method can't be requested before SET method.", __FILE__, __LINE__);
            endif;
            $query = $this->prepare_request;
            return $this->sql($query);
        elseif($this->content['insert']):
            if(!$this->content['set']):
                debug::error("sql", "EXEC method can't be requested before SET method.", __FILE__, __LINE__);
            endif;
            $field = implode(',', $this->fields);
            $value = implode('", "', $this->values);
            $this->prepare_request .= ' ('.$field.') VALUES ("'.$value.'")';
            if($this->content['onDuplicateKeyUpdate']):
                $this->prepare_request .= ' '.$this->duplicate;
            endif;
            $this->sql($this->prepare_request);
            return mysql_insert_id();
        elseif($this->content['delete']):
            $query = $this->prepare_request;
            return $this->sql($query);
        else:
            debug::error("sql", "EXEC method can't be requested before SELECT, UPDATE or INSERT method.", __FILE__, __LINE__);
        endif;
    }

    public function sql($req) {
        if(DBHOST):
            if(isset(page::$sql)):
                page::$sql++;
            endif;

            $return = mysql_query($req) or debug::error("sql", mysql_error()."<br />".$req, __FILE__, __LINE__);
            if($this->content['select']):
                $this->count = mysql_num_rows($return);
            endif;
            return $return;
        else:
            return false;
        endif;
    }

    public function next() {
        if(empty($this->result)):
            debug::error("sql", "NEXT method can't be requested before SELECT and EXEC method.", __FILE__, __LINE__);
            return false;
        endif;
        if($this->which == "FIRST"):
            debug::error("sql", "NEXT method can't be requested with FIRST as argument for EXEC method.", __FILE__, __LINE__);
            return false;
        endif;

        if(!$this->ok()):
            return false;
        endif;

        $this->line = mysql_fetch_assoc($this->result);
        
        return $this->line;

    }

    private function getRequest() {
        return $this->prepare_request;
    }

    public function get($field = '', $table='') {
        if(empty($this->result)):
            debug::error("sql", "GET method can't be requested before SELECT and EXEC method.", __FILE__, __LINE__);
        endif;
        if(empty($table)):
            $table = $this->table['select'];
        endif;
        if(!empty($field)):
            if(isset($this->line[$table.'_'.$field])):
                return stripslashes($this->line[$table.'_'.$field]);
            elseif(in_array($field, $this->alias)):
                return $this->line[$field];
            else:
                return false;
            endif;
        else:
            if(is_array($this->line) && count($this->line)!=0):
                $array = array();
                foreach($this->line as $ligne => $value):
                    $key = str_replace($this->table['select'].'_', '', $ligne);
                    if ($this->content['leftJoin']):
                        $underscore = '';
                        foreach ($this->table['leftJoin'] as $table):
                            $underscore .= "_";
                            $key = str_replace($table.'_', $underscore, $key);
                        endforeach;
                    endif;
                    if(!is_string($value)):
                        $array[$key] = $value;
                    else:
                        $array[$key] = stripslashes($value);
                    endif;
                endforeach;
                if(count($array) == 0):
                    return false;
                else:
                    return $array;
                endif;
            else:
                return false;
            endif;
        endif;
    }

    public function put($field, $value='', $table='') {
        if(empty($this->result)):
            debug::error("sql", "PUT method can't be requested before SELECT and EXEC method.", __FILE__, __LINE__);
        endif;
        if(empty($table)):
            $table = $this->table['select'];
        endif;
        $this->line[$this->table['select'].'_'.$field] = $value;
        return true;
    }

    public function count() {
        if(is_int($this->count)):
            return $this->count;
        else:
            return false;
        endif;
    }

    public function ok() {
        if(is_int($this->count) && $this->count > 0):
            return true;
        else:
            return false;
        endif;
    }

    public function getArray() {
        $array = array();
        while($this->next()):
            array_push($array, $this->get());
        endwhile;
        return $array;
    }

    public function addString($string) {
        $this->prepare_request .= $string;
        return $this;
    }
    
    public function page($get, $results, $variable = "pagination") {
        global $template;
        
        $nb = mysql_num_rows(mysql_query($this->prepare_request));
        $current = get($get);
        if($current == "index"):
            $current = 1;
        elseif($current-1 > ($nb/$results)):
            $current = 1;
        endif;
        $place = ($current-1)*$results;
        $this->Limit($place, $results);
        
        $text['page']  = $current;
        $text['total'] = ceil($nb / $results);
        
        if($current > 1):
            $start = '<a href="'.get($get, 1)         .'" class="start">'.lang::text('pagination:start').'</a>';
            $prev  = '<a href="'.get($get, $current-1).'" class="prev">'. lang::text('pagination:prev') .'</a>';
        else:
            $start = '';
            $prev  = '';
        endif;
        
        if($current < ceil($nb / $results)):
            $next  = '<a href="'.get($get, $current+1)          .'" class="next">'. lang::text('pagination:next') .'</a>';
            $end   = '<a href="'.get($get, ceil($nb / $results)).'" class="end">'.  lang::text('pagination:end')  .'</a>';
        else:
            $end   = '';
            $next  = '';
        endif;

        $template->assign($variable, '<div class="pagination">'.$start.$prev.lang::text('pagination:page', $text).$end.$next.'</div>');
        
        return $this;
    }

    public function debug($force = false) {
        $debug = array();
        $debug['request'] = $this->prepare_request;
        $debug['results'] = $this->count;
        
        if(!DEV):
            echo '<!-- DEBUG ';
            print_r($debug);
            echo '-->';
        else:
            if(!$force):
                debug::display($debug);
            else:
                echo '<pre>';
                print_r($debug);
                echo '</pre>';
            endif;
        endif;
    }

    public function reset() {
        $this->content['select']               = false;
        $this->content['from']                 = false;
        $this->content['where']                = false;
        $this->content['limit']                = false;
        $this->content['orderBy']              = false;
        $this->content['groupBy']              = false;
        $this->content['leftJoin']             = false;
        $this->content['on']                   = false;
        $this->content['select']               = false;
        $this->content['delete']               = false;
        $this->content['insert']               = false;
        $this->content['set']                  = false;
        $this->content['update']               = false;
        $this->content['onDuplicateKeyUpdate'] = false;
        $this->content['countOn']          = 0;

        if(!isset($this->table['select'])):
            $this->table['select']   = '';
        endif;

        $this->table['leftJoin'] = array();
        $this->table['set']      = '';
        $this->table['delete']   = '';
        $this->line              = array();
        $this->prepare_request   = '';
        $this->count             = 0;
        $this->duplicate         = '';
    }
}
?>