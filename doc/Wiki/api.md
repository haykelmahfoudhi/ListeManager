API de ListManager
================================

ListManager dispose désormais de son **API**!
Le but de celle-ci est de proposer un point d'entrée afin d'intérargir simplement avec n'importe quelle base de données de Mecaprotec. Pour l'utiliser il faut vous rendre sur la page `api.php`.

## Principe de fonctionnement

Cette API utilise un protocole de connexion avec session sécurisée en 3 temps :
  * **Phase de connexion** : l'utilisateur se connecte à la base de données de son choix.
  * **Interraction avec la base de données** : l'utilisateur peut alors exécuter des requêtes SQL et récupérer les résultats sous format objet JSON (par défaut) ou sous le format de son choix parmi fichier Excel, tableau JSON ou enfin liste HTML.
  * **Déconnexion** : l'utilisateur se déconnecte et sa session est détruite. Il peut alors se reconnecter à une autre base de données.

### Paramètres utilisables

Les paramètres utilisés par cette API sont les paramètres d'url GET. Ci-dessous la liste de tous les paramètres reconnus :
  * **dsn** peut être accompagné par **user** et **pass** : à utiliser pour se connecter sur une base de donnée non enregistrée.
  * **label** : permet de se connecter sur une base de données enregistrées au préalable.
  * **sql** : une fois connecté, contient les requête SQL à exécuter. Vous pouvez également spécifier un tableau de paramètres avec **params**.
  * **type** : lors de l'exécution d'une requete vous pouvez spécifier un type parmi 'TABLEAU', 'EXCEL' ou 'TEMPLATE'
  * **save** : enregistre la connexion en cours dans le fichier de conf sous le nom indiqué.
  * **disconnect** : permet de se déconnecter et de quitter la session
  * A cela s'ajoute tous les paramètres utilisés nativement par ListManager tels que **lm_tabSelect[]** pour le filtrage ou **lm_orderBy** pour le tri.

### Format de réponse

De base l'api vous renvoie un *objet encodé en JSON* possédant 3 attrivus :
  * **error** : booléen valant true si une erreur s'est glissée dans votre requete HTTP ou SQL.
  * **errorMessage** : string correspondant au message associé à l'erreur, vaut null ou peut ne pas être présent s'il n'y a pas d'erreur
  * **data** : array contient les données renvoyées par l'API et/ou la base de données, peut valoir null en cas d'erreur.

## Connexion / déconnexion

L'api vous propose 2 modes de connexion:
  * Par DSN + user + pass
  * Par label si les données de configuration se trouvent dans le fichier `src/dbs.json`

La première méthode correspond exactement à celle utilisée lorsque vous vous connectez avec [Database](home#database). Vous avez juste à spécifier le DSN correspondant et le nom d'utilisateur

#### Exemples :

**Connexion OK via DSN :**

Requête : `http://diego/ListManager/api.php?dsn=oci:dbname=MECAPROTEC&user=example&pass=mot_de_passe_super_chou3tt3`<br>
Réponse : 
```json
{
  error: false,
  errorMessage: null,
  data: [true]
}
```

**Connexion refusée via label :**

Requête : `http://diego/ListManager/api.php?label=mauvais_label`<br>
Réponse : 
```json
{
  error: true,
  errorMessage: "Impossible de se connecter à la base de données",
  data: [false]
}
```

### Sauvegarde

Les configurations des bases de données sauvegardées sont inscrites en JSON dans le fichier `src/dbs.json` sous le format suivant :
```json
{
  label1: {
    dsn: "mysql:dbname=test;charset=utf8",
    login: "user",
    passwd: "pass"
  },
  label2: {
    // ....
  }
}
```
Pour enregistrer votre configuration vous devez spécifier un label via le paramètre 'save'. Ce paramètre doit être unique, et si vous entrez un nom déjà utilisé l'api vous renverra une erreur.

Exemple : `http://diego/ListManager/api.php?save=nom_conf`.

## Exécution de requete

Une fois connecté vous pourrez exécuter n'importe quelle requete SQL en spécifiant le paramètre 'sql'. Vous povez modifier el type de réponse avec le paramètre 'type'.

### Exemples

**1 :** Requete : `http://diego/ListManager/api.php?sql=SELECT 1 AS test FROM dual`<br>
Réponse : 
```json
{
  error: false,
  data: {test: 1}
}
```

**2 :** Requete : `http://diego/ListManager/api.php?sql=erreur de syntaxe`<br>
Réponse : 
```json
{
  error: true,
  errorMessage: "Syntax error"
  data: null,
}
```