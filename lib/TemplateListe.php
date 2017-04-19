<?php


/************************************************************************************************
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

class TemplateListe {
	
	
			/********************
			***   ATTRIBUTS   ***
			********************/
	
	/**
	 * @var string $id : l'id du tableau HTML
	 */
	private $id;
	/**
	 * Varaibles contenatn les classes HTML appliquées aux lignes paires / impaires
	 * @var string $classe1 : classe des lignes imparaires
	 * @var string $classe2 : classe des lignes paires
	 */
	private $classe1, $classe2;
	/**
	* @var boolean $activerRecherche : spécifie si la fonction recherche est diponible ou non
	*/
	private $activerRecherche;
	/**
	* @var boolean $recherche : spécifie si les champs de saisie pour la recherche sont visibles ou non
	*/
	private $afficherRecherche;
	/**
	* @var string $messageListeVide le message qui sera affiché si la liste ne contient pas de données
	*/
	private $messageListeVide;
	/**
	* @var string $classeErreur : le nom de la classe HTML des balises p qui contiendront le message d'erreur
	*/
	private $classeErreur;
	/**
	* @var int $nbResultatsParPage : nombre de résultats affichés par page. Valeur par défaut = 100
	*/
	private $nbResultatsParPage;
    /**
    * @var int $pageActuelle : numéro de la page de résultats actuelle
    */
	private $pageActuelle;
	/**
	* @var string $callbackCellule : nom du callback à appeler lors de l'affichage d'une cellule
	*/
	private $callbackCellule;
	

	public static $CLASSE1 = 'gris';
	public static $CLASSE2 = 'orange';
	
	const MAX_LEN_INPUT = 30;
	const NB_PAGES_MAX = 10;
	
	
			/***********************
			***   CONSTRUCTEUR   ***
			***********************/
	
	public function __construct($id=null, $classe1=null, $classe2=null){
		$this->id = $id;
		$this->classe1 = (($classe1 == null)? self::$CLASSE1 : $classe1);
		$this->classe2 = (($classe2 == null)? self::$CLASSE2 : $classe2);
		$this->activerRecherche = true;
		$this->afficherRecherche = false;
		$this->messageListeVide = "Aucun résultat!";
		$this->classeErreur = 'erreur';
		$this->pageActuelle = ((isset($_GET['page']) && $_GET['page'] > 0) ? $_GET['page'] : 1 );
		$this->nbResultatsParPage = 50;
		$this->callbackCellule = null;
	}
	
	
			/*******************
			***   METHODES   ***
			*******************/

	/**
	 * Construit une liste HTML avec le tableau de données passé en paramètres
	 * @param ReponseRequete $reponse contenant l'ensemble des résultats de la requete
	 * @return string : le code HTML de la liste HTML généré
	 */
	public function construireListe(ReponseRequete $reponse){

		// On teste d'abord s'il y a erreur dans la réponse
		if($reponse->erreur()){
			return self::messageHTML($reponse->getMessageErreur(),
				$this->classeErreur);
		}

		// Préparation de l'array à afficher
		$donnees = $reponse->listeResultat();
		$nbLignes = $reponse->getNbLignes();
		$debut = ($this->pageActuelle-1) * $this->nbResultatsParPage;
		// $donnees ne contient plus que les valeurs à afficher
		$donnees = array_slice($donnees, $debut, $this->nbResultatsParPage);


		// Création de la div HTML parente
		$ret = "\n".'<div class="liste-parent">';

		//Affichage du nombre de résultats
		$debut++;
		$fin = ($this->pageActuelle) * $this->nbResultatsParPage;
		$ret .= self::messageHTML("Lignes : $debut - $fin / $nbLignes", null);
		
		//Ajout des boutons options sur le côté
		$ret .= "\n<div id='boutons-options'>";
		//Bouton quest (recherche)
		if($this->activerRecherche){
			$ret .= '<a href="'.self::creerUrlGET('quest', 
				( ($this->afficherRecherche) ? 0 : 1)).'">?</a>'; 
		}
		// Bouton excel
		// TODO

		// Bouton pour reset le mask
		if(isset($_GET['mask']) && strlen($_GET['mask']) > 0) {
			$ret .= '<a href="'.self::creerUrlGET('mask', '').'">M</a>';
		}
		$ret .= "<div>\n";


		// Initialisation de la liste
		$ret .= '<table'.(($this->id == null)?'' : " id ='$this->id' ").'>'."\n<tr>";

		//Création des titres
		$titres = $reponse->getNomColonnes();
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
			$lienOrderBy = '<a href="'
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
				var_dump($nouvGET);
			}

			$lienMasque = '<a class="masque" href="'
				.self::creerUrlGET('mask', $maskString, ((isset($nouvGET))? $nouvGET : null))
				.'">.</a>';

			// Affiche les liens et les titres
			$ret .= '<th>'.$lienMasque.$lienOrderBy.'</th>';
			$i++;
		}
		$ret .= "</tr>\n";

		//Affichage des champs de saisie pour la  recherche
		if($this->activerRecherche && $this->afficherRecherche){
			$ret .= "<tr>";
			$types = $reponse->getTypeColonnes();
			for ($i=0; $i < count($titres); $i++) {
				//Determine la taille du champs
				$taille = min($types[$i]->len, self::MAX_LEN_INPUT);
				$ret .= "<td><input type='text' name='tabselect[]' size='$taille'/></td>";
			}
			$ret .= "</tr>\n";
		}

		// Si le tableau est vide -> retourne messageListeVide
		if(count($donnees) == 0){
			$ret .= "</table>\n";
			$ret .= self::messageHTML($this->messageListeVide, $this->classeErreur);
		}
		
		//Insertion de données
		else {
			$i = 0;
			foreach ($donnees as $ligne) {
				//Gestion des calsses
				$classe = (($i % 2)? $this->classe1 : $this->classe2);
				$ret .= '<tr'.(($classe == null)? '' : " class='$classe' ").'>';

				//Construction des cellules
				$j = 0;
				foreach ($ligne as $cellule){
					if(strlen($cellule) == 0)
						$cellule = '-';
					$ret .= '<td>';
					// Application du callback (si non null)
					if($this->callbackCellule == null)
						$ret .= $cellule;
					else {
						$fct = $this->callbackCellule;
						$ret .= $fct($cellule, $titres[$j], $i);
					}
					$ret .= '</td>';
					$j++;
				}
				$ret .= "</tr>\n";
				$i++;
			}
			$ret .= "</table>\n";
		}

		// Affichage du tableau des numeros de page
		if($nbLignes > $this->nbResultatsParPage){
			$ret .= '<table id="pagination"><tr>';
			$nbPages = ($nbLignes / $this->nbResultatsParPage);

			// S'il y a plus de pages que la limite affichable
			if($nbPages > self::NB_PAGES_MAX){
				$debut = $this->pageActuelle - 2;
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
			
			// Création des liens
			for ($i=$debut; $i <= $fin; $i++) {
				$ret .= '<td>';

				// Pas de lien si on est déjà sur la pageActuelle
				if($i == $this->pageActuelle)
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
	
	
			/******************
			***   SETTERS   ***
			******************/
	
	/**
	 * Attribue un nouvel id HTML à la liste.
	 * @param string $id l'id HTML du tableau
	 */
	public function setId($id){
		$this->id = $id;
	}

	/**
	* Définit le message d'erreur à afficher si aucun résultat n'est retournée par la requete 
	* @param string $message le nouveau message à définir
	*/
	public function setMessageListeVide($message){
		$this->messageListeVide = $message;
	}

	/**
	* Définit le nom de la class HTML des messages d'erreurs affichés
	* @param string $classe le nouveau nom de la classe des messages d'erreur
	*/
	public function setClasseErreur($classe){
		$this->classeErreur = $classe;
	}

	/**
	 * Détermine si le template doit afficher la ligne des champs de saisie pour recherche dans la liste
	 * @param boolean $valeur, valeur par défaut : false
	 */
	public function afficherChampsRecherche($valeur){
		if(is_bool($valeur))
			$this->afficherRecherche = $valeur;
	}

	/**
	 * Détermine si la fonction recherche par colonne doit être activée ou non pour cette liste
	 * @param boolean $valeur, valeur par défaut : true
	 */
	public function activerRecherche($valeur){
		if(is_bool($valeur))
			$this->activerRecherche = $valeur;
	}
	
	/**
	 * Attribue les nouvelles classes HTML à appliquer une ligne sur deux dans la liste HTML
	 * @param string $classe1 classe des lignes impaires. Si null rien ne sera appliqué
	 * @param string $classe2 classe des linges paires. Si null rien ne sera appliqué
	 */
	public function setClasseLignes($classe1, $classe2){
		$this->classe1 = $classe1;
		$this->classe2 = $classe2;
	}

	/**
	* Définit le nombre de résultats à afficher sur une page. Valeur par défaut = 50
	* @param int $valeur le nombre de lignes à afficher par pages
    * @return boolean faux si la valeur entrée est incorrecte
	*/
	public function setNbResultatsParPage($valeur){
		if(!is_int($valeur) || $valeur <= 0)
			return false;

		$this->nbResultatsParPage = $valeur;
	}

	/**
	* Définit quelle page de résultats doit afficher le template. Valeur par défaut : 1
	* @param int $numeroPage le numéro de la page à afficher (pour la 1re page : 1)
	* @return boolean faux si la valeur entrée est incorrecte
	*/
	public function setPageActuelle($numeroPage){
		if(!is_int($valeur) || $numeroPage <= 0)
			return false;

		$this->pageActuelle = $numeroPage;
	}

	/**
	* Définit le callback (la fonction) qui sera exécutée lors de l'affichage des données
	* dans les cellules du tableau. Cette fonction doit être définie comme il suit :
	* 	-> 3 paramètres d'entrée 
	* 			* element : la valeur de l'élément en cours
	* 			* colonne : le nom de la colonne en cours
	* 			* ligne   : le numéro de la ligne en cours
	* 	-> valeur de retour de type string (ou du moins affichable via echo)
	* @param string $fonction : le nom du callback à utiliser, null si aucun.
	* Valeur par défaut : null
	*/
	public function setCallbackCellule($fonction=null){
		if($fonction != null && function_exists($fonction))
			$this->callbackCellule = $fonction;
		else 
			$this->callbackCellule = null;
	}

			/******************
			***   PRIVATE   ***
			******************/

	private static function messageHTML($message, $classe){
		return '<p'.(($classe == null)? '' : " class=$classe ").'>'.$message.'</p>';
	}

	private static function creerUrlGET($nom, $val, $get=null){
		if($get == null){
			$get = $_GET;
		}
		$get[$nom] = $val;
		return strtok($_SERVER['REQUEST_URI'], '?').'?'.http_build_query($get);
	}
	
}

?>