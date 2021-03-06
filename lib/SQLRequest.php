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
 * Un objet SQLRequest se construit autour d'une requete SQL de base. Le constructeur parse la requete et récupère son type (cf RequestType).
 * S'il s'agit d'un *SELECT* le constructeur tente de récupérer le nom de chaque colonne écrite dans la requete, ainsi que le nom des tables et leur alias
 * Ensuite les blocs *LIMIT/OFFSET GROUP BY ORDER BY HAVING* et *WHERE* sont identifiés, ce qui permettra de les modifier par la suite.
 * Les méthodes de la classe permettent donc de filtrer les données séléctionnées en completant les clauses *HAVING* ou *WHERE* de la requete, ainsi que de créer/modifier les clauses citées plus haut.
 *
 * @author RookieRed
 *
 */
class SQLRequest {

	/**
	 * @var string $_requestBasis bloc de base SQL (sans WHERE, ORDER BY, HAVING...)
	 */
	private $_requestBasis;
	/**
	 * @var array $_tables contient le nom des tables extraites de FRIOM uniquement si elles ont un alias
	 * ['alias'] => 'nom_table'
	 */
	private $_tables;
	/**
	 * @var array $_columnsMeta tableau d'objet conteantn les métas données des colonnes de la clause SELECT.
	 * Chaque objet possède les 3 attributs suivants :
	 *   * -> *name*   : le nom de la colonne
	 *   * -> *table*  : le nom de la table de la colonne ou null si non spécifié
	 *   * -> *alias*  : l'alias de la colonne ou null si non spécifié
	 */
	private $_columnsMeta;
	/**
	 * @var string $_whereBlock correspond à la clause Where de la requete SQL
	 */
	private $_whereBlock;
	/**
	 * @var string $_havingBlock correspond à la clause Having de la requete SQL
	 */
	private $_groupByBlock;
	/**
	 * @var string $_havingBlock correspond à la clause Having de la requete SQL
	 */
	private $_havingBlock;
	/**
	 * @var array $_orderBy tableau contenant le numéro/nom de colonnes pour le tri des données
	 */
	private $_orderBy;
	/**
	 * @var int $_limit correpsond à la valeur de la clause 'LIMIT' de la requete
	 */
	private $_limit;
	/**
	 * @var string $_offset correpsond à la valeur de la clause 'OFFSET' de la requete
	 */
	private $_offset;
	/**
	 * @var array $_userParameters tableau contenant les valeurs entrées par l'utilisateur lors de la recherche. A utiliser lors de l'exécution avec PDO::prepare()
	 */
	private $_userParameters;
	/**
	 * @var RequestType $_requestType type de la requete SQL (SELECT, INSERT, DELETE, UPDATE, AUTRE)
	 */
	private $_requestType;
	/**
	 * @var bool $_forOracle détemrine si cette requête SQL est destinée à etre exécutée par une base de données Oracle ou non
	 */
	private $_forOracle;



			/*-*********************
			***   CONSTRUCTEUR   ***
			***********************/
	/**
	* Construit une nouvelle requete SQL à partir d'une requete de base.
	* Définit le type de la requete et la parse pour récupérer les différentes clauses qui la compose.
	* @param string $requestBasis la base de la requete SQL
	* @param bool $forOracle passez le paramètre à true si la requête sera exécutée par une BD Oracle
	*/
	public function __construct($requestBasis, $forOracle=false){
		$this->_requestBasis = ((substr($requestBasis, -1) == ';')? substr($requestBasis, 0, strlen($requestBasis)-1) : $requestBasis );
		$this->_requestType = RequestType::AUTRE;
		$this->_tables = [];
		$this->_columnsMeta = [];
		$this->_whereBlock = '';
		$this->_groupByBlock = '';
		$this->_havingBlock = '';
		$this->_orderBy = array();
		$this->_limit = null;
		$this->_offset = null;
		$this->_userParameters = [];
		$this->_forOracle = $forOracle;
		$this->matchRequete();
	}

