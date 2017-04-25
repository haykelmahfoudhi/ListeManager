<?php

/**
* 
* @author RookieRed
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

	public function connectUser($identifiant) {

	}

	public function userConnected() {

	}

	public function destroy() {
		session_destroy();
	}
}