# Glossaire des erreurs

Cette page est consacrée aux messages erreurs qui peuvent être affcihés par ListManager, et aux solutions qui peuvent y être apportées.
L'affichage des erreurs est activé par défaut pour ListManager. Pour désactiver ce mode verbeux utilisez la méthode *verbose()*. Vous pourrez à tous moments récupérer les messages d'erreurs générés grâce à la méthode *getErrorMessage()* :
```php
<?php
$lm = new ListManager();
//Désactive les messages d'erreur
$lm->verbose(false);

// Afficher tous les messages
var_dump($lm->getErrorMessages());
?>
```
Vous pouvez consulter la [page du wiki](Listmanager#verbose) consacré à *verbose* pour plus d'informations.

## Erreurs provoquées par Database

 * **Connection a la base de donnees impossible (etiquette = 'principale')**

##### Provenance

Ce message d'erreur est provoqué par le constructeur de Database si son attribut PDO ne peut être instancié. il peut s'agir d'une erreur dans le dsn, dans le login ou bien dans le mot de passe que vous avez spécifiez dans la méthode *instantiate()* de Database ou bien dans la methode *connectDatabase()* de ListManager.

##### Solution(s)

Assurez vous que les identifiants à votre de base de données sont correctement entrés.

 * **Il existe deje une BD portant l'etiquette 'principale'**

##### Provenance

Cette erreur est provoquée si vous tentez de créer une nouvelle instance de Database en réutilisant une étiquette qui existe déjà. En effet les bases de données de l'applications se différencient dans la classe Database par leur étiquette, il est donc impossible d'avoir plusieurs instances de Database portant la même.

##### Solution(s)

Vérifiez que vous avez spécifié des étiquettes différentes pour toutes les nouvelles instances de Database (que ce soit avec les méthodes *Database::instantiate()* ou *ListManager::connectDatabase()* ). Notez par ailleurs que si vous ne spécifiez pas d'étiquettes par ces méthodes la, l'étiquette de la base de données sera par défaut 'principale', et que de ce fait il vous faudra impérativmezent spécifier des étiquettes différentes pour toutes les autres base de données que vous instancierez.

 * **Database::execute() (etiquette = 'principale') ...**

##### Provenance

Cette erreur est provoquée par la méthode execute de la classe Database et introduit le message d'erreur provoqué par l'exécution d'une requete SQL. Le plus souvent cette erreur est dûe à une erreur de syntaxe dans la requete que vous essayez d'exécuter.

##### Solution(s)

Référez-vous au manuel SQL à partir de l'erreur et de son code afin de corriger votre requête SQL.

## Erreurs provoquées par ListManager

 * **Veuillez définir la constante LM_ROOT avant d'utiliser ListManager**

##### Provenance

Cette erreur se produit si vous incluez ListManger sans avoir définit la constante LM_ROOT qui correspond au chemin relatif jusqu'à la racine de ListManager. Si cette constante n'est aps définie ListManger ne peut marcher correctement, et stop le script via un *die* 

##### Solution(s)

Je vous invite à consulter la page de ce wiki qui traite de [l'installation du projet](installation).

 * **ListManager::execute() : aucune base de donnees n'est disponible ou instanciée**

##### Provenance

Cette erreur est provoquée apr la méthode *construct()* ou *execute()* de ListManager, et traduit le fait qu'aucune instance de Database n'a été définie dans la classe. Cela signifit donc que ListManager n'a aucun objet Database pour exécuter al requête que vous tentez de lui passer en paramètres.

##### Solution(s)

Instanciez une connection à une base de données ou bien précisez une instance de Database à utiliser pour votre objet ListManager. Vous pouvez vous réferer à la page concernant la [conncetion aux bases de données](base-de-donnees#gérer-plusieurs-bases-de-données) pour plus d'informations  

 * **ListManager::execute() : le fichier excel n'a pas pu être généré**

##### Provenance

Cette erreur est provoquée si vous tentez d'exporter des données en format Excel. Les causes peuvent être multiples, mais la plupart du temps il s'agit des droits du dossier src/excel/ qui ne permettent pas à php de créer un nouveau fichier. Sinon l'erreur peut être simplement provoquée par une erreur lors de l'exécution de la requête SQL par ListManager

##### Solution(s)

Changez les drotis du dossier src/excel/ et assurez vous que votre requête SQL retourne des résultats.
