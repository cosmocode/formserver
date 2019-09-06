<?php

namespace CosmoCode\Formserver\FormGenerator;


use CosmoCode\Formserver\Exceptions\TwigException;
use CosmoCode\Formserver\FormGenerator\FormElements\FieldsetFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\AbstractFormElement;

/**
 * Renders twig blocks
 */
class FormRenderer
{
	/** @var \Twig\TemplateWrapper */
	protected $twig;

	public function __construct(string $twigLayout = 'layout.twig')
	{
		$twigLoader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../../view/');
		$twigEnvironment = new \Twig\Environment($twigLoader);

		try {
			$this->twig = $twigEnvironment->load($twigLayout);
		} catch (\Twig\Error\Error $e) {
			throw new TwigException("Could not load twig layout file '$twigLayout':" . $e->getMessage());
		}
	}

	/**
	 * Renders a complete Form
	 *
	 * @param AbstractFormElement[] $formElements
	 * @return string
	 * @throws TwigException
	 */
	public function renderForm(Form $form) {
		$formHtml = '';
		$title = $form->getMeta('title');
		foreach ($form->getFormElements() as $formElement) {
			if ($formElement instanceof FieldsetFormElement) {
				$formHtml .= $this->renderFieldsetFormElement($formElement);
			} else {
				$formHtml .= $this->renderFormElement($formElement);
			}
		}

		return $this->renderBlock('form', ['formHtml' => $formHtml, 'title' => $title]);
	}

	/**
	 * Renders the view of a FormElement
	 *
	 * @param FieldsetFormElement $fieldsetFormElement
	 * @return string
	 * @throws TwigException
	 */
	protected function renderFieldsetFormElement(FieldsetFormElement $fieldsetFormElement) {
		foreach ($fieldsetFormElement->getChildren() as $childFormElement) {
			$fieldsetFormElement->addRenderedChildView(
				$this->renderFormElement($childFormElement)
			);
		}

		return $this->renderFormElement($fieldsetFormElement);
	}

	/**
	 * Helper function to render a FormElement
	 *
	 * @param AbstractFormElement $formElement
	 * @return string
	 */
	protected function renderFormElement(AbstractFormElement $formElement) {
		return $this->renderBlock($formElement->getType(), $formElement->getViewVariables());
	}

	/**
	 * Helper function to render a twig block
	 *
	 * @param $block
	 * @param $variables
	 * @return string
	 * @throws TwigException
	 */
	protected function renderBlock($block, $variables) {
		if (!$this->twig->hasBlock($block)) {
			throw new TwigException("Template block for form element type '$block' not found.");
		}

		try {
			return $this->twig->renderBlock($block, $variables);
		} catch (\Throwable $e) {
			throw new TwigException("Could not render block '$block': " . $e->getMessage());
		}
	}
}
