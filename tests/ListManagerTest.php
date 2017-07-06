<?php


class ListManagerTest extends PHPUnit_Framework_TestCase {
	
	var $_lm, $_dbStub, $_ref;
	
	public function setUp(){
		$this->_dbStub = $this->getMockBuilder('Database')
			->disableOriginalConstructor()
			->setMethods(['execute'])
			->getMock();
		$this->_lm = new ListManager(null, $this->_dbStub, [ListManager::NO_VERBOSE]);
		$this->_ref = new ReflectionClass('ListManager');
	}
	
	public function testSetFilter(){
		$this->assertEmpty($this->_lm->getFilter());
		$this->_lm->setFilter(['col1' => '/ok']);
		$this->assertEquals(['col1' => '/ok'], $this->_lm->getFilter());
		$this->_lm->setFilter(['COLCAPS' => 'nocaps']);
		$this->assertEquals(['col1' => '/ok', 'colcaps' => 'nocaps'], $this->_lm->getFilter());
		$this->_lm->setFilter(['COlCAPS' => '']);
		$this->assertEquals(['col1' => '/ok'], $this->_lm->getFilter());
	}
	
	public function testManageFilter(){
		$this->assertFalse($this->_lm->issetUserFilter());
		$methode = $this->_ref->getMethod('manageFilter');
		$methode->setAccessible(true);
		
		// dev filter + 0 = false
		$this->_lm->setFilter(['col1' => '/ok']);
		$methode->invoke($this->_lm, new SQLRequest('SELECT * FROM table'), []);
		$this->assertFalse($this->_lm->issetUserFilter(), 'dev filter + 0 = false');
		
		// dev filter + userFilter = true (appel manageFilter)
		$_GET['lm_tabSelect'.$this->_lm->getId()]['col2'] = 'val';
		$methode->invoke($this->_lm, new SQLRequest('SELECT * FROM table'), []);
		$this->assertTrue($this->_lm->issetUserFilter(), 'dev filter + userFilter = true (appel manageFilter)');
		
		// 0 + userFilter = true
		$this->setUp();
		$methode->invoke($this->_lm, new SQLRequest('SELECT * FROM table'), []);
		$this->assertTrue($this->_lm->issetUserFilter(), '0 + userFilter = true');
		
		// 0 + 0 = false
		$this->setUp();
		$_GET['lm_tabSelect'.$this->_lm->getId()] = null;
		$methode->invoke($this->_lm, new SQLRequest('SELECT * FROM table'), []);
		$this->assertFalse($this->_lm->issetUserFilter(), '0 + 0 = false');
	}
	
}

?>