<?php

class query {
    public
        $prepare_request = '',
        $result          = '',
        $values          = '',
        $table           = '',
        $duplicate       = '',
        $which           = 'all',
        $line            = array(),
        $content         = array(),
        $fields          = array();
		
    public function __construct() {
        $this->reset();
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
            debug::html(lang::error('sql'), "TABLE argument must be valid in INSERT method.", __FILE__, __LINE__);
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
            debug::html(lang::error('sql'), "TABLE argument must be valid in DELETE method.", __FILE__, __LINE__);
        endif;
        $table_name               = DBPREF.$table;
        $this->prepare_request   .= ' DELETE FROM '.$table_name;
        $this->content['delete']  = true;
        $this->table['delete']    = $table;
        return $this;
    }

    public function set($field, $value = '') {
        if(empty($field)):
            debug::html(lang::error('sql'), "FIELD argument must be valid in SET method.", __FILE__, __LINE__);
        endif;
        if($this->content['select']):
            debug::html(lang::error('sql'), "SET method can't be requested with the SELECT method.", __FILE__, __LINE__);
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
            debug::html(lang::error('sql'), "SET method can't be requested before UPDATE method.", __FILE__, __LINE__);
        endif;
        return $this;
    }

    public function from($table) {
        if(empty($table)):
            debug::html(lang::error('sql'), "TABLE argument must be valid in FROM method.", __FILE__, __LINE__);
        endif;
        if(!$this->content['select']):
            debug::html(lang::error('sql'), "FROM method can't be requested before SELECT method.", __FILE__, __LINE__);
        endif;
        if($this->content['from']):
            debug::html(lang::error('sql'), "FROM method has been already requested.", __FILE__, __LINE__);
        endif;
        if(count($this->fields)==0 || $this->fields[0] == '*'):
            $this->prepare_request  .= ' SELECT * ';
        else:
            $this->prepare_request  .= ' SELECT '.$table.'_'.implode(', '.$table.'_', $this->fields);
        endif;
        $table_name             = DBPREF.$table;
        $this->prepare_request .= ' FROM '.$table_name;
        $this->content['from']  = true;
        $this->table['select']  = $table;
        return $this;
    }

    public function leftJoin($table) {
        if(empty($table)):
            debug::html(lang::error('sql'), "TABLE argument must be valid in LEFT JOIN method.", __FILE__, __LINE__);
        endif;
        if(!$this->content['select']):
            debug::html(lang::error('sql'), "LEFT JOIN method can't be requested before SELECT method.", __FILE__, __LINE__);
        endif;
        if(!$this->content['from']):
            debug::html(lang::error('sql'), "LEFT JOIN method can't be requested before FROM method.", __FILE__, __LINE__);
        endif;
        $table_name                 = DBPREF.$table;
        $this->prepare_request     .= ' LEFT JOIN '.$table_name;
        $this->content['leftJoin']  = true;
        $this->table['leftJoin'][]  = $table;
        return $this;
    }

    public function on($value1, $value2, $value3 = '') {
		
        $this->content['countOn']++;
        
        if(empty($value1)):
            debug::html(lang::error('sql'), "VALUE1 argument must be valid in ON method.", __FILE__, __LINE__);
        endif;
        if(empty($value2)):
            debug::html(lang::error('sql'), "VALUE2 argument must be valid in ON method.", __FILE__, __LINE__);
        endif;
        if(!$this->content['select']):
            debug::html(lang::error('sql'), "ON method can't be requested before SELECT method.", __FILE__, __LINE__);
        endif;
        if(!$this->content['from']):
            debug::html(lang::error('sql'), "ON method can't be requested before FROM method.", __FILE__, __LINE__);
        endif;
        if(($this->content['countOn'] > count($this->table['leftJoin']))):
            debug::html(lang::error('sql'), "ON method can't be requested before LEFT JOIN method.", __FILE__, __LINE__);
        endif;
        if(empty($value3)):
            $value3 = $this->table['select'];
        endif;
        $this->prepare_request .= ' ON '.DBPREF.$value3.'.'.$value3.'_'.$value1.' = '.DBPREF.$this->table['leftJoin'][count($this->table['leftJoin'])-1].'.'.$this->table['leftJoin'][count($this->table['leftJoin'])-1].'_'.$value2;
        $this->content['on']    = true;
        return $this;
    }

