<?php

namespace CosmoCode\Formserver\FormGenerator\FormElements;


/**
 * FieldsetFormElement is a special StaticFormElement, since it contains child formElements
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