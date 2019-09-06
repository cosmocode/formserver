<?php

namespace CosmoCode\Formserver\FormGenerator;


use CosmoCode\Formserver\Exceptions\FormException;
use CosmoCode\Formserver\FormGenerator\FormElements\AbstractFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\InputFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\FieldsetFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\MarkDownFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\StaticFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\UploadFormElement;

class FormElementFactory
{
	public static function createFormElement(string $id, array $config, AbstractFormElement $parent = null) {
		$formType = $config['type'];
		switch ($formType) {
			case 'fieldset':
				return self::createFieldsetFormElement($id, $config);
			case 'markdown':
				return self::createMarkdownFormElement($id, $config, $parent);
			case 'hidden':
			case 'download':
			case 'image':
				return self::createStaticFormElement($id, $config, $parent);
			case 'upload':
				return self::createUploadFormElement($id, $config, $parent);
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
				return self::createInputFormElement($id, $config, $parent);
			default:
				throw new FormException("Could not build FormElement with id $id. Undefined type ($formType)");
		}
	}

	protected static function createFieldsetFormElement(string $id, array $config)
	{
		$listFormElement = new FieldsetFormElement($id, $config);

		foreach ($config['children'] as $childId => $childConfig) {
			$childFormElement = self::createFormElement($childId, $childConfig, $listFormElement);
			if ($childFormElement instanceof FieldsetFormElement) {
				throw new FormException("Fieldsets cannot be nested. (Fieldset with id '$id' has child fieldset with id '$childId'");
			}
			$listFormElement->addChild($childFormElement);
		}

		return $listFormElement;
	}

	protected static function createMarkdownFormElement(string $id, array $config, AbstractFormElement $parent = null) {
		return new MarkDownFormElement($id, $config, $parent);
	}

	protected static function createInputFormElement(string $id, array $config, AbstractFormElement $parent = null)
	{
		return new InputFormElement($id, $config, $parent);
	}

	protected static function createStaticFormElement(string $id, array $config, AbstractFormElement $parent = null)
	{
		return new StaticFormElement($id, $config, $parent);
	}

	protected static function createUploadFormElement(string $id, array $config, AbstractFormElement $parent = null) {
		return new UploadFormElement($id, $config, $parent);
	}
}