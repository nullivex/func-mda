<?php
/**
 *  OpenLSS - Lighter Smarter Simpler
 *
 *	This file is part of OpenLSS.
 *
 *	OpenLSS is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU Lesser General Public License as
 *	published by the Free Software Foundation, either version 3 of
 *	the License, or (at your option) any later version.
 *
 *	OpenLSS is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU Lesser General Public License for more details.
 *
 *	You should have received a copy of the 
 *	GNU Lesser General Public License along with OpenLSS.
 *	If not, see <http://www.gnu.org/licenses/>.
*/
require_once(dirname(__DIR__).'/vendor/autoload.php');
require('lss_boot.php');

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

	public function testMDAMerge(){
		$array1 = array('testing'=>'test var');
		$array2 = array('testing'=>'test var**');
		$array3 = array('testing'=>'test var***');
		$arr = mda_merge($array1);
		$this->assertEquals('test var',$arr['testing']);
		$arr = mda_merge($array1,$array2);
		$this->assertEquals('test var**',$arr['testing']);
		$arr = mda_merge($array1,$array2,$array3);
		$this->assertEquals('test var***',$arr['testing']);
	}

}
