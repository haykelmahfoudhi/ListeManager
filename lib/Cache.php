<?php

/**
* 
*/
class Cache {
	
	private $file;
	private $fileName;
	private $cacheExists;

	const NB_LIGNES_MIN = 200;

	function __construct($id) {
		$this->fileName = LM_CACHE.$id.'.json';
		// Si fichier existe : ouverture en lecture
		if(file_exists($this->fileName)){
			$this->file = fopen($this->fileName, 'r');
			$this->cacheExists = (($this->file !== false)? true : false);
		}
		// Sinon on crée un fichier cache vide
		else {
			$this->file = fopen($this->fileName, 'a+');
			$this->cacheExists = false;
		}

		// TODO -> listenner on connection closed
	}

	/**
	* Formate en JSON et écrit les données passées en paramètres
	* dans le fichier cache, sauf s'il existe déjà
	* @var RequestResponse $reponse : l'objet réponse produit par l'exécution de la requete
	* @var int $nbResultatsParPage : le nombre de lignes contenues dans une seule page
	* @return boolean true si l'opération d'ecriture s'est bien passée, false en cas d'erreur
	*/
	public function write(RequestResponse $reponse, $nbResultatsParPage) {
		// On vérifie que le cache est vide et qu'il y a suffisemment de données
		if($this->exists() && $reponse->getNbLignes() >= self::NB_LIGNES_MIN)
			return false;

		// Encodage JSON
		$obj->nbResultatsParPage = $nbResultatsParPage;
		$obj->titres  = $reponse->getNomColonnes();
		$obj->donnees = $reponse->listeResultat();

		// Ecriture dans le fichier
		$ret = fwrite($this->file, json_encode($obj));
		$this->cacheExists = ($ret !== false);
		return $this->cacheExists;
	}

	/**
	* Charges la totalité des données contenues dans le fichier cache
	* @return mixed : un array contenant toutes les données enregistrées, ou false en cas d'erreur
	*/
	public function load() {
		// On vérifie si le cache contient quelque chose
		if(! $this->exists())
			return false;

		$contenu = fread($this->file, filesize($this->fileName));
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
	public function loadPage($page) {
		// On charge toutes les données enregistrées
		$obj = $this->load();
		if($obj === false)
			return false;

		// retour des données
		return array_slice($obj->donnees, ($page - 1) * $obj->nbResultatsParPage,
			$obj->nbResultatsParPage);

	}

	/**
	* Effectue une recherche de données dans le cache
	* @param array $rechcerche le tableau contenant les éléments de recehrche :
	* 	-> ce paramètre doit être sous la forme d'un tableau associatif comme il suit :
	* 		array(	[nomColonne1] => 'valeur recherchée',
	* 				[nomColonne2] => 'valeur recherchée' ... )  
	* @return l'ensemble des données correpsondant à la recherche ou faux en cas d'erreur
	*/
	public function search(array $recherche){
		// On charge toutes les données enregistrées
		$obj = $this->load();
		if($obj === false)
			return false;

		$ret = $obj->donnees;
		foreach ($recherche as $titre => $valeur) {
			
			// Récupération du numéro de colonne à partir du titre
			if( ($numColonne = array_search($titre, $obj->titre)) !== false) {

				//Création du callback pour filtrer les résultats
				$callback = function($ligne) use($numColonne, $valeur) {
					// Si la recherche concerne un nombre
					if(is_numeric($ligne[$numColonne])){
						return $ligne[$numColonne] == $valeur;
					}

					// Sinon on recherche une correspondance dans un string
					return (strpos($ligne[$numColonne], $valeur) !== false);
				};
				// Filtrage
				$ret = array_filter($ret, $callback);
			}
		}
	}

	/**
	* Supprime les données contenues dans le cache, ainsi que le fichier qui les contenaient
	* @return boolean true en cas de succès, faux sinon;
	*/
	public function delete() {
		//Fermeture du fichier
		if(! fclose($this->file))
			return false;

		// Suppression du fichier
		if($this->exists() && unlink($this->fileName)){
			$this->cacheExists = false;
			$this->file = null;
			return true;
		}

		return false;
	}

	/**
	* @return boolean true si le fichier cache est ecrit en dur et existe, false sinon
	*/
	public function exists() {
		return $this->cacheExists && ($this->file !== false);
	}

	public function getPathFile() {
		return $this->fileName;
	}
}

?>