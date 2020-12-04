<?php

class Divido_Array
{

	static function toSingleArray($_array,$_key,$_value)
	{
		if(is_array($_array)) {
			$array = array();
			foreach($_array as $row) {
				$val = strval($row[$_key]);
				$array[$val] = $row[$_value];
			}
		}
		
		return $array;
	}
	
	static function sort(&$array, $key,$recursive=0) {
		$sorter=array();
		$ret=array();
		reset($array);
		foreach ($array as $ii => $va) {
			$sorter[$ii]=$va[$key];
		}
		if ($recursive) {
			arsort($sorter);
		} else {
			asort($sorter);
		}
		foreach ($sorter as $ii => $va) {
			$ret[$ii]=$array[$ii];
		}
		$array=$ret;
	}
	
	static function merge(array $array1, array $array2 = NULL) 
	{ 
		foreach($array2 as $key=>$value) {
			if (!isset($array1[$key])) {
				$array1[$key] = $value;
			} else {
				$array1[] = $value;
			}
		}
		
			
		return $array1;
	}
	
	
}