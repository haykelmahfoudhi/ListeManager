<?php

class DatabaseTest extends PHPUnit\Framework\TestCase {
	
	use PHPUnit_Extensions_Database_TestCase_Trait;
	
	var $_db = null;
	static $dsn = 'sqlite::test:';
	
	public function setUp(){
		$instances = (new ReflectionClass('Database'))
			->getProperty('instances');
		$instances->setAccessible(true);
		$instances->setValue(null, []);
		$this->_db = Database::instantiate(self::$dsn);
		$this->_db->execute('CREATE TABLE `table`(id INT PRIMARY KEY, col1 CHAR(20), col2 INT, col3 DATE, col4 REAL);');
	}
	
	public function getConnection(){
		return $this->createDefaultDBConnection($this->_db->getPDO(), ':test:');
	}
	
	public function getDataSet(){
		return $this->createFlatXMLDataSet('tests/dataset.xml');
	}
	
	public function testSingleton(){
		$this->assertSame($this->_db, Database::getInstance());
		$this->assertEquals('principale', $this->_db->getLabel());
		$this->expectException('InvalidArgumentException');
		Database::instantiate(self::$dsn);
	}
	
	public function testGetErrorMessages(){
		$this->assertEquals(['principale' => []], Database::getAllErrorMessages());
	}
	
	public function testMultiton(){
		$instances = (new ReflectionClass('Database'))
			->getProperty('instances');
		$instances->setAccessible(true);
		$tabDB = $instances->getValue();
		$this->assertEquals(['principale' => $this->_db], $tabDB);
		$deuz = Database::instantiate(self::$dsn, null, null, 'deuz');
		$tabDB = $instances->getValue();
		$this->assertEquals(['principale' => $this->_db, 'deuz' => $deuz], $tabDB);
		$this->assertSame($deuz, Database::getInstance('deuz'));
		$this->assertNull(Database::getInstance('ter'));
	}
	
	public function testLabels(){
		$this->_db->setLabel('prim');
		$this->assertEquals('prim', $this->_db->getLabel());
		$instances = (new ReflectionClass('Database'))
			->getProperty('instances');
		$instances->setAccessible(true);
		$this->assertEquals(['prim' => $this->_db], $instances->getValue());
	}
	
	public function testExecute(){
		$rep = $this->_db->execute("pas bon");
		$this->assertNotNull($rep);
		$this->assertNotEmpty($rep->getErrorMessage());
		$this->assertTrue($rep->error());
		$this->assertFalse($rep->dataList());
		$rep = $this->_db->execute("SELECT * FROM `table`");
		$this->assertFalse($rep->error());
		$this->assertEmpty($rep->getErrorMessage());
	}
	
}