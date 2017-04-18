<?php

require_once 'core/includes.php';

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

// Connecction à la BD
$db = Database::instancier('mysql:dbname=mecaprotec;host=localhost;charset=UTF8',
	'root', '');

//Base de la requete SQL
$baseSQL = "SELECT id, a1, test.a6 as A5 FROM test";

//Exécution de la requete et affichage de la liste
$lm = new ListeManager();
echo $lm->construire($baseSQL);

?>
</body>
</html>