<?php


/**
 * Singleton pour la gestion des sessions PHP
 * 
 * @author RookieRed
 */
class Session {
	
	private static $instance = null;

	private function __construct() {
		if(session_status() == PHP_SESSION_NONE)
			session_start();
	}

	/**
	 * session_start et retourne l'objet session
	 */
	public static function start() {
		if(self::$instance == null)
			self::$instance = new self();

		return self::$instance;
	}

	public function connectUser($identifiant) {

	}

	public function userConnected() {

	}

	public function destroy() {
		session_destroy();
	}
}