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
 * @author celoundou
 *
 */
class ListTemplate {
	
	
			/*-******************
			***   ATTRIBUTS   ***
			********************/
	
	/**
	 * @var string l'id du tableau HTML
	 */
	private $id;
	/**
	 * Varaibles contenatn les classes HTML appliquees aux lignes paires / impaires
	 * @var string classe des lignes imparaires
	 * @var string classe des lignes paires
	 */
	private $class1, $class2;
	/**
	 * @var boolean specifie si la fonction recherche est diponible ou non
	 */
	private $enableSearch;
	/**
	 * @var string le message qui sera affiche si la liste ne contient pas de donnees
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
	 * @var string nom du callback a appeler lors de l'affichage d'une cellule
	 */
	private $cellCallback;
	/**
	 * @var boolean definit l'utilisation du système de cache pour les requêtes lourdes
	 */
	private $useCache;
	/**
	 *
	 */
	private $enableExcel;
	/**
	 * @var int nombre de liens de page à afficher au maximum dans la pagination
	 */
	private $maxPagesDisplayed;
	
	/**
	 * @var string classe par défaut des lignes impaires du tableau
	 * @var string classe par défaut des lignes paires du tableau
	 */
	public static $CLASSE1 = 'gris', $CLASSE2 = 'orange';
	
	/**
	 * @var integer longueur maximale des champs de saisie pour la recherche par colonne
	 */
	const MAX_LEN_INPUT = 30;
	
	
			/*-*********************
			***   CONSTRUCTEUR   ***
			***********************/
	
