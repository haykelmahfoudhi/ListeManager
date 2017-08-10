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
 *   * 'lm_tabSelect' : permet de filtrer les données par colonnes (se rajoute à la clause WHERE ou HAVING de la requete)
 *   * 'lm_orderBy' : permet de trier els données par ordre croissant / décroissant selon une colonne (ajoute le numéro de la colonne à la clause ORDER BY)
 *   * 'lm_excel' : lance le téléchargemennt du fichier excel généré par ListManager
 *   * 'lm_page' : correspond à la page de résultat affichée
 * * L'exécution de la requête SQL
 * * La mise en forme des données dans une liste HTML dans un template correpsondant à la classe ListTemplate
 * 
 * Ce comportement de base et adaptable et modifibale grâce aux nombreuses méthodes de la classe. Vous pouvez entre autre choisir 
 * de retourner les données sous forme de array PHP, d'objet, de fichier excel... ou bien modifier le comportement du template HTML.
 * Il est également à noter que les setters de la classe retourne la référence de l'objet ($this) ($this) en cas de succès, ce qui vous permet d'appler à la suite un ensemble de setter dans la même instruction.
 * 
 * @author RookieRed
 *
 */
class ListManager {

	// Cette classe peut générer et afficher des messages d'erreur
	use T_ErrorGenerator;

			/*-******************
			***   ATTRIBUTS   ***
			********************/
	/**
	 * @var string _$_id correspond à l'id du ListManager, cet attribu est utile si vous utilisez plusieurs listes sur la meme page
	 */
	private $_id;
	/**
	 * @var ResponseType $_responseType correspond au format de données retourné par ListManager
	 * Valeur par défaut : TEMPLATE.
	 */
	private $_responseType;
	/**
	 * @var ListTemplate $_template objet template utilisé par ListManager pour générer les listes HTML
	 */
	private $_template;
	/**
	 * @var Database $_db objet Databse qui sera utilisé pour l'éxecution des requêtes SQL
	 */
	private $_db;
	/**
	 * @var boolean $_enableSearch specifie si la fonction recherche est diponible ou non. N'a d'effets que si vous construisez construct avec le template
	 */
	private $_enableSearch;
	/**
	 * @var boolean $_issetUserFilter indique au template si l'utilisateur à modifier les champs de recherche ou non.
	 */
	private $_issetUserFilter;
	/**
	 * @var boolean $_enableOrderBy spécifie si ListManger autorise le trie par colonne en modifiant la clause ORDER BY des requetes. N'a d'effet que si vous utilisez la méthode construct
	 */
	private $_enableOrderBy;
	/**
	 * @var bool $_enableExcel définit si ListTemplate propose la fonctionnalité d'export Excel
	 */
	private $_enableExcel;
	/**
	 * @var callable $_excelCallback le callback qui sera appelé lors de la génération d'un doncument excel
	 */
	private $_excelCallback;
	/**
	 * @var array $_mask correspond aux titre des colonnes à ne pas retourner lors de la selection de données
	 */
	private $_mask;
	/**
	 * @var array $_listTitles tableau associatif pour l'affichage des titres des colonnes. Ce tableau à pour format [titre_colonne] => [titre_a_afficher]
	 */
	private $_listTitles;
	/**
	 * @var array $_filterArray filtre posé par le développeur qui sera applqiué par défaut à la construction de la liste.
	 */
	private $_filterArray;
	/**
	 * @var array $_orderBy
	 */
	private $_orderBy;
	/**
	 * @var int $_longColWidth largeur des colonnes excédant 100 caractères.
	 */
	private $_longColWidth;

