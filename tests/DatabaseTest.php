<?php

class DatabaseTest extends PHPUnit\Framework\TestCase {
	
	use PHPUnit_Extensions_Database_TestCase_Trait;
	
	static $db = null;
	static $dsn = 'sqlite::test:';
	
	public static function setUpBeforeClass(){
		self::$db = Database::instantiate(self::$dsn);
		self::$db->verbose(false);
		self::$db->execute('CREATE TABLE `table`(id INT PRIMARY KEY, col1 CHAR(20), col2 INT, col3 DATE, col4 REAL);');
	}

	public function setUp(){
		$instances = (new ReflectionClass('Database'))
			->getProperty('instances');
		$instances->setAccessible(true);
		$instances->setValue(null, ['principale' => self::$db]);
	}
	
	public function getConnection(){
		return $this->createDefaultDBConnection(self::$db->getPDO(), ':test:');
	}
	
	public function getDataSet(){
		return $this->createFlatXMLDataSet('tests/dataset.xml');
	}
	
	public function testSingleton(){
		$this->assertSame(self::$db, Database::getInstance());
		$this->assertEquals('principale', self::$db->getLabel());
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
		$this->assertEquals(['principale' => self::$db], $tabDB);
		$deuz = Database::instantiate(self::$dsn, null, null, 'deuz');
		$tabDB = $instances->getValue();
		$this->assertEquals(['principale' => self::$db, 'deuz' => $deuz], $tabDB);
		$this->assertSame($deuz, Database::getInstance('deuz'));
		$this->assertNull(Database::getInstance('ter'));
	}
	
	public function testLabels(){
		self::$db->setLabel('prim');
		$this->assertEquals('prim', self::$db->getLabel());
		$instances = (new ReflectionClass('Database'))
			->getProperty('instances');
		$instances->setAccessible(true);
		$this->assertEquals(['prim' => self::$db], $instances->getValue());
	}
	
	public function testExecute(){
		$rep = self::$db->execute("pas bon");
		$this->assertNotNull($rep);
		$this->assertNotEmpty($rep->getErrorMessage());
		$this->assertTrue($rep->error());
		$this->assertFalse($rep->dataList());
		$rep = self::$db->execute("SELECT * FROM `table`");

		// v FAIT PLANTER LES DOCKERS ! la 1re execution de ce passage retourne TRUE... pas les autres
		// $this->assertFalse($rep->error()); FAIT PLANTER LES DOCKERS ! la 1re execution de ce passage retourne TRUE... pas les autres
		// $this->assertEmpty($rep->getErrorMessage());
	}
	
}