    public function where($field, $calculator, $value, $table = '', $calcul = "where") {
        if(!$this->content['select'] && !$this->content['update'] && !$this->content['delete']):
            debug::html(lang::error('sql'), $calcul." method can't be requested before SELECT, DELETE or UPDATE method.", __FILE__, __LINE__);
        endif;
        if(!$this->content['from'] && !$this->content['update'] && !$this->content['delete']):
            debug::html(lang::error('sql'), $calcul." method can't be requested before FROM or UPDATE method.", __FILE__, __LINE__);
        endif;
        if($this->content['leftJoin'] && !$this->content['on']):
            debug::html(lang::error('sql'), $calcul." method can't be requested before ON method when LEFT JOIN method has been requested.", __FILE__, __LINE__);
        endif;
        if($this->content['orderBy']):
            debug::html(lang::error('sql'), $calcul." method can't be requested after ORDER BY method.", __FILE__, __LINE__);
        endif;
        if($this->content['groupBy']):
            debug::html(lang::error('sql'), $calcul." method can't be requested after GROUP BY method.", __FILE__, __LINE__);
        endif;
        if($this->content['limit']):
            debug::html(lang::error('sql'), $calcul." method can't be requested after LIMIT method.", __FILE__, __LINE__);
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
        if($this->content['leftJoin']):
            if(empty($table)):
                 $table = $this->table['select'];
            endif;
            $field = DBPREF.$table.'.'.$table.'_'.$field;
        else:
            if($this->content['select']):
                $table = $this->table['select'];
            elseif($this->content['delete']):
                $table = $this->table['delete'];
            else:
                $table = $this->table['set'];
            endif;
            $field = $table.'_'.$field;
        endif;
        
        if(($calculator == "IN") || ($calculator == "NOT IN")):
            if(is_array($value)):
                $this->prepare_request .= ' '.$field.' '.$calculator.' ("'.implode('", "', $value).'")';
            elseif(is_object($value)):
                $this->prepare_request .= ' '.$field.' '.$calculator.' ( '.$value->getRequest().')';
            else:
                $this->prepare_request .= ' '.$field.' = "'.mysql_real_escape_string($value).'"';
            endif;
        else:
            $this->prepare_request .= ' '.$field.' '.$calculator.' "'.mysql_real_escape_string($value).'"';
        endif;
        return $this;
    }

    public function ou($field, $calculator, $value, $table = '') {
        $this->where($field, $calculator, $value, $table, 'OR');
        return $this;
    }

    public function onDuplicateKeyUpdate($field, $value) {
        if(!$this->content['insert']):
            debug::html(lang::error('sql'), "ON DUPLICATE KEY UPDATE method can't be requested before INSERT method.", __FILE__, __LINE__);
        endif;
        if(!$this->content['set']):
            debug::html(lang::error('sql'), "ON DUPLICATE KEY UPDATE method can't be requested before SET method.", __FILE__, __LINE__);
        endif;
        if($this->content['onDuplicateKeyUpdate']):
            $this->duplicate .= ',';
        else:
            $this->content['onDuplicateKeyUpdate'] = true;
            $this->duplicate = 'ON DUPLICATE KEY UPDATE';
        endif;
        $this->duplicate .= ' '.$this->table['set'].'_'.$field.' = "'.mysql_real_escape_string($value).'"';
        return $this;
    }

    public function drderBy($field = "", $order = 'ASC', $table = '') {
        $order = strtoupper($order);
        if(!$this->content['select']):
            debug::html(lang::error('sql'), "ORDER BY method can't be requested before SELECT method.", __FILE__, __LINE__);
        endif;
        if(!$this->content['from']):
            debug::html(lang::error('sql'), "ORDER BY method can't be requested before FROM method.", __FILE__, __LINE__);
        endif;
        if(($this->content['countOn'] > count($this->table['leftJoin']))):
            debug::html(lang::error('sql'), "ORDER BY method can't be requested before ON method when LEFT JOIN method has been requested.", __FILE__, __LINE__);
        endif;
        if($order != 'ASC' && $order != 'DESC'):
            debug::html(lang::error('sql'), "ORDER BY method only accept blank, DESC or ASC for second argument.", __FILE__, __LINE__);
        endif;
        if($this->content['limit']):
            debug::html(lang::error('sql'), "ORDER BY method can't be requested after LIMIT method.", __FILE__, __LINE__);
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
            if($this->content['leftJoin']):
                if(empty($table)):
                    $table = $this->table['select'];
                endif;
                $field = DBPREF.$table.'.'.$table.'_'.$field;
            else:
                $field =  $this->table['select'].'_'.$field;
            endif;
        endif;
        if($order != "ASC" && $order != "DESC"):
            $this->prepare_request .= ' ABS('.$field.' - '.$order.')';
        else:
            $this->prepare_request .= ' '.$field.' '.$order.'';
        endif;
        return $this;
    }

    public function groupBy($field, $table = '') {
        if(!$this->content['select']):
            debug::html(lang::error('sql'), "GROUP BY method can't be requested before SELECT method.", __FILE__, __LINE__);
        endif;
        if(!$this->content['from']):
            debug::html(lang::error('sql'), "GROUP BY method can't be requested before FROM method.", __FILE__, __LINE__);
        endif;
        if(($this->content['countOn'] > count($this->table['LeftJoin']))):
            debug::html(lang::error('sql'), "GROUP BY method can't be requested before ON method when LEFT JOIN method has been requested.", __FILE__, __LINE__);
        endif;
        if($this->content['limit']):
            debug::html(lang::error('sql'), "GROUP BY method can't be requested after LIMIT method.", __FILE__, __LINE__);
        endif;
        if(!$this->content['groupBy']):
            $this->prepare_request .= ' GROUP BY ';
            $this->content['groupBy'] = true;
        else:
            $this->prepare_request .= ', ';
        endif;
        if($this->content['leftJoin']):
            if(empty($table)):
                debug::html(lang::error('sql'), "TABLE argument is require in the GROUP BY method after using the LEFT JOIN method.", __FILE__, __LINE__);
            endif;
            $field = DBPREF.$table.'.'.$table.'_'.$field;
        else:
            $field =  $this->table['select'].'_'.$field;
        endif;
        $this->prepare_request .= $field;
        return $this;
    }

