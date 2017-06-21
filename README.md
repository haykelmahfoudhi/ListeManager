Refonte de PHPLib
=============================================

## Contexte

Mecaprotec utilise à l'heure actuelle une librairie de fonctions PHP pour la réalisation de sites de gestion peramettant principalement la visualisation de listes de données en interne. Cette librairie permet entre autre la *connexion à la base de données*, la *construction de requêtes* SQL à partir de données GET, la création et la gestion de *listes HTML* de données, l'exportation de données sous format *Excel*... Cependant certaines portions de code de la librairie ont été écrites il y a 14 ans (PHP v4) et de ce fait quelques unes d'entres sont *obselètes* ou inutiles.

## Objectifs

Les objectifs de ce projet étaient donc :

 * **Garder les mêmes fonctionnalités principales** que celles proposées par PHPLib, foncitonnalités qui sont :
   * Construction d'une liste HTML à partir de données selctionnées en BD
   * (Grâce aux paramètres GET) permet à l'utilisateur de :
     * *Exporter* les données en excel
     * *Masquer* des colonnes
     * *Filtrer* les données
     * *Trier* les données par colonnes
   * Possiblité pour le développeur de :
     * Désactiver cetaines fonctionnalités utilisateur
     * Modifier les données de la liste grâce à des callbacks
 * **Nouveaux objectifs** :
   * **Passer en orienté objet** version PHP 5.6
   * Utiliser une ou plusieurs bases de données (avec PDO)
   * Utiliser une ou plusieurs listes sur la meme page 

INTRODUCING THE BRAND NEW OBJECT THAT WILL SIMPLIFY THE DEVELOPPERS' LIFE : 

# ListManager

**ListManager** est le nom de la classe centrale du projet. Elle joue le rôle d'interface entre le développeur et les fonctionalités de la bibliothèque.
Un objet ListManager fonctionne à peu près comme une **factory** : en effet chaque objet possède un comportement de base (défini lors de sa création) qui peut être modifié via les setters de la classe. Une fois que vous avez défini le comportement de votre objet et/ou défini les callbacks pour modifier vos données, vous pourrez utiliser les méthodes *execute()* ou *construct()* afin de générer des listes de données sous 5 formes différentes (HTML, Excel, JSON, array PHP, objet ReponseRequest).

## Descriptif du projet

### Arborescence

 * **/**
   * **doc/** : contient toute la doncumentaion du projet
     * **PHPDoc/** : pages HTML de documentaion des classes du projet
     * **Wiki/** : fichiers MD des pages Wiki du projet
   * **lib/** : contient l'ensemble des classes du projet
   * **src/** : contient les fichiers accessibles côté client
     * **excel/** : ensemble des fichiers Excel générés
     * **css/** : css du projet
     * **img/** : images du projet
     * **js/** : fichiers script du projet
   * **PHPExcel/** : submodule git du projet PHP Excel
   * **pdooci/** : submodule git du projet PDO OCI (drivers oracles)
   * **includes.php** : à inclure à votre projet pour utiliser ListManager

### Classes

Le projet compte actuellement 5 **classes** et 2 *énumérations* :

  * **Database** : Multiton - permet la connexion et l'interaction avec les bases de données
  * **ListManager** : Permet de créer et de manipuler des listes de données à partir d'une requete
  * **ListTemplate** : Met en forme les données sélectionnées depuis la BD dans une liste HTML
  * **ReponseRequest** : Objet généré lors de l'exécution des requêtes par Database : contient les éléments retournés par la BD
  * **SQLRequest** : Parse et modifie les requêtes SQL pour filtrer / ordonner des données
  * *RequestType* : Enumération du type de requete SQL
  * *ResponseType* : Enumération du type d'objet que doit retourner ListManager


## Prise en main

Pour inclure ListManager à votre projet, clonnez ce repo à la racine de votre projet avec la commande 
`git clone <url>`
**/!\\** il faudra aussi cloner les sous modules PHPExcel et PDOOCI pour que tout fonctionne, utilisez la commande
`git submodule update --init`

Une fois clonné il vous faut définir la constante 'LM_ROOT' comme le chemin relatif vers la racine du dossier ListManager puis inclure le fichier 'includes.php' à vos pages.
Vous pourrez ensuite utiliser toutes les classes du projet. L'exemple suivant vous montre comment inclure ListManager, se connecter à une base de données et construire votre première liste avant de l'afficher.

```php
<?php

//Inclusion de ListManager
define('LM_ROOT', 'chemein/vers/ListManager/'); // Ne pas oublier le '/' à la fin
require_once LM_ROOT.'includes.php';

// Connexion à la base de données
$db = Database::instantiate('dsn:dbname=db;host=localhost', 'login', 'mot de passe');

// Création de l'objet requete SQL
$req = new SQLRequest("SELECT * FROM table;");

// Instantiation de ListManger
$lm = new ListManager($db); // avec une seule instance de Database le paramètre du constructeur est facultatif

// Construction de la liste & affichage
echo $lm->construct($req);

?>
``` 

Consultez les pages de documentation du projet ou le wiki de ce repo pour plus d'informations concernant ListManager.