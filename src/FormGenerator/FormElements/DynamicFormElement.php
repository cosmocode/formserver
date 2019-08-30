<?php

namespace CosmoCode\Formserver\FormGenerator\FormElements;

/**
 * Dynamic form elements have an input which the user can fill out
 */
class DynamicFormElement extends AbstractFormElement
{
	/**
	 * @var mixed
	 */
	protected $value;

	public function getValue() {
		return $this->value;
	}

	public function setValue($value) {
		$this->value = $value;
	}

	public function getValidationRules() {
		return $this->getConfig()['validation'] ?? [];
	}

	public function getViewVariables()
	{
		return array_merge($this->getConfig(),[ 'id' => $this->getFormElementId(), 'value' => $this->getValue()]);
	}
}