<?php

namespace CosmoCode\Formserver\FormGenerator;


use CosmoCode\Formserver\FormGenerator\FormElements\DefaultFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\FieldsetFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\HiddenFormElement;

class FormElementFactory
{
	public static function createFormElement(string $id, array $config) {
		switch ($config['type']) {
			case 'fieldset':
				return self::createFieldsetFormElement($id, $config);
			case 'hidden':
				return self::createHiddenFormElement($id, $config);
			default:
				return self::createDefaultFormElement($id, $config);
		}
	}

	protected static function createDefaultFormElement(string $id, array $config)
	{
		return new DefaultFormElement($id, $config);
	}

	protected static function createHiddenFormElement(string $id, array $config)
	{
		return new HiddenFormElement($id, $config);
	}

	protected static function createFieldsetFormElement(string $id, array $config)
	{
		$listFormElement = new FieldsetFormElement($id, $config);

		foreach ($config['children'] as $childId => $childConfig) {
			$childId = $id.'[' . $childId . ']';
			$listFormElement->addChild(self::createFormElement($childId, $childConfig));
		}

		return $listFormElement;
	}
}