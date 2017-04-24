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

function test($cellule, $titre, $ligne){
	if($titre === 'a3')
		return 'coucou';
	else 
		return $cellule;
}

//Base de la requete SQL
$baseSQL = "SELECT * FROM test";

$req = new RequeteSQL($baseSQL);

// Liste Manager
$lm = new ListeManager();
var_dump($lm->setCallbackCellule('test'));
echo $lm->construire($req);

?>
<script type="text/javascript" src="<?=JS?>jquery-3.2.1.min.js"></script>
<script type="text/javascript" src="<?=JS?>listeManager.js"></script>
</body>
</html>