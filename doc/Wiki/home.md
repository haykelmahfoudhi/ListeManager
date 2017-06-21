Refonte de PHPLib
=============================================

Ce Wiki est à lire en complément de la PHPDoc fournie dans le dossier doc/PHPDoc de ce projet. Dans ces pages vous trouverez comment utiliser les classes du projet pour obtenir tel ou tel résultat, et il vous sera proposé des exemples de code pour faciliter la compréhension de ListManager.

# Menu du Wiki
	
  * [Présentation du projet](#contexte)
  * [[Installation|Installation]]
  * [[Bases de données|Bases de données]]
  * [[ListManager|ListManager]]
  * [[Template|Template]]
  * [[Gloassaire des erreurs|Gloassaire des erreurs]]

## <a name='contexte'></a> Présentation

Les principales classes du projet sont au nombre de 2 :
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