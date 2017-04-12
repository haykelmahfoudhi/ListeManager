<?php

require_once 'core/includes.php';

// Connecction à la BD
$db = Database::connecter('mysql:dbname=mecaprotec;host=localhost;charset=UTF8',
	'root', '');

//Base de la requete SQL
$baseSQL = "SELECT * FROM test";

//Exécution du ListeManager
$lm = ListeManager::getInstance();
$listeHTML = $lm->executerRequeteGET($baseSQL);

//Affichage de la liste
echo $listeHTML;

?>