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
* Les classes de ListeManager sont toutes contenues dans le dossier lib/
*/
function autoLoader($name) {
	//On teste l'existence du fichier contenant la classe
	if(file_exists(LIB.$name.'.php')){
		require_once LIB.$name.'.php';
	}
}

spl_autoload_register('autoLoader',false,true);

?>