<?php 


/*-****************************************************************************************************** 
**                                                                                                     ** 
**         88888888ba                                                                                  ** 
**         88      "8b                                                                ,d               ** 
**         88      ,8P                                                                88               ** 
**         88aaaaaa8P'  ,adPPYba,   ,adPPYb,d8  88       88   ,adPPYba,  ,adPPYba,  MM88MMM            ** 
**         88""""88'   a8P_____88  a8"    `Y88  88       88  a8P_____88  I8[    ""    88               **
**         88    `8b   8PP"""""""  8b       88  88       88  8PP"""""""   `"Y8ba,     88               **
**         88     `8b  "8b,   ,aa  "8a    ,d88  "8a,   ,a88  "8b,   ,aa  aa    ]8I    88,              **
**         88      `8b  `"Ybbd8"'   `"YbbdP'88   `"YbbdP'Y8   `"Ybbd8"'  `"YbbdP"'    "Y888            **
**                                          88                                                         **
**                                          88                                                         **
**                                                                                                     **
**  88888888ba                                                                                         **
**  88      "8b                                                                                        **
**  88      ,8P                                                                                        **
**  88aaaaaa8P'  ,adPPYba,  ,adPPYba,  8b,dPPYba,    ,adPPYba,   8b,dPPYba,   ,adPPYba,   ,adPPYba,    **
**  88""""88'   a8P_____88  I8[    ""  88P'    "8a  a8"     "8a  88P'   `"8a  I8[    ""  a8P_____88    **
**  88    `8b   8PP"""""""   `"Y8ba,   88       d8  8b       d8  88       88   `"Y8ba,   8PP"""""""    **
**  88     `8b  "8b,   ,aa  aa    ]8I  88b,   ,a8"  "8a,   ,a8"  88       88  aa    ]8I  "8b,   ,aa    **
**  88      `8b  `"Ybbd8"'  `"YbbdP"'  88`YbbdP"'    `"YbbdP"'   88       88  `"YbbdP"'   `"Ybbd8"'    **
**                                     88                                                              **
**                                     88                                                              **
**                                                                                                     **
********************************************************************************************************/

/**
 * Objet PHP généré par l'execution des requetes SQL.
 * Cet objet est construit par Database suite à l'exécution d'une requête et permet de récupérer le status de la requete ainsi que les données générées. Il contient 3 champs :
 * * La requete SQL executee (qui correspond à un objet PDOStatement)
 * * Un booleen signalant la presence d'une erreur lors de l'execution de la requete
 * * Un champs contenant le message d'erreur associe
 * Les méthodes de la classe permettent de récupérer les données séléctionnées grâces aux méthodes *nextLine()* et *dataList()* qui sont les homologues respectifs de *fetch()* et *fetchAll()*
 * de la classe *PDOStatement*
 * Vous pouvez tester le status de la réponse avec la méthode *error()* qui retourne un booléen, et récupérer le message d'erreur associé via la méthode *getErrorMessage()*
 * 
 * @link http://php.net/manual/en/class.pdostatement.php Manuel PHP de PDOStatement
 *
 * @author RookieRed
 *
 */
class RequestResponse {
	
			/********************
			***   ATTRIBUTS   ***
			********************/
	
	/**
	 * @var PDOStatement $_statement l'objet PDOStatement généré par l'execution d'une requete SQL
	 */
	private $_statement;
	/**
	 * @var boolean $_error indique l'existence ou non d'une erreur lors de l'exécution de la requete
	 */
	private $_error;
	/**
	 * @var string $_errorMessage le contenu du message d'erreur si il y a une erreur
	 */
	private $_errorMessage;
	/**
	 * @var array $_data contient l'ensemble des lignes retournées par une requete de sélection
	 */
	private $_data;
	/**
	 * @var array $_columns contient le nom des table.colonne selctionnées à utiliser pour filtrer les donnees
	 */
	private $_columns;

	

		/*-*********************
		***   CONSTRUCTEUR   ***
		***********************/
	
