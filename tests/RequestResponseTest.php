<?php

class RequestResponseTest extends PHPUnit\Framework\TestCase {
	
	use PHPUnit_Extensions_Database_TestCase_Trait;
	
	static $db = null;
	
	public static function setUpBeforeClass(){
		try {
			self::$db = Database::instantiate(DatabaseTest::$dsn);
			self::$db->verbose(false);
			self::$db->execute('CREATE TABLE `table`(id INT PRIMARY KEY, col1 CHAR(20), col2 INT, col3 DATE, col4 REAL);');
		} catch (InvalidArgumentException $e){
			self::$db = Database::getInstance();
			if(self::$db === null){
				var_dump(Database::getAllErrorMessages());
				die();
			}
		}
	}
	
	public function getConnection(){
		return $this->createDefaultDBConnection(self::$db->getPDO(), ':test:');
	}
	
	public function getDataSet(){
		return $this->createFlatXMLDataSet('tests/dataset.xml');
	}
	
	public function testError(){
		$rep = new RequestResponse(null, true, 'unit test');
		$this->assertTrue($rep->error());
		$this->assertEquals('unit test', $rep->getErrorMessage());
		$rep = new RequestResponse(null, false, null);
		$this->assertTrue($rep->error());
		$pdosStub = $this->getMockBuilder('PDOStatement')
			->disableOriginalConstructor()
			->getMock();
		$rep = new RequestResponse($pdosStub);
		$this->assertFalse($rep->error());
	}

	public function testGetPDOStatement() {
		$rep = new RequestResponse(null);
		$this->assertNull($rep->getPDOStatement());
		$pdosStub = $this->getMockBuilder('PDOStatement')
			->disableOriginalConstructor()
			->getMock();
		$rep = new RequestResponse($pdosStub);
		$this->assertSame($pdosStub, $rep->getPDOStatement());
	}
	
	public function testNextLine(){
		$rep = new RequestResponse(null, true, 'unit test');
		$this->assertFalse($rep->nextLine());
		$rep = self::$db->execute("SELECT 1+1;");
		$this->assertEquals([2, '1+1' => 2], $rep->nextLine());
		$this->assertFalse($rep->nextLine());
		$rep = self::$db->execute("SELECT 1+1;");
		$this->assertEquals([2], $rep->nextLine(PDO::FETCH_NUM));
	}
	
	public function testDataList(){
		$rep = new RequestResponse(null, true, 'unit test');
		$this->assertFalse($rep->dataList());
		$rep = self::$db->execute("SELECT 1+1;");
		$this->assertEquals([[2, '1+1' => 2]], $rep->dataList());
		$this->assertEquals([[2, '1+1' => 2]], $rep->dataList());
		$rep = self::$db->execute("SELECT 1+1;");
		$this->assertEquals([[2]], $rep->dataList(PDO::FETCH_NUM));
	}
	
	public function testGetColumnsCount(){
		$rep = new RequestResponse(null, true, 'unit test');
		$this->assertEquals(-1, $rep->getColumnsCount());
		$rep = self::$db->execute("DELETE FROM `table` WHERE `id`=42;");
		$this->assertEquals(0, $rep->getColumnsCount());
		$rep = self::$db->execute("SELECT * FROM `table`;");
		$xmlColsData = $this->getDataSet()->getTableMetaData('table')->getColumns();
		$this->assertEquals(count($xmlColsData), $rep->getColumnsCount());
	}
	
	public function testGetRowsCount(){
		$rep = new RequestResponse(null, true, 'unit test');
		$this->assertEquals(-1, $rep->getRowsCount());
		$rep = self::$db->execute("DELETE FROM `table` WHERE `id`=42;");
		$this->assertEquals(0, $rep->getRowsCount());
		$rep = self::$db->execute("SELECT * FROM `table`;");
		$xmlIterator = $this->getDataSet()->getIterator();
		$expected = 0;
		while ($xmlIterator->next())
			$expected++;
		$this->assertEquals($expected, $rep->getRowsCount());
	}
	
	public function testColumnsMeta(){
		$pdosStub = $this->getMockBuilder('PDOStatement')
			->disableOriginalConstructor()
			->setMethods(['getColumnMeta', 'columnCount'])
			->getMock();
		$pdosStub->expects($this->any())->method('getColumnMeta')->willReturn(
				// Metas du 1er test ...
				['native_type' => 'INT', 'len' => 10, 'name' => 'id'],
				['native_type' => 'DATE', 'len' => 8, 'name' => 'col1'],
				['native_type' => 'INT', 'len' => 10, 'name' => 'col2'],
				['native_type' => 'CHAR', 'len' => 20, 'name' => 'col3'],
				['native_type' => 'REAL', 'len' => 10, 'name' => 'col4'],
				// ... Metas du 2e test
				['native_type' => 'INT', 'len' => 10, 'name' => 'id'],
				['native_type' => 'DATE', 'len' => 8, 'name' => 'col1'],
				['native_type' => 'INT', 'len' => 10, 'name' => 'col2'],
				['native_type' => 'CHAR', 'len' => 20, 'name' => 'col3'],
				['native_type' => 'REAL', 'len' => 10, 'name' => 'col4']
		);
		$pdosStub->expects($this->any())->method('columnCount')->willReturn(5);
		$rep = new RequestResponse($pdosStub);
		$expected = [
				(Object) ['name' => 'id', 'type' => 'INT', 'len' => 10, 'table' => null, 'alias' => null],
				(Object) ['name' => 'col1', 'type' => 'DATE', 'len' => 8, 'table' => null, 'alias' => null],
				(Object) ['name' => 'col2', 'type' => 'INT', 'len' => 10, 'table' => null, 'alias' => null],
				(Object) ['name' => 'col3', 'type' => 'CHAR', 'len' => 20, 'table' => null, 'alias' => null],
				(Object) ['name' => 'col4', 'type' => 'REAL', 'len' => 10, 'table' => null, 'alias' => null]
		];
		$this->assertEquals($expected, $rep->getColumnsMeta());
		$req = new SQLRequest("SELECT t.id AS 'alias_id', col1, `table`.col2, col3 AS 'yolo', a.col4 FROM `table` t;");
		$rep = new RequestResponse($pdosStub);
		$rep->setColumnsMeta($req->getColumnsMeta());
		$expected = [
				(Object) ['name' => 'id', 'type' => 'INT', 'len' => 10, 'table' => 't', 'alias' => 'alias_id'],
				(Object) ['name' => 'col1', 'type' => 'DATE', 'len' => 8, 'table' => null, 'alias' => null],
				(Object) ['name' => 'col2', 'type' => 'INT', 'len' => 10, 'table' => 'table', 'alias' => null],
				(Object) ['name' => 'col3', 'type' => 'CHAR', 'len' => 20, 'table' => null, 'alias' => 'yolo'],
				(Object) ['name' => 'col4', 'type' => 'REAL', 'len' => 10, 'table' => 'a', 'alias' => null]
		];
		$this->assertEquals($expected, $rep->getColumnsMeta());
	}
}