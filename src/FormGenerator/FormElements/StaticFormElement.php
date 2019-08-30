<?php

namespace CosmoCode\Formserver\FormGenerator\FormElements;

/**
 * Static form elements have no user input (e.g. hidden, simple text, image, download)
 */
class StaticFormElement extends AbstractFormElement
{
	/**
	 * Prepare variables array for twig view.
	 *
	 * @return array
	 */
	public function getViewVariables()
	{
		return array_merge($this->getConfig(),[ 'id' => $this->getId()]);
	}
}