	/**
	 * Construit un nouvel objet de réponse
	 * @param PDOStatement $statement l'objet PDOStatement retourné par la methodes *PDO->execute()*, ou null si erreur
	 * @param boolean $erreur (facultatif) indique la présence ou non d'une erreur lors de l'exécution de la requete
	 * @param string $message (facultatif) le message d'erreur associé
	 */
	public function __construct($statement, $erreur=false, $message=''){
		$this->_columns = [];
		$this->_statement = $statement;
		$this->_error = $erreur;
		$this->_errorMessage = $message;
		$this->_data = array();
	}
	

			/*-*****************
			***   METHODES   ***
			*******************/

	/**
	 * Retourne la ligne suivante du résultat de la requete
	 * @return array|null : la ligne suivante du resultat de la requete (utilise *PDOStatement->fetch()*) ou null si plus de résutlats
	 */
	public function nextLine(){
		if(!$this->error()){
			$ret = $this->_statement->fetch(\PDO::FETCH_ASSOC);
			if($ret != null)
				$this->_data[] = $ret;
			return $ret;
		}
		return false;
	}
	
	/**
	 * Retourne l'ensemble des lignes selectionnées par la requete SQL
	 * @return array l'ensemble des resultats de la requete contenu dans un tableau (utilise la méthode PDOStatement->fetchAll()*)
	 */
	public function dataList(){
		if(!$this->error()){
			if(count($this->_data) != $this->getRowsCount())
				$this->_data = $this->_statement->fetchAll(\PDO::FETCH_ASSOC);
			return $this->_data;
		}
		return false;
	}
	
	
	/**
	 * Detection d'une erreur dans l'execution de la requete.
	 * @return boolean true si erreur ou si l'attribut statement est null
	 */
	public function error(){
		return $this->_statement == null || $this->_error;
	}

	public function setColumnsName(array $columns) {
		$this->_columns = $columns;
	}
	

			/*-****************
			***   GETTERS   ***
			******************/

	/**
	 * Retourne le message d'erreur pour cette reponse
	 * @return string le message d'erreur associe a l'erreur detectee.
	 */
	public function getErrorMessage(){
		return $this->_errorMessage;
	}
	
	/**
	 * Retourne le nom des colonnes selectionnées
	 * @return array contenant le nom des colonnes retournees par la requete SQL, ou null si erreur
	 */
	public function getColumnsName(){
		if($this->error())
			return null;
		
		if(count($this->_columns) != $this->getColumnsCount()) {
			$this->_columns = [];
			foreach ($this->getColumnsInfos() as $info) {
				$this->_columns[] = $info->name;
			}
		}
		return $this->_columns;
	}

	/**
	 * Retourne le nombre de lignes retournées par la requete
	 * @return int le nombre de colonnes du resultat de la requete, -1 en cas d'erreur
	 */
	public function getColumnsCount(){
		if($this->error())
			return -1;

		return $this->_statement->columnCount();
	}

	/**
	 * Retourne les informations relatives au type de données des colonnes selectionnées
	 * @return object[]|boolean un tableau d'objets contenant les infos relatives au type de donnees de chaque colonne. Cet objet possede les attribus suivants :
	 * * -> name : le nom de la colonne tel que retourné par SQL
	 * * -> type : le type de donnees SQL 
	 * * -> len  : la taille de la donnee
	 * retourne false si erreur
	 */
	public function getColumnsInfos(){
		if(($len = $this->getColumnsCount()) == -1)
			return false;
		
		for ($i=0; $i < $len; $i++) {
			$meta = $this->_statement->getColumnMeta($i);
			$obj = new \stdClass();
			if(!isset($meta['native_type']))
				$obj->type = $meta['driver:decl_type'];
			else
				$obj->type = $meta['native_type'];
			$obj->len  = $meta['len'];
			$obj->name  = $meta['name'];
			$ret[] = $obj;
		}
		return $ret;
	}

	/**
	 * Retourne le nombre de lignes selectionnees
	 * @return int : le nombre de lignes du resultat de la requete, -1 en cas d'erreur
	 */
	public function getRowsCount(){
		if($this->error())
			return -1;

		return $this->_statement->rowCount();
	}

	/**
	 * Fonction de debug
	 * @return PDOStatement : l'objet PDO Statement contenu dans cet objet RequestResponse
	 */
	public function getPDOStatement(){
		return $this->_statement;
	}
	
}

?>