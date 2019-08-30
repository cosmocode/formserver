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

	public function submitData(array $postData) {
		// Traverse each AbstractFormElement of this Form
		foreach ($this->formElements as $formElement) {
			// If AbstractFormElement is FieldsetFormElement it contains childs which have to be filled
			if ($formElement instanceof FieldsetFormElement) {
				foreach ($formElement->getChildren() as $fieldsetChild) {
					if ($fieldsetChild instanceof DynamicFormElement) {
						$fieldsetChildValue = $postData[$formElement->getId()][$fieldsetChild->getId()] ?? '';
						$fieldsetChild->setValue($fieldsetChildValue);
					}
				}
			} elseif ($formElement instanceof DynamicFormElement) {
				$formElement->setValue($postData[$formElement->getId()]);
			}
		}
	}
}