			/*-*****************
			***   METHODES   ***
			*******************/

	/**
	* Filtre les données sélectionnées par une requete.
	* Cette méthode ajoute des clauses aux blocs WHERE et/ou HAVING de la requete SQL selon les tableaux qui lui sont passés en paramètre
	* @param array $tabSelect le tableau contenant toutes les conditions à rajouter. Ce tableau aura la forme suivante :
	* ['nomColonne1' => 'condition1', 'nomColonne2' => 'cond2A,cond2B,cond3C'] ... Il est possible de combiner les conditions en les séparant par une virgule. Ainsi la condition 'prenom' => 'Roger,Patrick' recherchera tous ceux ayant le prénom Roger ou Patrick
	* Une condition prend la forme suivante : [OPERATEUR][VALEUR]
	* Les opérateurs possibles sont :
	* * (pas d'opérateur) : égalité stricte avec la valeur entrée
	* * < > <= >= = : infèrieur, supèrieur, supèrieur ou égal, infèrieur ou égal, égal
	* * ! : opérateur 'différent de'. La condition '!' est traduite par différent de ''
	* * /- : correspond à NULL. Doit être utilisé seul, !/- est traduit par NOT NULL
	* * << : opérateur 'BETWEEN' pour les dates
	* * _ % : opérateurs joker SQL, remplacent respectivement un seul caractère ou un nombre indéfini de caractère dans une chaine.
	* @param array $colonnesHaving tableau contenant le nom des colonnes selectionnées dont le filtre doit se trouver dans la clause HAVING.
	*    Ce tableau doit avoir pour fomrat : [ 'alias_colonne' => 'COUNT(t.id)' ]. Dans cet exemple 'alias_colonne' est l'alias du COUNT tel quel dans le bloc SELECT de la requete. SI vous n'utilisez pas d'alias
	*    laissez juste le 'COUNT(t.id)' dans le tableau.
	*/
	public function filter(array $tabSelect, array $colonnesHaving=[]){
		$retWhere = ((strlen($this->_whereBlock) > 0)? ' AND ' : '');
		$retHaving = ((strlen($this->_havingBlock) > 0)? ' AND ' : '');

		foreach ($tabSelect as $nomColonne => $conditions) {
			//Initialisation variables
			$nomColonne = strtolower($nomColonne);
			$not = false;

			// Gestion HAVING / WHERE
			$estHaving = false;
			if(isset($colonnesHaving[$nomColonne])) {
				$estHaving = true;
				$nomColonne = $colonnesHaving[$nomColonne];
			}
			else if (in_array($nomColonne, array_map('strtolower', $colonnesHaving)))
				$estHaving = true;

			// Gestion des conditions
			$conditions = explode(',', $conditions);
			$ret = '(';
			foreach ($conditions as &$condition) {
				//Initilasation des variables
				$operateur = '=';

				// Construction de l'operateur et sa valeur : NOT
				if(mb_substr($condition, 0, 1) === '/'){
					$not = true;
					$condition = mb_substr($condition, 1);
				}
				// Comparateurs > < >= <=
				if( in_array($op = mb_substr($condition, 0, 2), array('>=', '<='))
					|| in_array($op = mb_substr($condition, 0, 1), array('>', '<', '=')) ){
					$valeur = mb_substr($condition, strlen($op));
					$operateur = $op;
				}
				// Opérateur BETWEEN
				else if(($pos = mb_strpos($condition, '<<')) !== false ){
					// Récupération des deux bornes
					$val1 = htmlentities(mb_substr($condition, 0, $pos), ENT_QUOTES, 'UTF-8');
					$val2 = htmlentities(mb_substr($condition, $pos + 2), ENT_QUOTES, 'UTF-8');
					// Constructiond de $valeur
					$valeur = [$val1, $val2];
				}
				// /- => IS NULL
				else if($condition === '-'){
					$valeur = 'NULL';
					$operateur = 'IS';
					if($not) {
						$not = false;
						$operateur .= ' NOT';
					}
				}
				else {
					// Opérateur LIKE
					if(mb_strpos($condition, '_') !== false
						|| mb_strpos($condition, '%') !== false){
						$operateur = 'LIKE';
					}
					// Valeur seule
					$valeur = $condition;
				}

				// Créaton du parametre à enregistrer
				if(!is_array($valeur)){
					// MARCHE PAS AVEC PDOOCI :'(
					if($valeur !== 'NULL' && !$this->_forOracle){
						$nomParam = uniqid(':');
						$this->_userParameters[$nomParam] = $valeur;
					}
					// On met la valeur directement dans la requete
					else{
						if($valeur === 'NULL')
							$nomParam = $valeur;
						else
							$nomParam = "'".htmlentities($valeur, ENT_QUOTES)."'";
					}

					// Mise en forme de la condition
					$ret .= (($not)? 'NOT ' : '')."$nomColonne $operateur $nomParam ".(($not)? 'AND ' : 'OR ');
				}
				// Gestion du BETWEEN
				else {
					$nomParam = [];
					if(!$this->_forOracle){
						$nomParam[0] = uniqid(':');
						$this->_userParameters[$nomParam[0]] = $valeur[0];
						$nomParam[1] = uniqid(':');
						$this->_userParameters[$nomParam[1]] = $valeur[1];
					}
					else {
						$nomParam[0] = ((!is_numeric($valeur[0]))? "'".htmlentities($valeur[0])."'" : $valeur[0] );
						$nomParam[1] = ((!is_numeric($valeur[1]))? "'".htmlentities($valeur[1])."'" : $valeur[1] );
					}
					$ret .= (($not)? 'NOT ' : '')."$nomColonne BETWEEN $nomParam[0] AND $nomParam[1] OR ";
				}
			}
			$ret = mb_substr($ret, 0, strlen($ret) - 4).') AND ';

			// Ajout dans le bloc where ou having
			if($estHaving) {
				$retHaving .= $ret;
			}
			else {
				$retWhere .= $ret;
			}
		}

		$retWhere = mb_substr($retWhere, 0, strlen($retWhere) - 5);
		$retHaving = mb_substr($retHaving, 0, strlen($retHaving) - 5);
		$this->_whereBlock .= $retWhere;
		$this->_havingBlock .= $retHaving;
	}

