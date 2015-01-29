<?php
class query {
	public
		/**
		 * Raw SQL container
		 * @var String
		 */
		$prepare_request= '',
		/**
		 * Results
		 * @var Array
		 */
		$results		= '',
		/**
		 * Values (SET instruction)
		 * @var Array
		 */
		$values			= '',
		/**
		 * Table(s) from which results are extracted
		 * @var Array
		 */
		$table		 	= '',
		/**
		 * Return : all results or just first ?
		 * Can equals 'ALL' or 'FIRST'
		 * @var String
		 */
		$which		 	= 'ALL',
		/**
		 * Main (understand first) table prefix
		 * @var String
		 */
		$prefix			= '',
		/**
		 * Counter - index when scanning results with self::next()
		 * @var Integer
		 */
		$i				= 0,
		/**
		 * Will be set to true when one (or more) error(s) occurred
		 * @var Boolean
		 */
		$error		 	= false,
		/**
		 * DB connection
		 * @var String
		 */
		$queryConnexion	= false,
		/**
		 * Cache query (category of cache or {true|false})
		 * @var Boolean|String
		 */
		$cache		 	= false,
		/**
		 * Is the query already cached ?
		 * @var Boolean
		 */
		$cached			= false,
		/**
		 * Identifier of the cache (false if no caching)
		 * @var String|Boolean
		 */
		$cacheHash		= false,
		/**
		 * List of aliases
		 * @var Array
		 */
		$alias		 	= array(),
		/**
		 * Current line for self::next method
		 * @var Array
		 */
		$line			= array(),
		/**
		 * List of SQL instructions encountered
		 * @var Array
		 */
		$content		= array(),
		/**
		 * Fields to return (SELECT instruction)
		 * @var Array
		 */
		$fields			= array();
	/**
	 * Used to identify query on the page
	 * @var Boolean
	 */
	public static $queryNumber	= 0;

	
	/**
	 * SQL query constructor, tests database connection
	 * Specify a category for first parameter to cache by category, otherwise set true (uncategorized cache)
	 * or false (no cache).
	 * 
	 * @access public
	 * @param Boolean|String $cache category, true or false
	 * @param String $prefix database prefix
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
		
		if(is_string($cache) || $cache):
			$this->cache = $cache;
			
			if(!is_dir(CACHE.'/sql')):
				if(!mkdir(CACHE.'/sql')):
					$this->cache = false;
				endif;
			endif;
		endif;
	}


	/**
	 * Converts a query field syntax into SQL syntax
	 * 
	 * Three way to use parameter :
	 * 		- String : select a field of the main table
	 * 		- Array	:
	 * 			 * use a function like (array('FUNCTION' => array('arg1', 'arg2', ...)))
	 * 			 * select a field of a specific table (array('table' => 'champ'))
	 * @access public
	 * @param mixed $params
	 * @return Array selected field(s)
	 */
	public function getField($params) {
		if($params == "*"):
			return '*';
		endif;
			
		if($this->content['select']):
			$table = $this->prefix.$this->table['select'];
			$pref	= $this->table['select'];
		elseif($this->content['delete']):
			$table = $this->prefix.$this->table['delete'];
			$pref	= $this->table['delete'];
		else:
			$table = $this->prefix.$this->table['set'];
			$pref	= $this->table['set'];
		endif;

		if(is_array($params)):
			// Si le champ demandé est un array
			$field = "";
			$as	= "";
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
							$field	= $key."(";
							$var = array();
							foreach($value as $val):
							 if(!is_array($val)):
							 	if(is_numeric($val)):
									$var[] = $val;
								elseif(is_string($val)):
									$var[] = '"'.$val.'"';
								endif;
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
	 * Select field(s) to extract from query
	 * Accepts custom arguments, according to query::getField syntax
	 * 
	 * @access public
	 * @param mixed $champs fields to select ; '*' or leave empty to select all - @see query::getField
	 * @return query $this for chaining
	 */
	public function select() {
		// On récupère tous les champs et on les enregistres
		$this->fields = func_get_args();
		$this->content['select'] = true;
		return $this;
	}
	
	
	/**
	 * Select table to update
	 * 
	 * @access public
	 * @param mixed $table table to update
	 * @return query $this for chaining
	 */
	public function update($table) {
		if(empty($table)):
			$this->error = true;
			debug::error("SQL", "TABLE argument must be valid in INSERT method.", __FILE__, __LINE__);
		endif;
		
		$table_name			 = $this->prefix.$table;
		$this->prepare_request	.= ' UPDATE '.$table_name;
		$this->content['update']	= true;
		$this->table['set']		= $table;
		return $this;
	}
	
	
	/**
	 * Select the table to insert in
	 * 
	 * @access public
	 * @param mixed $table table to insert in
	 * @return query $this for chaining
	 */
	public function insert($table) {
		if(empty($table)):
			debug::error("SQL", "TABLE argument must be valid in INSERT method.", __FILE__, __LINE__);
			$this->error = true;
		endif;
		
		$table_name			= $this->prefix.$table;
		$this->prepare_request	.= ' INSERT INTO '.$table_name;
		$this->content['insert']	= true;
		$this->table['set']		= $table;
		return $this;
	}
	
	/**
	 * Select table we want to delete from
	 * 
	 * @access public
	 * @param mixed $table table to delete from
	 * @return query $this for chaining
	 */
	public function delete($table) {
		if(empty($table)):
			debug::error("SQL", "TABLE argument must be valid in DELETE method.", __FILE__, __LINE__);
			$this->error = true;
		endif;
		
		$table_name			= $this->prefix.$table;
		$this->prepare_request	.= ' DELETE FROM '.$table_name;
		$this->content['delete']	= true;
		$this->table['delete']	= $table;
		return $this;
	}
	
	
	/**
	 * Instruction to add after query::update or query::insert
	 * Set a value to a field
	 * 
	 * @access public
	 * @param mixed $field name of the field
	 * @param String $value[optional] value of the field - default: ''
	 * @return query $this pour assurer la chaînabilité de la classe
	 */
	public function set($field = '', $value = '') {
		// FIXME $field should not be optional
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
			$this->content['set']	= true;
		elseif($this->content['insert']):
			$this->fields[]		= $this->table['set'].'_'.$field;
			$this->values[]		= addslashes($value);
			$this->content['set'] = true;
		else:
			debug::error("SQL", "SET method can't be requested before UPDATE or a INSERT method.", __FILE__, __LINE__);
			$this->error = true;
		endif;
		return $this;
	}
	

	/**
	 * Select table from which data will be extracted (called after query::select)
	 * 
	 * @access public
	 * @param mixed $table table to extract from
	 * @return query $this for chaining
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
		
		
		if($table instanceof query):
			$as = ($as===false?$table->table['select']:$as);
			$this->table['select']	= $as;
		else:
			$this->table['select']	= $table;
		endif;
		
		if(count($this->fields)==0):
			$this->prepare_request	.= ' SELECT * ';
		else:
			$this->prepare_request	.= ' SELECT ';
			$array = array();
			foreach($this->fields as $field):
			$array[] = $this->getField($field);
			endforeach;
			$this->prepare_request .= implode(', ', $array);
		endif;
		
		$table_name = $as;
		if($table instanceof query):
			$table_name			= '('.$table->getRequest().') AS ' . $as;
		else:
			$table_name			= $this->prefix.$table;
		endif;
		
		$this->prepare_request .= ' FROM '.$table_name;
		$this->content['from']	= true;
		
		return $this;
	}
	
	
	/**
	 * Joins another table to input. This method should be called only within this class.
	 * Please use query::leftJoin, query::rightJoin or query::innerJoin elsewhere.
	 * 
	 * @access public
	 * @param mixed $table name of table to join
	 * @param String $joinType case-insensitive join type (left, right, outer, inner) - default: left
	 * @return query $this for chaining
	 */
	public function join($table, $joinType='left') {
		
		$joins = array('RIGHT', 'LEFT', 'FULL OUTER', 'INNER', 'OUTER');
		$joinType = strtoupper($joinType);
		
		if(!in_array($joinType, $joins)):
			$joinType = 'LEFT';
			debug::error("SQL", "JOIN type must be one of these : ".implode(', ', $joins), __FILE__, __LINE__);
			$this->error = true;
		endif;
		
		switch ($joinType):
			default: break;
		endswitch;
			
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
		
		
		
		$table_name				= $this->prefix.$table;
		$this->prepare_request	.= ' '.$joinType.' JOIN '.$table_name;
		$this->content['join']		= true;
		$this->table['join'][]		= $table;
		return $this;
	}

	
	/**
	 * Performs a LEFT JOIN
	 * 
	 * @access public
	 * @remark this method is no longer deprecated
	 * @param mixed $table name of table to join
	 * @return query $this for chaining
	 */
	public function leftJoin($table) {
		return $this->join($table);
	}

	/**
	 * Performs an INNER JOIN
	 * 
	 * @access public
	 * @param mixed $table name of table to join
	 * @return query $this for chaining
	 */
	public function innerJoin($table) {
		return $this->join($table, 'inner');
	}
	
	/**
	 * Performs a RIGHT JOIN - should NOT be used according to MySQL documentation
	 * @see http://dev.mysql.com/doc/refman/5.0/en/join.html
	 * @remark "To keep code portable across databases, it is recommended that you use LEFT JOIN instead of RIGHT JOIN."
	 * 
	 * @access public
	 * @param mixed $table name of table to join
	 * @return query $this for chaining
	 */
	public function rightJoin($table) {
		return $this->join($table, 'right');
	}
	
	/**
	 * Performs an OUTER JOIN
	 * 
	 * Although OUTER JOIN doesn't exists in MySQL, this method allow to simulate it.
	 * Because of that, this method must be called instead of the FROM method.
	 * You have to provide both tables plus "ON" fields.
	 * 
	 * You can use only one outerJoin in a query, and must not call
	 * query::from or query::on methods.
	 * 
	 * This method is based on UNION, LEFT JOIN and RIGHT JOIN.
	 * 
	 * @access public
	 * @param mixed $table name of main table
	 * @param mixed $join name of table to join
	 * @param mixed $on1 field of main table to filter on
	 * @param mixed $on2 field of join table to filter on
	 * @return query $this for chaining
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
		
		$this->content['join']		= true;
		$this->table['join'][]		= $join;
		
		// small workaround
		return $this->from($subRequest1);
	}
	
	/**
	 * Specifies the common fields to filter a JOIN instruction.
	 * 
	 * @access public
	 * @param mixed $value1 field of first table
	 * @param mixed $value2 field of second table
	 * @return query $this for chaining
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
		$this->content['on']	= true;
		return $this;
	}

	/**
	 * Specify query filters
	 * 
	 * @access public
	 * @param mixed $field field to apply filter on
	 * @param string $calculator condition operator
	 * @param mixed $value value to test field with 
	 * @param string $calcul "where" or "or"
	 * @return query $this for chaining
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
	 * Adds a OR condition
	 * 
	 * @access public
	 * @param mixed $field field to apply filter on
	 * @param string $calculator condition operator
	 * @param mixed $value value to test field with 
	 * @return query $this pour assurer la chaînabilité de la classe
	 */
	public function either($field, $calculator, $value) {
		$this->where($field, $calculator, $value, 'OR');
		return $this;
	}

	
	/**
	 * Allow to do an update instead of insert in case of existing primary key
	 * 
	 * @access public
	 * @param mixed $field field to update
	 * @param mixed $value new value
	 * @return query $this for chaining
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
	 * Specify a sort filter and a sort order
	 * NB : you can use an array for field parameter, to specify table or more complex operations 
	 * Ex. array(array('table'=>'field'),'>',0) will order on result of [table_field > 0] expression @see query::where method
	 * 
	 * @access public
	 * @param mixed $field field name to sort by - default: ""
	 * @param string $order ASC or DESC - default: 'ASC'
	 * @return query $this for chaining
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
		elseif (is_array($field) && count($field)>1):
			
			$f = $this->getField($field[0]);
			$calculator = $field[1];
			$value = isset($field[2]) ? $field[2] : false;
			
			if(is_array($value)):
				if(array_key_exists('FUNCTION', $value)):
					// Utilisation d'une fonction sans argument comme paramètre à vérifier
				 $field = ' '.$f.' '.$calculator.' '.$value['FUNCTION'].'()';
				else:
					// Utilisation d'une fonction en temps qu'opérateur comme paramètre à vérifier
				 $field = ' '.$f.' '.$calculator.' ("'.implode('", "', $value).'")';
				endif;
			elseif(is_object($value)):
				$name = get_class($value);
				if($name == "query"):
					// Utilisation de sous-requêtes comme paramètre à vérifier
				 $field = ' '.$f.' '.$calculator.' ( '.$value->getRequest().')';
				else:
					// Utilisation d'un string sous forme d'object comme paramètre à vérifier
				 $field = ' '.$f.' '.$calculator.' "'.addslashes((string) $value).'"';
				endif;
			elseif($value === false):
				$field = ' '.$f.' '.$calculator;
			else:
				if(is_numeric($value)):
					// Utilisation d'un nombre comme paramètre à vérifier
				 $field = ' '.$f.' '.$calculator.' '.addslashes($value);
				else:
					// Utilisation d'un string comme paramètre à vérifier
				 $field = ' '.$f.' '.$calculator.' "'.addslashes($value).'"';
				endif;
			endif;
		else:
			$field = $this->getField($field);
		endif;
		
		$this->prepare_request .= ' '.$field.' '.$order.'';
		
		return $this;
	}

	
	/**
	 * Group lines according to a field
	 * 
	 * @access public
	 * @param mixed $field group lines according to this field 
	 * @return query $this for chaining
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
	 * Bounds results
	 * 
	 * @access public
	 * @param mixed $limit1 Starting bound
	 * @param bool $limit2 length (number of lines to return)
	 * @return query $this for chaining
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
	 * Finalise query and launch execution - @see query::sql
	 * After query::exec, results can be accessed by query::get
	 * (use query::next iterator if you didn't select FIRST result)
	 * or query::getArray
	 * 
	 * @access public
	 * @param string $which FIRST result or ALL - default: "ALL"
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
		
		// called here because of subqueries that are not "executed" on their own
		if (!self::$queryNumber):
			debug::timer('SQL call',true);
		endif;
		self::$queryNumber++;
		
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
			
			$which		= strtoupper($which);
			$this->result = $this->sql($this->prepare_request);
			$this->which	= $which;

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
	 * Can be used with flushSQL to flush only this request. @see flushSQL
	 * @return String|Boolean cache hash
	 */
	public function getCacheHash() {
		return $this->cacheHash;
	}
	
	/**
	 * Execute query and collect results
	 * 
	 * @access public
	 * @param mixed $req SQL string to execute
	 * @return void
	 */
	public function sql($req) {
		// On vérifie que jusque là, tout se passe bien
		if(DBHOST && !$this->error):
			try {
				$queryStart = microtime(true);
				if($this->content['select']):
					
					// cache category - file prefix
					$cachePref = ($this->cache===true?'':md5('prefix'.$this->cache));
										
					// Cache verification
					if($this->cache):
						// Already cached
						$file = CACHE.'/sql/'.$cachePref.md5($req).'.cache';
						$this->cached = false;
						
						// Cache exists and not too old
						if(file_exists($file) && (filemtime($file) > (time() - SQLCACHETIME)) ):
							$this->cached = true;
						endif;
					endif;
					
					if(!$this->cached):
						$return = $this->bdd->query($req);
						$return->setFetchMode(PDO::FETCH_ASSOC);
						
						$results = $return->fetchAll();
						
						// Must caching the request
						if($this->cache):
							$fileName = CACHE.'/sql/'.$cachePref.md5($req).'.cache';
							$file = fopen($fileName, 'w+');
							fwrite($file, serialize($results));
							fclose($file);
						endif;
					else:
						$results = unserialize(file_get_contents(CACHE.'/sql/'.$cachePref.md5($req).'.cache'));
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
				
				$queryEnd = microtime(true);
				
				if (!$this->cached && debug::$timeQueries):
					// send data to timer
					$nb = self::$queryNumber;
					$a = '<a href="#debugQuery'.$nb.'" onclick="$(\'#debugSQLContent\').show();">';
					
					debug::timer($a.'Query '.$nb.'</a>',true);
				endif;
				debug::sql($req, $this->count, $this->cached,$this->cache,$queryEnd-$queryStart);
				
				
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
	 * Result iterator
	 * Example of use : while($query->next()) { $query->get(); }
	 * 
	 * @access public
	 * @return Array|Boolean false if all results browsed, current line otherwise
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
	 * Returns the genrated SQL string
	 * 
	 * @access public
	 * @return String SQL string
	 */
	public function getRequest() {
		return $this->prepare_request;
	}

	
	/**
	 * Get a field (or all fields) of current line
	 * 
	 * @access public
	 * @param string $field specify a field, leave empty to get all - default: all
	 * @return Array|String Either value of specified field, either all field values
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
			 
			 foreach($array as $key => $value):
			 	if(is_array($value) && empty($value)):
			 		unset($array[$key]);
			 	endif;
			 endforeach;
                
			 foreach($this->alias as $alias):
			 	$array[$alias] = $this->line[$alias];
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
	 * Modify a line output
	 * 
	 * @access public
	 * @param mixed $field field to update
	 * @param String $value[optional] new value - default: empty string
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
	 * Number of lines affected by query
	 * 
	 * @access public
	 * @return Integer number of results
	 */
	public function count() {
		if(is_int($this->count)):
			return $this->count;
		else:
			return 0;
		endif;
	}

	
	/**
	 * Is the query OK (for select query only) ?
	 * 
	 * @access public
	 * @return Boolean true if OK, false if not
	 */
	public function ok() {
		if($this->content['select'] && is_int($this->count) && $this->count > 0 && !$this->error):
			return true;
		else:
			return false;
		endif;
	}

	
	/**
	 * Returns all results into a regular array
	 * Each result is an associative array with {fieldName => fieldValue} couples
	 * 
	 * @access public
	 * @return Array results
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
	 * Adds a raw SQL string to the query ; don't forget to wrap in spaces
	 * 
	 * @access public
	 * @param mixed $string raw SQL Strings
	 * @return query $this for chaining
	 */
	public function addString($string) {
		$this->prepare_request .= $string;
		return $this;
	}
	
	
	/**
	 * Paginate query according to URL
	 * 
	 * @access public
	 * @param mixed $get which "get" to use @see get
	 * @param mixed $results Number of result per page
	 * @param string $variable tpl var to assign results to
	 * @return query $this for chaining
	 */
	public function page($get, $results, $variable = "pagination", $variableCount = "count") {
		global $page;
		
		$this->sql($this->prepare_request);
		
		$nb = $this->count();
		
		$page->template($variableCount, $nb);
		
		$current = get($get);
		if($current == "index"):
			$current = 1;
		//elseif($current-1 > ($nb/$results)):
		//	$current = 1;
		endif;
		$place = ($current-1)*$results;
		$this->Limit($place, $results);
		
		
		$text['page']	= $current;
		$text['total'] = ceil($nb / $results);
		
		if($current > 1):
			$start  = '<li class="pageStart"><a href="'.get($get, 1).'" class="start">'.lang::text('pagination:start').'</a></li>';
			$prev	= '<li class="pagePrev"><a href="'.get($get, $current-1).'" class="prev">'. lang::text('pagination:prev') .'</a></li>';
		else:
			$start  = '<li class="disabled pageStart"><a href="'.get().'" class="start">'.lang::text('pagination:start').'</a></li>';
			$prev	= '<li class="disabled pagePrev"><a href="'.get().'" class="prev">'. lang::text('pagination:prev') .'</a></li>';
		endif;
		
		if($current < ceil($nb / $results)):
			$next	= '<li class="pageNext"><a href="'.get($get, $current+1).'" class="next">'. lang::text('pagination:next') .'</a></li>';
			$end	= '<li class="pageEnd"><a href="'.get($get, ceil($nb / $results)).'" class="end">'.	lang::text('pagination:end')	.'</a></li>';
		else:
			$end	= '<li class="disabled pageNext"><a href="'.get().'" class="next">'. lang::text('pagination:next') .'</a></li>';
			$next	= '<li class="disabled pageEnd"><a href="'.get().'" class="end">'.	lang::text('pagination:end')	.'</a></li>';
		endif;
		
		$center = '<li class="disabled pageCount"><a href="'.get().'">'.lang::text('pagination:page', $text).'</a></li>';

		$page->template($variable, '<ul class="pager">'.$start.$prev.$center.$next.$end.'</ul>');
		
		return $this;
	}

	
	/**
	 * Send query data to debug bar
	 * 
	 * @access public
	 * @return void
	 */
	public function debug() {
		$debug = array();
		$debug['request']	= $this->prepare_request;
		$debug['count']	= $this->count;
		$debug['cache']	= $this->cache;
		$debug['cached']	= $this->cached;
		
		if($this->ok() && $this->which == "ALL"):
			$debug['results'] = $this->getArray();
		elseif($this->ok() && $this->which == "FIRST"):
			$debug['results'] = $this->get();
		endif;
		
		debug::dump($debug, lang::text('sql:request'));
	}

	/**
	 * (Re-)init the query object
	 * @return void
	 */
	public function reset() {
		$this->content['select']			= false;
		$this->content['from']				= false;
		$this->content['where']			 	= false;
		$this->content['limit']			 	= false;
		$this->content['orderBy']			= false;
		$this->content['groupBy']			= false;
		$this->content['join']				= false;
		$this->content['on']				= false;
		$this->content['select']			= false;
		$this->content['delete']			= false;
		$this->content['insert']			= false;
		$this->content['set']				= false;
		$this->content['update']			= false;
		$this->content['onDuplicateKeyUpdate'] = false;
		$this->content['countOn']				= 0;

		if(!isset($this->table['select'])):
			$this->table['select']	= '';
		endif;

		$this->table['join']	= array();
		$this->table['set']	 = '';
		$this->table['delete']	= '';
		$this->line			= array();
		$this->prepare_request	= '';
		$this->count			= 0;
		$this->duplicate		= '';
	}
	
}

