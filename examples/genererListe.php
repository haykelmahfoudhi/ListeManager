<?php

/*
	
	Ce document propose plusieurs exemples de code pour mieux comprendre le fonctionnement de ListManager.
	================================================================================================================

	Dans les lignes suivantes vous trouverez comment :

	1	-> Inclure ListManger à votre projet
	2	-> Connecter votre objet à une base de données
		a	-> Une seule base de données
		b 	-> Plusieurs base de données
	3	-> Désactiver des fonctionnalités 
		a	-> En utilisant les options du constructeur
		b	-> En utilisant les méthodes de classe
	4	-> Construire une liste simple à partir d'une requete
		a	-> Comprendre la différence entre construct et execute
		b	-> Exécution simple d'une requete SQL
		c	-> Exécution avec prepare & execute (avec des paramètres varaibles dans la requete)
	5	-> Changer le nom des titres de votre liste
*/

// ============================> 1) INCLURE LISTMANAGER

// Définition de la constante LM_ROOT : elle correspond au chemin relatif vers le dossier racine contenant le projet ListManager
define('LM_ROOT', 'ListManager/');
require_once LM_ROOT.'includes.php'; // le fichier includes.php situé à la racine du projet est le seul que vous avez besoin d'inclure pour utiliser ListManger


// ============================> 2) CONNECTER UNE BASE DE DONNEES


//--------------- a) Une seule BD

	// Plusieurs méthodes existent : la première est la plus simple : instantier un seul objet Database sans préciser son étiquette, puis instancier un objet ListManager
Database::instantiate('dsn:dbname=db;host=localhost', 'login', 'password'); // Il vous suffit ensuite de créer un objet 
//ListManager qui se connectera automatiquement sur la base de données que vous venez d'instancier :
$lm = new ListManager(); // selectionne automatiquement la base de données principale de l'application

	// Méthode 1 : connectDatabase
// /!\ en faisant comme ceci ListManager peut afficher un message d'erreur car le constructeur ne trouvera aucune instance de Database à utiliser !
$lm = new ListManager(); 
$lm->connectDatabase('dsn:dbname=db;host=localhost', 'login', 'password');

	// Méthode 2 : setDatabase
// /!\ en faisant comme ceci ListManager peut afficher un message d'erreur car le constructeur ne trouvera aucune instance de Database à utiliser !
$lm = new ListManager(); 
$db = Database::instantiate('dsn:dbname=db;host=localhost', 'login', 'password');
$lm->setDatabase($db); // cette méthode fonctionne également si vous précisez en paramètre l'étiquette de la base de données et non une référence


//--------------- b) Plusieurs BD

	// Si vous utilisez plusieurs base de données, il faut les intancier avec des étiquettes différentes (uniques pour chaque objet Database)
$db1 = Database::instantiate('dsn:dbname=db;host=localhost', 'login', 'password', 'etiquette1');
$db2 = Database::instantiate('dsn:dbname=autre_db;host=localhost', 'user', 'resu', 'etiquette2');
// Le constructeur de ListManager prend en 2e paramètre la base de données sur laquelle il se connectera :
$lm = new ListManager(null, $db1); // Ici nous nous connectons sur la 1re base de données ...
$lm = new ListManager(null, 'etiquette1'); // Les 2 lignes sont équivalentes : comem pour setDatabase le constructeur prend soit la référence de Database soit l'étiquette


// ============================> 3) DESACTIVER DES FONCTIONNALITES


//--------------- a) Constructeur

