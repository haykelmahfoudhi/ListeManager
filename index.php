<?php

require_once 'core/includes.php';

// Connecction à la BD
$db = Database::instancier('mysql:dbname=mecaprotec;host=localhost;charset=UTF8',
	'root', '');

//Base de la requete SQL
$baseSQL = "SELECT * FROM test";

//Exécution de la requete et affichage de la liste
$lm = new ListeManager();
echo $lm->construire($baseSQL);

?>