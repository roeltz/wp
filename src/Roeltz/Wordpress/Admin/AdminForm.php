<?php

namespace Roeltz\Wordpress\Admin;
use Roeltz\HTML\HTML;
use Roeltz\HTML\HTMLTag;

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

	function boolField($label, $name, $value = 0, array $attr = [], $description = null) {
		return $this->field(
			$label,
			HTML::tag("p", [], [
				$r1 = HTML::radio(array_merge($attr, ["name"=>$name, "id"=>$id1]), 1, $value),
				HTML::label(__("Yes"), $r1),
				HTML::tag("br"),
				$r2 = HTML::radio($name, 0, !$value),
				HTML::label(__("No"), $r2)
			]),
			$description
		);
	}

	function checklistField($label, $name, array $options, array $values = [], array $attr = [], $description = null) {
		return $this->field(
			$label,
			HTML::tag("div", [], array_map(function($option, $value) use($name, $attr, $values){
				$checked = in_array($value, $values);

				return HTML::tag("p", [], [
					HTML::label($option)
						->prepend(HTML::checkbox(array_merge($attr, ["name"=>$name]), $value, $checked))
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

	function inputField($label, $type, $name, $value = null, array $attr = [], $description = null) {
		return $this->field(
			$label,
			HTML::input($type, array_merge($attr, ["name"=>$name, "class"=>"regular-text"]), $value),
			$description
		);
	}

	function selectField($label, $name, array $options, $default = null, $value = null, array $attr = [], $description = null) {
		if ($default) {
			$options = array_merge([""=>$default], $options);
		}

		return $this->field(
			$label,
			HTML::select(array_merge($attr, ["name"=>$name]), $options, $value),
			$description
		);
	}

	function textField($label, $name, $value = null, array $attr = [], $description = null) {
		return $this->field(
			$label,
			HTML::input("text", array_merge($attr, ["name"=>$name, "class"=>"regular-text"]), $value),
			$description
		);
	}

	function textareaField($label, $name, $value = null, array $attr = [], $description = null) {
		return $this->field(
			$label,
			HTML::textarea(array_merge($attr, ["name"=>$name, "class"=>"large-text"]), $value),
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
