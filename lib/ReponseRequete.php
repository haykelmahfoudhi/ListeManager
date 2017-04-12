<?php 

/**
 * Objet PHP utilisé pour l'exécution de requetes SQL. Cet objet contient 3 champs :
 * 		- La requete SQL exécutée (un objet PDOStatement)
 * 		- Un booléen signalant la présence d'une erreur lors de l'exécution de la requête
 * 		- Un champs contenant le message d'erreur associé
 * @author RookieRed
 *
 */
class ReponseRequete {
	
			/********************
			***   ATTRIBUTS   ***
			********************/
	
	private $statement;
	private $erreur;
	private $messageErreur;
	public $data;
	

		/***********************
		***   CONSTRUCTEUR   ***
		***********************/
	
	public function __construct(PDOStatement $statement, $erreur=false, $message=''){
		$this->statement = $statement;
		$this->erreur = $erreur;
		$this->messageErreur = $message;
	}
	

			/*******************
			***   METHODES   ***
			*******************/

	/**
	 * @return array : la ligne suivante du résultat de la requête ( -> fetch)
	 */
	public function ligneSuivante(){
		if(!$this->erreur()){
			return $this->statement->fetch(PDO::FETCH_NUM);
		}
		return false;
	}
	
	/**
	 * @return array : l'ensemble des résultats de la requête contenu dans un tableau ( -> fetchALl)
	 */
	public function listeResultat(){
		if(!$this->erreur()){
			return $this->statement->fetchAll(PDO::FETCH_NUM);
		}
		return false;
	}
	
	
	/**
	 * Détection d'une erreur dans l'exécution de la requête.
	 * @return boolean vrai si erreur ou si l'attribut statement est null
	 */
	public function erreur(){
		return $this->statement == null || $this->erreur;
	}
	

			/******************
			***   GETTERS   ***
			******************/

	/**
	 * @return string : le message d'erreur associé à l'erreur detectée.
	 */
	public function getMessageErreur(){
		return $this->messageErreur;
	}
	
	/**
	 * @return array contenant le nom des colonnes retournées par la requete SQL, ou null si erreur
	 */
	public function getNomColonnes(){
		if($this->erreur())
			return null;
		
		$ret = array();
		for ($i=0; $i < count(); $i++){
			array_push($ret, $this->statement->getColumnMeta($i)['name']);
		}
		return $ret;
	}

	/**
	* @return int : le nombre de colonnes du résultat de la requete, -1 en cas d'erreur
	*/
	public function getNbColonnes(){
		if($this->erreur())
			return -1;

		return $this->statement->columnCount();
	}

	/**
	* @return un tableau d'objet contenant les infos relatives au type de données de chaque colonne
	*	Cet objet possède les attribus suivants
	* 		-> type : le type de données SQL 
	* 		-> len  : la longueur du champs
	* Retourne faux si erreur
	*/
	public function getTypeColonnes(){
		if(($len = $this->getNbColonnes()) == -1)
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
	* @return int : le nombre de lignes du résultat de la requete, -1 en cas d'erreur
	*/
	public function getNbLignes(){
		if($this->erreur())
			return -1;

		return $this->statement->rowCount();
	}

	/**
	* Fonction de debug
	* @return PDOStatement : l'objet PDO Statement contenu dans cet objet ReponseRequete
	*/
	public function getPDOStatement(){
		return $this->statement;
	}
	
}

?>