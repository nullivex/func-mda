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

// define('MDA_DEBUG',true);

function _mda_get_var($args=array(),$strip_chars=true){
	$var = '';
	$parts = array();
	foreach($args as $v){
		if(is_null($v)) continue;
		$parts = array_merge($parts,explode('.',$v));
	}
	if(defined('MDA_DEBUG')) echo "MDA Parsed Path: ".implode($parts,'.')."\n";
	foreach($parts as $part){
			if($strip_chars && strspn($part,';$()[]{}=+@!% ') != 0){
					if(defined('MDA_DEBUG') && MDA_DEBUG === true) throw new Exception('Invalid MDA path passed: '.implode('.',$parts));
					else continue;
			}
			// if($strip_chars) $part = preg_replace('/[;$()\[\]\{\}\s=+@!%]+/','',$part);
			$var .= "['".$part."']";
	}
	$var = '$arr'.$var;
	return $var;
}

function mda_get(&$arr,$path=null){
	$args = func_get_args(); array_shift($args);
	$var = _mda_get_var($args);
	eval('$val = isset('.$var.') ? '.$var.' : null;');
	return $val;
}

//VALUE WILL ALWAYS BE THE LAST ARG
function mda_set(&$arr,$path=null){
	$args = func_get_args(); array_shift($args);
	$value = array_pop($args);
	$var = _mda_get_var($args);
	eval('$val = '.$var.' = $value;');
	return $val;
}

//VALUE WILL ALWAYS BE THE LAST ARG
function mda_add(&$arr,$path=null){
	$args = func_get_args(); array_shift($args);
	$value = array_pop($args);
	$var = _mda_get_var($args);
	eval('$val = '.$var.'[] = $value;');
	return $val;
}

function mda_del(&$arr,$path=null){
	$args = func_get_args(); array_shift($args);
	$var = _mda_get_var($args);
	eval('unset('.$var.');');
	return true;
}

//VALUE WILL ALWAYS BE THE LAST ARG
function mda_del_value(&$arr,$path=null){
	$args = func_get_args(); array_shift($args);
	$value = array_pop($args);
	$var = _mda_get_var($args);
	eval('$val =& '.$var.';');
	foreach(array_keys($val,$value) as $key) unset($val[$key]);
	return $val;
}

//VALUE WILL ALWAYS BE THE LAST ARG
function mda_exists_value(&$arr,$path=null){
	$rv = false;
	$args = func_get_args(); array_shift($args);
	$value = array_pop($args);
	$var = _mda_get_var($args);
	eval('$val =& '.$var.';');
	if(!is_array($val)) return false;
	foreach(array_keys($val,$value) as $key) $rv = true;
	return $rv;
}

function mda_exists(&$arr,$path){
	$args = func_get_args(); array_shift($args);
	$var = _mda_get_var($args);
	eval('$val = isset('.$var.') ? true : false;');
	return $val;
}

//NOTE this does not take a path it takes a keyname
//	use mda_get to find the lowest possible path and pass that
//	to this functions eg: $arr = mda_flatten(mda_get($arr,'path1.path2.path3'),'row_id);
function mda_flatten(&$arr,$keyname){
	$rv = array();
	foreach($arr as $row){
		if(!isset($row[$keyname])) continue;
		$rv[] = $row[$keyname];
	}
	return $rv;
}

function implodei($join,$arr=array()){ //improved join that accepts arrays of join
	if(!is_array($arr)) return $arr;
	if(!is_array($join)) return implode($join,$arr);
	//improved functionality
	$str = '';
	foreach($arr as $v){
		$j = array_shift($join);
		$str .= $v.$j;
	}
	return rtrim($str,$j);
}

//THIS WILL NOT INCREASE THE ARRAYS POINTER (DOES NOT FUNCTION LIKE ARRAY_SHIFT)
function mda_shift($arr){
	return array_shift($arr);
}
