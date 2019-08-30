<?php

namespace CosmoCode\Formserver\FormGenerator;


use CosmoCode\Formserver\Exceptions\FormException;
use CosmoCode\Formserver\FormGenerator\FormElements\AbstractFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\DynamicFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\FieldsetFormElement;

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

	public function submitData(array $data) {
		// Traverse each AbstractFormElement of this Form
		foreach ($this->formElements as $formElement) {
			// If AbstractFormElement is FieldsetFormElement it contains childs which have to be filled
			if ($formElement instanceof FieldsetFormElement) {
				$fieldsetData = $data[$formElement->getId()] ?? [];
				foreach ($fieldsetData as $key => $fdata) {
					try {
						$fieldsetChild = $formElement->getChildById($formElement->getId() . '[' . $key . ']');
						$this->submitDataIfDynamicFormElement($fieldsetChild, $fdata);
					} catch (FormException $ignored) {}
				}
			} else {
				$this->submitDataIfDynamicFormElement($formElement, $data[$formElement->getId()]);
			}
		}
	}

	protected function submitDataIfDynamicFormElement(AbstractFormElement $formElement, $value) {
		if ($formElement instanceof  DynamicFormElement) {
			$formElement->setValue($value);
		}
	}
}