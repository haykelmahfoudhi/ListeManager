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
 * 
 * @author RookieRed
 *
 */
class Database {
	

			/********************
			***   ATTRIBUTS   ***
			********************/

	private $pdo;
	private static $instance = null;
	
	
		/***********************
		***   CONSTRUCTEUR   ***
		***********************/
	
	/**
	 * Instancie la connexion avec la base de donn�es via un objet PDO contenu dans l'objet Database
	 * @param string $dsn le DSN (voir le manuel PHP concernant PDO)
	 * @param stirng $login le nom d'utilisateur pour la connexion
	 * @param stirng $mdp son mot de passe
	 */
	private function __construct($dsn, $login, $mdp){
		try {
			$this->pdo = new PDO($dsn, $login, $mdp);
			
		} catch (Exception $e) {
			$this->pdo = null;
			echo "Connection � la base de donn�es impossible :\n".$e->getMessage();
		}
	}
	

			/*******************
			***   METHODES   ***
			*******************/

	/**
	 * Instancie une nouvelle connexion avec la base de donn�es via un objet PDO contenu dans l'objet Database
	 * @param string $dsn le DSN (voir le manuel PHP concernant PDO)
	 * @param stirng $login le nom d'utilisateur pour la connexion
	 * @param stirng $mdp son mot de passe
	 * @return Database : l'instance de Database cr�� et connect�, ou null en cas d'echec.
	 */
	public static function connecter($dsn, $login, $mdp){
		self::$instance = new self($dsn, $login, $mdp);
		return self::$instance;
	}
	
	/**
	 * Ex�cute la requete SQL pass�e en param�tres
	 * @param mixed $requete la requete SQL � ex�cuter, peut �tre string ou objet RequeteSQL
	 * @return ReponseRequete : la r�ponse de la requete, ou faux si la BD n'est pas connect�e
	 */
	public function executer($requete){
		if($this->pdo == null)
			return false;

		//On transforme la RequeteSQL en string
		if($requete instanceof RequeteSQL)
			$requete = $requete->__toString();

		//Ex�cution de la requ�te
		$statement = $this->pdo->query($requete);
		if($statement == false)
			return new ReponseRequete($statement, true,
				self::$instance->errorInfo()[2]);
		else 
			return new ReponseRequete($statement);
	}
	
	
			/******************
			***   GETTERS   ***
			******************/
	
	/**
	 * Retourne la seule instance de la base de donn�es de l'application
	 * @return Database : l'instance de Database ( ! peut retourner null si mal connect�)
	 */
	public static function getIstance(){
		return self::$instance;
	}

	/**
	* Fonction de d�bug
	* @return PDO : l'instance de l'objet PDO de cette Database
	*/
	public function getPDO(){
		return $this->pdo;
	}

}




?>