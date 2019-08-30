<?php

namespace CosmoCode\Formserver\FormGenerator\FormElements;


class HiddenFormElement extends AbstractFormElement
{


	public function getValue() {
		return $this->getConfig()['value'] ?? '';
	}

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