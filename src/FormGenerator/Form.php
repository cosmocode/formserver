<?php

namespace CosmoCode\Formserver\FormGenerator;


use CosmoCode\Formserver\FormGenerator\FormElements\AbstractFormElement;

class Form
{
	/**
	 * @var string
	 */
	protected $configDir;

	/** @var AbstractFormElement[] */
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

	public function validate() {

	}

	public function storeValidValues() {

	}
}