<?php

namespace Roeltz\Wordpress;
use Roeltz\Wordpress\Admin\AdminPage;
use Roeltz\Wordpress\HTTP\Request;

class Admin {

	public $app;

	static function actionToSlug($action) {
		return strtolower(preg_replace('#[^\w-]#', '-', $action));
	}

	function __construct(App $app) {
		$this->app = $app;
	}

	function actionURL($action, array $args = []) {
		$url = admin_url("admin.php?page=" . self::actionToSlug($action));

		if ($args) {
			$url = add_query_arg($args, $url);
		}

		return $url;
	}

	function executeAction($action) {
		$this->app->loadInit();

		try {
			$request = Request::fromGlobals();
			$action = new Action($action);
			echo $action->execute($request);
		} catch (Exception $ex) {
			// TODO: Mejorar despliegue de excepciones
			echo html("div", ["class"=>"error"], [
				html_p($ex->getMessage()),
				html_pre($ex->getTraceAsString())
			]);
			die();
		}
	}

	function innerPage($action, $pageTitle) {
		return AdminPage::createInner($this->app, $action, $pageTitle);
	}

	function page($action, $menuTitle, $pageTitle = null) {
		return AdminPage::create($this->app, $action, $menuTitle, $pageTitle);
	}

	function redirectAction($action, array $args = []) {
		$url = $this->actionURL($action, $args);
		header("Location: $url");
		exit(0);
	}
}