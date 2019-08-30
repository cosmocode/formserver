<?php

namespace CosmoCode\Formserver\FormGenerator;


use CosmoCode\Formserver\Exceptions\FormException;
use CosmoCode\Formserver\FormGenerator\FormElements\DynamicFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\FieldsetFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\StaticFormElement;

class FormElementFactory
{
	public static function createFormElement(string $id, array $config) {
		$formType = $config['type'];
		switch ($formType) {
			case 'fieldset':
				return self::createFieldsetFormElement($id, $config);
			case 'hidden':
			case 'download':
			case 'image':
				return self::createStaticFormElement($id, $config);
			case 'textinput':
			case 'numberinput':
			case 'date':
			case 'time':
			case 'datetime':
			case 'email':
			case 'textarea':
			case 'radioset':
			case 'checklist':
			case 'dropdown':
			case 'upload':
				return self::createDynamicFormElement($id, $config);
			default:
				throw new FormException("Could not build FormElement with id $id. Undefined type ($formType)");
		}
	}

	protected static function createFieldsetFormElement(string $id, array $config)
	{
		$listFormElement = new FieldsetFormElement($id, $config);

		foreach ($config['children'] as $childId => $childConfig) {
			$completeChildId = $id.'[' . $childId . ']';
			$childFormElement = self::createFormElement($completeChildId, $childConfig);
			if ($childFormElement instanceof FieldsetFormElement) {
				throw new FormException("Fieldsets cannot be nested. (Fieldset with id '$id' has child fieldset with id '$childId'");
			}
			$listFormElement->addChild($childFormElement);
		}

		return $listFormElement;
	}

	protected static function createDynamicFormElement(string $id, array $config)
	{
		return new DynamicFormElement($id, $config);
	}

	protected static function createStaticFormElement(string $id, array $config)
	{
		return new StaticFormElement($id, $config);
	}
}