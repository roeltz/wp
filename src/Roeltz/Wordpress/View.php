<?php

namespace Roeltz\Wordpress;

class View {

	protected $app;

	function __construct(App $app) {
		$this->app = $app;
	}

	function file($__path, array $__data = []) {
		if (strpos($__path, "@") === 0) {
			$__path = $this->app->path(ltrim($__path, "@"));
		} else {
			$__trace = debug_backtrace();
			$__file = $__trace[0]["file"];
			$__path = dirname($__file) . "/$__path";
		}
	
		ob_start();
		extract($__data);
		require "$__path.php";
		return ob_get_clean();
	}
	
	function jsvars(array $data) {
	?><script>
	<?php foreach ($data as $k=>$v): ?>
	var <?php echo $k ?> = <?php echo json_encode($v) ?>;
	<?php endforeach ?>
	</script>
	<?php
	}

	function redirect($url) {
		header("Location: $url");
		exit(0);
	}
	
	function setup($item) {
		global $post;
	
		if (is_scalar($item))
			$item = page($item);
	
		setup_postdata($post = $item);
	}
	
	function script($path) {
	?><script src="<?php echo scripturl($path) ?>"></script><?php
	}
	
	function scripturl($path) {
		return themeurl($path, "scripts");
	}
	
	function stylesheet($path) {
	?><link rel="stylesheet" href="<?php echo styleurl($path) ?>"><?php
	}
	
	function styleurl($path) {
		return themeurl($path, "styles");
	}
	
	function themeurl($path, $base = null) {
		if (preg_match('#^(https?:)?//#', $path)) {
			return $path;
		} else {
			if ($base) $path = "$base/$path";
			return get_template_directory_uri() . "/$path";
		}
	}
	
	function themepath($path, $base = null) {
		if ($base) $path = "$base/$path";
		return get_template_directory() . "/$path";
	}
}