    public function limit($limit1, $limit2 = false) {
        if(!$this->content['select']):
            debug::html(lang::error('sql'), "LIMIT method can't be requested before SELECT method.", __FILE__, __LINE__);
        endif;
        if(!$this->content['from']):
            debug::html(lang::error('sql'), "LIMIT method can't be requested before FROM method.", __FILE__, __LINE__);
        endif;
        if(($this->content['countOn'] > count($this->table['LeftJoin']))):
            debug::html(lang::error('sql'), "LIMIT method can't be requested before ON method when LEFT JOIN method has been requested.", __FILE__, __LINE__);
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
            debug::html(lang::error('sql'), "SELECT, UPDATE and INSERT methods can't be requested in the same time.", __FILE__, __LINE__);
        endif;

        if($this->content['select']):

            if(!$this->content['from']):
                debug::html(lang::error('sql'), "EXEC method can't be requested before FROM method.", __FILE__, __LINE__);
            endif;
            $which        = strtoupper($which);
            $this->result = $this->sql($this->prepare_request);
            $this->which  = $which;

            if(mysql_num_rows($this->result)=='0'):
                return false;
            endif;

            if($this->which != 'ALL' && $this->which != 'FIRST'):
                debug::html(lang::error('sql'), 'EXEC method only accept blank, "ALL" or "FIRST" for argument.', __FILE__, __LINE__);
            endif;
            if($this->which == "FIRST"):
                $this->line = mysql_fetch_assoc($this->result);
            endif;

            return $this->result;
        elseif($this->content['update']):
            if(!$this->content['set']):
                debug::html(lang::error('sql'), "EXEC method can't be requested before SET method.", __FILE__, __LINE__);
            endif;
            $query = $this->prepare_request;
            return $this->sql($query);
        elseif($this->content['insert']):
            if(!$this->content['set']):
                debug::html(lang::error('sql'), "EXEC method can't be requested before SET method.", __FILE__, __LINE__);
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
            debug::html(lang::error('sql'), "EXEC method can't be requested before SELECT, UPDATE or INSERT method.", __FILE__, __LINE__);
        endif;
    }

    public function sql($req) {
        if(DBHOST):
            if(isset(page::$sql)):
                page::$sql++;
            endif;

            $return = mysql_query($req) or debug::html(lang::error('sql'), mysql_error()."<br />".$req, __FILE__, __LINE__);
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
            debug::html(lang::error('sql'), "NEXT method can't be requested before SELECT and EXEC method.", __FILE__, __LINE__);
            return false;
        endif;
        if($this->which == "FIRST"):
            debug::html(lang::error('sql'), "NEXT method can't be requested with FIRST as argument for EXEC method.", __FILE__, __LINE__);
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
            debug::html(lang::error('sql'), "GET method can't be requested before SELECT and EXEC method.", __FILE__, __LINE__);
        endif;
        if(empty($table)):
            $table = $this->table['select'];
        endif;
        if(!empty($field)):
            if(isset($this->line[$table.'_'.$field])):
                return stripslashes($this->line[$table.'_'.$field]);
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
            debug::html(lang::error('sql'), "PUT method can't be requested before SELECT and EXEC method.", __FILE__, __LINE__);
        endif;
        if(empty($table)):
            $table = $this->table['select'];
        endif;
        $this->line[$this->table['select'].'_'.$field] = $value;
        return true;
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
    
    public function page($get, $results, $variable = false) {
        global $smarty;
        
        if(!$variable):
            $variable = "pagination";
        endif;
        
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
            $next  = '';
        endif;
        
        if($current < ceil($nb / $results)):
            $next  = '<a href="'.get($get, $current+1)          .'" class="next">'. lang::text('pagination:next') .'</a>';
            $end   = '<a href="'.get($get, ceil($nb / $results)).'" class="end">'.  lang::text('pagination:end')  .'</a>';
        else:
            $end   = '';
            $next  = '';
        endif;

        $smarty->append($variable, '<div class="pagination">'.$start.$prev.lang::text('pagination:page', $text).$end.$next.'</div>');
        
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
        $this->content['countOn']	       = 0;

        if(!isset($this->table['select'])):
            $this->table['select']   = '';
        endif;

        $this->table['leftJoin'] = '';
        $this->table['set']      = '';
        $this->table['delete']   = '';
        $this->line              = array();
        $this->prepare_request   = '';
        $this->count             = 0;
        $this->duplicate         = '';
    }

}
?>