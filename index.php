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
$req2 = new SQLRequest("select * from fact_1_delais where of='1727562';");
$req = new SQLRequest("SELECT * FROM Trace where of > 2000 LIMIT 2000 ");
// $req = new SQLRequest('SELECT * FROM ordre_fabrication WHERE ROWNUM < :num', true);

// Liste Manager
$lm = new ListManager('mysql'
	// '',
	// 'oracle'
	// [ListManager::NO_CSS, 
	// ListManager::NO_SEARCH, 
	// ListManager::NO_EXCEL, 
	// ListManager::NO_VERBOSE,
	// ListManager::NO_ORDER_BY,
	// ListManager::NO_JS_MASK,
	// ListManager::UNFIXED_TITLES,
	// ListManager::NO_PAGING]
	);

?><pre></pre>
<?php

$html = 
$lm	->setNbResultsPerPage(10)
	->construct($req
	// , [':num' => ($a = 100)]
);

$lm2 = new ListManager('postgre', 'postgre');
echo $lm2->construct($req2);

?>
<?=$html?>
</body>
</html>
