<?php


/*-**********************************************************************************************
**                                                                                             ** 
**                             88           88                                                 **
**                             88           ""               ,d                                **
**                             88                            88                                **
**                             88           88  ,adPPYba,  MM88MMM                             **
**                             88           88  I8[    ""    88                                **
**                             88           88   `"Y8ba,     88                                **
**                             88           88  aa    ]8I    88,                               **
**                             88888888888  88  `"YbbdP"'    "Y888                             **
**                                                                                             **
**  888888888888                                          88                                   ** 
**       88                                               88                ,d                 ** 
**       88                                               88                88                 ** 
**       88   ,adPPYba,  88,dPYba,,adPYba,   8b,dPPYba,   88  ,adPPYYba,  MM88MMM  ,adPPYba,   ** 
**       88  a8P_____88  88P'   "88"    "8a  88P'    "8a  88  ""     `Y8    88    a8P_____88   ** 
**       88  8PP"""""""  88      88      88  88       d8  88  ,adPPPPP88    88    8PP"""""""   ** 
**       88  "8b,   ,aa  88      88      88  88b,   ,a8"  88  88,    ,88    88,   "8b,   ,aa   ** 
**       88   `"Ybbd8"'  88      88      88  88`YbbdP"'   88  `"8bbdP"Y8    "Y888  `"Ybbd8"'   ** 
**                                           88                                                ** 
**                                           88                                                **
**                                                                                             **
************************************************************************************************/

/**
 * Objet Template : construit la liste HTML avec les donn??es qu'elle contient. Constitue la vue.
 * 
 * Tout comme ListManager, ListTemplate poss??de un comportement de base modifiable gr??ce aux m??thodes de classe. Vous pouvez modifier :
 * * Activer / desactiver / modifier le nom des classes des lignes paires / impaires
 * * Modifier la classe et le message des erreurs 'Liste vide'
 * * Activer / d??sactiveer les champs de saisie pour rechercher
 * * Modifier le nombre de lignes par page
 * * Modifier le nombre de pages ?? afficher dans la pagination
 * * Utiliser des callbacks pour :
 *    * modifier le contenu des cellules
 *    * ajouter des colonnes ?? votre tableau
 *    * modifier les attributs des balises tr du tableau
 * 
 * @author RookieRed
 *
 */
class ListTemplate {
	
	
			/*-******************
			***   ATTRIBUTS   ***
			********************/
	/**
	 * @var ListManager $_lm objet ListManager parent qui r??git ce template
	 */
	private $_lm;
	/**
	 * @var string $_class1 nom de la classe HTML appliqu??e aux lignes imparaires
	 */
	private $_class1;
	/**
	 * @var string $_class2 nom de la classe HTML appliqu??e aux lignes paires
	 */
	private $_class2;
	/**
	 * @var string $_emptyListMessage le message qui sera affiche si la liste ne contient pas de donnees
	 */
	private $_emptyListMessage;
	/**
	 * @var string $_errorClass le nom de la classe HTML des balises p qui contiendront le message d'erreur
	 */
	private $_errorClass;
	/**
	 * @var int $_nbResultsPerPage nombre de resultats affiches par page. Valeur par defaut = 100
	 */
	private $_nbResultsPerPage;
    /**
     * @var int $_currentPage numero de la page de resultats actuelle
     */
	private $_currentPage;
	/**
	 * @var string $_cellCallback nom du callback a appeler lors de l'affichage d'une cellule (balises 'td')
	 */
	private $_cellCallback;
	/**
	 * @var bool $_replaceTagTD d??finit si le callback de cellule r????crit les balises TD des cellules ou non. 
	 * Passez cet attribut ?? true pour ajouter manuellement les balises td avec le callback et pour modifier leurs attribus
	 */
	private $_replaceTagTD;
	/**
	 * @var callable $_rowCallback nom du callback a appeler lors de l'affichage des des lignes (balises 'tr').
	 * Permet de modifer les attributs HTML de la balise
	 */
	private $_rowCallback;
	/**
	 * @var callable $_columnCallback nom du callback qui servira ?? ajouter des colonnes ?? la liste 
	 */
	private $_columnCallback;
	/**
	 * @var array contient les titres des colonnes ?? rajouter via le callback de colonnes.
	 */
	private $_addedColumns;
	/**
	 * @var bool $_enableJSMask d??finit si le template permet ?? l'utilisateur de masquer les colonnes gr??ce
	 * ?? JavaScript avecla petite croix rouge
	 */
	private $_enableJSMask;
	/**
	 * @var int $_pagingLinksNb nombre de liens de page ?? afficher au maximum dans la pagination
	 */
	private $_pagingLinksNb;
	/**
	 * @var string $_helpLink lien vers la page d'aide associ??e ?? cette liste
	 */
	private $_helpLink;
	/**
	 * @var array $_userButtons contient les boutons rajout?? par le d??veloppeur qui seront affich?? dans la div des boutons utilisateur
	 */
	private $_userButtons;
	/**
	 * @var bool $_quest d??finit si ListTemplate affiche ou non les champs de recherche automatiquement au chargement de la page
	 */
	private $_quest;
	/**
	 * @var bool $_displayResultsInfos d??finit si ListTemplate affiche ou non le nombre de r??sultats total retourn??e par la requete
	 */
	private $_displayResultsInfos;
	/**
	 * @var bool $_applyDefaultCSS d??init si le template doit appliquer le style par defaut du fichier base.css ou non
	 */
	private $_applyDefaultCSS;
	/**
	 * @var integer $_maxSizeInputs longueur maximale des champs de saisie pour la recherche par colonne
	 */
	private $_maxSizeInputs;
	/**
	 * @var bool $_constInputsSize definit si les champs de saisie ont tous la meme taille
	 */
	private $_constInputsSize;
	/**
	 * @var bool $_fixedTitles d??finti si les titres de la listes sont fix??s lorsque l'utilisateur scroll
	 */
	private $_fixedTitles;
	/**
	 * @var bool $_fixedTitles d??finti si les titres de la listes sont fix??s lorsque l'utilisateur scroll
	 */
	private $_fixedPaging;
	
