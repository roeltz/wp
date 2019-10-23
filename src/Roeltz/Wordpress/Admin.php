<?php

namespace Roeltz\Wordpress;
use Roeltz\Wordpress\Admin\AdminPage;
use Roeltz\Wordpress\HTTP\Request;

class Admin {

	public $app;

	function __construct(App $app) {
		$this->app = $app;
	}

	function actionToSlug($action) {
		return strtolower(preg_replace('#[^\w-]#', '-', $action));
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

	function page($action, $menuTitle, $pageTitle = null) {
		return AdminPage::create($this->app, $action, $menuTitle, $pageTitle);
	}
}