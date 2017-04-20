<?php 
require_once 'core/includes.php';


		/*****************************************
		**                                      **
		**         db         88888888ba   88   **
		**        d88b        88      "8b  88   **
		**       d8'`8b       88      ,8P  88   **
		**      d8'  `8b      88aaaaaa8P'  88   **
		**     d8YaaaaY8b     88""""""'    88   **
		**    d8""""""""8b    88           88   **
		**   d8'        `8b   88           88   **
		**  d8'          `8b  88           88   **
		**                                      **
		******************************************/


/*
Cette API est utilisable pour les requêtes AJAX déclenchée lors de la navigation dans 
les sites internes, et pour toute autre application nécessistant de communiquer avec la BD.

	Pour fonctionner, les requêtes entrantes devront être paramétrée comme ceci : 
		* adresse : url-vers-api/BASE REQUETE/?&mask=[...]&orderby=[...]

	En retour, l'API fournit un objet réponse encodé en JSON construit comme ceci :
		* reponse -> erreur : booléen sur la présence ou nom d'erreur lors de l'exécution de la requête
		* reponse -> messageErreur : le message d'erreur associé (seulement si erreur)
		* reponse -> donnees : les données générées par l'exécution de la requête

--------------------------------------------------------------------------------------*/



?>