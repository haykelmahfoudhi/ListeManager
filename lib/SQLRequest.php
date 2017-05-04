<?php

/*-*********************************************************************************************************************************
**                                                                                                                                **
**   ad88888ba     ,ad8888ba,    88           88888888ba                                                                          **
**  d8"     "8b   d8"'    `"8b   88           88      "8b                                                                ,d       **
**  Y8,          d8'        `8b  88           88      ,8P                                                                88       **
**  `Y8aaaaa,    88          88  88           88aaaaaa8P'  ,adPPYba,   ,adPPYb,d8  88       88   ,adPPYba,  ,adPPYba,  MM88MMM    **
**    `"""""8b,  88          88  88           88""""88'   a8P_____88  a8"    `Y88  88       88  a8P_____88  I8[    ""    88       **
**          `8b  Y8,    "88,,8P  88           88    `8b   8PP"""""""  8b       88  88       88  8PP"""""""   `"Y8ba,     88       **
**  Y8a     a8P   Y8a.    Y88P   88           88     `8b  "8b,   ,aa  "8a    ,d88  "8a,   ,a88  "8b,   ,aa  aa    ]8I    88,      **
**   "Y88888P"     `"Y8888Y"Y8a  88888888888  88      `8b  `"Ybbd8"'   `"YbbdP'88   `"YbbdP'Y8   `"Ybbd8"'  `"YbbdP"'    "Y888    **
**                                                                             88                                                 **
**                                                                             88                                                 **
**                                                                                                                                **
***********************************************************************************************************************************/

/**
 * Classe représentant les requetes SQL.
 * Un objet SQLRequest est composé d'une requete de base à laquelle il est possible de rajouter des clauses WHERE, un ORDER By ou de supprimer des colonnes.
 * 
 * @author RookieRed
 *
 */
class SQLRequest {

	/**
	 * @var string $requestBasis bloc de base SQL (sans WHERE, ORDER BY, HAVING...)
	 */
	private $requestBasis;
	/**
	 * @var string $whereBlock correspond à la partie Where de la requete SQL 
	 */
	private $whereBlock;
	/**
	 * @var string $havingBlock correspond à la partie Having de la requete SQL 
	 */
	private $havingBlock;
	/**
	 * @var array $orderByArray tableau contenant le numéro/nom de colonnes pour le tri des données 
	 */
	private $orderByArray;
	/**
	 * @var int correpsond à la valeur de la clause 'LIMIT' d'une requete SELECT
	 */
	private $limit;
	/**
	 * @var string correpsond à la valeur de la clause 'OFFSET' d'une requete SELECT
	 */
	private $offset;
	/**
	 * @var RequestType $requestType : énumération sur le type de la requete SQL
	 */
	private $requestType;


			/*-*********************
			***   CONSTRUCTEUR   ***
			***********************/
	/**
	* Construit une nouvelle requete SQL à partir d'une requete de base
	* La requete SQL de base passée en paramètre ne doit pas contenir :
	* * de clause ODER BY (pour le moment...) -> TODO
	* @param string $requestBasis la base de la requete SQL
	*/
	public function __construct($baseRequete){
		$this->requestBasis = str_replace(';', '', $baseRequete);
		$this->whereBlock = '';
		$this->havingBlock = '';
		$this->orderByArray = array();
		$this->limit = null;
		$this->offset = null;
		$this->matchRequete();
	}

			/*-*****************
			***   METHODES   ***
			*******************/

