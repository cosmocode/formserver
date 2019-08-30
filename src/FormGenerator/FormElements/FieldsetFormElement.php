<?php

namespace CosmoCode\Formserver\FormGenerator\FormElements;

use CosmoCode\Formserver\Exceptions\FormException;

/**
 * FieldsetFormElement is a special StaticFormElement, since it contains child formElements
 * @package CosmoCode\Formserver\FormGenerator\FormElements
 */
class FieldsetFormElement extends StaticFormElement
{

	/**
	 * @var AbstractFormElement[]
	 */
	protected $children;

	public function __construct($id, array $config)
	{
		unset($config['children']); // Children config not needed
		parent::__construct($id, $config);
	}

	/**
	 * @return AbstractFormElement[]
	 */
	public function getChildren() {
		return $this->children;
	}

	public function getChildById(string $id) {
		foreach ($this->children as $child) {
			if ($child->getId() === $id) {
				return $child;
			}
		}
		throw new FormException("Could not get child in FieldsetFormElement '" . $this->id . "'. Child with id $id not found.");
	}

	public function addChild(AbstractFormElement $child) {
		$this->children[] = $child;
	}
}