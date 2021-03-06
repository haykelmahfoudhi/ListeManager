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
 * Objet PHP g??n??r?? par l'execution des requetes SQL.
 * Cet objet est construit par Database suite ?? l'ex??cution d'une requ??te et permet de r??cup??rer le status de la requete ainsi que les donn??es g??n??r??es. Il contient 3 champs :
 * * La requete SQL executee (qui correspond ?? un objet PDOStatement)
 * * Un booleen signalant la presence d'une erreur lors de l'execution de la requete
 * * Un champs contenant le message d'erreur associe
 * Les m??thodes de la classe permettent de r??cup??rer les donn??es s??l??ctionn??es gr??ces aux m??thodes *nextLine()* et *dataList()* qui sont les homologues respectifs de *fetch()* et *fetchAll()*
 * de la classe *PDOStatement*
 * Vous pouvez tester le status de la r??ponse avec la m??thode *error()* qui retourne un bool??en, et r??cup??rer le message d'erreur associ?? via la m??thode *getErrorMessage()*
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
	 * @var PDOStatement $_statement l'objet PDOStatement g??n??r?? par l'execution d'une requete SQL
	 */
	private $_statement;
	/**
	 * @var boolean $_error indique l'existence ou non d'une erreur lors de l'ex??cution de la requete
	 */
	private $_error;
	/**
	 * @var string $_errorMessage le contenu du message d'erreur si il y a une erreur
	 */
	private $_errorMessage;
	/**
	 * @var array $_data contient l'ensemble des lignes retourn??es par une requete de s??lection
	 */
	private $_data;
	/**
	 * @var array $_columnsMeta contient le nom des table.colonne selctionn??es ?? utiliser pour filtrer les donnees
	 */
	private $_columnsMeta;

	

		/*-*********************
		***   CONSTRUCTEUR   ***
		***********************/
	
	/**
	 * Construit un nouvel objet de r??ponse
	 * @param PDOStatement $statement l'objet PDOStatement retourn?? par la methodes *PDO->execute()*, ou null si erreur
	 * @param boolean $erreur (facultatif) indique la pr??sence ou non d'une erreur lors de l'ex??cution de la requete
	 * @param string $message (facultatif) le message d'erreur associ??
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
	 * Retourne la ligne suivante du r??sultat de la requete
	 * @param int $fetchMode d??finit le type de r??ponse renvoy??, consultez le manuel PDO correspondant ( @see http://php.net/manual/fr/pdostatement.fetch.php )
	 * @return array|bool : la ligne suivante du resultat de la requete (utilise *PDOStatement->fetch()*) ou false si plus de r??sutlats
	 */
	public function nextLine($fetchMode=PDO::FETCH_BOTH){
		if(!$this->error()){
			$ret = $this->_statement->fetch($fetchMode);
			if($ret != false)
				$this->_data[] = $ret;
			return $ret;
		}
		return false;
	}
	
	/**
	 * Retourne l'ensemble des lignes selectionn??es par la requete SQL
	 * @param int $fetchMode d??finit le type de r??ponse renvoy??, consultez le manuel PDO correspondant ( @see http://php.net/manual/fr/pdostatement.fetch.php )
	 * @return array|bool l'ensemble des resultats de la requete contenu dans un tableau (utilise la m??thode PDOStatement->fetchAll()*) ou false
	 */
	public function dataList($fetchMode=PDO::FETCH_BOTH){
		if(!$this->error()){
			if(count($this->_data) < 1)
				$this->_data = $this->_statement->fetchAll($fetchMode);
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
	 * Met ?? jour les m??tas donn??es des colonnes s??lectionn??es.
	 * Cette m??thode est appel??e dans la classe Database pour r??cup??rer les donn??es de chaque colonnes pars??es par l'objet SQLRequest ?? l'origine de la requete.
	 * @param array $columns le tableau d'objets contenant les metas donn??es r??cup??r??es par Database.
	 * @return bool false si erreur de pararm??tre.
	 */
	public function setColumnsMeta(array $columns) {
		$len = $this->getColumnsCount();

		if($len <= 0)
			return false;

		// R??cup des metas PDO pour toutes les colonnes
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
	 * Retourne les meta donn??es relatives aux colonnes selectionn??es
	 * @return array un tableau d'objets contenant les infos relatives au type de donnees de chaque colonne. Cet objet possede les attribus suivants :
	 * * -> table : le nom de la table de la colonne, null si non renseign??
	 * * -> name  : le nom de la colonne tel que retourn?? par SQL
	 * * -> alias : l'alias de la colonne, null si non renseign??
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
	 * Retourne le nombre de lignes retourn??es par la requete
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