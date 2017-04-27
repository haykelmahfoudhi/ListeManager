<?php

require_once 'includes.php';

// Connecction à la BD
Database::instantiate('pgsql:host=periscope;port=5432;dbname=warehouse;user=php_liste;password=php_liste', null, null, 'postgre');
Database::instantiate("mysql:host=localhost;dbname=marklate", "marquage", "marquage", 'mysql');

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

//Base de la requete SQL
$baseSQL = "select *
from fact_1_delais where of='1727562'";
$req = new SQLRequest($baseSQL);

$req2 = new SQLRequest("SELECT * FROM Trace where of > 2000 LIMIT 2000;");

// Liste Manager
$lm = new ListManager('mysql');
$html = $lm->construct($req2);

echo $html;

?> <pre> <?php
var_dump($req2);
?>
</pre>
<script type="text/javascript" src="<?=LM_JS?>jquery-3.2.1.min.js"></script>
<script type="text/javascript" src="<?=LM_JS?>listeManager.js"></script>
</body>
</html>