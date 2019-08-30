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

	public function __construct($id, array $config, AbstractFormElement $parent = null)
	{
		unset($config['children']); // Children config not needed
		parent::__construct($id, $config, $parent);
	}

	/**
	 * @return AbstractFormElement[]
	 */
	public function getChildren() {
		return $this->children;
	}

	public function addChild(AbstractFormElement $child) {
		$this->children[] = $child;
	}
}