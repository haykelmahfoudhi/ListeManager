<?php

class RequeteSQL {

	private $requete;

	private $typeRequete;

	public __construct($requete){

	}

	/**
	* Retourne le type de la base de requête en paramètre est
	*/
	private static function matchRequete($requete){
		$tabMatch = array();

		// Teste si SELECT
		$re = '/^(SELECT +(DISTINCT +)?([a-z0-9.*`,-_\ ]+)(FROM +(([a-z0-9*`,\-_ ]+))+))$/i';
		preg_match_all($re, $requete, $tabMatch);
		if($tabMatch != array()){
			$this->typeRequete = TypeRequete::SELECT;
		}

		// Teste si INSERT
		$re = '/^(INSERT +INTO [a-z0-9`_ -]+( \([a-z0-9,` ]+\))? VALUES *\((.*)+\))$/i';
		preg_match_all($re, $requete, $tabMatch);
		if($tabMatch != array()){
			$this->typeRequete = TypeRequete::INSERT;
		}

		// Teste si DELETE
		$re = '/^(DELETE +FROM +[a-z0-9 .`_-]+)$/';
		preg_match_all($re, $requete, $tabMatch);
		if($tabMatch != array()){
			$this->typeRequete = TypeRequete::DELETE;
		}

	}

	public function masquer(){

	}

	public function where(){

	}

	public function orderBy(){

	}

	public function getType(){
		return $this->typeRequete;
	}

	public function __toString(){
		return $this->requete;
	}

}

?>