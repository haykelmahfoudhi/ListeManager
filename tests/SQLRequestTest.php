<?php


class SQRequestTest extends PHPUnit_Framework_TestCase {
	
	var $tabReq = [
			'sel1' => "SELECT * FROM table LIMIT 100;",
			'sel2' => "SELECT (2+2) calcul FROM DUAL;",
			'sel3' => "SELECT t1.a1, t1.a2 AS bla, table1.a3 'blb', id, b2 as col2, t2.*
						FROM table1 t1, table2 AS t2 JOIN table3 as t3, table;",
			'sel4' => "SELECT ftc1(blabla, random, values) as NB, tabcol, (SELECT col FROM mv) mv
						FROM table WHERE id = col;",
			'updt' => "UPDATE table SET col1 = 'ojk' WHERE id = 5;",
			'del'  => "DELETE FROM table WHERE id = 6;",
			'othr' => "DESCRIBE database MECAPROTEC;"
	];
	
	public function testGetRequestType(){
		$this->assertEquals(RequestType::SELECT, (new SQLRequest($this->tabReq['sel1']))->getType());
		$this->assertEquals(RequestType::SELECT, (new SQLRequest($this->tabReq['sel2']))->getType());
		$this->assertEquals(RequestType::SELECT, (new SQLRequest($this->tabReq['sel3']))->getType());
		$this->assertEquals(RequestType::SELECT, (new SQLRequest($this->tabReq['sel4']))->getType());
		$this->assertEquals(RequestType::UPDATE, (new SQLRequest($this->tabReq['updt']))->getType());
		$this->assertEquals(RequestType::DELETE, (new SQLRequest($this->tabReq['del']))->getType());
		$this->assertEquals(RequestType::AUTRE, (new SQLRequest($this->tabReq['othr']))->getType());
	}

	public function testColumnsMeta(){
		$req = new SQLRequest($this->tabReq['sel2']);
		$this->assertEquals(['name' => '(2+2)', 'table' => null, 'alias' => 'calcul'], (array) $req->getColumnsMeta()[0]);
		$req = new SQLRequest($this->tabReq['sel3']);
		$expected = [
				(Object)['name' => 'a1', 'table' => 't1', 'alias' => null],
				(Object)['name' => 'a2', 'table' => 't1', 'alias' => 'bla'],
				(Object)['name' => 'a3', 'table' => 'table1', 'alias' => "blb"],
				(Object)['name' => 'id', 'table' => null, 'alias' => null],
				(Object)['name' => 'b2', 'table' => null, 'alias' => 'col2'],
				(Object)['name' => '*', 'table' => 't2', 'alias' => null]
		];
		$this->assertEquals($expected, (array) $req->getColumnsMeta());
		$this->assertEquals([], (new SQLRequest($this->tabReq['othr']))->getColumnsMeta());
	}

	public function testTableAliases(){
		$req = new SQLRequest($this->tabReq['sel3']);
		$this->assertEquals(['t1' => 'table1', 't2' => 'table2', 't3' => 'table3'], $req->getTablesAliases());
		$req = new SQLRequest($this->tabReq['sel4']);
		$this->assertEquals([], $req->getTablesAliases());
	}

	public function testLimit(){
		$req = new SQLRequest($this->tabReq['sel1']);
		$this->assertEquals(100, $req->getLimit());
		$req = new SQLRequest($this->tabReq['othr']);
		$this->assertNull($req->getLimit());
	}
}