	/**
	 * @var string $CLASS1 classe par d??faut des lignes impaires du tableau
	 */
	public static $CLASS1 = 'gris-clair';
	/**
	 * @var string $CLASS2 classe par d??faut des lignes paires du tableau
	 */
	public static $CLASS2 = 'blanc';
	
	
	
			/*-*********************
			***   CONSTRUCTEUR   ***
			***********************/
	
	/**
	 * Construit un objet ListTemplate et lui assigne son comportement par d??faut.
	 * @param ListManager $lm l'objet ListManager appelant qui utilise cet objet template
	 */
	 public function __construct(ListManager $lm){
		$this->_lm = $lm;
		$this->_class1 = self::$CLASS1;
		$this->_class2 = self::$CLASS2;
		$this->_enableJSMask = true;
		$this->_emptyListMessage = "Aucun resultat!";
		$this->_errorClass = 'erreur';
		$this->_currentPage = 1;
		$this->_nbResultsPerPage = 100;
		$this->_pagingLinksNb = 10;
		$this->_cellCallback = null;
		$this->_replaceTagTD = false;
		$this->_rowCallback = null;
		$this->_columnCallback = null;
		$this->_addedColumns = [];
		$this->_helpLink = LM_ROOT.'doc/presentation.html';
		$this->_userButtons = [];
		$this->_quest = false;
		$this->_displayResultsInfos = true;
		$this->_applyDefaultCSS = true;
		$this->_maxSizeInputs = 15;
		$this->_constInputsSize = false;
		$this->_fixedTitles = ListManager::isUnique();
		$this->_fixedPaging = ListManager::isUnique();
	}
	
	
			/*-*****************
			***   METHODES   ***
			*******************/

