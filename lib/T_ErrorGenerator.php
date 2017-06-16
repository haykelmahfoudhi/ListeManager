<?php 

/**
 * Trait à appliquer à toutes les classes qui peuvent générer des messages d'erreur et les afficher dans la page.
 * 
 * Ce trait gère aussi le mode verbeux des classes qui l'utiliseront.
 * 
 * @author RookieRed
 */
trait T_ErrorGenerator {

	/**
	 * @var bool $verbose determine si l'objet doit echo les messages d'erreur et d'avertissement ou non
	 */
	private $_verbose = false;
	/**
	 * @var array $errors contient tous les messages d'erreurs générés par l'objet
	 */
	private $_errors = [];

	/**
	 * Enregistre une nouvelle erreur et l'affiche si verbose activée.
	 * @param string $method le nom de la méthode à l'origine de l'erreur 
	 * @param string $message le message d'erreur associé
	 */
	protected function addError($message, $method) {
		$erreur = "\n<br><b> [!] <i style='color:#333'>".get_class().'::'.$method.'()</i></b> : '.$message."<br>\n";
		$this->_errors[] = $erreur;
		if($this->_verbose)
			echo $erreur;
	}

	/**
	 * @return array le tableau contenant toutes les erreurs enregistrées
	 */
	public function getErrorMessages() {
		return $this->_errors;
	}

	/**
	 * Affiche la dernière erreur enregistré
	 */
	public function displayLastError() {
		echo end($this->_errors);
	}

	/**
	 * Getter OU Setter : modifie la valeur de verbose, ou retourne l'état verbeux de l'objet.
	 * @param bool $value la nouvelle valeur pour l'attribut verbose. Laissez vide pour que la méthode vous retourne la valeur actuelle
	 * @return mixed : 
	 *   * false en cas d'erreur de paramètre
	 *   * bool la valeur actuel de verbose
	 *   * T_ErorGenerator la référence de l'objet
	 */
	public function verbose($value=null) {
		if($value === null)
			return $this->_verbose;

		if(!is_bool($value))
			return false;

		$this->_verbose = $value;
		return $this;
	}
}