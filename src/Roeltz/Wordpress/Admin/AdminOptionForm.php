<?php

namespace Roeltz\Wordpress\Admin;

class AdminOptionForm extends AdminForm {

	private static $registeredOptionGroups = [];

	private $optionGroup;

	static function create($optionGroup) {
		if (!isset(self::$registeredOptionGroups[$optionGroup]))
			throw new Exception("AdminForm '{$optionGroup}' must be registered first in functions.php");

		return new static($optionGroup);
	}

	static function register($optionGroup, $options) {
		if (!is_admin()) return;

		self::$registeredOptionGroups[$optionGroup] = $options;

		add_action("admin_init", function() use($optionGroup, $options){
			foreach ($options as $option)
				register_setting($optionGroup, $option);
		});
	}

	function __construct($optionGroup) {
		parent::__construct(admin_url("options.php"), true);
		$this->optionGroup = $optionGroup;
	}

	function boolField($label, $name, $description = null, $value = 0) {
		$value = !!get_option($name, 0);

		return parent::boolField($label, $name, $description, $value);
	}

	function checklistField($label, array $options, $description = null) {
		foreach ($options as $k=>&$v)
			if (!is_array($v))
				$v = [$v, !!get_option($k)];

		return parent::checklistField($label, $options, $description);
	}

	function prerender() {
		ob_start();
		settings_fields($this->optionGroup);
		do_settings_sections($this->optionGroup);
		return ob_get_clean();
	}

	function selectField($label, $name, array $options, $description = null, $value = null) {
		$value = get_option($name, "");

		return parent::selectField($label, $name, $options, $description, $value);
	}

	function textField($label, $name, $description = null, $value = null) {
		$value = get_option($name);

		return parent::textField($label, $name, $description, $value);
	}

	function textareaField($label, $name, $description = null, $value = null) {
		$value = get_option($name);

		return parent::textareaField($label, $name, $description, $value);
	}

}
