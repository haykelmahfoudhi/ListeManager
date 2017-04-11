<?php


/**
 * << SINGLETON >>
 * 
 * @author RookieRed
 *
 */
class ListeManager {
	
			/********************
			***   ATTRIBUTS   ***
			********************/
	/**
	 * 
	 */
	private $typeReponse;
	
	/**
	 * 
	 */
	private $template;

	private $requeteSQL;

	private static $instance = null;
	
	
			/***********************
			***   CONSTRUCTEUR   ***
			***********************/

	private function __construct(){
		$this->typeReponse = TypeReponse::TEMPLATE;
		$this->template = new TemplateListe();
	}

			/*******************
			***   METHODES   ***
			*******************/

	public function construireRequeteAvecGET($baseSQL){
		
		// Instanciation de l'objet RequeteSQL
		$this->requeteSQL = new RequeteSQL($baseSQL);
		$this->requeteSQL->
	}

	/**
	 * 
	 */
	public function executerRequete($requete){
		// Récupération de l'objet DB
		$db = Database::getInstance();
		if($db == null){
			return false;
		}

		//Exécution de la requête
		$reponse = $db->executerRequete($requete);
		if($reponse->erreur()){
			return $reponse;
		}

		//Création de l'objet de réponse
		switch ($this->typeReponse){
			case TypeReponse::OBJET:
				return $reponse;

			case TypeReponse::TABLEAU:
				return $reponse->listeResultat();

			case TypeReponse::EXCEL:
				return ; // TODO

			case TypeReponse::TEMPLATE:
				$liste = $reponse->listeResultat();
				$titres = $reponse->getNomColonnes();
				return $this->template->construireListe($liste, $titres);
		}
	}


			/******************
			***   GETTERS   ***
			******************/

	/**
	* @return ListeManager la seule instance de la classe Liste Manager
	*/
	public static function getInstance(){
		if(self::$instance == null)
			self::$instance = new PHPLib();
		return self::$instance;
	}

	/**
	* @return TemplateListe l'objet template des liste de l'objet
	*/
	public function getTemplateListe(){
		return $this->template;
	}

			/******************
			***   SETTERS   ***
			******************/

	public function setReponse(TypeReponse $typeReponse){
		$this->typeReponse = $typeReponse;
	}

	public function setTemplateListe(TemplateListe $template){
		$this->template = $template;
	}

}

?>