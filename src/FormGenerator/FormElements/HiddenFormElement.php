<?php

namespace CosmoCode\Formserver\FormGenerator\FormElements;


class HiddenFormElement extends AbstractFormElement
{


	public function getValue() {
		return $this->config['value'];
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