<?php

class ListTemplateTest extends PHPUnit_Framework_TestCase {
	
	var $_lt, $_stubRep, $_stubLM;
	
	public function setUp(){
		$this->_stubLM = $this->getMockBuilder('ListManager')
			->setMethods([])
			->disableOriginalConstructor()
			->getMock();
		
		$this->_stubRep = $this->getMockBuilder('RequestResponse')
			->setMethods(['error', 'dataList', 'nextLine', 'getErrorMessage', 'getColumnsMeta', 'getColumnsCount'])
			->disableOriginalConstructor()
			->getMock();
		$this->_stubRep->expects($this->any())->method('error')->willReturnOnConsecutiveCalls(true, false, false);
		$this->_stubRep->expects($this->any())->method('getErrorMessage')->willReturn('Unit Test');
		$this->_stubRep->expects($this->any())->method('getColumnsCount')->willReturn(2);
		$this->_stubRep->expects($this->any())->method('getColumnsMeta')
			->willReturn([
					(Object)['name'=>'col1', 'table'=>null, 'alias'=>null],
					(Object)['name'=>'col2', 'table'=>null, 'alias'=>null]
			]);
		$data  = [['val1', 'val2'],['val3', 'val3']];
		$this->_stubRep->expects($this->any())->method('dataList')->willReturn($data);
		$this->_stubRep->expects($this->any())->method('nextLine')->willReturnOnConsecutiveCalls($data[0],$data[1], null);
		$this->_lt = new ListTemplate($this->_stubLM);
	}
	
	public function testGeneratePaging(){
		$meth = (new ReflectionClass('ListTemplate'))
			->getMethod('generatePaging');
		$meth->setAccessible(true);
		$this->_lt->setPagingLinksNb(false);
		$this->assertEmpty($meth->invoke($this->_lt, 20000));
		$this->_lt->setPagingLinksNb(10);
		$this->_lt->setNbResultsPerPage(100);
		$this->assertEmpty($meth->invoke($this->_lt, 10));
		$this->assertNotEmpty($meth->invoke($this->_lt, 1000));
	}
	
}