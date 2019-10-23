<?php

namespace Roeltz\Wordpress\Admin;
use Pipa\Data\Criteria;
use Roeltz\HTML\HTML;
use WP_List_Table;

class AdminTable extends WP_List_Table {

	private $data;

	private $pk = "ID";

	private $pageSize = 20;

	private $callbacks = [];

	private $columns = [];

	private $links = [];

	private $sortable = [];

	private $bulkActions = [];

	private $search = false;

	private $views;

	private $viewsParam;

	static function create($data, $singular = "item", $plural = "items") {
		return new self($data, $singular, $plural);
	}

	function __construct($data, $singular = "item", $plural = "items") {
		$this->data = $data;

		parent::__construct([
			"singular"=>$singular,
			"plural"=>$plural
		]);
	}

	function bulkAction($name, $label = null) {
		if (!$label) $label = $name;

		$this->bulkActions[$name] = $label;

		return $this;
	}

	function column($name, $label = null, $sortable = false, $link = null) {
		if (!$label) $label = $name;

		$this->columns[$name] = $label;

		if ($sortable)
			$this->sortable[$name] = [$name, true];

		if ($link)
			$this->links[$name] = $link;

		return $this;
	}

	function itemCallback($fn) {
		$this->callbacks[] = $fn;

		return $this;
	}

	function pageSize($n) {
		$this->pageSize = $n;

		return $this;
	}

	function pk($column) {
		$this->pk = $column;

		return $this;
	}

	function render() {
		$this->prepare_items();
		$this->views();

		if ($this->search || $this->bulkActions) {
			$form = HTML::tag("form", ["method"=>"post", "action"=>""]);
			echo $form->renderOpeningTag();
		}

		if ($this->search)
			$this->search_box($this->search, $this->_args["plural"]);

		$this->display();

		if ($this->search || $this->bulkActions)
			echo $form->renderClosingTag();
	}

	function search($label) {
		$this->search = $label;

		return $this;
	}

	function viewTypes($param, array $views) {
		$this->viewsParam = $param;
		$this->views = $views;

		return $this;
	}

	function __toString() {
		ob_start();
		$this->render();
		return ob_get_clean();
	}

	// Overriden methods

	function column_cb($item) {
		if ($this->bulkActions && $this->pk)
			return HTML::checkbox("{$this->pk}[]", $item[$this->pk]);
	}

	function column_default($item, $name) {
		if ($link = @$this->links[$name]) {
			return HTML::a(fill($link, $item), $item[$name]);
		} else {
			return $item[$name];
		}
	}

	function get_bulk_actions() {
		return $this->bulkActions;
	}

	function get_columns() {
		$columns = $this->columns ? $this->columns : array_combine(@array_keys($this->items[0]), @array_keys($this->items[0]));

		if ($this->bulkActions)
			$columns = array_merge(["cb"=>(string) HTML::checkbox()], $columns);

		return $columns;
	}

	function get_views() {
		if (!$this->viewsParam) return [];

		$views = [];
		$current = @$_REQUEST[$this->viewsParam];

		foreach ($this->views as $arg=>$label) {
			$url = add_query_arg($this->viewsParam, $arg ? $arg : false);
			$views[$arg] = (string) HTML::a($url, $label, [
				"class"=>$arg == $current ? "current" : null
			]);
		}

		return $views;
	}

	function prepare_items() {
		if ($this->data instanceof Criteria) {
			if ($orderBy = @$_GET["orderby"])
				$this->data->orderBy($orderBy, @$_GET["order"]);

			$count = $this->data->count();
			$this->data->page($this->get_pagenum(), $this->pageSize);
			$this->items = $this->data->queryAll();

			if (is_object(@$this->items[0])) {
				foreach ($this->items as $i=>$o) {
					$this->items[$i] = (array) $o;
				}
			}
		} else {
			$this->items = $this->data;
			$count = count($this->data);
		}

		if ($this->callbacks) {
			foreach ($this->items as &$item) {
				foreach ($this->callbacks as $fn) {
					call_user_func_array($fn, [&$item]);
				}
			}
		}

		$this->set_pagination_args([
			"total_items"=>$count,
			"per_page"=>$this->pageSize
		]);

		$this->_column_headers = [$this->get_columns(), [], $this->sortable];
	}
}
