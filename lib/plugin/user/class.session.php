<?php
class session {
	private $user;
	/*
	 * Méthode     : __construct
	 * Description : Constructeur de la classe session
	 * Paramètre   : Aucun
	 * Retour      : Aucun
	 */
	public function __construct() {
		// Si l'utilisateur est déjà connecté, on instancie une classe user avec les informations contenues dans sa session
		if(isset($_SESSION['user']) && is_array($_SESSION['user']) && array_key_exists('id', $_SESSION['user'])):
			$this->user = new user($_SESSION['user']);
			
			// Mise à jour de la session après le délai dépassé
			if(isset($_SESSION['user']['login']) && ($_SESSION['user']['login']+SESSION_REFRESH) < time()):
				$this->login($_SESSION['user']['id']);
			endif;
		// Si l'utilisateur n'est pas connecté, on instancie un nouvel utilisateur vide
		else:
			$this->user = new user();
		endif;
	}
	
	/*
	 * Méthode     : isOnline
	 * Description : Précise si l'utilisateur est connecté ou non
	 * Paramètres  : Aucun
	 * Retour      :
	 *     - (bool) "true"  : L'utilisateur est connecté
	 *     - (bool) "false" : L'utilisateur n'est pas connecté
	 */
	public function isOnline() {
		// Une session existe
		if(isset($_SESSION['user']) && is_array($_SESSION['user']) && array_key_exists('id', $_SESSION['user'])):
			// l'utilisateur est connecté
			return true;
		
		// Aucune session n'est trouvée
		else:
			// L'utilisateur n'est pas connecté
			return false;
		endif;
	}
	
	/*
	 * Méthode : get
	 * Description : Récupère la valeur d'un champs de l'utilisateur connecté
	 * Paramètre :
	 *     [$key] - (string) : Nom du champs utilisateur
	 * Retour :
	 *     - (bool) "false" : le champs n'existe pas
	 *     - (string)       : Valeur du champs
	 *     - (array)        : Tableau associatif complet des champs de l'utilisateur connecté
	 */
	public function get($key = false) {
		return $this->user->get($key);
	}
	
	/*
	 * Méthode : set
	 * Description : Modification  d'un champs de l'utilisateur connecté
	 * Paramètre :
	 *     $key   - (string) : Nom du champs utilisateur
	 *     $value - (string) : Valeur du champs utilisateur
	 * Retour : Aucun
	 */
	public function set($key, $value) {
		$this->user->set($key, $value);
	}
	
	/*
	 * Méthode : save
	 * Description : Sauvegarde les modifications apportées à l'utilisateur
	 * Paramètre : Aucun
	 * Retour :
	 *     - (bool) "false" : Sauvegarde échouée
	 *     - (bool) 'true"  : Sauvegarde réussie
	 */
	public function save() {
		if($this->isOnline()):
			$this->user->save();
			$this->login($this->get('id'));
			return true;
		else:
			return false;
		endif;
	}
	
	/*
	 * Méthode     : option
	 * Description : Retourne ou modifie les paramètres personnalisés de l'utilisateur connecté.
	 * Paramètres  :
	 *     $key     - (string) : Nom de l'option
	 * 	   [$value] - (string) : Valeur de l'option.
	 * Retour      :
	 *     - (bool) "true"  : l'option a été créée
	 *     - (bool) "false" : l'option n'existe pas
	 *     - (string)       : Valeur l'option dans la BDD si le paramètre $value n'est pas renseignée lors de l'appel de la méthode.
	 */
	public function option($key, $value = false) {
		if($this->isOnline()):
			return $this->user->option($key, $value);
		else:
			return false;
		endif;
	}
	
	/*
	 * Méthode     : sign
	 * Description : Inscrit l'utilisateur connecté dans la base de donnée puis le connecte.
	 * Paramètres  :
	 *     [$require] - (array) : Tableau des champs requis à l'inscription. Si le champs se trouve être un tableau associatif, alors la clef est le nom du champs, sa valeur représente le motif à vérifier avec la fonction "preg"
	 * Retour      :
	 *     - (bool) "true"  : L'utilisateur a été créé et connecté
	 *     - (bool) "false" : Erreur lors de l'inscription, utilisateur déjà connecté
	 *     - (string)       : Erreur lors de l'inscription, le champs posant problème est retourné.
	 */
	public function sign($require = false) {
		// Si l'utilisateur est déjà en ligne
		if($this->isOnline()):
			return false;
			
		// Si l'utilisateur peut s'inscrire
		else:
					
			if(is_array($require)):
				foreach($require as $key => $value):
					// Vérification des champs
					if(!preg($key, $value)):
						return $key;
					endif;
				endforeach;
			endif;
			
			// Si l'inscription a échouée, on retourne une erreur inconnue
			if(!$this->user->save()):
				return false;
			endif;
			
			// On log l'utilisateur
			$_SESSION['user'] = $this->user->get();
			return true;
		endif;
	}

	/*
 	 * Méthode     : login
	 * Description : Connecte l'utilisateur
	 * Paramètre   :
	 *     $login - (string) : Username de l'utilisateur
	 * 			  - (array)  : Tableau des champs de l'utilisateur
	 * 	  [$pass] - (string) : Mot de passe de l'utilisateur
	 * Retour      :
	 *     - (bool) "true"   : L'utilisateur est maintenant connecté
	 *     - (bool) "false"  : L'utilisateur est inconnu
	 *     
 	 */
 	public function login($login, $pass = false) {
 		
 		// Si l'utilisateur est loggué avec son ID
 		if(is_numeric($login) && !$pass):
			$this->user = new user($login);
			if($this->user->exists()):
				$_SESSION['user'] = $this->user->get();
			else:
				return false;
			endif;
			
		// Si l'utilisateur a demandé sa connexion avec un login et un mot de passe
		else:
			
			$query = new query();
			$query->select()
				  ->from('user')
				  ->where('username', '=', $login)
				  ->where('password', '=', md5($pass))
				  ->exec('FIRST');
				  
			if($query->ok()):
				$this->user       = new user($query->get());
				$_SESSION['user'] = $this->user->get();
			else:
				return false;
			endif;
		endif;
		
		$_SESSION['user']['login'] = time();
		return true;
		
 	}
	
	/*
 	 * Méthode     : logout
	 * Description : Déconnecte l'utilisateur
	 * Paramètre   : Aucun
	 * Retour      : Aucun
 	 */
	public function logout() {
		$_SESSION["user"] = false;
	}

}
?>