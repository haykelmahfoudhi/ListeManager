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
	 * @var array $_columnsMeta contient le nom des table.colonne selctionnées à utiliser pour filtrer les donnees
	 */
	private $_columnsMeta;

	

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
		$this->_columnsMeta = [];
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
			$ret = $this->_statement->fetch();
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
			if(coutn($this->_data) != $this->getRowsCount())
				$this->_data = $this->_statement->fetchAll();
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

	/**
	 * Met à jour les métas données des colonnes sélectionnées.
	 * Cette méthode est appelée dans la classe Database pour récupérer les données de chaque colonnes parsées par l'objet SQLRequest à l'origine de la requete.
	 * @param array $columns le tableau d'objets contenant les metas données récupérées par Database.
	 * @return bool false si erruer de pararmètre.
	 */
	public function setColumnsMeta(array $columns) {
		$len = $this->getColumnsCount();

		if($len <= 0)
			return false;

		// Récup des metas PDO pour toutes les colonnes
		for ($i=0; $i < $len; $i++) {
			$metas[] = $this->_statement->getColumnMeta($i);
		}
		$this->_columnsMeta = [];

		if(count($columns) == $len){
			for ($i=0; $i < $len; $i++) {
				if(!isset($metas[$i]['native_type']))
					$columns[$i]->type = $metas[$i]['driver:decl_type'];
				else
					$columns[$i]->type = $metas[$i]['native_type'];
				$columns[$i]->len = $metas[$i]['len'];
				
				$aliasNoQuotes = str_replace('`', '', str_replace('"', '',  str_replace("'", '', $columns[$i]->alias)));
				// S'il y a un alias -> ajout du name dans la partie alias
				if(strtolower($aliasNoQuotes) !== strtolower($metas[$i]['name']))
					$columns[$i]->name = $metas[$i]['name'];
				
				$this->_columnsMeta[] = $columns[$i];
			}
		}

		else {
			$this->setPDOSColumnsMeta();
		}
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
	 * Retourne les meta données relatives aux colonnes selectionnées
	 * @return array un tableau d'objets contenant les infos relatives au type de donnees de chaque colonne. Cet objet possede les attribus suivants :
	 * * -> table : le nom de la table de la colonne, null si non renseigné
	 * * -> name  : le nom de la colonne tel que retourné par SQL
	 * * -> alias : l'alias de la colonne, null si non renseigné
	 * * -> type  : le type de donnees SQL 
	 * * -> len   : la taille de l'attribut
	 * retourne false si erreur
	 */
	public function getColumnsMeta(){
		if($this->error())
			return null;

		if(count($this->_columnsMeta) != $this->getColumnsCount())
			$this->setPDOSColumnsMeta();
		return $this->_columnsMeta;
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

	private function setPDOSColumnsMeta() {
		$this->_columnsMeta = [];
		for ($i=0; $i < $this->getColumnsCount(); $i++) {
			$meta = $this->_statement->getColumnMeta($i);
			$obj = new stdClass();
			$obj->table = null;
			$obj->alias = null;
			$obj->type = ( (!isset($meta['native_type'])) ? $meta['driver:decl_type'] : $meta['native_type'] );
			$obj->len = $meta['len'];
			$obj->name = $meta['name'];
			$this->_columnsMeta[] = $obj;
		}
	}
	
}

?>