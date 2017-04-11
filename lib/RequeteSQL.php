<?php

class RequeteSQL {

	private $baseRequete;
	private $blocWhere;
	private $blocOrderBy;

	private $typeRequete;

	public function __construct($baseRequete){
		$this->baseRequete = $baseRequete;
		$this->typeRequete = self::matchRequete($baseRequete);
		$this->blocWhere = '';
		$this->blocOrderBy = '';
	}

	/**
	* Retourne le type de la base de requête en paramètre est
	*/
	private static function matchRequete($requete){
		$tabMatch = array();

		// Teste si SELECT
		$re = '/^(SELECT +(DISTINCT +)?([a-z0-9.*`,-_\ ]+)(FROM +(([a-z0-9*`,\-_ \(\)]+))+))$/i';
		preg_match_all($re, $this->requete, $tabMatch);
		if($tabMatch != array()){
			$this->typeRequete = TypeRequete::SELECT;
		}

		// Teste si INSERT
		$re = '/^(INSERT +INTO [a-z0-9`_ -]+( \([a-z0-9,` ]+\))? VALUES *\((.*)+\))$/i';
		preg_match_all($re, $this->requete, $tabMatch);
		if($tabMatch != array()){
			$this->typeRequete = TypeRequete::INSERT;
		}

		// Teste si DELETE
		$re = '/^(DELETE +FROM +[a-z0-9 .`_-]+)$/i';
		preg_match_all($re, $this->requete, $tabMatch);
		if($tabMatch != array()){
			$this->typeRequete = TypeRequete::DELETE;
		}

		//Teste si UPDATE
		$re = '/^(UPDATE +([a-z0-9_- `]+ +)SET +[a-z0-9_\(\)= -`*]+)$/i';
		preg_match_all($re, $this->requete, $tabMatch);
		if($tabMatch != array()){
			$this->typeRequete = TypeRequete::UPDATE;
		}

		//Si non reconnue
		return TypeRequete::AUTRE;

	}

	public function masquer($numColonne){

	}

	
	public function where($tabWhere){

	}

	/**
	* Ajoute une ou plusieurs colonnes au bloc order by de la requete
	* @param mixed $colonne : correpsond au nom ou numéro de la (ou des colonnes) à ajouter au bloc group by.
	*	Pour ajouter plusieurs colonnes ce paramèttre doit etre un array
	* @return boolean faux si le type de requete n'est pas SELECT ou si le paramètre $colonne est vide.
	*/
	public function orderBy($colonne){
		//Vérification du type de requete
		if($this->typeRequete == TypeRequete::SELECT && ($colonne != '' || $colonne != array())){

			if($this->orderBy == '')
				$this->blocOrderBy = 'ORDER BY ';
			else
				$this->orderBy .= ',';

			//Si $colonne est un tableau on ajoute toutes ses lignes
			if(is_array($colonne)){
				foreach ($col as $colonne) {
					$this->blocOrderBy .= "$col,"
				}
				//On supprime la dernière virgule
				substr($this->blocOrderBy, 0, strlen($this->blocOrderBy)-1);
			}

			else {
				$this->orderBy .= $colonne;
			}
			return true;
		}
		//type de requete incompatbile
		return false;
	}

	/**
	* @return TypeRequete : le type de la requete SQL
	*/
	public function getType(){
		return $this->typeRequete;
	}

	/**
	* @return string le résultat de la requête construite
	*/
	public function __toString(){
		return htmlspecialchars($this->baseRequete.' '.$this->blocWhere.
			' '.$this->blocOrderBy.';', ENT_QUOTE, 'UTF-8');
	}

}

?>