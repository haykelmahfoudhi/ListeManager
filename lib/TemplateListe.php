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
	private $recherche;
	
	
	public static $CLASSE1 = 'GRIS';
	public static $CLASSE2 = 'ORANGE';
	
	
			/***********************
			***   CONSTRUCTEUR   ***
			***********************/
	
	public function __construct($id=null, $classe1=null, $classe2=null){
		$this->id = $id;
		$this->classe1 = (($classe1 == null)? self::$CLASSE1 : $classe1);
		$this->classe2 = (($classe2 == null)? self::$CLASSE2 : $classe2);
		$this->recherche = false;
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
		$ret = '<table'.(($this->id == null)?'' : " id ='$this->id' ").'>'."\n<tr>";

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
				$taille = 
				$ret .= "<td><input type='text' name='tabselect[]' size='$taille'/></td>";
			}
			$ret .= "</tr>\n";
		}

		//Insertion de donn�es
		$donnees = $reponse->listeResultat();
		for ($i=0; $i < $reponse->getNbLinges(); $i++) {
			//Gestion des calsses
			$classe = (($i % 2)? $this->classe2 : $this->classe1);
			$ret .= '<tr'.(($classe == null)? '' : " class='$classe' ").'>';

			//Construction des cellules
			foreach ($donnees[$i] as $cellule){
				$ret .= '<td>'.$cellule.'</td>';
			}
			$ret .= "</tr>\n";
		}

		return $ret.'</table>';
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
	
}

?>