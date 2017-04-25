<?php


/************************************************************************************************
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

class ListTemplate {
	
	
			/********************
			***   ATTRIBUTS   ***
			********************/
	
	/**
	 * @var string $id : l'id du tableau HTML
	 */
	private $id;
	/**
	 * Varaibles contenatn les classes HTML appliquees aux lignes paires / impaires
	 * @var string $classe1 : classe des lignes imparaires
	 * @var string $classe2 : classe des lignes paires
	 */
	private $class1, $class2;
	/**
	* @var boolean $activerRecherche : specifie si la fonction recherche est diponible ou non
	*/
	private $enableSearch;
	/**
	* @var boolean $recherche : specifie si les champs de saisie pour la recherche sont visibles ou non
	*/
	private $displaySearch;
	/**
	* @var string $messageListeVide le message qui sera affiche si la liste ne contient pas de donnees
	*/
	private $emptyListMessage;
	/**
	* @var string $classeErreur : le nom de la classe HTML des balises p qui contiendront le message d'erreur
	*/
	private $errorClass;
	/**
	* @var int $nbResultatsParPage : nombre de resultats affiches par page. Valeur par defaut = 100
	*/
	private $nbResultsPerPage;
    /**
    * @var int $pageActuelle : numero de la page de resultats actuelle
    */
	private $currentPage;
	/**
	* @var string $callbackCellule : nom du callback a appeler lors de l'affichage d'une cellule
	*/
	private $cellCallback;
	/**
	*
	*/
	private $useCache;
	/**
	*
	*/
	private $searchFormID;
	

	public static $CLASSE1 = 'gris';
	public static $CLASSE2 = 'orange';
	
	const MAX_LEN_INPUT = 30;
	const NB_PAGES_MAX = 10;
	
	
			/***********************
			***   CONSTRUCTEUR   ***
			***********************/
	
	public function __construct($id='liste', $classe1=null, $classe2=null){
		$this->id = $id;
		$this->class1 = (($classe1 == null)? self::$CLASSE1 : $classe1);
		$this->class2 = (($classe2 == null)? self::$CLASSE2 : $classe2);
		$this->enableSearch = true;
		$this->displaySearch = false;
		$this->emptyListMessage = "Aucun resultat!";
		$this->errorClass = 'erreur';
		$this->currentPage = ((isset($_GET['page']) && $_GET['page'] > 0) ? $_GET['page'] : 1 );
		$this->nbResultsPerPage = 50;
		$this->cellCallback = null;
		$this->useCache = false;
		$this->searchFormID = 'recherche';
	}
	
	
			/*******************
			***   METHODES   ***
			*******************/

	/**
	 * Construit une liste HTML avec le tableau de donnees passe en parametres
	 * @param RequestResponse $reponse contenant l'ensemble des resultats de la requete
	 * @return string : le code HTML de la liste HTML genere
	 */
	public function construct(RequestResponse $reponse){

		// On teste d'abord s'il y a erreur dans la reponse
		if($reponse->error()){
			return self::messageHTML($reponse->getErrorMessage(),
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
		$fin = ($this->currentPage) * $this->nbResultsPerPage;
		$ret .= self::messageHTML("Lignes : $debut - $fin / $nbLignes", null);
		
		//Ajout des boutons options sur le cete
		$ret .= "\n<div id='boutons-options'>";

		//Bouton quest (recherche)
		if($this->enableSearch){
			$ret .= '<a href="'.self::creerUrlGET('quest', 
				( ($this->displaySearch) ? 0 : 1)).'">?</a>'; 
			
			// Ajout du form si recherche activee
			if($this->displaySearch)
				$ret .= "\n<form id='$this->searchFormID' method='GET'"
					.'\'><input type="submit" value="Go!"/></form>';
		}
		// Bouton excel
		// TODO

		// Bouton pour reset le mask
		if(isset($_GET['mask']) && strlen($_GET['mask']) > 0) {
			$ret .= '<a id="annuler-masque" href="'
				.self::creerUrlGET('mask', '').'">M</a>';
		}
		$ret .= "<div>\n";


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

			//Gestion du masque
			if(isset($_GET['mask'])){
				$maskArray = explode(',', $_GET['mask']);
				array_push($maskArray, intval($i));
			}
			else {
				$maskArray = array($i+1);
			}
			
			$maskString = implode(',', array_unique($maskArray));
			
			// Ajustements du orderBy du au masque
			if(isset($_GET['orderBy'])) {
				$orderArray = explode(',', $_GET['orderBy']);
				asort($maskArray);
				foreach($maskArray as $numMask) {
					foreach ($orderArray as &$numOrder) {
						if(abs(intval($numOrder)) == $numMask) {
							unset($numOrder);
						}
						else if (abs($numOrder) > $numMask) {
							if($numOrder > 0) $numOrder--;
							else if($numOrder < 0) $numOrder++;
						}
					}
				}
				$nouvGET = $_GET;
				$nouvGET['orderBy'] = implode(',', array_unique($orderArray));
			}

			$lienMasque = '<a class="masque" href="'
				.self::creerUrlGET('mask', $maskString, ((isset($nouvGET))? $nouvGET : null))
				.'">x</a>';

			// Affiche les liens et les titres
			$ret .= '<th>'.$lienMasque.$lienOrderBy.'</th>';
			$i++;
		}
		$ret .= "</tr>\n";

		//Affichage des champs de saisie pour la  recherche
		if($this->enableSearch && $this->displaySearch){
			$ret .= "<tr>";
			$types = $reponse->getColumnsType();
			for ($i=0; $i < count($titres); $i++) {
				//Determine le contenu du champs
				$valeur = (isset($_GET['tabSelect'][$titres[$i]])? 
					$_GET['tabSelect'][$titres[$i]] : null);
				//Determine la taille du champs
				$taille = min($types[$i]->len, self::MAX_LEN_INPUT);
				$ret .= '<td><input type="text" name="tabSelect['.$titres[$i].']"'
					." form='$this->searchFormID' size='$taille' value='$valeur'/></td>";
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
						$cellule = $fct($cellule, $titres[$j], $i);
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
			$nbPages = ($nbLignes / $this->nbResultsPerPage);

			// S'il y a plus de pages que la limite affichable
			if($nbPages > self::NB_PAGES_MAX){
				$debut = $this->currentPage - 2;
				if($debut < 1){
					$debut = 1;
					$fin = self::NB_PAGES_MAX - 1;
				}
				// Ajout de la 1re page si besoin
				else {
					$ret .= '<td><a href="'.self::creerUrlGET('page', 1).'">&lt;&lt;</td>';
					$fin = min($debut + self::NB_PAGES_MAX, $nbPages);
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
		// Fin
		return $ret;
	}
	
	
			/****************************
			***   SETTERS & GETTERS   ***
			****************************/
	
	/**
	 * Attribue un nouvel id HTML a la liste.
	 * @param string $id l'id HTML du tableau
	 */
	public function setId($id){
		$this->id = $id;
	}

	/**
	* Definit le message d'erreur a afficher si aucun resultat n'est retournee par la requete 
	* @param string $message le nouveau message a definir
	*/
	public function setEmptyListMessage($message){
		$this->emptyListMessage = $message;
	}

	/**
	* Definit le nom de la class HTML des messages d'erreurs affiches
	* @param string $classe le nouveau nom de la classe des messages d'erreur
	*/
	public function setErrorMessageClass($classe){
		$this->errorClass = $classe;
	}

	/**
	 * Determine si le template doit afficher la ligne des champs de saisie pour recherche dans la liste
	 * @param boolean $valeur, valeur par defaut : false
	 */
	public function displaySearchInputs($valeur){
		if(is_bool($valeur))
			$this->displaySearch = $valeur;
	}

	/**
	 * Determine si la fonction recherche par colonne doit etre activee ou non pour cette liste
	 * @param boolean $valeur, valeur par defaut : true
	 */
	public function enableSearch($valeur){
		if(is_bool($valeur))
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
	* Definit le nombre de resultats a afficher sur une page. Valeur par defaut = 50
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
	* Definit le callback (la fonction) qui sera executee lors de l'affichage des donnees
	* dans les cellules du tableau. Cette fonction doit etre definie comme il suit :
	* 	-> 3 parametres d'entree 
	* 			* element : la valeur de l'element en cours
	* 			* colonne : le nom de la colonne en cours
	* 			* ligne   : le numero de la ligne en cours
	* 	-> valeur de retour de type string (ou du moins affichable via echo)
	* @param string $fonction : le nom du callback a utiliser, null si aucun.
	* Valeur par defaut : null
	*/
	public function setCellCallback($fonction=null){
		if($fonction != null && function_exists($fonction))
			$this->cellCallback = $fonction;
		else 
			$this->cellCallback = null;
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
	*
	*/
	public function setSearchFormID($valeur) {
		if(strlen($valeur) == 0)
			$this->searchFormID = null;
		else
			$this->searchFormID = $valeur;
	}

			/******************
			***   PRIVATE   ***
			******************/

	private static function messageHTML($message, $nom, $balise='p', $id=false){
		return '<'.$balise.(($nom == null)? '' : (($id)?' id="' : ' class="').$nom.'"' )
			.'>'.$message.'</'.$balise.'>';
	}

	private static function creerUrlGET($nom, $val, $get=null){
		if($get == null)
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