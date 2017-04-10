<?php

final class TemplateListe {
	
	// Attributs
	
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
	
	// Attributs de classe
	
	private static $CLASSE1 = 'GRIS';
	private static $CLASSE2 = 'ORANGE';
	
	// Constructeur
	
	public function __construct($id=null, $classe1=null, $classe2=null){
		$this->id = $id;
		$this->classe1 = (($classe1 == null)? self::$CLASSE1 : $classe1);
		$this->classe2 = (($classe2 == null)? self::$CLASSE2 : $classe2);
	}
	
	
	// M�thodes
	/**
	 * Construit une liste HTML avec le tableau de donn�es pass� en param�tres
	 * @param array $donnees contenant toutes les valeurs � mettre dans la liste
	 * @param array $titres (facultatif) contenant l'ensemble des titres des colonnes
	 * @return string : le code HTML de la liste HTML cr��e
	 */
	public function construireListe(array $donnees, array $titres){
		$ret = '<table'.(($this->id == null)?'' : " id ='$this->id' ").'>'."\n<tr>";

		//Cr�ation des titres
		foreach ($titres as $titre) {
			$ret .= "<th>$titre</th>"
		}
		$ret .= "</tr>\n";

		//Insertion de donn�es
		foreach ($donnees as $ligne) {
			$ret .= '<tr>';
			foreach ($ligne as $cellule){
				$ret .= '<td class="'.$this->classe1.'">'.$cellule.'</td>';
			}
			$ret .= "</tr>\n";
		}

		return $ret.'</table>';
	}
	
	
	// Setters
	
	/**
	 * Attribue un nouvel id HTML � la liste.
	 * @param string $id l'id HTML du tableau
	 */
	public function setId($id){
		$this->id = $id;
	}
	
	/**
	 * Attribue les nouvelles classes HTML � appliquer une ligne sur deux dans la liste HTML
	 * @param string $classe1 classe des lignes impaires. Si null, applique la valeur par d�faut
	 * @param string $classe2 classe des linges paires. Si null, applique la valeur par d�faut
	 */
	public function setClasseLignes($classe1, $classe2){
		$this->classe1 = (($classe1 == null)? self::$CLASSE1 : $classe1);
		$this->classe2 = (($classe2 == null)? self::$CLASSE2 : $classe2);
	}
	
}

?>