<?php

namespace Roeltz\Wordpress\Admin;
use Roeltz\HTML\HTML;

class AdminSideForm extends AdminForm {

	function renderFields() {
		$html = "";

		foreach ($this->fields as $label=>$elements)
			$html .= $this->renderField($label, $elements);

		return $html;
	}

	function renderField($name, array $elements) {
		return HTML::tag("div", ["class"=>"form-field"], [html_label($name)])
			->append($elements)
		;
	}
}
