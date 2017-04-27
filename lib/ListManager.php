<?php

/*-************************************************************************************************
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
 * --= Objet ListManager =--
 * 
 * C'est l'objet central du projet. Il joue le rôle d'interface entre le developpeur, les bases de données et les listes.
 * ListManager possède un comportement de base :
 * * Connection à une base de données, ou utilisation d'une base de données particulière de l'applicaiton en spécifiant son étiquette (cf. la doc de l'objet Database)
 * * Utilisation d'une requête SQL de base permettant la sélection
 * * Utilisation des données situées dans les variables GET de l'url pour modifier la requete SQL de base, à savoir
 *   * 'mask' : permet de supprimer de la sélectionner les colonnes dont les numéro sont indiqués (spprime la colonne du select)
 *   * 'tabSelect' : permet de filtrer les données par colonnes (se rajoute à la clause WHERE de la requete)
 *   * 'orderBy' : permet de trier els données par ordre croissant / décroissant selon une colonne (ajoute le numéro de la colonne à la clause ORDER BY)
 * * L'exécution de la requête SQL
 * * La mise en forme des données dans une liste HTML dans un template correpsondant à la classe ListTemplate
 * 
 * Ce comportement de base et adaptable et modifibale grâce aux nombreuses méthodes de la classe. Vous pouvez entre autre choisir de retourner les données sous forme de array PHP, d'objet, de fichier excel... ou bien modifier le comportement du template HTML
 * 
 * @author RookieRed
 *
 */
class ListManager {
	
			/********************
			***   ATTRIBUTS   ***
			********************/
	/**
	 * @var ResponseType $responseType correspond au format de données retourné par ListManager
	 * Valeur par défaut : TEMPLATE.
	 */
	private $responseType;
	/**
	 * @var ListTemplate $template objet template utilisé par ListManager pour générer les listes HTML
	 */
	private $template;
	/**
	 * @var Database $db objet Databse qui sera utilisé pour l'éxecution des requêtes SQL
	 */
	private $db;
	/**
	* @var boolean $useGET définit si ListManager doit utiliser ou non les données GET de l'url pour modifier la requete SQL de base.
	* Valeur par défaut : false. Si cette valeur est passée à true, il vous sera possible de spécifier vous même les objets à utiliser pour modifier la requete SQL (where, orderBy et mask)
	*/
	private $useGET;
	/**
	* @var array $tabSelect correpsond au tabelau $_GET['tabSelect']. Ce tableau doit avoir pour format [nomColonne] => 'valeur filtre'
	* A utiliser si vous ne souhaitez pas utiliser les variables GET pour filtrer les données par colonne.
	*/
	private $tabSelect;
	/**
	* @var string $orderBy correspond à l'entrée $_GET['orderBy']. Liste les numéro de colonnes pour la clause ORDER BY spéraées par une virgule.
	* A utiliser si vous ne souhaitez pas passer par la variable GET pour trier les données
	*/
	private $orderBy;
	
	
			/*-*********************
			***   CONSTRUCTEUR   ***
			***********************/
	
	/**
	 * Construit un nouvel objet ListManager définit par son comportement de base.
	 * Tente de récupérer la base de données principale de l'application. Vous pouvez donc utiliser la méthode *Database::instantaite()* au préalable pour ne pas avoir à spécifier la base de données à utiliserpar la suite.
	 * Précisez l'etiquette de la base de données à utiliser si nécessaire
	 * @var string $labelDB l'étiquette de la base de données que doit utiliser listManager. Laissez null si vous n'avez qu'une seule base de données.
	 */
	public function __construct($labelDB=null){
		$this->responseType = ResponseType::TEMPLATE;
		$this->template = new ListTemplate();
		$this->useGET = true;
		$this->tabSelect = null;
		$this->orderBy = null;
		$this->recherche = true;
		if($labelDB == null)
			$this->db = Database::getInstance();
		else 
			$this->db = Database::getInstance($labelDB);
	}


			/*-*****************
			***   METHODES   ***
			*******************/