	/**
	* Ajoute des clauses where à la requête.
	* @var array $tabWhere : le tableau contenant toutes les conditions à rajouter. Ce tableau aura la forme suivante :
	*    -> 'nomColonne1' => 'condition1', 'nomColonne2' => 'condition2' ...
	* Une condition prend la forme suivante : [OPERATEUR][VALEUR]
	* Les opérateurs possibles sont :
	* * (pas d'opérateur) : égalité stricte avec la valeur entrée
	* * < > <= >= : infèrieur, supèrieur, supèrieur ou égal, infèrieur ou égale (pour les valeurs numériques)
	* * ! : opérateur 'différent de'
	* * << : opérateur 'BETWEEN' pour les dates
	* * _ % : opérateurs joker SQL, remplacent respectivement un seul caractère ou un nombre indéfini de caractère dans une chaine.
	* Il est possible de combiner les conditions en les séparant par une virgule. Ainsi la condition 'prenom' => 'Roger,Patrick' recherchera tous ceux ayant le prénom Roger ou Patrick
	* La condition '!' est traduite par 'NOT NULL'
	* @param array 
	*/
	public function where(array $tabWhere){
		$ret = ((strlen($this->whereBlock) > 0)? ' AND ' : '');
		foreach ($tabWhere as $nomColonne => $conditions) {
			$conditions = explode(',', $conditions);
			$ret .= '(';
			foreach ($conditions as &$condition) {
				//Initilasation des variables
				$operateur = '=';
				$not = false;
				$btw = false;

				// Construction de l'operateur et sa valeur : NOT
				if(mb_substr($condition, 0, 1) === '!'){
					$not = true;
					$condition = mb_substr($condition, 1);
				}
				// Comparateurs > < >= <=
				if( (in_array($op = mb_substr($condition, 0, 2), array('>=', '<='))
					|| in_array($op = mb_substr($condition, 0, 1), array('>', '<')) ) 
					&& is_numeric($val = mb_substr($condition, strlen($op))) ) {
					$valeur = $val;
					$operateur = $op;
				}
				// Opérateur BETWEEN
				else if(($pos = mb_strpos($condition, '<<')) !== false ){
					$operateur = 'BETWEEN';
					// Récupération des deux bornes
					$val1 = htmlentities(mb_substr($condition, 0, $pos), ENT_QUOTES, 'UTF-8');
					$val2 = htmlentities(mb_substr($condition, $pos + 2), ENT_QUOTES, 'UTF-8');
					// Constructiond de $valeur
					$valeur = (is_numeric($val1)? $val1 : "'$val1'" )
						.' AND '.(is_numeric($val2)? $val2 : "'$val2'"  );
					// Pour ne pas reformater les valeurs lors de la reconnaissance du type
					$btw = true;
				}
				else {
					// Opérateur LIKE
					if(mb_strpos($condition, '_') !== false 
						|| mb_strpos($condition, '%') !== false){
						$operateur = 'LIKE';
					}
					$valeur = $condition;
				}

				// Reconnaissance type de valeur
				if(! is_numeric($valeur) && ! $btw)
					$valeur = '\''.htmlentities($valeur, ENT_QUOTES, 'UTF-8').'\'';
				// Mise en forme de la condition
				$ret .= (($not)? 'NOT ' : '')."$nomColonne $operateur $valeur OR ";
			}
			$ret = mb_substr($ret, 0, strlen($ret) - 4).')';
			$ret .= ' AND ';
		}

		$ret = mb_substr($ret, 0, strlen($ret) - 5);
		$this->whereBlock .= $ret;
	}

	/**
	* Ajoute une ou plusieurs colonnes au bloc order by de la requete
	* @param mixed $numColonne : correpsond numéro de la (ou des) colonne(s) à ajouter au group by.
	*	Pour ajouter plusieurs colonnes ce paramèttre doit etre un array, sinon c'est un int.
	*	Pour classer la colonnes par 'DESC' le numéro de colonne doit être négatif.
	* @return boolean faux si le type de requete n'est pas SELECT ou si le paramètre $numColonne est vide.
	*/
	public function orderBy($numColonne){
		//Vérification du type de requete
		if($this->requestType == RequestType::SELECT){

			//Si $numColonne est un tableau
			if(is_array($numColonne)){
				// Suppression des colonnes déjà existantes
				foreach ($numColonne as $val)
						$negColonne[] = -1 * $val;
				$orderBy = array_diff($this->orderByArray, $numColonne, $negColonne);
				
				foreach (array_reverse($numColonne) as $col){
					if(intval($col) != 0)
						array_unshift($orderBy, intval($col));
				}
				$this->orderByArray = array_unique($orderBy);
			}

			//Sinon si c'est un int
			else {
				$numColonne = intval($numColonne);
				if($numColonne == 0)
					return false;

				//Suppression de la valeur existante
				if(($key = array_search($numColonne, $this->orderByArray)) != false
					|| ($key = array_search(-$numColonne, $this->orderByArray)) != false){
					unset($this->orderByArray[$key]);
				}
				// Ajout de la colonne en début
				array_unshift($this->orderByArray, $numColonne);
			}
			return true;
		}
		//param incompatbile / type incompatible
		return false;
	}

	/**
	* Remet à zéro le contenu du bloc ORDER BY
	* @return boolean vrai si opération ok, faux sinon (type de requete incompatible)
	*/
	public function removeOrderBy(){
		if($this->requestType == RequestType::SELECT) {
			$this->orderByArray = array();
			return true;
		}
		return false;
	}

