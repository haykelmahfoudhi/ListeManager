<?php 
require_once 'includes.php';


		/*****************************************
		**                                      **
		**         db         88888888ba   88   **
		**        d88b        88      "8b  88   **
		**       d8'`8b       88      ,8P  88   **
		**      d8'  `8b      88aaaaaa8P'  88   **
		**     d8YaaaaY8b     88""""""'    88   **
		**    d8""""""""8b    88           88   **
		**   d8'        `8b   88           88   **
		**  d8'          `8b  88           88   **
		**                                      **
		******************************************/


/*
Cette API est utilisable pour les requêtes AJAX déclenchée lors de la navigation dans 
les sites internes, et pour toute autre application nécessistant de communiquer avec la BD.

L'une des fonctions première de cette API est la possibilité de naviguer entre les pages
des listes en utilisant le cache, et donc sans recharger la requete SQL à chaque coup

--------------------------------------------------------------------------------------*/

$reponse = new stdClass();
$reponse->donnees = null;
$reponse->erreur = true;
$reponse->messageErreur = '';

if(isset($_GET['RequestType'])){

	switch ($_GET['RequestType']) {

		// Cas de demande AJAX concernant la page suivante d'une liste enregistrée en cache
		case 'page':
			if(isset($_GET['page']) && isset($_GET['id_cache'])){

				//Chargement de l'objet cache
				$cache = new Cache($_GET['id_cache']);
				if($cache->exists()){

					//Récupération de la page correspondante
					$ret = $cache->chargerDonnees($_GET['page']);
					if($ret !== false){
						$reponse->donnees = $ret;
						$reponse->erreur = false;
					}
					else {
						$reponse->messageErreur = 'Page inconnue';
					}
				}
				else {
					$reponse->messageErreur = 'id_cache non reconnu';
				}
			}
			else {
				$reponse->messageErreur = 'Erreur de paramètres : preciser page et id_cahce';	
			}
			break;
		
		default:
			$reponse->messageErreur = 'Le type de requete n\'est pas reconnu';
			break;
	}
}

else {
	$reponse->messageErreur = 'Le type de requete n\'est pas reconnu';
}

echo json_encode($reponse);

?>