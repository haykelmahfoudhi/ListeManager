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
 * ListManager : construire, manipuler des listes de donn??es ?? partir d'une requ??te SQL
 *
 * C'est l'objet central du projet. Il joue le r??le d'interface entre le developpeur, les bases de donn??es et les listes.
 * ListManager poss??de un comportement de base :
 * * Connection ?? une base de donn??es, ou utilisation d'une base de donn??es particuli??re de l'applicaiton en sp??cifiant son ??tiquette (cf. la doc de l'objet Database)
 * * Utilisation d'une requ??te SQL de base permettant la s??lection
 * * Utilisation des donn??es situ??es dans les variables GET de l'url pour modifier la requete SQL de base, ?? savoir
 *   * 'lm_tabSelect' : permet de filtrer les donn??es par colonnes (se rajoute ?? la clause WHERE ou HAVING de la requete)
 *   * 'lm_orderBy' : permet de trier els donn??es par ordre croissant / d??croissant selon une colonne (ajoute le num??ro de la colonne ?? la clause ORDER BY)
 *   * 'lm_excel' : lance le t??l??chargemennt du fichier excel g??n??r?? par ListManager
 *   * 'lm_page' : correspond ?? la page de r??sultat affich??e
 * * L'ex??cution de la requ??te SQL
 * * La mise en forme des donn??es dans une liste HTML dans un template correpsondant ?? la classe ListTemplate
 *
 * Ce comportement de base et adaptable et modifibale gr??ce aux nombreuses m??thodes de la classe. Vous pouvez entre autre choisir
 * de retourner les donn??es sous forme de array PHP, d'objet, de fichier excel... ou bien modifier le comportement du template HTML.
 * Il est ??galement ?? noter que les setters de la classe retourne la r??f??rence de l'objet ($this) ($this) en cas de succ??s, ce qui vous permet d'appler ?? la suite un ensemble de setter dans la m??me instruction.
 *
 * @author RookieRed
 *
 */
class ListManager {

	// Cette classe peut g??n??rer et afficher des messages d'erreur
	use T_ErrorGenerator;

			/*-******************
			***   ATTRIBUTS   ***
			********************/
	/**
	 * @var string _$_id correspond ?? l'id du ListManager, cet attribu est utile si vous utilisez plusieurs listes sur la meme page
	 */
	private $_id;
	/**
	 * @var ResponseType $_responseType correspond au format de donn??es retourn?? par ListManager
	 * Valeur par d??faut : TEMPLATE.
	 */
	private $_responseType;
	/**
	 * @var ListTemplate $_template objet template utilis?? par ListManager pour g??n??rer les listes HTML
	 */
	private $_template;
	/**
	 * @var Database $_db objet Databse qui sera utilis?? pour l'??xecution des requ??tes SQL
	 */
	private $_db;
	/**
	 * @var boolean $_enableSearch specifie si la fonction recherche est diponible ou non. N'a d'effets que si vous construisez construct avec le template
	 */
	private $_enableSearch;
	/**
	 * @var boolean $_issetUserFilter indique au template si l'utilisateur ?? modifier les champs de recherche ou non.
	 */
	private $_issetUserFilter;
	/**
	 * @var boolean $_enableOrderBy sp??cifie si ListManger autorise le trie par colonne en modifiant la clause ORDER BY des requetes. N'a d'effet que si vous utilisez la m??thode construct
	 */
	private $_enableOrderBy;
	/**
	 * @var bool $_enableExcel d??finit si ListTemplate propose la fonctionnalit?? d'export Excel
	 */
	private $_enableExcel;
	/**
	 * @var callable $_excelCallback le callback qui sera appel?? lors de la g??n??ration d'un doncument excel
	 */
	private $_excelCallback;
	/**
	 * @var array $_mask correspond aux titre des colonnes ?? ne pas retourner lors de la selection de donn??es
	 */
	private $_mask;
	/**
	 * @var array $_listTitles tableau associatif pour l'affichage des titres des colonnes. Ce tableau ?? pour format [titre_colonne] => [titre_a_afficher]
	 */
	private $_listTitles;
	/**
	 * @var array $_filterArray filtre pos?? par le d??veloppeur qui sera applqiu?? par d??faut ?? la construction de la liste.
	 */
	private $_filterArray;
	/**
	 * @var array $_orderBy
	 */
	private $_orderBy;
	/**
	 * @var int $_longColWidth largeur des colonnes exc??dant 100 caract??res.
	 */
	private $_longColWidth;

	/**
	 * @var array $idList contient la liste de tous les ID des objets ListManager instanci??s
	 */
	private static $idList = [];

			/*-*******************************************
			***  CONSTANTES : OPTIONS DU CONSTRUCTEUR  ***
			*********************************************/
	/**
	 * @var const NO_SEARCH ?? utiliser pour d??sactiver l'utilisation de la recherche par colonne
	 */
	const NO_SEARCH = 1;
	/**
	 * @var const NO_EXCEL ?? utiliser dans le constructeur pour d??sactiver l'export de la liste en Excel
	 */
	const NO_EXCEL = 2;
	/**
	 * @var const NO_JS_MASK ?? utiliser dans le constructeur pour d??sactiver l'utilisation du masquage de colonne en JS
	 */
	const NO_JS_MASK = 4;
	/**
	 * @var const NO_ORDER_BY ?? utiliser dans le constructeur pour d??sactiver le tri des don??nes par colonnes
	 */
	const NO_ORDER_BY = 8;
	/**
	 * @var const NO_CSS ?? utiliser dans le constructeur pour d??sactiver l'utilisation du CSS par d??afut
	 * Implique l'option UNFIXED_TITLES
	 */
	const NO_CSS = 16;
	/**
	 * @var const NO_PAGING ?? utiliser dans le constructeur pour d??sactiver la pagination et la navigation entre les pages de r??sutlats.
	 */
	const NO_PAGING = 32;
	/**
	 * @var const NO_VERBOSE ?? utiliser dans le constructeur pour d??sactiver le mode verbeux
	 */
	const NO_VERBOSE = 64;
	/**
	 * @var const UNFIXED_TITLES ?? utiliser dans le constructeur pour empecher les titres de rester fix??s lorsque l'utilisateur scroll.
	 */
	const UNFIXED_TITLES = 128;
	/**
	 * @var const UNFIXED_TITLES ?? utiliser dans le constructeur pour empecher la fixation de la pagination en bas de l'??cran.
	 */
	const UNFIXED_PAGING = 256;
	/**
	 * @var const NO_RESULTS ?? utiliser dans le constructeur pour masquer la ligne contenant le nombre de r??sultats affich??s et s??lectionn??s.
	 */
	const NO_RESULTS = 512;
	/**
	 * @var const NO_HELP_LINK ?? utiliser dans le constructeur pour masquer le bouton-lien vers la page d'aide.
	 */
	const NO_HELP_LINK = 1024;
	/**
	 * @var const DISPLAY_SEARCH ?? utiliser dans le constructeur pour masquer le bouton-lien vers la page d'aide.
	 */
	const DISPLAY_SEARCH = 2048;

	/**
	 * @var array $optionsArray tableau associatif entre chaque option du constructeur et la m??thode permettant de desactiver la fonctionnalit?? correspondate
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
	 * Construit un nouvel objet ListManager et d??finit son comportement de base.
	 * Tous les param??tres de ce constructeurs sont facultatifs, et permettent dans l'ordre de d??finir l'identifiant de ListManager, la base de donn??es ?? utilier pour les requ??tes SQL, et un tableau
	 * d'options pour d??sactiver certaines fonctionnalit??s de base sans passer par les setters de la classe.
	 * @param string $id l'identifiant de l'objet, ?? utiliser si vous souhaitez utiliser plusieurs listes sur la m??me page. Dans le cas contraire, laissez ?? null.
	 * @param Database|string $db l'insatnce de Database ou l'etiquette de la base de donn??es ?? utiliser si n??cessaire. Laissez null si vous n'avez qu'une seule base de donn??es.
	 * @param array $options tableau de constantes permettant de d??sactiver certaines fonctionnalit??s de ListManager, c.f. la documention des constantes de la classe.
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

		// Gestion des options : d??sactivation de fonctionnalit??s
		$i = 0;
		foreach ($options as $option) {
			$i++;
			if(isset(self::$optionsArray[$option]))
				call_user_func_array([$this, self::$optionsArray[$option]], [$option==self::DISPLAY_SEARCH]);

			// Option non reconnue
			else {
				$this->addError('l\'option n??'.$i.' (valeur = "'.$option.'") n\'est pas reconnue' ,'__construct');
			}
		}
	}


			/*-*****************
			***   METHODES   ***
			*******************/

	/**
	 * Execute la requete SQL dont la base est passee en parametres.
	 * La m??thode prend en param??tre la base de votre requ??te SQL, et la compl??te en fonction des param??tres d'url GET, en rajoutant soit des blocs ?? la clause WHERE ou HAVING soit ?? la clause ORDER BY si toutesfois ces deux foncitonnalit??es sont activ??es.
 	 * Le format des resultats obtenus par la requete d??pend du ResponseType sp??cifi??.
	 * @param mixed $baseSQL la requete a executer. Peut etre de type string ou SQLRequest.
	 * @param array $params (facultatif) ?? utiliser si vous saouhaitez passer par les m??thodes prepare puis ex??cute pour ex??cuter votre requete SQL
	 * @param array $having tableau contenant le nom des colonnes selectionn??es dont le filtre doit se trouver dans la clause HAVING.
	 * @return mixed
	 * * l'objet de reponse dependant de $responseType, parametrable via la methode *setResponseType()*.
	 * * false en cas d'erreur, par exemple si ListManager ne parvient pas ?? utiliser la base de donn??es.
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
				//exec('find xls/ -name "*.xlsx" -mtime +1 -delete');
					//****************************** HMAHFOUDHI le 24/06/2021
				$path = LM_XLS;
					if ($repertoir = opendir($path)) {

					    while (false !== ($file = readdir($repertoir))) {
					        $filelastmodified = filemtime($path.$file);
					        //24 heurs par jour * 3600 seconds par heur
					        if((time() - $filelastmodified) >  20)
					        {
										if(is_file($path.$file)){
											unlink($path.$file);
										}

					        }
					    }
						    closedir($repertoir);
						}
				//******************************
				$chemin = $this->generateExcel($reponse);
				if($chemin !== false){
					header('Location:'.$chemin);
				}
				// Si erreur de redirection : on propose le lien de t??l??chargement
				return $this->_template->construct(new RequestResponse(null, true,
						'Pour t??l??charger le fichier <a href="'.$chemin.'">cliquez ici</a>'));

			case ResponseType::JSON:
				return $this->generateJSON($reponse);


			case ResponseType::TEMPLATE:
				// Selection de la page
				if($this->_template->issetPaging() && isset($_GET['lm_page'.$this->_id]) && $_GET['lm_page'.$this->_id] > 0)
					$this->_template->setCurrentPage($_GET['lm_page'.$this->_id]);
				return $this->_template->construct($reponse);
		}

		$this->addError('type de r??ponse non reconnu : '.$this->_responseType, 'construct');
		return false;
	}

			/*-****************
			***   SETTERS   ***
			******************/

	/**
	 * Definit le format de la reponse de la methode *construct()*
	 * A la suite de l'execution d'une requete SQL ListManager peut retourner une liste de donn??es sous 5 formes diff??rentes :
	 * * TEMPLATE (par defaut) pour obtenir un string representant la liste HTML contenant toutes les donnees
	 * * ARRAY pour obtenir les resultats dans un array PHP (equivalent a PDOStaement::fetchAll())
	 * * JSON pour obtenir les donnees dans un objet encode en JSON
	 * * EXCEL pour obtenir les resultats dans une feuille de calcul Excel
	 * * OBJET pour obtenir un objet \stdClass
	 * Par d??faut le type de r??ponse est TEMPLATE. Vous pouvez le changer en indiquant le param??tre suivant
	 * @param ResponseType $responseType le nouveau type de r??ponse
	 * @return mixed false si le param??tre n'est pas un type de reponse correct sinon retourne la r??f??rence de l'objet ($this)
	 */
	public function setResponseType($responseType){
		if(!in_array($responseType, range(1,5)))
			return false;

		$this->_responseType = $responseType;
		return $this;
	}

	/**
	 * Instancie une nouvelle connexion a une base de donnees.
	 * Cette m??thode utilise la m??thode *Database::instantiate()* et donc enregistre l'instance creee dans la classe Database. Sp??cifiez une etiquette si vous en utilisez plusieurs
	 * En cas d'erreur de connection le message correspondant est enregistr?? et affich?? si la verbosit?? est activ??e.
	 * @param string $dsn le DSN (voir le manuel PHP concernant PDO)
	 * @param string $login le nom d'utilisateur pour la connexion
	 * @param string $mdp son mot de passe
	 * @param string $label l'etiquette de la base de donn??es. Laisser ?? null si vous n'en utiliser qu'une seule
	 * @return mixed false si ListManager ne parvient pas ?? connecter la base de donn??es, sinon retourne la r??f??rence de l'objet ($this)
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
	 * Affiche un message d'erreur si la base de donn??es n'a pas pu ??tre r??cup??r??e.
	 * @param string|Database $dataBase la base de donn??es ?? utiliser. Peut ??tre de type string ou Database :
	 * * Si string : represente l'etiquette de la base de donnees a utiliser.
	 * * Si null ou non sp??cifi?? : recupere la base de donnee principale de la classe Database.
	 * @return mixed la r??f??rence de l'objet ($this) si l'operation est un succ??s, false sinon + enregistrement d'un message d'erreur et affichage si verbose
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
			$this->addError('aucune base de donnees trouv??e, avez-vous instanci?? une connexion ?', 'setDatabase');
			return false;
		}
		return $this;
	}

	/**
	 * D??finit l'id HTML de la balise table correspondant ?? la liste
	 * @param string $id le nouvel id du tableau. Si null aucun ID ne sera affich??.
	 * @return mixed false en cas d'erreur (id d??ja utilis??) ou sinon la r??f??rence de l'objet ($this)
	 */
	public function setId($id) {
		if(in_array($id, self::$idList)){
			$this->addError("il existe d??j?? une liste avec liste avec l'id '$id', veuillez en choisir un nouveau", 'setId');
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
	 * D??finit le nouveau masque ?? appliquer.
	 * Le masque est un tableau contenant le nom des colonnes que vous ne souhaitez pas afficher dans la liste HTML
	 * @param array $mask le nouveau masque ?? appliquer. Si null : aucun masque ne sera applqiu??
	 * @return mixed false si le param??tre en entr?? n'est ni null, ni un array, sinon retourne la r??f??rence de l'objet ($this)
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
	 * @param string $class1 le nom de la classe des lignes impaires. Mettre ?? null si vous ne souhaitez pas de classe
	 * @param string $class2 le nom de la classe des lignes paires. Mettre a null si vous ne souhaitez pas de classe
	 * @return ListManager la r??f??rence de l'objet ($this)
	 */
	public function setRowsClasses($class1, $class2){
		$this->_template->setRowsClasses($class1, $class2);
		return $this;
	}

	/**
	 * Permet de changer les titres des colonnes de la liste
	 * Le tableau ?? passer en param??tre est un tableau associatif o?? la cl?? correspond au nom de la colonne tel qu'il est restitu?? lors de la selection des donn??es, associ?? au titre que vous souhaitez afficher
	 * @param array le tableau des nouveaux titres
	 * @return ListManager la r??f??rence de l'objet ($this)
	 */
	public function setListTitles(array $liste) {
		foreach ($liste as $col => $val)
			$this->_listTitles[strtolower($col)] = $val;

		return $this;
	}

	/**
	 * Definit si l'option recherche doit etre activee ou non. Si cette valeur est passee a false il ne sera plus possible pour l'utilisateur de filtrer les donn??es de la liste
	 * @param boolean $valeur la nouvelle valeur ?? attribuer.
	 * @return mixed false si la valeur sp??cifi?? n'est pas un boolean, sinon retourne la r??f??rence de l'objet ($this)
	 */
	public function enableSearch($valeur){
		if(!is_bool($valeur))
			return false;

		$this->_enableSearch = $valeur;
		return $this;
	}

	/**
	 * D??finit si l'option de tri par colonne est acitv??e ou non pour cette liste. Si d??sactiv??e, l'utilisateur ne pourra plus cliquer sur les colonnes pour trier
	 * @param boolean $valeur la nouvelle valeur ?? attribuer.
	 * @return mixed false si la valeur sp??cifi?? n'est pas un boolean, sinon retourne la r??f??rence de l'objet ($this)
	 */
	public function enableOrderBy($valeur) {
		if(!is_bool($valeur))
			return false;

		$this->_enableOrderBy = $valeur;
		return $this;
	}

	/**
	 * D??finir un callback ?? appeler dans chaque cellule de la liste.
	 * Definit le callback (la fonction) qui sera executee pour chaque valeur lors de l'affichage des donnees dans les cellules du tableau. Cette fonction doit etre definie comme il suit :
	 * * 4 parametres d'entree :
	 *    1. cellule  : la valeur de l'element en cours
	 *    2. colonne  : le nom de la colonne en cours
	 *    3. numLigne : le numero de la ligne en cours
	 *    4. ligne    : un array associatif contenant toutes les donn??es de la ligne en cours
	 *    5. numCol   : le numero de la colonne en cours
	 * * valeur de retour de type string (ou du moins un type qui peut ??tre transform?? en string). Si vous voulez laissez la case vide, retournez false
	 * @param callable $fonction le nom du callback a utiliser, null si aucun. Valeur par defaut : null
	 * @param bool $replaceTagTD d??finit si le callback d??finit r????crit les balises td ou non. Par d??faut ce param??tre vaut false, ce qui signifit que ListTemplate ??crit automatiquement des balises td de la liste.
	 * @return mixed false si vous entrez un non bool??en comme 2e argument de cette m??thode, sinon retourne la r??f??rence de l'objet ($this)
	 */
	public function setCellCallback(callable $fonction, $replaceTagTD=false){
		if($this->_template->setCellCallback($fonction, $replaceTagTD) === false)
			return false;
		return $this;
	}

	/**
	 * D??finir un callback ?? appeler ?? la cr??ation de chaque ligne de la liste.
	 * Ce callback sera appel?? par le template ?? la cr??ation d'une nouvelle balise tr (balise ouvrante) et doit avoir pour caract??ristiques :
	 *  * 2 param??tres d'entr??e :
	 *    1. numero  : correspond au num??ro de la ligne en cours
	 *    2. donnees : array php contenant l'ensemble des donn??es selectionn??es dans la base de donn??es qui seront affich??es dans cette ligne du tableau
	 *  * valeur de retour de type string (ou du moins un type qui peut ??tre transform?? en string). Si vous voulez laissez la case vide, retournez false
	 * @param callable $fonction le nom du callback a utiliser, null si aucun.
	 */
	public function setRowCallback(callable $fonction){
		$this->_template->setRowCallback($fonction);
		return $this;
	}

	/**
	 * D??finir un callback pour rajouter manuellement des colonnes dans votre liste.
	 * Ce callback sera appel?? par le template ?? la fin de la cr??ation des titres ET a la fnc de la cr??ation de chaque
	 * ligne de la liste. La fonction doit correspondre au format suivant
	 *  * 3 param??tres d'entr??e :
	 *    * 1. numLigne  : int correspond au num??ro de la ligne en cours
	 *    * 2. donnees   : array contenant l'ensemble des donn??es selectionn??es dans la base de donn??es qui seront affich??es dans cette ligne du tableau. Vaut null pour les titres
	 *  * valeur de retour de type string (ou du moins un type qui peut ??tre transform?? en string).
	 * @param callable $fonction le nom du callback a utiliser, null si aucun. Valeur par defaut : null
	 * @param array $titres contient les titres des colonnes ?? ajouter
	 */
	public function setColumnCallback(callable $fonction, array $titres){
		$this->_template->setColumnCallback($fonction, $titres);
		return $this;
	}

	/**
	 * D??finir un callback pour modifier les cellules et leur donn??es lors de la g??n??ration du fichier excel.
	 * Ce callback sera appel?? par ListManager lors de la g??n??ration d'un fichier excel, et vous permettra via l'objet PHPExcel de modifier
	 * la cellule et le document en cours. Le format du callback est :
	 *  *  param??tres d'entr??e :
	 *    1. phpExcel : l'objet PHPExcel utilis?? pour g??n??rer le document
	 *    2. donnees  : le tableau contenant les donn??es ins??r??es dans le doc
	 *    3. metas    : array d'objets des m??tas donn??es des colonnes ( @see RequestResponse::getColumnsMeta() )
	 *    4. titres   : array des titres des colonnes telles qu'??crites dans le fichier
	 *  * pas de valeur de retour, tout se fait via l'objet PHPExcel
	 * @param callable $fonction le nom du callback a utiliser, null si aucun.
	 * @return ListManager la r??f??rence de l'objet ($this)
	 */
	public function setExcelCallback(callable $fonction){
		$this->_excelCallback = $fonction;
		return $this;
	}

	/**
	 * Definit le nombre de resultats a afficher sur une page. Valeur par defaut = 50
	 * @param int $valeur le nombre de lignes a afficher par pages
     * @return boolean false si la valeur entree est incorrecte, sinon retourne la r??f??rence de l'objet ($this)
	 */
	public function setNbResultsPerPage($valeur){
		if($this->_template->setNbResultsPerPage($valeur) === false)
			return false;

		return $this;
	}

	/**
	 * D??finit le nombre max de liens vers les pages de la liste propos??s par la pagination du template.
	 * La valeur par d??faut est 15 : c'est ?? dire que par exemple le template propose les liens des pages 1 ?? 15 si l'utilisateur est sur la 1re page.
	 * @param int $valeur le nombre de liens ?? proposer
	 * @return mixed false si la valeur sp??cifi?? et un entier incorrect, sinon retourne la r??f??rence de l'objet ($this)
	 */
	public function setPagingLinksNb($valeur){
		if($this->_template->setPagingLinksNb($valeur) === false)
			return false;

		return $this;
	}

	/**
	 * Applique un filtre par d??faut sur les donn??es de la liste.
	 * Via cette m??thode vous pourrez appliquer un filtre par d??faut dans les colonnes de votre choix, que l'utilisateur pourra modifier
	 * ou supprimer lors de la navigation
	 * @param array $filter le tableau contenant toutes les conditions ?? rajouter. Ce tableau aura la forme suivante :
	 * ['nomColonne1' => 'condition1', 'nomColonne2' => 'cond2A,cond2B,cond3C'] ... Il est possible de combiner les conditions en les s??parant par une virgule. Ainsi la condition 'prenom' => 'Roger,Patrick' recherchera tous ceux ayant le pr??nom Roger ou Patrick
	 * Une condition prend la forme suivante : [OPERATEUR][VALEUR]
	 * Les op??rateurs possibles sont :
	 * * (pas d'op??rateur) : ??galit?? stricte avec la valeur entr??e
	* * < > <= >= = : inf??rieur, sup??rieur, sup??rieur ou ??gal, inf??rieur ou ??gal, ??gal
	 * * / : op??rateur 'diff??rent de'. La condition '!' est traduite par diff??rent de ''
	 * * - : correspond ?? NULL. Doit ??tre utilis?? seul, !\n est traduit par NOT NULL
	 * * << : op??rateur 'BETWEEN' pour les dates
	 * @param bool $displaySearch passez ce param??tre ?? false si vous souhaitez ne pas afficher les champs de recherche, sinon laissez le ?? true
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
	 * D??finit si ListTemplate affiche ou non les champs de recherche au chargmeent de la page.
	 * @param bool $valeur true pour activer, false pour desactiver
	 * @return bool false si l'argument n'est pas un booleen
	 */
	public function displaySearchInputs($valeur) {
		if($this->_template->displaySearchInputs($valeur) === false)
			return false;
		return $this;
	}

	/**
	 * D??finit la taille maximale des champs de saisie pour la recherche.
	 * @param int $valeur la nouvelle taille maximale des champs de saisie pour al recherche
	 * @param bool $invariable passez ce param??tre a true si vous souhaitez que la m??me taille de champs soit appliqu??es ?? tous
	 * @return bool false si l'argument est incorrect (pas un int, inf??rieur ?? 0)
	 */
	public function setMaxSizeInputs($valeur, $invariable=false) {
		if($this->_template->setMaxSizeInputs($valeur, $invariable) === false)
			return false;

		return $this;
	}

	/**
	 * D??finit si ListTemplate doit proposer l'export de donn??es en format excel ?? l'utilisateur. Valeur apr d??faut : true
	 * @param bool $valeur : la nouvelle valeur ?? appliquer
	 * @return mixed false si la valeur sp??cifi?? n'est pas un boolean, sinon retourne la r??f??rence de l'objet ($this)
	 */
	public function enableExcel($valeur){
		if(!is_bool($valeur))
			return false;

		$this->_enableExcel = $valeur;
		return $this;
	}

	/**
	 * D??finit si le template propose la fonctionnalit?? de masquage des colonnes cot?? client en JS
	 * @param bool $valeur la nouvelle valeur ?? appliquer
	 * @return mixed false si la valeur sp??cifi?? n'est pas un boolean, sinon retourne la r??f??rence de l'objet ($this)
	 */
	public function enableJSMask($valeur){
		if($this->_template->enableJSMask($valeur) === false)
			return false;

		return $this;
	}

	/**
	 * D??finit si ListTemplate affiche ou non le nombre de r??sultats total retourn??e par la requete
	 * @param bool $valeur true pour activer, false pour desactiver
	 * @return mixed false si la valeur sp??cifi?? n'est pas un boolean, sinon retourne la r??f??rence de l'objet ($this)
	 */
	public function displayResultsInfos($valeur) {
		if($this->_template->displayResultsInfos($valeur) === false)
			return false;

		return $this;
	}

	/**
	 * D??finit si le template doit charger le fichier CSS par d??faut et appliquer le style du template par d??aut
	 * Si vous souhaitez personnaliser le style de votre liste vous devriez desactiver cette option et inclure votre propre fichier CSS
	 * @param bool $valeur false pour desactiver, true pour activer
	 * @return mixed false si la valeur sp??cifi?? n'est pas un boolean, sinon retourne la r??f??rence de l'objet ($this)
	 */
	public function applyDefaultCSS($valeur) {
		if($this->_template->applyDefaultCSS($valeur) === false)
			return false;

		return $this;
	}

	/**
	 * Permet d'ajouter une rubrique d'aide ou une legende ?? la liste actuelle
	 * @param string $link le lien url vers la page d'aide. Si null alors le lien sera desactiv??
	 * @return mixed false si la valeur sp??cifi?? n'est pas un boolean, sinon retourne la r??f??rence de l'objet ($this)
	 */
	public function setHelpLink($link) {
		if($this->_template->setHelpLink($link) === false)
			return false;

		return $this;
	}

	/**
	 * D??finit si les titres de votre liste restent fix??s en haut de l'??cran lorsque l'utilisateur scroll sur la page.
	 * Il ne vous ai pas possible de fixer ni les titres ni la pagination si vous utilisez plusieurs instances de ListManager, par cons??quent
	 * cette m??thode vous retournera false si vous tentez quand m??me d'activer cette option.
	 * @param bool $valeur true pour activer false pour d??sactiver cette option
	 * @return mixed false si la valeur sp??cifi?? n'est pas un boolean, sinon retourne la r??f??rence de l'objet ($this)
	 */
	public function fixTitles($valeur) {
		if($this->_template->fixTitles($valeur) === false)
			return false;

		return $this;
	}

	/**
	 * D??finit si les liens de page de votre liste restent fix??s en bas de l'??cran.
	 * Il ne vous ai pas possible de fixer ni les titres ni la pagination si vous utilisez plusieurs instances de ListManager, par cons??quent
	 * cette m??thode vous retournera false si vous tentez quand m??me d'activer cette option.
	 * @param bool $valeur true pour activer false pour d??sactiver cette option
	 * @return mixed false si la valeur sp??cifi?? n'est pas un boolean, sinon retourne la r??f??rence de l'objet ($this)
	 */
	public function fixPaging($valeur) {
		if($this->_template->fixPaging($valeur) === false)
			return false;

		return $this;
	}

	/**
	 * Ajoute des boutons dans la division boutons ?? gauche des listes
	 * @param array $buttons contient le code HTML des boutons ?? ajouter
	 * @return ListManager la r??f??rence de cet objet
	 */
	public function addButtons(array $buttons) {
		$this->_template->addButtons($buttons);
		return $this;
	}

	/**
	 * D??finit la largeur pour les colonnes exc??dant 100 caract??res.
	 * @param int $value nouvelle valeur
	 * @return bool|ListManager false si parametre invalide (<= 0 ou non entier), $this si op??ration ok (method chaining)
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
	 * @return string l'id de l'objet ListManager tel qu'il sera affich?? dans le template (avec un underscore avant)
	 */
	public function getId() {
		return $this->_id;
	}

	/**
	 * @return array le tableau associatif des titres des colonnes ?? remplacer par des nouveaux titres
	 */
	public function getListTitles() {
		return $this->_listTitles;
	}

	/**
	 * @return array le tableau associatif du filtre pos?? par le d??veloppeur.
	 */
	public function getFilter() {
		return $this->_filterArray;
	}

	/**
	 * @return boolean true si l'utilisateur a modifi?? ou effectu?? une recherche.
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
	 * D??termine si au moins un des noms de colonne se trouve dans le tableau du masque.
	 * @var string $column ... le nom ou les alias de la colonne
	 * @return bool true si la colonne est ?? masquer, false sinon
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
	 * @return bool true si la foncitonnalit?? export excel est activ??e, false sinon
	 */
	public function isExcelEnabled() {
		return $this->_enableExcel;
	}

	/**
	 * @return bool true si la foncitonnalit?? recherche par colonne est activ??e, false sinon
	 */
	public function isSearchEnabled() {
		return $this->_enableSearch;
	}

	/**
	 * @return bool true si la foncitonnalit?? tri par colonne est activ??e, false sinon
	 */
	public function isOrderByEnabled() {
		return $this->_enableOrderBy;
	}

	/**
	 * @return bool true s'il n'y a pas plusieurs ListManager instanci??s sur la meme page
	 */
	public static function isUnique() {
		return count(self::$idList) <= 1;
	}

	/**
	 * Retourne un tableau contenant la largeur id??eale en nombre de caractere pour chaque colonne du tableau en entr??e.
	 * @param array $data le tableau des donn??es (2 dimensions, sinon ??a marche pas)
	 * @param int $min la valeur minimale de la largeur des colonnes
	 * @param int $max la valeur maximale de la largeur
	 * @return array|bool tableau contenant pour chaque colonne la largeur ?? appliquer ou false si params invalides.
	 */
	public function getIdealColumnsWidth(array $data, $min, $max){
		//V??rification des param??tres
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
	 * G??re les filtres.
	 * Cette m??thode interne r??cup??re le filtre d??veloppeur, le lie au filtre utilisateur et d??t??cte les diff??rences entre les deux.
	 * Actualise les attributs _filterArray & _issetUserFilter.
	 * @param SQLRequest $sqlRequest la requete SQL ?? modifier
	 */
	private function manageFilter(SQLRequest $sqlRequest, array $having){
		if($this->_enableSearch){
			if(isset($_GET['lm_tabSelect'.$this->_id]) && is_array($_GET['lm_tabSelect'.$this->_id])){
				// D??tection des diff??rences
				$devFilterDiff = false;
				$tousVide = true;
				if(isset($_GET['lm_tabSelect'.$this->_id])){
					foreach ($_GET['lm_tabSelect'.$this->_id] as $col => $filtre) {
						// V??rification que le filtre dev correspond au tabSelect utilisateur
						if(isset($this->_filterArray[$col]) && $this->_filterArray[$col] != $filtre
								|| ! isset($this->_filterArray[$col]) && strlen($filtre))
							$devFilterDiff = true;
							// V??rifit que tous les champs tabSelect utilisateur sont vides
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
	 * G??n??re un array PHP contenant les donn??es selectionn??es.
	 * @param RequestResponse $reponse l'objet de r??ponse retourn?? par Database
	 * @return NULL|array null si erreur, tableau des r??sultats sinon
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
	 * G??n??re un objet encod?? en JSON ?? partri d'un objet RequestResponse
	 * @param RequestResponse $reponse la r??ponse renvoy?? par Database::execute()
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
	 * G??n??re un fichier excel ?? partir d'une r??ponse de requete en utilisant la biblioth??que PHPExcel.
	 * Le fichier g??n??r?? sera sauvegard?? dans le dossier excel/, et le chemin complet de ce fichier sera retourn??e par la m??thode
	 * @param RequestResponse $reponse l'objet r??ponse produit par l'ex??cution de la requete SQL
	 * @return bool false si erreur
	 */
	private function generateExcel(RequestResponse $reponse) {
		// V??rification des erreurs
		if($reponse->error()){
			$this->addError('le fichier excel n\'a pas pu ??tre g??n??r??', 'generateExcel');
			return false;
		}
		if(!$this->_enableExcel){
			$this->addError('la foncitonnalit?? d\'export excel est d??sactiv??e pour cette liste', 'generateExcel');
			return false;
		}

		// R??cup??ration du masque dans les params GET
		$metas = $reponse->getColumnsMeta();
		if(isset($_GET['lm_mask'.$this->_id])){
			$tabMask = [];
			foreach (explode(',', $_GET['lm_mask'.$this->_id]) as $numCol) {
				$tabMask[] = $metas[intval($numCol)]->name;
			}
			$this->setMask($tabMask);
		}

		// Cr??ation de l'objet PHPExcel
		$phpExcel = new \PHPExcel();
		$phpExcel->setActiveSheetIndex(0);

		// Ajout des propri??t??s
		$phpExcel->getProperties()->setCreator("ListManager")
			->setLastModifiedBy("ListManager")
			->setTitle("Liste de donn??es")
			->setSubject("Liste de donn??es")
			->setDescription("Feuille de donn??es g??n??r?? automatiqument ?? partir de l'url ".$_SERVER['REQUEST_URI']);

		$donnees = [];
		while(($ligne = $reponse->nextLine()) != null)
			$donnees[] = $ligne;

		// Cr??ation des titres
		$phpExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(20);
		$i = 0;
		$col = 'A';
		$width = $this->getIdealColumnsWidth($donnees, 8, 30);
		$titres = [];
		foreach ($metas as $meta) {

			// Pr??paration du titre ?? ins??rer
			if($meta->table != null && isset($this->_listTitles["$meta->table.$meta->name"]))
				$titres[] = $this->_listTitles["$meta->table.$meta->name"];
			else if(isset($this->_listTitles[$meta->name]))
				$titres[] = $this->_listTitles[$meta->name];
			else if(isset($this->_listTitles[$meta->alias]))
				$titres[] = $this->_listTitles[$meta->alias];
			else
				$titres[] = (($meta->alias == null)? $meta->name : $meta->alias);

			// On v??rifie que la colonne n'est pas masqu??e
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

	 			// Incr??ment nom colonne
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

		// Insertion des donn??es
		$i = 2;
		foreach ($donnees as $ligne) {
			// Hauteur de ligne
			$phpExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(25);
			// Remplissage colonne par colonne
			$col = 'A';
			for ($j=0; $j < $reponse->getColumnsCount(); $j++){
				$cellule = $ligne[$j];
				// On v??rifie que la colonne n'est pas masqu??e
				if(!$this->isMasked($titres[$j], $metas[$j]->alias, $metas[$j]->name)) {
					// Insertion de la donn??e
					$phpExcel->getActiveSheet()->setCellValue($col.($i), $cellule);

		 			// Incr??ment nom colonne
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
			$this->addError('erreur cr??ation excel : '.$e->getMessage(), 'generateExcel');
			return false;
		}
		// Redirection vers le fichier
		return $chemin;
	}
}

?>
