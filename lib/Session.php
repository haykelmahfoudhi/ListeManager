<?php

/**
* 
*/
class Session {
	
	private static $instance = null;

	private function __construct() {
		if(session_status() == PHP_SESSION_NONE)
			session_start();
	}

	public static function getInstance() {
		if(self::$instance == null)
			self::$instance = new self();

		return self::$instance;
	}

	public function connecterUtilisateur($identifiant) {

	}

	public function estConnecte() {

	}

	public function detruire() {
		session_destroy();
	}
}