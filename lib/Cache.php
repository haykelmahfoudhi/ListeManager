<?php

/**
* 
*/
class Cache {
	
	private $fichier;
	private $nomFichier;
	private $cacheExiste;

	const NB_LIGNES_MIN = 200;

	function __construct($id) {
		$this->nomFichier = CACHE.$id.'.json';
		// Si fichier existe : ouverture en lecture
		if(file_exists($this->nomFichier)){
			$this->fichier = fopen($this->nomFichier, 'r');
			$this->cacheExiste = (($this->fichier !== false)? true : false);
		}
		// Sinon on crée un fichier cache vide
		else {
			$this->fichier = fopen($this->nomFichier, 'a+');
			$this->cacheExiste = false;
		}

		// TODO -> listenner on connection closed
	}

	/**
	* Formate en JSON et écrit les données passées en paramètres
	* dans le fichier cache, sauf s'il existe déjà
	* @var array $donnees : les données à écrire dans le fichier
	* @return boolean true si l'opération d'ecriture s'est bien passée, false en cas d'erreur
	*/
	public function ecrire(array $donnees, $nbResultatsParPage) {
		// On vérifie que le cache est vide
		if($this->existe())
			return false;

		// Encodage JSON
		$obj->liste = $donnees;
		$obj->nbResultatsParPage = $nbResultatsParPage;
		$string = json_encode($obj);

		// Ecriture dans le fichier
		$ret = fwrite($this->fichier, $string);
		$this->cacheExiste = (($ret != false) ? true : false);
		return $this->cacheExiste;
	}

	/**
	* Charges la totalité des données contenues dans le fichier cache
	* @return mixed : un array contenant toutes les données enregistrées, ou false en cas d'erreur
	*/
	public function charger() {
		// On vérifie si le cache contient quelque chose
		if(! $this->existe())
			return false;

		$contenu = fread($this->fichier, filesize($this->nomFichier));
		if($contenu === false)
			return false;
		return json_decode($contenu);
	}

	/**
	* Charge les données écrites dans le fichier cache et retourne la page correspondante
	* au paramètre en entrée. Une page est définie par le nombre de lignes qu'elle contient
	* définit par l'attribut nbResultatsParPage du fichier cache
	* @param int $prage : le numéro de la page à charger
	* @return mixed : un array contenant toutes les données enregistrées, ou false en cas d'erreur
	*/
	public function chargerPage($page) {
		// On charge toutes les données enregistrées
		$donnees = $this->charger();
		if($donnees === false)
			return false;

		// retour des données
		return array_slice($donnees->liste, ($page - 1) * $donnees->nbResultatsParPage,
			$donnees->nbResultatsParPage);

	}

	public function rechercher($colonne, $valeur){

	}

	/**
	* Supprime les données contenues dans le cache, ainsi que le fichier qui les contenaient
	* @return boolean true en cas de succès, faux sinon;
	*/
	public function supprimer() {
		//Fermeture du fichier
		if(! fclose($this->fichier))
			return false;

		// Suppression du fichier
		if($this->existe() && unlink($this->nomFichier)){
			$this->cacheExiste = false;
			$this->fichier = null;
			return true;
		}

		return false;
	}

	/**
	* @return boolean true si le fichier cache est ecrit en dur et existe, false sinon
	*/
	public function existe() {
		return $this->cacheExiste && ($this->fichier !== false);
	}

	public function getCheminFichier() {
		return $this->nomFichier;
	}
}

?>