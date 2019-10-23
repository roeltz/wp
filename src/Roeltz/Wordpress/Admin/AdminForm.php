<?php

namespace Roeltz\Wordpress\Admin;
use Roeltz\HTML\HTML;

class AdminForm {

	protected $action;

	protected $fields = [];

	protected $submitText = null;

	static function create($action = "") {
		return new static($action);
	}

	function __construct($action = "") {
		if (preg_match('#[\w+]+:\w+$#', $action)) {
			$this->action = admin_action_url($action);
		} elseif (preg_match('#(https?:)?//#', $action)) {
			$this->action = $action;
		} else {
			$this->action = home_url($action);
		}
	}

	function boolField($label, $name, $description = null, $value = 0) {
		return $this->field(
			$label,
			HTML::tag("p", [], [
				$r1 = HTML::radio(["name"=>$name, "id"=>$id1], 1, $value),
				HTML::label(__("Yes"), $r1),
				HTML::tag("br"),
				$r2 = HTML::radio($name, 0, !$value),
				HTML::label(__("No"), $r2)
			]),
			$description
		);
	}

	function checklistField($label, array $options, $description = null) {
		return $this->field(
			$label,
			HTML::tag("div", [], array_map(function($option, $name){
				if (is_array($option)) {
					list($text, $checked) = $option;
				} else {
					$text = $option;
					$checked = false;
				}
				return HTML::tag("p", [], [
					HTML::label($text)
						->prepend(HTML::checkbox($name, 1, $checked))
				]);
			}, $options, array_keys($options))),
			$description
		);
	}

	function field($label, HTMLTag $input, $description = null) {
		$elements = [$input];

		if ($description)
			$elements[] = HTML::tag("p", ["class"=>"description"], [$description]);

		$this->fields[$label] = $elements;

		return $this;
	}

	function selectField($label, $name, array $options, $description = null, $value = null) {
		return $this->field(
			$label,
			HTML::select($name, $options, $value),
			$description
		);
	}

	function textField($label, $name, $description = null, $value = null) {
		return $this->field(
			$label,
			HTML::input("text", ["name"=>$name, "class"=>"regular-text"], $value),
			$description
		);
	}

	function textareaField($label, $name, $description = null, $value = null) {
		return $this->field(
			$label,
			HTML::textarea(["name"=>$name, "class"=>"large-text"], $value),
			$description
		);
	}

	function submitText($text) {
		$this->submitText = $text;
	}

	function prerender() {

	}

	function postrender() {
		ob_start();
		submit_button($this->submitText);
		return ob_get_clean();
	}

	function render() {
		$form = HTML::tag("form", ["method"=>"post", "action"=>$this->action]);

		$html = $form->renderOpeningTag();
		$html .= $this->prerender();
		$html .= $this->renderFields();
		$html .= $this->postrender();
		$html .= $form->renderClosingTag();

		return $html;
	}

	function renderFields() {
		$table = HTML::tag("table", ["class"=>"form-table"]);
		$tbody = HTML::tag("tbody");

		$table->append($tbody);

		foreach ($this->fields as $label=>$elements)
			$tbody->append($this->renderField($label, $elements));

		return $table;
	}

	function renderField($name, array $elements) {
		return HTML::tag("tr")
			->child("th", ["scope"=>"row"], [$name])
			->child("td", [], $elements)
		;
	}

	function __toString() {
		return $this->render();
	}
}