// Le constructeur prend pour 3e paramètre un tableau de constantes permettant de désactiver les fonctionnalités principales de l'objet :
$lm = new ListManager(null, null,
	array(
		ListManager::NO_CSS, 			// Désactive le CSS par défaut
		ListManager::NO_SEARCH, 		// Désactive la recherche
		ListManager::NO_EXCEL, 			// Désactive l'export excel
		ListManager::NO_VERBOSE,		// Désactive le mode verbeux
		ListManager::NO_ORDER_BY,		// Désactive la fonction de tri
		ListManager::NO_JS_MASK,		// Désactive le masquage des colonnes
		ListManager::UNFIXED_TITLES,	// Empeche la fixation des titres en haut de la page
		ListManager::NO_RESULTS,		// Masque la ligne informative concernant le nombre de résultats retournés par la requete
		ListManager::NO_PAGING			// Désactive les liens pour naviguer entre les pages de résultat
	)); 


//--------------- b) Methodes de classe

// Tous les setters de la classe ListManager retourne la référence de l'objet si l'opération est un succès.
// Ainsi vous pouvez désactiver tout un ensemble de fonctionnalité et ce dans une seule instruction, et en appelant une seule fois la référence de l'objet.
// Le code suivant est équivalent au précédent pour la désactivation des options dans le constructeur :
$lm = new ListManager();
$lm -> enableSearch(false)				// Désactive la recherche
	-> verbose(false)					// Désactive le mode verbeux
	-> enableExcel(false)				// Désactive l'export excel
	-> enableOrderBy(false)				// Désactive la fonction de tri
	-> enableJSMask(false)				// Désactive le masquage des colonnes
	-> fixTitles(false)					// Empeche la fixation des titres en haut de la page
	-> displayResultsInfos (false)		// Masque la ligne informative concernant le nombre de résultats retournés par la requete
	-> setPagingLinksNb(0)				// Désactive les liens pour naviguer entre les pages de résultat
	-> applyDefaultCSS(false);			// Désactive le CSS par défaut



// ============================> 4) CONSTRUIRE UNE LISTE


//--------------- a) Différences execute construct

// Pour construire une liste 2 choix de méthode : execute & construct.
	// Utilisez construct si vous prenez en compte les paramètres d'url pour trier, rechercher, naviguer à travers les pages
	// Utilisez execute si vous vous souhaitez générer une liste simple sans utilser toutes ces fonctionnalités


//--------------- b) Exécuter une simple requete

Database::instantiate('dsn:dbname=db;host=localhost', 'login', 'password');	// Connexion DB
$lm = new ListManager();													// Instanciation LM
$html = $lm->construct("SELECT * FROM table WHERE id >= 1000 LIMIT 2000");	// Création de la liste HTML
echo $html;																	// Affichage de la liste HTML des données sélectionnées


//--------------- c) Exécuter une requete 'prepare -> execute'
// PDO propose une solution sécurisée pour exécuter des requetes SQL en évitant les injections SQL dûes à la concaténation d'une 
// requete SQL et de variables provenant de la saisie utilisateur : il faut pour cela utiliser les méthodes prepare puis execute de PDO et PDOStatement.
// ListManager vous permet de passer par ces méhtodes. Pour cela il suffit juste de préciser en 2e aramètre de construct ou execute 
// l'array qui lie les variables contenues dans la requete aux variables à utilsier.

$id = $_POST['id_table'];													// On récupère l'id à sélectionner depuis les données postées
Database::instantiate('dsn:dbname=db;host=localhost', 'login', 'password');	// Connexion DB
$lm = new ListManager();													// Instanciation LM
$html = $lm->construct("SELECT * FROM table WHERE id >= :id LIMIT 2000",
	[':id' => $id]); 														// Création de la liste HTML
echo $html;																	// Affichage de la liste


// ============================> 5) CHANGER LES TITRES DE LA LISTE
// Il arrive que les noms des colonnes de votre base de données ne soient pas tres 'sexys' ou assez équivoques pour l'utilisateur.
// ListManager vous permet de remplacer les titres de la base de données par d'autres lors de la génération de la liste de données.
// Cette fonctionnalité MODIFIE UNIQUEMENT L'AFFICHAGE DES TITRES, ce qui signifit que pour les callbacks / autres fonctionnalités
// utilisant les noms de colonnes, ce sont ceux de la base de données qui sont utilisés.


?>