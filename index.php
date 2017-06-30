<?php

define('LM_ROOT', '');
require_once LM_ROOT.'includes.php';

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
// Database::instantiate('pgsql:host=periscope;port=5432;dbname=warehouse;','php_liste', 'php_liste', 'postgre');
// Database::instantiate('oci:dbname=MECAPROTEC;', "DEMOV15","octal", 'oracle');
$db = Database::instantiate('mysql:host=localhost;dbname=marklate', "marquage", "marquage");

// //Base de la requete SQL
// $req = new SQLRequest("select * from fact_1_delais where of='1727562';");
// $req = new SQLRequest('SELECT * FROM ordre_fabrication WHERE ROWNUM < 2000', true);
// $req = new SQLRequest("SELECT * FROM Trace where of > 2000 LIMIT 2000 ");
$req = new SQLRequest("SELECT d.Id as ok, d.Nom, COUNT(a.IdDonneurOrdre) as nbId FROM Avion a, DonnOrdre d
						WHERE a.IdDonneurOrdre = d.Id GROUP BY d.Id, d.Nom
						ORDER BY d.id;");

$lm = new ListManager();

$lm->setFilter(['d.Id' => '>0']);


echo $lm->construct($req, [], ['COUNT(a.IdDonneurOrdre)']);

echo $req;

?>
</body>
</html>
