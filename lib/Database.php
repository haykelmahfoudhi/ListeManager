<?php 


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
 * Database permet la connection et l'interaction avec les bases de données.
 * 
 * L'interaction de l'application avec les bases de données se fait de façon générique pour tous les types de BD (Postgre, Oracle, MySql) grâce à l'objet *PDO* de PHP.
 * Cette classe est basée sur le design pattern du multiton : il est possible d'avoir plusieurs instances de l'objet Database en les identifiant avec une étiquette unique,
 * et d'y accéder partout dans l'application via la méthode statique *getInstance()*.
 * 
 * De ce fait, le constructeur de Database est private. Pour créer une nouvelle instance il faut utiliser la méthode de classe *instantiate()* 
 * en précisant une étiquette si vous utilisez plusieurs bases de données pour l'application.
 * Par la suite il vous sera possible d'exécuter vos requêtes SQL grâce à la méthode *execute()*, qui prend en paramètre une requete SQL
 * (string ou objet SQLRequest) et si besoin un tableau de paramèttres variables.
 * Si ce 2nd paramètre est précisé Databse fait appel aux méthodes *prepare()* puis *execute()* de PDO, sinon seul la méthode *query()* sera utilisée.
 * La méthode *execute()* retourne un objet RequestResponse. Consultez la documentation de la classe pour plus d'informations.
 * 
 * Database peut produire des messages d'erreur en cas de problème, mais ne les affiche pas dans le document.
 * Pour les récuppérer utilisez la méthode statique *getErrorMessages()*
 * 
 * @link http://php.net/manual/fr/intro.pdo.php Manuel PDO sur php.net 
 * 
 * @author RookieRed
 *
 */
class Database {
	
	// Cette classe peut générer et afficher des messages d'erreur
	use T_ErrorGenerator;

			/*-******************
			***   ATTRIBUTS   ***
			********************/
	
	/**
	 * @var PDO $_pdo la référence vers l'objet PDO utilisé par Database pour se connecter et interargir avec la base de données
	 */
	private $_pdo;
	/**
	 * @var string $_label l'etiquette de la base de données
	 */
	private $_label;
	/**
	 * @var string $_dsn chaine de cararctère contenant les données de connexion à la base de données
	 */
	private $_dsn;
	/**
	 * @var string $_login correspond au nom d'utilisateur pour se connecter à la base de données
	 */
	private $_login;
	/**
	 * @var string $_passwd mot de passe de l'utilisateur
	 */
	private $_passwd;
	
	/**
	 * @var array $instances tableau contenant l'ensemble des objet Database instanciés dans l'application. La clé d'une entrée correspond à l'étiquette de la base de données.
	 */
	private static $instances = array();
	/**
	 * @var array $tabDescribe tableau associatif : pour chaque driver PDO est associé le commande pour recupérer les noms des
	 * colonnes de la table à décrire, ainsi que le nom de la colonne qui contient cette information
	 */
	private static $tabDescribe = [
			'oci'   => ['req' => 'DESCRIBE ', 'col' => 'name'],
			'mysql' => ['req' => 'DESCRIBE ', 'col' => 'Field'] ,
			'pgsql' => ['req' => '\d ',       'col' => 'Column']
		];
	
	
		/*-*********************
		***   CONSTRUCTEUR   ***
		***********************/
	
