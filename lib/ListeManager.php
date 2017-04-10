<?php


/**
 * << SINGLETON >>
 * 
 * @author RookieRed
 *
 */
class ListeManager {
	
	//Attribus
	
	/**
	 * 
	 */
	private $typeReponse;
	
	/**
	 * 
	 */
	private $template;

	private static $instance = null;
	
	
	// Constructeur

	private __construct(){
		$this->typeReponse = TypeReponse::TEMPLATE;
		$this->template = new Template();
	}

	//M�thodes

	public function construireRequete($baseSQL){

	}

	/**
	 * 
	 */
	public function executerRequete($requete){
		// R�cup�ration de l'objet DB
		$db = Database::getInstance();
		if($db == null){
			return false;
		}

		//Ex�cution de la requ�te
		$reponse = $db->executerRequete($requete);
		if($reponse->erreur()){
			return $reponse;
		}

		//Cr�ation de l'objet de r�ponse
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

	//Getters

	public static function getInstance(){
		if(self::$instance == null)
			self::$instance = new PHPLib();
		return self::$instance;
	}

	// Setters

	public function setReponse(TypeReponse $typeReponse){
		$this->typeReponse = $typeReponse;
	}

	public function setTemplate(Template $template){
		$this->template = $template;
	}

}

?>