	/**
	 * Construit une liste HTML ?? partir d'un objet RequestResponse.
	 * Il s'agit de la fonction principale de la classe. L'obje g??n??re un template ?? partir des valeurs de ses attributs 
	 * et contenant le r??sultat de la requete pass??e en param??tre.
	 * Le template se d??coupe en 4 parties :
	 * * *Tout en haut* : les titres des colonnes avec les liens permettant de les trier ou de les masquer.
	 * * *Tout ?? gauche* : les boutons d'options permettant d'activer certaines fonctionnalit??es
	 * * *Au centre* : la liste de donn??es
	 * * *En bas* : la pagination et les liens vers les autres pages de la liste
	 * @param RequestResponse $reponse contenant l'ensemble des resultats de la requete
	 * @return string le code HTML de la liste HTML genere
	 */
	public function construct(RequestResponse $reponse){
		// On teste d'abord s'il y a erreur dans la reponse
		if($reponse->error()){
			$ret = "<div class='boutons-options'><a href='".self::creerUrlGET(null, null, array())."'>Clear</a></div>";
			return $ret.self::messageHTML($reponse->getErrorMessage(), $this->_errorClass);
		}
		
		// Creation de la div HTML parente
		$ret = "\n".'<div class="liste-parent">';
		
		// G??n??ration des bouttons utilisateur
		$ret .= $this->generateButtons();
		
		// Initialisation de la liste
		$ret .= '<div>';
		
		// Pr??paration des donn??es ?? afficher
		$donnees = $this->prepareDataArray($reponse, $ret);
		
		// Cr??ation de tableau HTML
		$lmId = $this->_lm->getId();
		$ret .= '<table class="liste'.(($this->_fixedPaging)? ' fix-margin"' : '"').' '
			.(($this->_fixedTitles)? ' fixed-titles="true"' : '')
			.' disp-tabSelect="'.(($this->_quest)? 'true' : 'false').'"'
			.(($lmId == null)?'' : " data-id='".$lmId."' ").'>'."\n";
		
		//Creation des titres
		$colonnes = $reponse->getColumnsMeta();
		$ret .= $this->generateTitles($colonnes);
		
		//Affichage des champs de saisie pour la  recherche
		$width = $this->_lm->getIdealColumnsWidth($donnees, 3, $this->_maxSizeInputs);
		$ret .= $this->generateSearchInputs($colonnes, $width);
		
		// Si le tableau est vide -> retourne messageListeVide
		if(count($donnees) == 0 || count($donnees[0]) == 0){
			$ret .= "</table>\n";
			$ret .= self::messageHTML($this->_emptyListMessage, $this->_errorClass);
		}
		else { // Cr??ation du contenu du tableau HTML
			$ret .= $this->generateContent($donnees, $colonnes)."</table>\n";
		}
		
		// Affichage du tableau des numeros de page
		$ret .= $this->generatePaging($reponse->getRowsCount());
		$ret .= "</div></div>\n</div>\n";

		// Ajout des scripts
		$ret .= '<script type="text/javascript" src="'.LM_JS.'jquery-3.2.1.min.js"></script>'
			."\n".'<script type="text/javascript" src="'.LM_JS.'listeManager.js"></script>'."\n";
		// Ajout du css si appliqu??
		if($this->_applyDefaultCSS){
			$ret .= '<link rel="stylesheet" type="text/css" href="'.LM_CSS.'base.css"/>'."\n";
		}
		return $ret;
	}

	
			/*-**************************
			***   SETTERS & GETTERS   ***
			****************************/

	/**
	* Definit le message d'erreur a afficher si aucun resultat n'est retournee par la requete 
	* @param string $message le nouveau message a definir
	*/
	public function setEmptyListMessage($message){
		$this->_emptyListMessage = $message;
	}

	/**
	 * Definit le nom de la class HTML des messages d'erreurs affiches
	 * @param string $classe le nouveau nom de la classe des messages d'erreur. Si null pas de classe affich??e.
	 */
	public function setErrorMessageClass($classe){
		$this->_errorClass = $classe;
	}

	/**
	 * Active / desactive la fonction de masquage de colonne en JS
	 * @param boolean $valeur la nouvele valeur pour ce param??tre, valeur par defaut true
	 * @return boolean false si le param??tre entr?? n'est pas un boolean.
	 */
	public function enableJSMask($valeur){
		if(!is_bool($valeur))
			return false;
		
		$this->_enableJSMask = $valeur;
	}
	
	/**
	 * Attribue les nouvelles classes HTML a appliquer une ligne sur deux dans la liste HTML
	 * @param string $class1 classe des lignes impaires. Si null rien ne sera applique
	 * @param string $class2 classe des linges paires. Si null rien ne sera applique
	 */
	public function setRowsClasses($class1, $class2){
		$this->_class1 = $class1;
		$this->_class2 = $class2;
	}

	/**
	 * Definit le nombre de resultats a afficher sur une page.
	 * @param int $valeur le nombre de lignes a afficher par pages
     * @return boolean false si la valeur entree est incorrecte
	 */
	public function setNbResultsPerPage($valeur){
		if(!is_int($valeur) || $valeur <= 0)
			return false;

		$this->_nbResultsPerPage = $valeur;
	}

	/**
	 * @return int le nombre de lignes de resultat a afficher par page
	 */
	public function getNbResultsPerPage(){
		return $this->_nbResultsPerPage;
	}

	/**
	 * Definit quelle page de resultats doit afficher le template. Valeur par defaut : 1
	 * @param int $numeroPage le numero de la page a afficher (pour la 1re page : 1)
	 * @return boolean false si la valeur entree est incorrecte
	 */
	public function setCurrentPage($numeroPage){
		if(intval($numeroPage) != $numeroPage || $numeroPage <= 0)
			return false;

		$this->_currentPage = $numeroPage;
	}