	/**
	 * Instancie la connexion avec la base de donnees via un objet PDO contenu dans l'objet Database.
	 * Si la connection n'est pas possible le message d'erreur sera produit, vous pourrez l'afficher avec la methode geterrorMessages.
	 * @param string $dsn le DSN (voir le manuel PHP concernant *PDO*)
	 * @param string $login le nom d'utilisateur pour la connexion
	 * @param string $passwd son mot de passe
	 * @param string $etiquette l'etiquette de la base de donnees, utile si plusieurs bases de donnees sont utilisees en meme temps dans l'application
	 */
	private function __construct($dsn, $login, $passwd, $label) {
		$this->_label 	= $label;
		$this->_dsn 	= $dsn;
		$this->_login 	= $login;
		$this->_passwd 	= $passwd;
		$this->verbose(true);
		try {
			// Test si BD Oracle
			if (strpos($this->_dsn, 'oci:') !== false && !extension_loaded('pdo_oci'))
				$this->_pdo = new \PDOOCI\PDO($this->_dsn, $this->_login, $this->_passwd);
			else 
				$this->_pdo = new \PDO($this->_dsn, $this->_login, $this->_passwd);
		}
		catch (\Exception $e) {
			$this->_pdo = null;
			$this->addError("Connection a la base de donnees impossible (etiquette = '$label') :\n".$e->getMessage(), '__construct');
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
	 * @param string $etiquette (facultatif) l'etiquette de la base de donnees, utile si plusieurs bases de donnees sont utilisees en meme temps dans l'application
	 * @return Database l'instance de Database créée et connectée, ou null en cas d'echec.
	 */
	public static function instantiate($dsn, $login, $mdp, $etiquette='principale'){
		$nouvelleInstance = new self($dsn, $login, $mdp, $etiquette);
		if($nouvelleInstance->_pdo == null)
			return null;

		if(isset(self::$instances[$etiquette])){
			$this->addError('Il existe deje une BD portant l\'etiquette "'.$etiquette.'", veuillez en specifier une nouvelle',
				'instantiate');
			return null;
		}
		self::$instances[$etiquette] = $nouvelleInstance;
		return $nouvelleInstance;
	}
	
	/**
	 * Execute la requete SQL passee en parametres
	 * @param mixed $request la requete SQL a executer, peut etre string ou objet SQLRequest
	 * @param array $params (facultatif) tableau contenant les paramètres variables de la requete (cf manuel de prepare & execute de la classe PDO) 
	 * @return RequestResponse l'objet repéresentant la reponse de la requete, ou false si la BD n'est pas connectee
	 */
	public function execute($request, array $params=array()){
		if($this->_pdo == null)
			return false;

		//On transforme l'objet SQLRequest en string
		if($request instanceof SQLRequest) {
			$sqlReq = $request;
			$request = $request->__toString();
		}
		else {
			$sqlReq = new SQLRequest($request, $this->oracle());
		}

		//Execution de la requete
		try {
			// Pas de prepare
			if($params === array())
				$statement = $this->_pdo->query($request);
			else 
				$statement = $this->_pdo->prepare($request);

			// Erreur prepare / query
			if($statement == false)
				return new RequestResponse(null, true, $this->_pdo->errorInfo()[2]);
			
			// Tout ok
			else {
				// Execute si params
				if(count($params)){
					if(! $statement->execute($params))
						return new RequestResponse($statement, true, $statement->errorInfo()[2]);
				}
				$rep = new RequestResponse($statement);

				// Listing des nom de colonnes / table pour éviter les ambiguités
				if($sqlReq->getType() === RequestType::SELECT){

					$ret = [];
					$tabAliases = $sqlReq->getTablesAliases();
					foreach($sqlReq->getColumnsMeta() as $obj){
						
						// Cas de l'étoile : table.*
						if($obj->name == '*'){
							if(isset($tabAliases[$obj->table]))
								$table = $tabAliases[$obj->table];
							else
								$table = $obj->table;

							foreach($this->describeTable($table) as $colonne){
								$newCol = new stdClass();
								$newCol->table = $table;
								$newCol->name = $colonne;
								$newCol->alias = null;
								$ret[] = $newCol;
							}
						}
						else
							$ret[] = $obj;
					}

					$rep->setColumnsMeta($ret);
				}
				return $rep;
			}
		}
		catch(Exception $e) {
			$this->addError("(etiquette = '$this->_label') : ".$e->getMessage(), 'execute');
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
		self::instantiate($this->_dsn, $this->_login, $this->_passwd, $this->_label);
	}

	/**
	 * Retourne le nom des colonnes d'une table.
	 * @param string $table le nom de la table à décrire
	 * @return array contenant le noms des colonnes de la table décrite
	 */
	public function describeTable($table) {
		if(!is_string($table))
			return false;

		$driver = $this->_pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
		if(!isset(self::$tabDescribe[$driver]))
			return false;
		
		$ret = [];
		$tabDriver = self::$tabDescribe[$driver];
		try {
			$statement = $this->_pdo->query($tabDriver['req'].$table.';');
			if($statement != false){
				while(($col = $statement->fetch()) != null) {
					$ret[] = $col[$tabDriver['col']];
				}
			}
			else {
				$this->addError("(etiquette = '$this->_label') : ".$this->_pdo->errorInfo()[2], 'describeTable');
			}
		}
		catch(Exception $e){
			$this->addError("(etiquette = '$this->_label') : ".$e->getMessage(), 'describeTable');
		}
		return $ret;
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
		unset(self::$instances[$this->_label]);
		$this->_label = $nouvEtiquette;
	}
	
			/*-****************
			***   GETTERS   ***
			******************/
	
	/**
	 * Retourne l'instance de la base de donnees dont l'étiquette est passée en paramètre.
	 * Si vous n'utilisez qu'une seule base de données vous n'avez pas besoin de spécifier d'étiquette.
	 * @param string $etiquette : l'etiquette de la base de donnees. Par defaut retourne la base de données étiquettée 'principale'
	 * @return Database : l'instance de Database ou null si l'étiquette ne correspond pas
	 */
	public static function getInstance($etiquette='principale'){
		if(!isset(self::$instances[$etiquette]))
			return null;

		return self::$instances[$etiquette];
	}

	/**
	* @return PDO l'objet PDO de contenu dans cette instance de Database.
	*/
	public function getPDO(){
		return $this->_pdo;
	}

	/**
	 * Retourne l'étiquette de la base de données
	 * @return string l'etiquette de la base de donnees
	 */
	public function getLabel(){
		return $this->_label;
	}

	/**
	 * Retourne le tableau des messages d'erreur enregistrés par la classe Database
	 * @return array le tableau des messages d'erreur enregistrés par la classe
	 */
	public static function getErrorMessages(){
		$errors = [];
		foreach (self::$instances as $label => $db) {
			$errors[$label] = $db->getErrorMessages();
		}
		return $errors;
	}

	/**
	 * @return bool true si l'objet est connecté sur une base de données Oracle, false sinon
	 */
	public function oracle() {
		return $this->_pdo->getAttribute(\PDO::ATTR_DRIVER_NAME) == 'oci';
	}

}

?>