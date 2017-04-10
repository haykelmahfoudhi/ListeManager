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
	
	// Attributs
	
	private $statement;
	private $erreur;
	private $messageErreur;
	public $data;
	
	//Constructeur
	
	public function __construct(PDOStaement $statement, $erreur=false, $message=''){
		$this->statement = $statement;
		$this->erreur = $erreur;
		$this->messageErreur = $message;
	}
	
	/**
	 * @return array : la ligne suivante du résultat de la requête ( -> fetch)
	 */
	public function ligneSuivante(){
		if(!$this->erreur()){
			return $this->statement->fetch();
		}
		return false;
	}
	
	/**
	 * @return array : l'ensemble des résultats de la requête contenu dans un tableau ( -> fetchALl)
	 */
	public function listeResultat(){
		if(!$this->erreur()){
			return $this->statement->fetchAll();
		}
		return false;
	}
	
	
	/**
	 * Détection d'une erreur dans l'exécution de la requête.
	 * @return boolean vrai si erreur ou si l'attribut statement est null
	 */
	public function erreur(){
		return $this->statement == null || $erreur;
	}
	
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
	
}

?>