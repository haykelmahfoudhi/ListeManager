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
	/**
	*
	*/
	private $colonnesSelect;


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

	/**
	* Supprime la seléction des colonnes dont les numéros sont passés en paramètre
	* 
	*/
	public function masquer(array $numColonnes){
		foreach ($numColonnes as $num) {
			if($num == intval($num) && ! in_array(intval($num), $this->tabMasque))
				$this->tabMasque[] = intval($num);
		}
	}

	
	public function where(array $tabWhere){

	}

	/**
	* Ajoute une ou plusieurs colonnes au bloc order by de la requete
	* @param mixed $numColonne : correpsond numéro de la (ou des) colonne(s) à ajouter au group by.
	*	Pour ajouter plusieurs colonnes ce paramèttre doit etre un array, sinon c'est un int.
	*	Pour classer la colonnes par 'DESC' le numéro de colonne doit être négatif.
	* @return boolean faux si le type de requete n'est pas SELECT ou si le paramètre $numColonne est vide.
	*/
	public function orderBy($numColonne){
		//Vérification du type de requete
		if($this->typeRequete == TypeRequete::SELECT){

			//Si $numColonne est un tableau
			if(is_array($numColonne)){
				// Suppression des colonnes déjà existantes
				foreach ($numColonne as $val)
						$negColonne[] = -1 * $val;
				$orderBy = array_diff($this->tabOrderBy, $numColonne, $negColonne);
				
				foreach (array_reverse($numColonne) as $col){
					if(intval($col) != 0)
						array_unshift($orderBy, intval($col));
				}
				$this->tabOrderBy = array_unique($orderBy);
			}

			//Sinon si c'est un int
			else {
				$numColonne = intval($numColonne);
				if($numColonne == 0)
					return false;

				//Suppression de la valeur existante
				if(($key = array_search($numColonne, $this->tabOrderBy)) != false
					|| ($key = array_search(-$numColonne, $this->tabOrderBy)) != false){
					unset($this->tabOrderBy[$key]);
				}
				// Ajout de la colonne en début
				array_unshift($this->tabOrderBy, $numColonne);
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
	* @return string la requete SQL complete
	*/
	public function __toString(){
		// Gestion du masque
		if($this->typeRequete == TypeRequete::SELECT
			&& is_array($this->tabMasque) && count($this->tabMasque) > 0) {
			foreach ($this->tabMasque as $masque) {
				unset($this->colonnesSelect[intval($masque)]);
			}
			$ret = 'SELECT '.implode(',', $this->colonnesSelect).' ';
			$ret .= strstr($this->baseRequete, 'FROM');
		}
		else
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
		return $ret.';';
	}


			/******************
			***   GETTERS   ***
			******************/

	/**
	* @return TypeRequete : le type de la requete SQL
	*/
	public function getType(){
		return $this->typeRequete;
	}

	/**
	* @return array les numéros des colonnes du order by, négatif si tri décroissant
	* retourne faux s'il n'y a pas d'order by.
	*/
	public function getTabOrderBy(){
		if($this->typeRequete === TypeRequete::SELECT
			&& count($this->tabOrderBy) > 0){
			return $this->tabOrderBy;
		}
		return false;
	}


			/******************
			***   PRIVATE   ***
			******************/

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
			$this->typeRequete = TypeRequete::SELECT;
			$this->colonnesSelect = explode(',', $tabMatch[2]);
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