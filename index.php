<?php

require_once 'core/includes.php';

// Connecction à la BD
$db = Database::instancier('mysql:dbname=mecaprotec;host=localhost;charset=UTF8',
	'root', '');

//Base de la requete SQL
$baseSQL = "ERREUR";

//Exécution de la requete et affichage de la liste
$lm = ListeManager::getInstance();
echo $lm->construire($baseSQL);

?>