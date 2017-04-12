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
			echo '<b>[!]</b> ListeManager::__construct() : aucune base de donn�es n\'est disponible ou instanci�e';
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
	public function constuire($baseSQL){
		if($baseSQL instanceof RequeteSQL)
			$requeteSQL = $baseSQL;
		else 
			$requeteSQL = new RequeteSQL($baseSQL);

		if($this->utiliserGET){
			//Construction de la requete � partir de variables GET disponibles
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
		if($this->db == null)
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
	* @return TemplateListe l'objet template utilis� par ListeManager
	*/
	public function getTemplate(){
		return $this->template;
	}

			/******************
			***   SETTERS   ***
			******************/

	/**
	* D�finit le format de l'objet retourn� par ListeManager suite � l'ex�cution d'une requete
	* @param TypeReponse $typeReponse peut prendre 5 valeurs :
	* 	-> TEMPLATE (par d�faut) pour obtenir un string repr�sentant la liste HTML contenant toutes les donn�es 
	* 	-> ARRAY pour obtenir les r�sultats dans un array PHP (equivalent � PDOStaement::fetchAll())
	* 	-> JSON pour obtenir les donn�es dans un objet encod� en JSON
	* 	-> EXCEL pour obtenir les r�sultats dans une feuille de calcul Excel
	* 	-> OBJET pour obtenir un objet ReponseResultat
	*/
	public function setTypeReponse(TypeReponse $typeReponse){
		$this->typeReponse = $typeReponse;
	}

	/**
	* D�finit la base de donn�es qui sera utilis�e pour l'ex�cution des requ�tes SQL
	* @param string $etiquette l'etiquette de la base de donn�es � utiliser. Si non sp�cifi�e (ou null)
	* la base de donn�e s�lectionn�e sera celle par d�faut de la classe Database.
	*/
	public function setDatabase($etiquette=null){
		if($etiquette == null)
			$this->$db = Database::getInstance();
		else
			$this->db = Database::getInstance($etiquette);

		if($this->db == null)
			echo '<b>[!]</b> ListeManager::setDatabase() : aucune base de donn�es correspondante';

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
	* D�finit si ListeManager doit utiliser les valeurs contenues dans GET pour construire
	* le masque, le order by et le where de la requete SQL qui sera ex�cut�e
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
	* D�finit
	* /!\ Ne pas oublier de passer � faux l'utilisation des variables GET
	* pour que le changement soit pris en compte lors de l'ex�cution de la requ�te
	* @param
	*/
	public function setWhere(array $tabWhere){
		$this->tabWhere = $tabWhere;
	}

	/**
	* D�finit
	* /!\ Ne pas oublier de passer � faux l'utilisation des variables GET
	* pour que le changement soit pris en compte lors de l'ex�cution de la requ�te
	* @param
	*/
	public function setOrderBy($orderBy){
		$this->orderBy = $orderBy;
	}

	/**
	* D�finit
	* /!\ Ne pas oublier de passer � faux l'utilisation des variables GET
	* pour que le changement soit pris en compte lors de l'ex�cution de la requ�te
	* @param
	*/
	public function setMasque($masque){
		$this->masque = $masque;
	}
}

?>