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

    public function Select() {
        $this->reset();
        $this->fields = func_get_args();
        $this->content['Select'] = true;
        return $this;
    }

    public function Update($table) {
        $this->reset();
        $table_name                = DBPREF.$table;
        $this->prepare_request    .= ' UPDATE '.$table_name;
        $this->content['Update']   = true;
        $this->table['Set']        = $table;
        return $this;
    }

    public function Insert($table) {
        $this->reset();
        if(empty($table)):
            debug::html(lang::error('sql'), "TABLE argument must be valid in INSERT method.", __FILE__, __LINE__);
        endif;
        $table_name               = DBPREF.$table;
        $this->prepare_request   .= ' INSERT INTO '.$table_name;
        $this->content['Insert']  = true;
        $this->table['Set']       = $table;
        return $this;
    }

    public function Delete($table) {
        $this->reset();
        if(empty($table)):
            debug::html(lang::error('sql'), "TABLE argument must be valid in DELETE method.", __FILE__, __LINE__);
        endif;
        $table_name               = DBPREF.$table;
        $this->prepare_request   .= ' DELETE FROM '.$table_name;
        $this->content['Delete']  = true;
        $this->table['Delete']    = $table;
        return $this;
    }

    public function Set($field, $value = '') {
        if(empty($field)):
            debug::html(lang::error('sql'), "FIELD argument must be valid in SET method.", __FILE__, __LINE__);
        endif;
        if($this->content['Select']):
            debug::html(lang::error('sql'), "SET method can't be requested with the SELECT method.", __FILE__, __LINE__);
        endif;
        if($this->content['Update']):
            if($this->content['Set']):
                $this->prepare_request .= ', ';
            else:
                $this->prepare_request .= ' SET';
            endif;
            if(preg_match("#^\+([0-9]{1,11})$#", $value)):
                $this->prepare_request .= ' '.$this->table['Set'].'_'.$field.' = '.$this->table['Set'].'_'.$field.' + '.parseInt($value).'';
            else:
                $this->prepare_request .= ' '.$this->table['Set'].'_'.$field.' = "'.mysql_real_escape_string($value).'"';
            endif;
            $this->content['Set']   = true;
        elseif($this->content['Insert']):
            $this->fields[]       = $this->table['Set'].'_'.$field;
            $this->values[]       = mysql_real_escape_string($value);
            $this->content['Set'] = true;
        else:
            debug::html(lang::error('sql'), "SET method can't be requested before UPDATE method.", __FILE__, __LINE__);
        endif;
        return $this;
    }

    public function From($table) {
        if(empty($table)):
            debug::html(lang::error('sql'), "TABLE argument must be valid in FROM method.", __FILE__, __LINE__);
        endif;
        if(!$this->content['Select']):
            debug::html(lang::error('sql'), "FROM method can't be requested before SELECT method.", __FILE__, __LINE__);
        endif;
        if($this->content['From']):
            debug::html(lang::error('sql'), "FROM method has been already requested.", __FILE__, __LINE__);
        endif;
        if(count($this->fields)==0 || $this->fields[0] == '*'):
            $this->prepare_request  .= ' SELECT * ';
        else:
            $this->prepare_request  .= ' SELECT '.$table.'_'.implode(', '.$table.'_', $this->fields);
        endif;
        $table_name             = DBPREF.$table;
        $this->prepare_request .= ' FROM '.$table_name;
        $this->content['From']  = true;
        $this->table['Select']  = $table;
        return $this;
    }

    public function LeftJoin($table) {
        if(empty($table)):
            debug::html(lang::error('sql'), "TABLE argument must be valid in LEFT JOIN method.", __FILE__, __LINE__);
        endif;
        if(!$this->content['Select']):
            debug::html(lang::error('sql'), "LEFT JOIN method can't be requested before SELECT method.", __FILE__, __LINE__);
        endif;
        if(!$this->content['From']):
            debug::html(lang::error('sql'), "LEFT JOIN method can't be requested before FROM method.", __FILE__, __LINE__);
        endif;
        $table_name                 = DBPREF.$table;
        $this->prepare_request     .= ' LEFT JOIN '.$table_name;
        $this->content['LeftJoin']  = true;
        $this->table['LeftJoin'][]  = $table;
        return $this;
    }

    public function On($value1, $value2, $value3 = '') {
		
        $this->content['CountOn']++;
        
        if(empty($value1)):
            debug::html(lang::error('sql'), "VALUE1 argument must be valid in ON method.", __FILE__, __LINE__);
        endif;
        if(empty($value2)):
            debug::html(lang::error('sql'), "VALUE2 argument must be valid in ON method.", __FILE__, __LINE__);
        endif;
        if(!$this->content['Select']):
            debug::html(lang::error('sql'), "ON method can't be requested before SELECT method.", __FILE__, __LINE__);
        endif;
        if(!$this->content['From']):
            debug::html(lang::error('sql'), "ON method can't be requested before FROM method.", __FILE__, __LINE__);
        endif;
        if(($this->content['CountOn'] > count($this->table['LeftJoin']))):
            debug::html(lang::error('sql'), "ON method can't be requested before LEFT JOIN method.", __FILE__, __LINE__);
        endif;
        if(empty($value3)):
            $value3 = $this->table['Select'];
        endif;
        $this->prepare_request .= ' ON '.DBPREF.$value3.'.'.$value3.'_'.$value1.' = '.DBPREF.$this->table['LeftJoin'][count($this->table['LeftJoin'])-1].'.'.$this->table['LeftJoin'][count($this->table['LeftJoin'])-1].'_'.$value2;
        $this->content['On']    = true;
        return $this;
    }

    public function Union() {
        if(!$this->content['Select']):
            debug::html(lang::error('sql'), "UNION method can't be requested before SELECT method.", __FILE__, __LINE__);
        endif;
        if(!$this->content['From']):
            exit("Error SQL :.");
            debug::html(lang::error('sql'), "UNION method can't be requested before FROM method.", __FILE__, __LINE__);
        endif;
        if(($this->content['CountOn'] > count($this->table['LeftJoin']))):
            debug::html(lang::error('sql'), "UNION method can't be requested before ON method when LEFT JOIN method has been requested.", __FILE__, __LINE__);
        endif;
        $this->prepare_request .= ' UNION';
        $this->content          = false;
        return $this;
    }

    public function UnionAll() {
        if(!$this->content['Select']):
            debug::html(lang::error('sql'), "UNION ALL method can't be requested before SELECT method.", __FILE__, __LINE__);
        endif;
        if(!$this->content['From']):
            debug::html(lang::error('sql'), "UNION ALL method can't be requested before FROM method.", __FILE__, __LINE__);
        endif;
        if(($this->content['CountOn'] > count($this->table['LeftJoin']))):
            debug::html(lang::error('sql'), "UNION ALL method can't be requested before ON method when LEFT JOIN method has been requested.", __FILE__, __LINE__);
        endif;
        $this->prepare_request .= ' UNION ALL';
        $this->content          = false;
        return $this;
    }

    public function Where($field, $calculator, $value, $table = '', $calcul = "where") {
        if(!$this->content['Select'] && !$this->content['Update'] && !$this->content['Delete']):
            debug::html(lang::error('sql'), $calcul." method can't be requested before SELECT, DELETE or UPDATE method.", __FILE__, __LINE__);
        endif;
        if(!$this->content['From'] && !$this->content['Update'] && !$this->content['Delete']):
            debug::html(lang::error('sql'), $calcul." method can't be requested before FROM or UPDATE method.", __FILE__, __LINE__);
        endif;
        if($this->content['LeftJoin'] && !$this->content['On']):
            debug::html(lang::error('sql'), $calcul." method can't be requested before ON method when LEFT JOIN method has been requested.", __FILE__, __LINE__);
        endif;
        if($this->content['OrderBy']):
            debug::html(lang::error('sql'), $calcul." method can't be requested after ORDER BY method.", __FILE__, __LINE__);
        endif;
        if($this->content['GroupBy']):
            debug::html(lang::error('sql'), $calcul." method can't be requested after GROUP BY method.", __FILE__, __LINE__);
        endif;
        if($this->content['Limit']):
            debug::html(lang::error('sql'), $calcul." method can't be requested after LIMIT method.", __FILE__, __LINE__);
        endif;
        if($this->content['Where']):
            if(strtolower($calcul) == 'where'):
                $this->prepare_request .= ' AND';
            else:
                $this->prepare_request .= ' OR';
            endif;
        else:
            $this->content['Where'] = true;
            $this->prepare_request .= ' WHERE';
        endif;
        if($this->content['LeftJoin']):
            if(empty($table)):
                 $table = $this->table['Select'];
            endif;
            $field = DBPREF.$table.'.'.$table.'_'.$field;
        else:
            if($this->content['Select']):
                $table = $this->table['Select'];
            elseif($this->content['Delete']):
                $table = $this->table['Delete'];
            else:
                $table = $this->table['Set'];
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

    public function Ou($field, $calculator, $value, $table = '') {
        $this->Where($field, $calculator, $value, $table, 'OR');
        return $this;
    }

    public function OnDuplicateKeyUpdate($field, $value) {
        if(!$this->content['Insert']):
            debug::html(lang::error('sql'), "ON DUPLICATE KEY UPDATE method can't be requested before INSERT method.", __FILE__, __LINE__);
        endif;
        if(!$this->content['Set']):
            debug::html(lang::error('sql'), "ON DUPLICATE KEY UPDATE method can't be requested before SET method.", __FILE__, __LINE__);
        endif;
        if($this->content['OnDuplicateKeyUpdate']):
            $this->duplicate .= ',';
        else:
            $this->content['OnDuplicateKeyUpdate'] = true;
            $this->duplicate = 'ON DUPLICATE KEY UPDATE';
        endif;
        $this->duplicate .= ' '.$this->table['Set'].'_'.$field.' = "'.mysql_real_escape_string($value).'"';
        return $this;
    }

    public function OrderBy($field = "", $order = 'ASC', $table = '') {
        $order = strtoupper($order);
        if(!$this->content['Select']):
            debug::html(lang::error('sql'), "ORDER BY method can't be requested before SELECT method.", __FILE__, __LINE__);
        endif;
        if(!$this->content['From']):
            debug::html(lang::error('sql'), "ORDER BY method can't be requested before FROM method.", __FILE__, __LINE__);
        endif;
        if(($this->content['CountOn'] > count($this->table['LeftJoin']))):
            debug::html(lang::error('sql'), "ORDER BY method can't be requested before ON method when LEFT JOIN method has been requested.", __FILE__, __LINE__);
        endif;
        if($order != 'ASC' && $order != 'DESC'):
            debug::html(lang::error('sql'), "ORDER BY method only accept blank, DESC or ASC for second argument.", __FILE__, __LINE__);
        endif;
        if($this->content['Limit']):
            debug::html(lang::error('sql'), "ORDER BY method can't be requested after LIMIT method.", __FILE__, __LINE__);
        endif;
        if(!$this->content['OrderBy']):
            $this->prepare_request .= ' ORDER BY';
            $this->content['OrderBy'] = true;
        else:
            $this->prepare_request .= ' ,';
        endif;
        if(empty($field)):
            $field = "RAND()";
        else:
            if($this->content['LeftJoin']):
                if(empty($table)):
                    $table = $this->table['Select'];
                endif;
                $field = DBPREF.$table.'.'.$table.'_'.$field;
            else:
                $field =  $this->table['Select'].'_'.$field;
            endif;
        endif;
        if($order != "ASC" && $order != "DESC"):
            $this->prepare_request .= ' ABS('.$field.' - '.$order.')';
        else:
            $this->prepare_request .= ' '.$field.' '.$order.'';
        endif;
        return $this;
    }

    public function GroupBy($field, $table = '') {
        if(!$this->content['Select']):
            debug::html(lang::error('sql'), "GROUP BY method can't be requested before SELECT method.", __FILE__, __LINE__);
        endif;
        if(!$this->content['From']):
            debug::html(lang::error('sql'), "GROUP BY method can't be requested before FROM method.", __FILE__, __LINE__);
        endif;
        if(($this->content['CountOn'] > count($this->table['LeftJoin']))):
            debug::html(lang::error('sql'), "GROUP BY method can't be requested before ON method when LEFT JOIN method has been requested.", __FILE__, __LINE__);
        endif;
        if($this->content['Limit']):
            debug::html(lang::error('sql'), "GROUP BY method can't be requested after LIMIT method.", __FILE__, __LINE__);
        endif;
        if(!$this->content['GroupBy']):
            $this->prepare_request .= ' GROUP BY ';
            $this->content['GroupBy'] = true;
        else:
            $this->prepare_request .= ', ';
        endif;
        if($this->content['LeftJoin']):
            if(empty($table)):
                debug::html(lang::error('sql'), "TABLE argument is require in the GROUP BY method after using the LEFT JOIN method.", __FILE__, __LINE__);
            endif;
            $field = DBPREF.$table.'.'.$table.'_'.$field;
        else:
            $field =  $this->table['Select'].'_'.$field;
        endif;
        $this->prepare_request .= $field;
        return $this;
    }

    public function Limit($limit1, $limit2 = false) {
        if(!$this->content['Select']):
            debug::html(lang::error('sql'), "LIMIT method can't be requested before SELECT method.", __FILE__, __LINE__);
        endif;
        if(!$this->content['From']):
            debug::html(lang::error('sql'), "LIMIT method can't be requested before FROM method.", __FILE__, __LINE__);
        endif;
        if(($this->content['CountOn'] > count($this->table['LeftJoin']))):
            debug::html(lang::error('sql'), "LIMIT method can't be requested before ON method when LEFT JOIN method has been requested.", __FILE__, __LINE__);
        endif;
        if(empty($limit2)):
            $this->prepare_request .= ' LIMIT '.$limit1;
        else:
            $this->prepare_request .= ' LIMIT '.$limit1.', '.$limit2;
        endif;
        $this->content['Limit'] = true;
        return $this;
    }

    public function exec($which = "ALL") {
        if(($this->content['Select'] && $this->content['Update'] && $this->content['Insert'])
        || ($this->content['Update'] && $this->content['Insert'])
        || ($this->content['Select'] && $this->content['Update'])
        || ($this->content['Select'] && $this->content['Insert'])):
            debug::html(lang::error('sql'), "SELECT, UPDATE and INSERT methods can't be requested in the same time.", __FILE__, __LINE__);
        endif;

        if($this->content['Select']):

            if(!$this->content['From']):
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
        elseif($this->content['Update']):
            if(!$this->content['Set']):
                debug::html(lang::error('sql'), "EXEC method can't be requested before SET method.", __FILE__, __LINE__);
            endif;
            $query = $this->prepare_request;
            return $this->sql($query);
        elseif($this->content['Insert']):
            if(!$this->content['Set']):
                debug::html(lang::error('sql'), "EXEC method can't be requested before SET method.", __FILE__, __LINE__);
            endif;
            $field = implode(',', $this->fields);
            $value = implode('", "', $this->values);
            $this->prepare_request .= ' ('.$field.') VALUES ("'.$value.'")';
            if($this->content['OnDuplicateKeyUpdate']):
                $this->prepare_request .= ' '.$this->duplicate;
            endif;
            $this->sql($this->prepare_request);
            return mysql_insert_id();
        elseif($this->content['Delete']):
            $query = $this->prepare_request;
            return $this->sql($query);
        else:
            debug::html(lang::error('sql'), "EXEC method can't be requested before SELECT, UPDATE or INSERT method.", __FILE__, __LINE__);
        endif;
    }

    public function sql($req) {
        if(isset(page::$sql)):
            page::$sql++;
        endif;

        $return = mysql_query($req) or debug::html(lang::error('sql'), mysql_error()."<br />".$req, __FILE__, __LINE__);
        if($this->content['Select']):
            $this->count = mysql_num_rows($return);
        endif;
        return $return;
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
            $table = $this->table['Select'];
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
                    $key = str_replace($this->table['Select'].'_', '', $ligne);
                    if ($this->content['LeftJoin']):
                        $underscore = '';
                        foreach ($this->table['LeftJoin'] as $table):
                            $underscore .= "_";
                            $key = str_replace($table.'_', $underscore, $key);
                        endforeach;
                    endif;
                    $array[$key] = stripslashes($value);
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
            $table = $this->table['Select'];
        endif;
        $this->line[$this->table['Select'].'_'.$field] = $value;
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
    
    public function page($get, $results) {
        global $smarty;
        
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

        $smarty->append('pagination', '<div class="pagination">'.$start.$prev.lang::text('pagination:page', $text).$end.$next.'</div>');
        
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
        $this->content['Select']               = false;
        $this->content['From']                 = false;
        $this->content['Where']                = false;
        $this->content['Limit']                = false;
        $this->content['OrderBy']              = false;
        $this->content['GroupBy']              = false;
        $this->content['LeftJoin']             = false;
        $this->content['On']                   = false;
        $this->content['Select']               = false;
        $this->content['Delete']               = false;
        $this->content['Insert']               = false;
        $this->content['Set']                  = false;
        $this->content['Update']               = false;
        $this->content['OnDuplicateKeyUpdate'] = false;
        $this->content['CountOn']	       = 0;

        if(!isset($this->table['Select'])):
            $this->table['Select']   = '';
        endif;

        $this->table['LeftJoin'] = '';
        $this->table['Set']      = '';
        $this->table['Delete']   = '';
        $this->line              = array();
        $this->prepare_request   = '';
        $this->count             = 0;
        $this->duplicate         = '';
    }

}
?>