	/**
	* Ajoute une ou plusieurs colonnes au bloc order by de la requete.
	* @param array $colonnes correpsond au nom / numéro de la (ou des) colonne(s) à ajouter au group by :
	*  * Pour classer la colonnes par 'DESC' le nom ou numéro doit commencer par '-'.
	* @return boolean false si le type de requete n'est pas SELECT ou si le paramètre $colonnes est vide.
	*/
	public function orderBy(array $colonnes){
		//Vérification du type de requete
		if($this->_requestType == RequestType::SELECT && count($colonnes)){

			$negColonne = [];
			$toRemove = [];
			// Suppression des colonnes déjà existantes
			foreach ($colonnes as $val){
				$val = strtolower($val);
				if($val[0] == '-'){
					$negColonne[] = substr($val, 1);
				}
				else if($val[0] == '*'){
					$toRemove[] = substr($val, 1);
					$toRemove[] = (($val[1] == '-')? substr($val, 2) : '-'.substr($val, 1));
					unset($colonnes[array_search($val, $colonnes)]);
				}
				else{
					$negColonne[] = "-$val";
				}
			}
			$orderBy = array_diff(array_map(function($val){
				return trim(strtolower($val));
			}, $this->_orderBy), $colonnes, $negColonne, $toRemove);
			foreach (array_reverse($colonnes) as $col){
				array_unshift($orderBy, $col);
			}
			$this->_orderBy = array_unique($orderBy);
		}
		return false;
	}

