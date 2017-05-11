<?php 

namespace LM;

/*-*******************************************************************************************************
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
 * Database permet la connection aux bases de données.
 * 
 * L'interaction de l'application avec les bases de données se fait de façon générique pour tous les types de BD (Postgre, Oracle, MySql) grâce à l'objet *PDO* de PHP
 * Cette classe est basée sur le design pattern du multiton : il est possible d'avoir plusieurs instances de l'objet Database en les identifiant avec une étiquette unique, et d'y accéder partout dans l'application via la méthode statique *getInstance()*.
 * De ce fait, le constructeur de Database est private. Pour créer une nouvelle instance il faut utiliser la méthode de classe *instantiate()* en précisant une étiquette si vous utilisez plusieurs bases de données pour l'application.
 * 
 * @link http://php.net/manual/fr/intro.pdo.php Manuel PDO sur php.net 
 * 
 * @author RookieRed
 *
 */
class Database {
	

			/*-******************
			***   ATTRIBUTS   ***
			********************/
	
	/**
	 * Objet PDO
	 * @var PDO $pdo le pointeur vers l'objet PDO utilisé par Database pour se connecter et interargir avec la base de données
	 */
	private $pdo;
	/**
	 * @var string $label l'etiquette de la base de données
	 */
	private $label;
	/**
	 * @var string $dsn 
	 */
	private $dsn;
	/**
	 * @var string $login 
	 */
	private $login;
	/**
	 * @var string $passwd 
	 */
	private $passwd;
	/**
	 * @var array $errorMessages le tableau contenatn l'ensemble des messages d'erreur enregistrés
	 */
	private static $errorMessages = array();
	/**
	 * @var array $instances tableau contenant l'ensemble des objet Database instanciés dans l'application. La clé d'une entrée correspond à l'étiquette de la base de données.
	 */
	private static $instances = array();
	
	
		/*-*********************
		***   CONSTRUCTEUR   ***
		***********************/
	
	/**
	 * Instancie la connexion avec la base de donnees via un objet PDO contenu dans l'objet Database.
	 * Si la connection n'est pas possible le message d'erreur sera produit, vous pourrez l'afficher avec la methode geterrorMessages.
	 * @param string $dsn le DSN (voir le manuel PHP concernant **PDO**)
	 * @param string $login le nom d'utilisateur pour la connexion
	 * @param string $passwd son mot de passe
	 */
	private function __construct($dsn, $login, $passwd, $label) {
		$this->label = $label;
		$this->dsn = $dsn;
		$this->login = $login;
		$this->passwd = $passwd;
		try {
			// Test si BD Oracle
			if (strpos($this->dsn, 'oci:') !== false && !extension_loaded('pdo_oci'))
				$this->pdo = new \PDOOCI\PDO($this->dsn, $this->login, $this->passwd);
			else 
				$this->pdo = new \PDO($this->dsn, $this->login, $this->passwd);
		}
		catch (Exception $e) {
			$this->pdo = null;
			self::$errorMessages[] = "<br><b>[!]</b>Connection a la base de donnees impossible (etiquette = '$label') :\n".$e->getMessage()
				.'<br>';
		}
	}
	

			/*-*****************
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
			self::$errorMessages[] = '<br><b>[!]</b>Database::instantiate() : Il existe deje une BD portant l\'etiquette "'
				.$etiquette.'", veuillez en specifier une nouvelle<br>';
			return null;
		}
		self::$instances[$etiquette] = $nouvelleInstance;
		return $nouvelleInstance;
	}
	
	/**
	 * Execute la requete SQL passee en parametres
	 * @param mixed $request la requete SQL a executer, peut etre string ou objet SQLRequest
	 * @return RequestResponse l'objet repéresentant la reponse de la requete, ou false si la BD n'est pas connectee
	 */
	public function execute($request){
		if($this->pdo == null)
			return false;

		//On transforme l'objet SQLRequest en string
		if($request instanceof SQLRequest)
			$request = $request->__toString();

		//Execution de la requete
		try {
			$statement = $this->pdo->query($request);
			if($statement == false)
				return new RequestResponse(null, true, $this->pdo->errorInfo()[2]);
			else 
				return new RequestResponse($statement);
		}
		catch(Exception $e) {
			self::$errorMessages[] = "<br><b>Database::execute()</b>(etiquette = '$this->label') : ".$e->getMessage();
			return new RequestResponse(null, true, $e->getMessage());
		}
	}

	/**
	 * Fonction magique PHP : permet la sérialisation de Database.
	 * @return array un tableau contenant le nom des attributs à enregistrer lors de la sérialisation
	 */
	public function __sleep() {
		return array('dsn', 'login', 'passwd', 'label');
	}

	/**
	 * Fonction magique PHP : permet la désérialisation de Database
	 */
	public function __wakeup() {
		self::instantiate($this->dsn, $this->login, $this->passwd, $this->label);
	}
	
	
			/*-****************
			***   GETTERS   ***
			******************/
	
	/**
	 * Retourne l'instance de la base de donnees dont l'étiquette est apssée en paramètre. Si vous n'utilisez qu'une seule base de données vous n'avez pas besoin de spécifier ce paramètre.
	 * @param string $etiquette : l'etiquette de la base de donnees. Par defaut retourne la base de données étiquettée 'principale'
	 * @return Database : l'instance de Database ou null si l'étiquette ne correspond pas
	 */
	public static function getInstance($etiquette='principale'){
		if(!isset(self::$instances[$etiquette]))
			return null;

		return self::$instances[$etiquette];
	}

	/**
	* Fonction de debug
	* @return PDO l'objet PDO de contenu dans cette instance de Database.
	*/
	public function getPDO(){
		return $this->pdo;
	}

	/**
	 * Retourne l'étiquette de la base de données
	 * @return string l'etiquette de la base de donnees
	 */
	public function getLabel(){
		return $this->label;
	}

	/**
	 * Retourne le tableau des messages d'erreur enregistrés par la classe Database
	 * @return string le dernier message d'erreur
	 */
	public static function getErrorMessages(){
		return self::$errorMessages;
	}

	/**
	 * @return bool true si l'objet est connecté sur une base de données Oracle, false sinon
	 */
	public function oracle() {
		return $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME) == 'oci';
	}

	/**
	 * Definit une nouvelle etiquette pour la base de donnees.
	 * Cette nouvelle etiquette ne doit pas etre deja utilisee par une autre base de donnees.
	 * @param string $nouvEtiquette la nouvelle etiquette de la base de donnees
	 * @return boolean true si la BD a ete re-étiquettée, false sinon
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