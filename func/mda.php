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

//define('MDA_DEBUG',true);

//action constants
define('MDA_PATH_GET',0);
define('MDA_PATH_EXISTS',1);
define('MDA_PATH_ADD',2);
define('MDA_PATH_DEL',3);
define('MDA_PATH_DEL_VALUE',4);
define('MDA_PATH_EXISTS_VALUE',5);

/*
 * User friendly functions
 */
function mda_get(&$arr,$path=null){
	$path = __mda_get_path(func_get_args());
	return __mda_path_action($arr,$path,MDA_PATH_GET);
}

//VALUE WILL ALWAYS BE THE LAST ARG
//	this is different than the rest of the functions
//	it needs to actually traverse the path and create as it goes
function mda_set(&$arr,$path=null){
	$args = func_get_args();
	$set = array_pop($args);
	$path = __mda_get_path($args);
	//traverse the path creating as we go and setting the last value
	$parts = explode('.',$path);
	$lpart = array_pop($parts);
	foreach($parts as $k => $part){
		if(!isset($arr[$part])) $arr[$part] = array();
		$arr = &$arr[$part];
	}
	//set the value
	$arr[$lpart] = $set;
	return $set;
}

//VALUE WILL ALWAYS BE THE LAST ARG
function mda_add(&$arr,$path=null){
	$args = func_get_args();
	$set = array_pop($args);
	$path = __mda_get_path($args);
	return __mda_path_action($arr,$path,MDA_PATH_ADD,$set);
}

function mda_del(&$arr,$path=null){
	$path = __mda_get_path(func_get_args());
	return __mda_path_action($arr,$path,MDA_PATH_DEL);
}

//VALUE WILL ALWAYS BE THE LAST ARG
function mda_del_value(&$arr,$path=null){
	$args = func_get_args();
	$set = array_pop($args);
	$path = __mda_get_path($args);
	return __mda_path_action($arr,$path,MDA_PATH_DEL_VALUE,$set);
}

//VALUE WILL ALWAYS BE THE LAST ARG
function mda_exists_value(&$arr,$path=null){
	$args = func_get_args();
	$set = array_pop($args);
	$path = __mda_get_path($args);
	return __mda_path_action($arr,$path,MDA_PATH_EXISTS_VALUE,$set);
}

function mda_exists(&$arr,$path){
	$path = __mda_get_path(func_get_args());
	return __mda_path_action($arr,$path,MDA_PATH_EXISTS);
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

/*
 * Internal functions
 */
function __mda_get_path($args=array(),$strip_chars=true){
	array_shift($args);
	$path = '';
	$parts = array();
	foreach($args as $v){
		if(is_null($v)) continue;
		$parts = array_merge($parts,explode('.',$v));
	}
	//if(defined('MDA_DEBUG')) echo "MDA Parsed Path: ".implode($parts,'.')."\n";
	foreach($parts as $part){
		if($strip_chars && strspn($part,';$()[]{}=+@!% ') != 0){
			//if(defined('MDA_DEBUG') && MDA_DEBUG === true) throw new Exception('Invalid MDA path passed: '.implode('.',$parts));
			//else continue;
			continue;
		}
		// if($strip_chars) $part = preg_replace('/[;$()\[\]\{\}\s=+@!%]+/','',$part);
		$path .= '.'.$part;
	}
	return trim($path,'.');
}

//this will return the referenced by the path (null for not exists, so exists check must be done separately
function __mda_path_action(&$arr,$path,$act=MDA_PATH_GET,$set_val=null,$cur_path=null){
	if(!is_array($arr)) return false;
	//recursively walk the array and look for a path match
	foreach($arr as $k => &$v){
		$cpath = $cur_path.(is_null($cur_path) ? '' : '.').$k;
		//if(defined('MDA_DEBUG') && MDA_DEBUG) echo "(search $path) == (current $cpath)... ";
		//perform actions on array members
		if(is_array($v) && $path != $cpath){
			$t = &__mda_path_action($v,$path,$act,$set_val,$cpath);
			switch($act){
				case MDA_PATH_GET:
					if(!is_null($t)) return $t;
					break;
				default:
					if($t === true) return $t;
					break;
			}
		} elseif(is_array($v) && $path == $cpath){
			switch($act){
				case MDA_PATH_GET:
					return $v;
					break;
				case MDA_PATH_EXISTS:
					return true;
					break;
				case MDA_PATH_ADD:
					$v[] = $set_val;
					return true;
					break;
				case MDA_PATH_DEL:
					unset($arr[$k]);
					return true;
					break;
				case MDA_PATH_DEL_VALUE:
					foreach(array_keys($v,$set_val,true) as $_v) unset($v[$_v]);
					unset($_v);
					return true;
					break;
				case MDA_PATH_EXISTS_VALUE:
					if(count(array_keys($v,$set_val,true)) > 0) return true;
					else return false;
					break;
				default:
					throw new Exception('Invalid action on array member');
					break;
			}
		}
		//if we have a path match then grab the var
		if($path == $cpath){
			//if(defined('MDA_DEBUG') && MDA_DEBUG) echo "hit\n";
			switch($act){
				case MDA_PATH_GET:
					return $v;
					break;
				case MDA_PATH_EXISTS:
					return true;
					break;
				case MDA_PATH_DEL:
					unset($arr[$k]);
					break;
				default:
					throw new Exception('Invalid path action for value modification/retrieval');
					break;
			}
		}
		//if(defined('MDA_DEBUG') && MDA_DEBUG) echo "miss\n";
	}
	//retrn then proper value based on the action
	switch($act){
		case MDA_PATH_GET:
			return null;
			break;
		default:
			return false;
			break;
	}
}


