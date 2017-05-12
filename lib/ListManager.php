<?php

namespace LM;

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
	 * Valeur par défaut : false. Si cette valeur est passée à true, il vous sera possible de spécifier vous même les objets à utiliser pour modifier la requete SQL (tabSelect & orderBy)
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
	
	
			/*-*********************
			***   CONSTRUCTEUR   ***
			***********************/
	
	/**
	 * Construit un nouvel objet ListManager définit par son comportement de base.
	 * Tente de récupérer la base de données principale de l'application. Vous pouvez donc utiliser la méthode *Database::instantaite()* au préalable pour ne pas avoir à spécifier la base de données à utiliserpar la suite.
	 * Précisez l'etiquette de la base de données à utiliser si nécessaire
	 * @var Database|string $db l'insatnce de Database à utiliser ou son étiquette. Laissez null si vous n'avez qu'une seule base de données.
	 */
	public function __construct($db=null){
		$this->responseType = \LM\ResponseType::TEMPLATE;
		$this->template = new \LM\ListTemplate();
		$this->useGET = true;
		$this->tabSelect = null;
		$this->orderBy = null;
		$this->recherche = true;
		$this->mask = null;
		$this->messages = array();
		$this->verbose = true;
		$this->setDatabase($db);
	}


			/*-*****************
			***   METHODES   ***
			*******************/

	/**
	 * Execute la requete SQL dont la base est passee en parametres.
	 * Cette base sera augmentee par les divers parametres fournis par la variable GET avant d'etre execute, ou par les attributs tabSelect et orderBy si vous ne souhaitez pas utiliser GET.
 	 * Le format des resultats obtenus par la requete dépend du ResponseType spécifié.
	 * @param mixed $baseSQL : la requete a executer. Peut etre de type string ou SQLRequest.
	 * @return mixed
	 * * l'objet de reponse dependant de $ResponseType, parametrable via la methode *setResponseType()*
	 * * false en cas d'erreur, par exemple si ListManager ne parvient aps à utiliser la base de données
	 */
	public function construct($baseSQL){

		// Gestion du parametre
		if(!$baseSQL instanceof \LM\SQLRequest)
			$requete = new \LM\SQLRequest($baseSQL, $this->db->oracle());
		else {
			$baseSQL->prepareForOracle($this->db->oracle());
			$requete = $baseSQL;
		}

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

			// Excel
			if(isset($_GET['excel'])){
				$this->setResponseType(\LM\ResponseType::EXCEL);
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
	 * Execute une requete SQL *sans prendre en compte les données GET ni les données tabSelect et orderBy*.
	 * Retourne le resultat dans le format specifie par ResponseType
	 * @param mixed $request : la requete a executer. Peut etre de type string ou SQLRequest.
 	 * @return string|bool :
	 * * l'objet de reponse dependant de $this->responseType, parametrable via la methode *setResponseType()*
	 * * false en cas d'erreur, par exemple si ListManager ne parvient aps à utiliser la base de données
	 */
	public function execute($request){
		
		// Gestion du parametre
		if($request instanceof \LM\SQLRequest) {
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
		$reponse = $this->db->execute($requete);

		//Creation de l'objet de reponse
		switch ($this->responseType){
			case \LM\ResponseType::OBJET:
			return $reponse;

			case \LM\ResponseType::TABLEAU:
				if($reponse->error())
					return null;
				else {
					while(($ligne = $reponse->nextLine()) != null)
						$donnees[] = $ligne;
					return $donnees;
				}

			case \LM\ResponseType::EXCEL:
				if($this->template->excelIsEnabled()){
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

			case \LM\ResponseType::JSON:
				$ret = new \stdClass();
				$ret->error = $reponse->error();
				if($ret->error){
					$ret->data = null;
					$ret->errorMessage = $reponse->getErrorMessage();
				}
				else{
					$ret->data = $reponse->dataList();
				}
			return json_encode($ret);


			case \LM\ResponseType::TEMPLATE:
				// Utilisation du masque
				if(count($this->mask) > 0)
					$this->template->setMask($this->mask);
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
			$this->db = \LM\Database::instantiate($dsn, $login, $mdp);
		else 
			$this->db = \LM\Database::instantiate($dsn, $login, $mdp, $label);
			
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
			$this->db = \LM\Database::getInstance();
		else {
			if($dataBase instanceof \LM\Database)
				$this->db = $dataBase;
			else 
				$this->db = \LM\Database::getInstance($dataBase);
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
	 * Definit un nouvel objet ListTemplate pour l'affichage des listes
	 * @param ListTemplate $template le nouveau template a definir.
	 */
	public function setTemplate(\LM\ListTemplate $template){
		$this->template = $template;
	}


	/**
	 * Définit l'id HTML de la balise table correspondant à la liste
	 * / ! \ Attention si vous changez l'id du tableau il se peut que le fichier JS associé ne fonctionne plus et que certaines telles que le masquage des colonnes ne soient plus possibles
	 * @param string $id le nouvel id du tableau. Si null aucun ID ne sera affiché.
	 */
	public function setIdTable($id) {
		$this->template->setIdTable($id);
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
	 * Utiliser les variables get ou non.
	 * Definit si ListManager doit utiliser ou non les valeurs contenues dans GET pour construire le order by et le tabSelect de la requete SQL qui sera executee.
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
	 * Permet de changer les titres des colonnes de la liste
	 * Le tableau à passer en paramètre est un tableau associatif où la clé correspond au nom de la colonne tel qu'il est restitué lors de la selection des données, associé au titre que vous souhaitez afficher
	 * @param array le tableau des nouveaux titres
	 * @return bool false si l'argument apssé n'est pas un tableau 
	 */
	public function setListTitles($array) {
		return $this->template->setListTitles($array);
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

	/**
	 * Definit si l'option recherche doit etre activee ou non. Si cette valeur est passee a false il ne sera plus possible pour l'utilisateur de filtrer les données de la liste
	 * @param boolean $valeur valeur par defaut : true
	 * @return false si la valeur spécifié n'est pas un boolean
	 */
	public function enableSearch($valeur){
		if(!is_bool($valeur))
			return false;

		$this->template->enableSearch($valeur);
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
	 * @param string $fonction le nom du callback a utiliser, null si aucun. Valeur par defaut : null
	 * @return boolean true si l'opération s'est bien déroulée et que la fonction existe false sinon (renvoie false si le paramètre est null)
	 */
	public function setCellCallback($fonction){
		return $this->template->setCellCallback($fonction);
	}

	/**
	 * Définir un callback à appeler à la création de chaque ligne de la liste.
	 * Ce callback sera appelé par le template à la création d'une nouvelle balise tr (balise ouvrante) et doit avoir pour caractéristiques :
	 *  * 2 paramètres d'entrée :
	 *    * 1. numero  : correspond au numéro de la ligne en cours
	 *    * 2. donnees : array php contenant l'ensemble des données selectionnées dans la base de données qui seront affichées dans cette ligne du tableau
	 *  * valeur de retour de type string (ou du moins un type qui peut être transformé en string). Si vous voulez laissez la case vide, retournez false
	 * @param string $fonction le nom du callback a utiliser, null si aucun. Valeur par defaut : null
	 * @return boolean true si l'opération s'est bien déroulée et que la fonction existe false sinon (renvoie false si le paramètre est null)
	 */
	public function setRowCallback($fonction){
		return $this->template->setRowCallback($fonction);
	}

	/**
	 * Définir un callback pour rajouter manuellement des colonnes dans votre liste
	 * Ce callback sera appelé par le template à la fin de la création des titres ET a la fnc de la création de chaque ligne de la liste. La fonction doit correspondre au format suivant
	 *  * 3 paramètres d'entrée :
	 *    * 1. numLigne  : int correspond au numéro de la ligne en cours
	 *    * 2. donnees   : array contenant l'ensemble des données selectionnées dans la base de données qui seront affichées dans cette ligne du tableau. Vaut null pour les titres
	 *    * 3. estTtitre : boolean vaut true si la fonciton est appelée dans la ligne des titres, false sinon 
	 *  * valeur de retour de type string (ou du moins un type qui peut être transformé en string).
	 * @param string $fonction le nom du callback a utiliser, null si aucun. Valeur par defaut : null
	 * @return boolean true si l'opération s'est bien déroulée et que la fonction existe false sinon (renvoie false si le paramètre est null)
	 */
	public function setColumnCallback($fonction){
		return $this->template->setColumnCallback($fonction);
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
	 * Définit si ListTemplate affiche ou non le nombre de résultats total retournée par la requete
	 * @param bool $valeur true pour activer, false pour desactiver
	 * @return bool false si l'argument n'est pas un booleen
	 */
	public function displayNbResults($valeur) {
		return $this->template->displayNbResults($valeur);
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


			/*-****************
			***   GETTERS   ***
			******************/
	
	/**
	 * @return ListTemplate l'objet template utilise par ListManager
	 */
	public function getTemplate() {
		return $this->template;
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
	private function generateExcel(\LM\RequestResponse $reponse) {
		if($reponse->error() || $reponse->getRowsCount() < 1)
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
		$types  = $reponse->getColumnsType();
		$col = 'A';
		$phpExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(20);
		for ($i = 0; $i < count($titres); $i++) {
			// On vérifie que la colonne n'est pas masquée
			if(!in_array($titres[$i], $this->mask)) {

				// Mise en forme & insertion des titres
				$phpExcel->getActiveSheet()->getColumnDimension($col)->setWidth(max($types[$i]->len, strlen($titres[$i])));
				$phpExcel->getActiveSheet()->setCellValue($col.'1', $titres[$i]);
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
		$donnees = $reponse->dataList();
		$i = 2;
		foreach ($donnees as $ligne) {
			// Hauteur de ligne
			$phpExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(25);
			// Remplissage colonne par colonne
			$col = 'A';
			for($j = 0; $j < count($ligne); $j++){
				// On vérifie que la colonne n'est pas masquée
				if(!in_array($titres[$j], $this->mask)) {

					// Insertion de la donnée
					$phpExcel->getActiveSheet()->setCellValue($col.($i), $ligne[$j]);
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