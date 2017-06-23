# Presentation liste

## <a name='structre'></a> Structure des listes

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

## Masquer une colonne

Pour masquer (ou tout simplement cacher) une colonne entière afin de faciliter la lecture de liste, il vous suffit de cliquer sur la croix rouge à coté du titre de la colonne. Pour démasquer les colonnes que vous avez caché, cliquez sur le bouton en forme de masque avec une croix rouge.
Si aucune croix rouge n'est affichée à coté des titres c'est que cette option a été désactivée.

## Effectuer une recherche

Si l'option de recherche est activée vous aurez l'icone en forme de loupe affichée à gauche de votre écran. Cliquez dessus afin de faire apparaître les champs de saisie par colonne.
A partir des ces champs vous pouvez entrer des valeurs, puis cliquer sur le bouton Go! ou bien sur la touche entrée, et seules les lignes correspondantes aux valeurs entrées s'afficheront.
Il existe un certain nombre de caractères spéciaux qui permettent d'effectuer des recherches plus poussées, en voici la liste exhaustive :

 * **[vide]**x :
    * si vous ne précisez qu'une valeur sans préfixe, ListManager récupèrera toutes les lignes valant exactement 'x' dans cette colonne
 * **>**x :
    * plus grand que : ne fonctionne qu'avec des valeurs numériques, retourne toutes les données dont la valeur est supèrieure à 'x'
 * **>=**x :
    * supérieur ou égal à 'x', ne fonctionne qu'avec des valeurs numériques
 * **<=**x :
    * inférieur ou égal à 'x', ne fonctionne qu'avec des valeurs numériques
 * **<**x :
    * plus petit que 'x', ne fonctionne qu'avec des valeurs numériques
 * x1 **&lt;&lt;** x2 retourne l'ensemble des valeurs comprises entre x1 et x2 (inclus). Fonctionne avec les valeurs numériques, les dates et les chaines de caractères.
 * **!**x :
    * retourne toutes les valeurs diférentes de x. Si x est vide (c'est-à-dire que vous ne mettez que '!') alors on vous retournera l'ensemble des valeurs non nulles
 * blabla **%** :
    * le '%' est un joker : il peut être remplacé par n'importe quelle suite de caractère. Ainsi dans cet exemple on souhaite obtenir toutes les valeurs commençant par 'blabla'. Ce caractère correspond également à l'absence de caractère, ainsi la chaine 'blabla' est reconnue dans cet exemple.
 * blabla **_** :
    * de même que pour '%', le caractère '_' est un joker, à la différence près qu'il remplace un seul caractère. Ainsi dans cet exemple, nous cherchons toutes les valeurs commençant par 'blabla' et ayant un caractère supplémentaire. Par exemple les chaines 'blablaC' et 'blabla$' seront sélectionnées tandis que 'blabla' ou 'blablaXY' ne le seront pas.  
