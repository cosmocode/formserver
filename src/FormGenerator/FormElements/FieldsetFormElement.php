<?php

namespace CosmoCode\Formserver\FormGenerator\FormElements;


class FieldsetFormElement extends AbstractFormElement
{

	/**
	 * @var AbstractFormElement[]
	 */
	protected $children;

	/**
	 * @return AbstractFormElement[]
	 */
	public function getChildren() {
		return $this->children;
	}

	public function addChild(AbstractFormElement $child) {
		$this->children[] = $child;
	}

	/**
	 * Prepare variables array for twig view.
	 *
	 * @return array
	 */
	public function getViewVariables()
	{
		return array_merge($this->config,[ 'id' => $this->getId()]);
	}
}