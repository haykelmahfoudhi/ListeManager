<?php

/**************************************************************************************************
**                                                                                               **
**                              88           88                                                  **
**                              88           ""               ,d                                 **
**                              88                            88                                 **
**                              88           88  ,adPPYba,  MM88MMM                              **
**                              88           88  I8[    ""    88                                 **
**                              88           88   `"Y8ba,     88                                 **
**                              88           88  aa    ]8I    88,                                **
**                              88888888888  88  `"YbbdP"'    "Y888                              **
**                                                                                               **
**  88b           d88                                                                            **
**  888b         d888                                                                            **
**  88`8b       d8'88                                                                            **
**  88 `8b     d8' 88  ,adPPYYba,  8b,dPPYba,   ,adPPYYba,   ,adPPYb,d8   ,adPPYba,  8b,dPPYba,  **
**  88  `8b   d8'  88  ""     `Y8  88P'   `"8a  ""     `Y8  a8"    `Y88  a8P_____88  88P'   "Y8  **
**  88   `8b d8'   88  ,adPPPPP88  88       88  ,adPPPPP88  8b       88  8PP"""""""  88          **
**  88    `888'    88  88,    ,88  88       88  88,    ,88  "8a,   ,d88  "8b,   ,aa  88          **
**  88     `8'     88  `"8bbdP"Y8  88       88  `"8bbdP"Y8   `"YbbdP"Y8   `"Ybbd8"'  88          **
**                                                           aa,    ,88                          **
**                                                            "Y8bbdP"                           **
**************************************************************************************************/

/**
 * 
 * @author RookieRed
 *
 */
class ListManager {
	
			/********************
			***   ATTRIBUTS   ***
			********************/
	/**
	 * 
	 */
	private $responseType;
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
	private $useGET;
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
	private $mask;
	
	
			/***********************
			***   CONSTRUCTEUR   ***
			***********************/

	public function __construct(){
		$this->responseType = ResponseType::TEMPLATE;
		$this->template = new ListTemplate();
		$this->useGET = true;
		$this->tabWhere = null;
		$this->mask = null;
		$this->orderBy = null;
		$this->recherche = true;
		$this->db = Database::getInstance();
		// Si la db est null alors on affiche une erreur
		if($this->db == null)
			echo '<br><b>[!]</b> ListManager::__construct() : aucune base de donnees n\'est disponible ou instanciee<br>';
	}


			/*******************
			***   METHODES   ***
			*******************/

	/**
	* Execute la requete SQL dont la base est passee en parametres.
	* Cette base sera augmentee par les divers parametres fournis par la variable GET avant d'etre execute.
	* Les resultats obtenus seront restitues par cette methode selon le parametre $ResponseType de l'objet.
	* @param mixed $baseSQL : la requete a executer. Peut etre de type string ou SQLRequest.
	* @return mixed : l'objet de reponse dependant de $ResponseType, parametrable via la methode setResponseType
	*/
	public function construct($baseSQL){
		if($baseSQL instanceof SQLRequest)
			$SQLRequest = $baseSQL;
		else 
			$SQLRequest = new SQLRequest($baseSQL);

		if($this->useGET){
			//Construction de la requete a partir de variables GET disponibles
			
			if(isset($_GET['mask']) && strlen($_GET['mask']) > 0){ // Masque
				$SQLRequest->mask(explode(',', $_GET['mask']));
			}
			
			// Conditions (where)
			if(isset($_GET['tabSelect'])){
				$tabWhere = array();
				foreach ($_GET['tabSelect'] as $titre => $valeur) {
					if(strlen($valeur) > 0)
						$tabWhere[$titre] = $valeur;
				}
				if(count($tabWhere) > 0)
					$SQLRequest->where($tabWhere);
			}
			
			// Tri (Order By)
			if(isset($_GET['orderBy'])){
				$SQLRequest->orderBy(explode(',', $_GET['orderBy']));
			}
		}
		else {
			if($this->mask != null)	
				$SQLRequest->mask($this->mask);
			if($this->tabWhere != null)	
				$SQLRequest->where($this->tabWhere);
			if($this->orderBy != null)	
				$SQLRequest->orderBy($this->orderBy);
		}

		//Execution de la requete
		return $this->execute($SQLRequest);

	}