	/**
	 * D??finir un callback ?? appeler dans chaque cellule de la liste.
	 * Definit le callback (la fonction) qui sera executee pour chaque valeur lors de l'affichage des donnees 
	 * dans les cellules du tableau. Cette fonction doit etre definie comme il suit :
	 * * 4 parametres d'entree :
	 *    1. cellule : la valeur de l'element en cours
	 *    2. colonne : le nom de la colonne en cours
	 *    3. numLigne   : le numero de la ligne en cours
	 *    4. ligne    : un array associatif contenant toutes les donn??es de la ligne en cours
	 *    5. numCol   : le numero de la colonne en cours
	 * * valeur de retour de type string (ou du moins un type qui peut ??tre transform?? en string). Si vous voulez laissez la case vide, retournez false
	 * @param callable $fonction le nom du callback a utiliser, null si aucun. Valeur par defaut : null
	 * @param bool $replaceTagTD d??finit si le callback d??finit r????crit les balises td ou non. Par d??faut ce param??tre vaut false, ce qui signifit que ListTemplate ??crit automatiquement des balises td de la liste.
	 * Cette option est utile si vous souhaitez ajouter des attributs particuliers aux cellules de votre liste
	 * @return bool false si le param??te $replaceTagTD n'est pas un bool??en
	 */
	public function setCellCallback(callable $fonction, $replaceTagTD=false){
		if(!is_bool($replaceTagTD)){
			return false;
		}
		$this->_replaceTagTD = $replaceTagTD;
		$this->_cellCallback = $fonction;
	}

	/**
	 * D??finir un callback ?? appeler ?? la cr??ation de chaque ligne de la liste.
	 * Ce callback sera appel?? par le template ?? la cr??ation d'une nouvelle balise tr (balise ouvrante) et doit avoir pour caract??ristiques :
	 *  * 2 param??tres d'entr??e :
	 *    * 1. numero  : correspond au num??ro de la ligne en cours
	 *    * 2. donnees : array php contenant l'ensemble des donn??es selectionn??es dans la base de donn??es qui seront affich??es dans cette ligne du tableau
	 *  * valeur de retour de type string (ou du moins un type qui peut ??tre transform?? en string).
	 * @param callable $fonction le nom du callback a utiliser, null si aucun. Valeur par defaut : null
	 */
	public function setRowCallback(callable $fonction){
		$this->_rowCallback = $fonction;
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
		$this->_columnCallback = $fonction;
		$this->_addedColumns = $titres;
	}

	/**
	 * D??finit le nombre de liens max ?? afficher dans la pagination.
	 * @param int $valeur le nombre de liens max ?? afficher.
	 */
	public function setPagingLinksNb($valeur){
		if(intval($valeur) < 0)
			return false;

		$this->_pagingLinksNb = $valeur;
	}

	/**
	 * Permet d'ajouter une rubrique d'aide ou une legende ?? la liste actuelle
	 * @param string|null $link : le lien url vers la page d'aide. Si null alors le lien sera desactiv??
	 */
	public function setHelpLink($link) {
		if(strlen($link) > 0)
			$this->_helpLink = $link;
		else
			$this->_helpLink = null;
	}

	/**
	 * D??finit si ListTemplate affiche ou non les champs de recherche.
	 * @param bool $valeur true pour activer, false pour desactiver
	 * @return bool false si l'argument n'est pas un booleen
	 */
	public function displaySearchInputs($valeur) {
		if(!is_bool($valeur))
			return false;

		$this->_quest = $valeur;
	}

	/**
	 * D??finit si ListTemplate affiche ou non le nombre de r??sultats total retourn??e par la requete
	 * @param bool $valeur true pour activer, false pour desactiver
	 * @return bool false si l'argument n'est pas un booleen
	 */
	public function displayResultsInfos($valeur) {
		if(!is_bool($valeur))
			return false;

		$this->_displayResultsInfos = $valeur;
	}

	/**
	 * D??finit si le template doit charger le fichier CSS par d??faut et appliquer le style du template par d??aut
	 * Si vous souhaitez personnaliser le style de votre liste vous devriez desactiver cette option et inclure votre propre fichier CSS
	 * Cette m??thode d??sactive aussi l'option des titres fix??s lorsque l'tuilisateur scroll, car sans le CSS par d??faut cette seconde option peut cr??er des r??sultats inattendus.
	 * Si toutesfois vous ne souhaitez pas d??sactiver cette option utilisez la m??thode *fixTitles(true)* pour la r??activer.
	 * @param bool $valeur false pour desactiver, true pour activer
	 * @return bool false si l'argument n'est pas un booleen
	 */
	public function applyDefaultCSS($valeur) {
		if(!is_bool($valeur))
			return false;

		$this->_applyDefaultCSS = $valeur;
		if(!$valeur)
			$this->fixTitles($valeur);
	}

