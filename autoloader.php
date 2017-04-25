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
*/
function lm_autoLoader($name) {
	//On teste l'existence du fichier contenant la classe
	if(file_exists(LM_LIB.$name.'.php')){
		require_once LM_LIB.$name.'.php';
	}
}

spl_autoload_register('lm_autoLoader',false);
?>