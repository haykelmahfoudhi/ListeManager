<?php

class ListTemplateTest extends PHPUnit_Framework_TestCase {
	
	var $_lt, $_stubRep, $_stubLM;
	
	public function setUp(){
		// Bouchon ListManager utilisé comme attribut du template
		$this->_stubLM = $this->getMockBuilder('ListManager')
			->setMethods(['isSearchEnabled', 'getFilter', 'isMasked', 'isOrderByEnabled', 'isExcelEnabled'])
			->disableOriginalConstructor()
			->getMock();
		$this->_stubLM->expects($this->any())->method('isSearchEnabled')->willReturnOnConsecutiveCalls(false, true);
		$this->_stubLM->expects($this->any())->method('getFilter')->willReturn([]);
		$this->_stubLM->expects($this->any())->method('isMasked')->willReturn(false);
		$this->_stubLM->expects($this->any())->method('isOrderByEnabled')->willReturn(false);
		$this->_stubLM->expects($this->any())->method('isExcelEnabled')->willReturn(false);
		
		// Bouchon ReponseRequest
		$this->_stubRep = $this->getMockBuilder('RequestResponse')
			->setMethods(['error', 'dataList', 'nextLine', 'getErrorMessage', 'getColumnsMeta', 'getColumnsCount'])
			->disableOriginalConstructor()
			->getMock();
		$this->_stubRep->expects($this->any())->method('error')->willReturnOnConsecutiveCalls(true, false, false);
		$this->_stubRep->expects($this->any())->method('getErrorMessage')->willReturn('Unit Test');
		$this->_stubRep->expects($this->any())->method('getColumnsCount')->willReturn(2);
		$this->_stubRep->expects($this->any())->method('getColumnsMeta')->willReturn([
					(Object)['name'=>'col1', 'table'=>null, 'alias'=>null],
					(Object)['name'=>'col2', 'table'=>'a', 'alias'=>null]
			]);
		$data  = [['val1', 'val2'],['val3', 'val3']];
		$this->_stubRep->expects($this->any())->method('dataList')->willReturn($data);
		$this->_stubRep->expects($this->any())->method('nextLine')->willReturnOnConsecutiveCalls($data[0],$data[1], null);
		
		// Initialistaion du template
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
	
	public function testGenerateContent(){
		$this->_lt->setErrorMessageClass(null);
		$this->_lt->setEmptyListMessage('Unit Test');
		$this->_lt->setRowsClasses(null, null);
		$meth = (new ReflectionClass('ListTemplate'))
			->getMethod('generateContent');
		$meth->setAccessible(true);
		$donnees = [[]];
		$colonnes = $this->_stubRep->getColumnsMeta();
		$this->assertEquals("</table>\n<p>Unit Test</p>",
				$meth->invoke($this->_lt, $donnees, []));
		$donnees = $this->_stubRep->dataList();
		$this->assertEquals("<tr ><td>val1</td><td>val2</td></tr>\n"
				."<tr ><td>val3</td><td>val3</td></tr>\n</table>\n", $meth->invoke($this->_lt, $donnees, $colonnes));
	}
	
	public function testGenerateSearch(){
		$meth = (new ReflectionClass('ListTemplate'))
			->getMethod('generateSearchInputs');
		$meth->setAccessible(true);
		$metas = $this->_stubRep->getColumnsMeta();
		$this->assertEmpty($meth->invoke($this->_lt, $metas, [5,6]));
		$this->assertEquals('<tr class=\'tabSelect\' style="display:none;" ><td><input type="text" name="lm_tabSelect[col1]" form=\'recherche\' size=\'5\' value=\'\'/></td>'
				.'<td><input type="text" name="lm_tabSelect[a.col2]" form=\'recherche\' size=\'6\' value=\'\'/></td></tr>'."\n",
				$meth->invoke($this->_lt, $metas, [5,6]));
	}
	
	public function testGenerateTitles(){
		$meth = (new ReflectionClass('ListTemplate'))
			->getMethod('generateTitles');
		$meth->setAccessible(true);
		$metas = $this->_stubRep->getColumnsMeta();
		$this->_lt->enableJSMask(false);
		$this->assertEmpty($meth->invoke($this->_lt, []));
		$this->assertEquals("<tr class='ligne-titres'><th>col1</th>\n<th>col2</th>\n</tr>\n", $meth->invoke($this->_lt, $metas));
	}
	
	public function testGenerateButtons(){
		$meth = (new ReflectionClass('ListTemplate'))
			->getMethod('generateButtons');
		$meth->setAccessible(true);
		$this->_lt->enableJSMask(false);
		$this->_lt->setHelpLink(null);
		$this->assertEquals("\n<div><div class='boutons-options'></div>\n", $meth->invoke($this->_lt));
	}
	
}