	/**
	 * D??finit la taille maximale des champs de saisie pour la recherche.
	 * Cette valeur correspond ?? un nombre de caract??re, et affecte l'attribut size des balises input.
	 * @param int $valeur la nouvelle taille maximale des champs de saisie pour la recherche
	 * @param bool $invariable passez ce param??tre a true si vous souhaitez que la m??me taille de champs soit appliqu??es ?? tous
	 * @return bool false si les param??tres sont incorrects
	 */
	public function setMaxSizeInputs($valeur, $invariable=false) {
		if($valeur != intval($valeur) || $valeur <= 0)
			return false;

		$this->_maxSizeInputs = $valeur;
		$this->_constInputsSize = $invariable;
	}

	/**
	 * D??finit si les titres de votre liste restent fix??s en haut de l'??cran lorsque l'utilisateur scroll sur la page.
	 * @param bool valeur true pour activer false pour d??sactiver cette option
	 * @return bool false si l'arguemnt n'est pas un bool??en.
	 */
	public function fixTitles($valeur) {
		if(!is_bool($valeur) || !ListManager::isUnique())
			return false;
		$this->_fixedTitles = $valeur;
	}

	/**
	 * D??finit sui les liens de navigation entre pages sont fix??s en bas de l'??cran ou non.
	 * @param bool valeur true pour activer false pour d??sactiver cette option
	 * @return bool false si l'arguemnt n'est pas un bool??en.
	 */
	public function fixPaging($valeur) {
		if(!is_bool($valeur) || !ListManager::isUnique())
			return false;
		$this->_fixedPaging = $valeur;
	}

	/**
	 * @return bool true si le tableau de pagination sera affich??, false sinon
	 */
	public function issetPaging() {
		return $this->_pagingLinksNb != false;
	}


	/**
	 * Ajoute des boutons dans la division boutons ?? gauche des listes
	 * @param array $buttons contient le code HTML des boutons ?? ajouter
	 */
	public function addButtons(array $buttons) {
		foreach ($buttons as $bouton) {
			$this->_userButtons[] = $bouton;
		}
	}

			/*-****************
			***   PRIVATE   ***
			******************/

	/**
	 * R??cup??re les donn??es de la r??ponse et les pr??pare pour le tableau ?? afficher.
	 * Cette m??thode r??ccup??re la liste de donn??es depuis l'objet r??ponse et retourne uniquement celles correspondantes ?? la page en cours.
	 * Inscrit ??galement le nombre de r??sultats retourn??s si l'option est activ??e.
	 * @param RequestResponse reponse l'objet r??ponse
	 * @param string ret (in out) contient le code HTML ?? retourner
	 * @return array l'array contenant les donn??es ?? afficher
	 */
	private function prepareDataArray($reponse, &$ret) {
		// Preparation de l'array a afficher
		$donnees =  array();
		while(($ligne = $reponse->nextLine()) != null)
			$donnees[] = $ligne;
		$nbLignes = $reponse->getRowsCount();
		$debut = ($this->_currentPage - 1) * $this->_nbResultsPerPage;
		$fin = min(($this->_currentPage) * $this->_nbResultsPerPage, $nbLignes);

		// Si la page actuelle n'existe pas -> redirection sur 1re page
		if($debut > $fin) {
			$debut = 0;
			$fin = $this->_nbResultsPerPage;
			$this->_currentPage = 1;
		}
		// $donnees ne contient plus que les valeurs a afficher
		$donnees = array_slice($donnees, $debut, $this->_nbResultsPerPage);
		
		//Affichage du nombre de resultats
		$debut++;
		if($this->_displayResultsInfos)
			$ret .= self::messageHTML("Lignes : $debut - $fin / $nbLignes", 'info-resultats', 'p')."\n";
		
		return $donnees;
	}
	
	
	/**
	 * G??n??re la division contenant tous les boutons utilisateur.
	 * @return string code HTML des boutons.
	 */
	private function generateButtons() {
		$lmId = $this->_lm->getId();
		//Ajout des boutons options sur le cete
		$ret = "\n<div><div class='boutons-options'>";
	
		// Bouton pour reset le mask en JS
		if($this->_enableJSMask)
			$ret .= '<a class="annuler-masque" style="display:none;" href="#"><img height="40" width="40" src="'.LM_IMG.'mask-cross.png"></a>';
	
		// Bouton excel
		if($this->_lm->isExcelEnabled()){
			$ret .= '<a href="'.self::creerUrlGET('lm_excel'.$lmId, 1).'" class="btn-excel"><img height="40" width="40" src="'.LM_IMG.'excel-ico.png"></a>';
		}

		//Bouton quest (recherche)
		if($this->_lm->isSearchEnabled()){
			$ret .= '<a class="btn-recherche" href="#"><img height="40" width="40" src="'.LM_IMG.'search-ico.png"></a>';
			// Ajout du form si recherche activee
			$ret .= "\n<form class='recherche' id='recherche".$lmId."' action='' method='GET'"
				.'><input type="submit" value="Go!" style="display:none;"/>';

			// Ajout des param??tres GET d??j?? pr??sents
			foreach ($_GET as $nom => $valeur) {
				if(!is_array($valeur) && !in_array($nom, ['lm_tabSelect'.$lmId, 'lm_excel'.$lmId, 'lm_page'.$lmId])) {
					$ret .= "<input type='hidden' name='$nom' value='$valeur'/>";
				}
			}
			$ret .= '</form>';
		}

		// Lien vers la rubrique d'aide / l??gende associ??e
		if($this->_helpLink != null){
			$ret .= "<a href='$this->_helpLink' target='_blank' class='btn-help'><img height='40' width='40' src='".LM_IMG."book-ico.png'></a>";
		}
		
		//Bouton RaZ
		if($this->_lm->issetUserFilter() || isset($_GET['lm_orderBy'.$lmId])) {
			$tabGet = $_GET;
			if(isset($_GET['lm_tabSelect'.$lmId]))
				unset($tabGet['lm_tabSelect'.$lmId]);
			if(isset($_GET['lm_orderBy'.$lmId]))
				unset($tabGet['lm_orderBy'.$lmId]);
			if(isset($_GET['lm_excel'.$lmId]))
				unset($tabGet['lm_excle'.$lmId]);
			
			$ret .= '<a href="'.self::creerUrlGET(null, null, $tabGet).'"><img height="40" width="40" src="'.LM_IMG.'eraser-ico.png"></a>';
		}

		// Boutons utilisateurs ajout?? par le d??veloppeurs
		foreach ($this->_userButtons as $bouton) {
			$ret .= "$bouton\n";
		}
		$ret .= "</div>\n";
		return $ret;
	}
	
