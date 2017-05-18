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
 * * Activer / desactiver / modifier l'id du tableau HTML, ainsi que les classes des lignes paires / impaires
 * * Modifier la classe et le message des erreurs
 * * Afficher / Masquer les champs de saisi pour les filtres
 * * Modifier le nombre de lignes par page
 * * Modifier le nombre de pages à afficher dans la pagination
 * * Utiliser un callback pour modifier le contenu des cellules
 * * Utiliser le système de cache pour accélérer la navigation entre les pages
 * 
 * @author RookieRed
 *
 */
class ListTemplate {
	
	
			/*-******************
			***   ATTRIBUTS   ***
			********************/
	
	/**
	 * @var string l'id du tableau HTML
	 */
	private $idTable;
	/**
	 * Varaibles contenatn les classes HTML appliquees aux lignes paires / impaires
	 * @var string classe des lignes imparaires
	 * @var string classe des lignes paires
	 */
	private $class1, $class2;
	/**
	 * @var string $emptyListMessage le message qui sera affiche si la liste ne contient pas de donnees
	 */
	private $emptyListMessage;
	/**
	 * @var array $mask tableau contenant le nom des colonnes qui seront masquées lors de la construction de la liste HTML 
	 */
	private $mask;
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
	 * @var array tableau associatif pour l'affichage des titres des colonnes. Ce tableau à pour format [titre_colonne] => [titre_a_afficher]
	 */
	private $listTitles;
	/**
	 * @var string nom du callback a appeler lors de l'affichage d'une cellule (balises 'td')
	 */
	private $cellCallback;
	/**
	 * @var bool définit si le callback de cellule réécrit les balises TD des cellules ou non. Passez cet attribut à faux pour ajouter manuellement des attributs aux balises td
	 */
	private $replaceTagTD;
	/**
	 * @var string $rowCallback nom du callback a appeler lors de l'affichage des des lignes (balises 'tr')
	 */
	private $rowCallback;
	/**
	 * @var string $columnCallback nom du callback qui servira à ajouter des colonnes à la liste 
	 */
	private $columnCallback;
	/**
	 * @var boolean definit l'utilisation du système de cache pour les requêtes lourdes
	 */
	private $useCache;
	/**
	 * @var bool $enableExcel définit si ListTemplate propose la fonctionnalité d'export Excel
	 */
	private $enableExcel;
	/**
	 * @var bool $enableSearch définit si ListTemplate propose la fonctionnalité de recherche par colonnes
	 */
	private $enableSearch;
	/**
	 * @var bool $enableOrderBy définit si ListTemplate propose la fonctionnalité de tri par colonne
	 */
	private $enableOrderBy;
	/**
	 * @var bool $enableMask définit si ListTemplate propose le masquage des colonnes
	 */
	private $enableMask;
	/**
	 * @var int nombre de liens de page à afficher au maximum dans la pagination
	 */
	private $maxPagesDisplayed;
	/**
	 * @var string $helpLink lien vers la page d'aide associée à cette liste
	 */
	private $helpLink;
	/**
	 * @var bool $displayNbResults définit si ListTemplate affiche ou non le nombre de résultats total retournée par la requete
	 */
	private $displayNbResults;
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
	 * @var string classe par défaut des lignes impaires du tableau
	 * @var string classe par défaut des lignes paires du tableau
	 */
	public static $CLASSE1 = 'bleu-fonce', $CLASSE2 = 'bleu-clair';
	
	
	
			/*-*********************
			***   CONSTRUCTEUR   ***
			***********************/
	
