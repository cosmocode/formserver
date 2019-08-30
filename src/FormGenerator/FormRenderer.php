<?php

namespace CosmoCode\Formserver\FormGenerator;


use CosmoCode\Formserver\FormGenerator\FormElements\AbstractFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\FieldsetFormElement;

// TODO Exception handling
class FormRenderer
{
	/** @var \Twig\Environment */
	protected $twig;

	public function __construct(string $twigLayout = 'layout.twig')
	{
		$twigLoader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../../view/');
		$twig = new \Twig\Environment($twigLoader);

		$this->twig = $twig->load($twigLayout);
	}

	/**
	 * @param AbstractFormElement[] $formElements
	 * @return string
	 * @throws \Throwable
	 */
	public function renderForm($formElements, string $title = '') {
		$formHtml = '';
		foreach ($formElements as $formElement) {
			$formHtml .= $this->renderFormElement($formElement);
		}

		return $this->twig->renderBlock('form', ['formHtml' => $formHtml, 'title' => $title]);
	}

	protected function renderFormElement(AbstractFormElement $formElement) {
		if ($formElement instanceof FieldsetFormElement) {
			$fieldsetHtml = '';
			foreach ($formElement->getChildren() as $childFormElement) {
				$fieldsetHtml .= $this->renderFormElement($childFormElement);
			}

			return $this->twig->renderBlock('fieldset', ['fieldsetHtml' => $fieldsetHtml]);
		}

		return $this->twig->renderBlock($formElement->getType(), $formElement->getViewVariables());
	}
}