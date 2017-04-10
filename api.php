<?php 


/**/

//Connexion à la base de données
Database::connecter('mysql:dbname=mecaprotec;host=localhost;charset=UTF8',
	'mecaprotec', 'mecaprotec');

//On parse la requête
// TODO
$requete = "SELECT * FROM test";

//Exécution de la requete
$lm = ListeManager::getInstance();
$lm->setTypeReponse(TypeReponse::TABLEAU);
$reponse = ListeManager::executer($requete);
if($reponse != false){
	echo json_encode($reponse);
}

?>