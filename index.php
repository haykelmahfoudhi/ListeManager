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
\LM\Database::instantiate('pgsql:host=periscope;port=5432;dbname=warehouse;','php_liste', 'php_liste', 'postgre');
\LM\Database::instantiate('mysql:host=localhost;dbname=marklate', "marquage","marquage", 'mysql');
\LM\Database::instantiate('oci:dbname=MECAPROTEC;', "DEMOV15","octal", 'oracle');


//Base de la requete SQL
// $req = new \LM\SQLRequest("select * from fact_1_delais where of='1727562';");
$req = new \LM\SQLRequest("SELECT * FROM Trace where of > 2000 LIMIT 2000 ");
// $req = new \LM\SQLRequest('SELECT * FROM ordre_fabrication WHERE ROWNUM < 100', true);

var_dump(extension_loaded('pdo_oci'));

// Liste Manager
$lm = new \LM\ListManager('mysql');
// $lm->setRowsClasses('gris', 'gris-clair');
$lm->setMask(array('SOF', 'CodeDisposition'));
$lm->setNbResultsPerPage(15);
$html = $lm->construct($req);

?><pre><?=$req?></pre>
<?=$html?>
</body>
</html>