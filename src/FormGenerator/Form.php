<?php

namespace CosmoCode\Formserver\FormGenerator;


use CosmoCode\Formserver\FormGenerator\FormElements\AbstractFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\InputFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\FieldsetFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\UploadFormElement;
use CosmoCode\Formserver\Helper\YamlHelper;
use Slim\Psr7\UploadedFile;

class Form
{
	/**
	 * @var string
	 */
	protected $formDirectory;

	/**
	 * @var array
	 */
	protected $meta;

	/** @var AbstractFormElement[] */
	protected $formElements = [];

	public function __construct(string $formId)
	{
		$this->formDirectory = __DIR__ . "/../../data/$formId/";
		$config = YamlHelper::parseYaml($this->formDirectory . 'config.yaml');
		$this->meta = $config['meta'] ?? [];
		$formConfig = $config['form'];
		foreach ($formConfig as $formElementId => $formElementConfig) {
			$this->formElements[] = FormElementFactory::createFormElement($formElementId, $formElementConfig);
		}
	}

	public function getFormElements() {
		return $this->formElements;
	}

	public function submitData(array $postData) {
		// Traverse each AbstractFormElement of this Form
		foreach ($this->formElements as $formElement) {
			// If AbstractFormElement is FieldsetFormElement it contains childs which have to be filled
			if ($formElement instanceof FieldsetFormElement) {
				foreach ($formElement->getChildren() as $fieldsetChild) {
					if ($fieldsetChild instanceof InputFormElement) {
						$fieldsetChildValue = $postData[$formElement->getId()][$fieldsetChild->getId()] ?? '';
						$fieldsetChild->setValue($fieldsetChildValue);
					}
				}
			} elseif ($formElement instanceof InputFormElement) {
				$formElement->setValue($postData[$formElement->getId()] ?? '');
			}
		}
	}
}