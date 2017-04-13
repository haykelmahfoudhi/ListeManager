<?php

final class TemplateListe {
	
	
			/********************
			***   ATTRIBUTS   ***
			********************/
	
	/**
	 * @var string $id : l'id du tableau HTML
	 */
	private $id;
	/**
	 * Varaibles contenatn les classes HTML appliqu�es aux lignes paires / impaires
	 * @var string $classe1 : classe des lignes imparaires
	 * @var string $classe2 : classe des lignes paires
	 */
	private $classe1, $classe2;
	/**
	* @var boolean $recherche : sp�cifie si les champs de saisie pour la recherche sont visibles ou non
	*/
	private $recherche;
	/**
	* @var 
	*/
	private $messageListeVide;

	private $classeErreur;

	private $resultatsParPage;

	private $pageActuelle;
	

	public static $CLASSE1 = 'GRIS';
	public static $CLASSE2 = 'ORANGE';
	
	const MAX_LEN_INPUT = 60;
	
	
			/***********************
			***   CONSTRUCTEUR   ***
			***********************/
	
	public function __construct($id=null, $classe1=null, $classe2=null){
		$this->id = $id;
		$this->classe1 = (($classe1 == null)? self::$CLASSE1 : $classe1);
		$this->classe2 = (($classe2 == null)? self::$CLASSE2 : $classe2);
		$this->recherche = false;
		$this->messageListeVide = "Aucun r�sultat!";
		$this->classeErreur = 'erreur';
		$this->pageActuelle = ((isset($_GET['page']) && $_GET['page'] > 0) ? $_GET['page'] : 1 );
		$this->resultatsParPage = 100;
	}
	
	
			/*******************
			***   METHODES   ***
			*******************/

	/**
	 * Construit une liste HTML avec le tableau de donn�es pass� en param�tres
	 * @param ReponseRequete $reponse contenant l'ensemble des r�sultats de la requete
	 * @return string : le code HTML de la liste HTML g�n�r�
	 */
	public function construireListe(ReponseRequete $reponse){

		// On teste d'abord s'il y a erreur dans la r�ponse
		if($reponse->erreur()){
			return self::messageHTML($reponse->getMessageErreur(),
				$this->classeErreur);
		}

		// Pr�paration de l'array � afficher
		$donnees = $reponse->listeResultat();
		$nbLignes = $reponse->getNbLignes();
		$debut = ($this->pageActuelle-1) * $this->resultatsParPage;
		// $donnees ne contient plus que les valeurs � afficher
		$donnees = array_slice($donnees, $debut, $this->resultatsParPage);


		//Affichage du nombre de r�sultats
		$debut++;
		$fin = ($this->pageActuelle) * $this->resultatsParPage;
		$ret = self::messageHTML("Lignes : $debut - $fin / $nbLignes", null);

		// Initialisation de la liste
		$ret .= '<table'.(($this->id == null)?'' : " id ='$this->id' ").'>'."\n<tr>";

		//Cr�ation des titres
		$titres = $reponse->getNomColonnes();
		foreach ($titres as $titre) {
			$ret .= "<th>$titre</th>";
		}
		$ret .= "</tr>\n";

		//Affichage des champs de saisie pour la  recherche
		if($this->recherche){
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
		
		//Insertion de donn�es
		else {
			$i = 0;
			foreach ($donnees as $ligne) {
				$i++;
				//Gestion des calsses
				$classe = (($i % 2)? $this->classe2 : $this->classe1);
				$ret .= '<tr'.(($classe == null)? '' : " class='$classe' ").'>';

				//Construction des cellules
				foreach ($ligne as $cellule){
					$ret .= '<td>'.$cellule.'</td>';
				}
				$ret .= "</tr>\n";
			}
			$ret .= "</table>\n";
		}

		// Affichage du tableau des numeros de page
		if($nbLignes > $this->resultatsParPage){
			$ret .= '<table><tr>';
			for ($i=1; $i < ($nbLignes / $this->resultatsParPage) + 1; $i++) {
				$ret .= '<td>';

				// Pas de lien si on est d�j� sur la pageActuelle
				if($i == $this->pageActuelle)
					$ret .= "$i";
				else {	
					// Construction du lien de la page
					$ret .= '<a href="'.self::creerUrlGET('page', $i).'">$i</a>';
				}

				$ret .= '</td>';
			}
			$ret .= "</tr></table>\n";
		}
		// Fin
		return $ret;
	}
	
	
			/******************
			***   SETTERS   ***
			******************/
	
	/**
	 * Attribue un nouvel id HTML � la liste.
	 * @param string $id l'id HTML du tableau
	 */
	public function setId($id){
		$this->id = $id;
	}

	/**
	* D�finit le message d'erreur � afficher si aucun r�sultat n'est retourn�e par la requete 
	* @param string $message le nouveau message � d�finir
	*/
	public function setMessageListeVide($message){
		$this->messageListeVide = $message;
	}

	/**
	* D�finit le nom de la class HTML des messages d'erreurs affich�s
	* @param string $classe le nouveau nom de la classe des messages d'erreur
	*/
	public function setClasseErreur($classe){
		$this->classeErreur = $classe;
	}

	/**
	 * D�termine si le template doit afficher la ligne des champs de saisie pour recherche dans la liste
	 * @param boolean $valeur
	 */
	public function afficherChampsRecherche($valeur){
		if(is_bool($valeur))
			$this->recherche = $valeur;
	}
	
	/**
	 * Attribue les nouvelles classes HTML � appliquer une ligne sur deux dans la liste HTML
	 * @param string $classe1 classe des lignes impaires. Si null rien ne sera appliqu�
	 * @param string $classe2 classe des linges paires. Si null rien ne sera appliqu�
	 */
	public function setClasseLignes($classe1, $classe2){
		$this->classe1 = $classe1;
		$this->classe2 = $classe2;
	}

	/**
	* D�finit le nombre de r�sultats � afficher sur une page. Valeur par d�faut = 100
	* @param int $valeur le nombre de lignes � afficher par pages
    * @return boolean faux si la valeur entr�e est incorrecte
	*/
	public function setNbResultatsParPage($valeur){
		if(!is_int($valeur) || $valeur <= 0)
			return false;

		$this->resultatsParPage = $valeur;
	}

	/**
	* D�finit quelle page de r�sultats doit afficher le template. Valeur par d�faut 
	* d�finie par GET['page'], ou 1
	* @param int $numeroPage le num�ro de la page � afficher (pour la 1re page : 1)
	* @return boolean faux si la valeur entr�e est incorrecte
	*/
	public function setPageActuelle($numeroPage){
		if(!is_int($valeur) || $numeroPage <= 0)
			return false;

		$this->pageActuelle = $numeroPage;
	}

			/******************
			***   PRIVATE   ***
			******************/

	private static function messageHTML($message, $classe){
		return '<p'.(($classe == null)? '' : " class=$classe ").'>'.$message.'</p>';
	}

	private static function creerUrlGET($nom, $val){
		$get = $_GET;
		$get[$nom] = $val;
		return strtok($_SERVER['REQUEST_URI'], '?').'?'.http_build_query($get);
	}
	
}

?>