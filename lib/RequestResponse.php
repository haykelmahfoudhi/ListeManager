<?php 

/**
 * Objet PHP utilise pour l'execution de requetes SQL. Cet objet contient 3 champs :
 * 		- La requete SQL executee (un objet PDOStatement)
 * 		- Un booleen signalant la presence d'une erreur lors de l'execution de la requete
 * 		- Un champs contenant le message d'erreur associe
 * @author RookieRed
 *
 */
class RequestResponse {
	
			/********************
			***   ATTRIBUTS   ***
			********************/
	
	private $statement;
	private $error;
	private $errorMessage;
	private $data;
	

		/***********************
		***   CONSTRUCTEUR   ***
		***********************/
	
	public function __construct($statement, $erreur=false, $message=''){
		$this->statement = $statement;
		$this->error = $erreur;
		$this->errorMessage = $message;
		$this->data = array();
	}
	

			/*******************
			***   METHODES   ***
			*******************/

	/**
	 * Retourne la ligne suivante du résultat de la requete
	 * @return array : la ligne suivante du resultat de la requete ( -> PDO::fetch)
	 * ou null si plus de résutlats
	 */
	public function nextLine(){
		if(!$this->error()){
			$ret = $this->statement->fetch(PDO::FETCH_NUM);
			if($ret != null)
				$this->data[] = $ret;
			return $ret;
		}
		return false;
	}
	
	/**
	 * @return array : l'ensemble des resultats de la requete contenu dans un tableau ( -> fetchAll)
	 */
	public function dataList(){
		if(!$this->error()){
			if(count($this->data) != $this->getRowsCount())
				$this->data = $this->statement->fetchAll(PDO::FETCH_NUM);
			return $this->data;
		}
		return false;
	}
	
	
	/**
	 * Detection d'une erreur dans l'execution de la requete.
	 * @return boolean vrai si erreur ou si l'attribut statement est null
	 */
	public function error(){
		return $this->statement == null || $this->error;
	}
	

			/******************
			***   GETTERS   ***
			******************/

	/**
	 * @return string : le message d'erreur associe a l'erreur detectee.
	 */
	public function getErrorMessage(){
		return $this->errorMessage;
	}
	
	/**
	 * @return array contenant le nom des colonnes retournees par la requete SQL, ou null si erreur
	 */
	public function getColumnsName(){
		if($this->error())
			return null;
		
		$ret = array();
		$nbCol = $this->getColumnsCount();
		for ($i=0; $i < $nbCol; $i++){
			array_push($ret, $this->statement->getColumnMeta($i)['name']);
		}
		return $ret;
	}

	/**
	* @return int : le nombre de colonnes du resultat de la requete, -1 en cas d'erreur
	*/
	public function getColumnsCount(){
		if($this->error())
			return -1;

		return $this->statement->columnCount();
	}

	/**
	* @return un tableau d'objets contenant les infos relatives au type de donnees de chaque colonne
	*	Cet objet possede les attribus suivants :
	* 		-> type : le type de donnees SQL 
	* 		-> len  : la taille de la donnee
	* Retourne faux si erreur
	*/
	public function getColumnsType(){
		if(($len = $this->getColumnsCount()) == -1)
			return false;
		
		for ($i=0; $i < $len; $i++) {
			$meta = $this->statement->getColumnMeta($i);
			$obj = new stdClass();
			$obj->type = $meta['native_type'];
			$obj->len  = $meta['len'];
			$ret[] = $obj;
		}
		return $ret;
	}

	/**
	* @return int : le nombre de lignes du resultat de la requete, -1 en cas d'erreur
	*/
	public function getRowsCount(){
		if($this->error())
			return -1;

		return $this->statement->rowCount();
	}

	/**
	* Fonction de debug
	* @return PDOStatement : l'objet PDO Statement contenu dans cet objet RequestResponse
	*/
	public function getPDOStatement(){
		return $this->statement;
	}
	
}

?>