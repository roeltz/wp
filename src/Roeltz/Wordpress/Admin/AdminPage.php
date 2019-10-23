<?php

namespace Roeltz\Wordpress\Admin;
use Roeltz\Wordpress\App;

class AdminPage {

	const VARIANT_TITLE_REGEX = '#\s*\[([^]]+)\]\s*$#';

	private $action;

	private $menuTitle;

	private $variantMenuTitle;

	private $pageTitle;

	private $capability = "read";

	private $icon;

	private $position;

	static function create($app, $action, $menuTitle, $pageTitle = null) {
		return new self($app, $action, $menuTitle, $pageTitle);
	}

	static function createInner($app, $action, $pageTitle) {
		return new self($app, "__hidden__/$action", $pageTitle);
	}

	function __construct(App $app, $action, $menuTitle, $pageTitle = null) {
		if (preg_match(self::VARIANT_TITLE_REGEX, $menuTitle, $m)) {
			$menuTitle = preg_replace(self::VARIANT_TITLE_REGEX, "", $menuTitle);
			$this->variantMenuTitle = $m[1];
		}

		$this->app = $app;
		$this->action = $action;
		$this->menuTitle = $menuTitle;
		$this->pageTitle = $pageTitle ? $pageTitle : $menuTitle;
		$this->register();
	}

	function capability($name) {
		$this->capability = $name;

		return $this;
	}

	function icon($path) {
		$this->icon = $this->app->path($path);

		return $this;
	}

	function pageTitle($title) {
		$this->pageTitle = $title;

		return $this;
	}

	function position($n) {
		$this->position = $n;

		return $this;
	}

	function register() {
		if (!is_admin()) return;

		add_action("admin_menu", function(){
			@list($action, $subaction) = explode("/", $this->action);
			$slug = $this->app->admin->actionToSlug($action);
			$finalAction = $subaction ? $subaction : $action;
			$callback = function() use($finalAction){
				$this->app->admin->executeAction($finalAction);
			};

			if ($subaction) {
				$subslug = $this->app->admin->actionToSlug($subaction);

				add_submenu_page($slug, $this->pageTitle, $this->menuTitle, $this->capability, $subslug, $callback);
			} else {
				add_menu_page($this->pageTitle, $this->menuTitle, $this->capability, $slug, $callback, $this->icon, $this->position);

				if ($this->variantMenuTitle) {
					add_submenu_page($slug, $this->pageTitle, $this->variantMenuTitle, $this->capability, $slug);
				}
			}
		});
	}
}
