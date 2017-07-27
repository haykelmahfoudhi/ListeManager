<?php

class SessionAPI {
	
	/**
	 * @var Dataabse $_db la base de données utilisée pour cette session.
	 */
	private $_db;
	
	/**
	 * Instancie une nouvelle session pour l'API.
	 * Démarrre la session et récupère l'objet Database si déjà connecté.
	 * @throws Exception si les paramètres de session du client sont invalides.
	 */
	public function __construct(){
		// Démarre la session
		session_start();
		if (!isset($_SESSION['id_client'])) {			
			// Configuration de la sesison
			session_regenerate_id(true);
			ini_set('session.cookie_secure', true);		// Les cookies ne sont émis que sur des connexions sécurisées
			ini_set('session.cookie_httponly', true);	// Les cookies ne sont accessibles que via HTTP
			ini_set('session.cookie_lifetime', 3600);	// Durée de vie d'une session : 1 heure

			// Enregistrement de l'adresse IP cliente : anti-vol de session
			$_SESSION['id_client'] = hash('sha512', $this->getClientIPs().session_id());
		}
		
		// Si Database enregistrée dans la session => enregistrement
		if(isset($_SESSION['api_db'])){
			$this->_db = $_SESSION['api_db'];
		}
		else {
			$this->_db = null;
		}
	}
	
	/**
	 * Retourne les adresses IP du client séparées par un underscore ainsi que son USER AGENT.
	 * Les adresses IP clientes correspondent aux champs REMOTE_ADDR HTTP_X_FORWARDED_FOR et HTTP_CLIENT_IP de $_SERVER.
	 * @return string les adresses IP du client.
	 */
	private function getClientIPs(){
		$ip = $_SERVER['REMOTE_ADDR'];
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip .= '_'.str_replace(',', '_', $_SERVER['HTTP_X_FORWARDED_FOR']);
		}
		if (isset($_SERVER['HTTP_CLIENT_IP'])) {
			$ip .= '_'.$_SERVER['HTTP_CLIENT_IP'];
		}
		if(isset($_SERVER['HTTP_USER_AGENT'])){
			$ip .= '_'.$_SERVER['HTTP_USER_AGENT'];
		}
		return $ip;
	}
	
	/**
	 * Détruit la sessions en cours et détruit l'objet.
	 */
	public function destroy(){
		session_unset();
		session_destroy();
	}

	/**
	 * Détermine si l'utilisateur et sa Database sont connectés.
	 * @return bool
	 */
	public function isStarted(){
		return $this->_db != null && isset($_SESSION['api_db'])
			&& $_SESSION['id_client'] === hash('sha512', $this->getClientIPs().session_id());
	}

	/**
	 * Enregistre l'objet Database dans la session.
	 * @param Database $db la abse de données à enregistrer.
	 */
	public function setDatabase(Database $db){
		$this->_db = $db;
		$_SESSION['api_db'] = $db;
	}
	
	/**
	 * Retourne l'objet Database enregistré dans la session, ou null sinon.
	 * @return Database|null l'objet Database de cette session.
	 */
	public function getDatabase(){
		return $this->_db;
	}

}

?>