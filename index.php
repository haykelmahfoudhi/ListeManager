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
$req1 = new SQLRequest("SELECT * FROM Trace where of > :of LIMIT 2000 ");
$req = new SQLRequest('SELECT * FROM ordre_fabrication WHERE ROWNUM < 100', true);

// Liste Manager
$lm1 = new ListManager(
	// ListManager::NO_CSS, 
	// ListManager::NO_SEARCH, 
	// ListManager::NO_EXCEL, 
	// ListManager::NO_VERBOSE,
	// ListManager::NO_ORDER_BY,
	// ListManager::NO_MASK,
	// ListManager::UNFIXED_TITLES,
	// ListManager::NO_PAGING
	);
// $lm1->setRowsClasses('gris', 'gris-clair');
$lm1->setMask(array('SOF', 'CodeDisposition'));
$lm1->setId('yolo');
$html = $lm1->construct($req1, array(':of' => 2000));

// $lm2 = new ListManager('oracle');
// $lm2->setIdTable('tab2');
// $html2 = $lm2->construct($req);

?><pre><?=$req?></pre>
<?=$html?>
</body>
</html>
