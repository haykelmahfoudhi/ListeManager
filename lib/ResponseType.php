<?php


/**
 * Enumération des formats de réponse possible de ListManager
 * Cette classe ne contient que des constantes exprimant le tyupe de données renvoyées par les méthodes *construct()* et *execute()* de la classe ListManager.
 * Les valeurs possibles sont : 
 * * TEMPLATE (par defaut) pour obtenir un string representant la liste HTML contenant toutes les donnees 
 * * ARRAY pour obtenir les resultats dans un array PHP (equivalent a PDOStaement::fetchAll())
 * * JSON pour obtenir les donnees dans un objet encode en JSON
 * * EXCEL pour obtenir les resultats dans une feuille de calcul Excel
 * * OBJET pour obtenir un objet \stdClass
 * 
 * @author RookieRed
 *
 */
class ResponseType {
	/**
	 * @var const pour retourner un objet php contenant le résultat de la requete
	 */
	const OBJET = 1;
	/**
	 * @var const pour retourner un array php contenant les lignes selectionnées
	 */
	const TABLEAU = 2;
	/**
	 * @var const pour générer une feuille de calcul Excel contenant les données selectionnées
	 */
	const EXCEL = 3;
	/**
	 * @var const pour utiliser le ListTemplate
	 */
	const TEMPLATE = 4;
	/**
	 * @var const pour renvoyer les données sous format JSON
	 */
	const JSON = 5;
}

?>