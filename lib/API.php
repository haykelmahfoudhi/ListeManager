<?php

/**
 * Gere les sessions de l'API de ListManager.
 * 
 * **Fonctionnement :** 
 * 
 *  * 1. Connexion de l'utilisateur à une BdD : recupération du DSN + USER + MDP dans la requête de connexion OU étiquette d'une BdD
 *      se trouvant dans le fichier databases.json. Renvoie de la réponse : true / false selon si connecté ou non. 
 *      Si connecté : enregistrement de la session et instanciation de ListManager
 *  * 2. Exécution de la requêtes SQL, et retour sous format JSON (par défaut), TABLEAU, EXCEL ou TEMPLATE (indiqué par 'type').
 *  * 3. Sauvegarde des données de connexion dans un fichier de configuration pour y acceder plus facilement par la suite
 *  * 4. Déconnexion de la base de données.
 * 
 * **Format de réponse :**
 * 
 * *Objet JSON* avec 3 attributs : 
 *  * error : bool true si erreur dans la requete
 *  * errorMessage : ?string message associé à l'erruer, null si error == true
 *  * data : ?array les données renvoyées par l'API
 * 
 * Possibilité de générer un array PHP seul encodé en JSON (type=tableau), un fichier Excel qui se téléchargera, ou d'afficher la liste.
 * 
 * @author RookieRed
 *
 */
class API  {
	
	/**
	 * @var ListManager $_lm pour l'exécution des requêtes. Type de réponse : JSON
	 */
	private $_lm;
	/**
	 * @var SessionAPI $_session gère la connexion et la sessions avec l'utilisateur
	 */
	private $_session;
	/**
	 * @var string $_lastError contient al dernière erreur générée par l'execution de l'API
	 */
	private $_lastError;
	/**
	 * @var stirng $dbConf chemin vers le fichier json contenant les dabatases préconfigurées
	 */
	private static $dbConf = LM_SRC.'dbs.json';
	
	/**
	 * Construit un nouvel objet API.
	 * Si la session est déjà démarrée on récupère son objet Database et instancie ListManager.
	 */
	public function __construct(){
		$this->_session = new SessionAPI();
		$this->_lm = null;
		$this->_lastError = '';
		
		// Si la session a déjà été lancée : récupération de Database et construction de _lm
		if($this->_session->isStarted()){
			$db = $this->_session->getDatabase();
			$this->setListManager($db);
		}
	}
	
	/**
	 * Connexion à une Database et instanciation de la session.
	 * 2 méthodes de connexion possible : soit en indiquant le DSN (+ user + pass facultatifs) soit en spécifiant l'étiquette
	 * d'une Database déjà existante dans le fichier dbs.json
	 * @param string $dsn DSN de la base de données
	 * @param string $user utilisateur de la base de données
	 * @param string $pass mot de passe
	 * @param string $label étiquette de la base de données
	 * @return boolean true si connecté, false sinon
	 * @throws Exception si le fichier de configuration des bases de données n'existe pas / est illisible
	 */
	public function connect($dsn, $user, $pass, $label){
		if($this->isConnected())
			$this->disconnect();
		
		$db = null;
		// Instanciation d'une database
		if(strlen($dsn)){
			try {
				$db = Database::instantiate($dsn, $user, $pass);
			}
			catch (InvalidArgumentException $e){
				$this->_lastError = $e->getMessage();
				return false;
			}
		}
		else if(strlen($label)){
			$db = $this->getDatabaseFromConf($label);
		}
		
		if($db != null){
			$this->setListManager($db);
			// Enregistrement dans la session
			$this->_session->setDatabase($db);
			return true;
		}
		$this->_lastError = 'Impossible de se connecter à la base de données';
		return false;
	}
	
	/**
	 * Déconnecte de la Database et détruit la session en cours.
	 */
	public function disconnect(){
		$this->_session->destroy();
		$db = $this->_session->getDatabase();
		if($db == null)
			return;
		
		$db->setLabel(uniqid());
		$this->_lm = null;
		$this->_lastError = '';
	}

