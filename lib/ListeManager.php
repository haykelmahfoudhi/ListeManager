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
	* Exécute la requete SQL dont la base est passée en paramètres.
	* Cette base sera augmentée par les divers paramètres fournis par la variable GET avant d'être exécuté.
	* Les résultats obtenus seront restitués par cette méthode selon le paramètre $typeReponse de l'objet.
	* @param mixed $baseSQL : la requete à exécuter. Peut être de type string ou RequeteSQL.
	* @return mixed : l'objet de réponse dépendant de $typeReponse, paramètrable via la méthode setTypeReponse
	*/
	public function executerRequeteGET($baseSQL){
		if($baseSQL instanceof RequeteSQL)
			$requeteSQL = $baseSQL;
		else 
			$requeteSQL = new RequeteSQL($baseSQL);

		//Construction de la requete à partir de variables GET disponibles
		if(isset($_GET['mask']))
			$requeteSQL->masquer($_GET['mask']);
		if(isset($_GET['tabselect']))
			$requeteSQL->where($_GET['tabselect']);
		if(isset($_GET['orderby']))
			$requeteSQL->orderBy($_GET['orderby']);

		//Exécution de la requete
		return $this->executerRequete($requeteSQL);

	}

	/**
	 * Exécute une requete SQL et retourne le résultat dans le format spécifié par typeReponse
	 * @param mixed $requeteSQL : la requete à exécuter. Peut être de type string ou RequeteSQL.
	 * @return mixed : l'objet de réponse dépendant de $typeReponse, paramètrable via la méthode setTypeReponse
	 */
	public function executerRequete($requeteSQL){

		// Gestion du paramètre
		if($requeteSQL instanceof RequeteSQL)
			$requete = $requeteSQL->__toString();
		else 
			$requete = $requeteSQL;

		// Récupération de l'objet DB
		$db = Database::getInstance();
		if($db == null)
			return false;

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