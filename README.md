openlss/func-mda
========

MDA (Multi Dimensional Array) access helpers for PHP

Usage
----

```php
$array = array('test'=>array('test2'=>array('test3'=>'value')));

//get a key
$val = mda_get($array,'test.test2.test3'); //returns 'value'

//set a key
mda_set($array,'test.test2.test3','newvalue');

//add a key
mda_add($array,'test.test2.test4','value3');

//get the added key
$val = mda_get($array,'test.test2.test4.0'); //return 'value3'

```

Reference
----

### Keys (Paths)
MDA keys are used in every function.
Sometimes they are referred to as "paths" or "path"
  * Dotted notation
   * Notation: mda_get($arr,'index1.index2.index3.index4.0')
   * Array: $arr['index1']['index2']['index3']['index4'][0]
  * Argument notation
   * Notation: mda_get($arr,'index1','index2','index3','index4','0')
   * Array: $arr['index1']['index2']['index3']['index4'][0]
  * Mixed Notation
   * Notation: mda_get($arr,'index1.index2.index3','index4','0')
   * Array: $arr['index1']['index2']['index3']['index4'][0]

### (mixed) mda_get(&$arr,$path=null)
Returns the key from $arr

### (mixed) mda_set(&$arr,$path=null)
All of the set functions take the value as the last argument
Example
```php
mda_set($arr,'index1','index2','index3.index4','value');
```

### (mixed) mda_add(&$arr,$path=null)
Same as set except it adds the value as an anonymous index
```php
mda_add($arr,'index1.index2','value');
//is the same as
$arr['index1']['index2'][] = 'value';
```

### (bool) mda_del(&$arr,$path=null)
Delete a path from array

### (mixed) mda_del_value(&$arr,$path=null)
This will delete all values from a path
```php
$arr['index'][0] = 'value';
$arr['index'][1] = 'value';
mda_del_value($arr,'index','value');
$count = count($arr['index']); //returns 0
```

### (bool) mda_exists_value(&$arr,$path=null)
This will check if a value exists in a path, same as mda_del_value()

### (bool) mda_exists(&$arr,$path)
Checks if a path exists

### (array) mda_flatten(&$arr,$keyname)
NOTE: this does not take a path it takes a keyname
  * use mda_get to find the lowest possible path and pass that
  * to this functions eg: $arr = mda_flatten(mda_get($arr,'path1.path2.path3'),'row_id);

### (string) implodei($join,$arr=array())
Same prototype as PHPs implode with the enhanced functionality that it will take $join as an array
If $join is a string it will pass directly to PHPs implode which is faster
Example
```php
$array = array(1,2,3,4,5);
$join = array('/','.',',');
$str = implodei($join,$array); //returns 1/2.3,4,5
```
NOTE: the last member of the array gets repeated

### (mixed) mda_shift($arr)
NOTE: THIS WILL NOT INCREASE THE ARRAYS POINTER (DOES NOT FUNCTION LIKE ARRAY_SHIFT)
Use this to shift anonymous arrays only it does not reference the original array
Otherwise its the same as PHPs array_shift()