	/**
	 * Execute la requete SQL dont la base est passee en parametres.
	 * Cette base sera augmentee par les divers parametres fournis par la variable GET avant d'etre execute, ou par les attributs tabSelect mask et orderBy si vous ne souhaitez pas utiliser GET.
 	 * Le format des resultats obtenus par la requete dépend du ResponseType spécifié.
	 * @param mixed $baseSQL : la requete a executer. Peut etre de type string ou SQLRequest.
	 * @return mixed
	 * * l'objet de reponse dependant de $ResponseType, parametrable via la methode *setResponseType()*
	 * * false en cas d'erreur, par exemple si ListManager ne parvient aps à utiliser la base de données
	 */
	public function construct($baseSQL){
		if($baseSQL instanceof SQLRequest)
			$requete = $baseSQL;
		else 
			$requete = new SQLRequest($baseSQL);

		//Construction de la requete a partir de variables GET disponibles
		if($this->useGET){
			
			// Conditions (where)
			if(isset($_GET['tabSelect'])){
				$tabWhere = array();
				foreach ($_GET['tabSelect'] as $titre => $valeur) {
					if(strlen($valeur) > 0)
						$tabWhere[$titre] = $valeur;
				}
				if(count($tabWhere) > 0)
					$requete->where($tabWhere);
			}
			
			// Tri (Order By)
			if(isset($_GET['orderBy'])){
				$requete->orderBy(explode(',', $_GET['orderBy']));
			}
		}
		else {
			if($this->tabSelect != null)	
				$requete->where($this->tabSelect);
			if($this->orderBy != null)	
				$requete->orderBy($this->orderBy);
		}

		//Execution de la requete
		return $this->execute($requete);

	}

