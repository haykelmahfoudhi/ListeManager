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
<pre><?php

$sql = "SELECT DISTINCT Article, DonnOrdre.Nom, Ref.Client, clnt_nom, gf, MasquePere, 
            DATE_FORMAT(Ref.DateModif,'%Y-%m-%d %H:%i'), Statut, Username, 'apercu', Article
        FROM Ref 
        LEFT JOIN  Masque
        ON Ref.IdMasque = Masque.Id
        JOIN client
        ON clnt_code = Ref.Client
        JOIN DonnOrdre
        ON DonnOrdre.Id = (select ma.IdDonneurOrdre from Masque ma where ma.CodeDisposition = Masque.MasquePere)";


$req = new SQLRequest($sql);
var_dump($req->getSelectedColumns());

// Connection aux BD
// Database::instantiate('pgsql:host=periscope;port=5432;dbname=warehouse;','php_liste', 'php_liste', 'postgre');
// Database::instantiate('oci:dbname=MECAPROTEC;', "DEMOV15","octal", 'oracle');
// $db = Database::instantiate('mysql:host=localhost;dbname=marklate', "marquage","marquage");


// //Base de la requete SQL
// $req2 = new SQLRequest("select * from fact_1_delais where of='1727562';");
// $req2 = new SQLRequest('SELECT * FROM ordre_fabrication WHERE ROWNUM < 2000', true);
// $req = new SQLRequest("SELECT * FROM Trace where of > 2000 LIMIT 2000 ");

// // Liste Manager
// $lm = new ListManager('uno', $db, []);
// $lm->setNbResultsPerPage(30);
// echo $html = $lm->construct($req);


// // 2e liste
// $lm2 = new ListManager('dos', $db);
// $lm2->setNbResultsPerPage(15);
// echo $lm2->construct($req2);

?></pre>
</body>
</html>
