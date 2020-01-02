<?php

namespace Roeltz\Wordpress\Admin;
use Roeltz\HTML\HTML;
use Roeltz\Wordpress\Admin;

class AdminNotice {

	static function create($class, $content, $deferUntilPage = null) {
		if ($deferUntilPage) {
			if (preg_match('#[\w+]+:\w+$#', $deferUntilPage))
				$deferUntilPage = Admin::actionToSlug($deferUntilPage);
	
			$_SESSION["custom-admin-notices"][$deferUntilPage][] = compact("class", "content");
		} else {
			add_action("custom_admin_notices", function() use($class, $content){
				echo HTML::tag("div", ["class"=>"notice notice-$class"], [
					HTML::tag("p", [], [$content])
				]);
			});
		}
	}
	
	static function info($content, $deferUntilPage = null) {
		self::create("info", $content, $deferUntilPage);
	}
	
	static function success($content, $deferUntilPage = null) {
		self::create("success", $content, $deferUntilPage);
	}
	
	static function warning($content, $deferUntilPage = null) {
		self::create("warning", $content, $deferUntilPage);
	}
	
	static function error($content, $deferUntilPage = null) {
		self::create("error", $content, $deferUntilPage);
	}
	
	static function put() {
		if ($page = @$_GET["page"]) {
			$deferred = (array) @$_SESSION["custom-admin-notices"][$page];
	
			foreach ($deferred as $notice)
				self::create($notice["class"], $notice["content"]);
	
			unset($_SESSION["custom-admin-notices"][$page]);
		}
	
		do_action("custom_admin_notices");
	}
	
}