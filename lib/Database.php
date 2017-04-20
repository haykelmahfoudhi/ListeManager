<?php 


/********************************************************************************************************
 **                                                                                                     **
 **    88888888ba,                                     88                                               **
 **    88      `"8b                 ,d                 88                                               **
 **    88        `8b                88                 88                                               **
 **    88         88  ,adPPYYba,  MM88MMM  ,adPPYYba,  88,dPPYba,   ,adPPYYba,  ,adPPYba,   ,adPPYba,   **
 **    88         88  ""     `Y8    88     ""     `Y8  88P'    "8a  ""     `Y8  I8[    ""  a8P_____88   **
 **    88         8P  ,adPPPPP88    88     ,adPPPPP88  88       d8  ,adPPPPP88   `"Y8ba,   8PP"""""""   **
 **    88      .a8P   88,    ,88    88,    88,    ,88  88b,   ,a8"  88,    ,88  aa    ]8I  "8b,   ,aa   **
 **    88888888Y"'    `"8bbdP"Y8    "Y888  `"8bbdP"Y8  8Y"Ybbd8"'   `"8bbdP"Y8  `"YbbdP"'   `"Ybbd8"'   **
 **                                                                                                     **
 **                                                                                                     **
 *********************************************************************************************************/


/**
 * << MULTITON >>
 * @author RookieRed
 *
 */
class Database {
	

			/********************
			***   ATTRIBUTS   ***
			********************/

	private $pdo;
	private $etiquette;
	private static $instances = array();
	
	
		/***********************
		***   CONSTRUCTEUR   ***
		***********************/
	
	/**
	 * Instancie la connexion avec la base de données via un objet PDO contenu dans l'objet Database
	 * @param string $dsn le DSN (voir le manuel PHP concernant PDO)
	 * @param stirng $login le nom d'utilisateur pour la connexion
	 * @param stirng $mdp son mot de passe
	 */
	private function __construct($dsn, $login, $mdp, $etiquette) {
		$this->etiquette = $etiquette;
		try {
			$this->pdo = new PDO($dsn, $login, $mdp);
		}
		catch (Exception $e) {
			$this->pdo = null;
			echo "<br><b>[!]</b>Connection à la base de données impossible :\n".$e->getMessage()
				.'<br>';
		}
	}
	

			/*******************
			***   METHODES   ***
			*******************/

	/**
	 * Instancie une nouvelle connexion avec la base de données via un objet PDO
	 * @param string $dsn le DSN de la connection (voir le manuel PHP concernant PDO)
	 * @param string $login le nom d'utilisateur de la BD
	 * @param string $mdp le mot de passe de l'utilisateur
	 * @param string $etiquette (facultatif) l'etiquette de la base de données, utile si plusieurs bases de données sont
	 * utilisées en même temps dans l'application
	 * @return Database : l'instance de Database créé et connecté, ou null en cas d'echec.
	 */
	public static function instancier($dsn, $login, $mdp, $etiquette='principale'){
		$nouvelleInstance = new self($dsn, $login, $mdp, $etiquette);
		if($nouvelleInstance->pdo == null)
			return null;

		if(isset(self::$instances[$etiquette])){
			echo '<br><b>[!]</b>Database::instancier() : Il existe déjà une BD portant l\'étiquette "'
				.$etiquette.'", veuillez en spécifier une nouvelle<br>';
			return null;
		}
		self::$instances[$etiquette] = $nouvelleInstance;
		return $nouvelleInstance;
	}
	
	/**
	 * Exécute la requete SQL passée en paramètres
	 * @param mixed $requete la requete SQL à exécuter, peut être string ou objet RequeteSQL
	 * @return ReponseRequete : la réponse de la requete, ou faux si la BD n'est pas connectée
	 */
	public function executer($requete){
		if($this->pdo == null)
			return false;

		//On transforme l'objet RequeteSQL en string
		if($requete instanceof RequeteSQL)
			$requete = $requete->__toString();

		//Exécution de la requête
		$statement = $this->pdo->query($requete);
		if($statement == false)
			return new ReponseRequete(null, true,$this->pdo->errorInfo()[2]);
		else 
			return new ReponseRequete($statement);
	}
	
	
			/******************
			***   GETTERS   ***
			******************/
	
	/**
	 * Retourne une instance de la base de données de l'application
	 * @param int $etiquette : l'etiquette de la base de données. Par défaut retourne la principale
	 * @return Database : l'instance de Database ( ! peut retourner null si erreur)
	 */
	public static function getInstance($etiquette='principale'){
		if(!isset(self::$instances[$etiquette]))
			return null;

		return self::$instances[$etiquette];
	}

	/**
	* Fonction de débug
	* @return PDO : l'objet PDO de contenu dans cette instance de Database.
	*/
	public function getPDO(){
		return $this->pdo;
	}

	/**
	* @return string l'etiquette de la base de données
	*/
	public function getEtiquette(){
		return $this->etiquette;
	}

	/**
	* Définit une nouvelle étiquette pour la base de données.
	* Cette nouvelle étiquette ne doit pas être déjà utilisée par une autre base de données
	* @param string $nouvEtiquette la nouvelle étiquette de la base de données
	* @return boolean vrai si la BD a été renommée, faux sinon
	*/
	public function setEtiquette($nouvEtiquette){
		if($nouvEtiquette == null || isset(self::$instances[$nouvEtiquette]))
			return false;

		self::$instances[$nouvEtiquette] = $this;
		unset(self::$instances[$this->etiquette]);
		$this->etiquette = $nouvEtiquette;
	}

}




?>