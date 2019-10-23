<?php

namespace Roeltz\Wordpress;
use Roeltz\Wordpress\HTTP\ActionNotFoundException;

class App {

	const TYPE_PLUGIN = "plugin";
	const TYPE_THEME = "theme";

	public $admin;

	public $path;

	static function init($path) {
		return new self($path);
	}

	function __construct($path) {
		$this->path = $path;
		$this->admin = new Admin($this);

		$this->actionHook(function($instance){
			$instance->app = $this;
			$instance->http = new HTTP($this);
			$instance->view = new View($this);
		});		
	}

	function actionHook(callable $hook) {
		return Action::hook($hook);
	}

	function getType() {
		if (strpos($this->path, "wp-content".DIRECTORY_SEPARATOR."plugins")) {
			return self::TYPE_PLUGIN;
		} elseif (strpos($this->path, "wp-content".DIRECTORY_SEPARATOR."themes")) {
			return self::TYPE_THEME;
		}
	}

	function loadInit() {
		require_once $this->path("init.php");
	}

	function path($path = "") {
		return dirname($this->path)."/$path";
	}

	function route(array $routes) {
		add_filter("do_parse_request", function($continue) use($routes){
			try {
				HTTP::route($routes, function(){
					$this->loadInit();
				});
			} catch (ActionNotFoundException $ex) {
				return $continue;
			}
		});
	}

	function url($path = "") {
		switch ($this->getType()) {
			case self::TYPE_PLUGIN:
				return plugin_dir_url($this->path).$path;
			case self::TYPE_THEME:
				return get_stylesheet_directory_uri()."/$path";
			default:
				return home_url($path);
		}
	}
}