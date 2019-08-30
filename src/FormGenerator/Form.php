<?php

namespace CosmoCode\Formserver\FormGenerator;


use CosmoCode\Formserver\FormGenerator\FormElements\DynamicFormElement;

class Form
{
	/**
	 * @var string
	 */
	protected $configDir;

	/** @var DynamicFormElement[] */
	protected $formElements = [];

	public function __construct(array $formConfig)
	{
		foreach ($formConfig as $formElementId => $formElementConfig) {
			$this->formElements[] = FormElementFactory::createFormElement($formElementId, $formElementConfig);
		}
	}

	public function getFormElements() {
		return $this->formElements;
	}

	public function submitData( array $data) {

	}
}