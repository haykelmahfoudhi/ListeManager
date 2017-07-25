<?php


class SQRequestTest extends PHPUnit\Framework\TestCase {

	var $tabReq = [
			'sel1' => "SELECT * FROM table LIMIT 100;",
			'sel2' => "SELECT (2+2) calcul FROM DUAL;",
			'sel3' => "SELECT t1.a1, t1.a2 AS bla, table1.a3 'blb', id, b2 as col2, t2.*
						FROM table1 t1, table2 AS t2 JOIN table3 as t3, table;",
			'sel4' => "SELECT ftc1(blabla, random, values) as NB, tabcol, (SELECT col FROM mv) mv
						FROM table WHERE id = col;",
			'updt' => "UPDATE table SET col1 = 'ojk' WHERE id = 5;",
			'del'  => "DELETE FROM table WHERE id = 6;",
			'othr' => "DESCRIBE database MECAPROTEC"
	];

	public function testGetRequestType(){
		$this->assertEquals(RequestType::SELECT, (new SQLRequest($this->tabReq['sel1']))->getType());
		$this->assertEquals(RequestType::SELECT, (new SQLRequest($this->tabReq['sel2']))->getType());
		$this->assertEquals(RequestType::SELECT, (new SQLRequest($this->tabReq['sel3']))->getType());
		$this->assertEquals(RequestType::SELECT, (new SQLRequest($this->tabReq['sel4']))->getType());
		$this->assertEquals(RequestType::UPDATE, (new SQLRequest($this->tabReq['updt']))->getType());
		$this->assertEquals(RequestType::DELETE, (new SQLRequest($this->tabReq['del']))->getType());
		$this->assertEquals(RequestType::INSERT, (new SQLRequest("INSERT INTO table(id,a) VALUES (1, '2');"))->getType());
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
		$this->assertEquals(false, (new SQLRequest($this->tabReq['othr']))->getColumnsMeta());
	}

	public function testTableAliases(){
		$req = new SQLRequest($this->tabReq['sel3']);
		$this->assertEquals(['t1' => 'table1', 't2' => 'table2', 't3' => 'table3'], $req->getTablesAliases());
		$req = new SQLRequest($this->tabReq['sel4']);
		$this->assertEquals([], $req->getTablesAliases());
	}

	public function testLimit(){
		$req = new SQLRequest($this->tabReq['othr']);
		$this->assertNull($req->getLimit());
		$this->assertFalse($req->setLimit(null));
		$req = new SQLRequest($this->tabReq['sel1']);
		$this->assertEquals(100, $req->getLimit());
		$this->assertNull($req->setLimit(10, 'OFFSET 25'));
		$this->assertEquals(10, $req->getLimit());
		$this->assertEquals("SELECT * FROM table LIMIT 10 OFFSET 25;", $req->__toString());
		$this->assertNull($req->setLimit(null));
		$this->assertEquals("SELECT * FROM table;", $req->__toString());
		$req->prepareForOracle(true);
		$req->setLimit(20);
		$this->assertEquals("SELECT * FROM table WHERE (rownum <= '20')", $req->__toString());
	}

	public function testUserParameters(){
		$req = new SQLRequest("SELECT * FROM table WHERE id < 100 ORDER BY 2");
		$this->assertEquals([], $req->getUserParameters());
		$req->prepareForOracle(true); // Le filtre utilisateur n'est aps appliqué pour une requete ORACLE
		$req->filter(['col' => 'recherche', 'col2' => '>5', 'col3' => '2014-01-01<<2015-31-12']);
		$this->assertEquals([], $req->getUserParameters());
		$req->prepareForOracle(false);
		$req->filter(['col' => 'recherche', 'col2' => '>5', 'col3' => '2014-01-01<<2015-31-12']);
		$this->assertEquals(['recherche', 5, '2014-01-01', '2015-31-12'], array_values($req->getUserParameters()));
	}

	public function testFilter(){
		$req = new SQLRequest("SELECT * FROM table", true);
		$this->assertEquals("SELECT * FROM table", $req->__toString());
		$req->filter(['col1' => '1']);
		$this->assertEquals("SELECT * FROM table WHERE (col1 = '1')", $req->__toString());
		$req->filter(['col2' => '>=10', 'col3' => 'a%,b_']);
		$this->assertEquals("SELECT * FROM table WHERE (col1 = '1') AND (col2 >= '10') AND (col3 LIKE 'a%' OR col3 LIKE 'b_')", $req->__toString());
		$req = new SQLRequest("SELECT * FROM table", true);
		$req->filter(['col1' => '-', 'date' => '2014-02-02<<2015-06-08']);
		$this->assertEquals("SELECT * FROM table WHERE (col1 IS NULL) AND (date BETWEEN '2014-02-02' AND '2015-06-08')", $req->__toString());
		$req->prepareForOracle(false);
		$this->assertNotEquals("SELECT * FROM table WHERE (col1 IS NULL) AND (date BETWEEN '2014-02-02' AND '2015-06-08')", $req->__toString());
	}

	public function testOrderByBasis(){
		$req = new SQLRequest("SELECT * FROM table;");
		$this->assertEquals([], $req->getOrderBy());
		$req = new SQLRequest("SELECT * FROM table ORDER BY col1;");
		$this->assertEquals(['col1'], $req->getOrderBy());
		$req = new SQLRequest("SELECT * FROM table ORDER BY col2 dEsc, COLonne3 ;");
		$this->assertEquals(['-col2', 'colonne3'], $req->getOrderBy());
		$this->assertTrue($req->removeOrderBy());
		$this->assertEquals([], $req->getOrderBy());
		$req = new SQLRequest($this->tabReq['del']);
		$this->assertFalse($req->removeOrderBy());
	}

	public function testOrderByAdded(){
		$req = new SQLRequest("SELECT * FROM table ORDER BY col;");
		$this->assertEquals(['col'], $req->getOrderBy());
		$req->orderBy(['-col']);
		$this->assertEquals(['-col'], $req->getOrderBy());
		$this->assertEquals("SELECT * FROM table ORDER BY col DESC;", $req->__toString());
		$req->orderBy(['col']);
		$this->assertEquals(['col'], $req->getOrderBy());
		$req->orderBy(['*col']);
		$this->assertEquals("SELECT * FROM table;", $req->__toString());
		$this->assertEquals([], $req->getOrderBy());
	}
}
