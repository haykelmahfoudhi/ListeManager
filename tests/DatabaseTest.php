<?php


class DatabaseTest extends PHPUnit_Framework_TestCase {
	
	private $_pdoStub, $_pdoOCIStub;
	
	public function setUp(){
		$this->_pdoStub = $this->getMockBuilder('PDO')
 			->disableOriginalConstructor()
 			->setMethods(['execute', 'query', 'prepare', '__construct'])
 			->getMock();
		echo 'ok';
	}
	
	public function testSingleton(){
// 		$this->_pdoStub->expects($this->any())->method('__construct')->will($this->return(true));
		$db = Database::instantiate('test:dbname=test;', 'root', 'toor');
		$this->assertNotNull($db);
		$this->assertEquals('principale', $db->getLabel());
	}
	
	public function testMultiton(){
		
	}
	
	public function testExecute(){
		
	}
	
	public function testDescribe(){
		
	}
	
	public function testLabels(){
		
	}
	
	public function testEstOracle(){
		
	}
	
}