	/**
	 * G??n??re les titres de la liste html.
	 * @param array $colonnesMeta les m??ta donn??es des colonnes.
	 * @return string code HTML des titres.
	 */
	private function generateTitles(array $colonnesMeta){
		if(count($colonnesMeta) <= 0)
			return '';
		
		$ret = "<tr class='ligne-titres'>";
		$baseOrderBy = $this->_lm->getOrderBy();
		$lmId = $this->_lm->getId();
		$i = 0;
		foreach ($colonnesMeta as $col ) {
			$nomColonne = strtolower( ($col->table != null) ? $col->table . '.' . $col->name : $col->name );
			
			// On v??rifie que la colonne en cours n'est pas masqu??e
			if (! $this->_lm->isMasked($nomColonne, $col->alias)) {
				
				// Gestion du order by
				$signeOrder = '';
				$orderArray = [];
				if (isset($_GET ['lm_orderBy' . $lmId]) || count($baseOrderBy)) {
					if (isset ( $_GET ['lm_orderBy' . $lmId] ))
						$orderArray = explode( ';', $_GET ['lm_orderBy' . $lmId]);
					$orderArray = array_map('strtolower', array_unique(array_merge($baseOrderBy, $orderArray)));

					// Tableau des signes : '' = tri croissant, '-' = tri d??croissant, '*' = pas de tri
					$tabSignes = ['', '-', '*'];
					// Tableau des expressions : on peut identifier une colonne par son nom, son num??ro ou son alias
					$tabExpr = ['$nomColonne', '($i + 1)', '$col->alias'];
					$signeOrder = '';
					$signeSuiv = false;
					// Pour chaque combinaison [signe x expresion_colonne]...
					for ($j=0; $j < count($tabSignes); $j++) {
						foreach ($tabExpr as $expr) {

							$valCol = eval("return $expr;");
							if(strlen($valCol) > 0){
								$valCol = $tabSignes[$j].$valCol;

								// ... on recherche si la valeur existe deja dans le tableau order by...
								$key = array_search($valCol, $orderArray);
								if($key !== false){
									// ... on la supprime ...
									unset($orderArray[$key]);
									// var_dump($orderArray);

									// ... si c'est le premier passage pour cette colonne, on r??cup??re le signe suivant
									if($signeSuiv === false){
										$numCol = $key + 1;
										$signeSuiv = $tabSignes[($j+1) % count($tabSignes)];
									}
								}
							}
						}
					}
					// Ajout de la colonne index?? par le signe suivant
					array_unshift($orderArray, $signeSuiv.($i + 1));
					// MaJ du signe order (html)
					if($signeSuiv == '-')
						$signeOrder = "<br>$numCol&Delta;";
					else if($signeSuiv == '*')
						$signeOrder = "<br>$numCol&nabla;";
					// orderArray => orderString
					$orderString = ((count($orderArray)) ? implode(';', array_unique($orderArray)) : null);
				}
				else {
					$orderString = $nomColonne;
				}
				
				// Pr??paration du titre ?? afficher
				$listTitles = $this->_lm->getListTitles();
				if (isset( $listTitles[strtolower($col->alias)]) ){
					$titreAffiche = $listTitles[strtolower($col->alias)];
				}
				else if(isset($listTitles[$nomColonne])){
					$titreAffiche = $listTitles[$nomColonne];
				}
				else {
					$titreAffiche = (($col->alias == null) ? $col->name : $col->alias);
					// Si titre en caps => ucfirst
					if ($titreAffiche == strtoupper($titreAffiche))
						$titreAffiche = ucfirst(strtolower($titreAffiche));
				}
				
				// Cr??ation du lien pour order by
				if ($this->_lm->isOrderByEnabled ())
					$lienOrderBy = '<a class="titre-colonne" href="'.self::creerUrlGET('lm_orderBy'.$lmId, $orderString )."\">$titreAffiche</a>$signeOrder";
				else
					$lienOrderBy = $titreAffiche;
				
				if ($this->_enableJSMask)
					$lienMasque = '<a class="masque" href="#">x</a>';
				else
					$lienMasque = '';
					
				// Affiche les liens et les titres
				$ret .= '<th>' . $lienMasque . $lienOrderBy . "</th>\n";
			}
			$i++;
		}

		// Utilisation du callback pour ajouter une colonne
		if($this->_columnCallback != null) {
			foreach ($this->_addedColumns as $col) {
				$ret .= '<th>';
				if($this->_enableJSMask)
					$ret .= '<a class="masque" href="#">x</a>';
				$ret .= $col.'</th>';
			}
		}
		$ret .= "</tr>\n";
		return $ret;
	}
	