	/**
	 * Construit un objet ListTemplate et lui assigne son comportemetn par défaut
	 */
	 public function __construct(){
	 	// [!] => si vous changez l'id de la liste pensez à le mettre à jour dans le fichier listManager.js
		$this->id = 'liste';
		$this->class1 = self::$CLASSE1;
		$this->class2 = self::$CLASSE2;
		$this->enableSearch = true;
		$this->enableOrderBy = true;
		$this->enableExcel = true;
		$this->enableMask = true;
		$this->mask = array();
		$this->emptyListMessage = "Aucun resultat!";
		$this->errorClass = 'erreur';
		$this->currentPage = ((isset($_GET['lm_page']) && $_GET['lm_page'] > 0) ? $_GET['lm_page'] : 1 );
		$this->nbResultsPerPage = 50;
		$this->maxPagesDisplayed = 10;
		$this->listTitles = array();
		$this->cellCallback = null;
		$this->replaceTagTD = false;
		$this->rowCallback = null;
		$this->columnCallback = null;
		$this->useCache = false;
		$this->helpLink = null;
		$this->displayNbResults = true;
		$this->applyDefaultCSS = true;
		$this->maxSizeInputs = 30;
		$this->fixedTitles = true;
	}
	
	
			/*-*****************
			***   METHODES   ***
			*******************/