	/**
	 * Construit un objet ListTemplate et lui assigne son comportemetn par défaut
	 * @param string $classe1 (facultatif) la classe à appliquer aux lignes paires. Si null : prend la valeur de self::$CLASSE1
	 * @param string $classe2 (facultatif) la classe à appliquer aux lignes impaires. Si null : prend la valeur de self::$CLASSE2
	 */
	 public function __construct($classe1=null, $classe2=null){
	 	// [!] => si vous changez l'id de la liste pensez à le mettre à jour dans le fichier listManager.js
		$this->id = 'liste';
		$this->class1 = (($classe1 == null)? self::$CLASSE1 : $classe1);
		$this->class2 = (($classe2 == null)? self::$CLASSE2 : $classe2);
		$this->enableSearch = true;
		$this->emptyListMessage = "Aucun resultat!";
		$this->errorClass = 'erreur';
		$this->currentPage = ((isset($_GET['page']) && $_GET['page'] > 0) ? $_GET['page'] : 1 );
		$this->nbResultsPerPage = 50;
		$this->maxPagesDisplayed = 10;
		$this->cellCallback = null;
		$this->useCache = false;
		$this->enableExcel = true;
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
		$donnees = $reponse->dataList();
		$nbLignes = $reponse->getRowsCount();
		$debut = ($this->currentPage-1) * $this->nbResultsPerPage;
		$titres = $reponse->getColumnsName();

		// Enregistrement des donnees dans le cache
		if($this->useCache && $nbLignes > Cache::NB_LIGNES_MIN){
			$cacheID = md5(uniqid());
			$cache = new Cache($cacheID);
			$cache->write($reponse, $this->nbResultsPerPage);
		}

		// $donnees ne contient plus que les valeurs a afficher
		$donnees = array_slice($donnees, $debut, $this->nbResultsPerPage);


		// Creation de la div HTML parente
		$ret = "\n".'<div class="liste-parent">';

		//Affichage du nombre de resultats
		$debut++;
		$fin = min(($this->currentPage) * $this->nbResultsPerPage, $nbLignes);
		$ret .= self::messageHTML("Lignes : $debut - $fin / $nbLignes", null);
		
		//Ajout des boutons options sur le cete
		$ret .= "\n<div id='boutons-options'>";

		// Bouton pour reset le mask
		$ret .= '<a id="annuler-masque" href="#">Annuler masque</a>';

		// Bouton excel
		if($this->enableExcel){
			$ret .= '<br><a href="'.self::creerUrlGET('excel', 1).'" id="lien-excel">Excel</a>';
		}

		//Bouton quest (recherche)
		if($this->enableSearch){
			$ret .= '<br><a class="recherche" href="#">Rechercher</a>'; 
			
			// Ajout du form si recherche activee
			$ret .= "\n<form id='recherche' action='' method='GET'"
				.'><input type="submit" value="Go!"/></form>';
		}

		$ret .= "<div>\n";

		//Bouton RaZ
		if(isset($_GET['tabSelect'])) {
			$tabGet = $_GET;
			unset($tabGet['tabSelect']);
			$ret .= '<a href="'.self::creerUrlGET(null, null, $tabGet).'">RaZ</a>';
		}


		// Initialisation de la liste
		$ret .= '<table'.(($this->id == null)?'' : " id ='$this->id' ").'>'."\n<tr>";

		//Creation des titres
		$i = 0;
		foreach ($titres as $titre) {

			//Gestion du order by
			if(isset($_GET['orderBy'])){
				$orderArray = explode(',', $_GET['orderBy']);

				// Construction de la chaine orderBy
				if(($key = array_search(($i + 1), $orderArray)) !== false ) {
					unset($orderArray[$key]);
					array_unshift($orderArray, -1*($i + 1));
				}
				else if (($key = array_search(-1 * ($i + 1), $orderArray)) !== false ){
					unset($orderArray[$key]);
					array_unshift($orderArray, $i + 1);
				}
				else {
					array_unshift($orderArray, ($i + 1));
				}
				$orderString = implode(',', $orderArray);
			}
			else {
				$orderString = $i+1;
			}
			$lienOrderBy = '<a class="titre-colonne" href="'
				.self::creerUrlGET('orderBy', $orderString)."\">$titre</a>";

			$lienMasque = '<a class="masque" href="#">x</a>';

			// Affiche les liens et les titres
			$ret .= '<th>'.$lienMasque.$lienOrderBy."</th>\n";
			$i++;
		}
		$ret .= "</tr>\n";

		//Affichage des champs de saisie pour la  recherche
		if($this->enableSearch){
			$ret .= "<tr class='tabSelect'>";
			$types = $reponse->getColumnsType();
			for ($i=0; $i < count($titres); $i++) {
				//Determine le contenu du champs
				$valeur = (isset($_GET['tabSelect'][$titres[$i]])? 
					$_GET['tabSelect'][$titres[$i]] : null);
				//Determine la taille du champs
				$taille = min($types[$i]->len, self::MAX_LEN_INPUT);
				$ret .= '<td><input type="text" name="tabSelect['.$titres[$i].']"'
					." form='recherche' size='$taille' value='$valeur'/></td>";
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
				$ret .= '<tr'.(($classe == null)? '' : " class='$classe' ").'>';

				//Construction des cellules
				$j = 0;
				foreach ($ligne as $cellule){
					// Application du callback (si non null)
					if($this->cellCallback != null) {
						$fct = $this->cellCallback;
						$cellule = ( (($retFCT = $fct($cellule, $titres[$j], $i)) == null)? $cellule : $retFCT ) ;
					}
					// Si la cellule ne contient rien -> '-'
					if(strlen($cellule) == 0)
						$cellule = '-';
					$ret .= '<td>'.$cellule.'</td>';
					$j++;
				}
				$ret .= "</tr>\n";
				$i++;
			}
			$ret .= "</table>\n";
		}

		// Affichage du tableau des numeros de page
		if($nbLignes > $this->nbResultsPerPage){
			$ret .= '<table id="pagination"><tr>';
			$nbPages = (is_int($nbPages = ($nbLignes / $this->nbResultsPerPage))? $nbPages : round($nbPages + 0.5) );

			// S'il y a plus de pages que la limite affichable
			if($nbPages > $this->maxPagesDisplayed){
				$debut = $this->currentPage - intval($this->maxPagesDisplayed / 2);
				if($debut < 1){
					$debut = 1;
					$fin = $this->maxPagesDisplayed + 1;
				}
				// Ajout de la 1re page si besoin
				else {
					$ret .= '<td><a href="'.self::creerUrlGET('page', 1).'">&lt;&lt;</td>';
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
					$ret .= '<a href="'.self::creerUrlGET('page', $i).'">'.$i.'</a>';
				}
				$ret .= '</td>';
			}
			// Ajout du lien vers la derniere page si besoin
			if($fin != $nbPages){
				$ret .= '<td><a href="'.self::creerUrlGET('page', $nbPages).'">&gt;&gt;</td>';
			}

			$ret .= "</tr></table>\n</div>\n";
		}

		// Ajout des scripts
		$ret .= '<script type="text/javascript" src="'.LM_JS.'jquery-3.2.1.min.js"></script>'
			."\n".'<script type="text/javascript" src="'.LM_JS.'listeManager.js"></script>'."\n";

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
	 * Définir un callback à utiliser dans le template.
	 * Definit le callback (la fonction) qui sera executee pour chaque valeur lors de l'affichage des donnees dans les cellules du tableau. Cette fonction doit etre definie comme il suit :
	 * * 3 parametres d'entree :
	 *    1. cellule : la valeur de l'element en cours
	 *    2. colonne : le nom de la colonne en cours
	 *    3. ligne   : le numero de la ligne en cours
	 * * valeur de retour de type string (ou du moins un type qui peut être transformé en string). [!] Si vous ne modifiez pas la valeur de la cellule penser tout de même à la retourner
	 * @param string|null $fonction le nom du callback a utiliser, null si aucun. Valeur par defaut : null
	 * @return boolean true si l'opération s'est bien déroulée et que la fonction existe false sinon (renvoie false si le paramètre est null)
	 */
	public function setCellCallback($fonction=null){
		if($fonction != null && function_exists($fonction)){
			$this->cellCallback = $fonction;
			return true;
		}
		else {
			$this->cellCallback = null;
			return false;
		}
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
		if($valeur != intval($valeur))
			return false;

		$this->maxPagesDisplayed = $valeur;
	}

	/**
	 *
	 */
	public function ennableExcel($valeur){
		if(!is_bool($valeur))
			return false;

		$this->enableExcel = $valeur;
	}

	/**
	 *
	 */
	public function excelIsEnabled() {
		return $this->enableExcel;
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