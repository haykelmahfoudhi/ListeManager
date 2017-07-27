<?php

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
	 * @return bool false si non connecté, true si ok
	 */
	public function saveDatabaseConf(){
		if(!$this->isConnected())
			return false;
		
		$infos = $this->_session->getDatabaseInfo();
	}

	/**
	 * Instancie l'attribut ListManager de l'objet API.
	 * @param Database $db la base de données à utiliser par ListManager.
	 */
	private function setListManager(Database $db){
		$this->_lm = new ListManager('', $db, [ListManager::NO_VERBOSE]);
		$this->_lm->setResponseType(ResponseType::JSON);
	}
	
	/**
	 * Récupère les données de la Database passée en paramètre, construit l'objet et le retourne.
	 * @param string $label l'etiquette de la Database à récupérer.
	 * @return Database|null l'instance de Database correspodnante ou null en cas d'erreur
	 * @throws Exception si le fichier de configuration n'existe pas / est illisible
	 */
	private function getDatabaseFromConf($label){
		if(!file_exists(self::$dbConf))
			throw new Exception('Fichier de configuration inexistant');
		
		// Récupération du contenu du fichier
		$json = file_get_contents(self::$dbConf);
		$dbs = json_decode($json);
		if($dbs == null)
			throw new Exception('Fichier de configuration non valide');
		
		if(property_exists($dbs, $label)){
			$dbInfo = $dbs->$label;
			// Si l'attribut dsn n'est pas présent : execption
			if(!isset($dbInfo->dns)){
				throw new Exception('Fichier de configuration non valide');
			}
			
			// Instanication de Database
			$dbInfo->user = ( (isset($dbInfo->user)) ? $dbInfo->user : '' );
			$dbInfo->pass = ( (isset($dbInfo->pass)) ? $dbInfo->pass : '' );
			return Database::instantiate($dbInfo->dns, $dbInfo->user, $dbInfo->pass);
		}
		else {
			return null;
		}
	}

}

?>