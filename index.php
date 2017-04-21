<?php

require_once 'includes.php';

// Connecction à la BD
Database::instancier('mysql:dbname=mecaprotec;host=localhost;charset=UTF8',
	'root', '');

?>
<!DOCTYPE html>
<html>
<head>
	<title>Test liste</title>
	<meta charset="utf-8" author="RookieRed">
	<link rel="stylesheet" type="text/css" href="<?=CSS?>base.css">
</head>
<body>
<?php

//Base de la requete SQL
$baseSQL = "SELECT id, a1, a2, a3, a6 as a4 FROM test";

//Exécution de la requete et affichage de la liste
$lm = new ListeManager();
$lm->setNbResultatsParPage(10);
//$lm->utiliserCache(true);
echo $lm->construire($baseSQL);

// Test du cache

?>
<script type="text/javascript" src="<?=JS?>jquery-3.2.1.min.js"></script>
<script type="text/javascript" src="<?=JS?>listeManager.js"></script>
</body>
</html>