<?php


class ListManagerTest extends PHPUnit\Framework\TestCase {

	var $_lm, $_dbStub, $_ref;

	public function setUp(){
		$this->_dbStub = $this->getMockBuilder('Database')
			->disableOriginalConstructor()
			->setMethods(['execute', 'oracle'])
			->getMock();
		$this->_lm = new ListManager(null, $this->_dbStub, [ListManager::NO_VERBOSE]);
		$this->_ref = new ReflectionClass('ListManager');
	}

	public function provideRepData(){
		$repStub = $this->getMockBuilder('RequestResponse')
			->disableOriginalConstructor()
			->setMethods(['error', 'dataList', 'nextLine', 'getErrorMessage', 'getColumnsMeta', 'getColumnsCount'])
			->getMock();
		$repStub->expects($this->any())->method('error')->willReturnOnConsecutiveCalls(true, false, false);
		$repStub->expects($this->any())->method('getErrorMessage')->willReturn('Unit Test');
		$repStub->expects($this->any())->method('getColumnsCount')->willReturn(2);
		$repStub->expects($this->any())->method('getColumnsMeta')
			->willReturn([
					(Object)['name'=>'col1', 'table'=>null, 'alias'=>null],
					(Object)['name'=>'col2', 'table'=>null, 'alias'=>null]
			]);

		$data  = [['val1', 'val2'],['val3', 'val3']];
		$repStub->expects($this->any())->method('dataList')->willReturn($data);
		$repStub->expects($this->any())->method('nextLine')->willReturnOnConsecutiveCalls($data[0],$data[1], null);
		return [[$repStub, $data]];
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

	/**
	 * @dataProvider provideRepData
	 */
	public function testGenerateArray($repStub, $data){
		$methode = $this->_ref->getMethod('generateArray');
		$methode->setAccessible(true);
		$this->_dbStub->expects($this->any())->method('oracle')->willReturnOnConsecutiveCalls(true, false, true);
		$this->assertNull($methode->invoke($this->_lm, $repStub));
		$this->assertEquals($data, $methode->invoke($this->_lm, $repStub));
		$this->assertEquals($data, $methode->invoke($this->_lm, $repStub));
	}

	/**
	 * @dataProvider provideRepData
	 */
	public function testGenerateJSON($repStub, $data){
		$methode = $this->_ref->getMethod('generateJSON');
		$methode->setAccessible(true);
		$excepeted = (Object) ['error' => true, 'errorMessage' => 'Unit Test', 'data' => null];
		$this->assertEquals($excepeted, json_decode($methode->invoke($this->_lm, $repStub)));
		$excepeted = (Object) ['error' => false, 'data' => $data];
		$this->assertEquals($excepeted, json_decode($methode->invoke($this->_lm, $repStub)));
		$excepeted = (Object) ['error' => false, 'data' => $data];
		$this->assertEquals($excepeted, json_decode($methode->invoke($this->_lm, $repStub)));
	}

	/**
	 * @dataProvider provideRepData
	 */
	public function testGenerateExcel($repStub, $data){
		$_SERVER['REQUEST_URI'] = 'Unit Test';
		$methode = $this->_ref->getMethod('generateExcel');
		$methode->setAccessible(true);
		$this->assertFalse($methode->invoke($this->_lm, $repStub));
		$this->assertTrue(file_exists($methode->invoke($this->_lm, $repStub)));
	}
	
	public function testGetIdealColumnsWidth(){
		$this->_lm->setLongColWidth(20);
		$this->assertFalse($this->_lm->getIdealColumnsWidth([], 5, 4));
		$this->assertFalse($this->_lm->getIdealColumnsWidth([], 4, 15));
		$this->assertFalse($this->_lm->getIdealColumnsWidth([[0]], 5, 4));
		$data = [
			['123', '1234', '123456', '123456789', 'Controle ressuage S2 suivant l\'AITM6-1001 issue 11 et l\'IF7115100002 conforme et repérée avec une étiquette "pièces en litige" Elle est soumise à vos services. Salutations AF: 8731476'],
			['1234567', '123', '123456', '1234567', 'test un peu moins long que le précédent dans la emme colonne, mais bon un peu quand même histoire de pouvoir tester si ça marche bien avec des colonnes extra longues t\as vu']	
		];
		$this->assertEquals([7, 4, 6, 9, 20], $this->_lm->getIdealColumnsWidth($data, 0, 11));
		$this->_lm->setLongColWidth(50);
		$this->assertEquals([7, 5, 6, 8, 50], $this->_lm->getIdealColumnsWidth($data, 5, 8));
	}

}

?>