	/**
	 * @var array $idList contient la liste de tous les ID des objets ListManager instanciés
	 */
	private static $idList = [];

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
	 * @var const UNFIXED_TITLES à utiliser dans le constructeur pour empecher la fixation de la pagination en bas de l'écran. 
	 */
	const UNFIXED_PAGING = 256;
	/**
	 * @var const NO_RESULTS à utiliser dans le constructeur pour masquer la ligne contenant le nombre de résultats affichés et sélectionnés. 
	 */
	const NO_RESULTS = 512;
	/**
	 * @var const NO_HELP_LINK à utiliser dans le constructeur pour masquer le bouton-lien vers la page d'aide. 
	 */
	const NO_HELP_LINK = 1024;
	/**
	 * @var const DISPLAY_SEARCH à utiliser dans le constructeur pour masquer le bouton-lien vers la page d'aide. 
	 */
	const DISPLAY_SEARCH = 2048;

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
		self::UNFIXED_TITLES => 'fixTitles',
		self::UNFIXED_PAGING => 'fixPaging',
		self::NO_RESULTS => 'displayResultsInfos',
		self::NO_HELP_LINK => 'setHelpLink',
		self::DISPLAY_SEARCH => 'displaySearch'
	];
	
	
			/*-*********************
			***   CONSTRUCTEUR   ***
			***********************/
	
	/**
	 * Construit un nouvel objet ListManager et définit son comportement de base.
	 * Tous les paramètres de ce constructeurs sont facultatifs, et permettent dans l'ordre de définir l'identifiant de ListManager, la base de données à utilier pour les requêtes SQL, et un tableau 
	 * d'options pour désactiver certaines fonctionnalités de base sans passer par les setters de la classe.
	 * @param string $id l'identifiant de l'objet, à utiliser si vous souhaitez utiliser plusieurs listes sur la même page. Dans le cas contraire, laissez à null.
	 * @param Database|string $db l'insatnce de Database ou l'etiquette de la base de données à utiliser si nécessaire. Laissez null si vous n'avez qu'une seule base de données.
	 * @param array $options tableau de constantes permettant de désactiver certaines fonctionnalités de ListManager, c.f. la documention des constantes de la classe.
	 */
	public function __construct($id='', $db=null, array $options=array()){
		$this->setId($id);
		$this->verbose(!in_array(self::NO_VERBOSE, $options));
		$this->setDatabase($db);
		$this->_template = new ListTemplate($this);
		$this->_responseType = ResponseType::TEMPLATE;
		$this->_enableSearch = true;
		$this->_issetUserFilter = false;
		$this->_enableOrderBy = true;
		$this->_enableExcel = true;
		$this->_excelCallback = null;
		$this->_mask = array();
		$this->_listTitles = array();
		$this->_filterArray = [];
		$this->_orderBy = [];
		$this->_longColWidth = 50;

		// Gestion des options : désactivation de fonctionnalités
		$i = 0;
		foreach ($options as $option) {
			$i++;
			if(isset(self::$optionsArray[$option]))
				call_user_func_array([$this, self::$optionsArray[$option]], [$option==self::DISPLAY_SEARCH]);

			// Option non reconnue
			else {
				$this->addError('l\'option n°'.$i.' (valeur = "'.$option.'") n\'est pas reconnue' ,'__construct');
			}
		}
	}


			/*-*****************
			***   METHODES   ***
			*******************/

	/**
	 * Execute la requete SQL dont la base est passee en parametres.
	 * La méthode prend en paramètre la base de votre requête SQL, et la complète en fonction des paramètres d'url GET, en rajoutant soit des blocs à la clause WHERE ou HAVING soit à la clause ORDER BY si toutesfois ces deux foncitonnalitées sont activées.
 	 * Le format des resultats obtenus par la requete dépend du ResponseType spécifié.
	 * @param mixed $baseSQL la requete a executer. Peut etre de type string ou SQLRequest.
	 * @param array $params (facultatif) à utiliser si vous saouhaitez passer par les méthodes prepare puis exécute pour exécuter votre requete SQL
	 * @param array $having tableau contenant le nom des colonnes selectionnées dont le filtre doit se trouver dans la clause HAVING.
	 * @return mixed
	 * * l'objet de reponse dependant de $responseType, parametrable via la methode *setResponseType()*.
	 * * false en cas d'erreur, par exemple si ListManager ne parvient pas à utiliser la base de données.
	 */
	public function construct($baseSQL, array $params=array(), array $having=array()){

		// Gestion du parametre
		if(!$baseSQL instanceof SQLRequest)
			$sqlRequest = new SQLRequest($baseSQL, $this->_db->oracle());
		else {
			$sqlRequest = $baseSQL;
			$sqlRequest->prepareForOracle($this->_db->oracle());
		}

		// Conditions (where & having)
		$this->manageFilter($sqlRequest, $having);
		
		// Tri (Order By)
		if($this->_enableOrderBy){
			if(isset($_GET['lm_orderBy'.$this->_id])){
				$sqlRequest->removeOrderBy();
				$sqlRequest->orderBy(explode(';', $_GET['lm_orderBy'.$this->_id]));
			}
			$this->_orderBy = $sqlRequest->getOrderBy();
		}

		// Excel
		if(isset($_GET['lm_excel'.$this->_id])){
			$this->setResponseType(ResponseType::EXCEL);
		}

		// Si la db est null alors on affiche une erreur
		if($this->_db == null) {
			$this->addError('aucune base de donnees n\'est disponible ou instanciee', 'construct');
		}

		//Execution de la requete
		$reponse = $this->_db->execute($sqlRequest,
				array_merge($sqlRequest->getUserParameters(), $params));

		//Creation de l'objet de reponse
		switch ($this->_responseType){
			case ResponseType::OBJET:
				return $reponse;

			case ResponseType::TABLEAU:
				return $this->generateArray($reponse);

			case ResponseType::EXCEL:
				// Suppression des fichiers q'il y a plus d'un jour
				exec('find xls/ -name "*.xlsx" -mtime +1 -delete');
				$chemin = $this->generateExcel($reponse);
				if($chemin !== false){
					header('Location:'.$chemin);
				}
				// Si erreur de redirection : on propose le lien de téléchargement
				return $this->_template->construct(new RequestResponse(null, true,
						'Pour télécharger le fichier <a href="'.$chemin.'">cliquez ici</a>'));

			case ResponseType::JSON:
				return $this->generateJSON($reponse);


			case ResponseType::TEMPLATE:
				// Selection de la page
				if($this->_template->issetPaging() && isset($_GET['lm_page'.$this->_id]) && $_GET['lm_page'.$this->_id] > 0)
					$this->_template->setCurrentPage($_GET['lm_page'.$this->_id]);
				return $this->_template->construct($reponse);
		}
	
		$this->addError('type de réponse non reconnu : '.$this->_responseType, 'construct');
		return false;
	}

			/*-****************
			***   SETTERS   ***
			******************/

	/**
	 * Definit le format de la reponse de la methode *construct()*
	 * A la suite de l'execution d'une requete SQL ListManager peut retourner une liste de données sous 5 formes différentes :
	 * * TEMPLATE (par defaut) pour obtenir un string representant la liste HTML contenant toutes les donnees 
	 * * ARRAY pour obtenir les resultats dans un array PHP (equivalent a PDOStaement::fetchAll())
	 * * JSON pour obtenir les donnees dans un objet encode en JSON
	 * * EXCEL pour obtenir les resultats dans une feuille de calcul Excel
	 * * OBJET pour obtenir un objet \stdClass
	 * Par défaut le type de réponse est TEMPLATE. Vous pouvez le changer en indiquant le paramètre suivant
	 * @param ResponseType $responseType le nouveau type de réponse
	 * @return mixed false si le paramètre n'est pas un type de reponse correct sinon retourne la référence de l'objet ($this)
	 */
	public function setResponseType($responseType){
		if(!in_array($responseType, range(1,5)))
			return false;
		
		$this->_responseType = $responseType;
		return $this;
	}

	/**
	 * Instancie une nouvelle connexion a une base de donnees.
	 * Cette méthode utilise la méthode *Database::instantiate()* et donc enregistre l'instance creee dans la classe Database. Spécifiez une etiquette si vous en utilisez plusieurs
	 * En cas d'erreur de connection le message correspondant est enregistré et affiché si la verbosité est activée.
	 * @param string $dsn le DSN (voir le manuel PHP concernant PDO)
	 * @param string $login le nom d'utilisateur pour la connexion
	 * @param string $mdp son mot de passe
	 * @param string $label l'etiquette de la base de données. Laisser à null si vous n'en utiliser qu'une seule
	 * @return mixed false si ListManager ne parvient pas à connecter la base de données, sinon retourne la référence de l'objet ($this)
	 */
	public function connectDatabase($dsn, $login, $mdp, $label=null){
		if($label == null)
			$this->_db = Database::instantiate($dsn, $login, $mdp);
		else 
			$this->_db = Database::instantiate($dsn, $login, $mdp, $label);
			
		if($this->_db == null) {
			$this->addError('echec de connection : '.end(Database::getErrorMessages()), 'connectDatabase');
			return false;
		}
		return $this;
	}

	/**
	 * Definit la base de donnees qui sera utilisee pour l'execution des requetes SQL.
	 * Affiche un message d'erreur si la base de données n'a pas pu être récupérée.
	 * @param string|Database $dataBase la base de données à utiliser. Peut être de type string ou Database :
	 * * Si string : represente l'etiquette de la base de donnees a utiliser.
	 * * Si null ou non spécifié : recupere la base de donnee principale de la classe Database.
	 * @return mixed la référence de l'objet ($this) si l'operation est un succès, false sinon + enregistrement d'un message d'erreur et affichage si verbose
	 */
	public function setDatabase($dataBase=null){
		if($dataBase == null)
			$this->_db = Database::getInstance();
		else {
			if($dataBase instanceof Database)
				$this->_db = $dataBase;
			else 
				$this->_db = Database::getInstance($dataBase);
		}
		if($this->_db == null) {
			$this->addError('aucune base de donnees trouvée, avez-vous instancié une connexion ?', 'setDatabase');
			return false;
		}
		return $this;
	}

	/**
	 * Définit l'id HTML de la balise table correspondant à la liste
	 * @param string $id le nouvel id du tableau. Si null aucun ID ne sera affiché.
	 * @return mixed false en cas d'erreur (id déja utilisé) ou sinon la référence de l'objet ($this)
	 */
	public function setId($id) {
		if(in_array($id, self::$idList)){
			$this->addError("il existe déjà une liste avec liste avec l'id '$id', veuillez en choisir un nouveau", 'setId');
			return false;
		}

		// suppression de l'ancien id du tableau static
		if(($key = array_search($this->_id, self::$idList)) !== false)
			unset(self::$idList[$key]);

		// Modif de l'id
		if(strlen($id) > 0)
			$id = "_$id";
		else 
			$id = '';
		$this->_id = $id;
		self::$idList[] = $id;
		return $this;
	}

	/**
	 * Définit le nouveau masque à appliquer.
	 * Le masque est un tableau contenant le nom des colonnes que vous ne souhaitez pas afficher dans la liste HTML
	 * @param array $mask le nouveau masque à appliquer. Si null : aucun masque ne sera applqiué
	 * @return mixed false si le paramètre en entré n'est ni null, ni un array, sinon retourne la référence de l'objet ($this)
	 */
	public function setMask($mask) {
		if($mask == null)
			$this->_mask = array();
		else if (is_array($mask))
			$this->_mask = $mask;
		else
			return false;
		return $this;
	}

	/**
	 * Redefinit le nom des classes qui seront affectees une ligne sur deux dans la liste HTML (balises tr).
	 * Si les valeurs sont mises a null les classes ne seront pas affiche.
	 * @param string $class1 le nom de la classe des lignes impaires. Mettre à null si vous ne souhaitez pas de classe
	 * @param string $class2 le nom de la classe des lignes paires. Mettre a null si vous ne souhaitez pas de classe
	 * @return ListManager la référence de l'objet ($this)
	 */
	public function setRowsClasses($class1, $class2){
		$this->_template->setRowsClasses($class1, $class2);
		return $this;
	}

	/**
	 * Permet de changer les titres des colonnes de la liste
	 * Le tableau à passer en paramètre est un tableau associatif où la clé correspond au nom de la colonne tel qu'il est restitué lors de la selection des données, associé au titre que vous souhaitez afficher
	 * @param array le tableau des nouveaux titres
	 * @return ListManager la référence de l'objet ($this)
	 */
	public function setListTitles(array $liste) {
		foreach ($liste as $col => $val)
			$this->_listTitles[strtolower($col)] = $val;

		return $this;
	}

	/**
	 * Definit si l'option recherche doit etre activee ou non. Si cette valeur est passee a false il ne sera plus possible pour l'utilisateur de filtrer les données de la liste
	 * @param boolean $valeur la nouvelle valeur à attribuer.
	 * @return mixed false si la valeur spécifié n'est pas un boolean, sinon retourne la référence de l'objet ($this)
	 */
	public function enableSearch($valeur){
		if(!is_bool($valeur))
			return false;

		$this->_enableSearch = $valeur;
		return $this;
	}

	/**
	 * Définit si l'option de tri par colonne est acitvée ou non pour cette liste. Si désactivée, l'utilisateur ne pourra plus cliquer sur les colonnes pour trier
	 * @param boolean $valeur la nouvelle valeur à attribuer.
	 * @return mixed false si la valeur spécifié n'est pas un boolean, sinon retourne la référence de l'objet ($this)
	 */
	public function enableOrderBy($valeur) {
		if(!is_bool($valeur))
			return false;

		$this->_enableOrderBy = $valeur;
		return $this;
	}

	/**
	 * Définir un callback à appeler dans chaque cellule de la liste.
	 * Definit le callback (la fonction) qui sera executee pour chaque valeur lors de l'affichage des donnees dans les cellules du tableau. Cette fonction doit etre definie comme il suit :
	 * * 4 parametres d'entree :
	 *    1. cellule  : la valeur de l'element en cours
	 *    2. colonne  : le nom de la colonne en cours
	 *    3. numLigne : le numero de la ligne en cours
	 *    4. ligne    : un array associatif contenant toutes les données de la ligne en cours
	 *    5. numCol   : le numero de la colonne en cours
	 * * valeur de retour de type string (ou du moins un type qui peut être transformé en string). Si vous voulez laissez la case vide, retournez false
	 * @param callable $fonction le nom du callback a utiliser, null si aucun. Valeur par defaut : null
	 * @param bool $replaceTagTD définit si le callback définit réécrit les balises td ou non. Par défaut ce paramètre vaut false, ce qui signifit que ListTemplate écrit automatiquement des balises td de la liste.
	 * @return mixed false si vous entrez un non booléen comme 2e argument de cette méthode, sinon retourne la référence de l'objet ($this)
	 */
	public function setCellCallback(callable $fonction, $replaceTagTD=false){
		if($this->_template->setCellCallback($fonction, $replaceTagTD) === false)
			return false;
		return $this;
	}

	/**
	 * Définir un callback à appeler à la création de chaque ligne de la liste.
	 * Ce callback sera appelé par le template à la création d'une nouvelle balise tr (balise ouvrante) et doit avoir pour caractéristiques :
	 *  * 2 paramètres d'entrée :
	 *    1. numero  : correspond au numéro de la ligne en cours
	 *    2. donnees : array php contenant l'ensemble des données selectionnées dans la base de données qui seront affichées dans cette ligne du tableau
	 *  * valeur de retour de type string (ou du moins un type qui peut être transformé en string). Si vous voulez laissez la case vide, retournez false
	 * @param callable $fonction le nom du callback a utiliser, null si aucun.
	 */
	public function setRowCallback(callable $fonction){
		$this->_template->setRowCallback($fonction);
		return $this;
	}

	/**
	 * Définir un callback pour rajouter manuellement des colonnes dans votre liste.
	 * Ce callback sera appelé par le template à la fin de la création des titres ET a la fnc de la création de chaque
	 * ligne de la liste. La fonction doit correspondre au format suivant
	 *  * 3 paramètres d'entrée :
	 *    * 1. numLigne  : int correspond au numéro de la ligne en cours
	 *    * 2. donnees   : array contenant l'ensemble des données selectionnées dans la base de données qui seront affichées dans cette ligne du tableau. Vaut null pour les titres
	 *  * valeur de retour de type string (ou du moins un type qui peut être transformé en string).
	 * @param callable $fonction le nom du callback a utiliser, null si aucun. Valeur par defaut : null
	 * @param array $titres contient les titres des colonnes à ajouter
	 */
	public function setColumnCallback(callable $fonction, array $titres){
		$this->_template->setColumnCallback($fonction, $titres);
		return $this;
	}

	/**
	 * Définir un callback pour modifier les cellules et leur données lors de la génération du fichier excel.
	 * Ce callback sera appelé par ListManager lors de la génération d'un fichier excel, et vous permettra via l'objet PHPExcel de modifier
	 * la cellule et le document en cours. Le format du callback est :
	 *  *  paramètres d'entrée :
	 *    1. phpExcel : l'objet PHPExcel utilisé pour générer le document
	 *    2. donnees  : le tableau contenant les données insérées dans le doc
	 *    3. metas    : array d'objets des métas données des colonnes ( @see RequestResponse::getColumnsMeta() )
	 *    4. titres   : array des titres des colonnes telles qu'écrites dans le fichier
	 *  * pas de valeur de retour, tout se fait via l'objet PHPExcel
	 * @param callable $fonction le nom du callback a utiliser, null si aucun.
	 * @return ListManager la référence de l'objet ($this)
	 */
	public function setExcelCallback(callable $fonction){
		$this->_excelCallback = $fonction;
		return $this;
	}

	/**
	 * Definit le nombre de resultats a afficher sur une page. Valeur par defaut = 50
	 * @param int $valeur le nombre de lignes a afficher par pages
     * @return boolean false si la valeur entree est incorrecte, sinon retourne la référence de l'objet ($this)
	 */
	public function setNbResultsPerPage($valeur){
		if($this->_template->setNbResultsPerPage($valeur) === false)
			return false;
		
		return $this;
	}

	/**
	 * Définit le nombre max de liens vers les pages de la liste proposés par la pagination du template.
	 * La valeur par défaut est 15 : c'est à dire que par exemple le template propose les liens des pages 1 à 15 si l'utilisateur est sur la 1re page.
	 * @param int $valeur le nombre de liens à proposer
	 * @return mixed false si la valeur spécifié et un entier incorrect, sinon retourne la référence de l'objet ($this)
	 */
	public function setPagingLinksNb($valeur){
		if($this->_template->setPagingLinksNb($valeur) === false)
			return false;

		return $this;
	}

	/** 
	 * Applique un filtre par défaut sur les données de la liste.
	 * Via cette méthode vous pourrez appliquer un filtre par défaut dans les colonnes de votre choix, que l'utilisateur pourra modifier 
	 * ou supprimer lors de la navigation
	 * @param array $filter le tableau contenant toutes les conditions à rajouter. Ce tableau aura la forme suivante :
	 * ['nomColonne1' => 'condition1', 'nomColonne2' => 'cond2A,cond2B,cond3C'] ... Il est possible de combiner les conditions en les séparant par une virgule. Ainsi la condition 'prenom' => 'Roger,Patrick' recherchera tous ceux ayant le prénom Roger ou Patrick
	 * Une condition prend la forme suivante : [OPERATEUR][VALEUR]
	 * Les opérateurs possibles sont :
	 * * (pas d'opérateur) : égalité stricte avec la valeur entrée
	* * < > <= >= = : infèrieur, supèrieur, supèrieur ou égal, infèrieur ou égal, égal
	 * * / : opérateur 'différent de'. La condition '!' est traduite par différent de ''
	 * * - : correspond à NULL. Doit être utilisé seul, !\n est traduit par NOT NULL
	 * * << : opérateur 'BETWEEN' pour les dates
	 * @param bool $displaySearch passez ce paramètre à false si vous souhaitez ne pas afficher les champs de recherche, sinon laissez le à true
	 * @return ListManager $this method chaining
	 */
	public function setFilter(array $filter, $displaySearch=true){
		if($displaySearch !== null && $this->_template->displaySearchInputs($displaySearch) === false)
			return false;
		
		foreach ($filter as $col => $valeur) {
			if(strlen($valeur))
				$this->_filterArray[strtolower($col)] = $valeur;
			else
				unset($this->_filterArray[strtolower($col)]);
		}
		return $this;
	}

	/**
	 * Définit si ListTemplate affiche ou non les champs de recherche au chargmeent de la page.
	 * @param bool $valeur true pour activer, false pour desactiver
	 * @return bool false si l'argument n'est pas un booleen
	 */
	public function displaySearchInputs($valeur) {
		if($this->_template->displaySearchInputs($valeur) === false)
			return false;
		return $this;
	}

	/**
	 * Définit la taille maximale des champs de saisie pour la recherche.
	 * @param int $valeur la nouvelle taille maximale des champs de saisie pour al recherche
	 * @param bool $invariable passez ce paramètre a true si vous souhaitez que la même taille de champs soit appliquées à tous
	 * @return bool false si l'argument est incorrect (pas un int, infèrieur à 0)
	 */
	public function setMaxSizeInputs($valeur, $invariable=false) {
		if($this->_template->setMaxSizeInputs($valeur, $invariable) === false)
			return false;

		return $this;
	}

	/**
	 * Définit si ListTemplate doit proposer l'export de données en format excel à l'utilisateur. Valeur apr défaut : true
	 * @param bool $valeur : la nouvelle valeur à appliquer
	 * @return mixed false si la valeur spécifié n'est pas un boolean, sinon retourne la référence de l'objet ($this)
	 */
	public function enableExcel($valeur){
		if(!is_bool($valeur))
			return false;

		$this->_enableExcel = $valeur;
		return $this;
	}

	/**
	 * Définit si le template propose la fonctionnalité de masquage des colonnes coté client en JS
	 * @param bool $valeur la nouvelle valeur à appliquer
	 * @return mixed false si la valeur spécifié n'est pas un boolean, sinon retourne la référence de l'objet ($this)
	 */
	public function enableJSMask($valeur){
		if($this->_template->enableJSMask($valeur) === false)
			return false;

		return $this;
	}

	/**
	 * Définit si ListTemplate affiche ou non le nombre de résultats total retournée par la requete
	 * @param bool $valeur true pour activer, false pour desactiver
	 * @return mixed false si la valeur spécifié n'est pas un boolean, sinon retourne la référence de l'objet ($this)
	 */
	public function displayResultsInfos($valeur) {
		if($this->_template->displayResultsInfos($valeur) === false)
			return false;

		return $this;
	}

	/**
	 * Définit si le template doit charger le fichier CSS par défaut et appliquer le style du template par déaut
	 * Si vous souhaitez personnaliser le style de votre liste vous devriez desactiver cette option et inclure votre propre fichier CSS
	 * @param bool $valeur false pour desactiver, true pour activer
	 * @return mixed false si la valeur spécifié n'est pas un boolean, sinon retourne la référence de l'objet ($this)
	 */
	public function applyDefaultCSS($valeur) {
		if($this->_template->applyDefaultCSS($valeur) === false)
			return false;

		return $this;
	}

	/**
	 * Permet d'ajouter une rubrique d'aide ou une legende à la liste actuelle
	 * @param string $link le lien url vers la page d'aide. Si null alors le lien sera desactivé
	 * @return mixed false si la valeur spécifié n'est pas un boolean, sinon retourne la référence de l'objet ($this)
	 */
	public function setHelpLink($link) {
		if($this->_template->setHelpLink($link) === false)
			return false;

		return $this;
	}

	/**
	 * Définit si les titres de votre liste restent fixés en haut de l'écran lorsque l'utilisateur scroll sur la page.
	 * Il ne vous ai pas possible de fixer ni les titres ni la pagination si vous utilisez plusieurs instances de ListManager, par conséquent
	 * cette méthode vous retournera false si vous tentez quand même d'activer cette option.
	 * @param bool $valeur true pour activer false pour désactiver cette option
	 * @return mixed false si la valeur spécifié n'est pas un boolean, sinon retourne la référence de l'objet ($this)
	 */
	public function fixTitles($valeur) {
		if($this->_template->fixTitles($valeur) === false)
			return false;

		return $this;
	}

	/**
	 * Définit si les liens de page de votre liste restent fixés en bas de l'écran.
	 * Il ne vous ai pas possible de fixer ni les titres ni la pagination si vous utilisez plusieurs instances de ListManager, par conséquent
	 * cette méthode vous retournera false si vous tentez quand même d'activer cette option.
	 * @param bool $valeur true pour activer false pour désactiver cette option
	 * @return mixed false si la valeur spécifié n'est pas un boolean, sinon retourne la référence de l'objet ($this)
	 */
	public function fixPaging($valeur) {
		if($this->_template->fixPaging($valeur) === false)
			return false;

		return $this;
	}

	/**
	 * Ajoute des boutons dans la division boutons à gauche des listes
	 * @param array $buttons contient le code HTML des boutons à ajouter
	 * @return ListManager la référence de cet objet
	 */
	public function addButtons(array $buttons) {
		$this->_template->addButtons($buttons);
		return $this;
	}

	/**
	 * Définit la largeur pour les colonnes excédant 100 caractères.
	 * @param int $value nouvelle valeur
	 * @return bool|ListManager false si parametre invalide (<= 0 ou non entier), $this si opération ok (method chaining)
	 */
	public function setLongColWidth($value){
		if(intval($value) != $value || $value <= 0)
			return false;

		$this->_longColWidth = $value;
		return $this;
	}


			/*-****************
			***   GETTERS   ***
			******************/
	
	/**
	 * @return string l'id de l'objet ListManager tel qu'il sera affiché dans le template (avec un underscore avant)
	 */
	public function getId() {
		return $this->_id;
	}

	/**
	 * @return array le tableau associatif des titres des colonnes à remplacer par des nouveaux titres
	 */
	public function getListTitles() {
		return $this->_listTitles;
	}

	/**
	 * @return array le tableau associatif du filtre posé par le développeur.
	 */
	public function getFilter() {
		return $this->_filterArray;
	}
	
	/**
	 * @return boolean true si l'utilisateur a modifié ou effectué une recherche.
	 */
	public function issetUserFilter(){
		return $this->_enableSearch && $this->_issetUserFilter;
	}

	/**
	 * @return array le tableau contenant les colonnes de la clause order by.
	 */
	public function getOrderBy() {
		return $this->_orderBy;
	}

	/**
	 * Détermine si au moins un des noms de colonne se trouve dans le tableau du masque.
	 * @var string $column ... le nom ou les alias de la colonne 
	 * @return bool true si la colonne est à masquer, false sinon 
	 */
	public function isMasked($column) {
		$tabMask = array_map('strtolower', $this->_mask);
		for ($i=0; $i < func_num_args(); $i++) { 
			if(in_array(strtolower(func_get_arg($i)), $tabMask))
				return true;
		}
		return false;
	}

	/**
	 * @return bool true si la foncitonnalité export excel est activée, false sinon
	 */
	public function isExcelEnabled() {
		return $this->_enableExcel;
	}

	/**
	 * @return bool true si la foncitonnalité recherche par colonne est activée, false sinon
	 */
	public function isSearchEnabled() {
		return $this->_enableSearch;
	}

	/**
	 * @return bool true si la foncitonnalité tri par colonne est activée, false sinon
	 */
	public function isOrderByEnabled() {
		return $this->_enableOrderBy;
	}

	/**
	 * @return bool true s'il n'y a pas plusieurs ListManager instanciés sur la meme page
	 */
	public static function isUnique() {
		return count(self::$idList) <= 1;
	}
	
	/**
	 * Retourne un tableau contenant la largeur idéeale en nombre de caractere pour chaque colonne du tableau en entrée.
	 * @param array $data le tableau des données (2 dimensions, sinon ça marche pas)
	 * @param int $min la valeur minimale de la largeur des colonnes
	 * @param int $max la valeur maximale de la largeur
	 * @return array|bool tableau contenant pour chaque colonne la largeur à appliquer ou false si params invalides.
	 */
	public function getIdealColumnsWidth(array $data, $min, $max){
		//Vérification des paramètres
		if(!isset($data[0]) || !is_array($data[0]) || intval($min) != $min || intval($max) != $max || $min > $max)
			return false;

		$ret = [];
		$keys = array_keys($data[0]);
		foreach ($data as $row) {
			$i = 0;
			foreach ($row as $key => $value) {
				if(strlen($value) < 100)
					$width = max($min, min(strlen($value), $max));
				else 
					$width = $this->_longColWidth;
				if(!isset($ret[$keys[$i]]) || $ret[$keys[$i]] < $width)
					$ret[$keys[$i]] = $width;
				$i++;
			}
		}
		return $ret;
	}
	

			/*-****************
			***   PRIVATE   ***
			******************/
	
	/**
	 * Gère les filtres.
	 * Cette méthode interne récupère le filtre développeur, le lie au filtre utilisateur et détècte les différences entre les deux.
	 * Actualise les attributs _filterArray & _issetUserFilter.
	 * @param SQLRequest $sqlRequest la requete SQL à modifier
	 */
	private function manageFilter(SQLRequest $sqlRequest, array $having){
		if($this->_enableSearch){
			if(isset($_GET['lm_tabSelect'.$this->_id]) && is_array($_GET['lm_tabSelect'.$this->_id])){
				// Détection des différences
				$devFilterDiff = false;
				$tousVide = true;
				if(isset($_GET['lm_tabSelect'.$this->_id])){
					foreach ($_GET['lm_tabSelect'.$this->_id] as $col => $filtre) {
						// Vérification que le filtre dev correspond au tabSelect utilisateur
						if(isset($this->_filterArray[$col]) && $this->_filterArray[$col] != $filtre
								|| ! isset($this->_filterArray[$col]) && strlen($filtre))
							$devFilterDiff = true;
							// Vérifit que tous les champs tabSelect utilisateur sont vides
							if(strlen($filtre))
								$tousVide = false;
					}
				}
				$this->_issetUserFilter = $devFilterDiff || ($this->_filterArray === [] && !$tousVide);
				
				// Application du filtre utilisateur
				$this->setFilter($_GET['lm_tabSelect'.$this->_id], null);
			}
					
			// Application du filtre sur la requete SQL
			if(count($this->_filterArray) > 0)
				$sqlRequest->filter($this->_filterArray, $having);
		}
	}
	
	/**
	 * Génère un array PHP contenant les données selectionnées.
	 * @param RequestResponse $reponse l'objet de réponse retourné par Database
	 * @return NULL|array null si erreur, tableau des résultats sinon
	 */
	private function generateArray(RequestResponse $reponse){
		$donnees = [];
		if($reponse->error()) {
			$this->addError($reponse->getErrorMessage(), 'generateArray');
			return null;
		}
		else {
			//Application du mask
			if(count($this->_mask) > 0 || $this->_db->oracle()) {
				while(($ligne = $reponse->nextLine()) != null) {
					$aInserer = array();
					foreach ($ligne as $colonne => $valeur) {
						if(!$this->isMasked($colonne))
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
	}
	
	/**
	 * Génère un objet encodé en JSON à partri d'un objet RequestResponse
	 * @param RequestResponse $reponse la réponse renvoyé par Database::execute()
	 * @return string reponse en JSON
	 */
	private function generateJSON(RequestResponse $reponse){
		$ret = new \stdClass();
		$ret->error = $reponse->error();
		if($ret->error){
			$ret->data = null;
			$ret->errorMessage = $reponse->getErrorMessage();
			$this->addError($ret->errorMessage, 'construct');
		}
		else{
			// Applicaiton du mask
			if(count($this->_mask) > 0 || $this->_db->oracle()) {
				while (($ligne = $reponse->nextLine()) != null) {
					$aInserer = array();
					foreach ($ligne as $colonne => $valeur) {
						if(!$this->isMasked($colonne))
							$aInserer[$colonne] = $valeur;
					}
					$ret->data[] = $aInserer;
				}
			}
			else
				$ret->data = $reponse->dataList();
		}
		return json_encode($ret);
	}
	
	/**
	 * Génère un fichier excel à partir d'une réponse de requete en utilisant la bibliothèque PHPExcel.
	 * Le fichier généré sera sauvegardé dans le dossier excel/, et le chemin complet de ce fichier sera retournée par la méthode
	 * @param RequestResponse $reponse l'objet réponse produit par l'exécution de la requete SQL
	 * @return bool false si erreur 
	 */
	private function generateExcel(RequestResponse $reponse) {
		// Vérification des erreurs
		if($reponse->error()){
			$this->addError('le fichier excel n\'a pas pu être généré', 'generateExcel');
			return false;
		}
		if(!$this->_enableExcel){
			$this->addError('la foncitonnalité d\'export excel est désactivée pour cette liste', 'generateExcel');
			return false;
		}
		
		// Récupération du masque dans les params GET
		$metas = $reponse->getColumnsMeta();
		if(isset($_GET['lm_mask'.$this->_id])){
			$tabMask = [];
			foreach (explode(',', $_GET['lm_mask'.$this->_id]) as $numCol) {
				$tabMask[] = $metas[intval($numCol)]->name;
			}
			$this->setMask($tabMask);
		}

		// Création de l'objet PHPExcel
		$phpExcel = new \PHPExcel();
		$phpExcel->setActiveSheetIndex(0);

		// Ajout des propriétés
		$phpExcel->getProperties()->setCreator("ListManager")
			->setLastModifiedBy("ListManager")
			->setTitle("Liste de données")
			->setSubject("Liste de données")
			->setDescription("Feuille de données généré automatiqument à partir de l'url ".$_SERVER['REQUEST_URI']);
		
		$donnees = [];
		while(($ligne = $reponse->nextLine()) != null)
			$donnees[] = $ligne;

		// Création des titres
		$phpExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(20);
		$i = 0;
		$col = 'A';
		$width = $this->getIdealColumnsWidth($donnees, 8, 30);
		$titres = [];
		foreach ($metas as $meta) {

			// Préparation du titre à insérer
			if($meta->table != null && isset($this->_listTitles["$meta->table.$meta->name"]))
				$titres[] = $this->_listTitles["$meta->table.$meta->name"];
			else if(isset($this->_listTitles[$meta->name]))
				$titres[] = $this->_listTitles[$meta->name];
			else if(isset($this->_listTitles[$meta->alias]))
				$titres[] = $this->_listTitles[$meta->alias];
			else
				$titres[] = (($meta->alias == null)? $meta->name : $meta->alias);

			// On vérifie que la colonne n'est pas masquée
			if(!$this->isMasked($meta->name, $meta->alias)) {

				// Mise en forme & insertion des titres
				$phpExcel->getActiveSheet()->getColumnDimension($col)->setWidth($width[$i]);
				$phpExcel->getActiveSheet()->setCellValue($col.'1', $titres[$i]);
	 			$phpExcel->getActiveSheet()->getStyle($col.'1')->applyFromArray(array(
	 				'font' => array(
	 					'bold' => true,
	 					'size' => 13,
	 				),
	 				'fill' => array(
	 					'type' => PHPExcel_Style_Fill::FILL_SOLID,
	 					'color' => array('rgb' => 'CCCCCC'),
	 				),
	 			));

	 			// Appel au callback
	 			if($this->_excelCallback !== null)
	 				call_user_func_array($this->_excelCallback, [$phpExcel, $meta, $titres[$i], $col, 1]);

	 			// Incrément nom colonne
				if(($lastCol = $col[strlen($col) - 1]) == 'Z')
					$col = substr($col, 0, strlen($col) - 1).'AA';
				else
					$col = substr($col, 0, strlen($col) - 1).(++$lastCol);
			}
			else {
				$this->_mask[] = $titres[$i];
			}
			$i++;
		}

		// Insertion des données
		$i = 2;
		foreach ($donnees as $ligne) {
			// Hauteur de ligne
			$phpExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(25);
			// Remplissage colonne par colonne
			$col = 'A';
			for ($j=0; $j < $reponse->getColumnsCount(); $j++){
				$cellule = $ligne[$j];
				// On vérifie que la colonne n'est pas masquée
				if(!$this->isMasked($titres[$j], $metas[$j]->alias, $metas[$j]->name)) {
					// Insertion de la donnée
					$phpExcel->getActiveSheet()->setCellValue($col.($i), $cellule);

		 			// Incrément nom colonne
					if(($lastCol = $col[strlen($col) - 1]) == 'Z')
						$col = substr($col, 0, strlen($col) - 1).'AA';
					else
						$col = substr($col, 0, strlen($col) - 1).(++$lastCol);
				}
			}
			$i++;
		}

		// Appel au callback
		if($this->_excelCallback !== null)
			call_user_func_array($this->_excelCallback, [$phpExcel, $donnees, $metas, $titres]);
		
		// Ecriture du fichier
		try {
			$writer = \PHPExcel_IOFactory::createWriter($phpExcel, 'Excel2007');
			$chemin = LM_XLS.'Liste LM-'.uniqid().'.xlsx';
			$writer->save($chemin);
		}
		catch (\Exception $e) {
			$this->addError('erreur création excel : '.$e->getMessage(), 'generateExcel');
			return false;
		}
		// Redirection vers le fichier
		return $chemin;
	}
}

?>