	/**
	 * G??n??re la ligne du tableau contenant les champs de recherche.
	 * @param array $colonnesMeta les meta donn??s des colonnes
	 * @param array $width les largeurs ?? appliquer pour chaque champs de saisie
	 * @return string le code HTML des champs de recherche.
	 */
	private function generateSearchInputs(array $colonnesMeta, $width){
		if(!$this->_lm->isSearchEnabled())
			return '';

		// Gestion des largeurs
		if(!is_array($width) || count($colonnesMeta) > count($width)) {
			$width = [];
			foreach($colonnesMeta as $meta)
				$width[] = min($meta->len, $this->_maxSizeInputs);
		}
		// Construction de la ligne
		$ret = "<tr class='tabSelect'"
				.(($this->_quest)? '' : ' style="display:none;" ').'>';
		$lmId = $this->_lm->getId(); 
		$i = 0;
		$filter = $this->_lm->getFilter();
		foreach ($colonnesMeta as $meta){
			// Nom de la colonne = table.colonne
			$nomColonne = strtolower(($meta->table != null)? $meta->table.'.'.$meta->name : $meta->name );

			// On v??rifie que la colonne en cours n'est pas masqu??e
			if(!$this->_lm->isMasked($nomColonne, $meta->alias)) {

				//Determine le contenu du champs
				$valeur = (isset($filter[$nomColonne])? $filter[$nomColonne] : '');
					
				//Determine la taille du champs
				if($this->_constInputsSize){
					$taille = $this->_maxSizeInputs;
				}
				else { 
					$taille = $width[$i];
				}

				$ret .= '<td><input type="text" name="lm_tabSelect'.$lmId.'['.$nomColonne.']"'
						." form='recherche".$lmId."' size='$taille' value='$valeur'/></td>";
			}
			$i++;
		}

		// Ajout de colonnes vide si callback activ??
		if($this->_columnCallback != null) {
			foreach ($this->_addedColumns as $col) {
				$ret .= '<td></td>';
			}
		}
		$ret .= "</tr>\n";
		return $ret;
	}
	
