<?php

class RequeteSQL {

	/**
	* @var string $baseRequete : bloc de base SQL (sans WHERE, ORDER BY, HAVING...)
	*/
	private $baseRequete;
	/**
	* @var string $blocWhere : correspond à la partie Where de la requete SQL 
	*/
	private $blocWhere;
	/**
	* @var string $blocHaving : correspond à la partie Having de la requete SQL 
	*/
	private $blocHaving;
	/**
	* @var array $tabOrderBy : tableau contenant le numéro/nom de colonnes pour le tri des données 
	*/
	private $tabOrderBy;
	/**
	* @var array $tabMasque : tableau contenant le numéro/nom de colonnes à masquer 
	*/
	private $tabMasque;
	/**
	* @var TypeRequete $typeRequete : énumération sur le type de la requete SQL
	*/
	private $typeRequete;


			/***********************
			***   CONSTRUCTEUR   ***
			***********************/
	/**
	* Construit une nouvelle requete SQL à partir d'une requete de base
	* La requete SQL de base passée en paramètre ne doit pas contenir :
	* 	- de clause ODER BY
	* 	- de point virgule à la fin
	*/
	public function __construct($baseRequete){
		$this->baseRequete = $baseRequete;
		$this->blocWhere = '';
		$this->blocHaving = '';
		$this->tabOrderBy = array();
		$this->tabMasque = array();
		$this->matchRequete();
	}

			/*******************
			***   METHODES   ***
			*******************/

	public function masquer($numColonne){

	}

	
	public function where($tabWhere){

	}

	public function having(){

	}

	/**
	* Ajoute une ou plusieurs colonnes au bloc order by de la requete
	* @param mixed $colonne : correpsond numéro de la (ou des) colonne(s) à ajouter au group by.
	*	Pour ajouter plusieurs colonnes ce paramèttre doit etre un array, sinon c'est un int.
	*	Pour classer la colonnes par 'DESC' le numéro de colonne doit être négatif.
	* @return boolean faux si le type de requete n'est pas SELECT ou si le paramètre $colonne est vide.
	*/
	public function orderBy($colonne){
		//Vérification du type de requete
		if($this->typeRequete == TypeRequete::SELECT
			&& (is_int($colonne) || is_array($colonne))){

			//Si $colonne est un tableau
			if(is_array($colonne)){
				// Suppression des colonnes déjà existantes
				foreach ($colonne as $val)
					$negColonne[] = -1*$val;
				$orderBy = array_diff($this->tabOrderBy, $colonne, $negColonne);
				foreach (array_reverse($colonne) as $col) 
					array_unshift($orderBy, $col);
				$this->tabOrderBy = array_unique($orderBy);
			}

			//Sinon si c'est un int
			else {
				//Suppression de la valeur existante
				if(($key = array_search($colonne, $this->tabOrderBy)) != false
					|| ($key = array_search(-$colonne, $this->tabOrderBy)) != false){
					unset($this->tabOrderBy[$key]);
				}
				// Ajout de la colonne en début
				array_unshift($this->tabOrderBy, $colonne);
			}
			return true;
		}
		//param incompatbile / type incompatible
		return false;
	}

	/**
	* Remet à zéro le contenu du bloc ORDER BY
	* @return boolean vrai si opération ok, faux sinon (type de requete incompatible)
	*/
	public function supprimerOrderBy(){
		if($this->typeRequete == TypeRequete::SELECT) {
			$this->tabOrderBy = array();
			return true;
		}
		return false;
	}

	/**
	* @return TypeRequete : le type de la requete SQL
	*/
	public function getType(){
		return $this->typeRequete;
	}

	/**
	* @return string la requete SQL complete
	*/
	public function __toString(){
		$ret = $this->baseRequete;
		if(in_array($this->typeRequete,
			array(TypeRequete::SELECT, TypeRequete::UPDATE, TypeRequete::DELETE))) {
			//Ajout du bloc WHERE
			if(strlen($this->blocWhere) > 0)
				$ret .= ' WHERE '.$this->blocWhere;

			if($this->typeRequete == TypeRequete::SELECT){
				//Ajout du bloc HAVING
				if(strlen($this->blocHaving) > 0)
					$ret .= ' HAVING '.$this->blocHaving;
				//Ajout du order by
				if(count($this->tabOrderBy) > 0){
					$ret .= ' ORDER BY ';
					foreach ($this->tabOrderBy as $num) {
						$ret .= abs($num).(($num > 0)?'':' DESC ').',';
					}
					$ret = substr($ret, 0, strlen($ret) - 1);
				}
			}
		}
		var_dump($this);
		return $ret.';';
	}

	/**
	* Définit le type de la base de requête en paramètre parmi 
	* 	- SELECT - UPDATE - DELETE - INSERT - AUTRE -
	* Actualise les blocs where et having si necessaire. A appler dans le constructeur
	*/
	private function matchRequete(){
		//Traitement bloc WHERE & HAVING
		$reWhere = '/^(.+)(\s+WHERE\s+)([\s\S]+)$/i';
		$reHaving = '/^(.+)(\s+HAVING\s+)([\s\S]+)$/i';
		if(preg_match($reHaving, $this->baseRequete, $tabMatch) === 1){
			$this->baseRequete = $tabMatch[1];
			$this->blocHaving = $tabMatch[3];
		}
		if(preg_match($reWhere, $this->baseRequete, $tabMatch) === 1){
			$this->baseRequete = $tabMatch[1];
			$this->blocWhere = $tabMatch[3];
		}

		//Expressions régulières
		$reSelect = '/^(SELECT(.+))(FROM(.+))$/i';
		$reInsert = '/^(INSERT(.+))$/i';
		$reUpdate = '/^(UPDATE(.+))$/i';
		$reDelete = '/^(DELETE(.+))$/i';

		$tabMatch = array();
		// Teste si SELECT
		preg_match($reSelect, $this->baseRequete, $tabMatch);
		if($tabMatch != array()){
			var_dump($tabMatch);
			$this->typeRequete = TypeRequete::SELECT;
			return;
		}
		
		// Teste si INSERT
		preg_match($reInsert, $this->baseRequete, $tabMatch);
		if($tabMatch != array()){
			$this->baseRequete = $tabMatch[1];
			$this->typeRequete = TypeRequete::INSERT;
			return;
		}

		// Teste si DELETE
		preg_match($reDelete, $this->baseRequete, $tabMatch);
		if($tabMatch != array()){
			$this->typeRequete = TypeRequete::DELETE;
			$this->baseRequete = $tabMatch[1];
			return;
		}

		//Teste si UPDATE
		preg_match($reUpdate, $this->baseRequete, $tabMatch);
		if($tabMatch != array()){
			$this->typeRequete = TypeRequete::UPDATE;
			$this->baseRequete = $tabMatch[1];
			return;
		}

		// Si rien de tout cela
		$this->typeRequete = TypeRequete::AUTRE;
	}

}

?>