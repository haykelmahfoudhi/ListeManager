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
// Database::instantiate('pgsql:host=periscope;port=5432;dbname=warehouse;','php_liste', 'php_liste', 'postgre');
// Database::instantiate('oci:dbname=MECAPROTEC;', "DEMOV15","octal", 'oracle');
$db = Database::instantiate('mysql:host=localhost;dbname=marklate', "marquage", "marquage");

// //Base de la requete SQL
// $req2 = new SQLRequest("select * from fact_1_delais where of='1727562';");
// $req2 = new SQLRequest('SELECT * FROM ordre_fabrication WHERE ROWNUM < 2000', true);
// $req = new SQLRequest("SELECT * FROM Trace where of > 2000 LIMIT 2000 ");
$sql = "SELECT d.Id as ok, d.Nom, COUNT(a.IdDonneurOrdre) as nbId FROM Avion a, DonnOrdre d
WHERE a.IdDonneurOrdre = d.Id GROUP BY d.Id, d.Nom;";
$lm = new ListManager();
// $lm->setCellCallback(function ($cell, $titre, $data, $num) {echo "$titre\n";});
echo $lm->construct($sql, [], ['nbId' => 'COUNT(a.IdDonneurOrdre)']);


?>
</body>
</html>
