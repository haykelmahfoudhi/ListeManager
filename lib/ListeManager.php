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
	/**
	 * 
	 */
	private $db;
	/**
	*
	*/
	private $utiliserGET;
	/**
	*
	*/
	private $tabWhere;
	/**
	*
	*/
	private $orderBy;
	/**
	*
	*/
	private $masque;

	/**
	*
	*/
	private static $instance = null;
	
	
			/***********************
			***   CONSTRUCTEUR   ***
			***********************/

	private function __construct(){
		$this->typeReponse = TypeReponse::TEMPLATE;
		$this->template = new TemplateListe();
		$this->utiliserGET = true;
		$this->tabWhere = null;
		$this->masque = null;
		$this->orderBy = null;
		$this->db = Database::getInstance();
		// Si la db est null alors on affiche une erreur
		if($this->db == null)
			echo '<b>[!]</b> ListeManager::__construct() : aucune base de données n\'est disponible ou instanciée';
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
	public function constuire($baseSQL){
		if($baseSQL instanceof RequeteSQL)
			$requeteSQL = $baseSQL;
		else 
			$requeteSQL = new RequeteSQL($baseSQL);

		if($this->utiliserGET){
			//Construction de la requete à partir de variables GET disponibles
			if(isset($_GET['mask']))
				$requeteSQL->masquer($_GET['mask']);
			if(isset($_GET['tabselect']))
				$requeteSQL->where($_GET['tabselect']);
			if(isset($_GET['orderby']))
				$requeteSQL->orderBy($_GET['orderby']);
		}
		else {
			if($this->masque != null)	
				$requeteSQL->masquer($this->masque);
			if($this->tabWhere != null)	
				$requeteSQL->where($this->tabWhere);
			if($this->orderBy != null)	
				$requeteSQL->orderBy($this->orderBy);
		}

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
		if($this->db == null)
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

			case TypeReponse::JSON:
				$ret = new stdClass();
				$ret = $reponse;
				$ret->donnees = $reponse->listeResultat();
				unset($ret->statement);


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
	* @return TemplateListe l'objet template utilisé par ListeManager
	*/
	public function getTemplate(){
		return $this->template;
	}

			/******************
			***   SETTERS   ***
			******************/

	/**
	* Définit le format de l'objet retourné par ListeManager suite à l'exécution d'une requete
	* @param TypeReponse $typeReponse peut prendre 5 valeurs :
	* 	-> TEMPLATE (par défaut) pour obtenir un string représentant la liste HTML contenant toutes les données 
	* 	-> ARRAY pour obtenir les résultats dans un array PHP (equivalent à PDOStaement::fetchAll())
	* 	-> JSON pour obtenir les données dans un objet encodé en JSON
	* 	-> EXCEL pour obtenir les résultats dans une feuille de calcul Excel
	* 	-> OBJET pour obtenir un objet ReponseResultat
	*/
	public function setTypeReponse(TypeReponse $typeReponse){
		$this->typeReponse = $typeReponse;
	}

	/**
	* Définit la base de données qui sera utilisée pour l'exécution des requêtes SQL
	* @param string $etiquette l'etiquette de la base de données à utiliser. Si non spécifiée (ou null)
	* la base de donnée sélectionnée sera celle par défaut de la classe Database.
	*/
	public function setDatabase($etiquette=null){
		if($etiquette == null)
			$this->$db = Database::getInstance();
		else
			$this->db = Database::getInstance($etiquette);

		if($this->db == null)
			echo '<b>[!]</b> ListeManager::setDatabase() : aucune base de données correspondante';

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
	* Définit si ListeManager doit utiliser les valeurs contenues dans GET pour construire
	* le masque, le order by et le where de la requete SQL qui sera exécutée
	* @param boolean $valeur true ou false.
	*/
	public function utiliserGET($valeur){
		if(!is_bool($valeur))
			return false;

		$this->utiliserGET = $valeur;
	}

	/**
	* 
	* @param 
	* @param 
	*/
	public function setClasseLignes($classe1, $classe2){
		$this->template->setClasseLignes($classe1, $classe2);
	}

	/**
	* Définit
	* /!\ Ne pas oublier de passer à faux l'utilisation des variables GET
	* pour que le changement soit pris en compte lors de l'exécution de la requête
	* @param
	*/
	public function setWhere(array $tabWhere){
		$this->tabWhere = $tabWhere;
	}

	/**
	* Définit
	* /!\ Ne pas oublier de passer à faux l'utilisation des variables GET
	* pour que le changement soit pris en compte lors de l'exécution de la requête
	* @param
	*/
	public function setOrderBy($orderBy){
		$this->orderBy = $orderBy;
	}

	/**
	* Définit
	* /!\ Ne pas oublier de passer à faux l'utilisation des variables GET
	* pour que le changement soit pris en compte lors de l'exécution de la requête
	* @param
	*/
	public function setMasque($masque){
		$this->masque = $masque;
	}
}

?>