<?php

/**************************************************************************************************
**                                                                                               **
**                        88           88                                                        **
**                        88           ""               ,d                                       **
**                        88                            88                                       **
**                        88           88  ,adPPYba,  MM88MMM  ,adPPYba,                         **
**                        88           88  I8[    ""    88    a8P_____88                         **
**                        88           88   `"Y8ba,     88    8PP"""""""                         **
**                        88           88  aa    ]8I    88,   "8b,   ,aa                         **
**                        88888888888  88  `"YbbdP"'    "Y888  `"Ybbd8"'                         **
**                                                                                               **
**  88b           d88                                                                            **
**  888b         d888                                                                            **
**  88`8b       d8'88                                                                            **
**  88 `8b     d8' 88  ,adPPYYba,  8b,dPPYba,   ,adPPYYba,   ,adPPYb,d8   ,adPPYba,  8b,dPPYba,  **
**  88  `8b   d8'  88  ""     `Y8  88P'   `"8a  ""     `Y8  a8"    `Y88  a8P_____88  88P'   "Y8  **
**  88   `8b d8'   88  ,adPPPPP88  88       88  ,adPPPPP88  8b       88  8PP"""""""  88          **
**  88    `888'    88  88,    ,88  88       88  88,    ,88  "8a,   ,d88  "8b,   ,aa  88          **
**  88     `8'     88  `"8bbdP"Y8  88       88  `"8bbdP"Y8   `"YbbdP"Y8   `"Ybbd8"'  88          **
**                                                           aa,    ,88                          **
**                                                            "Y8bbdP"                           **
**************************************************************************************************/

/**
 * 
 * @author RookieRed
 *
 */
class ListeManager {
	
			/********************
			***   ATTRIBUTS   ***
			********************/
	/**
	 * 
	 */
	private $typeReponse;
	/**
	 * 
	 */
	private $template;
	/**
	 * 
	 */
	private $db;
	/**
	*
	*/
	private $utiliserGET;
	/**
	*
	*/
	private $tabWhere;
	/**
	*
	*/
	private $orderBy;
	/**
	*
	*/
	private $masque;
	
	
			/***********************
			***   CONSTRUCTEUR   ***
			***********************/

	public function __construct(){
		$this->typeReponse = TypeReponse::TEMPLATE;
		$this->template = new TemplateListe();
		$this->utiliserGET = true;
		$this->tabWhere = null;
		$this->masque = null;
		$this->orderBy = null;
		$this->recherche = true;
		$this->db = Database::getInstance();
		// Si la db est null alors on affiche une erreur
		if($this->db == null)
			echo '<b>[!]</b> ListeManager::__construct() : aucune base de donn�es n\'est disponible ou instanci�e';
	}


			/*******************
			***   METHODES   ***
			*******************/

	/**
	* Ex�cute la requete SQL dont la base est pass�e en param�tres.
	* Cette base sera augment�e par les divers param�tres fournis par la variable GET avant d'�tre ex�cut�.
	* Les r�sultats obtenus seront restitu�s par cette m�thode selon le param�tre $typeReponse de l'objet.
	* @param mixed $baseSQL : la requete � ex�cuter. Peut �tre de type string ou RequeteSQL.
	* @return mixed : l'objet de r�ponse d�pendant de $typeReponse, param�trable via la m�thode setTypeReponse
	*/
	public function construire($baseSQL){
		if($baseSQL instanceof RequeteSQL)
			$requeteSQL = $baseSQL;
		else 
			$requeteSQL = new RequeteSQL($baseSQL);

		if($this->utiliserGET){
			//Construction de la requete � partir de variables GET disponibles
			if(isset($_GET['mask']) && strlen($_GET['mask']) > 0)
				$requeteSQL->masquer(explode(',', $_GET['mask']));
			if(isset($_GET['tabSelect']))
				$requeteSQL->where($_GET['tabSelect']);
			if(isset($_GET['orderBy']))
				$requeteSQL->orderBy(explode(',', $_GET['orderBy']));
		}
		else {
			if($this->masque != null)	
				$requeteSQL->masquer($this->masque);
			if($this->tabWhere != null)	
				$requeteSQL->where($this->tabWhere);
			if($this->orderBy != null)	
				$requeteSQL->orderBy($this->orderBy);
		}

		echo $requeteSQL;

		//Ex�cution de la requete
		return $this->executerRequete($requeteSQL);

	}

	/**
	 * Ex�cute une requete SQL et retourne le r�sultat dans le format sp�cifi� par typeReponse
	 * @param mixed $requeteSQL : la requete � ex�cuter. Peut �tre de type string ou RequeteSQL.
	 * @return mixed : l'objet de r�ponse d�pendant de $typeReponse, param�trable via la m�thode setTypeReponse
	 */
	public function executerRequete($requeteSQL){

		// Gestion du param�tre
		if($requeteSQL instanceof RequeteSQL)
			$requete = $requeteSQL->__toString();
		else 
			$requete = $requeteSQL;

		// R�cup�ration de l'objet DB
		if($this->db == null)
			return false;

		//Ex�cution de la requ�te
		$reponse = $this->db->executer($requete);

		//Cr�ation de l'objet de r�ponse
		switch ($this->typeReponse){
			case TypeReponse::OBJET:
			return $reponse;

			case TypeReponse::TABLEAU:
				if($reponse->erreur())
					return null;
				else
					return $reponse->listeResultat();

			case TypeReponse::EXCEL:
			return ; // TODO

			case TypeReponse::JSON:
				$ret = new stdClass();
				$ret->erreur = $reponse->erreur();
				if($ret->erreur){
					$ret->donnees = null;
					$ret->messageErreur = $reponse->getMessageErreur();
				}
				else
					$ret->donnees = $reponse->listeResultat();
			return json_encode($ret);


			case TypeReponse::TEMPLATE:
				// Affichage (ou non) des champs de recherches
				$this->template->afficherChampsRecherche(
					isset($_GET['quest']) && intval($_GET['quest']) == 1);
			return $this->template->construireListe($reponse);
		}

		return false;
	}


