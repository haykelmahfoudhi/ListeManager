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
	 * Instancie la connexion avec la base de donn�es via un objet PDO contenu dans l'objet Database
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
			echo "<br><b>[!]</b>Connection � la base de donn�es impossible :\n".$e->getMessage()
				.'<br>';
		}
	}
	

			/*******************
			***   METHODES   ***
			*******************/

	/**
	 * Instancie une nouvelle connexion avec la base de donn�es via un objet PDO
	 * @param string $dsn le DSN de la connection (voir le manuel PHP concernant PDO)
	 * @param string $login le nom d'utilisateur de la BD
	 * @param string $mdp le mot de passe de l'utilisateur
	 * @param string $etiquette (facultatif) l'etiquette de la base de donn�es, utile si plusieurs bases de donn�es sont
	 * utilis�es en m�me temps dans l'application
	 * @return Database : l'instance de Database cr�� et connect�, ou null en cas d'echec.
	 */
	public static function instantiate($dsn, $login, $mdp, $etiquette='principale'){
		$nouvelleInstance = new self($dsn, $login, $mdp, $etiquette);
		if($nouvelleInstance->pdo == null)
			return null;

		if(isset(self::$instances[$etiquette])){
			echo '<br><b>[!]</b>Database::instancier() : Il existe d�j� une BD portant l\'�tiquette "'
				.$etiquette.'", veuillez en sp�cifier une nouvelle<br>';
			return null;
		}
		self::$instances[$etiquette] = $nouvelleInstance;
		return $nouvelleInstance;
	}
	
	/**
	 * Ex�cute la requete SQL pass�e en param�tres
	 * @param mixed $request la requete SQL � ex�cuter, peut �tre string ou objet SQLRequest
	 * @return RequestResponse : la r�ponse de la requete, ou faux si la BD n'est pas connect�e
	 */
	public function execute($request){
		if($this->pdo == null)
			return false;

		//On transforme l'objet SQLRequest en string
		if($request instanceof SQLRequest)
			$request = $request->__toString();

		//Ex�cution de la requ�te
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
	 * Retourne une instance de la base de donn�es de l'application
	 * @param int $etiquette : l'etiquette de la base de donn�es. Par d�faut retourne la principale
	 * @return Database : l'instance de Database ( ! peut retourner null si erreur)
	 */
	public static function getInstance($etiquette='principale'){
		if(!isset(self::$instances[$etiquette]))
			return null;

		return self::$instances[$etiquette];
	}

	/**
	* Fonction de d�bug
	* @return PDO : l'objet PDO de contenu dans cette instance de Database.
	*/
	public function getPDO(){
		return $this->pdo;
	}

	/**
	* @return string l'etiquette de la base de donn�es
	*/
	public function getLabel(){
		return $this->label;
	}

	/**
	* D�finit une nouvelle �tiquette pour la base de donn�es.
	* Cette nouvelle �tiquette ne doit pas �tre d�j� utilis�e par une autre base de donn�es
	* @param string $nouvEtiquette la nouvelle �tiquette de la base de donn�es
	* @return boolean vrai si la BD a �t� renomm�e, faux sinon
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