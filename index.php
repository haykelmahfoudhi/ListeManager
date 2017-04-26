<?php

require_once 'includes.php';

// Connecction à la BD
Database::instantiate('pgsql:host=periscope;port=5432;dbname=warehouse;user=php_liste;password=php_liste', null, null, 'postgre');
// Database::instantiate("mysql:host=localhost;dbname=marklate", "marquage", "marquage", 'mysql');

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
$baseSQL = "select a.*, 
dt_fin_prevu - dt_debut_prevu - nb_j_non_ouvre(dt_debut_prevu,dt_fin_prevu) as jour_prevu, 
dt_debut - dt_debut_prevu - nb_j_non_ouvre(dt_debut, dt_debut_prevu) as retard_debut,
dt_fin - dt_fin_prevu - nb_j_non_ouvre(dt_fin, dt_fin_prevu) as retard_fin
from (select f.*,get_phof_dt_debut_prevue(of,phase) as dt_debut_prevu, get_phof_dt_fin_prevu(of,phase) as dt_fin_prevu from fact_1 f) a where of='1727562'";
;
$req = new SQLRequest($baseSQL);

$req2 = new SQLRequest("SELECT * FROM Trace where of > 2000 LIMIT 2000;");

// Liste Manager
$lm = new ListManager('postgre');
$html = $lm->construct($req);

echo $html;

?> <pre> <?php
echo $req;
var_dump($req);
?>
</pre>
<script type="text/javascript" src="<?=LM_JS?>jquery-3.2.1.min.js"></script>
<script type="text/javascript" src="<?=LM_JS?>listeManager.js"></script>
</body>
</html>