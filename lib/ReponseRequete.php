<?php 

/**
 * Objet PHP utilis� pour l'ex�cution de requetes SQL. Cet objet contient 3 champs :
 * 		- La requete SQL ex�cut�e (un objet PDOStatement)
 * 		- Un bool�en signalant la pr�sence d'une erreur lors de l'ex�cution de la requ�te
 * 		- Un champs contenant le message d'erreur associ�
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
	 * @return array : la ligne suivante du r�sultat de la requ�te ( -> fetch)
	 */
	public function ligneSuivante(){
		if(!$this->erreur()){
			return $this->statement->fetch();
		}
		return false;
	}
	
	/**
	 * @return array : l'ensemble des r�sultats de la requ�te contenu dans un tableau ( -> fetchALl)
	 */
	public function listeResultat(){
		if(!$this->erreur()){
			return $this->statement->fetchAll();
		}
		return false;
	}
	
	
	/**
	 * D�tection d'une erreur dans l'ex�cution de la requ�te.
	 * @return boolean vrai si erreur ou si l'attribut statement est null
	 */
	public function erreur(){
		return $this->statement == null || $erreur;
	}
	
	/**
	 * @return string : le message d'erreur associ� � l'erreur detect�e.
	 */
	public function getMessageErreur(){
		return $this->messageErreur;
	}
	
	/**
	 * @return array contenant le nom des colonnes retourn�es par la requete SQL, ou null si erreur
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