<?php

define('MDA_DEBUG',false);

function _mda_get_var($path=null,$args=array(),$req_arg_count=1,$strip_chars=true){
	$var = '';
	if(count($args) > $req_arg_count){
			for($i=1;$i<$req_arg_count;$i++) array_shift($args);
			$parts = $args;
	}
	else if(!is_array($path)) $parts = explode('.',$path);
	else if(is_array($path)) $parts = $path;
	else $parts = array();
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
	$var = _mda_get_var($path,func_get_args(),2);
	eval('$val = isset('.$var.') ? '.$var.' : null;');
	return $val;
}

function mda_set(&$arr,$value=null,$path=null){
	$var = _mda_get_var($path,func_get_args(),3);
	eval('$val = '.$var.' = $value;');
	return $val;
}

function mda_add(&$arr,$value,$path=null){
	$var = _mda_get_var($path,func_get_args(),3);
	eval('$val = '.$var.'[] = $value;');
	return $val;
}

function mda_del(&$arr,$path=null){
	$var = _mda_get_var($path,func_get_args(),2);
	eval('unset('.$var.');');
	return true;
}

function mda_del_value(&$arr,$value,$path=null){
	$var = _mda_get_var($path,func_get_args(),3);
	eval('$val =& '.$var.';');
	foreach(array_keys($val,$value) as $key) unset($val[$key]);
	return $val;
}

function mda_exists_value(&$arr,$value,$path=null){
	$rv = false;
	$var = _mda_get_var($path,func_get_args(),3);
	eval('$val =& '.$var.';');
	if(!is_array($val)) return false;
	foreach(array_keys($val,$value) as $key) $rv = true;
	return $rv;
}

function mda_exists(&$arr,$path){
	$var = _mda_get_var($path,func_get_args(),3);
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

function mda_shift($arr){
	return array_shift($arr);
}
