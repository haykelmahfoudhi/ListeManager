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
	
	
	//Attributs
	private $pdo;
	private static $instance = null;
	
	//Constructeur
	
	/**
	 * Instancie la connexion avec la base de données via un objet PDO contenu dans l'objet Database
	 * @param string $dsn le DSN (voir le manuel PHP concernant PDO)
	 * @param stirng $login le nom d'utilisateur pour la connexion
	 * @param stirng $mdp son mot de passe
	 */
	private function __construct($dsn, $login, $mdp){
		try {
			$this->pdo = new PDO($dsn, $user, $psw);
			
		} catch (Exception $e) {
			$this->pdo = null;
			echo "Connection à la base de données inmpossible :\n".$e->getMessage();
		}
	}
	
	/**
	 * Instancie une nouvelle connexion avec la base de données via un objet PDO contenu dans l'objet Database
	 * @param string $dsn le DSN (voir le manuel PHP concernant PDO)
	 * @param stirng $login le nom d'utilisateur pour la connexion
	 * @param stirng $mdp son mot de passe
	 * @return Database : l'instance de Database créé et connecté, ou null en cas d'echec.
	 */
	public static function connecter($dsn, $login, $mdp){
		self::$instance = new self($dsn, $login, $mdp);
		return self::$instance;
	}
	
	/**
	 * Exécute la requete SQL passée en paramètres
	 * @param string $requqete la requete SQL à exécuter
	 * @return ReponseRequete : la réponse de la requete
	 */
	public function executer($requqete){
		$statement = $this->pdo->execute($requete);
		if($statement == false)
			return new ReponseRequete($statement, true,
				self::$instance->errorInfo()[2]);
		else 
			return new ReponseRequete($statement);
	}
	
	
	/**
	 * Return la seule instance de la base de données de l'application
	 * @return Database : l'instance de Database ( ! peut retourner null si mal connecté)
	 */
	public static function getIstance(){
		return self::$instance;
	}

}




?>