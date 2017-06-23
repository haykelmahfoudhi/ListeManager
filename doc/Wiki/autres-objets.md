# Autres objets

Cette page Wiki traîte des autres classes de la librairie, leur foncitonnement et leurs objectifs.

 * [Créer des requetes SQL avec SQLRequest](#sql-request)
 * [Réponse d'une requete : RequestResponse](#request-response)
 
## <a name='sql-request'></a> SQLRequest
 
La classe SQLRequest représente les requetes SQL à exécuter, et est utilisée en interne par *Database* et *ListManager*. Elle permet entre autre de rajouter des conditions dans la clause *where* (pour effectuer des recherches), ou de modifier la clause *order by*.
Cette classe a une méthode *toString()* quipermet de transformer l'objet en requete SQL sous forme de chaine de caractères.
 
### Constructeur
 
Le constructeur de la classe SQLRequest prend 2 arguments :
 
 * La requete SQL de base
 * (facultatif) Un booléen décrivant si la requete doit être préparée pour une base de données Oracle. Par défaut cette valeur vaut *false*
 
```php
<?php
$requete = new SQLRequest("SELECT t.*, t2.a1 FROM table t, t2 WHERE t2.id = t.id_t2 ORDER BY 1;");
?>
```
 
Ce constructeur va parser la base de la requête que vous lui avez passé en paramètre, tenter de reconnaitre son type (parmi SELECT, UPDATE, DELETE, INSERT INTO, ou AUTRE), et tenter de la découper en différentes partie (une partie ORDER BY, partie HAVING, partie WHERE, et enfin la base de la requete).
L'objectif de ce parsage est de pouvoir rajouter des clauses WHERE ou ORDER BY à celles déjà présentes dans la requete de base.
 
### Utilisation

#### Méthode filter

La méthode where de la classe permet d'ajouter des contditions au bloc where déjà existant. Elle prend en paramètre un array clé => valeur qui doit avoir pour format :

 * **Pour les clés** le nom de la colonne sur laquelle vous souhaitez poser la condition
 * **Pour les valeurs** la valeur de la recherche. Elle peut contenir un certain nombre de caractères spéciaux qui sont détaillés sur la page de [[presentation de la liste|Presentation liste]].

Les nouvelles conditions where sont ajoutées les unes à la suite des autres et sont séparées par des *'OR'*.

### Méthode orderBy

La méthode order by permet d'ajouter des colonnes ou numéro de colonnes à la clause déjà existante. Pour se faire passez lui en paramètre le tableau contenant le nom ou les numéro de colonnes à trier, ou simplement un seul numéro, ou un seul nom de colonne. Pour spécifier le fait que le tri doit être décroissant, rajoutez un '-' devant votre nom ou numéro de colonne.

#### Avec ListManger
 
Pour exécuter des requêtes SQL avec les méthodes *construct()* ou *execute()* de ListManager (ou avec la méthode *execute()* de Database) vous pouvez soit passer en argument directement la requête sous forme de chaine de caractère, soit lui passer un objet SQLRequest que vous avez créé auparavant.
L'avatange d'utiliser un objet SQLRequest est qu'il vous permettra de débugguer plus facilement.
Vous pouvez exécuter les requetes SQL que vous avez créé avec les méthodes *construct()* ou *execute()*, et aussi avec la méthode *execute()* de Database.

#### Déboggage

L'avantage de passer apr l'objet SQLRequest est la possibilité d'afficher la totalité de requete SQL qui a exécutée par ListManager avec un simple 'echo'.

```php
<?php
$req = new SQLRequest("SELECT * FROM table WHERE id > 5 ORDER BY a1");

// Execution de ListManager
$lm = new ListManager();
echo $lm->construct($req);

// Affichage de la requete SQL
echo "<br>$req<br>";
?>
```
Dans l'exemple suivant LitManger utilisera les données fournies dans les praramètres GET telles quqe le tabSelect pour recehrcehr parmi les résultats ou le ORDER BY. Faire un echo de l'objet SQLRequest vous permettra d'afficher la requête SQL exacte qui a été exécutée. 

--------------------------------------------

## <a name='request-response'></a> RequestResponse

L'objet RequestResponse est celui produit par Database lors de l'appel de la méthode *execute()*, mais c'est aussi celui retourné par *execute()* et *construct()* de ListManager lorsque le type de réponse choisi est 'OBJET'.
Cet objet a pour attribut un objet [PDOStatement](https://secure.php.net/manual/fr/class.pdostatement.php) et permet de récupérer els résutlats sélectionnés grâce aux méthode *nextLine()* et *dataList()* qui utilisent respectivement *fetch()* et *fetchAll()*. 


--------------------------------------------------
 
## <a name='session'></a> Session


-------------------------------------------------