<?php


/**
 * Enumération des types de requete SQL.
 * Cette classe ne contient que des constantes permettant de définir le type des requetes SQL parmi :
 * * SELECT
 * * DELETE
 * * UPDATE
 * * INSERT INTO
 * * AUTRE pour tous les types qui ne peuvent pas rentrer dans les cases précédentes
 * 
 * @author RookieRed 
 */
class RequestType {
	/**
	 * @var const type assigné aux requêtes qui sélectionnent des données.
	 */
	const SELECT = 0;
	/**
	 * @var const type assigné aux requêtes supprimant des données.
	 */
	const DELETE = 1;
	/**
	 * @var const type assigné aux requêtes qui mettent à jour des données.
	 */
	const UPDATE = 2;
	/**
	 * @var const type assigné aux requêtes qui ajoutent des données.
	 */
	const INSERT = 3;
	/**
	 * @var const type assigné aux requêtes dont le type n'est aps reconnu.
	 */
	const AUTRE  = -1;
}