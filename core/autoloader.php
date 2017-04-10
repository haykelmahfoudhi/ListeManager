<?php

/***********************************************
***                                          ***
***     	db                  88           ***
***        d88b                 88           ***
***       d8'`8b                88           ***
***      d8'  `8b      88888    88           ***
***     d8YaaaaY8b     88888    88           ***
***    d8""""""""8b             88           ***
***   d8'        `8b            88           ***
***  d8'          `8b           88888888888  ***
                                             ***
*************************************************/

/**
* Permet l'inclusion des classes appelées dans l'application. Ces classes doivent
* se trouver dans les dossiers listés dans le tableau $nomDossiers
*/
function autoLoader($name) {
	//Ensemble des dossiers pouvant contenir la classe
	$nomDossiers = array(CORE, LIB);
	//On teste l'existence du fichier dans les divers dossiers possibles
	foreach ($nomDossiers as $dossier) {
		if(file_exists($dossier.$name.'.php')){
			require_once $dossier.$name.'.php';
			break;
		}
	}
}

spl_autoload_register('autoLoader',false,true);

?>