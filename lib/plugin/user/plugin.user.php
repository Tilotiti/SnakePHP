<?php
/**
 * User management
 * @author Tilotiti
 */
class user {
    public
	/**
	 * Contains user data
	 * @var Array
	 */
	$field  = array(),
	/**
	 * List of modified options
	 * @var Array
	 */
	$change = array(),
	/**
	 * List of new options
	 * @var Array
	 */
	$create = array(),
	/**
	 * Contains user options
	 * @var Array
	 */
	$option = array(),
	/**
	 * True if user exists in database
	 * @var Boolean
	 */
	$ok     = false;
	
	/**
	 * User constructor
	 * @param Array|Boolean|Integer [$params] user ID or user data (associative array), or false (void user) - default: false
	 * @return void
	 */
    public function __construct($params = false) {
	
		// Si un id est renseigné, on va chercher les informations correspondantes à l'utilisateur
		if(is_numeric($params)):
		    $user = new query();
		    $user->select()
		    	 ->from('user')
		    	 ->where('id', '=', $params)
		    	 ->exec("FIRST");

	    if($user->ok()):
				$this->ok     = true;
				$this->field  = $user->get();
	    endif;
			
		// Si un tableau est renseigné, on va charger le tableau comme informations utilisateur
		elseif(is_array($params)):
			// L'id de l'utilisateur n'est pas optionnel
		    if(array_key_exists('id', $params)):
				$this->ok    = true;
				$this->field = $params;
		    endif;
		endif;
    }

	/**
	 * Save the user instance in database
	 * @return Boolean true if successfully saved, false otherwise
	 */
    public function save() {
	
		// Si l'utilisateur existe, on le met à jour
		if($this->ok()):
			
			// Mise à jour de l'utilisateur
			$user = new query();
			$user->update('user');
		
			foreach($this->field as $key => $value):
				switch($key):
					case "id":
					case "login":
					// On ne modifie jamais l'ID de l'utilisateur et on enregistre pas le temps de mise à jour de la session s'il y en a une.
					break;
					default:
						// On modifie la ligne
						$user->set($key, trim($value));
					break;
				endswitch;
			endforeach;
		
			$user->where('id', '=', $this->get('id'));
			$user->exec();
				
			//Mise à jour des options
			foreach($this->create as $create):
				$query = new query();
				$query->insert('user_option')
					  ->set('key', $create)
					  ->set('value', $this->option[$create])
					  ->set('owner', $this->get('id'))
					  ->exec();
			endforeach;
			
			foreach($this->change as $change):
				$query = new query();
				$query->update('user_option')
					  ->set('value', $this->option[$change])
					  ->where('owner', '=', $this->get('id'))
					  ->where('key', '=', $change)
					  ->exec();
			endforeach;
		
			return true;
		
		// Si l'utilisateur n'existe pas, impossible de le mettre à jour, on retourne une erreur.
		else:
		
			$user = new query();
			$user->insert('user')
			     ->set('username', $this->get('username'));
					 
			$id = $user->exec();
		
			$this->set('id', $id);
			$this->ok = true;
			
			$this->save();
			return true;
		endif;

    }
	
	/**
	 * 
	 * Description : Retourne ou modifie les paramètres personnalisés de l'utilisateur.
	 * Paramètres  :
	 *     [$key]   - (string) : Nom de l'option
	 * 	   [$value] - (string) : Valeur de l'option.
	 * Retour      :
	 *     - (bool) "true"  : l'option a été créée
	 *     - (bool) "false" : l'option n'existe pas
	 *     - (array)	: Tableau associatif de toutes les fonctions si la méthode est appelée sans aucun paramètre
	 *     - (string)       : Valeur l'option dans la BDD si le paramètre $value n'est pas renseignée lors de l'appel de la méthode.
	 */
    public function option($key = false, $value = false) {
		if(count($this->option) == 0):
		    $req = new query();
		    $req->select('key','value')
				->from("user_option")
				->where('owner', '=', $this->get('id'))
				->exec("ALL");
	
		    while($result = $req->next()):
				$this->option[$req->get("key")] = $req->get("value");
		    endwhile;
		endif;
	
		if(!$key):
			return $this->option;
		elseif(!$value):
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


	/*
	 * Méthode : get
	 * Description : Récupère la valeur d'un champs de l'utilisateur
	 * Paramètre :
	 *     [$key] - (string) : Nom du champs utilisateur
	 * Retour :
	 *     - (bool) "false" : le champs n'existe pas
	 *     - (string)       : Valeur du champs
	 *     - (array)	: Tableau associatif complet des champs de l'utilisateur
	 */
    public function get($key = false) {
		if($key !== false):
		    if(array_key_exists($key, $this->field)):
				return $this->field[$key];
		    else:
				return false;
		    endif;
		else:
		    return $this->field;
		endif;
    }
	
	/*
	 * Méthode : set
	 * Description : Modification  d'un champs utilisateur
	 * Paramètre :
	 *     $key - (string)   : Nom du champs utilisateur
	 *     $value - (string) : Valeur du champs utilisateur
	 * Retour : Aucun
	 */
    public function set($key, $value) {
		$this->field[$key] = $value;
		return true;
    }
	
	/*
	 * Méthode : ok
	 * Description : Indique si l'utilisateur instancié existe ou non
	 * Paramètre : Aucun
	 * Retour : 
	 *     - (bool) "true"  : L'utilisateur existe
	 *     - (bool) "false" : L'utilisateur n'existe pas
	 */
	public function ok() {
		return $this->ok;
	}

}
?>