	/**
	 * Construit une liste HTML à partir d'un objet RequestResponse.
	 * Il s'agit de la fonction principale de la classe. L'obje génère un template à partir des valeurs de ses attributs et contenant le résultat de la requqete apssée en paramètre.
	 * Le template se découpe en 4 parties :
	 * * *Tout en haut* : le nombre de résultat de la page en cours sur le nombre de résultats total
	 * * *Tout à gauche* : les boutons d'options permettant d'activer certaines fonctionnalitées
	 * * *Au centre* : la liste de données
	 * * *En bas* : la pagination et les liens vers les autres pages de la liste
	 * @param RequestResponse $reponse contenant l'ensemble des resultats de la requete
	 * @return string le code HTML de la liste HTML genere
	 */
	public function construct(RequestResponse $reponse){

		// On teste d'abord s'il y a erreur dans la reponse
		if($reponse->error()){
			$ret = "<div id='boutons-options'><a href='".self::creerUrlGET(null, null, array())."'>Clear</a></div>";
			return $ret.self::messageHTML($reponse->getErrorMessage(),
				$this->errorClass);
		}

		// Preparation de l'array a afficher
		$donnees =  array();
		while(($ligne = $reponse->nextLine()) != null)
			$donnees[] = $ligne;
		$nbLignes = $reponse->getRowsCount();
		$debut = ($this->currentPage-1) * $this->nbResultsPerPage;
		$titres = $reponse->getColumnsName();

		// Enregistrement des donnees dans le cache
		// if($this->useCache && $nbLignes > Cache::NB_LIGNES_MIN){
		// 	$cacheID = md5(uniqid());
		// 	$cache = new Cache($cacheID);
		// 	$cache->write($reponse, $this->nbResultsPerPage);
		// }

		// $donnees ne contient plus que les valeurs a afficher
		$donnees = array_slice($donnees, $debut, $this->nbResultsPerPage);

		// Creation de la div HTML parente
		$ret = "\n".'<div id="liste-parent">';

		//Affichage du nombre de resultats
		$debut++;
		$fin = min(($this->currentPage) * $this->nbResultsPerPage, $nbLignes);
		if($this->displayNbResults)
			$ret .= self::messageHTML("Lignes : $debut - $fin / $nbLignes", null);
		
		//Ajout des boutons options sur le cete
		$ret .= "\n<div><div id='boutons-options'>";
		
		// Bouton pour reset le mask en JS
		if($this->enableMask)
			$ret .= '<a id="annuler-masque" href="#"><img height="40" width="40" src="'.LM_IMG.'mask-cross.png"></a>';

		// Bouton excel
		if($this->enableExcel){
			$ret .= '<a href="'.self::creerUrlGET('lm_excel', 1).'" id="btn-excel"><img height="40" width="40" src="'.LM_IMG.'excel-ico.png"></a>';
		}

		//Bouton quest (recherche)
		if($this->enableSearch){
			$ret .= '<a id="btn-recherche" href="#"><img height="40" width="40" src="'.LM_IMG.'search-ico.png"></a>'; 
			
			// Ajout du form si recherche activee
			$ret .= "\n<form id='recherche' action='' method='GET'"
				.'><input type="submit" value="Go!"/>';

			// Ajout des paramètres GET déjà présents
			foreach ($_GET as $nom => $valeur) {
				if($nom != 'lm_tabSelect' && !is_array($valeur)) {
					$ret .= "<input type='hidden' name='$nom' value='$valeur'/>";
				}
			}

			$ret .= '</form>';
		}

		// Lien veers la rubrique d'aide / légende associée
		if($this->helpLink != null){
			$ret .= "<a href='$this->helpLink' target='_blank' id='btn-help'><img height='40' width='40' src='".LM_IMG."book-ico.png'></a>";
		}

		//Bouton RaZ
		if(isset($_GET['lm_tabSelect']) || isset($_GET['lm_orderBy'])) {
			$tabGet = $_GET;
			
			if(isset($_GET['lm_tabSelect']))
				unset($tabGet['lm_tabSelect']);
			if(isset($_GET['lm_orderBy']))
				unset($tabGet['lm_orderBy']);
			
			$ret .= '<a href="'.self::creerUrlGET(null, null, $tabGet).'"><img height="40" width="40" src="'.LM_IMG.'eraser-ico.png"></a>';
		}

		$ret .= "</div>\n";

		// Initialisation de la liste
		$ret .= '<div><table'.(($this->fixedTitles)? ' fixed-titles="true"' : '')
			.(($this->id == null)?'' : " id='$this->id' ").'>'."\n<tr id='ligne-titres'>";

		//Creation des titres
		$i = 0;
		foreach ($titres as $titre) {

			// On vérifie que la colonne en cours n'est aps masquée
			if(!in_array($titre, $this->mask)) {

				//Gestion du order by
				$signeOrder = '';
				if(isset($_GET['lm_orderBy'])){
					$orderArray = explode(',', $_GET['lm_orderBy']);

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
				if(isset($this->listTitles[$titre]))
					$titreAffiche = $this->listTitles[$titre];
				else 
					$titreAffiche = $titre;

				// Création du lien pour order by
				if($this->enableOrderBy)
					$lienOrderBy = '<a class="titre-colonne" href="'
						.self::creerUrlGET('lm_orderBy', $orderString)."\">$titreAffiche</a><br>$signeOrder";
				else
					$lienOrderBy = $titreAffiche;

				if($this->enableMask)
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
		if($this->enableSearch){
			$ret .= "<tr class='tabSelect'>";
			$types = $reponse->getColumnsType();
			for ($i=0; $i < count($titres); $i++) {

				// On vérifie que la colonne en cours n'est aps masquée
				if(!in_array($titres[$i], $this->mask)) {

					//Determine le contenu du champs
					$valeur = (isset($_GET['lm_tabSelect'][$titres[$i]])? 
						$_GET['lm_tabSelect'][$titres[$i]] : null);
					//Determine la taille du champs
					$taille = min($types[$i]->len, $this->maxSizeInputs);
					$ret .= '<td><input type="text" name="lm_tabSelect['.$titres[$i].']"'
						." form='recherche' size='$taille' value='$valeur'/></td>";
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
					if(!in_array($titres[$j], $this->mask)) {

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
		if($nbLignes > $this->nbResultsPerPage && $this->maxPagesDisplayed != false){
			$ret .= '<div id="pagination"><table align="center"><tr>';
			$nbPages = (is_int($nbPages = ($nbLignes / $this->nbResultsPerPage))? $nbPages : round($nbPages + 0.5) );

			// S'il y a plus de pages que la limite affichable
			if($nbPages > $this->maxPagesDisplayed){
				$debut = $this->currentPage - intval($this->maxPagesDisplayed / 2);
				if($debut <= 1){
					$debut = 1;
					$fin = $this->maxPagesDisplayed + 1;
				}
				// Ajout de la 1re page si besoin
				else {
					$ret .= '<td><a href="'.self::creerUrlGET('lm_page', 1).'">&lt;&lt;</td>';
					$fin = min($debut + $this->maxPagesDisplayed, $nbPages);
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
					$ret .= '<a href="'.self::creerUrlGET('lm_page', $i).'">'.$i.'</a>';
				}
				$ret .= '</td>';
			}
			// Ajout du lien vers la derniere page si besoin
			if($fin != $nbPages){
				$ret .= '<td><a href="'.self::creerUrlGET('lm_page', $nbPages).'">&gt;&gt;</td>';
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
	 * Définit l'id HTML de la balise table correspondant à la liste. 
	 * / ! \ Attention si vous changez l'id du tableau il se peut que le fichier JS associé ne fonctionne plus et que certaines telles que le masquage des colonnes ne soient plus possibles
	 * @param string $id le nouvel id du tableau. Si null aucun ID ne sera affiché.
	 */
	public function setIdTable($id) {
		$this->id = $id;
	}

	/**
	 * Définit le nouveau masque à appliquer. =
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
	 * Definit le nom de la class HTML des messages d'erreurs affiches
	 * @param string|null $classe le nouveau nom de la classe des messages d'erreur. Si null pas de classe affichée.
	 */
	public function setErrorMessageClass($classe){
		$this->errorClass = $classe;
	}

	/**
	 * Active / desactive la fonction recherche par colonne pour cette liste
	 * @param boolean $valeur la nouvele valeur pour ce paramètre, valeur par defaut true
	 * @return boolean false si le paramètre netré n'est pas un boolean.
	 */
	public function enableSearch($valeur){
		if(!is_bool($valeur))
			return false;
		
		$this->enableSearch = $valeur;
	}

	/**
	 * Active / desactive la fonction de masquage de colonne en JS
	 * @param boolean $valeur la nouvele valeur pour ce paramètre, valeur par defaut true
	 * @return boolean false si le paramètre netré n'est pas un boolean.
	 */
	public function enableMask($valeur){
		if(!is_bool($valeur))
			return false;
		
		$this->enableMask = $valeur;
	}

	/**
	 * Permet de changer les titres des colonnes de la liste
	 * Le tableau à passer en paramètre est un tableau associatif où la clé correspond au nom de la colonne tel qu'il est restitué lors de la selection des données, associé au titre que vous souhaitez afficher
	 * @param array le tableau des nouveaux titres
	 * @return bool false si l'argument apssé n'est pas un tableau 
	 */
	public function setListTitles($array) {
		if(!is_array($array))
			return false;

		$this->listTitles = $array;
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
     * @return boolean faux si la valeur entree est incorrecte
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
	 * @return boolean faux si la valeur entree est incorrecte
	 */
	public function setCurrentPage($numeroPage){
		if(!is_int($valeur) || $numeroPage <= 0)
			return false;

		$this->currentPage = $numeroPage;
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
	 * Ce callback sera appelé par le template à la fin de la création des titres ET a la fnc de la création de chaque ligne de la liste. La fonction doit correspondre au format suivant
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

	/**
	 * Active ou désactive l'utilisation du cache pour les requêtes retournant un grand nombre de données
	 * @param boolean $valeur : true pour activer le système de cache, faux sinon
	 */
	public function enableCache($valeur){
		if(!is_bool($valeur))
			return false;

		$this->useCache = $valeur;
	}

	/**
	 * Définit le nombre de liens max à afficher dans la pagination.
	 * @param int $valeur le nombre de liens max à afficher.
	 */
	public function setMaxPagesDisplayed($valeur){
		if(intval($valeur) < 0)
			return false;

		$this->maxPagesDisplayed = $valeur;
	}

	/**
	 * Définit si ListTemplate doit proposer l'export de données en format excel à l'utilisateur. Valeur apr défaut : true
	 * @param bool $valeur : la nouvelle valeur à appliquer
	 * @return bool false si le paramètre n'est aps un booléen
	 */
	public function enableExcel($valeur){
		if(!is_bool($valeur))
			return false;

		$this->enableExcel = $valeur;
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
	 * @return bool true si la fonctionnalité d'export Excel est activée, false sinon
	 */
	public function excelIsEnabled() {
		return $this->enableExcel;
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
	public function displayNbResults($valeur) {
		if(!is_bool($valeur))
			return false;

		$this->displayNbResults = $valeur;
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

			/*-****************
			***   PRIVATE   ***
			******************/

	private static function messageHTML($message, $nom, $balise='p', $id=false){
		return '<'.$balise.(($nom == null)? '' : (($id)?' id="' : ' class="').$nom.'"' )
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