<?php

namespace Roeltz;

abstract class Interpolate {

	static function callback($str, $callback) {
		return preg_replace_callback('/\{([^}]+)\}/', function($m) use($callback){
			return $callback($m[1]);
		}, $str);
	}	

	static function fill($str, array $values) {
		return self::callback($str, function($key) use(&$values) {
			return @$values[$key];
		}, $str);
	}	
}