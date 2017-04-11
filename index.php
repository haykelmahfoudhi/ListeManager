<?php

require_once 'core/includes.php';

$rq = new RequeteSQL("SELECT a.tieh, oathe.ef, apegb.ef FROM `val=a1` where a=1");
$rq->orderBy(array(-1, 3));
$rq->orderBy(array(1, 2, -3));
$rq->orderBy(-5);
$rq->supprimerOrderBy();
echo $rq;

?>