<?php

/**************************************************************************************************
**                                                                                               **
**                        88           88                                                        **
**                        88           ""               ,d                                       **
**                        88                            88                                       **
**                        88           88  ,adPPYba,  MM88MMM  ,adPPYba,                         **
**                        88           88  I8[    ""    88    a8P_____88                         **
**                        88           88   `"Y8ba,     88    8PP"""""""                         **
**                        88           88  aa    ]8I    88,   "8b,   ,aa                         **
**                        88888888888  88  `"YbbdP"'    "Y888  `"Ybbd8"'                         **
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
	
	
			/***********************
			***   CONSTRUCTEUR   ***
			***********************/

	public function __construct(){
		$this->typeReponse = TypeReponse::TEMPLATE;
		$this->template = new TemplateListe();
		$this->utiliserGET = true;
		$this->tabWhere = null;
		$this->masque = null;
		$this->orderBy = null;
		$this->recherche = true;
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
	public function construire($baseSQL){
		if($baseSQL instanceof RequeteSQL)
			$requeteSQL = $baseSQL;
		else 
			$requeteSQL = new RequeteSQL($baseSQL);

		if($this->utiliserGET){
			//Construction de la requete à partir de variables GET disponibles
			if(isset($_GET['mask']) && strlen($_GET['mask']) > 0)
				$requeteSQL->masquer(explode(',', $_GET['mask']));
			if(isset($_GET['tabSelect']))
				$requeteSQL->where($_GET['tabSelect']);
			if(isset($_GET['orderBy']))
				$requeteSQL->orderBy(explode(',', $_GET['orderBy']));
		}
		else {
			if($this->masque != null)	
				$requeteSQL->masquer($this->masque);
			if($this->tabWhere != null)	
				$requeteSQL->where($this->tabWhere);
			if($this->orderBy != null)	
				$requeteSQL->orderBy($this->orderBy);
		}

		echo $requeteSQL;

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
		$reponse = $this->db->executer($requete);

		//Création de l'objet de réponse
		switch ($this->typeReponse){
			case TypeReponse::OBJET:
			return $reponse;

			case TypeReponse::TABLEAU:
				if($reponse->erreur())
					return null;
				else
					return $reponse->listeResultat();

			case TypeReponse::EXCEL:
			return ; // TODO

			case TypeReponse::JSON:
				$ret = new stdClass();
				$ret->erreur = $reponse->erreur();
				if($ret->erreur){
					$ret->donnees = null;
					$ret->messageErreur = $reponse->getMessageErreur();
				}
				else
					$ret->donnees = $reponse->listeResultat();
			return json_encode($ret);


			case TypeReponse::TEMPLATE:
				// Affichage (ou non) des champs de recherches
				$this->template->afficherChampsRecherche(
					isset($_GET['quest']) && intval($_GET['quest']) == 1);
			return $this->template->construireListe($reponse);
		}

		return false;
	}


			/******************
			***   GETTERS   ***
			******************/

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
	public function setTypeReponse($typeReponse){
		$this->typeReponse = $typeReponse;
	}

	/**
	* Définit la base de données qui sera utilisée pour l'exécution des requêtes SQL
	* @param mixed $dataBase : peut être de type Database ou string.
	*	Si string : représente l'etiquette de la base de données à utiliser.
	*	Si null (valeur par défaut) : recupère la base de donnée principale de la classe Database
	* la base de donnée sélectionnée sera celle par défaut de la classe Database.
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
			echo '<b>[!]</b> ListeManager::setDatabase() : aucune base de données correspondante';

	}

	/**
	* Définit un nouvel objet TemplateListe pour l'affichage des listes
	* @param TemplateListe $template le nouveau template à définir.
	*/
	public function setTemplate(TemplateListe $template){
		$this->template = $template;
	}

	/**
	* Définit le nouvel ID HTML de la liste HTML
	* @param stirng $id
	*/
	public function setListeId($id){
		$this->template->setId($id);
	}

	/**
	* Définit si ListeManager doit utiliser les valeurs contenues dans GET pour construire
	* le masque, le order by et le where de la requete SQL qui sera exécutée.
	* Valeur par défaut : vrai
	* @param boolean $valeur true ou false.
	*/
	public function utiliserGET($valeur){
		if(!is_bool($valeur))
			return false;

		$this->utiliserGET = $valeur;
	}

	/**
	* Redéfinit le nom des classes qui seront affectées une ligne sur deux dans la liste HTML (balises tr).
	* Si les valeurs sont mises à null les classes ne seront pas affiché.
	* @param string $class1 : le nom de la classe des lignes impaires
	* @param string $class2 : le nom de la classe des lignes paires
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

	/**
	* Définit si l'option recherche doit être activée ou non. Valeur par défaut : vrai
	* Si cette valeur est passée à faux il ne sera plus possible pour l'utilisateur
	* d'effectuer de recherches dans la liste
	* @param boolean $valeur, valeur par défautl : true
	*/
	public function activerRecherche($valeur){
		if(!is_bool($valeur))
			return false;

		$this->$template->activerRecherche($valeur);
	}

	/**
	* Définit le callback (la fonction) qui sera exécutée lors de l'affichage des données
	* dans les cellules du tableau. Cette fonction doit être définie comme il suit :
	* 	-> 3 paramètres d'entrée 
	* 			1 - element : la valeur de l'élément en cours
	* 			2 - colonne : le numéro de la colonne en cours
	* 			3 - ligne   : le numéro de la ligne en cours
	* 	-> valeur de retour de type string (ou du moins affichable via echo)
	* @param string $fonction : le nom du callback à utiliser, null si aucun.
	* Valeur par défaut : null
	*/
	public function setCallbackCellule($fonction){
		$this->template->setCallbackCellule($fonction);
	}
}

?>