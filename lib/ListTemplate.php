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
 * Objet Template : construit la liste HTML avec les données qu'elle contient. Constitue la vue.
 * 
 * Tout comme ListManager, ListTemplate possède un comportement de base modifiable grâce aux méthodes de classe. Vous pouvez modifier :
 * * Activer / desactiver / modifier le nom des classes des lignes paires / impaires
 * * Modifier la classe et le message des erreurs 'Liste vide'
 * * Activer / désactiveer les champs de saisie pour rechercher
 * * Modifier le nombre de lignes par page
 * * Modifier le nombre de pages à afficher dans la pagination
 * * Utiliser des callbacks pour :
 *    * modifier le contenu des cellules
 *    * ajouter des colonnes à votre tableau
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
	 * @var ListManager $lm objet ListManager parent qui régit ce template
	 */
	private $lm;
	/**
	 * @var string $class1 nom de la classe HTML appliquée aux lignes imparaires
	 */
	private $class1;
	/**
	 * @var string $class2 nom de la classe HTML appliquée aux lignes paires
	 */
	private $class2;
	/**
	 * @var string $emptyListMessage le message qui sera affiche si la liste ne contient pas de donnees
	 */
	private $emptyListMessage;
	/**
	 * @var string  le nom de la classe HTML des balises p qui contiendront le message d'erreur
	 */
	private $errorClass;
	/**
	 * @var int  nombre de resultats affiches par page. Valeur par defaut = 100
	 */
	private $nbResultsPerPage;
    /**
     * @var int numero de la page de resultats actuelle
     */
	private $currentPage;
	/**
	 * @var string nom du callback a appeler lors de l'affichage d'une cellule (balises 'td')
	 */
	private $cellCallback;
	/**
	 * @var bool définit si le callback de cellule réécrit les balises TD des cellules ou non. 
	 * Passez cet attribut à true pour ajouter manuellement les balises td avec le callback et pour modifier leurs attribus
	 */
	private $replaceTagTD;
	/**
	 * @var string $rowCallback nom du callback a appeler lors de l'affichage des des lignes (balises 'tr').
	 * Permet de modifer les attributs HTML de la balise
	 */
	private $rowCallback;
	/**
	 * @var string $columnCallback nom du callback qui servira à ajouter des colonnes à la liste 
	 */
	private $columnCallback;
	/* TODO
	 * @var boolean definit l'utilisation du système de cache pour les requêtes lourdes
	 */
	// private $useCache;
	/**
	 * @var bool $enableJSMask définit si le template permet à l'utilisateur de masquer les colonnes grâce
	 * à JavaScript avecla petite croix rouge
	 */
	private $enableJSMask;
	/**
	 * @var int nombre de liens de page à afficher au maximum dans la pagination
	 */
	private $pagingLinksNb;
	/**
	 * @var string $helpLink lien vers la page d'aide associée à cette liste
	 */
	private $helpLink;
	/**
	 * @var bool $displayResultsInfos définit si ListTemplate affiche ou non le nombre de résultats total retournée par la requete
	 */
	private $displayResultsInfos;
	/**
	 * @var bool $applyDefaultCSS déinit si le template doit appliquer le style par defaut du fichier base.css ou non
	 */
	private $applyDefaultCSS;
	/**
	 * @var integer longueur maximale des champs de saisie pour la recherche par colonne
	 */
	private $maxSizeInputs;
	/**
	 * @var bool $fixedTitles définti si les titres de la listes sont fixés lorsque l'utilisateur scroll
	 */
	private $fixedTitles;
	
	/**
	 * @var string $CLASS1 classe par défaut des lignes impaires du tableau
	 */
	public static $CLASS1 = 'gris-clair';
	/**
	 * @var string $CLASS2 classe par défaut des lignes paires du tableau
	 */
	public static $CLASS2 = 'blanc';
	
	
	
			/*-*********************
			***   CONSTRUCTEUR   ***
			***********************/
	
	/**
	 * Construit un objet ListTemplate et lui assigne son comportement par défaut.
	 * @param ListManager $lm l'objet ListManager appelant qui utilise cet objet template
	 */
	 public function __construct(ListManager $lm){
		$this->lm = $lm;
		$this->class1 = self::$CLASS1;
		$this->class2 = self::$CLASS2;
		$this->enableJSMask = true;
		$this->emptyListMessage = "Aucun resultat!";
		$this->errorClass = 'erreur';
		$this->currentPage = 1;
		$this->nbResultsPerPage = 50;
		$this->pagingLinksNb = 10;
		$this->cellCallback = null;
		$this->replaceTagTD = false;
		$this->rowCallback = null;
		$this->columnCallback = null;
		// $this->useCache = false;
		$this->helpLink = null;
		$this->displayResultsInfos = true;
		$this->applyDefaultCSS = true;
		$this->maxSizeInputs = 30;
		$this->fixedTitles = true;
	}
	
	
			/*-*****************
			***   METHODES   ***
			*******************/

	/**
	 * Construit une liste HTML à partir d'un objet RequestResponse.
	 * Il s'agit de la fonction principale de la classe. L'obje génère un template à partir des valeurs de ses attributs 
	 * et contenant le résultat de la requete passée en paramètre.
	 * Le template se découpe en 4 parties :
	 * * *Tout en haut* : les titres des colonnes avec les liens permettant de les trier ou de les masquer.
	 * * *Tout à gauche* : les boutons d'options permettant d'activer certaines fonctionnalitées
	 * * *Au centre* : la liste de données
	 * * *En bas* : la pagination et les liens vers les autres pages de la liste
	 * @param RequestResponse $reponse contenant l'ensemble des resultats de la requete
	 * @return string le code HTML de la liste HTML genere
	 */
	public function construct(RequestResponse $reponse){

		// On teste d'abord s'il y a erreur dans la reponse
		if($reponse->error()){
			$ret = "<div class='boutons-options'><a href='".self::creerUrlGET(null, null, array())."'>Clear</a></div>";
			return $ret.self::messageHTML($reponse->getErrorMessage(),
				$this->errorClass);
		}

		// Preparation de l'array a afficher
		$donnees =  array();
		while(($ligne = $reponse->nextLine()) != null)
			$donnees[] = $ligne;
		$nbLignes = $reponse->getRowsCount();
		$debut = ($this->currentPage - 1) * $this->nbResultsPerPage;
		$fin = min(($this->currentPage) * $this->nbResultsPerPage, $nbLignes);

		// Si la page actuelle n'existe aps -> redirection sur 1re page
		if($debut > $fin) {
			$debut = 0;
			$fin = $this->nbResultsPerPage;
			$this->currentPage = 1;
		}

		// Enregistrement des donnees dans le cache
		// if($this->useCache && $nbLignes > Cache::NB_LIGNES_MIN){
		// 	$cacheID = md5(uniqid());
		// 	$cache = new Cache($cacheID);
		// 	$cache->write($reponse, $this->nbResultsPerPage);
		// }

		// $donnees ne contient plus que les valeurs a afficher
		$donnees = array_slice($donnees, $debut, $this->nbResultsPerPage);
		$lmId = $this->lm->getId();

		// Creation de la div HTML parente
		$ret = "\n".'<div class="liste-parent">';
		
		//Ajout des boutons options sur le cete
		$ret .= "\n<div><div class='boutons-options'>";
		
		// Bouton pour reset le mask en JS
		if($this->enableJSMask)
			$ret .= '<a class="annuler-masque" href="#"><img height="40" width="40" src="'.LM_IMG.'mask-cross.png"></a>';

		// Bouton excel
		if($this->lm->isExcelEnabled()){
			$ret .= '<a href="'.self::creerUrlGET('lm_excel'.$lmId, 1).'" class="btn-excel"><img height="40" width="40" src="'.LM_IMG.'excel-ico.png"></a>';
		}

		//Bouton quest (recherche)
		if($this->lm->isSearchEnabled()){
			$ret .= '<a class="btn-recherche" href="#"><img height="40" width="40" src="'.LM_IMG.'search-ico.png"></a>'; 
			
			// Ajout du form si recherche activee
			$ret .= "\n<form class='recherche' id='recherche".$lmId."' action='' method='GET'"
				.'><input type="submit" value="Go!"/>';

			// Ajout des paramètres GET déjà présents
			foreach ($_GET as $nom => $valeur) {
				if($nom != 'lm_tabSelect'.$lmId && !is_array($valeur)) {
					$ret .= "<input type='hidden' name='$nom' value='$valeur'/>";
				}
			}

			$ret .= '</form>';
		}

		// Lien vers la rubrique d'aide / légende associée
		if($this->helpLink != null){
			$ret .= "<a href='$this->helpLink' target='_blank' class='btn-help'><img height='40' width='40' src='".LM_IMG."book-ico.png'></a>";
		}

		//Bouton RaZ
		if(isset($_GET['lm_tabSelect'.$lmId]) || isset($_GET['lm_orderBy'.$lmId])) {
			$tabGet = $_GET;
			
			if(isset($_GET['lm_tabSelect'.$lmId]))
				unset($tabGet['lm_tabSelect'.$lmId]);
			if(isset($_GET['lm_orderBy'.$lmId]))
				unset($tabGet['lm_orderBy'.$lmId]);
			
			$ret .= '<a href="'.self::creerUrlGET(null, null, $tabGet).'"><img height="40" width="40" src="'.LM_IMG.'eraser-ico.png"></a>';
		}

		$ret .= "</div>\n";

		// Initialisation de la liste
		$ret .= '<div>';

		//Affichage du nombre de resultats
		$debut++;
		if($this->displayResultsInfos)
			$ret .= self::messageHTML("Lignes : $debut - $fin / $nbLignes", 'info-resultats', 'p')."\n";

		$ret .= '<table class="liste'.((strlen($lmId))? '"' : ' fix-margin"').' '.(($this->fixedTitles)? ' fixed-titles="true"' : '')
			.(($lmId == null)?'' : " data-id='".$lmId."' ").'>'."\n<tr class='ligne-titres'>";

		//Creation des titres
		$titres = $reponse->getColumnsName();
		$i = 0;
		foreach ($titres as $titre) {

			// On vérifie que la colonne en cours n'est aps masquée
			if(!in_array($titre, $this->lm->getMask())) {

				//Gestion du order by
				$signeOrder = '';
				if(isset($_GET['lm_orderBy'.$lmId])){
					$orderArray = explode(',', $_GET['lm_orderBy'.$lmId]);

					// Construction de la chaine orderBy
					if(($key = array_search($titre, $orderArray)) !== false ) {
						unset($orderArray[$key]);
						array_unshift($orderArray, "-$titre");
						$signeOrder = '&Delta;';
					}
					else if (($key = array_search("-$titre", $orderArray)) !== false ){
						unset($orderArray[$key]);
						array_unshift($orderArray, $titre);
						$signeOrder = '&nabla;';
					}
					else {
						array_unshift($orderArray, $titre);
					}
					$orderString = implode(',', $orderArray);
				}
				else {
					$orderString = $titre;
				}

				// Préparation du titre à afficher
				$listTitles = $this->lm->getListTitles();
				if(isset($listTitles[$titre]))
					$titreAffiche = $listTitles[$titre];
				else 
					$titreAffiche = $titre;

				// Création du lien pour order by
				if($this->lm->isOrderByEnabled())
					$lienOrderBy = '<a class="titre-colonne" href="'
						.self::creerUrlGET('lm_orderBy'.$lmId, $orderString)."\">$titreAffiche</a><br>$signeOrder";
				else
					$lienOrderBy = $titreAffiche;

				if($this->enableJSMask)
					$lienMasque = '<a class="masque" href="#">x</a>';
				else 
					$lienMasque = '';

				// Affiche les liens et les titres
				$ret .= '<th>'.$lienMasque.$lienOrderBy."</th>\n";
				$i++;
			}
		}

		// Utilisation du callback pour ajouter une colonne
		if($this->columnCallback != null) {
			$fct = $this->columnCallback;
			$ret .= call_user_func_array($fct, array(0, $titres, true));
		}

		$ret .= "</tr>\n";

		//Affichage des champs de saisie pour la  recherche
		if($this->lm->isSearchEnabled()){
			$ret .= "<tr class='tabSelect'>";
			$types = $reponse->getColumnsType();
			for ($i=0; $i < count($titres); $i++) {

				// On vérifie que la colonne en cours n'est aps masquée
				if(!in_array($titres[$i], $this->lm->getMask())) {

					//Determine le contenu du champs
					$valeur = (isset($_GET['lm_tabSelect'.$lmId][$titres[$i]])? 
						$_GET['lm_tabSelect'.$lmId][$titres[$i]] : null);
					//Determine la taille du champs
					$taille = min($types[$i]->len, $this->maxSizeInputs);
					$ret .= '<td><input type="text" name="lm_tabSelect'.$lmId.'['.$titres[$i].']"'
						." form='recherche".$lmId."' size='$taille' value='$valeur'/></td>";
				}
			}
			$ret .= "</tr>\n";
		}
		
		// Si le tableau est vide -> retourne messageListeVide
		if(count($donnees) == 0){
			$ret .= "</table>\n";
			$ret .= self::messageHTML($this->emptyListMessage, $this->errorClass);
		}

		
		//Insertion de donnees
		else {
			$i = 0;
			foreach ($donnees as $ligne) {
				//Gestion des classes
				$classe = (($i % 2)? $this->class1 : $this->class2);
				$ret .= '<tr'.(($classe == null)? '' : " class='$classe' ");

				// Utilisation du callback
				if($this->rowCallback != null) {
					$fct = $this->rowCallback;
					$ret .= ' '.call_user_func_array($fct, array($i, $ligne));
				}
				$ret .= '>';

				//Construction des cellules colonne par colonne
				$j = 0;
				foreach ($ligne as $cellule){

					// On vérifie que la colonne en cours n'est pas masquée
					if(!in_array($titres[$j], $this->lm->getMask())) {

						// Application du callback (si non null)
						if($this->cellCallback != null) {
							$fct = $this->cellCallback;
							$cellule = ( (($retFCT = call_user_func_array($fct, array($cellule, $titres[$j], $i, $ligne))) === null)? $cellule : $retFCT ) ;
						}
						// Si la cellule ne contient rien -> '-'
						if(strlen($cellule) == 0)
							$cellule = '-';
						$ret .= (($this->replaceTagTD)? '' : '<td>') .$cellule. (($this->replaceTagTD)? '' : '</td>');
					}
					$j++;
				}

				// Ajout des colonnes par callback
				if($this->columnCallback != null) {
					$fct = $this->columnCallback;
					$ret .= call_user_func_array($fct, array($i, $ligne, false));
				}

				$ret .= "</tr>\n";
				$i++;
			}
			$ret .= "</table>\n";
		}

		// Affichage du tableau des numeros de page
		if($nbLignes > $this->nbResultsPerPage && $this->pagingLinksNb != false){
			$ret .= '<div class="pagination'.((strlen($lmId)? '' : ' fixed')).'"><table align="center"><tr>';
			$nbPages = (is_int($nbPages = ($nbLignes / $this->nbResultsPerPage))? $nbPages : round($nbPages + 0.5) );

			// S'il y a plus de pages que la limite affichable
			if($nbPages > $this->pagingLinksNb){
				$debut = $this->currentPage - intval($this->pagingLinksNb / 2);
				if($debut <= 1){
					$debut = 1;
					$fin = $this->pagingLinksNb + 1;
				}
				// Ajout de la 1re page si besoin
				else {
					$ret .= '<td><a href="'.self::creerUrlGET('lm_page'.$lmId, 1).'">&lt;&lt;</td>';
					$fin = min($debut + $this->pagingLinksNb, $nbPages);
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
				if($i == $this->currentPage)
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
		}

		$ret .= "</div></div>\n</div>\n";

		// Ajout des scripts
		$ret .= '<script type="text/javascript" src="'.LM_JS.'jquery-3.2.1.min.js"></script>'
			."\n".'<script type="text/javascript" src="'.LM_JS.'listeManager.js"></script>'."\n";
		// Ajout du css si appliqué
		if($this->applyDefaultCSS)
			$ret .= '<link rel="stylesheet" type="text/css" href="'.LM_CSS.'base.css">'."\n";

		// Fin
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
		$this->emptyListMessage = $message;
	}

	/**
	 * Definit le nom de la class HTML des messages d'erreurs affiches
	 * @param string $classe le nouveau nom de la classe des messages d'erreur. Si null pas de classe affichée.
	 */
	public function setErrorMessageClass($classe){
		$this->errorClass = $classe;
	}

	/**
	 * Active / desactive la fonction de masquage de colonne en JS
	 * @param boolean $valeur la nouvele valeur pour ce paramètre, valeur par defaut true
	 * @return boolean false si le paramètre entré n'est pas un boolean.
	 */
	public function enableJSMask($valeur){
		if(!is_bool($valeur))
			return false;
		
		$this->enableJSMask = $valeur;
	}
	
	/**
	 * Attribue les nouvelles classes HTML a appliquer une ligne sur deux dans la liste HTML
	 * @param string $classe1 classe des lignes impaires. Si null rien ne sera applique
	 * @param string $classe2 classe des linges paires. Si null rien ne sera applique
	 */
	public function setRowsClasses($classe1, $classe2){
		$this->class1 = $classe1;
		$this->class2 = $classe2;
	}

	/**
	 * Definit le nombre de resultats a afficher sur une page.
	 * @param int $valeur le nombre de lignes a afficher par pages
     * @return boolean false si la valeur entree est incorrecte
	 */
	public function setNbResultsPerPage($valeur){
		if(!is_int($valeur) || $valeur <= 0)
			return false;

		$this->nbResultsPerPage = $valeur;
	}

	/**
	 * @return int le nombre de lignes de resultat a afficher par page
	 */
	public function getNbResultsPerPage(){
		return $this->nbResultsPerPage;
	}

	/**
	 * Definit quelle page de resultats doit afficher le template. Valeur par defaut : 1
	 * @param int $numeroPage le numero de la page a afficher (pour la 1re page : 1)
	 * @return boolean false si la valeur entree est incorrecte
	 */
	public function setCurrentPage($numeroPage){
		if(intval($numeroPage) != $numeroPage || $numeroPage <= 0)
			return false;

		$this->currentPage = $numeroPage;
	}

	/**
	 * Définir un callback à appeler dans chaque cellule de la liste.
	 * Definit le callback (la fonction) qui sera executee pour chaque valeur lors de l'affichage des donnees 
	 * dans les cellules du tableau. Cette fonction doit etre definie comme il suit :
	 * * 4 parametres d'entree :
	 *    1. cellule : la valeur de l'element en cours
	 *    2. colonne : le nom de la colonne en cours
	 *    3. numLigne   : le numero de la ligne en cours
	 *    4. ligne    : un array associatif contenant toutes les données de la ligne en cours
	 * * valeur de retour de type string (ou du moins un type qui peut être transformé en string). Si vous voulez laissez la case vide, retournez false
	 * @param callable $fonction le nom du callback a utiliser, null si aucun. Valeur par defaut : null
	 * @param bool $replaceTagTD définit si le callback définit réécrit les balises td ou non. Par défaut ce paramètre vaut false, ce qui signifit que ListTemplate écrit automatiquement des balises td de la liste.
	 * Cette option est utile si vous souhaitez ajouter des attributs particuliers aux cellules de votre liste
	 * @return bool false si le paramète $replaceTagTD n'est pas un booléen
	 */
	public function setCellCallback(callable $fonction, $replaceTagTD=false){
		if(!is_bool($replaceTagTD)){
			return false;
		}
		$this->replaceTagTD = $replaceTagTD;
		$this->cellCallback = $fonction;
	}

	/**
	 * Définir un callback à appeler à la création de chaque ligne de la liste.
	 * Ce callback sera appelé par le template à la création d'une nouvelle balise tr (balise ouvrante) et doit avoir pour caractéristiques :
	 *  * 2 paramètres d'entrée :
	 *    * 1. numero  : correspond au numéro de la ligne en cours
	 *    * 2. donnees : array php contenant l'ensemble des données selectionnées dans la base de données qui seront affichées dans cette ligne du tableau
	 *  * valeur de retour de type string (ou du moins un type qui peut être transformé en string).
	 * @param callable $fonction le nom du callback a utiliser, null si aucun. Valeur par defaut : null
	 */
	public function setRowCallback(callable $fonction){
		$this->rowCallback = $fonction;
	}

	/**
	 * Définir un callback pour rajouter manuellement des colonnes dans votre liste
	 * Ce callback sera appelé par le template à la fin de la création des titres ET a la fnc de la création de chaque
	 * ligne de la liste. La fonction doit correspondre au format suivant
	 *  * 3 paramètres d'entrée :
	 *    * 1. numLigne  : int correspond au numéro de la ligne en cours
	 *    * 2. donnees   : array contenant l'ensemble des données selectionnées dans la base de données qui seront affichées dans cette ligne du tableau. Vaut null pour les titres
	 *    * 3. estTtitre : boolean vaut true si la fonciton est appelée dans la ligne des titres, false sinon 
	 *  * valeur de retour de type string (ou du moins un type qui peut être transformé en string).
	 * @param callable $fonction le nom du callback a utiliser, null si aucun. Valeur par defaut : null
	 */
	public function setColumnCallback(callable $fonction){
		$this->columnCallback = $fonction;
	}

	/* TODO
	 * Active ou désactive l'utilisation du cache pour les requêtes retournant un grand nombre de données
	 * @param boolean $valeur : true pour activer le système de cache, faux sinon
	 */
	// public function enableCache($valeur){
	// 	if(!is_bool($valeur))
	// 		return false;

	// 	$this->useCache = $valeur;
	// }

	/**
	 * Définit le nombre de liens max à afficher dans la pagination.
	 * @param int $valeur le nombre de liens max à afficher.
	 */
	public function setPagingLinksNb($valeur){
		if(intval($valeur) < 0)
			return false;

		$this->pagingLinksNb = $valeur;
	}

	/**
	 * Permet d'ajouter une rubrique d'aide ou une legende à la liste actuelle
	 * @param string|null $link : le lien url vers la page d'aide. Si null alors le lien sera desactivé
	 */
	public function setHelpLink($link) {
		if(strlen($link) > 0)
			$this->helpLink = $link;
		else
			$this->helpLink = null;
	}

	/**
	 * Définit si ListTemplate affiche ou non le nombre de résultats total retournée par la requete
	 * @param bool $valeur true pour activer, false pour desactiver
	 * @return bool false si l'argument n'est pas un booleen
	 */
	public function displayResultsInfos($valeur) {
		if(!is_bool($valeur))
			return false;

		$this->displayResultsInfos = $valeur;
	}

	/**
	 * Définit si le template doit charger le fichier CSS par défaut et appliquer le style du template par déaut
	 * Si vous souhaitez personnaliser le style de votre liste vous devriez desactiver cette option et inclure votre propre fichier CSS
	 * Cette méthode désactive aussi l'option des titres fixés lorsque l'tuilisateur scroll, car sans le CSS par défaut cette seconde option peut créer des résultats inattendus.
	 * Si toutesfois vous ne souhaitez pas désactiver cette option utilisez la méthode *fixTitles(true)* pour la réactiver.
	 * @param bool $valeur false pour desactiver, true pour activer
	 * @return bool false si l'argument n'est pas un booleen
	 */
	public function applyDefaultCSS($valeur) {
		if(!is_bool($valeur))
			return false;

		$this->applyDefaultCSS = $valeur;
		if(!$valeur)
			$this->fixTitles($valeur);
	}

	/**
	 * Définit la taille maximale des champs de saisie pour la recherche
	 * @param int $valeur la nouvelle taille maximale des champs de saisie pour al recherche
	 * @return bool false si l'argument est incorrect (pas un int, infèrieur à 0)
	 */
	public function setMaxSizeInputs($valeur) {
		if($valeur != intval($valeur) || $valeur <= 0)
			return false;

		$this->maxSizeInputs = $valeur;
	}

	/**
	 * Définit sui les titres de votre liste restent fixés en haut de l'écran lorsque l'utilisateur scroll sur la page.
	 * @var bool valeur true pour activer false pour désactiver cette option
	 * @return bool false si l'arguemnt n'est pas un booléen.
	 */
	public function fixTitles($valeur) {
		if(!is_bool($valeur))
			return false;
		$this->fixedTitles = $valeur;
	}

	/**
	 * @return bool true si le tableau de pagination sera affiché, false sinon
	 */
	public function issetPaging() {
		return $this->pagingLinksNb != false;
	}

			/*-****************
			***   PRIVATE   ***
			******************/

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
