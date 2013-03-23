<?php
require_once(dirname(__DIR__).'/vendor/autoload.php');
require('boot.php');
ld('func/mda');

class FuncMDATest extends PHPUNIT_Framework_TestCase {

	static $test = array('test1'=>true,'test2'=>array('test3'=>false));

	public function testMDAGet(){
		$this->assertTrue(mda_get(self::$test,'test1'));
		$this->assertFalse(mda_get(self::$test,'test2.test3'));
	}

	public function testMDASet(){
		$test = self::$test;
		mda_set($test,'test1',false);
		mda_set($test,'test2','test3',true);
		$this->assertFalse(mda_get($test,'test1'));
		$this->assertTrue(mda_get($test,'test2.test3'));
	}

	public function testMDAAdd(){
		$test = self::$test;
		mda_add($test,'test2',1);
		$this->assertEquals(1,mda_get($test,'test2',0));
	}

	public function testMDADel(){
		$test = self::$test;
		mda_add($test,'test2',1);
		mda_del($test,'test2.0');
		$this->assertTrue(!isset($test['test2'][0]));
	}

	public function testMDADelValue(){
		$test = self::$test;
		mda_del_value($test,'test2',false);
		$this->assertTrue(!isset($test['test2']['test3']));
	}

	public function testMDAExists(){
		$this->assertTrue(mda_exists(self::$test,'test1'));
		$this->assertTrue(mda_exists(self::$test,'test2'));
		$this->assertTrue(mda_exists(self::$test,'test2.test3'));
		$this->assertFalse(mda_exists(self::$test,'test2.test3.test4'));
	}

	public function testMDAExistsValue(){
		$this->assertTrue(mda_exists_value(self::$test,'test2',false));
	}

	public function testMDAFlatten(){
		$test = array('row1'=>array('id'=>2),'row2'=>array('id'=>3));
		$this->assertEquals(2,count(mda_flatten($test,'id')));
	}

	public function testImplodeI(){
		$arr = array(1,2,3,4);
		$join = array(',','/','&');
		$result = '1,2/3&4';
		$this->assertEquals($result,implodei($join,$arr));
	}

	public function testMDAShift(){
		$array = array(1,2);
		$this->assertEquals(1,array_shift($array));
	}

}