	/**
	* Remet à zéro le contenu du bloc ORDER BY
	* @return boolean true si opération ok, false sinon (type de requete incompatible)
	*/
	public function removeOrderBy(){
		if($this->_requestType == RequestType::SELECT) {
			$this->_orderBy = array();
			return true;
		}
		return false;
	}

	/**
	* @return string la requete SQL complete
	*/
	public function __toString(){
		$ret = $this->_requestBasis;

		if(in_array($this->_requestType,
			array(RequestType::SELECT, RequestType::UPDATE, RequestType::DELETE))) {

			//Ajout du bloc WHERE
			if(strlen($this->_whereBlock) > 0) {
				$ret .= ' WHERE '.$this->_whereBlock;
			}

			if($this->_requestType == RequestType::SELECT){

				// Ajout du bloc GROUP BY
				if(strlen($this->_groupByBlock) > 0)
					$ret .= ' GROUP BY '.$this->_groupByBlock;

				//Ajout du bloc HAVING
				if(strlen($this->_havingBlock) > 0)
					$ret .= ' HAVING '.$this->_havingBlock;

				//Ajout du order by
				if(count($this->_orderBy) > 0){
					$ret .= ' ORDER BY ';
					foreach ($this->_orderBy as $colonne) {
						if(is_numeric($colonne))
							$ret .= abs($colonne).(($colonne > 0)?'':' DESC ').',';
						else
							$ret .= (($colonne[0] == '-')? substr($colonne, 1).' DESC': $colonne).',';
					}
					$ret = substr($ret, 0, strlen($ret) - 1);
				}


				//Ajout de la limit
				if($this->_limit !== null && !$this->_forOracle) {
					$ret .= ' LIMIT '.$this->_limit;
					if($this->_offset != null)
						$ret .= " $this->_offset";
				}
			}
		}
		return $ret.((!$this->_forOracle && (strpos($ret, ';') === false))? ';' : '');
	}


			/*-***************************
			***   GETTERS && SETTERS   ***
			*****************************/

	/**
	* @return RequestType le type de la requete SQL
	*/
	public function getType(){
		return $this->_requestType;
	}

	/**
	 * @return array le tableau contenant les valeurs entrées par l'utilisateur lors de la recherche. A utiliser lors de l'exécution avec PDO::prepare()
	 */
	public function getUserParameters(){
		return $this->_userParameters;
	}

	/**
	 * Retourne un tableau d'objets contenant les données pour chaque colonne de la clause SELECT.
	 * Chaque objet possède les 3 attributs suivants :
	 *   * -> *name*   : le nom de la colonne
	 *   * -> *table*  : le nom de la table de la colonne ou null si non spécifié
	 *   * -> *alias*  : l'alias de la colonne ou null si non spécifié
	 * @return array tableau des metas données des colonnes selectionnées
	 */
	public function getColumnsMeta() {
		if($this->_requestType !== RequestType::SELECT)
			return false;
		return $this->_columnsMeta;
	}

	/**
	 * Retourne un tableau associatifs des tables ayant un alias dans la clause FROM de la requete.
	 * La clé de chaque entrée est l'alias de la table et sa valeur el nom complet de celle ci
	 * @return array tableau contenant le nom des tables de la clause FROM et leur alias si précisé.
	 */
	public function getTablesAliases() {
		if($this->_requestType !== RequestType::SELECT)
			return false;
		return $this->_tables;
	}

	/**
	* @return array le tableau contenant le nom / numéro des colonnes à inscrire dans le ORDER BY.
	*/
	public function getOrderBy(){
		if($this->_requestType === RequestType::SELECT
			&& count($this->_orderBy) > 0){
			return $this->_orderBy;
		}
		return [];
	}

	/**
	* @return int la valeur de la clause 'LIMIT' de la requete, ou null si non définie
	*/
	public function getLimit(){
		return $this->_limit;
	}

