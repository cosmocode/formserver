<?php

namespace CosmoCode\Formserver\FormGenerator\FormElements;

class DefaultFormElement extends AbstractFormElement
{

	public function setValue($value) {
		$this->value = $value;
	}

	public function getValidationRules() {
		return $this->config['validation'] ?? [];
	}

	public function getViewVariables()
	{
		return array_merge($this->config,[ 'id' => $this->getId(), 'value' => $this->getValue()]);
	}
}