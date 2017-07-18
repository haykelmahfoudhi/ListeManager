<?php

class DatabaseTest extends PHPUnit\Framework\TestCase {
	
	use PHPUnit_Extensions_Database_TestCase_Trait;
	
	var $_pdo = null;
	
	public function getConnection(){
		if($this->_pdo == null){
			$this->_pdo = new PDO('sqlite::test:');
			$this->_pdo->exec('CREATE TABLE `table`(id INT PRIMARY KEY, col1 CHAR(20), col2 INT, col3 DATE, col4 REAL);');
		}
		return $this->createDefaultDBConnection($this->_pdo, ':test:');
	}
	
	public function getDataSet(){
		return $this->createFlatXMLDataSet('tests/dataset.xml');
	}
	
	public function test(){
		$this->assertTrue(true);
	}
	
}