			/******************
			***   GETTERS   ***
			******************/

	/**
	* @return TemplateListe l'objet template utilis� par ListeManager
	*/
	public function getTemplate(){
		return $this->template;
	}

			/******************
			***   SETTERS   ***
			******************/

	/**
	* D�finit le format de l'objet retourn� par ListeManager suite � l'ex�cution d'une requete
	* @param TypeReponse $typeReponse peut prendre 5 valeurs :
	* 	-> TEMPLATE (par d�faut) pour obtenir un string repr�sentant la liste HTML contenant toutes les donn�es 
	* 	-> ARRAY pour obtenir les r�sultats dans un array PHP (equivalent � PDOStaement::fetchAll())
	* 	-> JSON pour obtenir les donn�es dans un objet encod� en JSON
	* 	-> EXCEL pour obtenir les r�sultats dans une feuille de calcul Excel
	* 	-> OBJET pour obtenir un objet ReponseResultat
	*/
	public function setTypeReponse($typeReponse){
		$this->typeReponse = $typeReponse;
	}

	/**
	* D�finit la base de donn�es qui sera utilis�e pour l'ex�cution des requ�tes SQL
	* @param mixed $dataBase : peut �tre de type Database ou string.
	*	Si string : repr�sente l'etiquette de la base de donn�es � utiliser.
	*	Si null (valeur par d�faut) : recup�re la base de donn�e principale de la classe Database
	* la base de donn�e s�lectionn�e sera celle par d�faut de la classe Database.
	*/
	public function setDatabase($dataBase=null){
		if($dataBase == null)
			$this->db = Database::getInstance();
		else {
			if($dataBase instanceof Database)
				$this->db = $dataBase;
			else 
				$this->db = Database::getInstance($dataBase);
		}

		if($this->db == null)
			echo '<b>[!]</b> ListeManager::setDatabase() : aucune base de donn�es correspondante';

	}

	/**
	* D�finit un nouvel objet TemplateListe pour l'affichage des listes
	* @param TemplateListe $template le nouveau template � d�finir.
	*/
	public function setTemplate(TemplateListe $template){
		$this->template = $template;
	}

	/**
	* D�finit le nouvel ID HTML de la liste HTML
	* @param stirng $id
	*/
	public function setListeId($id){
		$this->template->setId($id);
	}

	/**
	* D�finit si ListeManager doit utiliser les valeurs contenues dans GET pour construire
	* le masque, le order by et le where de la requete SQL qui sera ex�cut�e.
	* Valeur par d�faut : vrai
	* @param boolean $valeur true ou false.
	*/
	public function utiliserGET($valeur){
		if(!is_bool($valeur))
			return false;

		$this->utiliserGET = $valeur;
	}

	/**
	* Red�finit le nom des classes qui seront affect�es une ligne sur deux dans la liste HTML (balises tr).
	* Si les valeurs sont mises � null les classes ne seront pas affich�.
	* @param string $class1 : le nom de la classe des lignes impaires
	* @param string $class2 : le nom de la classe des lignes paires
	*/
	public function setClasseLignes($classe1, $classe2){
		$this->template->setClasseLignes($classe1, $classe2);
	}

	/**
	* D�finit
	* /!\ Ne pas oublier de passer � faux l'utilisation des variables GET
	* pour que le changement soit pris en compte lors de l'ex�cution de la requ�te
	* @param
	*/
	public function setWhere(array $tabWhere){
		$this->tabWhere = $tabWhere;
	}

	/**
	* D�finit
	* /!\ Ne pas oublier de passer � faux l'utilisation des variables GET
	* pour que le changement soit pris en compte lors de l'ex�cution de la requ�te
	* @param
	*/
	public function setOrderBy($orderBy){
		$this->orderBy = $orderBy;
	}

	/**
	* D�finit
	* /!\ Ne pas oublier de passer � faux l'utilisation des variables GET
	* pour que le changement soit pris en compte lors de l'ex�cution de la requ�te
	* @param
	*/
	public function setMasque($masque){
		$this->masque = $masque;
	}

	/**
	* D�finit si l'option recherche doit �tre activ�e ou non. Valeur par d�faut : vrai
	* Si cette valeur est pass�e � faux il ne sera plus possible pour l'utilisateur
	* d'effectuer de recherches dans la liste
	* @param boolean $valeur, valeur par d�fautl : true
	*/
	public function activerRecherche($valeur){
		if(!is_bool($valeur))
			return false;

		$this->$template->activerRecherche($valeur);
	}

	/**
	* D�finit le callback (la fonction) qui sera ex�cut�e lors de l'affichage des donn�es
	* dans les cellules du tableau. Cette fonction doit �tre d�finie comme il suit :
	* 	-> 3 param�tres d'entr�e 
	* 			1 - element : la valeur de l'�l�ment en cours
	* 			2 - colonne : le num�ro de la colonne en cours
	* 			3 - ligne   : le num�ro de la ligne en cours
	* 	-> valeur de retour de type string (ou du moins affichable via echo)
	* @param string $fonction : le nom du callback � utiliser, null si aucun.
	* Valeur par d�faut : null
	*/
	public function setCallbackCellule($fonction){
		$this->template->setCallbackCellule($fonction);
	}
}

?>