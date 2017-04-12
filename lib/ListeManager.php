<?php


/**
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

	/**
	* Ex�cute la requete SQL dont la base est pass�e en param�tres.
	* Cette base sera augment�e par les divers param�tres fournis par la variable GET avant d'�tre ex�cut�.
	* Les r�sultats obtenus seront restitu�s par cette m�thode selon le param�tre $typeReponse de l'objet.
	* @param mixed $baseSQL : la requete � ex�cuter. Peut �tre de type string ou RequeteSQL.
	* @return mixed : l'objet de r�ponse d�pendant de $typeReponse, param�trable via la m�thode setTypeReponse
	*/
	public function executerRequeteGET($baseSQL){
		if($baseSQL instanceof RequeteSQL)
			$requeteSQL = $baseSQL;
		else 
			$requeteSQL = new RequeteSQL($baseSQL);

		//Construction de la requete � partir de variables GET disponibles
		if(isset($_GET['mask']))
			$requeteSQL->masquer($_GET['mask']);
		if(isset($_GET['tabselect']))
			$requeteSQL->where($_GET['tabselect']);
		if(isset($_GET['orderby']))
			$requeteSQL->orderBy($_GET['orderby']);

		//Ex�cution de la requete
		return $this->executerRequete($requeteSQL);

	}

	/**
	 * Ex�cute une requete SQL et retourne le r�sultat dans le format sp�cifi� par typeReponse
	 * @param mixed $requeteSQL : la requete � ex�cuter. Peut �tre de type string ou RequeteSQL.
	 * @return mixed : l'objet de r�ponse d�pendant de $typeReponse, param�trable via la m�thode setTypeReponse
	 */
	public function executerRequete($requeteSQL){

		// Gestion du param�tre
		if($requeteSQL instanceof RequeteSQL)
			$requete = $requeteSQL->__toString();
		else 
			$requete = $requeteSQL;

		// R�cup�ration de l'objet DB
		$db = Database::getInstance();
		if($db == null)
			return false;

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
				$this->template->afficherChampsRecherche(isset($_GET['Quest']));
				return $this->template->construireListe($reponse);
		}
		return false;
	}


			/******************
			***   GETTERS   ***
			******************/

	/**
	* @return ListeManager la seule instance de la classe Liste Manager
	*/
	public static function getInstance(){
		if(self::$instance == null)
			self::$instance = new self();
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

	/**
	* 
	* @param 
	*/
	public function setTypeReponse(TypeReponse $typeReponse){
		$this->typeReponse = $typeReponse;
	}

	/**
	* 
	* @param 
	*/
	public function setTemplate(TemplateListe $template){
		$this->template = $template;
	}

	/**
	* 
	* @param 
	*/
	public function setListeId($id){
		$this->template->setId($id);
	}

	/**
	* 
	* @param 
	* @param 
	*/
	public function setClasseLignes($classe1, $classe2){
		$this->template->setClasseLignes($classe1, $classe2);
	}
}

?>