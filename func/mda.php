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

/**
 * array_merge_recursive does indeed merge arrays, but it converts values with duplicate
 * keys to arrays rather than overwriting the value in the first array with the duplicate
 * value in the second array, as array_merge does. I.e., with array_merge_recursive,
 * this happens (documented behavior):
 *
 * array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
 *     => array('key' => array('org value', 'new value'));
 *
 * array_merge_recursive_distinct does not change the datatypes of the values in the arrays.
 * Matching keys' values in the second array overwrite those in the first array, as is the
 * case with array_merge, i.e.:
 *
 * array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
 *     => array('key' => array('new value'));
 *
 * Parameters are passed by reference, though only for performance reasons. They're not
 * altered by this function.
 *
 * @param array $arr ....
 * @return array
 * @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
 * @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
 * @author Bryan Tong <contact@nullivex.com>
 * In the OpenLSS version array_merge_recursive_distinct is renamed to mda_merge
 * Also there is now a variable amount of merge variables
 * References have been removed to prevent unpredictable results and to support variable
 *     argument lists.
 */
function mda_merge(){
	$args = func_get_args();
	$merged = array_shift($args);
	foreach($args as $arg){
		foreach($arg as $key => $value){
			if(is_array($value) && isset($merged[$key]) && is_array($merged[$key])){
				$merged[$key] = mda_merge($merged[$key],$value);
			} else {
				$merged[$key] = $value;
			}
		}
	}
	return $merged;
}
