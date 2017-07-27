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
		if($exists = file_exists(LM_SRC.'dbs.json'))
			exec('mv '.LM_SRC.'dbs.json '.LM_SRC.'dbs.json.old');
		$this->expectException('Exception');
		$this->expectExceptionMessage('Fichier de configuration inexistant');
		$meth->invoke($this->_api, 'test');
		file_put_contents(LM_SRC.'dbs.json', 'erreur json');
		$this->expectException('Exception');
		$this->expectExceptionMessage('Fichier de configuration non valide');
		$meth->invoke($this->_api, 'test');
		
		// Construction du fichier
		$dbs = new stdClass();
		$dbs->test = new stdClass();
		$dbs->test->dsn = 'sqlite::test:';
		file_put_contents(LM_SRC.'dbs.json', json_encode($dbs));
		$this->assertNull($meth->invoke($this->_api, 'test'));
		$this->assertInstanceOf('Database', $meth->invoke($this->_api, 'test'));
		
		// RaZ
		unlink(LM_SRC.'dbs.json');
		if($exists)
			exec('mv '.LM_SRC.'dbs.json.old '.LM_SRC.'dbs.json');
	}
	
	public function testConnect(){
		$this->assertFalse($this->_api->connect('', '', '', ''));
		$this->assertEquals('Impossible de se connecter à la base de données', $this->_api->getLastError());
	}
	
}

?>