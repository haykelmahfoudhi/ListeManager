Refonte de PHPLib
=============================================

## Contexte

Mecaprotec utilise à l'heure actuelle une librairie de fonctions PHP pour la réalisation de sites de gestion peramettant principalement la visualisation de listes de données en interne. Cette librairie permet entre autre la *connexion à la base de données*, la *construction de requêtes* SQL à partir de données GET, la création et la gestion de *listes HTML* de données, l'exportation de données sous format *Excel*... Cependant quelques unes de ces fonctions ont été codées depuis la toute *première version de PHP* (il y a 14 ans) et de ce fait quelques unes d'entres sont *obselètes* ou inutiles.

## Mission

Vous pouvez consulter la page **Wiki** du projet dans le menu Wiki de ce repository, ou en [cliquant sur ce lien](http://list-manager.torchpad.com/)

La mission ici sera donc de refondre cette librairie en la rendant plus facile à manipuler pour le développeur. La nouvelle librairie PHPLib, renommée ListManager, consistera en 2 classes principales :
* Un objet **Database** basé sur le design pattern multiton, utilisant PDO et un certain nombre de drivers pour permettre la connection vers n'importe quel type de base de données
* Un objet **ListManager** possèdant un comportement de base, paramètrables via un ensemble de setters. Cet objet sera l'interface entre le développeur et la librairie.
    * **Comportement de base** : affichage d'une liste HTML avec les mêmes fonctionnalités que celles porposées par PHPLib à savoir : recherche, tri par colonne, export Excel, masquage colonnes
    * **Comportement modifiables** :
        * Type de données retournées parmi Liste HTML, Excel, objet ou array PHP, objet JSON
        * Affichage du template Liste HTML :
            * modification de l'id du tableau
            * modification classes pour les lignes paires/impaires
            * modification du nombre de lignes par page
            * activation / désactivation des fonctionnalités recherche, masque et order
            * modification des messages d'erreur / liste vide
        * Activation / désactivation d'un système de cache pour la navigation entre les pages pour les requêtes SQL lourdes *(à venir...)*
A voir pour la suite ... 
[?] Une API JSON

---------------------------------------------------------------

# AVANCEMENT DU PROJET
    
    [o] Phase de développement
        [o] Connection & interactions avec la BD
            [x] Multiton Database
            [x] Drivers Oracle / Postgre
        [x] Fonctionnalités liste
            [x] Masquage des colonnes / affichage cahmps saisie en JS
            [x] Réécriture des ORDER BY
            [x] Réécriture clauses where
                [x] Reconnaissance du type de données
                    [x] Gestion des dates (BETWEEN)
                [x] Clause LIKE
                [x] Reconnaissance des opérateurs < <= > >= % _ << , !
            [?] Système de cache pour naviguer entre les pages
                [ ] API php pour les requêtes AJAX
                [ ] Utilisation de $_SESSION
        [x] Export PHPExcel
        [?] API JSON pour exécuter des requêtes (SQL?) depuis une application externe
            [ ] Définir un protocole de communication & connextion aux bases de données
            [ ] Gestionnaire de sessions sécurisé
    [o] Phase de tests
    [o] Production de documentaiton
        [x] PHPDoc -> utilisation des classes et méthodes
        [o] Pages Wiki

-----------------------------------------------------------------

# Utilisation

## Inclure ListManager à votre projet

Pour utiliser les fonctionnalités de ListManager il faut l'inclure à votre projet PHP, en clonant ce repo à al racine de votre projet par exemple. Ensuite il faudra définir la constante **LM_ROOT** qui correspond au chemin relatif vers la racine de ListManager, et inclure le fichier includes comme ceci :
```php
<?php
define('LM_ROOT', 'path/to/ListManager/');
require LM_ROOT.'includes.php';
?>
```

## Créer une liste simple

Une fois ListManager inclut à votre projet il vous sera possible de créer votre preière liste HTML. Pour se faire vous aurez besoin d'un accès à une base de données et d'une requête SQL qui sélectionnera les données souhaitées. Le code ci-dessous vous permet d'afficher le contenu des attributs *a1 a2 et a3* de la table *table* :

```php
<?php

// Création de la requête SQL
$requete = "SELECT a1, a2, a3 FROM table WHERE 1";

// Instanciation de ListManager
$lm = new ListManager();

// Connection à la base de données & éxecution de la requete
$lm->connectDatabase('DSN PDO', 'utilisateur', 'mot_de_passe');
$html = $lm->construct($requete);

// Affichage de la liste
echo $html;
?>
```

## Modifier le comportement de ListManager

Si vous souhaitez modifier le comportement de l'objet ListManager, vous pouvez utiliser les méthodes de la classe en vous référant au Wiki de ce repo ou à la PHPDoc située dans le dossier doc/PHPDoc/.
