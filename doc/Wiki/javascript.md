# ListManager : partie JavaScript

Lors de la génération de liste HTML ListManager inclut dans la page un fichier JavaScript associé se trouvant dans `src/js/listManager.js`. Ce fichier contient un ensemble de fonction utilisées notamment pour :
  * Masquer / démasquer les colonnes.
  * Fixer les titres des colonnes lorsque l'utilisateur scroll.
  * Afficher / masquer les champs de saisie pour la recherche.

Gérer ces fonctionalités en JavaScript plutôt qu'en PHP permet d'économiser un rechargement de page à chaque action, ce qui fait gagner un temps précieux de navigation, surtout si la page en question met du temps à ré-éxecuter la requête SQL à l'origine de la liste.

## Utilisation du sessionStorage

[SessionStorage](https://developer.mozilla.org/fr/docs/Web/API/Window/sessionStorage) est une variable globale de JavaScript qui permet de sauvegarder des données et de les utiliser tout le temps que l'onglet restera ouvert.
Le script de ListManager l'utilise pour mémoriser les colonnes masquées et l'affichage des champs de recherche et ce même si l'utilisateur recharge ou change de page (simplement en cliquant sur les liens / boutons de navigation).

Par ailleurs cette variable est écrite en JSON directement dans l'URL, après le '#'. Ce procédé permet aux utilisateurs d'enregistrer le lien (en marque page par exemple) et de réouvrir leur liste en la récupérant dans le même état qu'ils l'ont laissé, c'est-à-dire avec les colonnes masquées et les champs de saisie affichés / masqués. En effet sans ça, les données contenues dans le sessionStorage seront perdues à la fermeture de l'onglet et l'utilisateur devra refaire tous ses changements manuellement.

**L'inconvenient** majeur de ce procédé est que vous ne pouvez plus utiliser les [ancres HTML](https://www.w3schools.com/html/html_links.asp) sur une page utilisant ListManager, car tout ce qui se trouve derière le '#' de l'url sera remplacé par le contenu de sessionStorage.

## Masquage des colonnes

Le masquage des colonnes est la principale fonction assurée par le script. Si cette fonction est activée dans ListManager alors l'utilisateur pourra masquer les colonnes en cliquant sur la croix rouge à côté du titre de celle-ci. Au clic sur la croix rouge la fonction `masquerColonneOnClick()` est appelée, elle récupère le numéro de la colonne ainsi que l'identifiant de la liste parente et masque toute la colonne correspondante.
La variable globale sessionStorage est alors mise à jour : on y ajoute le numéro de la colonne qui vient d'être masquée.

Pour démasquer les colonnes l'utilisateur clique sur l'icone en forme de masque barré, ce qui déclenche la fonction `afficherColonnes()`. Cette fonction récupère la liste correspondante et affiche toutes les colonnes.

Lors de l'exportation des données en format Excel les colonnes masquées sont récupérées et sont envoyées dans les paramètres GET de l'url. ListManager récupère alros ces informations et supprime les colonnes correspondantes lorsqu'il génère le ficheir Excel.

## Fixer les titres

La fixation des titres de la liste lorsque l'utilisateur scroll est la seconde fonction assurée par le script. Cette fonctionnalité peut être desactivée en PHP par le developpeur, et ne concerne pas la fixation des numéros de pages qui est elle gérée en CSS.

**Fonctionnement** : lorsque l'utilisateur descend surffisamment sur la page pour que les titres n'y apparaissent plus le script leur applique alors une classe HTML 'fixed' ainsi qu'a la div contenant les boutons.
JavaScript actualise alors la largeur des colonnes des titres et de la liste (avec la fonction `actualiserLargeurCol()`), car en passant de non-fixés à fixés les titres perdent leur taille d'origine, dû à l'attribu CSS `position: fixed`.

Cette fonction est à l'origine d'un **bug d'affichage** : lorsque l'on clique plusieurs fois sur le bouton loupe les colonnes s'agrandissent à l'infini. Actuellement ce bug est fixé par une petite constante appelée `padding` dans la fonction`actualiserLargeurCol()`. S'il vient à ré-apparaitre modifiez sa valeur afin de retrouver le point d'équilibre. Ce bug peut en effet ré-apparaitre si vous modifiez le CSS des balises `th` ou `td`.