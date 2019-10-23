<?php

namespace Roeltz\Wordpress;
use Roeltz\Wordpress\HTTP\Request;
use Roeltz\Wordpress\HTTP\Router;

class HTTP {

	static function route(array $routes, callable $actionFound = null) {
		$router = Router::map($routes);
		$request = Request::fromGlobals();
		$action = $router->route($request);
		
		if ($actionFound) {
			$actionFound($action);
		}

		$result = $action->execute($request);

		if (is_callable($result)) {
			$result();
		} else {
			echo $result;
		}

		exit(0);
	}

	function __constrcut(App $app) {
		$this->app = $app;
	}

	function download($file, $name = null, $deleteAfter = false) {
		return function() use($file, $contentType, $name, $deleteAfter) {
			if (!$name) $name = basename($file);
	
			header("Content-Disposition: attachment; filename=$name");
			header("Content-Type: application/octet-stream");
			header("Content-Length: " . filesize($file));
			readfile($file);
			exit;
		};
	}
	
	function json($data, $status = "200 OK") {
		 return $this->response(json_encode($data), "application/json", $status);
	}
	
	function redirect($uri) {
		return function() use($uri) {
			header("Location: $uri");
		};
	}
	
	function response($body, $contentType = "text/plain", $status = "200 OK") {
		return function() use($body, $contentType, $status) {
			header("HTTP/1.1 $status");
			header("Content-Type: $contentType");
			header("Content-Length: " . strlen($body));
			echo $body;
		};
	}
}