	/**
	* Définit les nouvelles valeurs des clauses LIMIT et OFFSET pour les requêtes de type SELECT
	* @param int $limit la nouvelle valeur limit. Si null cette clause sera desactivée.
	* @param string $offset la clause OFFSET ENTIERE (et non la valeur seule de la clause). Pour MySQL ce paramètre doit prendre la forme 'OFFSET 2'. null pour désactiver
	* @return bool false si le type de requete n'est pas SELECT ou si les paramètres sont incorrects
	*/
	public function setLimit($limit, $offset=''){
		if($this->_requestType != RequestType::SELECT || intval($limit) != $limit)
			return false;

		if(!$this->_forOracle) {
			$this->_limit = $limit;
			if($offset !== '')
				$this->_offset = $offset;
		}
		else {
			$this->filter(['ROWNUM' => "<=$limit"]);
		}
	}

	/**
	 * Définit si la requete est destinée à être exécuté par une base de données Oracle
	 * @param bool $valeur nouvelle valeur
	 * @return bool false si le paramètre d'entrée n'est pas un booléen
	 */
	public function prepareForOracle($valeur) {
		if(!is_bool($valeur))
			return false;

		$this->_forOracle = $valeur;
	}


			/*-****************
			***   PRIVATE   ***
			******************/

