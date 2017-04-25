<?php

require_once 'includes.php';

// Connecction à la BD
Database::instantiate('mysql:dbname=marklate;host=localhost;charset=UTF8', 'marquage', 'marquage');

?>
<!DOCTYPE html>
<html>
<head>
	<title>Test liste</title>
	<meta charset="utf-8" author="RookieRed">
	<link rel="stylesheet" type="text/css" href="<?=LM_CSS?>base.css">
</head>
<body>
<?php

// Callback : ajout d'un lien colonne a2 + img colonne a6
// function test($contenu, $titre, $ligne){
// 	if($titre == 'a2') {
// 		return "$contenu - <a href='/vers/autre/chose'>lien$ligne</a>";
// 	}
// 	else if($titre == 'a6' && strlen($contenu) > 0) {
// 		return "$contenu || <img src='/404' alt='img 2 test'>";
// 	}
// 	else 
// 		return $contenu;
// }

//Base de la requete SQL
$baseSQL = "SELECT * FROM `Trace` LIMIT 8000";

$req = new SQLRequest($baseSQL);

// Liste Manager
$lm = new ListManager();
// $lm->setCellCallback('test');
$lm->setNbResultsPerPage(100);
$lm->setMaxPagesDisplayed(15);
$html = $lm->construct($req);
echo $req;
echo $html;

?>
<script type="text/javascript" src="<?=LM_JS?>jquery-3.2.1.min.js"></script>
<script type="text/javascript" src="<?=LM_JS?>listeManager.js"></script>
</body>
</html>