	/**
	 * Execute une requete SQL et retourne le resultat dans le format specifie par ResponseType
	 * @param mixed $request : la requete a executer. Peut etre de type string ou SQLRequest.
	 * @return mixed : l'objet de reponse dependant de $ResponseType, parametrable via la methode setResponseType
	 */
	public function execute($request){

		// Gestion du parametre
		if($request instanceof SQLRequest)
			$requete = $request->__toString();
		else 
			$requete = $request;

		// Recuperation de l'objet DB
		if($this->db == null)
			return false;

		//Execution de la requete
		$reponse = $this->db->execute($requete);

		//Creation de l'objet de reponse
		switch ($this->responseType){
			case ResponseType::OBJET:
			return $reponse;

			case ResponseType::TABLEAU:
				if($reponse->error())
					return null;
				else {
					while(($ligne = $reponse->nextLine()) != null)
						$donnees[] = $ligne;
					return $donnees;
				}

			case ResponseType::EXCEL:
			return ; // TODO

			case ResponseType::JSON:
				$ret = new stdClass();
				$ret->error = $reponse->error();
				if($ret->error){
					$ret->data = null;
					$ret->errorMessage = $reponse->getErrorMessage();
				}
				else{
					while(($ligne = $reponse->nextLine()) != null)
						$donnees[] = $ligne;
					$ret->data = $donnees;
				}
			return json_encode($ret);


			case ResponseType::TEMPLATE:
				// Affichage (ou non) des champs de recherches
				$this->template->displaySearchInputs(
					(isset($_GET['quest']) && intval($_GET['quest']) == 1)
					|| (isset($_GET['tabSelect']) 
						&& !(isset($_GET['quest']) && intval($_GET['quest']) == 0)) );
				
				//Gestion avec cache
				
				return $this->template->construct($reponse);
		}

		return false;
	}


			/******************
			***   GETTERS   ***
			******************/

	/**
	* @return ListTemplate l'objet template utilise par ListManager
	*/
	public function getTemplate(){
		return $this->template;
	}

			/******************
			***   SETTERS   ***
			******************/

	/**
	* Definit le format de l'objet retourne par ListManager suite a l'execution d'une requete
	* @param ResponseType $ResponseType peut prendre 5 valeurs :
	*    * TEMPLATE (par defaut) pour obtenir un string representant la liste HTML contenant toutes les donnees 
	*    * ARRAY pour obtenir les resultats dans un array PHP (equivalent a PDOStaement::fetchAll())
	*    * JSON pour obtenir les donnees dans un objet encode en JSON
	*    * EXCEL pour obtenir les resultats dans une feuille de calcul Excel
	*    * OBJET pour obtenir un objet ReponseResultat
	*/
	public function setResponseType($ResponseType){
		$this->responseType = $ResponseType;
	}

	/**
	* Instancie une nouvelle connexion a une base de donnees, et enregistre l'instance creee
	* pour l'execution des requetes futures.
	 * @param string $dsn le DSN (voir le manuel PHP concernant PDO)
	 * @param stirng $login le nom d'utilisateur pour la connexion
	 * @param stirng $mdp son mot de passe
	*/
	public function connectDatabase($dsn, $login, $mdp){
		$this->db = Database::instantiate($dsn, $login, $mdp);
		
		if($this->db == null)
			echo '<br><b>[!]</b> ListManager::connecterDatabase() : echec de connection<br>';
	}

	/**
	* Definit la base de donnees qui sera utilisee pour l'execution des requetes SQL
	* @param mixed $dataBase : peut etre de type Database ou string.
	*	Si string : represente l'etiquette de la base de donnees a utiliser.
	*	Si null (valeur par defaut) : recupere la base de donnee principale de la classe Database
	* la base de donnee selectionnee sera celle par defaut de la classe Database.
	*/
	public function setDatabase($dataBase=null){
		if($dataBase == null)
			$this->db = Database::getInstance();
		else {
			if($dataBase instanceof Database)
				$this->db = $dataBase;
			else 
				$this->db = Database::getInstance($dataBase);
		}

		if($this->db == null)
			echo '<br><b>[!]</b> ListManager::setDatabase() : aucune base de donnees correspondante<br>';

	}

