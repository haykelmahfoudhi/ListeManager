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
	private $label;
	private static $instances = array();
	
	
		/***********************
		***   CONSTRUCTEUR   ***
		***********************/
	
	/**
	 * Instancie la connexion avec la base de donnees via un objet PDO contenu dans l'objet Database
	 * @param string $dsn le DSN (voir le manuel PHP concernant PDO)
	 * @param stirng $login le nom d'utilisateur pour la connexion
	 * @param stirng $passwd son mot de passe
	 */
	private function __construct($dsn, $login, $passwd, $label) {
		$this->label = $label;
		try {
			$this->pdo = new PDO($dsn, $login, $passwd);
		}
		catch (Exception $e) {
			$this->pdo = null;
			echo "<br><b>[!]</b>Connection a la base de donnees impossible :\n".$e->getMessage()
				.'<br>';
		}
	}
	

			/*******************
			***   METHODES   ***
			*******************/

	/**
	 * Instancie une nouvelle connexion avec la base de donnees via un objet PDO
	 * @param string $dsn le DSN de la connection (voir le manuel PHP concernant PDO)
	 * @param string $login le nom d'utilisateur de la BD
	 * @param string $mdp le mot de passe de l'utilisateur
	 * @param string $etiquette (facultatif) l'etiquette de la base de donnees, utile si plusieurs bases de donnees sont
	 * utilisees en meme temps dans l'application
	 * @return Database : l'instance de Database cree et connecte, ou null en cas d'echec.
	 */
	public static function instantiate($dsn, $login, $mdp, $etiquette='principale'){
		$nouvelleInstance = new self($dsn, $login, $mdp, $etiquette);
		if($nouvelleInstance->pdo == null)
			return null;

		if(isset(self::$instances[$etiquette])){
			echo '<br><b>[!]</b>Database::instancier() : Il existe deje une BD portant l\'etiquette "'
				.$etiquette.'", veuillez en specifier une nouvelle<br>';
			return null;
		}
		self::$instances[$etiquette] = $nouvelleInstance;
		return $nouvelleInstance;
	}
	
	/**
	 * Execute la requete SQL passee en parametres
	 * @param mixed $request la requete SQL a executer, peut etre string ou objet SQLRequest
	 * @return RequestResponse : la reponse de la requete, ou faux si la BD n'est pas connectee
	 */
	public function execute($request){
		if($this->pdo == null)
			return false;

		//On transforme l'objet SQLRequest en string
		if($request instanceof SQLRequest)
			$request = $request->__toString();

		//Execution de la requete
		$statement = $this->pdo->query($request);
		if($statement == false)
			return new RequestResponse(null, true,$this->pdo->errorInfo()[2]);
		else 
			return new RequestResponse($statement);
	}
	
	
			/******************
			***   GETTERS   ***
			******************/
	
	/**
	 * Retourne une instance de la base de donnees de l'application
	 * @param int $etiquette : l'etiquette de la base de donnees. Par defaut retourne la principale
	 * @return Database : l'instance de Database ( ! peut retourner null si erreur)
	 */
	public static function getInstance($etiquette='principale'){
		if(!isset(self::$instances[$etiquette]))
			return null;

		return self::$instances[$etiquette];
	}

	/**
	* Fonction de debug
	* @return PDO : l'objet PDO de contenu dans cette instance de Database.
	*/
	public function getPDO(){
		return $this->pdo;
	}

	/**
	* @return string l'etiquette de la base de donnees
	*/
	public function getLabel(){
		return $this->label;
	}

	/**
	* Definit une nouvelle etiquette pour la base de donnees.
	* Cette nouvelle etiquette ne doit pas etre deje utilisee par une autre base de donnees
	* @param string $nouvEtiquette la nouvelle etiquette de la base de donnees
	* @return boolean vrai si la BD a ete renommee, faux sinon
	*/
	public function setLabel($nouvEtiquette){
		if($nouvEtiquette == null || isset(self::$instances[$nouvEtiquette]))
			return false;

		self::$instances[$nouvEtiquette] = $this;
		unset(self::$instances[$this->label]);
		$this->label = $nouvEtiquette;
	}

}




?>