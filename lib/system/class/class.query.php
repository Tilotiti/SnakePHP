<?php
class query {
    public
        $prepare_request = '',
        $results         = '',
        $values          = '',
        $table           = '',
        $which           = 'all',
        $prefix          = '',
        $i               = 0,
        $error           = false,
        $queryConnexion  = false,
        $cache           = false,
        $cached          = false,
        $cacheHash		 = false,
        $alias           = array(),
        $line            = array(),
        $content         = array(),
        $fields          = array();

    
    /**
     * Méthode __construct : Construction de la requête SQL, test la connexion à la base de donnée.
     * 
     * @access public
	 * @param Boolean|String $cache indique la catégorie de cache (ou simplement la mise en cache) - default: false
	 * @param String $prefix prefixe de la table
     * @return void
     */
    public function __construct($cache = false, $prefix = DBPREF) {
    	global $queryConnexion;
        $this->reset();
        
        $this->prefix = $prefix;
        
        if(isset($queryConnexion)):
        	// La connexion à la BDD a bien été faite.
        	$this->bdd = $queryConnexion;
        else:
        	// La connexion ne s'est pas faite, on ne donne pas suite à la requête.
        	$this->bdd = false;
        	$this->error = true;
        endif;
		
		if (is_string($cache) || $cache):
            $this->cache = $cache;
        endif;
    }

    
    /**
     * Méthode getField : Récupère un champs d'une table donnée ou induite avec gestion des préfixes automatique.
     * 
     * @access public
     * @param mixed $params
     *		Trois type d'utilisation :
     *			- string : Sélection du champs dans la table principale
     			- array  :
     				- Utilisation d'une fonction (array('FUNCTION' => array('arg1', 'arg2', ...)))
     				- Sélection d'une table en particulier (array('table' => 'champ'))
     * @return champs sélectionné
     */
    public function getField($params) {
        if($params == "*"):
            return '*';
        endif;
            
        if($this->content['select']):
            $table = $this->prefix.$this->table['select'];
            $pref  = $this->table['select'];
        elseif($this->content['delete']):
            $table = $this->prefix.$this->table['delete'];
            $pref  = $this->table['delete'];
        else:
            $table = $this->prefix.$this->table['set'];
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
                                $field = $this->prefix.$key.'.*';
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

    
    /**
     * Méthode select : Sélectionne les champs à insérer dans la requête.
     * 
     * @access public
     * @param mixed champs Insérez autant de paramètre que vous le souhaitez. Chaque paramètre représente un champs, celon les syntaxes autorisés par la méthode getField.
     * @return query $this pour assurer la chaînabilité de la classe
     */
    public function select() {
    	// On récupère tous les champs et on les enregistres
        $this->fields = func_get_args();
        $this->content['select'] = true;
        return $this;
    }
    
    
    /**
     * Méthode update : Sélection de la table à modifier.
     * 
     * @access public
     * @param mixed $table table à modifier
     * @return query $this pour assurer la chaînabilité de la classe
     */
    public function update($table) {
    	if(empty($table)):
    		$this->error = true;
            debug::error("SQL", "TABLE argument must be valid in INSERT method.", __FILE__, __LINE__);
        endif;
        
        $table_name                = $this->prefix.$table;
        $this->prepare_request    .= ' UPDATE '.$table_name;
        $this->content['update']   = true;
        $this->table['set']        = $table;
        return $this;
    }
    
    
    /**
     * Méthode insert : Sélection de la table dans laquelle insérer une nouvelle entrée.
     * 
     * @access public
     * @param mixed $table
     * @return query $this pour assurer la chaînabilité de la classe
     */
    public function insert($table) {
        if(empty($table)):
            debug::error("SQL", "TABLE argument must be valid in INSERT method.", __FILE__, __LINE__);
    		$this->error = true;
        endif;
        
        $table_name               = $this->prefix.$table;
        $this->prepare_request   .= ' INSERT INTO '.$table_name;
        $this->content['insert']  = true;
        $this->table['set']       = $table;
        return $this;
    }
    
    /**
     * Méthode delete : Sélection de la table dans laquelle insérer supprimer des entrées.
     * 
     * @access public
     * @param mixed $table
     * @return query $this pour assurer la chaînabilité de la classe
     */
    public function delete($table) {
        if(empty($table)):
            debug::error("SQL", "TABLE argument must be valid in DELETE method.", __FILE__, __LINE__);
    		$this->error = true;
        endif;
        
        $table_name               = $this->prefix.$table;
        $this->prepare_request   .= ' DELETE FROM '.$table_name;
        $this->content['delete']  = true;
        $this->table['delete']    = $table;
        return $this;
    }
    
    
    /**
     * Méthode set : Définition des champs et de leur nouvelle valeur.
     * 
     * @access public
     * @param mixed $field
     * @param string $value (default: '')
     * @return query $this pour assurer la chaînabilité de la classe
     */
    public function set($field = '', $value = '') {
    	
    	// Vérification de l'argument FIELD indispensable
        if(empty($field)):
            debug::error("SQL", "FIELD argument must be valid in SET method.", __FILE__, __LINE__);
    		$this->error = true;
        endif;
        
        // La méthode set ne peut être appelée après la méthode SELECT
        if($this->content['select']):
            debug::error("SQL", "SET method can't be requested with the SELECT method.", __FILE__, __LINE__);
    		$this->error = true;
        endif;
        
        // Si le paramètre entré en est un array associatif, on met en place un multiple-set
        if(is_array($field)):
            foreach($field as $key => $value):
        		$this->set($key, $value);
        	endforeach;
        	return $this;
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
                $this->prepare_request .= ' '.$this->table['set'].'_'.$field.' = "'.addslashes($value).'"';
            endif;
            $this->content['set']   = true;
        elseif($this->content['insert']):
            $this->fields[]       = $this->table['set'].'_'.$field;
            $this->values[]       = addslashes($value);
            $this->content['set'] = true;
        else:
            debug::error("SQL", "SET method can't be requested before UPDATE or a INSERT method.", __FILE__, __LINE__);
    		$this->error = true;
        endif;
        return $this;
    }
    

    /**
     * Méthode from : Sélection de la table.
     * 
     * @access public
     * @param mixed $table
     * @return query $this pour assurer la chaînabilité de la classe
     */
    public function from($table,$as=false) {
        if(empty($table)):
            debug::error("SQL", "TABLE argument must be valid in FROM method.", __FILE__, __LINE__);
            $this->error = true;
        endif;
        if(!$this->content['select']):
            debug::error("SQL", "FROM method can't be requested before SELECT method.", __FILE__, __LINE__);
             $this->error = true;
        endif;
        if($this->content['from']):
            debug::error("SQL", "FROM method has been already requested.", __FILE__, __LINE__);
             $this->error = true;
        endif;
		
		
		if($table instanceof query) {
			$as = ($as===false?$table->table['select']:$as);
			$this->table['select']  = $as;
		}
		else {
			$this->table['select']  = $table;
		}
		
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
		
		$table_name = $as;
		if($table instanceof query) {
			$table_name             = '('.$table->getRequest().') AS ' . $as;
		}
		else {
			$table_name             = $this->prefix.$table;
		}
		
        $this->prepare_request .= ' FROM '.$table_name;
        $this->content['from']  = true;
        
        return $this;
    }
    
    
    /**
     * Méthode join : Joindre une autre table au résultat
     * 
     * @access public
     * @param mixed $table table name
	 * @param String $joinType case-insensitive join type (left, right, outer, inner) - default: left
     * @return query $this pour assurer la chaînabilité de la classe
     */
    public function join($table, $joinType='left') {
        if(empty($table)):
            debug::error("SQL", "TABLE argument must be valid in JOIN method.", __FILE__, __LINE__);
             $this->error = true;
        endif;
        if(!$this->content['select']):
            debug::error("SQL", "JOIN method can't be requested before SELECT method.", __FILE__, __LINE__);
             $this->error = true;
        endif;
        if(!$this->content['from']):
            debug::error("SQL", "JOIN method can't be requested before FROM method.", __FILE__, __LINE__);
             $this->error = true;
        endif;
		
		$joins = array('RIGHT', 'LEFT', 'FULL OUTER', 'INNER', 'OUTER');
		$joinType = strtoupper($joinType);
		
		if(!in_array($joinType, $joins)):
            $joinType = 'LEFT';
            debug::error("SQL", "JOIN type must be one of these : ".implode(', ', $joins), __FILE__, __LINE__);
			$this->error = true;
        endif;
		
		switch ($joinType):
			case 'OUTER':
				$joinType = 'FULL OUTER';
			break;
			
			default: break;
		endswitch;
		
        $table_name                 = $this->prefix.$table;
        $this->prepare_request     .= ' '.$joinType.' JOIN '.$table_name;
        $this->content['join']  	= true;
        $this->table['join'][]  	= $table;
        return $this;
    }

    
    /**
     * Performs a LEFT JOIN
     * 
     * @access public
     * @param mixed $table
     * @return query $this pour assurer la chaînabilité de la classe
     */
    public function leftJoin($table) {
    	return $this->join($table);
    }

	/**
     * Performs a RIGHT JOIN - should NOT be used according to MySQL documentation
	 * @see http://dev.mysql.com/doc/refman/5.0/en/join.html
	 * "To keep code portable across databases, it is recommended that you use LEFT JOIN instead of RIGHT JOIN."
     * 
     * @access public
     * @param mixed $table
     * @return query $this pour assurer la chaînabilité de la classe
     */
    public function innerJoin($table) {
    	return $this->join($table, 'inner');
    }
	
	/**
     * Performs a RIGHT JOIN
     * 
     * @access public
     * @param mixed $table
     * @return query $this pour assurer la chaînabilité de la classe
     */
    public function rightJoin($table) {
    	return $this->join($table, 'right');
    }
	
	/**
     * Performs an OUTER JOIN
	 * 
	 * Although OUTER JOIN doesn't exists in MySQL, this method allow to simulate it.
	 * Because of that, this method must be called instead of the FROM method.
	 * 
	 * Beware of the parameters though, because you have to provide both tables, and "ON" values.
     * This method is based on UNION, LEFT JOIN and RIGHT JOIN.
	 * 
     * @access public
     * @param mixed $table
     * @return query $this pour assurer la chaînabilité de la classe
     */
    public function outerJoin($table, $join, $on1, $on2) {
    	// sub-join 2 (right part of outer join)
    	$subrequest2 = new query($this->cache);
		$subrequest2->select()
					->from($table)
					->rightJoin($join)
					->on($on1, $on2)
					// do not duplicate
					->where($on1, 'IS NULL', false);
		
		// sub-join 1 (left part of outer join)
    	$subRequest1 = new query($this->cache);
		$subRequest1->select()
					->from($table)
					->leftJoin($join)
					->on($on1, $on2)
					
					// union part
					->addString(' UNION ALL ('.$subrequest2->getRequest().') ');
		
		// small workaround
		return 	$this->from($subRequest1);
    }
    
    /**
     * Méthode on : Détermine les champs communs servant à joindre deux tables.
     * 
     * @access public
     * @param mixed $value1
     * @param mixed $value2
     * @return query $this pour assurer la chaînabilité de la classe
     */
    public function on($value1, $value2) {

        $this->content['countOn']++;
        
        if(empty($value1)):
            debug::error("SQL", "VALUE1 argument must be valid in ON method.", __FILE__, __LINE__);
             $this->error = true;
        endif;
        if(empty($value2)):
            debug::error("SQL", "VALUE2 argument must be valid in ON method.", __FILE__, __LINE__);
             $this->error = true;
        endif;
        if(!$this->content['select']):
            debug::error("SQL", "ON method can't be requested before SELECT method.", __FILE__, __LINE__);
             $this->error = true;
        endif;
        if(!$this->content['from']):
            debug::error("SQL", "ON method can't be requested before FROM method.", __FILE__, __LINE__);
             $this->error = true;
        endif;
        if(($this->content['countOn'] > count($this->table['join']))):
            debug::error("SQL", "ON method can't be requested before JOIN method.", __FILE__, __LINE__);
             $this->error = true;
        endif;
        
        if(is_string($value1) && is_string($value2)):
            // On gère la rétrocompatibilité
            if(empty($value3)):
                $value3 = $this->table['select'];
            endif;
            $this->prepare_request .= ' ON '.$this->prefix.$value3.'.'.$value3.'_'.$value1;
            $this->prepare_request .= ' = '.$this->prefix.$this->table['join'][count($this->table['join'])-1].'.'.$this->table['join'][count($this->table['join'])-1].'_'.$value2;
        else:
            $this->prepare_request .= ' ON '.$this->getField($value1).' = '.$this->getField($value2);
        endif;
        $this->content['on']    = true;
        return $this;
    }

	/**
	 * Méthode USING : peut être appelée uniquement après un des JOIN
	 * 
	 */
	public function using() {
		
		$this->content['countOn']++;
		
		if(!$this->content['select']):
            debug::error("SQL", "USING method can't be requested before SELECT method.", __FILE__, __LINE__);
             $this->error = true;
        endif;
        if(!$this->content['from']):
            debug::error("SQL", "USING method can't be requested before FROM method.", __FILE__, __LINE__);
             $this->error = true;
        endif;
        if(($this->content['countOn'] > count($this->table['join']))):
            debug::error("SQL", "USING method can't be requested before JOIN method.", __FILE__, __LINE__);
             $this->error = true;
        endif;
		$usings = func_get_args();
		$this->prepare_request .= ' USING ';
		
		foreach ($usings as $k => $using) {
			$usings[$k] = $this->getField($using);
		}
		$this->prepare_request .= implode(',', $usings);
		
		$this->content['using']    = true;
	}

    /**
     * Méthode where : Détermine les conditions de sélection de la requête
     * 
     * @access public
     * @param mixed $field Champs sélectionné
     * @param string $calculator Opérateur de la condition
     * @param mixed $value Valeur du champs à tester
     * @param string $calcul Type de test à effectuer ("where" ou "and")
     * @return query $this pour assurer la chaînabilité de la classe
     */
    public function where($field, $calculator, $value = false, $calcul = "where") {
        if(!$this->content['select'] && !$this->content['update'] && !$this->content['delete']):
            debug::error("SQL", $calcul." method can't be requested before SELECT, DELETE or UPDATE method.", __FILE__, __LINE__);
            $this->error = true;
        endif;
        if(!$this->content['from'] && !$this->content['update'] && !$this->content['delete']):
            debug::error("SQL", $calcul." method can't be requested before FROM or UPDATE method.", __FILE__, __LINE__);
            $this->error = true;
        endif;
        /*if($this->content['join'] && !$this->content['on']):
            debug::error("SQL", $calcul." method can't be requested before ON method when JOIN method has been requested.", __FILE__, __LINE__);
            $this->error = true;
        endif;*/
        if($this->content['orderBy']):
            debug::error("SQL", $calcul." method can't be requested after ORDER BY method.", __FILE__, __LINE__);
            $this->error = true;
        endif;
        if($this->content['groupBy']):
            debug::error("SQL", $calcul." method can't be requested after GROUP BY method.", __FILE__, __LINE__);
            $this->error = true;
        endif;
        if($this->content['limit']):
            debug::error("SQL", $calcul." method can't be requested after LIMIT method.", __FILE__, __LINE__);
            $this->error = true;
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
            	// Utilisation d'une fonction sans argument comme paramètre à vérifier
                $this->prepare_request .= ' '.$field.' '.$calculator.' '.$value['FUNCTION'].'()';
            else:
            	// Utilisation d'une fonction en temps qu'opérateur comme paramètre à vérifier
                $this->prepare_request .= ' '.$field.' '.$calculator.' ("'.implode('", "', $value).'")';
            endif;
        elseif(is_object($value)):
            $name = get_class($value);
            if($name == "query"):
            	// Utilisation de sous-requêtes comme paramètre à vérifier
                $this->prepare_request .= ' '.$field.' '.$calculator.' ( '.$value->getRequest().')';
            else:
            	// Utilisation d'un string sous forme d'object comme paramètre à vérifier
                $this->prepare_request .= ' '.$field.' '.$calculator.' "'.addslashes((string) $value).'"';
            endif;
        elseif($value === false):
        	$this->prepare_request .= ' '.$field.' '.$calculator;
        else:
            if(is_numeric($value)):
            	// Utilisation d'un nombre comme paramètre à vérifier
                $this->prepare_request .= ' '.$field.' '.$calculator.' '.addslashes($value);
            else:
            	// Utilisation d'un string comme paramètre à vérifier
                $this->prepare_request .= ' '.$field.' '.$calculator.' "'.addslashes($value).'"';
            endif;
        endif;

        return $this;
    }
    
    
    /**
     * Methode ou : Détermine les conditions annexes de sélection de la requête (Equivalent de l'opérateur OR en SQL)
     * 
     * @access public
     * @param mixed $field Champs sélectionné
     * @param string $calculator Opérateur de la condition
     * @param mixed $value Valeur du champs à tester
     * @return query $this pour assurer la chaînabilité de la classe
     */
    public function ou($field, $calculator, $value) {
        $this->where($field, $calculator, $value, 'OR');
        return $this;
    }

    
    /**
     * Méthode onDuplicateKeyUpdate : Lors d'une insertion, si la clef primaire existe déjà, on permet alors la modification de certains champs.
     * 
     * @access public
     * @param mixed $field champs à modifier
     * @param mixed $value valeur du champs
     * @return query $this pour maintenir la chaînabilité de la classe.
     */
    public function onDuplicateKeyUpdate($field, $value) {
        if(!$this->content['insert']):
            debug::error("SQL", "ON DUPLICATE KEY UPDATE method can't be requested before INSERT method.", __FILE__, __LINE__);
            $this->error = true;
        endif;
        if(!$this->content['set']):
            debug::error("SQL", "ON DUPLICATE KEY UPDATE method can't be requested before SET method.", __FILE__, __LINE__);
            $this->error = true;
        endif;
        if($this->content['onDuplicateKeyUpdate']):
            $this->duplicate .= ',';
        else:
            $this->content['onDuplicateKeyUpdate'] = true;
            $this->duplicate = 'ON DUPLICATE KEY UPDATE';
        endif;
        $this->duplicate .= ' '.$this->getField($field).' = "'.addslashes($value).'"';
        return $this;
    }
    
    
    /**
     * Méthode orderBy : Spécifie sur quel champs et dans quel ordre trier les résultats.
     * 
     * @access public
     * @param string $field (default: "")
     * @param string $order (default: 'ASC')
     * @return query $this pour maintenir la chaînabilité de la classe
     */
    public function orderBy($field = "", $order = 'ASC') {
        $order = strtoupper($order);
        if(!$this->content['select']):
            debug::error("SQL", "ORDER BY method can't be requested before SELECT method.", __FILE__, __LINE__);
            $this->error = true;
        endif;
        if(!$this->content['from']):
            debug::error("SQL", "ORDER BY method can't be requested before FROM method.", __FILE__, __LINE__);
            $this->error = true;
        endif;
        if(($this->content['countOn'] > count($this->table['join']))):
            debug::error("SQL", "ORDER BY method can't be requested before ON method when JOIN method has been requested.", __FILE__, __LINE__);
            $this->error = true;
        endif;
        if($order != 'ASC' && $order != 'DESC'):
            $order = 'ASC';
        endif;
        if($this->content['limit']):
            debug::error("SQL", "ORDER BY method can't be requested after LIMIT method.", __FILE__, __LINE__);
            $this->error = true;
        endif;
        if(!$this->content['orderBy']):
            $this->prepare_request .= ' ORDER BY';
            $this->content['orderBy'] = true;
        else:
            $this->prepare_request .= ' ,';
        endif;
        
        // Si le champs n'est pas renseigné, on applique un ordre aléatoire aux résultats
        if(empty($field)):
            $field = "RAND()";
        else:
            $field = $this->getField($field);
        endif;
        
        $this->prepare_request .= ' '.$field.' '.$order.'';
        
        return $this;
    }

    
    /**
     * Méthode groupBy : Regroupe les résultats par un champs commun.
     * 
     * @access public
     * @param mixed $field champs commun sur lequel appliquer le regroupement des résultats
     * @return query $this pour maintenir la chaînabilité de la classe
     */
    public function groupBy($field) {
        if(!$this->content['select']):
            debug::error("SQL", "GROUP BY method can't be requested before SELECT method.", __FILE__, __LINE__);
            $this->error = true;
        endif;
        if(!$this->content['from']):
            debug::error("SQL", "GROUP BY method can't be requested before FROM method.", __FILE__, __LINE__);
            $this->error = true;
        endif;
        if(($this->content['countOn'] > count($this->table['join']))):
            debug::error("SQL", "GROUP BY method can't be requested before ON method when JOIN method has been requested.", __FILE__, __LINE__);
            $this->error = true;
        endif;
        if($this->content['limit']):
            debug::error("SQL", "GROUP BY method can't be requested after LIMIT method.", __FILE__, __LINE__);
            $this->error = true;
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

    
    /**
     * Méthode limit : Détermine l'interval de sélection des résultats.
     * 
     * @access public
     * @param mixed $limit1 Début de l'interval (ou fin de l'interval si le second argument est vide)
     * @param bool $limit2 Fin de l'interval (default: false)
     * @return query $this pour maintenir la chaînabilité de la classe
     */
    public function limit($limit1, $limit2 = false) {
        if(!$this->content['select']):
            debug::error("SQL", "LIMIT method can't be requested before SELECT method.", __FILE__, __LINE__);
            $this->error = true;
        endif;
        if(!$this->content['from']):
            debug::error("SQL", "LIMIT method can't be requested before FROM method.", __FILE__, __LINE__);
            $this->error = true;
        endif;
        if(($this->content['countOn'] > count($this->table['join']))):
            debug::error("SQL", "LIMIT method can't be requested before ON method when JOIN method has been requested.", __FILE__, __LINE__);
            $this->error = true;
        endif;
        
        if(empty($limit2)):
            $this->prepare_request .= ' LIMIT '.$limit1;
        else:
            $this->prepare_request .= ' LIMIT '.$limit1.', '.$limit2;
        endif;
        
        $this->content['limit'] = true;
        return $this;
    }

    
    /**
     * Méthode exec : Génère la requête et enclenche son éxecution.
     * 
     * @access public
     * @param string $which (default: "ALL")
     * @return void
     */
    public function exec($which = "ALL") {
        if(($this->content['select'] && $this->content['update'] && $this->content['insert'])
        || ($this->content['update'] && $this->content['insert'])
        || ($this->content['select'] && $this->content['update'])
        || ($this->content['select'] && $this->content['insert'])):
            debug::error("SQL", "SELECT, UPDATE and INSERT methods can't be requested in the same time.", __FILE__, __LINE__);
            $this->error = true;
        endif;
        
        // Si une erreur s'est produite pendant la requête, en empêche celle-ci d'être envoyée
        if($this->error):
        	return false;
        endif;
        
        // Requête SELECT
        if($this->content['select']):

            if(!$this->content['from']):
                debug::error("SQL", "EXEC method can't be requested before FROM method.", __FILE__, __LINE__);
                $this->error = true;
            endif;
            
            // Si on demande un résultat unique, on applique un limit 1 pour alléger la requête
            if($which == "FIRST"):
                $this->limit(1);
            endif;
            
            $which        = strtoupper($which);
            $this->result = $this->sql($this->prepare_request);
            $this->which  = $which;

            if($this->which != 'ALL' && $this->which != 'FIRST'):
                debug::error("SQL", 'EXEC method only accept blank, "ALL" or "FIRST" for argument.', __FILE__, __LINE__);
                $this->error = true;
            endif;
            
            // Si on demande un résultat unique, on le place directement en mémoire
            if($this->which == "FIRST" && $this->ok()):
            	$this->line = $this->results[0];
            endif;

            return $this->result;
            
        // Requête UPDATE
        elseif($this->content['update']):
            if(!$this->content['set']):
                debug::error("SQL", "EXEC method can't be requested before SET method.", __FILE__, __LINE__);
                $this->error = true;
            endif;
            
            $this->sql($this->prepare_request);
            
            return $this->count();
            
        // Requête INSERT
        elseif($this->content['insert']):
            if(!$this->content['set']):
                debug::error("SQL", "EXEC method can't be requested before SET method.", __FILE__, __LINE__);
                $this->error = true;
            endif;
            
            $field = implode(',', $this->fields);
            $value = implode('", "', $this->values);
            $this->prepare_request .= ' ('.$field.') VALUES ("'.$value.'")';
            
            if($this->content['onDuplicateKeyUpdate']):
                $this->prepare_request .= ' '.$this->duplicate;
            endif;
            
            $this->sql($this->prepare_request);
            
            return $this->bdd->lastInsertId();
            
        // Requête DELETE
        elseif($this->content['delete']):
            $this->sql($this->prepare_request);
            return $this->count();
        
        // Erreur
        else:
            debug::error("SQL", "EXEC method can't be requested before SELECT, UPDATE or INSERT method.", __FILE__, __LINE__);
            $this->error = true;
        endif;
    }

    /**
	 * Return "cache hash" of the query if cached, false otherwise.
	 * Can be used with flushSQL to flush only this request. 
	 * @return String|Boolean cache hash
	 */
    public function getCacheHash() {
		return $this->cacheHash;
    }
    
    /**
     * Méthode sql : Exécute la requête s'il n'y a aucune erreur
     * 
     * @access public
     * @param mixed $req Requête à éxecuter
     * @return void
     */
    public function sql($req) {
    	// On vérifie que jusque là, tout se passe bien
        if(DBHOST && !$this->error):
            try {
            	if($this->content['select']):
            		
					// cache category - file prefix
	            	$cachePref = ($this->cache===true?'':md5('prefix'.$this->cache));
					
					// Cache verification
	            	if($this->cache):
	            		// Already cached
	            		$file = SQLCACHE.'/'.$cachePref.md5($req).'.cache';
						$this->cached = false;
						
						// Cache exists and not too old
	            		if( file_exists($file) && (filemtime($file) > (time() - SQLCACHETIME)) ):
            				$this->cached = true;
	            		endif;
	            	endif;
	            	
	            	if(!$this->cached):
			            $return = $this->bdd->query($req);
			            $return->setFetchMode(PDO::FETCH_ASSOC);
			            
			            $results = $return->fetchAll();
			            
			            // Must caching the request
			            if($this->cache):
			            	$file = fopen(SQLCACHE.'/'.$cachePref.md5($req).'.cache', 'w+');
			            	fwrite($file, serialize($results));
			            	fclose($file);
			            endif;
		            else:
		            	$results = unserialize(file_get_contents(SQLCACHE.'/'.$cachePref.md5($req).'.cache'));
		            endif;
					
					if ($this->cache) {
						$this->cacheHash = $cachePref.md5($req);
					}
		            
		            // On compte le nombre d'occurence trouvée
		            $this->count = count($results);
		            
		            // On enregistre les résultats
		            $this->results = $results;
		            
	            else:
	            	// On récupère le nombre d'occurence touchée par la requête
	            	$return 	 = $this->bdd->exec($req);
	            	$this->count = $return;
	            endif;
	            
	            debug::sql($req, $this->count, $this->cached);
	            return true;
            } catch( Exception $error ) {
	            debug::error("SQL", $error->getMessage()."<br />".$req, __FILE__, __LINE__);
	            $this->error = true;
	            return false;
            }
        else:
            return false;
        endif;
    }
    
    /**
     * Méthode next : Affiche le résultat suivant.
     * 
     * @access public
     * @return void
     */
    public function next() {
        	
        if(!is_array($this->results)):
            debug::error("SQL", "NEXT method can't be requested before SELECT and EXEC method.", __FILE__, __LINE__);
            $this->error = true;
        endif;
        
        if($this->which == "FIRST"):
            debug::error("SQL", "NEXT method can't be requested with FIRST as argument for EXEC method.", __FILE__, __LINE__);
            $this->error = true;
        endif;
                
        if(!$this->ok()):
            return false;
        endif;
                
        if(isset($this->results[$this->i])):
	        $this->line = $this->results[$this->i];
	        $this->i++;
	        return $this->line;
	    else:
	    	$this->i = 0;
	    	return false;
	    endif;
    }

    
    /**
     * Méthode getRequest : Récupère la requête générée
     * 
     * @access public
     * @return void
     */
    public function getRequest() {
        return $this->prepare_request;
    }

    
    /**
     * Méthode get : Récupère le ou les champs de la ligne en cours.
     * 
     * @access public
     * @param string $field (default: false)
     * @param bool $isGetArray (default: false)
     * @return Soit la valeur du champs sélectionné soit un tableau contenant tous les champs
     */
    public function get($field = false) {

        if($this->error):
    		$return = false;
    	endif;
    	
        if(!is_array($this->results)):
            debug::error("SQL", "GET method can't be requested before SELECT and EXEC method.", __FILE__, __LINE__);
            $this->error = true;
        endif;
        
        // Si on demande un champ spécifique avec la methode getField
        if(is_array($field)):
            if(isset($this->line[$this->getField($field)])):
                $return = stripslashes($this->line[$this->getField($field)]);
            else:
                $return = false;
            endif;
            
        // Si on demande un champ spécifique simple
        elseif($field != false):
        	if(in_array($field, $this->table['join'])):
        		$array = array();
        		foreach($this->line as $ligne => $value):
        			if(preg_match('#^'.$field.'#', $ligne)):
        				$array[str_replace($field.'_', '', $ligne)] = $value; 
        			endif;
        		endforeach;
        		$return = $array;
            elseif(isset($this->line[$this->table['select'].'_'.$field])):
                $return = stripslashes($this->line[$this->table['select'].'_'.$field]);
            elseif(in_array($field, $this->alias)):
                $return = $this->line[$field];
            else:
                $return = false;
            endif;
            
        // Si on demande tous les champs
        else:
            if(is_array($this->line) && count($this->line)!=0):
                $array = array();
                
                foreach($this->table['join'] as $table):
                    $array[$table] = array();
                endforeach;
                
                foreach($this->line as $ligne => $value):
                    if(preg_match('#^'.$this->table['select'].'#', $ligne)):
                    	$key = str_replace($this->table['select'].'_', '', $ligne);
                    	if(!isset($array[$key])):
                    		if(!is_string($value)):
		                        $array[$key] = $value;
		                    else:
		                        $array[$key] = stripslashes($value);
		                    endif;
                    	endif;
                    else:
                    	foreach($this->table['join'] as $table):
                    		if(preg_match('#^'.$table.'#', $ligne)):
                    			$key = str_replace($table.'_', '', $ligne);
                    			if(!is_string($value)):
			                        $array[$table][$key] = $value;
			                    else:
			                        $array[$table][$key] = stripslashes($value);
			                    endif;
                    			break;
                    		endif;
                    	endforeach;
                    endif;
                endforeach;
                
                if(count($array) == 0):
                    $return = false;
                else:
                    $return = $array;
                endif;
            else:
                $return = false;
            endif;
        endif;
        
        return $return;
    }
    
    /**
     * Méthode put : Modifie le tableau de sortie d'une ligne.
     * 
     * @access public
     * @param mixed $field Champ à modifier
     * @param string $value Valeur du champ à modifier (default: '')
     * @return void
     */
    public function put($field, $value='') {
    
        if(empty($this->result)):
            debug::error("SQL", "PUT method can't be requested before SELECT and EXEC method.", __FILE__, __LINE__);
            $this->error = true;
        endif;
        
        if(empty($table)):
            $table = $this->table['select'];
        endif;
        
        $this->line[$this->table['select'].'_'.$field] = $value;
        return true;
    }

    
    /**
     * Méthode count : Compte le nombre de résultat de la requête.
     * 
     * @access public
     * @return void
     */
    public function count() {
        if(is_int($this->count)):
            return $this->count;
        else:
            return 0;
        endif;
    }

    
    /**
     * Méthode ok : Vérifie la présence d'au moins un résultat et si aucune erreur n'est survenue.
     * 
     * @access public
     * @return void
     */
    public function ok() {
        if($this->content['select'] && is_int($this->count) && $this->count > 0 && !$this->error):
            return true;
        else:
            return false;
        endif;
    }

    
    /**
     * Méthode getArray : Ressort le tableau associatif total des résultats de la reqête.
     * 
     * @access public
     * @return void
     */
    public function getArray() {
    	if(is_array($this->results)):
    	
    		$return = array();
    		while($this->next()):
    			$return[] = $this->get();
    		endwhile;
    		
	        return $return;
        else:
        	return array();
        endif;
    }

    
    /**
     * Méthode addString : Permet d'ajouter une chaîne de caractère à la requête.
     * 
     * @access public
     * @param mixed $string Chaîne de caractère à ajouter
     * @return query $this pour maintenir la chaînabilité de la classe
     */
    public function addString($string) {
        $this->prepare_request .= $string;
        return $this;
    }
    
    
    /**
     * Méthode page : Génère une pagination de la requête automatiquement, basée sur un paramètre dans l'URL.
     * 
     * @access public
     * @param mixed $get get à utiliser
     * @param mixed $results Nombre de résultat à afficher
     * @param string $variable Nom de la variable template dans lequel injecter la pagination (default: "pagination")
     * @return query $this pour maintenir la chaînabilité de la classe
     */
    public function page($get, $results, $variable = "pagination") {
        global $page;
        
        $this->sql($this->prepare_request);
        
        $nb = $this->count();
        
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
            $start = '<li><a href="'.get($get, 1)         .'" class="start">'.lang::text('pagination:start').'</a></li>';
            $prev  = '<li><a href="'.get($get, $current-1).'" class="prev">'. lang::text('pagination:prev') .'</a></li>';
        else:
            $start = '';
            $prev  = '';
        endif;
        
        if($current < ceil($nb / $results)):
            $next  = '<li><a href="'.get($get, $current+1)          .'" class="next">'. lang::text('pagination:next') .'</a></li>';
            $end   = '<li><a href="'.get($get, ceil($nb / $results)).'" class="end">'.  lang::text('pagination:end')  .'</a></li>';
        else:
            $end   = '';
            $next  = '';
        endif;
        
        $center = '<li class="disabled"><a href="'.get().'">'.lang::text('pagination:page', $text).'</a></li>';

        $page->template($variable, '<div class="pagination pagination-centered"><ul>'.$start.$prev.$center.$next.$end.'</ul></div>');
        
        return $this;
    }

    
    /**
     * Méthode debug : Permet d'afficher dans le template la requête générée ainsi que les résultats ressortis.
     * 
     * @access public
     * @param bool $force Si activé, alors la requête ne sera pas affichée dans le template mais directement (default: false)
     * @return void
     */
    public function debug() {
        $debug = array();
        $debug['request']  = $this->prepare_request;
        $debug['count']    = $this->count;
        $debug['cache']    = $this->cache;
        $debug['cached']   = $this->cached;
        
        if($this->ok() && $this->which == "ALL"):
        	$debug['results'] = $this->getArray();
        elseif($this->ok() && $this->which == "FIRST"):
        	$debug['results'] = $this->get();
        endif;
        
        debug::dump($debug, lang::text('sql:request'));
    }

    public function reset() {
        $this->content['select']               = false;
        $this->content['from']                 = false;
        $this->content['where']                = false;
        $this->content['limit']                = false;
        $this->content['orderBy']              = false;
        $this->content['groupBy']              = false;
        $this->content['join']	               = false;
        $this->content['on']                   = false;
        $this->content['select']               = false;
        $this->content['delete']               = false;
        $this->content['insert']               = false;
        $this->content['set']                  = false;
        $this->content['update']               = false;
        $this->content['onDuplicateKeyUpdate'] = false;
        $this->content['countOn']          	   = 0;

        if(!isset($this->table['select'])):
            $this->table['select']   = '';
        endif;

        $this->table['join']     = array();
        $this->table['set']      = '';
        $this->table['delete']   = '';
        $this->line              = array();
        $this->prepare_request   = '';
        $this->count             = 0;
        $this->duplicate         = '';
    }
    
}
