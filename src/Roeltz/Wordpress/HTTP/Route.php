<?php

namespace Roeltz\Wordpress\HTTP;

class Route {

	const ROUTE_EXPRESSION_REGEX = '#^([A-Z]+)\s+(?=//(\S+))?(/\S+)$#';

	const PATH_VAR_EXPRESSION_REGEX = '#:(\w+)#';

	public $expression;

	public $destination;

	public $data;

	function __construct($expression, $destination, array $data = null) {
		$this->expression = $expression;
		$this->destination = $destination;
		$this->data = $data;
	}

	function matches(Request $request, $basePath) {
		$path = $request->path;

		if ($basePath)
			$path = preg_replace('#^'. preg_quote($basePath, "#") . '#', "", $path);

		$state = $this->parse($this->expression);
		return ($state["method"] == $request->method)
			&& (!@$state["host"] || $state["host"] == $request->host)
			&& $this->matchesPath($state["path"], $path, $request->data)
		;
	}

	function matchesPath($expression, $path, &$data) {
		$vars = [];
		$regex = preg_replace_callback(self::PATH_VAR_EXPRESSION_REGEX, function($m) use(&$vars){
			$vars[] = $m[1];
			return '([^/]+)';
		}, $expression);

		if (preg_match("#^$regex$#", $path, $m)) {
			foreach (array_slice($m, 1) as $i=>$value)
				$data[$vars[$i]] = $value;

			return true;
		}
	}

	function parse($expression) {
		if (preg_match(self::ROUTE_EXPRESSION_REGEX, $expression, $m)) {
			return [
				"method"=>$m[1],
				"host"=>$m[2],
				"path"=>$m[3]
			];
		} else {
			die("Invalid router expression: $expression");
		}
	}
}
