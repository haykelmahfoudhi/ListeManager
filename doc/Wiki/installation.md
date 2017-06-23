# Inclure ListManager

Pour utiliser ListManager et l'ensemble de ses classes vous pouvez clonner le projet, et inclure le dossier à la racine de votre projet.

## Inclure ListManager à votre projet

ListManager utilise autoload pour inclure automatiquement les classes du projet. Pour utiliser les classes de ListManager il vous suffira d'inclure le fichier "includes.php" de manière à ce qu'il soit accessible partout dans votre projet.
```php
<?php
require PATH_TO_LISTMANAGER.'includes.php';
?>
```

## Créer une liste simple

Une fois ListManager inclu à votre projet il vous sera possible de créer votre preière liste HTML, pourvu que vous ayez un accès base de données. Pour se faire vous aurez besoin d'un accès à une base de données et d'une requête SQL qui sélectionnera les données souhaitées. Le code ci-dessous vous permet d'afficher le contenu des attributs *a1 a2 et a3* de la table *table* :

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

[Comment se connecter à la base de données ?](base+de+donnees)

## Modifier le comportement de ListManager

Si vous souhaitez modifier le comportement de l'objet ListManager, vous pouvez utiliser les méthodes de la classe en vous référant à la PHPDoc accessible dans le dossier doc/PHPDoc/.
Les pages de ce Wiki vous indiqueront aussi comment obtenir tel ou tel résultat avec des exemples de code fournis.

[Modifier son objet ListManager](listmanager)