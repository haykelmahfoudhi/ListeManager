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
	*
	*/
	private $limite;
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
		$this->limite = null;
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

	/**
	* Ajoute des clauses where à la requête.
	* @var array $tabWhere : le tableau contenant toutes les conditions à rajouter.
	* Ce tableau aura la forme suivante :
	* 	-> 'nomColonne1' => 'condition1', 'nomColonne2' => 'condition2' ...
	* Une condition prend la forme suivante : [OPERATEUR][VALEUR]
	* Les opérateurs possibles sont :
	* 	(pas d'opérateur) : égalité stricte avec la valeur entrée
	* 	< > <= >= : infèrieur, supèrieur, supèrieur ou égal, infèrieur ou égale (pour les valeurs numériques)
	* 	! : opérateur 'différent de'
	* 	<< : opérateur 'BETWEEN' pour les dates
	* 	_ % : opérateurs joker SQL, remplacent respectivement un seul caractère ou un nombre indéfini de caractère dans une chaine.
	* Il est possible de combiner les conditions en les séparant par une virgule. Ainsi la condition 'prenom' => 'Roger,Patrick' recherchera tous ceux ayant le prénom Roger ou Patrick
	*/
	public function where(array $tabWhere){
		$ret = '';
		foreach ($tabWhere as $nomColonne => $conditions) {
			$conditions = explode(',', $conditions);
			$ret .= '(';
			foreach ($conditions as &$condition) {
				//Initilasation des variables
				$operateur = '=';
				$not = false;

				// Construction de l'operateur et sa valeur : NOT
				if(mb_substr($condition, 0, 1) === '!'){
					$not = true;
					$condition = mb_substr($condition, 1);
				}
				// Comparateurs > < >= <=
				if( (in_array($op = mb_substr($condition, 0, 2), array('>=', '<='))
					|| in_array($op = mb_substr($condition, 0, 1), array('>', '<')) ) 
					&& is_numeric($val = mb_substr($condition, strlen($op))) ) {
					$valeur = $val;
					$operateur = $op;
				}
				// Opérateur BETWEEN
				else if(($pos = mb_strpos($condition, '<<')) !== false ){
					$valeur = mb_substr($condition, 0, $pos).' AND '
						.mb_substr($condition, $pos + 2);
					$operateur = 'BETWEEN';
				}
				else {
					// Opérateur LIKE
					if(mb_strpos($condition, '_') !== false 
						|| mb_strpos($condition, '%') !== false){
						$operateur = 'LIKE';
					}
					$valeur = $condition;
				}

				$ret .= (($not)? 'NOT ' : '')."$nomColonne $operateur $valeur OR ";
			}
			$ret = mb_substr($ret, 0, strlen($ret) - 4).')';
			$ret .= ' AND ';
		}

		$ret = mb_substr($ret, 0, strlen($ret) - 5);
		$this->blocWhere .= $ret;
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

				//Ajout de la limite
				if($this->limite !== null)
					$ret .= ' LIMIT '.intval($this->limite);
			}
		}
		return htmlentities($ret).';';
	}


			/*****************************
			***   GETTERS && SETTERS   ***
			*****************************/

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

	/**
	* @return int la valeur de la clause 'LIMIT' de la requete, ou null si non définie
	*/
	public function getLimite(){
		return $this->limite;
	}

	/**
	* Définit la nouvelle valeur de la clause LIMIT pour les requêtes de type SELECT
	* @param int $valeur : la nouvelle valeur limite. Si null cette clause sera desactivée. 
	*/
	public function setLimite($valeur){
		if($this->typeRequete != TypeRequete::SELECT)
			return false;

		$this->limite = $valeur;
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

		//Traitement bloc WHERE & HAVING & LIMIT
		$reWhere = '/^(.+)(\s+WHERE\s+)([\s\S]+)$/i';
		$reHaving = '/^(.+)(\s+HAVING\s+)([\s\S]+)$/i';
		$reLimit = '/^(.+)(\s+LIMIT\s+)([0-9]+)([\s\S]+)$/i';
		if(preg_match($reLimit, $this->baseRequete, $tabMatch) === 1){
			$this->baseRequete = $tabMatch[1];
			$this->limite = $tabMatch[3];
		}
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