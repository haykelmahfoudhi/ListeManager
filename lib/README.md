#API REST & Libraire de fonctions Mecaprotec
---------------------------------------------

##Contexte

Mecaprotec utilise à l'heure actuelle une librairie de fonctions PHP pour la réalisation de sites de gestion ou de visualisation en interne. Cette librairie permet entre autre la *connexion à la base de données*, la *construction de requêtes* SQL à partir de données GET, la création et la gestion de *listes (tableaux)* en HTML (utilisation de templates), l'importation de données sous format *Excel*... Cependant quelques unes de ces fonctions ont été codées depuis la toute *première version de PHP* (il y a 14 ans) et de ce fait quelques unes d'entres sont *obselètes* ou inutiles.

##Mission

La mission ici sera donc de réécrire cette librairie en la rendant plus facile à manipuler pour le développeur. La nouvelle librairie PHPLib sera composée de :
    * Un modèle permettant la connexion à la BD et l'exécution de requêtes SQL. Ce modèle sera accessible en interne grâce à une API qui retournera les données en JSON
    * Un controleur (classe PHPLib) qui sera l'interface entre le développeur et la librairie. Cette classe prendra en entrée une requête SQL et retournera une vue HTML.
    * Un template de base (vue) qui sera appelée et rempli par les données générées par le controleur.
    * Un ensemble d'outils et de fonctions PHP qui sont présentent dans la version 1 de la librairir
 
