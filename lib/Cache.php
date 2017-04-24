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
	* @var ReponseRequete $reponse : l'objet réponse produit par l'exécution de la requete
	* @var int $nbResultatsParPage : le nombre de lignes contenues dans une seule page
	* @return boolean true si l'opération d'ecriture s'est bien passée, false en cas d'erreur
	*/
	public function ecrire(ReponseRequete $reponse, $nbResultatsParPage) {
		// On vérifie que le cache est vide et qu'il y a suffisemment de données
		if($this->existe() && $reponse->getNbLignes() >= self::NB_LIGNES_MIN)
			return false;

		// Encodage JSON
		$obj->nbResultatsParPage = $nbResultatsParPage;
		$obj->titres  = $reponse->getNomColonnes();
		$obj->donnees = $reponse->listeResultat();

		// Ecriture dans le fichier
		$ret = fwrite($this->fichier, json_encode($obj));
		$this->cacheExiste = ($ret !== false);
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
		$obj = $this->charger();
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
	public function rechercher(array $recherche){
		// On charge toutes les données enregistrées
		$obj = $this->charger();
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