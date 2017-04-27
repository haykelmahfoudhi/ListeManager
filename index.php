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
$lm = new ListManager('postgre');
$html = $lm->construct($req);

echo $html;

?> <pre> <?php
echo $req;
?>
</pre>
</body>
</html>