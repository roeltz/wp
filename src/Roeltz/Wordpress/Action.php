<?php

namespace Roeltz\Wordpress;
use ReflectionClass;
use ReflectionMethod;
use Roeltz\Wordpress\HTTP\Request;

class Action {

	protected static $hooks = [];

	private $class;

	private $method;

	static function hook(callable $hook) {
		self::$hooks[] = $hook;
	}

	function __construct($route) {
		list($class, $method) = preg_split('#::?|/#', $route);
		$this->class = ucfirst($class);
		$this->method = $method;
	}

	function computeArgs(ReflectionMethod $method, array $data, Request $request) {
		$args = [];

		foreach ($method->getParameters() as $p) {
			$name = $p->getName();
			$class = $p->getClass();
			$value = null;

			if ($class) {
				switch ($class->getName()) {
					case 'Roeltz\Wordpress\HTTP\Request':
						$value = $request;
						break;
				}
			} elseif (isset($data[$name])) {
				$value = $data[$name];
			} elseif ($p->isOptional() && $v = $p->getDefaultValue()) {
				$value = $v;
			}

			$args[$name] = $value;
		}

		return $args;
	}

	function execute(Request $request) {
		$class = new ReflectionClass($this->class);
		$instance = $class->newInstance();		
		$method = $class->getMethod($this->method);
		$args = $this->computeArgs($method, $request->data, $request);

		foreach (self::$hooks as $hook) {
			$hook($instance, $method, $args);
		}
			
		return $method->invokeArgs($instance, $args);
	}
}
