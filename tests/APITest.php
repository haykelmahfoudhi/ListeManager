<?php

class APITest extends PHPUnit\Framework\TestCase {
	
	var $_api,
		$_rc,
		$_sessionStub;
	
	function setUp(){
		$this->_rc = new ReflectionClass('API');
		$this->_api = $this->_rc->newInstanceWithoutConstructor();
		// Création du bouchon session
		$this->_sessionStub = $this->getMockBuilder('SessionAPI')
			->disableOriginalConstructor()
			->setMethods(['getDatabase', 'isStarted'])
			->getMock();
		// Set le bouchon comme attribut de l'api
		$sessAttr = $this->_rc->getProperty('_session');
		$sessAttr->setAccessible(true);
		$sessAttr->setValue($this->_api, $this->_sessionStub);
	}
	
	function tearDown(){
		$rc = new ReflectionClass('Database');
		$instances = $rc->getProperty('instances');
		$instances->setAccessible(true);
		$instances->setValue(null, []);
	}
	
	public function testGetDbFromConf(){
		$meth = (new ReflectionClass('API'))
			->getMethod('getDatabaseFromConf');
		$meth->setAccessible(true);
		
		// Tests exceptions
		$this->moveConf();
		$this->expectException('Exception');
		$this->expectExceptionMessage('Fichier de configuration inexistant');
		$meth->invoke($this->_api, 'test');
		file_put_contents(LM_SRC.'dbs.json', 'erreur json');
		$this->expectException('Exception');
		$this->expectExceptionMessage('Fichier de configuration non valide');
		$meth->invoke($this->_api, 'test');
		
		// Construction du fichier
		$this->createConf();
		file_put_contents(LM_SRC.'dbs.json', json_encode($dbs));
		$this->assertNull($meth->invoke($this->_api, 'test'));
		$this->assertInstanceOf('Database', $meth->invoke($this->_api, 'test'));
		
		// RaZ
		$this->resetConf();
	}
	
	public function testConnect(){
		$this->assertFalse($this->_api->connect('', '', '', ''));
		$this->assertEquals('Impossible de se connecter à la base de données', $this->_api->getLastError());
		$this->assertTrue($this->_api->connect(DatabaseTest::$dsn, '', '', ''));
		$this->tearDown();
		$this->setUp();
		$this->moveConf();
		$this->createConf();
		$this->assertTrue($this->_api->connect('', null, null, 'test'));
		$this->resetConf();
	}
	
	public function testDisconnect(){
		$this->_api->connect(DatabaseTest::$dsn, '', '', '');
		$this->assertTrue(true);
		
	}
	
	private function moveConf(){
		if(file_exists(LM_SRC.'dbs.json'))
			exec('mv '.LM_SRC.'dbs.json '.LM_SRC.'dbs.json.old');
	}
	
	private function createConf(){
		$dbs = new stdClass();
		$dbs->test = new stdClass();
		$dbs->test->dsn = DatabaseTest::$dsn;
		file_put_contents(LM_SRC.'dbs.json', json_encode($dbs));
	}
	
	private function resetConf(){
		unlink(LM_SRC.'dbs.json');
		if(file_exists(LM_SRC.'dbs.json.old'))
			exec('mv '.LM_SRC.'dbs.json.old '.LM_SRC.'dbs.json');
	}
	
}

?>