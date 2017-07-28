<?php

/** API de ListManager
 * =====================================
 * 
 * **Fonctionnement :** 
 * 
 *  * 1. Connexion de l'utilisateur à une BdD : recupération du DSN + USER + MDP dans la requête de connexion OU étiquette d'une BdD
 *      se trouvant dans le fichier databases.json. Renvoie de la réponse : true / false selon si connecté ou non. 
 *      Si connecté : enregistrement de la session et instanciation de ListManager
 *  * 2. Exécution de la requêtes SQL, et retour sous format JSON (par défaut), TABLEAU, EXCEL ou TEMPLATE (indiqué par 'type').
 *  * 3. Sauvegarde des données de connexion dans un fichier de configuration pour y acceder plus facilement par la suite
 *  * 4. Déconnexion de la base de données.
 * 
 * **Format de réponse :**
 * 
 * *Objet JSON* avec 3 attributs : 
 *  * error : bool true si erreur dans la requete
 *  * errorMessage : ?string message associé à l'erruer, null si error == true
 *  * data : ?array les données renvoyées par l'API
 * 
 * Possibilité de générer un array PHP seul encodé en JSON (type=tableau), un fichier Excel qui se téléchargera, ou d'afficher la liste.
 * 
 */

define('LM_ROOT', './');
require_once LM_ROOT.'includes.php';

// Construction de l'objet API & response
$api = new API();
$response = new stdClass();
$response->error = true;
$response->errorMessage = '';
$response->data = null;

try {
	// Connexion
	if(!$api->isConnected()){
		// Récupération des éléments de connexion
		if(isset($_GET['dsn']) || isset($_GET['label'])){
			$dsn   = ( (isset($_GET['dsn']) )? $_GET['dsn']  : '' );
			$user  = ( (isset($_GET['user']))? $_GET['user'] : '' );
			$pass  = ( (isset($_GET['pass']))? $_GET['pass'] : '' );
			$label = ((isset($_GET['label']))? $_GET['label'] : '');
			
			$response->error = !$api->connect($dsn, $user, $pass, $label);
			// Connexion OK
			if(!$response->error){
				$response->errorMessage = null;
				$response->data = [true];
			}
			// Connexion refusée
			else {
				$response->errorMessage = $api->getLastError();
				$response->data = [false];
			}
		}
	
		// Non connecté + erreur de requete
		else {
			$response->errorMessage = 'Non connecté : veuillez vous connecter un indiquant un DSN ou une étiquette de base de données préconfigurée';
		}
	}
	
	// Exécution d'une requete
	else if(isset($_GET['sql'])) {
		// Récupération des params
		$params = ( (isset($_GET['params']))? $_GET['params'] : [] );
		
		// Récupération du type de réponse
		if(isset($_GET['type'])){
			$type = strtoupper($_GET['type']);
			if(in_array($type, ['TABLEAU', 'EXCEL', 'TEMPLATE']))
				$api->setResponseType(constant("ResponseType::$type"));
		}
		// Exécution
		$sql = new SQLRequest($_GET['sql']);
		$response = $api->execute($sql, $params);
	}
	
	// Déconnexion
	else if(isset($_GET['disconnect'])){
		$api->disconnect();
		$response->error = false;
		$response->data = ['Bye bye'];
	}
	
	// Enregistrmeent des données de Database
	else if(isset($_GET['save'])){
		$response->error = !$api->saveDatabaseConf($_GET['save']);
		$response->data = [ ($response->error)? 'Enregistrement impossible : '.$api->getLastError()
				: 'Données de connexion enregistrés sous l\'étiquette '.$_GET['save'] ];
	}
	
	// Erreur requete
	else {
		$response->errorMessage = "Pour éxécuter une requête vous devez spécifier le paramètre GET 'sql' dans les données GET de l'url.\n"
			."Pour enregistrer votre configuration BD spécifiez save=<nom de la sauvegarde>.\n"
			."Pour vous déconnecter spécifiez disconnect.";
	}
}
catch(Exception $e){
	$response->error = true;
	$response->errorMessage = $e->getMessage();
}

// Affichage de la réponse
if(is_string($response))
	echo $response;
else
	echo json_encode($response, JSON_UNESCAPED_UNICODE);

?>