	/**
	 * Exécute une requete SQL via ListManager.
	 * @param string $sql la requête à exécuter
	 * @param array $params paramètres utilisateurs ( @see ListManager::execute() )
	 * @return string réponse sous format JSON
	 */
	public function execute($sql, array $params=[]){
		if(!$this->isConnected())
			return '{error: true, errorMessage: "Non connecté, veuillez instancier une session", data: null}';
		
		return $this->_lm->construct($sql, $params);
	}
	
	/**
	 * Détermine si la session API est lancée.
	 * @return boolean true si l'utilisateur est connecté et la sessiosn lancée, false sinon.
	 */
	public function isConnected(){
		return $this->_lm != null && $this->_session->isStarted();
	}

	/**
	 * Retourne la dernière erreur enregistrée.
	 * @return string derniere erreur enregistrée.
	 */
	public function getLastError(){
		return $this->_lastError;
	}
	
	/**
	 * Inscrit les données de la Database utilisée dans le fichier de conf.
	 * @param string $label l'etiquette sous laquelle on enregistre la base de données.
	 * @return bool false si non connecté + message d'erreur dans _lastError, true si ok
	 */
	public function saveDatabaseConf($label){
		if(!$this->isConnected()){
			$this->_lastError = 'Non connecté';
			return false;
		}
		if(!strlen($label)){
			$this->_lastError = 'Veuillez spécifier un nom pour enregistrer la base de données';
			return false;
		}
		
		// Récupération du fichier
		try{
			$dbs = $this->getAllDatabasesFromConf();
		} catch(Exception $e){
			$dbs = new stdClass();
		}
		$db = $this->_session->getDatabase();
		if(isset($dbs->$label)){
			$this->_lastError = 'Il existe déjà une base de données enregistrée sous le nom "'.$label.'"';
			return false;
		}
		
		// Enregistrement des infos
		$dbs->$label = $db;
		return true;
	}

	/**
	 * Définit le type de réponse renvoyé par ListManager.
	 * @param int $type une des constante de l'enumération ResponseType
	 * @return bool false si type non reconnu ou non connecté, true sinon
	 */
	public function setResponseType($type){
		if(!$this->isConnected() || !in_array($type, range(1, 5)))
			return false;

		return $this->_lm->setResponseType($type) === $this->_lm;
	}

	/**
	 * Instancie l'attribut ListManager de l'objet API.
	 * @param Database $db la base de données à utiliser par ListManager.
	 */
	private function setListManager(Database $db){
		$this->_lm = new ListManager('', $db, [ListManager::NO_VERBOSE, ListManager::NO_HELP_LINK]);
		$this->_lm->setResponseType(ResponseType::JSON);
	}
	
	/**
	 * Récupère les données de la Database passée en paramètre, construit l'objet et le retourne.
	 * @param string $label l'etiquette de la Database à récupérer.
	 * @return Database|null l'instance de Database correspodnante ou null en cas d'erreur
	 * @throws Exception si le fichier de configuration n'existe pas / est illisible
	 */
	private function getDatabaseFromConf($label){
		$dbs = $this->getAllDatabasesFromConf();
		if(isset($dbs->$label)){
			
			$dbInfo = $dbs->$label;
			// Si l'attribut dsn n'est pas présent : execption
			if(!isset($dbInfo->dsn)){
				throw new Exception('Fichier de configuration non valide');
			}
			// Instanciation de Database
			$dbInfo->login = ( (isset($dbInfo->login)) ? $dbInfo->login : '' );
			$dbInfo->passwd = ( (isset($dbInfo->passwd)) ? $dbInfo->passwd : '' );
			return Database::instantiate($dbInfo->dsn, $dbInfo->login, $dbInfo->passwd);
		}
		else {
			return null;
		}
	}
	
	/**
	 * Récupère toutes les infos des instances de Databases enregistrées dans le fichier conf.
	 * @throws Exception si le fichier de conf json n'existe pas ou n'est pas valide.
	 * @return stdObject contient toutes les Databases enregistrées.
	 */
	private function getAllDatabasesFromConf(){
		if(!file_exists(self::$dbConf))
			throw new Exception('Fichier de configuration inexistant');
		
		// Récupération du contenu du fichier
		$json = file_get_contents(self::$dbConf);
		$dbs = json_decode($json);
		if($dbs == null)
			throw new Exception('Fichier de configuration non valide');
		return $dbs;
		
	}

}

?>