	/**
	* Parse la requete SQL.
	* Définit le type de la base de requête en paramètre parmi
	*   - SELECT - UPDATE - DELETE - INSERT - AUTRE -
	* Parse la requête et détermine les blocs HAVING LIMIT OFFSET GROUP BY ORDER BY et WHERE.
	* Récupère le nom des colonnes selctionnées et le nom des tables et leur alias
	*/
	private function matchRequete(){

		// Récuppération & suppression des blocks parenthésés de la requete
		$regParentheses = '/\(([^\(\)]|(?R))*\)/';
		preg_match_all($regParentheses, $this->_requestBasis, $matchParentheses);
		$matchParentheses = array_values(array_unique($matchParentheses[0]));
		$replaceArray = [];
		for ($i=0; $i < count($matchParentheses); $i++) {
			$replaceArray[] = "($i)";
		}
		$sqlReplaced = str_replace($matchParentheses, $replaceArray, $this->_requestBasis);

		//Traitement bloc WHERE & HAVING & ORDER BY & LIMIT
		$regArray = [
			'_limit' 		=> '/^([\s\S]+)(\s+LIMIT\s+)([0-9]+)([\s\S]*)$/i',
			'_orderBy' => '/^([\s\S]+)(\s+ORDER\s+BY\s+)([\s\S]+)$/i',
			'_havingBlock' 	=> '/^([\s\S]+)(\s+HAVING\s+)([\s\S]+)$/i',
			'_groupByBlock' => '/^([\s\S]+)(\s+GROUP\s+BY\s+)([\s\S]+)$/i',
			'_whereBlock' 	=> '/^([\s\S]+)(\s+WHERE\s+)([\s\S]+)$/i'
		];
		foreach ($regArray as $attribu => $regEx) {
			$tabMatch = [];
			// On teste si le pattern est présent dans la requete
			if(preg_match($regEx, $sqlReplaced, $tabMatch) === 1){
				$sqlReplaced = $tabMatch[1];

				$valeur = preg_replace_callback($regParentheses, function($match) use ($matchParentheses){
					$num = substr($match[0], 1, strlen($match[0]) - 2);
					return $matchParentheses[$num];
				} , $tabMatch[3]);

				// Mise à jour des attribus
				if($attribu == '_limit') {
					$this->_limit = $valeur;
					$this->_offset = ( (strlen($offset = trim($tabMatch[4])) > 0 )? $offset : null );
				}
				else if($attribu == '_orderBy')
					$this->_orderBy = array_map(function($col){
						if(preg_match('/(\S*)[\s]+DESC[\s]*$/i', $col, $matchCol))
							return trim('-'.strtolower($matchCol[1]));
						return trim(strtolower($col));
					}, explode(',', $valeur));
				else
					$this->$attribu = $valeur;
			}
		}

		// Mise à jour de la base de la requete
		$this->_requestBasis = $sqlReplaced;

		// Définition du type de requete
		$reSelect = '/^[\s]*(SELECT([\s]+DISTINCT)?([\s\S]+)FROM([\s\S]+))$/i';
		$reInsert = '/^[\s]*(INSERT([\s\S]+))$/i';
		$reUpdate = '/^[\s]*(UPDATE([\s\S]+))$/i';
		$reDelete = '/^[\s]*(DELETE([\s\S]+))$/i';

		$regAlias = '/(?:([\S]+)(?:[\s]*\.[\s]*))?([\S]+)(?:[\s]+(?:AS[\s]+)?([\S\s]+))?/i';

		// Teste si SELECT
		if(preg_match($reSelect, $this->_requestBasis, $tabMatch) === 1){

			// Récupération des nom de colonnes selectionnées
			$this->_columnsMeta = explode(',', $tabMatch[3]);
			foreach($this->_columnsMeta as &$col) {
				$tabAlias = [];
				preg_match_all($regAlias, $col, $tabAlias, PREG_SET_ORDER, 0);

				// Création de l'objet contenant toutes les metas de la colonne
				$obj = new stdClass();

				$table = str_replace('"', '',
						str_replace("'", '',
							str_replace('`', '', trim($tabAlias[0][1]))));
				$obj->table = strtolower(strlen($table)? $table : null);

				$obj->name = strtolower(preg_replace_callback($regParentheses, function($match) use ($matchParentheses){
					$num = substr($match[0], 1, strlen($match[0]) - 2);
					return $matchParentheses[$num];
				}, trim($tabAlias[0][2])));

				$as = ((isset($tabAlias[0][3])) ? str_replace('"', '',
						str_replace("'", '',
							str_replace('`', '', trim($tabAlias[0][3])))) : null);
				$obj->alias = strtolower( strlen($as) ? preg_replace_callback($regParentheses, function($match) use ($matchParentheses){
					$num = substr($match[0], 1, strlen($match[0]) - 2);
					return $matchParentheses[$num];
					},$as) : null );

				$col = $obj;
			}

			// Récupération des noms de table et leurs alias dans le FROM
			$tabElements = explode(',',
				preg_replace('/(?:[\s]*(?:INNER|LEFT|RIGHT|FULL|CROSS|SELF|NATURAL)?[\s]+JOIN[\s]+)/i', ',',
				preg_replace('/(ON[\s]*[\S]+[\s]*=[\s]*[\S]+)/i', '', $tabMatch[4])));
			foreach ($tabElements as $element) {

				// si alias présent pour cette table -> enregistre dans le tableau
				preg_match_all($regAlias, $element, $tabAlias, PREG_SET_ORDER, 0);
				if(isset($tabAlias[0][3]) && strlen(trim($tabAlias[0][3]))){
					$this->_tables[trim($tabAlias[0][3])] = trim($tabAlias[0][2]);
				}
			}
			$this->_requestType = RequestType::SELECT;
		}

		// Teste si INSERT
		if(preg_match($reInsert, $this->_requestBasis, $tabMatch) === 1){
			$this->_requestBasis = $tabMatch[1];
			$this->_requestType = RequestType::INSERT;
		}

		// Teste si DELETE
		if(preg_match($reDelete, $this->_requestBasis, $tabMatch) === 1){
			$this->_requestType = RequestType::DELETE;
			$this->_requestBasis = $tabMatch[1];
		}

		//Teste si UPDATE
		if(preg_match($reUpdate, $this->_requestBasis, $tabMatch) === 1){
			$this->_requestType = RequestType::UPDATE;
			$this->_requestBasis = $tabMatch[1];
		}

		// Mise à jour de la base de la requete
		$this->_requestBasis = preg_replace_callback($regParentheses, function($match) use ($matchParentheses){
					$num = substr($match[0], 1, strlen($match[0]) - 2);
					return $matchParentheses[$num];
		}, $sqlReplaced);
	}

}

?>