	/**
	 * G??n??re et retourne le contenu de la liste HTML
	 * @param array $donnees le tableau contenant les donn??es ?? ins??rer
	 * @param array $colonnesMeta m??tas donn??es des colonnes 
	 * @return string code HTML du contenu de la liste
	 */
	private function generateContent(array $donnees, array $colonnesMeta){
		$ret = '';
		
		//Insertion de donnees
		$i = 0;
		foreach ($donnees as $ligne) {
			//Gestion des classes
			$classe = (($i % 2)? $this->_class1 : $this->_class2);
			$ret .= '<tr'.(($classe == null)? ' ' : " class='$classe' ");
	
			// Utilisation du callback
			if($this->_rowCallback != null) {
				$fct = $this->_rowCallback;
				$ret .= ' '.call_user_func_array($fct, array($i, $ligne));
			}
			$ret .= '>';
	
			//Construction des cellules colonne par colonne
			for ($j=0; $j < count($colonnesMeta); $j++){
	
				$cellule = $ligne[$j];
				$nomColonne = strtolower( (($colonnesMeta[$j]->table != null)?
					$colonnesMeta[$j]->table.'.'.$colonnesMeta[$j]->name :
					$colonnesMeta[$j]->name ) );
				
				// On v??rifie que la colonne en cours n'est pas masqu??e
				if(!$this->_lm->isMasked($nomColonne, $colonnesMeta[$j]->alias)) {
	
					// Application du callback (si non null)
					if($this->_cellCallback != null) {
							
						// Appel au callback
						$fct = $this->_cellCallback;
						$cellule = ( (($retFCT = call_user_func_array($fct,
								array($cellule, $nomColonne, $i, $ligne, $j))) === null)?
								(($this->_replaceTagTD)? "<td>$cellule</td>" : $cellule ) : $retFCT ) ;
					}
					// Si la cellule ne contient rien -> '-'
					if(strlen($cellule) == 0)
						$cellule = '-';
						$ret .= (($this->_replaceTagTD)? '' : '<td>') .$cellule. (($this->_replaceTagTD)? '' : '</td>');
				}
			}
	
			// Ajout des colonnes par callback
			if($this->_columnCallback != null) {
				$fct = $this->_columnCallback;
				$ret .= call_user_func_array($fct, array($i, $ligne));
			}
	
			$ret .= "</tr>\n";
			$i++;
		}
		return $ret;
	}
	
	/**
	 * G??n??re le tableau HTML contenant la pagination.
	 * @param int $nbLignes nombre de lignes retourn??e par l'ex??cution de la requete
	 * @return string code html des liens pagination
	 */
	private function generatePaging($nbLignes){
		if($nbLignes <= $this->_nbResultsPerPage || $this->_pagingLinksNb == false)
			return '';
		
		$lmId = $this->_lm->getId(); 
		$ret = '<div class="pagination'.(($this->_fixedPaging)? ' fixed' : '' ).'"><table align="center"><tr>';
		$nbPages = (is_int($nbPages = ($nbLignes / $this->_nbResultsPerPage))? $nbPages : round($nbPages + 0.5) );
		
		// S'il y a plus de pages que la limite affichable
		if($nbPages > $this->_pagingLinksNb){
			$debut = $this->_currentPage - intval($this->_pagingLinksNb / 2);
			if($debut <= 1){
				$debut = 1;
				$fin = $this->_pagingLinksNb + 1;
			}
			// Ajout de la 1re page si besoin
			else {
				$ret .= '<td><a href="'.self::creerUrlGET('lm_page'.$lmId, 1).'">&lt;&lt;</td>';
				$fin = min($debut + $this->_pagingLinksNb, $nbPages);
			}
		}
		else {
			$debut = 1;
			$fin = $nbPages;
		}
			
		// Creation des liens
		for ($i=$debut; $i <= $fin; $i++) {
			$ret .= '<td>';
			// Pas de lien si on est deje sur la pageActuelle
			if($i == $this->_currentPage)
				$ret .= "$i";
				else {
					// Construction du lien de la page
					$ret .= '<a href="'.self::creerUrlGET('lm_page'.$lmId, $i).'">'.$i.'</a>';
				}
				$ret .= '</td>';
		}
		// Ajout du lien vers la derniere page si besoin
		if($fin != $nbPages){
			$ret .= '<td><a href="'.self::creerUrlGET('lm_page'.$lmId, $nbPages).'">&gt;&gt;</td>';
		}
		$ret .= "</tr></table></div>\n";
		return $ret;
	}
	
	private static function messageHTML($message, $nom, $balise='p'){
		return '<'.$balise.(($nom == null)? '' : ' class="'.$nom.'"' )
			.'>'.$message.'</'.$balise.'>';
	}

	private static function creerUrlGET($nom, $val, $get=null){
		if($get === null)
			$get = $_GET;
		
		if($val !== null)
			$get[$nom] = $val;

		foreach ($get as &$valeur) {
			if(!is_array($valeur) && strlen($valeur) == 0)
				unset($valeur);
		}
		return strtok($_SERVER['REQUEST_URI'], '?').'?'.http_build_query($get);
	}
	
}

?>
