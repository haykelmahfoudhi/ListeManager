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
 * ListManager : construire, manipuler des listes de données à partir d'une requête SQL
 * 
 * C'est l'objet central du projet. Il joue le rôle d'interface entre le developpeur, les bases de données et les listes.
 * ListManager possède un comportement de base :
 * * Connection à une base de données, ou utilisation d'une base de données particulière de l'applicaiton en spécifiant son étiquette (cf. la doc de l'objet Database)
 * * Utilisation d'une requête SQL de base permettant la sélection
 * * Utilisation des données situées dans les variables GET de l'url pour modifier la requete SQL de base, à savoir
 *   * 'lm_tabSelect' : permet de filtrer les données par colonnes (se rajoute à la clause WHERE de la requete)
 *   * 'lm_orderBy' : permet de trier els données par ordre croissant / décroissant selon une colonne (ajoute le numéro de la colonne à la clause ORDER BY)
 *   * 'lm_excel' : lance le téléchargemennt du fichier excel généré par ListManager
 *   * 'lm_page' : correspond à la page de résultat affichée
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
	 * @var string $id correspond à l'id du ListManager, cet attribu est utile si vous utilisez plusieurs listes sur la meme page
	 */
	private $id;
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
	 * @var boolean specifie si la fonction recherche est diponible ou non. N'a d'effets que si vous construisez construct avec le template
	 */
	private $enableSearch;
	/**
	 * @var boolean spécifie si ListManger autorise le trie par colonne en modifiant la clause ORDER BY des requetes. N'a d'effet que si vous utilisez la méthode construct
	 */
	private $enableOrderBy;
	/**
	 * @var bool $enableExcel définit si ListTemplate propose la fonctionnalité d'export Excel
	 */
	private $enableExcel;
	/**
	 * @var array $meesages le tableau contenant l'ensemble des messages d'erreur générés par l'objet ListManager. S'affichent automatiquement si verbose = true
	 */
	private $messages;
	/**
	 * @var bool $verbose determine si ListManagert doit echo les messages d'erreur et d'avertissement ou non 
	 */
	private $verbose;
	/**
	 * @var array $mask correspond aux titre des colonnes à ne pas retourner lors de la selection de données
	 */
	private $mask;
	/**
	 * @var array tableau associatif pour l'affichage des titres des colonnes. Ce tableau à pour format [titre_colonne] => [titre_a_afficher]
	 */
	private $listTitles;

			/*-*******************************************
			***  CONSTANTES : OPTIONS DU CONSTRUCTEUR  ***
			*********************************************/
	/**
	 * @var const NO_SEARCH à utiliser pour désactiver l'utilisation de la recherche par colonne 
	 */
	const NO_SEARCH = 1;
	/**
	 * @var const NO_EXCEL à utiliser dans le constructeur pour désactiver l'export de la liste en Excel
	 */
	const NO_EXCEL = 2;
	/**
	 * @var const NO_JS_MASK à utiliser dans le constructeur pour désactiver l'utilisation du masquage de colonne en JS
	 */
	const NO_JS_MASK = 4;
	/**
	 * @var const NO_ORDER_BY à utiliser dans le constructeur pour désactiver le tri des donénes par colonnes
	 */
	const NO_ORDER_BY = 8;
	/**
	 * @var const NO_CSS à utiliser dans le constructeur pour désactiver l'utilisation du CSS par déafut
	 * Implique l'option UNFIXED_TITLES
	 */
	const NO_CSS = 16;
	/**
	 * @var const NO_PAGING à utiliser dans le constructeur pour désactiver la pagination et la navigation entre les pages de résutlats.
	 */
	const NO_PAGING = 32;
	/**
	 * @var const NO_VERBOSE à utiliser dans le constructeur pour désactiver le mode verbeux
	 */
	const NO_VERBOSE = 64;
	/**
	 * @var const UNFIXED_TITLES à utiliser dans le constructeur pour empecher les titres de rester fixés lorsque l'utilisateur scroll. 
	 */
	const UNFIXED_TITLES = 128;

	/**
	 * @var array $optionsArray tableau associatif entre chaque option du constructeur et la méthode permettant de desactiver la fonctionnalité correspondate
	 */
	private static $optionsArray = [
		self::NO_SEARCH => 'enableSearch',
		self::NO_EXCEL => 'enableExcel',
		self::NO_ORDER_BY => 'enableOrderBy',
		self::NO_JS_MASK => 'enableJSMask',
		self::NO_CSS => 'applyDefaultCSS',
		self::NO_PAGING => 'setPagingLinksNb',
		self::NO_VERBOSE => 'verbose',
		self::UNFIXED_TITLES => 'fixTitles'
	];
	
	
			/*-*********************
			***   CONSTRUCTEUR   ***
			***********************/
	
	/**
	 * Construit un nouvel objet ListManager définit par son comportement de base.
	 * Tente de récupérer la base de données principale de l'application. Vous pouvez donc utiliser la méthode *Database::instantaite()* au préalable pour ne pas avoir à spécifier la base de données à utiliserpar la suite.
	 * Précisez l'etiquette de la base de données à utiliser si nécessaire
	 * @var Database|string $db l'insatnce de Database à utiliser ou son étiquette. Laissez null si vous n'avez qu'une seule base de données.
	 */
	public function __construct($id, $db, array $options){
		$this->setId($id);
		$this->setDatabase($db);
		$this->template = new ListTemplate($this);
		$this->responseType = ResponseType::TEMPLATE;
		$this->enableSearch = true;
		$this->enableOrderBy = true;
		$this->enableExcel = true;
		$this->mask = array();
		$this->messages = array();
		$this->verbose = true;
		$this->listTitles = array();

		// Gestion des options : désactivation de fonctionnalités
		$i = 0;
		foreach ($options as $option) {
			$i++;
			if(isset(self::$optionsArray[$option]))
				call_user_func_array([$this, self::$optionsArray[$option]], array(false));

			// Option non reconnue
			else {
				$message = '<br><b>[!]</b> ListManager::__construct() : l\'option n°'.$i.' (valeur = "'.$option.'") n\'est pas reconnue<br>';
				if($this->verbose)
					echo $message;
				$this->messages[] = $message;
			}
		}
	}


			/*-*****************
			***   METHODES   ***
			*******************/

	/**
	 * Execute la requete SQL dont la base est passee en parametres.
	 * Cette base sera augmentee par les divers parametres fournis par la variable GET avant d'etre execute, ou par les attributs tabSelect et orderBy si vous ne souhaitez pas utiliser GET.
 	 * Le format des resultats obtenus par la requete dépend du ResponseType spécifié.
	 * @param mixed $baseSQL la requete a executer. Peut etre de type string ou SQLRequest.
	 * @param array $params (facultatif) à utiliser si vous saouhaitez passer par les méthodes prepare puis exécute pour exécuter votre requete SQL
	 * @return mixed
	 * * l'objet de reponse dependant de $ResponseType, parametrable via la methode *setResponseType()*
	 * * false en cas d'erreur, par exemple si ListManager ne parvient aps à utiliser la base de données
	 */
	public function construct($baseSQL, array $params=array()){

		// Gestion du parametre
		if(!$baseSQL instanceof SQLRequest)
			$requete = new SQLRequest($baseSQL, $this->db->oracle());
		else {
			$baseSQL->prepareForOracle($this->db->oracle());
			$requete = $baseSQL;
		}

		// Construction de la requete a partir de variables GET disponibles :
		// Conditions (where)
		if($this->enableSearch && isset($_GET['lm_tabSelect'.$this->id])){
			$tabWhere = array();
			foreach ($_GET['lm_tabSelect'.$this->id] as $titre => $valeur) {
				if(strlen($valeur) > 0)
					$tabWhere[$titre] = $valeur;
			}
			if(count($tabWhere) > 0)
				$requete->where($tabWhere);
		}
		
		// Tri (Order By)
		if($this->enableOrderBy && isset($_GET['lm_orderBy'.$this->id])){
			$requete->orderBy(explode(',', $_GET['lm_orderBy'.$this->id]));
		}

		// Excel
		if(isset($_GET['lm_excel'.$this->id])){
			$this->setResponseType(ResponseType::EXCEL);
		}

		//Execution de la requete
		return $this->execute($requete, $params);

	}

	/**
	 * Execute une requete SQL *sans prendre en compte les données GET ni les données tabSelect et orderBy*.
	 * Retourne le resultat dans le format specifie par ResponseType
	 * @param mixed $request : la requete a executer. Peut etre de type string ou SQLRequest.
	 * @param array $params (facultatif) à utiliser si vous saouhaitez passer par les méthodes prepare puis exécute pour exécuter votre requete SQL
 	 * @return string|bool
	 * * l'objet de reponse dependant de $this->responseType, parametrable via la methode *setResponseType()*
	 * * false en cas d'erreur, par exemple si ListManager ne parvient aps à utiliser la base de données
	 */
	public function execute($request, array $params=array()){
		
		// Gestion du parametre
		if($request instanceof SQLRequest) {
			$request->prepareForOracle($this->db->oracle());
			$requete = $request->__toString();
		}
		else 
			$requete = $request;

		// Si la db est null alors on affiche une erreur
		if($this->db == null) {
			$message =  '<br><b>[!]</b> ListManager::execute() : aucune base de donnees n\'est disponible ou instanciee<br>';
			$this->messages[] = $message;

			//Affiche le message d'erreur
			if($this->verbose) 
				echo $message;
			return false;
		}

		//Execution de la requete
		$reponse = $this->db->execute($requete, $params);

		//Creation de l'objet de reponse
		switch ($this->responseType){
			case ResponseType::OBJET:
			return $reponse;

			case ResponseType::TABLEAU:
				if($reponse->error()) {
					$this->messages[] = $reponse->getErrorMessage();
					return null;
				}
				else {
					//Application du mask
					if(count($this->mask) > 0 || $this->db->oracle()) {
						while(($ligne = $reponse->nextLine()) != null) {
							$aInserer = array();
							foreach ($ligne as $colonne => $valeur) {
								if(!in_array($colonne, $this->mask))
									$aInserer[$colonne] = $valeur;
							}
							$donnees[] = $aInserer;
						}
					}
					else {
						$donnees = $reponse->dataList();
					}
					return $donnees;
				}

			case ResponseType::EXCEL:
				if($this->enableExcel){
					$chemin = $this->generateExcel($reponse);
					if($chemin != false) {
						header('Location:'.$chemin);
					}
					else {
						$message = '<br><b>[!]</b>ListManager::execute() : le fichier excel n\'a pas pu être généré<br>';
						$this->messages[] = $message;
						if($this->verbose)
							echo $message;
						return false;
					}
				}
				else{
					$message = '<br><b>[!]</b>ListManager::execute() : la foncitonnalité d\'export excel est désactivée pour cette liste<br>';
					$this->messages[] = $message;
					if($this->verbose)
						echo $message;
					return false;
				}

			case ResponseType::JSON:
				$ret = new \stdClass();
				$ret->error = $reponse->error();
				if($ret->error){
					$ret->data = null;
					$ret->errorMessage = $reponse->getErrorMessage();
					$this->messages[] = $ret->errorMessage;
				}
				else{
					// Applicaiton du mask
					if(count($this->mask) > 0 || $this->db->oracle()) {
						while (($ligne = $reponse->nextLine()) != null) {
							$aInserer = array();
							foreach ($ligne as $colonne => $valeur) {
								if(!in_array($colonne, $this->mask))
									$aInserer[$colonne] = $valeur;
							}
							$ret->data[] = $aInserer;
						}
					}
					else
						$ret->data = $reponse->dataList();
				}
			return json_encode($ret);


			case ResponseType::TEMPLATE:
				// Selection de la page
				if($this->template->issetPaging() && isset($_GET['lm_page'.$this->id]) && $_GET['lm_page'.$this->id] > 0)
					$this->template->setCurrentPage($_GET['lm_page'.$this->id]);
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
	 * * OBJET pour obtenir un objet \stdClass
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
			
		if($this->db == null) {
			$message = '<br><b>[!]</b> ListManager::connectDatabase() : echec de connection<br>';
			$this->messages[] = $message;
			if($this->verbose)
				echo $message;
		}
	}

	/**
	 * Definit la base de donnees qui sera utilisee pour l'execution des requetes SQL.
	 * Affiche un message d'erreur si la base de données n'a pas pu être récupérée.
	 * @param string|Database $dataBase la base de données à utiliser. Peut être de type string ou Database :
	 * * Si string : represente l'etiquette de la base de donnees a utiliser.
	 * * Si null ou non spécifié : recupere la base de donnee principale de la classe Database.
	 * @return bool true si l'operation est un succès, false sinon + affichage d'un message d'erreur si verbose
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

		if($this->db == null) {
			$message = '<br><b>[!]</b> ListManager::setDatabase() : aucune base de donnees correspondante<br>';
			$this->messages[] = $message;
			if($this->verbose)
				echo $message;
			return false;
		}
		return true;
	}

	/**
	 * Définit l'id HTML de la balise table correspondant à la liste
	 * @param string $id le nouvel id du tableau. Si null aucun ID ne sera affiché.
	 */
	public function setId($id) {
		if(strlen($id) > 0)
			$this->id = '_'.$id;
		else 
			$this->id = '';
	}

	/**
	 * Définit le nouveau masque à appliquer.
	 * Le masque est un tableau contenant le nom des colonnes que vous ne souhaitez pas afficher dans la liste HTML
	 * @var array $mask le nouveau masque à appliquer. Si null : aucun masque ne sera applqiué
	 * @return bool false si le paramètre en entré n'est ni null, ni un array
	 */
	public function setMask($mask) {
		if($mask == null)
			$this->mask = array();
		else if (is_array($mask))
			$this->mask = $mask;
		else
			return false;
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
	 * Permet de changer les titres des colonnes de la liste
	 * Le tableau à passer en paramètre est un tableau associatif où la clé correspond au nom de la colonne tel qu'il est restitué lors de la selection des données, associé au titre que vous souhaitez afficher
	 * @param array le tableau des nouveaux titres
	 */
	public function setListTitles(array $liste) {
		$this->listTitles = $liste;
	}

	/**
	 * Definit si l'option recherche doit etre activee ou non. Si cette valeur est passee a false il ne sera plus possible pour l'utilisateur de filtrer les données de la liste
	 * @param boolean $valeur valeur par defaut : true
	 * @return false si la valeur spécifié n'est pas un boolean
	 */
	public function enableSearch($valeur){
		if(!is_bool($valeur))
			return false;

		$this->enableSearch = $valeur;
	}

	/**
	 * Définit si l'option de tri par colonne est acitvée ou non pour cette liste. Si désactivée, l'utilisateur ne pourra plus cliquer sur les colonnes pour trier
	 * @param boolean $valeur valeur par defaut : true
	 * @return false si la valeur spécifié n'est pas un booléen
	 */
	public function enableOrderBy($valeur) {
		if(!is_bool($valeur))
			return false;

		$this->enableOrderBy = $valeur;
	}

	/**
	 * Définir un callback à appeler dans chaque cellule de la liste.
	 * Definit le callback (la fonction) qui sera executee pour chaque valeur lors de l'affichage des donnees dans les cellules du tableau. Cette fonction doit etre definie comme il suit :
	 * * 4 parametres d'entree :
	 *    1. cellule : la valeur de l'element en cours
	 *    2. colonne : le nom de la colonne en cours
	 *    3. numLigne   : le numero de la ligne en cours
	 *    4. ligne    : un array associatif contenant toutes les données de la ligne en cours
	 * * valeur de retour de type string (ou du moins un type qui peut être transformé en string). Si vous voulez laissez la case vide, retournez false
	 * @param callable $fonction le nom du callback a utiliser, null si aucun. Valeur par defaut : null
	 * @param bool $replaceTagTD définit si le callback définit réécrit les balises td ou non. Par défaut ce paramètre vaut false, ce qui signifit que ListTemplate écrit automatiquement des balises td de la liste.
	 * @return boolean true si l'opération s'est bien déroulée et que la fonction existe false sinon (renvoie false si le paramètre est null)
	 */
	public function setCellCallback(callable $fonction, $replaceTagTD=false){
		return $this->template->setCellCallback($fonction, $replaceTagTD);
	}

	/**
	 * Définir un callback à appeler à la création de chaque ligne de la liste.
	 * Ce callback sera appelé par le template à la création d'une nouvelle balise tr (balise ouvrante) et doit avoir pour caractéristiques :
	 *  * 2 paramètres d'entrée :
	 *    * 1. numero  : correspond au numéro de la ligne en cours
	 *    * 2. donnees : array php contenant l'ensemble des données selectionnées dans la base de données qui seront affichées dans cette ligne du tableau
	 *  * valeur de retour de type string (ou du moins un type qui peut être transformé en string). Si vous voulez laissez la case vide, retournez false
	 * @param callable $fonction le nom du callback a utiliser, null si aucun. Valeur par defaut : null
	 */
	public function setRowCallback(callable $fonction){
		$this->template->setRowCallback($fonction);
	}

	/**
	 * Définir un callback pour rajouter manuellement des colonnes dans votre liste
	 * Ce callback sera appelé par le template à la fin de la création des titres ET a la fnc de la création de chaque ligne de la liste. La fonction doit correspondre au format suivant
	 *  * 3 paramètres d'entrée :
	 *    * 1. numLigne  : int correspond au numéro de la ligne en cours
	 *    * 2. donnees   : array contenant l'ensemble des données selectionnées dans la base de données qui seront affichées dans cette ligne du tableau. Vaut null pour les titres
	 *    * 3. estTtitre : boolean vaut true si la fonciton est appelée dans la ligne des titres, false sinon 
	 *  * valeur de retour de type string (ou du moins un type qui peut être transformé en string).
	 * @param callable $fonction le nom du callback a utiliser, null si aucun. Valeur par defaut : null
	 */
	public function setColumnCallback(callable $fonction){
		$this->template->setColumnCallback($fonction);
	}

	/**
	 * Definit le nombre de resultats a afficher sur une page. Valeur par defaut = 50
	 * @param int $valeur le nombre de lignes a afficher par pages
     * @return boolean faux si la valeur entree est incorrecte
	 */
	public function setNbResultsPerPage($valeur){
		return $this->template->setNbResultsPerPage($valeur);
	}

	/* TODO
	 * Definit si ListManager doit utiliser ou non le systeme de cache pour accelerer
	 * la navigation entre les pages de la liste
	 * @param boolean $valeur : true pour activer le fonctionnement par cache, false sinon
	 */
	// public function useCache($valeur){
	// 	if(!is_bool($valeur))
	// 		return false;

	// 	$this->template->useCache($valeur);
	// }

	/**
	 * Définit le nombre max de liens vers les pages de la liste proposés par la pagination du template.
	 * La valeur par défaut est 15 : c'est à dire que par exemple le template propose les liens des pages 1 à 15 si l'utilisateur est sur la 1re page.
	 * @param int $valeur le nombre de liens à proposer
	 */
	public function setPagingLinksNb($valeur){
		return $this->template->setPagingLinksNb($valeur);
	}


	/**
	 * Définit la taille maximale des champs de saisie pour la recherche
	 * @param int $valeur la nouvelle taille maximale des champs de saisie pour al recherche
	 * @return bool false si l'argument est incorrect (pas un int, infèrieur à 0)
	 */
	public function setMaxSizeInputs($valeur) {
		return $this->template->setMaxSizeInputs($valeur);
	}

	/**
	 * Définit si ListTemplate doit proposer l'export de données en format excel à l'utilisateur. Valeur apr défaut : true
	 * @param bool $valeur : la nouvelle valeur à appliquer
	 * @return bool false si le paramètre n'est aps un booléen
	 */
	public function enableExcel($valeur){
		return $this->template->enableExcel($valeur);
	}

	/**
	 * Définit si le template propose la fonctionnalité de masquage des colonnes coté client en JS
	 * @param bool $valeur : la nouvelle valeur à appliquer
	 * @return bool false si le paramètre n'est aps un booléen
	 */
	public function enableJSMask($valeur){
		return $this->template->enableJSMask($valeur);
	}

	/**
	 * Définit si ListTemplate affiche ou non le nombre de résultats total retournée par la requete
	 * @param bool $valeur true pour activer, false pour desactiver
	 * @return bool false si l'argument n'est pas un booleen
	 */
	public function displayResultsNb($valeur) {
		return $this->template->displayResultsNb($valeur);
	}

	/**
	 * Définit si le template doit charger le fichier CSS par défaut et appliquer le style du template par déaut
	 * Si vous souhaitez personnaliser le style de votre liste vous devriez desactiver cette option et inclure votre propre fichier CSS
	 * @param bool $valeur false pour desactiver, true pour activer
	 * @return bool false si l'argument n'est pas un booleen
	 */
	public function applyDefaultCSS($valeur) {
		return $this->template->applyDefaultCSS($valeur);
	}

	/**
	 * Définit si ListManager doit afficher ou non les messages d'erreur générés.
	 * Dans les deux cas vous pourrez récupérer tous els messages d'erreur produits grâce a la méthode getErrorMessages
	 * @param bool $valeur la nouvelle valeur à appliquer pour la verbosité
	 * @return bool false si l'argument n'est pas un booléen
	 */
	public function verbose($valeur) {
		if(!is_bool($valeur))
			return false;

		$this->verbose = $valeur;
	}

	/**
	 * Permet d'ajouter une rubrique d'aide ou une legende à la liste actuelle
	 * @param string|null $link : le lien url vers la page d'aide. Si null alors le lien sera desactivé
	 */
	public function setHelpLink($link) {
		return $this->template->setHelpLink($link);
	}

	/**
	 * Définit sui les titres de votre liste restent fixés en haut de l'écran lorsque l'utilisateur scroll sur la page.
	 * @var bool valeur true pour activer false pour désactiver cette option
	 * @return bool false si l'arguemnt n'est pas un booléen.
	 */
	public function fixTitles($valeur) {
		return $this->template->fixTitles($valeur);
	}


			/*-****************
			***   GETTERS   ***
			******************/

	public function getId() {
		return $this->id;
	}

	public function getListTitles() {
		return $this->listTitles;
	}

	public function getMask() {
		return $this->mask;
	}

	public function isExcelEnabled() {
		return $this->enableExcel;
	}

	public function isSearchEnabled() {
		return $this->enableSearch;
	}

	public function isOrderByEnabled() {
		return $this->enableOrderBy;
	}
	
	/**
	 * @return array le tableau utilisé pour filtrer les données par colonne
	 */
	public function getTabSelect() {
		return $this->tabSelect;
	}
	
	/**
	 * @return string le numéro des colonnes utilisées pour trier les données, séparés par par des virgules. Une colonne dont le numéro est négatif représente le tri décroissant.
	 */
	public function getOrderBy() {
		return $this->orderBy;
	}

	/**
	 * @return array l'ensemble des messages d'erreur générés par cet objet
	 */
	public function getErrorMessages() {
		return $this->messages;
	}
	

			/*-****************
			***   PRIVATE   ***
			******************/

	/**
	 * Génère un fichier excel à partir d'une réponse de requete en utilisant la bibliothèque PHPExcel.
	 * Le fichier généré sera sauvegardé dans le dossier excel/, et le chemin complet de ce fichier sera retournée par la méthode
	 * @param RequestResponse $reponse l'objet réponse produit par l'exécution de la requete SQL
	 * @return bool|string le chemin du fichier généré, ou false en cas d'erreur 
	 */
	private function generateExcel(RequestResponse $reponse) {
		if($reponse->error())
			return false;

		// Création de l'objet PHPExcel
		$phpExcel = new \PHPExcel();
		$phpExcel->setActiveSheetIndex(0);

		// Ajout des propriétés
		$phpExcel->getProperties()->setCreator("ListManager")
			->setLastModifiedBy("ListManager")
			->setTitle("Liste de données")
			->setSubject("Liste de données")
			->setDescription("Feuille de données généré automatiqument à partir de l'url ".$_SERVER['REQUEST_URI']);
		
		// Création des titres
		$titres = $reponse->getColumnsName();
		$col = 'A';
		$phpExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(20);
		$width = [];
		$maxWidth = 30;
		for ($i = 0; $i < count($titres); $i++) {
			// On vérifie que la colonne n'est pas masquée
			if(!in_array($titres[$i], $this->mask)) {

				// Préparation du titre à insérer
				if(isset($this->listTitles[$titres[$i]]))
					$titre = $this->listTitles[$titres[$i]];
				else
					$titre = $titres[$i];

				// Mise en forme & insertion des titres
				$width[$col] = min(strlen($titre), $maxWidth);
				$phpExcel->getActiveSheet()->getColumnDimension($col)->setWidth($width[$col]);
				$phpExcel->getActiveSheet()->setCellValue('G'.($i+1), $types[$i]->ty);
				$phpExcel->getActiveSheet()->setCellValue($col.'1', $titre);
	 			$phpExcel->getActiveSheet()->getStyle($col.'1')->applyFromArray(array(
	 				'font' => array(
	 					'bold' => true,
	 					'size' => 13,
	 				),
	 				'fill' => array(
	 					'type' => \PHPExcel_Style_Fill::FILL_SOLID,
	 					'color' => array('rgb' => 'CCCCCC'),
	 				),
	 			));
				$col++;
			}
		}

		// Insertion des données
		while(($ligne = $reponse->nextLine()) != null)
			$donnees[] = $ligne;
		$i = 2;
		foreach ($donnees as $ligne) {
			// Hauteur de ligne
			$phpExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(25);
			// Remplissage colonne par colonne
			$col = 'A';
			foreach ($ligne as $titre => $cellule){
				// On vérifie que la colonne n'est pas masquée
				if(!in_array($titre, $this->mask)) {
					// Insertion de la donnée
					$phpExcel->getActiveSheet()->setCellValue($col.($i), $cellule);

					//Modification largeur colonne
					$cellWidth = min(strlen($cellule), $maxWidth);
					if($cellWidth > $width[$col]){
						$width[$col] = $cellWidth;
						$phpExcel->getActiveSheet()->getColumnDimension($col)->setWidth($cellWidth);
					}
					$col++;
				}
			}
			$i++;
		}
		
		// Ecriture du fichier
		try {
			$writer = \PHPExcel_IOFactory::createWriter($phpExcel, 'Excel2007');
			$chemin = LM_XLS.'Liste LM-'.uniqid().'.xlsx';
			$writer->save($chemin);
		}
		catch (\Exception $e) {
			if($this->verbose)
				echo "<br><b>[!]</b>ListManager::generateExcel() erreur création excel : ".$e->getMessage();
			return null;
		}
		return $chemin;
	}
}

?>