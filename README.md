Refonte de PHPLib
=============================================

## Contexte

Mecaprotec utilise à l'heure actuelle une librairie de fonctions PHP pour la réalisation de sites de gestion peramettant principalement la visualisation de listes de données en interne. Cette librairie permet entre autre la *connexion à la base de données*, la *construction de requêtes* SQL à partir de données GET, la création et la gestion de *listes HTML* de données, l'exportation de données sous format *Excel*... Cependant quelques unes de ces fonctions ont été codées depuis la toute *première version de PHP* (il y a 14 ans) et de ce fait quelques unes d'entres sont *obselètes* ou inutiles.

## Mission

La mission ici sera donc de refondre cette librairie en la rendant plus facile à manipuler pour le développeur. La nouvelle librairie PHPLib, renommée ListManager, consistera en :
* Un objet ListManager possèdant un comportement de base, paramètrables via un ensemble de setters. Cet objet sera l'interface entre le développeur et la librairie.
    * **Comportement de base** : affichage d'une liste HTML avec les mêmes fonctionnalités que celles porposées par PHPLib à savoir : recherche, tri par colonne, export Excel, masquage colonnes
    * **Comportement modifiables** :
        * Type de données retournées parmi Liste HTML, Excel, objet ou array PHP, objet JSON
        * Affichage du template Liste HTML :
            * modification de l'id du tableau
            * modification classes pour les lignes paires/impaires
            * modification du nombre de lignes par page
            * activation / désactivation des fonctionnalités recherche, masque et order
            * modification des messages d'erreur / liste vide
        * Activation / désactivation d'un système de cache pour la navigation entre les pages pour les requêtes SQL lourdes
* Un objet Database basé sur le design pattern multiton, utilisant PDO et un certain nombre de drivers pour permettre la connection vers n'importe quel type de base de données
* Une API JSON permettant l'interaction entre AJAX (ou application non PHP) d'interagir avec une base de données.
* [?] Un objet Session .. conception à venir
* [?] Un patron MVC léger facilement implémentable pour créer rapidement un petit ensemble de pages dans un site web dynamique.

---------------------------------------------------------------

# AVANCEMENT DU PROJET
    
    [o] Phase de développement
        [o] Connection & interactions avec la BD
            [x] Multiton Database
            [ ] Drivers Oracle / Postgre
        [o] Fonctionnalités liste
            [x] Masquage des colonnes / affichage cahmps saisie en JS
            [x] Réécriture des ORDER BY
            [x] Réécriture clauses where
                [x] Reconnaissance du type de données
                    [x] Gestion des dates (BETWEEN)
                [x] Clause LIKE
                [x] Reconnaissance des opérateurs < <= > >= % _ << , !
            [ ] Système de cache pour naviguer entre les pages
                [o] API php pour les requêtes AJAX
                [ ] Utilisation de $_SESSION
        [ ] Export PHPExcel
        [ ] API JSON pour exécuter des requêtes (SQL?) depuis une application externe
            [ ] Définir un protocole de communication & connextion aux bases de données
            [ ] Gestionnaire de sessions sécurisé
    [ ] Phase de tests
    [ ] Production de documentaiton
        [x] PHPDoc -> utilisation des classes et méthodes
        [ ] Pages Wiki

-----------------------------------------------------------------

# Utilisation

## Inclure ListManager à votre projet

Pour utiliser les fonctionnalités de ListManager il faut tout d'abord inclure la bibliothèque à votre projet PHP. Pour se faire 2 méthodes sont possibles : 
* **Vous n'utilisez pas autoload** : il vous suffira d'inclure le fichier "includes.php" à votre projet, de manière à ce qu'il soit accessible partout dans vote projet.
* **Vous utilisez autoload** : il vous faudra inclure le fichier "includes.php" ainsi que penser à inclure le dossier contenant l'ensemble des classes de ListManager et représenté par la constante 'LM_LIB' à votre fonction autoload.

```php
<?php
require PATH_TO_LISTMANAGER.'includes.php';
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

Si vous souhaitez modifier le comportement de l'objet ListManager, vous pouvez utiliser les méthodes de la classe en vous référant à la PHPDoc accessible dans le dossier doc/PHPDoc/.
