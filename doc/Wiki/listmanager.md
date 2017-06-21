# ListManager

La classe ListManager représente l'objet central de ce projet. Il s'agit de l'interface entre le développeur et les fonctionnalités proposées par PHPLib. Son fonctionnement est simple : on lui passe une requete SQL de base qui est exécutée, et qui retourne les résultats sous 5 formes possibles (dans une liste HTML, dans un array PHP, dans un objet PHP, dans un document Excel ou en format JSON).
Comme expliqué précédemment ListManager possède un comportement de base, qui peut être modifié grâce aux méthodes de la classe.

## Menu

  * [Construct & execute](#construct-execute)
  * [Description du comportement de base](#base)
  * [Modifier un comportement](#modifs)
    * [Format de réponse](#formats)
    * [Modifier le template](#template)
    * [Desaction l'utilisation des paramètres GET](#get)
    * [Verbosité](#verbose)
  * [Utiliser un callback pour les lignes](#row-callback)
  * [Utiliser un callback pour modifier les cellules](#cell-callback)
  * [Utiliser un callback pour ajouter des colonnes](#column-callback)
  
---------------------------------------------------------

## <a name='construct-execute'></a> Construct & execute

Les méthodes *execute()* et *construct()* de la classe ont le même objectif en fonctionnement : elles prennent en paramètre une requete SQL, puis l'exécute et retourne des résultats dans le format qui lui est indiqué. La différence majeure entre les deux est que *construct()* va modifier la requête SQL que vous lui avez passé, et y rajouter un **order by** ou des conditions dans la clause **where**. Par défaut ListManager récupère les informations dans l'url (avec la variable GET) pour modifier la requete.
Ensuite *construct()* fait appel à *execute()* pour exécuter cette nouvelle requête et retourner les résultats. 
Ainsi, si vous ne souhaitez pas que votre requête ne soit modifiée utilisez la méthode *execute()*, sinon utilisez *construct()*. 

## <a name='base'></a> Description du comportement de base

  * **Base de données utilisée** : celle portant l'étiquette 'principale' ([cf. base de données](Bases+de+données#instantiate))
  * **Format des résultats retournés** : liste HTML dépendant du [Template](Template) de base
    * Nombre de lignes par page : 50
    * Nombre de liens de pages proposés : 10
    * Callback utilisé pour modifier l'affichage des résultats : aucun
  * **Utilisation des paramètres GET** : oui
  * **Fonctionnalité de recherche par colonne** : activée
  * **Fonctionnalité de tri par colonne** : activée
  * **Fonctionnalité d'export des données Excel** : activée
  * **Fonctionnalité de masquage des colonnes** : activée
  * **Verbosité** : activée

## <a name='modifs'></a> Modifier un comportement

### <a name='formats'></a> Changer le format de données retourné par ListManager

ListManager vous permet de récupérer une liste de données sélectionnées sous 5 formes différentes lorsque vous exécutez une des méthodes *construct()* ou *execute()* :
  * **Liste HTML (par défaut)** les méthodes vous retourneront un string contenant le tableau HTML qui contiendra toutes les données. Ce tableau dépend de l'objet [Template](Template) utilisé par ListManager.
  * **Array PHP** retourne un tableau PHP contenant toutes les données selectionnées
  * **Objet PHP** retourne un objet RequestResponse (voir la PHPDoc concernant la classe)
  * **Objet encodé en JSON** retourne un objet encodé en JSON ayant les attributs suivants :
    * -> *error* un booleen permettant de détecter si la requete a levé une erreur
    * -> *errorMessage* le message d'erreur associé, null si error == false
    * -> *data* le tableau contenant l'ensemble des données retournées par la requete. Null si error == true
  * **Tableau Excel** redirige la page vers le fichier Excel généré par ListManger à partir des données sélectionnées.

Pour changer le format de retour il vous faut utiliser la méthode *setResponseType()* :
```php
<?php
$sql = "SELECT * FROM personne;";
$lm = new ListManager();
// Retourne par defaut une liste HTML
$html = $lm->execute($sql);

// Retourne un array PHP
$lm->setResponseType(ResponseType::ARRAY);
$tabPersonnes = $lm->execute($sql); 

// Retourne un objet RequestResponse
$lm->setResponseType(ResponseType::OBJET);
$reponse = $lm->execute($sql);

// Retourne un objet encodé en JSON
$lm->setResponseType(ResponseType::JSON);
$json = $lm->execute($sql);

// Télécharge un fichier Excel contenant les données
$lm->setResponseType(ResponseType::EXCEL);
$lm->execute($sql);
?>
```

### <a name='template'></a> Désactiver certaines fonctionnalitées

ListManager propose des méthodes pour modifier son comportement ainsi que celui du [Template](Template) qu'il utilise. Pour se faire vous pouvez soit utiliser les méthodes de la classe et désactiver une à une les fonctionalités de votre choix, soit préciser des options directement dans le constructeur de la classe.
Dans l'exemple suivant nous allons désactiver les fonctionnalités de tri, de recherche, d'export Excel, et nous souhaitons que notre template n'affiche que 3 liens dans la pagination, désactiver les classes pour les lignes paires/impaires et afficher 100 lignes par page :

```php
<?php
// Désactiver les fonctionnalités : 2 méthodes
//    => Par le constructeur (le 1er argument correspond à la base de données) :
$lm = new ListManager(null, ListManager::NO_SEARCH,
    ListManager::NO_EXCEL,
    ListManager::NO_ORDER_BY);

//    => Par les méthodes de la classe : 
$lm->enableExcel(false);
$lm->enableSearch(false);
$lm->enableOrderBy(false);

// On change le comportement du template
$lm->setMaxPagesDisplayed(3);
$lm->setRowsClasses('lignes-impaires-bleues', 'lignes-paires-rouges');
$lm->setNbResultsPerPage(100);
?>
```
Consultez la documentaion de ListManager pour obtenir la liste complète des options de son constructeur, ainsi que l'ensemble des méthodes qui permettent de désactiver ses fonctionnalités. 


### <a name='get'></a> Desactiver l'utilisation des paramètres GET

La methode *constuct()* utilise par défaut les paramètres GET de l'url (*tabSelect* et *orderBy*) pour filtrer et trier les données selectionnées. Il vous est possible de désactiver ce comportement en utilisant la méthode *useGET()* de ListManager. De ce fait ListManager n'utilisera plus les paramètres du GET mais utilisera à la place ses attributs tabSelect et orderBy. par défaut ces attributs sont initialisés à null. Vous pouvez les définir avec les méthodes *setTabSelect()* et *setOrderBy()* comme ceci :
```php
<?php
$sql = "SELECT nom, prenom, age FROM personne;";
$lm = new ListManager();

// On désactive l'utilisation des paramètres GET
$lm->useGET(false);

// On remplace les arrays orderBy et tabSelect par de nouveaux
$tabSelect = ['nom' => 'C%', 'age' => '>=10'];
$orderBy = [1, -2];
$lm->setTabSelect($tabSelect);
$lm->setOrderBy($orderBy);

echo $lm->construct($sql);
?>
```

### <a name='verbose'></a> Verbosité

Le mode verbose est activée par défaut. Cela signifie que tous les messages d'erreurs générés lors de l'exécution seront **echo** dans la page web. Si vous ne souhaitez pas qu'ils s'affichent (dans le but de cacher les erreurs éventuelles pour les besoins de la prod) il vous suffit de désactiver ce mode avec la méthode *verbose()*. Les messages d'erreurs sont tous enregistrés dans un attribut de l'objet ListManager et peuvent être récupérés dans un array via la méthode *getErrorMessages()* :
```php
<?php
$lm = new ListManager();
// Désactiver l'affichage des messages
$lm->verbose(false);
// Affichage des messages d'erreur enregistrés
$tabMessages = $lm->getErrorMessages();
print_r($tabMessages);
?>
```

## <a name='row-callback'></a> Utiliser un callback pour les lignes

Il arrive parfois que vous souhaitiez modifier l'aspect graphique d'une ligne particulière en fonction par exemple des données qu'elle contient. ListManager permet cette fonctionnalité grâce à un système de callbacks (fonction de rappel), qui sera appelé lors de la création des balises *tr* correspodnantes aux lignes de votre liste.
Pour se faire il vous faudra créer une fonction php et passer le nom de cette fonction en paramètre de la méthode *setRowCallback()* de ListManager.

### Format du callback

Le callback à définir doit respect un certain format à savoir :

 * **Avoir deux arguments** en entrée correspondant à :
   * 1. Le numéro de la ligne en cours
   * 2. Un array associatif contenant l'ensemble des données de la ligne en cours, la clé de chaque donnée étant le titre de la colonne correspondante.
 * **Retourner une chaine de caractère** qui sera contenu à l'intérieur de la balise tr

### Exemple

Dans cet exemple nous souhaitons séléctionner la liste des étudiants inscrits dans notre base de données. Ces étudiants peuvent être soit *admis* à leurs examens, soit *recalés*. Nous souhaitons donc faire apparaître les lignes en **rouge** pour tous ceux qui n'ont pas été admis, et en **vert** pour les autres. Pour se faire nous allons attribuer la classe HTML *rouge* ou *vert* et avant cela, nous allons désactiver l'utilisation des classes que le template attribut par défaut une ligne sur deux.
De plus nous souhaitons ajouter un listener 'onclick' qui fera apparaître le numéro de la ligne sur laquelle l'utilisateur a cliqué (pas très utile tout ça mais c'est pour l'exemple).
Voici le code :

```php
<?php
// Préparation de la requete SQL
$requete = "SELECT num_etudiant, nom, prenom, admis FROM etudiant";

// Création de l'objet ListManager
$lm = new ListManager();
// Désactivation de l'utilisation des classes des lignes par défaut
$lm->setRowsClasses(null, null);
// On masque la colonne 'admis'
$lm->setMask(array('admis'));

// Création du callback
function modifAttributsTR($numLigne, $etudiant) {
	// Ajout du listener onclick
	$attr = 'onclick="alert(Vous avez cliqué sur la ligne n°$numLigne);"';
	// Modificaiton de la couleur via la classe de la ligne
	if($etudiant['admis'] == true) {
		$attr .= ' class="vert"';
	}
	else {
		$attr .= ' class="rouge"';
	}
	return $attr;
}

// On définit le callback à utiliser pour ListManager
$lm->setRowCallback('modifAttributsTR');

// Exécution & affichage de la liste
echo $lm->execute($requete);
?>
```
Pour que le scouleurs des lignes marche il faut penser à ajouter à votre CSS les lignes suivantes :

```css
tr.vert {
  background-color: green;
}
tr.rouge {
  background-color: red;
}
```

Le code HTML qui sera généré est le suivant (l'exemple suivant correspond à une ligne affichée pour un étudiant admis dans la liste générée) :

```html
<tr onclick="alert(Vous avez cliqué sur la ligne n°2);" class="vert">
<td>123456</td><td>MARTIN</td><td>Paul<td>
</tr>
```

## <a name='cell-callback'></a> Utiliser un callback pour les cellules

Il arrive parfois que vous ayez besoin de modifier ou de traiter les données brutes extraites de la base de données avant de les afficher dans la lsite HTML. Pour se faire ListManager vous permet d'utiliser des callbacks pour modifier le contenu des cellules (*balises td*) de vos listes HTML afin de, par exemple, créer des liens HTML ou bien insérer des images etc... Pour se faire il vous faudra définir une fonction (le callback) et d'indiquer à ListManager le nom de votre fonction afin qu'il l'utilise.

### Format du callback

Les callbacks utilisés par ListManager doivent :
 * **Avoir 4 paramètres** en entrée qui correspondent respectivement à :
   * Le contenu brut de la cellule : c'est-à-dire la donnée directement extraite de la base de données.
   * Le titre de la colonne en cours.
   * Le numéro de la ligne en cours.
   * Un array associatif contenant l'ensemble des données de la ligne en cours ('titre colonne' => 'valeur').
 * **Retourner le nouveau contenu de la cellule** sous forme de chaine de caractère. Ce contenu peut être du code HTML ou une chaine de caractère, ou même un cript JavaScript... Vous pouvez changer absolument tout le contenu de vos cellules via le callback.
Si ce callback ne retourne rien (ou null) pour une cellule en particulier alors ListManager affichera automatiquement le contenu brut de la cellule. Si vous souhaitez laisser la cellule en question vide, retrounez false.

### Exemple

Dans cet exemple nous allons seléctionner des données dans la table *personne* de notre base de données. Cette table possède les colonnes *id*, *nom*, *prenom*, *lien_blog* et *chemin_avatar*. Nous souhaitons masquer la colonne id mais pouvoir récupérer cette information afin d'en faire un lien vers une autre page (la fiche détaillée de la personne), et aussi, nous souhaitons qu'à la place des colonnes *lien_blog* et *chemin_avatar* nous puissons afficher respectivement un lien vers le blog de la personne (si disponible) et son avatar en image :
```php
<?php
// Préparation de la requete SQL
$req = "SELECT id, nom, prenom, lien_blog, chemin_avatar as avatar FROM personne LIMIT 2000";
// On instancie notre LM, on suppose ici que nous n'avaons qu'une seule base de données etiquettée 'principale'
$lm = new ListManager();

// Création du callback
function callbackPersonne($contenu, $titre, $numLigne, $personne) {
  // Si la colonne est lien_blog et que le lien est non null on affiche un lien html 
  if($titre == 'lien_blog') {
    if($contenu != null) {
      return "<a href='$contenu'>Lien vers mon blog</a>";
    }
    else { // Affichera un simple '-' s'il n'y a pas de lien
      return false;
    }
  }
  // Si la colonne est avatar alors on affiche l'avatar de la personne
  else if($titre == 'avatar') {
    return "<img src='$contenu' alt='avatar' height='50' width='50'>";
  }
  // Pour les colonnes nom et prenom on crée un lien vers la page de la personne via son id
  else if ($titre == 'nom' || $titre == 'prenom') {
     return '<a href="lien/vers/fiche?&id_personne='.$personne['id'].'">'.$contenu.'</a>';
  }
  // Sinon on retourne simplement le contenu de la cellule
  else 
    return $contenu;
}

// On passe à ListManager le callback à executer
$lm->setCellCallback('callbackPersonne');
//On Indique à LM qu'on masque la colonne 'id'
$lm->setMask(array('id'));
// Execution requete + affichage de la liste
echo $lm->construct($req);
?>
```

### Notes

Il est important de noter que le paramètre 'titre' du callback correpsond au nom de la colonne qui sera reellement affiché, c'est à dire dans notre exemple la colonne chemin_avatar possède un alias 'avatar' dans la requete SQL. Ainsi lors de l'affichage de la liste seul l'alias sera affiché, et donc le nom de la colonne deviendra 'avatar'. Ainsi dans votre callback pour cibler les données contenues dans cette colonne en particulier vous devez utiliser le même titre qui sera affiché **en respectant scrupuleusement la casse**.

## <a name='column-callback'></a> Utiliser un callback pour ajouter des colonnes

ListManager propose un troisième type de callback pour modifier vos listes de données. Celui-ci s'exécute après chaque fin de ligne, et vous permet de rajouter des colonnes à votre liste, ou tout autre élément HTML à l'intérieur des balises *tr* et à la suite de la dernière balise *th* ou *td* insérée.
Pour définir votre callback à utiliser par ListManger utilisez la méthode *setColumnCallback()*.

### Format du callback

La fonction à utiliser doit respecter le format suivant :

 * **3 paramètres en entrée** dans l'ordre :
   * *Le numéro de la ligne en cours*
   * *Un array associatif contenant les données de la ligne en cours ('titre colonne' => 'valeur')*. Lorsque le callback est appelé dans la ligne des titres ce paramètre est l'array contenant tous les titres de la liste.
   * *Un booléen* qui détermine si le callback a été appelé dans la ligne des titres (balises *th*, dans ce cas il vaut **true**), ou bien dans les autres lignes de la liste (balises *td*, valeur du paramètre : **false**)
 * **Retourner les colonnes à ajouter** sous forme de string, ou null si vous ne souhaitez rien inséerer.
 
### Exemple

Dans l'exemple suivant nous avons sélectionnons dans notre base de données une liste de produits. L'ensemble des attributs de la table nous imorte peu, on ne considère que le numéro du produit comme utile (il nous servira d'identifiant pour la suppression). Nous souhaitons utiliser un callback de colonnes pour ajouter une colonne contenant un logo 'suppression' qui peremttra à l'utilisateur de supprimer le produit de la base de données. Lorsque l'utilisateur cliquera sur le logo, un message de confirmation apparaîtra et si l'utilisateur clique sur ok, il sera redirigé vers la page de suppression du produit.
Voici le code correspondant :

```php
<?php
$lm = new ListManager();

// Création du callback
function callback_colonnes($numLigne, $produit, $estTitre) {
  // Ajout du titre de la colonne
  if($estTitre) {
    return '<th>Suppression</th>';
  }
  // Ajout de la colonne de suppression
  else {
    return "<td><a onclick='return confirm(\"Voulez-vous supprimer le produit N°".$produit['numero_produit']
      .'?");\' href="/supprimer?&id_prod='.$produit['numero_produit'].'"><img src="img/suppression.png"></a></td>';
  }
}

// On ajoute le callback à ListManager
$lm->setColumnCalback('callback_colonnes');
// Exécution de la requete & affichage de la liste
echo $lm->construct("SELECT * FROM produits");
?>
```