	/**
	* Definit un nouvel objet ListTemplate pour l'affichage des listes
	* @param ListTemplate $template le nouveau template a definir.
	*/
	public function setTemplate(ListTemplate $template){
		$this->template = $template;
	}

	/**
	* Definit le nouvel ID HTML de la liste HTML
	* @param stirng $id
	*/
	public function setListeId($id){
		$this->template->setId($id);
	}

	/**
	* Definit si ListManager doit utiliser les valeurs contenues dans GET pour construire
	* le masque, le order by et le where de la requete SQL qui sera executee.
	* Valeur par defaut : vrai
	* @param boolean $valeur true ou false.
	*/
	public function useGET($valeur){
		if(!is_bool($valeur))
			return false;

		$this->useGET = $valeur;
	}

	/**
	* Redefinit le nom des classes qui seront affectees une ligne sur deux dans la liste HTML (balises tr).
	* Si les valeurs sont mises a null les classes ne seront pas affiche.
	* @param string $class1 : le nom de la classe des lignes impaires
	* @param string $class2 : le nom de la classe des lignes paires
	*/
	public function setRowsClasses($classe1, $classe2){
		$this->template->setRowsClasses($classe1, $classe2);
	}

	/**
	* Definit
	* Passe automatiqument a faux l'attribut sur l'utilisation des variables GET
	* pour la reecriture des requetes SQL
	* @param
	*/
	public function setWhere(array $tabWhere){
		$this->useGET = false;
		$this->tabWhere = $tabWhere;
	}

	/**
	* Definit
	* Passe automatiqument a faux l'attribut sur l'utilisation des variables GET
	* pour la reecriture des requetes SQL
	* @param
	*/
	public function setOrderBy($orderBy){
		$this->useGET = false;
		$this->orderBy = $orderBy;
	}

	/**
	* Definit
	* Passe automatiqument a faux l'attribut sur l'utilisation des variables GET
	* pour la reecriture des requetes SQL
	* @param
	*/
	public function setMasque($masque){
		$this->useGET = false;
		$this->mask = $masque;
	}

	/**
	* Definit si l'option recherche doit etre activee ou non. Valeur par defaut : vrai
	* Si cette valeur est passee a faux il ne sera plus possible pour l'utilisateur
	* d'effectuer de recherches dans la liste
	* @param boolean $valeur, valeur par defautl : true
	*/
	public function enableSearch($valeur){
		if(!is_bool($valeur))
			return false;

		$this->$template->enableSearch($valeur);
	}

	/**
	* Definit le callback (la fonction) qui sera executee lors de l'affichage des donnees
	* dans les cellules du tableau. Cette fonction doit etre definie comme il suit :
	* 	-> 3 parametres d'entree 
	* 			1 - element : la valeur de l'element en cours
	* 			2 - colonne : le nom de la colonne en cours
	* 			3 - ligne   : le numero de la ligne en cours
	* 	-> valeur de retour de type string (ou du moins affichable via echo)
	* @param string $fonction : le nom du callback a utiliser, null si aucun.
	* Valeur par defaut : null
	*/
	public function setCellCallback($fonction){
		if(!is_string($fonction))
			return false;

		$this->template->setCellCallback($fonction);
	}


	/**
	* Definit le nombre de resultats a afficher sur une page. Valeur par defaut = 50
	* @param int $valeur le nombre de lignes a afficher par pages
    * @return boolean faux si la valeur entree est incorrecte
	*/
	public function setNbResultsPerPage($valeur){
		return $this->template->setNbResultsPerPage($valeur);
	}

	/**
	* Definit si ListManager doit utiliser ou non le systeme de cache pour accelerer
	* la navigation entre les pages de la liste
	* @param boolean $valeur : true pour activer le fonctionnement par cache, false sinon
	*/
	public function useCache($valeur){
		if(!is_bool($valeur))
			return false;

		$this->template->useCache($valeur);
	}

	/**
	*
	*/
	public function setMaxPagesDisplayed($valeur){
		return $this->template->setMaxPagesDisplayed($valeur);
	}
}

?>