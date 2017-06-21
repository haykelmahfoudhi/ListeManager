# Template

Cette page du Wiki traîtera de l'objet ListTemplate ainsi que de l'aspect graphique des listes générées par ListManager.

### Menu

 * [Structure du template](#structure)
 * [Modifier le style (CSS)](#style)
 * [Masquer / afficher le nombre de résultats](#resultats)
 * [Ajouter / supprimer une page d'aide](#aide)
 * [Modifier les couleurs des lignes](#couleurs-lignes)
 
## <a name='structre'></a> Structure du template

Le template par défaut de ListManager est découpé en 6 parties :

 * <span style="color:red;">**Tout en haut à gauche**</span> se trouve le nombre de résutltats récupérés et la tranche affichée, en rouge
 * <span style="color:cyan;">**A gauche**</span> se situent les boutons de la liste, en cyan. Dans l'ordre se toruvent :
   * Le bouton pour annuler le masquage des colonnes
   * Le bouton pour exporter en format Excel
   * Le bouton pour afficher / masquer les champs de recherche
   * Le bouton pour lancer la recherche (Go!)
   * Le lien vers la légende associée à la liste
   * La gomme qui permet de remettre à zéro la recherche et le tri
 * <span style="color:green;">**En haut**</span> se trouvent les titres des colonnes, en vert. Ces titres sont composées de :
   * Une croix rouge permettant à l'utilisateur de masquer la colonne. Pour annuler le masquage d'une colonne cliquez sur l'icone en forme de masque.
   * Le titre de la colonne cliquable : permet de trier par ordre croissant / décroissant. Pour revenir à l'ordenancement apr défaut de la liste, cliquez sur l'icône en forme de gomme.
 * <span style="color:purple;">**Juste en dessous des titres**</span> se trouvent les champs de saisie pour la recherche par colonne. Par défaut ces cahmps sont amsqués, pour les afficher il faut cliquer une fois sur l'icône en forme de loupe (si elle est disponible, sinon c'est que la fonctionnalité de recherche est désactivée pour cette liste) 
 * **Au centre** se trouve la liste de données
 * <span style="color:blue;">**En bas**</span> se trouve le tableau de pagination qui permet à l'utilisateur de naviguer à travers les pages de résultat, en bleu.
 
<img class="center" src="https://img4.hostingpics.net/pics/203151basecolor.png">

Il est important de noté que pour les besoins de l'exemple tous les boutons sont affichés sur cette illustration. Cependant par défaut seuls quelques uns d'entre eux ne s'affichent. Il est également possible de désactiver toutes les foncitonnalités de ListManager, et que par conséquent dans ce cas précis aucun de ces boutons ne s'affichera.

## <a name='style'></a> Modifier le style (CSS)

Ce template utilise par défaut un fichier CSS qui permet d'appliquer ce style par défaut. Si toutesfois vous souhaitez personnaliser l'aspect de vos listes, vous pouvez desactiver le style par défaut et importer vos propores fichiers CSS.
Pour se faire utilisez la ligne de code suivante :
```php
<?php
$lm = new ListManager();
$lm->applyDefaultCSS(false);
?>
```
La méthode *applyDefaultCSS()* permet de définir si le template doit ou non inclure le fichier CSS pour le style par défaut. Après avoir désactiver cette option, il vous suffira d'inclure votre propre feuille de CSS.

## <a name='resultats'></a> Masquer / afficher le nombre de résultats

Vous pouvez désactiver l'affichage du nombre de résultats (rectangle en rouge sur l'illustration). Par défaut cette option est activée. Pour se faire utilisez la méthode *displayNbResulsts()* :
```php
<?php
$lm = new ListManager();
$lm->displayNbResults(false);
?>
```

## <a name='aide'></a> Ajouter / supprimer une page d'aide

Vous pouvez inclure un lien url vers une page d'aide (externe ou locale) pour chacune de vos liste. Par défaut cette option est désactivée. Pour activer le lien faites ceci :
```php
<?php
$lm = new ListManager();
// Activer le lien vers la page d'aide (ici notre page c'est Google)
$lm->setHelpLink('http://www.google.com/');

// Désactiver le lien
$lm->setHelpLink(null);
?>
```

## <a name='couleurs-lignes'></a> Modifier les couleurs des lignes

ListManager permet d'attribuer des classes CSS différentes pour les lignes (balises tr) impaires et paires. Par défaut ces lignes portent les classes *orange* et *gris* et le CSS par défaut applique les couleurs correspondantes. Pour modifier ce paramètre utilisez la mméthode *setRowsClasses()* qui prend en paramètre le nouveau nom de la classe pour les lignes impaires, et celui pour les lignes paires. Par exemple :
```php
<?php
$lm = new ListManager();
// On modifie les classes des lignes par 'classe-impaires' et 'classe-paires'
$lm->setRowsClasses('classe-impaires', 'classe-paires');
?>
```
Ainsi pour modifier les couleurs (par exemple on met les lignes impaires en rouge et les paires en bleu) vous pouvez ajouter à votre CSS les lignes suivantes :
```css
tr.classe-impaires {
  background-color: red;
}
tr.classe-paires {
  background-color: blue;
}
```
 Pour désactiver cette option, mettez les valeurs des classes des lignes à null :
```php
<?php
$lm->setRowsClasses(null, null);
?>
```