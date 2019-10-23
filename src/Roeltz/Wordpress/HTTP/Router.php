<?php

namespace Roeltz\Wordpress\HTTP;
use Roeltz\Wordpress\Action;

class Router {

	private static $instance;

	private $basePath = "";

	private $routes = [];

	static function get() {
		if (!self::$instance)
			self::$instance = new Router();

		return self::$instance;
	}

	static function map(array $routes) {
		$router = self::get();

		foreach ($routes as $route=>$action) {
			if ($route == "base") {
				$router->basePath = parse_url($action, PHP_URL_PATH);
				continue;
			} else {
				if (is_array($action)) {
					$data = $action[1];
					$action = $action[0];
				} else {
					$data = null;
				}
			}
			$router->add($route, $action, $data);
		}

		return $router;
	}

	private function __construct() {}

	function add($route, $action, $data) {
		$this->routes[] = new Route($route, $action, $data);
	}

	function route(Request $request) {
		foreach ($this->routes as $route) {
			if ($route->matches($request, $this->basePath)) {
				if ($route->data)
					$request->data = array_merge($request->data, $route->data);

				return new Action($route->destination);
			}
		}

		throw new ActionNotFoundException;
	}
}