	/**
	 * Execute une requete SQL *sans prendre en compte les données GET ni les données tabSelect, mask et orderBy*.
	 * Retourne le resultat dans le format specifie par ResponseType
	 * @param mixed $request : la requete a executer. Peut etre de type string ou SQLRequest.
 	 * @return string|bool :
	 * * l'objet de reponse dependant de $this->responseType, parametrable via la methode *setResponseType()*
	 * * false en cas d'erreur, par exemple si ListManager ne parvient aps à utiliser la base de données
	 */
	public function execute($request){

		// Gestion du parametre
		if($request instanceof SQLRequest)
			$requete = $request->__toString();
		else 
			$requete = $request;

		// Recuperation de l'objet DB
		if($this->db == null) {
			// Si la db est null alors on affiche une erreur
			echo '<br><b>[!]</b> ListManager::execute() : aucune base de donnees n\'est disponible ou instanciee<br>';
			return false;
		}

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

			/*-****************
			***   SETTERS   ***
			******************/

	/**
	 * Definit le format de la reponse des methodes *construct()* et *execute()*.
	 * A la suite de l'execution d'une requete SQL ListManager peut retourner une liste de données sous 5 formes différentes :
	 * * TEMPLATE (par defaut) pour obtenir un string representant la liste HTML contenant toutes les donnees 
	 * * ARRAY pour obtenir les resultats dans un array PHP (equivalent a PDOStaement::fetchAll())
	 * * JSON pour obtenir les donnees dans un objet encode en JSON
	 * * EXCEL pour obtenir les resultats dans une feuille de calcul Excel
	 * * OBJET pour obtenir un objet stdClass
	 * Par défaut le type de réponse est TEMPLATE. Vous pouvez le changer en indiquant le paramètre suivant
	 * @param ResponseType $responseType le nouveau type de réponse
	 */
	public function setResponseType($responseType){
		$this->responseType = $responseType;
	}

	/**
	 * Instancie une nouvelle connexion a une base de donnees.
	 * Cett méthode utilise la méthode *Database::instantiate()* et donc enregistre l'instance creee dans la classe Database. Spécifiez une etiquette si vous en utilisez plusieurs
	 * @param string $dsn le DSN (voir le manuel PHP concernant PDO)
	 * @param string $login le nom d'utilisateur pour la connexion
	 * @param string $mdp son mot de passe
	 * @param string $label l'etiquette de la base de données. Laisser à null si vous n'en utiliser qu'une seule
	 */
	public function connectDatabase($dsn, $login, $mdp, $label=null){
		if($label == null)
			$this->db = Database::instantiate($dsn, $login, $mdp);
		else 
			$this->db = Database::instantiate($dsn, $login, $mdp, $label);
			
		if($this->db == null)
			echo '<br><b>[!]</b> ListManager::connecterDatabase() : echec de connection<br>';
	}

	/**
	 * Definit la base de donnees qui sera utilisee pour l'execution des requetes SQL.
	 * Affiche un message d'erreur si la base de données n'a pas pu être récupérée.
	 * @param string|Database $dataBase : la base de données à utiliser. Peut être de type string ou Database :
	 * * Si string : represente l'etiquette de la base de donnees a utiliser.
	 * * Si null ou non spécifié : recupere la base de donnee principale de la classe Database.
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
	 * Definit le nouvel ID du tableau HTML
	 * @param string|null $id le nouvel id du tableau html. Si null aucun ID ne sera utilisé.
	 */
	public function setListeId($id){
		$this->template->setId($id);
	}

	/**
	 * Utiliser les variables get ou non.
	 * Definit si ListManager doit utiliser ou non les valeurs contenues dans GET pour construire le masque, le order by et le where de la requete SQL qui sera executee.
	 * @param boolean $valeur true ou false. Valeur par defaut : true
	 */
	public function useGET($valeur){
		if(!is_bool($valeur))
			return false;

		$this->useGET = $valeur;
	}

	/**
	 * Redefinit le nom des classes qui seront affectees une ligne sur deux dans la liste HTML (balises tr).
	 * Si les valeurs sont mises a null les classes ne seront pas affiche.
	 * @param string $class1 le nom de la classe des lignes impaires. Mettre à null si vous ne souhaitez pas de classe
	 * @param string $class2 le nom de la classe des lignes paires. Mettre a null si vous ne souhaitez pas de classe
	 */
	public function setRowsClasses($classe1, $classe2){
		$this->template->setRowsClasses($classe1, $classe2);
	}

	/**
	 * Definit le tableau à utiliser pour filtrer les données à selectionner.
	 * Ce tableau doit avoir pour format [nomColonne] => 'valeur filtre'. Pour le format des filtres veuillez consulter la doc de la methode *SQLRequeste->where()*.
	 * Cette methode passe automatiqument a false l'attribut sur l'utilisation des variables GET.
	 * @param array $tabSelect le tableau contenant les associations colonnes -> filtre à utiliser pour filtrer les données
	 */
	public function setTabSelect(array $tabSelect){
		$this->useGET = false;
		$this->tabSelect = $tabSelect;
	}

	/**
	 * Definit la façon de trier les donénes selectionnées dans la base de données représenté par un string.
	 * Passe automatiqument a faux l'attribut sur l'utilisation des variables GET pour la reecriture des requetes SQL
	 * @param string la liste des colonnes pour effectuer le tri
	 */
	public function setOrderBy($orderBy){
		$this->useGET = false;
		$this->orderBy = $orderBy;
	}

	/*
	 * Definit le masque à utiliser pour supprimer certaines colonnes lors de la selection de données.
	 * Passe automatiqument a faux l'attribut sur l'utilisation des variables GET pour la reecriture des requetes SQL
	 * @param string $mask la liste des numéros de colonne à masquer séparés par une virgule.
	 */
	// public function setMask($mask){
	// 	$this->useGET = false;
	// 	$this->mask = $masque;
	// }

	/**
	 * Definit si l'option recherche doit etre activee ou non. Si cette valeur est passee a false il ne sera plus possible pour l'utilisateur de filtrer les données de la liste
	 * @param boolean $valeur valeur par defaut : true
	 * @return false si la valeur spécifié n'est pas un boolean
	 */
	public function enableSearch($valeur){
		if(!is_bool($valeur))
			return false;

		$this->$template->enableSearch($valeur);
	}

	/**
	 * Définir un callback à utiliser dans le template.
	 * Definit le callback (la fonction) qui sera executee pour chaque valeur lors de l'affichage des donnees dans les cellules du tableau. Cette fonction doit etre definie comme il suit :
	 * * 3 parametres d'entree :
	 *    1. cellule : la valeur de l'element en cours
	 *    2. colonne : le nom de la colonne en cours
	 *    3. ligne   : le numero de la ligne en cours
	 * * valeur de retour de type string (ou du moins un type qui peut être transformé en string). *[!]* Si vous ne modifiez pas la valeur de la cellule penser tout de même à la retourner
	 * @param string $fonction le nom du callback a utiliser, null si aucun. Valeur par defaut : null
	 * @return boolean true si l'opération s'est bien déroulée et que la fonction existe false sinon (renvoie false si le paramètre est null)
	 */
	public function setCellCallback($fonction){
		if(!is_string($fonction))
			return false;

		return $this->template->setCellCallback($fonction);
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
	 * Définit le nombre max de liens vers les pages de la liste proposés par la pagination du template.
	 * La valeur par défaut est 15 : c'est à dire que par exemple le template propose les liens des pages 1 à 15 si l'utilisateur est sur la 1re page.
	 * @param int $valeur le nombre de liens à proposer
	 */
	public function setMaxPagesDisplayed($valeur){
		return $this->template->setMaxPagesDisplayed($valeur);
	}


			/*-****************
			 ***   GETTERS   ***
			 ******************/
	
	/**
	 * 
	 * @return ListTemplate l'objet template utilise par ListManager
	 */
	public function getTemplate() {
		return $this->template;
	}
	
	/**
	 * 
	 * @return array le tableau utilisé pour filtrer les données par colonne
	 */
	public function getTabSelect() {
		return $this->tabSelect;
	}
	
	/*
	 * 
	 * @return string le mask utilisé pour supprimer des colonnes. Le numéro des colonnes masquées est données séparés par des virgules
	 */
	// public function getMask() {
	// 	return $this->mask;
	// }
	
	/**
	 * 
	 * @return string le numéro des colonnes utilisées pour trier les données, séparés par par des virgules. Une colonne dont le numéro est négatif représente le tri décroissant.
	 */
	public function getOrderBy() {
		return $this->orderBy;
	}
	
}

?>