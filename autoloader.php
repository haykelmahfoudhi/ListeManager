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
* Permet l'inclusion des classes appelées dans l'application.
* Les classes de ListManager sont toutes contenues dans le dossier lib/
* On ajoute à cela les classes de PHPExcel dans le dossier PHPExcel
*/
function lm_autoLoader($name) {
	$dossiers = array(LM_LIB, LM_PHPXL);

	foreach ($dossiers as $dossier) {
		//On teste l'existence du fichier contenant la classe
		if(file_exists($dossier.$name.'.php')){
			require_once $dossier.$name.'.php';
			return;
		}
	}
}

spl_autoload_register('lm_autoLoader',false);
?>