	/**
	* @return string la requete SQL complete
	*/
	public function __toString(){
		$ret = $this->requestBasis;

		if(in_array($this->requestType,
			array(RequestType::SELECT, RequestType::UPDATE, RequestType::DELETE))) {
			
			//Ajout du bloc WHERE
			if(strlen($this->whereBlock) > 0)
				$ret .= ' WHERE '.$this->whereBlock;

			if($this->requestType == RequestType::SELECT){
				
				//Ajout du bloc HAVING
				if(strlen($this->havingBlock) > 0)
					$ret .= ' HAVING '.$this->havingBlock;
				
				//Ajout du order by
				if(count($this->orderByArray) > 0){
					$ret .= ' ORDER BY ';
					foreach ($this->orderByArray as $num) {
						$ret .= abs($num).(($num > 0)?'':' DESC ').',';
					}
					$ret = substr($ret, 0, strlen($ret) - 1);
				}

				//Ajout de la limit
				if($this->limit !== null) {
					$ret .= ' LIMIT '.$this->limit;
					if($this->offset != null)
						$ret .= " $this->offset";
				}
			}
		}
		return $ret.((mb_strpos($ret,';') === false)? ';' : '');
	}


			/*-***************************
			***   GETTERS && SETTERS   ***
			*****************************/

	/**
	* @return RequestType : le type de la requete SQL
	*/
	public function getType(){
		return $this->requestType;
	}

	/**
	* @return array les numéros des colonnes du order by, négatif si tri décroissant
	* retourne faux s'il n'y a pas d'order by.
	*/
	public function getOrderByArray(){
		if($this->requestType === RequestType::SELECT
			&& count($this->orderByArray) > 0){
			return $this->orderByArray;
		}
		return false;
	}

	/**
	* @return int la valeur de la clause 'LIMIT' de la requete, ou null si non définie
	*/
	public function getLimit(){
		return $this->limit;
	}

	/**
	* Définit la nouvelle valeur de la clause LIMIT pour les requêtes de type SELECT
	* @param int $valeur : la nouvelle valeur limit. Si null cette clause sera desactivée. 
	*/
	public function setLimit($valeur){
		if($this->requestType != RequestType::SELECT)
			return false;

		$this->limit = $valeur;
	}


			/*-****************
			***   PRIVATE   ***
			******************/

	/**
	* Définit le type de la base de requête en paramètre parmi 
	* 	- SELECT - UPDATE - DELETE - INSERT - AUTRE -
	* Actualise les blocs where et having si necessaire. A appler dans le constructeur
	*/
	private function matchRequete(){

		//Traitement bloc WHERE & HAVING & LIMIT
		$reWhere = '/^([\s\S]+)(\s+WHERE\s+)([\s\S]+)$/i';
		$reHaving = '/^([\s\S]+)(\s+HAVING\s+)([\s\S]+)$/i';
		$reLimit = '/^([\s\S]+)(\s+LIMIT\s+)([0-9]+)([\s\S]*)$/i';
		if(preg_match($reLimit, $this->requestBasis, $tabMatch) === 1){
			$this->requestBasis = $tabMatch[1];
			$this->limit = $tabMatch[3];
			$this->offset = (( strlen($offset = trim($tabMatch[4])) > 0 )? $offset : null );
		}
		if(preg_match($reHaving, $this->requestBasis, $tabMatch) === 1){
			$this->requestBasis = $tabMatch[1];
			$this->havingBlock = $tabMatch[3];
		}
		if(preg_match($reWhere, $this->requestBasis, $tabMatch) === 1){
			$this->requestBasis = $tabMatch[1];
			$this->whereBlock = $tabMatch[3];
		}

		//Expressions régulières
		$reSelect = '/^[\s]*(SELECT([\s\S]+))$/i';
		$reInsert = '/^[\s]*(INSERT([\s\S]+))$/i';
		$reUpdate = '/^[\s]*(UPDATE([\s\S]+))$/i';
		$reDelete = '/^[\s]*(DELETE([\s\S]+))$/i';

		$tabMatch = array();
		// Teste si SELECT
		preg_match($reSelect, $this->requestBasis, $tabMatch);
		if($tabMatch != array()){
			$this->requestType = RequestType::SELECT;
			return;
		}
		
		// Teste si INSERT
		preg_match($reInsert, $this->requestBasis, $tabMatch);
		if($tabMatch != array()){
			$this->requestBasis = $tabMatch[1];
			$this->requestType = RequestType::INSERT;
			return;
		}

		// Teste si DELETE
		preg_match($reDelete, $this->requestBasis, $tabMatch);
		if($tabMatch != array()){
			$this->requestType = RequestType::DELETE;
			$this->requestBasis = $tabMatch[1];
			return;
		}

		//Teste si UPDATE
		preg_match($reUpdate, $this->requestBasis, $tabMatch);
		if($tabMatch != array()){
			$this->requestType = RequestType::UPDATE;
			$this->requestBasis = $tabMatch[1];
			return;
		}

		// Si rien de tout cela
		$this->requestType = RequestType::AUTRE;
	}

}

?>