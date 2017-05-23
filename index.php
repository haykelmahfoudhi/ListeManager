<?php

define('LM_ROOT', '');
require_once 'includes.php';

?>
<!DOCTYPE html>
<html>
<head>
	<title>Test liste</title>
	<meta charset="utf-8" author="RookieRed">
</head>
<body>
<?php


// Connection aux BD
Database::instantiate('pgsql:host=periscope;port=5432;dbname=warehouse;','php_liste', 'php_liste', 'postgre');
Database::instantiate('mysql:host=localhost;dbname=marklate', "marquage","marquage");
Database::instantiate('oci:dbname=MECAPROTEC;', "DEMOV15","octal", 'oracle');


//Base de la requete SQL
// $req = new SQLRequest("select * from fact_1_delais where of='1727562';");
$req = new SQLRequest("SELECT * FROM Trace where of > :of LIMIT 2000 ");
// $req = new SQLRequest('SELECT * FROM ordre_fabrication WHERE ROWNUM < 100', true);

// Liste Manager
$lm = new ListManager(
	// ListManager::NO_CSS, 
	// ListManager::NO_SEARCH, 
	// ListManager::NO_EXCEL, 
	// ListManager::NO_VERBOSE,
	// ListManager::NO_ORDER_BY,
	// ListManager::NO_MASK,
	// ListManager::UNFIXED_TITLES,
	// ListManager::NO_PAGING
	);
// $lm->setRowsClasses('gris', 'gris-clair');
$lm->setMask(array('SOF', 'CodeDisposition'));
$html = $lm->construct($req, array(':of' => 2000));


?><pre><?=$req?></pre>
<?=$html?>
</body>
</html>
