<?php

require_once 'includes.php';
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


// Connection aux BD
Database::instantiate('pgsql:host=periscope;port=5432;dbname=warehouse;','php_liste', 'php_liste', 'postgre');
Database::instantiate("mysql:host=localhost;dbname=marklate", "marquage","marquage", 'mysql');
Database::instantiate("oci:dbname=MECAPROTEC;", "DEMOV15","octal", 'oracle');


//Base de la requete SQL
// $req = new SQLRequest("select * from fact_1_delais where of='1727562';");
// $req = new SQLRequest("SELECT * FROM Trace where of > 2000 LIMIT 2000 ");
$req = new SQLRequest('SELECT * FROM ordre_fabrication WHERE ROWNUM < 100', true);


// Liste Manager
$lm = new ListManager('oracle');
$html = $lm->construct($req);

?><pre><?=$req?></pre>
<?=$html?>
</body>
</html>