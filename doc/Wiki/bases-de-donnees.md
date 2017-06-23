# Base de donnnées

La classe Database permet au développeur de se connecter à une ou plusieurs base de données dans son application web. Cette classe se base sur le design pattern 'multiton' qui consiste à enregistrer toutes les nouvelles instances de la classe dans un tableau static, et de pouvoir en récupérer une à tout moment avec un *getInstance()*.
Cette classe utilise l'objet PDO de PHP pour communqiuer avec les bases de données, vous pouvez consulter [le manuel PHP correspondant](http://php.net/manual/fr/book.pdo.php).

## Sommaire
	
  * [Instancier une connection](#instantiate)
  * [Gérer plusieurs bases de données](#plusieursBD)
  * [Lien entre Database & ListManager](#listManager)
  * [Exécuter des requêtes](#execute)

## <a name="instantiate"></a>Instancier une connection

#### Instancier une connection : PDO & DSN

Pour instancier une nouvelle connection à une base de données, il vous faut le DSN correspondant, un nom d'utilisateur et un mot de passe. Le DSN est une chaine de caractère spécifique à PDO, contenant l'ensemble des informations nécessaires pour se connecter telles que le type de BD, l'hôte, le port (souvent facultatif), l'utilisateur et son mot de passe.
Il est à noter que vous pouvez soit spécifier le nom d'utilisateur et son mot de passe dans le DSN, soit le séparer du reste. Ci-dessous un exemple de DSN :
```php
<?php
$dsnComplet = "typeBD:host=localhost;dbname=nom_base_donnees;port=1234;user=login;passwd=pass";
// Equivalent à ...
$dsn = "typeBD:host=localhost;dbname=nom_base_donnees;port=1234";
$login = "login";
$pass = "pass";
?>
```

#### Connecter une base de données avec Database

Pour créer un nouvel objet Database utilisez la méthode static *instantiate()*.
```php
<?php
$db = Database::instantiate($dsnComplet, null, null);
// Equivalent à ...
$db = Database::instantiate($dns, $login, $pass);
?>
```
La methode *instantiate()* vous retournera null si Database ne parvient pas à se connecter. Si c'est le cas, utilisez la méthode static *getErrorMessage()* pour récupérer le dernier message d'erreur retourné.
Dans le cas contraire l'objet Database est enregistré dans la classe et vous est retourné. Si vous n'utilisez qu'une seule BD dans votre application vous n'avez pas besoin de préciser son étiquette. L'étiquette par défaut est 'principale' et ne diffère que si vous la changez dans la méthode *instantiate()*.
Vous pourrez à tout moment récupérer votre instance de Database comme ceci :
```php
<?php
$db = Database::getInstance();
?>
```

## <a name="plusieursBD"></a>Gérer plusieurs bases de données

Si vous devez gérer plusieurs connection avec différentes base de données il vous faudra préciser une étiquette différente pour chacune d'entre elles. Cette étiquette sera l'identifiant qui vous permettra de récupérer une instance particulière avec la méthode static *getInstance()* :
```php
<?php // Création de 2 BD
$db1 = Database::instantiate($dsn1, $login1, $pass1, 'etiquette1');
$db2 = Database::instantiate($dsn2, $login2, $pass2, 'test');

// ... Récupération de la première BD :
$db = Database::getInstance('etiquette1');
?>
```
Vous pouvez respectivement changer et récupérer les étiquettes d'une base de données grâce aux méthodes *setLabel()* et *getLabel()*.

## <a name="listManager"></a>Préciser la BD à utiliser avec ListManager

L'objet ListManager à besoin que vous lui spécifiez quelle base de données vous voulez qu'il utilise pour éxécuter les requêtes SQL. La seule exception est si vous en utilisez qu'une seule et que vous n'avez pas changé son étiquette par défaut. Dans ce cas précis, ListManager récupèrera la base de données principale en utilisant la méthode *getInstance()* de Database.
Dans le cas contraire il faut spécifier une base de données, et pour se faire il existe plusieurs méthodes. Ci-dessous nous reprennons l'exemple de la section précédente et instancions ListManager avec la base de données n°1 :
```php
<?php// On suppose que $db1 et $db2 sont correctement instancié comme dans la secton précédente
// Méthode 1 : spécifier l'étiquette dans le constructeur
$lm = new ListManager('etiquette1');

// Méthode 2 : instancier ListManager puis spécifier la base de données après
$lm = new ListManager();
// avec l'objet Database ...
$lm->setDatabase($db1);
//.. OU son étiquette
$lm->setDatabase('etiquette1');
?>
```

### Sans passer par Database

ListManager propose une méthode qui vous évitera de passer par la classe Database pour vous connecter à une base de données. Cette méthode est *connectDatabase()* et prend en paramètres les mêmes arguments que la méthode *instantiate()* de Database. Cette fonction retournera true ou false selon si la connection s'est bien effectuée.
```php
<?php
$lm = new ListManager();
$lm->connectDatabase("dsn:host=localhost;dbname=test", 'login', 'pass', 'etiquette');
?>
```

## <a name="execute"></a>Exécuter des requêtes

### Avec *execute()*

Pour éxécuter des requêtes SQL de séléction et récupérer une liste de résultat le mieux est de passer par un objet [ListManager](ListManager) en utilisant la méthode *execute()*. Cependant il est également possible de passer directement par la classe Database, en utilisant la méthode du même nom. Cette méthode prend en paramètre la requête SQL quelqu'elle soit (objet SQLRequest ou string) et retourne un objet RequestResponse.
Exemple :
```php
<?php
$db = Database::getInstance();
$reponse = $db->execute("UPDATE personne SET prenom='Didier' WHERE id=1 ;");
 // Détection d'une erreur dans l'exécution
if($reponse->error())
	echo $reponse->getErrorMessage();
?>
```
Dans l'exemple si dessous nous récupérons la BD principale, puis nous lui demandons de modifier le prénom de la personne n°1. Si l'exécution provoque une erreur alors elle sera affichée. Je vous invite à consulter la PHPDoc concernant RequestResponse, notamment les méthodes *dataList()* et *nextLine()* qui retournent des données sélectionées.

### Préparer une requête avec des paramètres variables

Les méthodes permetttant d'exécuter des requêtes SQL présentées dans le paragraphe précédent peuvent présenter un risque d'injection SQL si vous utilisez des variables directememnt rentrées par l'utilisateur.
Pour palier ce risque la classe PDO propose deux méthodes *prepare()* et *execute()* (vous pouvez consulter le manuel en [cliquant ici](http://php.net/manual/fr/pdo.prepare.php).
La méthode *execute()* de Database vous permet de préparer vos requêtes. pour se faire il vous faut préciser un second paramètre correpsondant aux paramètres de la requête SQL, comme dans l'exemple suivant :
```php
<?php
// On récupère un id de personne depuis les données postées par l'utilisateur
$varID = $_POST['idPersonne'];
// on récupère l'instance de notre Database principale
$db = Database::getInstance();
// Préparation & exécution de la requete
$reponse = $db->execute("SELECT * FROM personne WHERE id = :id", array(':id' => $varID));
// On récupère la personne correspondante à l'id
$personne = $reponse->nextLine();
?>
```