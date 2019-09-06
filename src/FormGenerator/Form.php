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

	public function getMeta(string $key) {
		return $this->meta[$key] ?? null;
	}

	public function submit(array $data, array $files) {
		foreach ($this->formElements as $formElement) {
			if ($formElement instanceof FieldsetFormElement) {
				foreach ($formElement->getChildren() as $fieldsetChild) {
					$this->submitFormElement($fieldsetChild, $data, $files);
				}
			} else {
				$this->submitFormElement($formElement, $data, $files);
			}
		}
	}

	public function persist() {
		$values = [];
		foreach ($this->formElements as $formElement) {
			if ($formElement instanceof FieldsetFormElement) {
				foreach ($formElement->getChildren() as $fieldsetChild) {
					$this->getValue($values, $fieldsetChild);
				}
			} else {
				$this->getValue($values, $formElement);
			}
		}

		if (!empty($values)) {
			YamlHelper::persistYaml($values, $this->formDirectory . 'values.yaml');
		}
	}

	public function restore() {
		$values = YamlHelper::parseYaml($this->formDirectory . 'values.yaml');

		foreach ($this->formElements as $formElement) {
			if ($formElement instanceof FieldsetFormElement) {
				foreach ($formElement->getChildren() as $fieldsetChild) {
					$this->restoreValue($values, $fieldsetChild);
				}
			} else {
				$this->restoreValue($values, $formElement);
			}
		}
	}

	protected function submitFormElement(AbstractFormElement $formElement, array $data, array $files) {
		if ($formElement instanceof InputFormElement) {
			$value = $formElement->hasParent()
				? $data[$formElement->getParent()->getId()][$formElement->getId()] ?? null
				: $data[$formElement->getId()] ?? null;
			$formElement->setValue($value);
		} elseif ($formElement instanceof UploadFormElement) {
			/** @var UploadedFile $file */
			$file = $formElement->hasParent()
				? $files[$formElement->getParent()->getId()][$formElement->getId()] ?? null
				: $files[$formElement->getId()] ?? null;

			if ($file !== null && $file->getError() === UPLOAD_ERR_OK) {
				$filePath = $this->moveUploadedFile($file, $formElement);
				$formElement->setFileName($filePath);
			}
		}
	}

	/**
	 * Moves an uploaded file.
	 * http://www.slimframework.com/docs/v3/cookbook/uploading-files.html
	 *
	 * @param UploadedFile $uploadedFile
	 * @param AbstractFormElement $formElement
	 */
	protected function moveUploadedFile(UploadedFile $uploadedFile, AbstractFormElement $formElement) {
		$extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
		$baseName = $formElement->hasParent()
			? $formElement->getParent()->getId() . '_' . $formElement->getId()
			: $formElement->getId();
		$fileName = sprintf('%s.%0.8s', $baseName, $extension);

		$filePath = $this->formDirectory . $fileName;
		$uploadedFile->moveTo($filePath);

		return $fileName;
	}

	protected function getValue(array &$values, AbstractFormElement $formElement)
	{
		if ($formElement instanceof InputFormElement && $formElement->hasValue()) {
			if ($formElement->hasParent()) {
				$values[$formElement->getParent()->getId()][$formElement->getId()] = $formElement->getValue();
			} else {
				$values[$formElement->getId()] = $formElement->getValue();
			}
		} elseif ($formElement instanceof UploadFormElement && $formElement->isUploaded()) {
			if ($formElement->hasParent()) {
				$values[$formElement->getParent()->getId()][$formElement->getId()] = $formElement->getFileName();
			} else {
				$values[$formElement->getId()] = $formElement->getFileName();
			}
		}
	}

	protected function restoreValue(array $values, AbstractFormElement $formElement)
	{
		if ($formElement instanceof InputFormElement) {
			if ($formElement->hasParent()) {
				$value = $values[$formElement->getParent()->getId()][$formElement->getId()] ?? null;
			} else {
				$value = $values[$formElement->getId()] ?? null;
			}
			if (!empty($value)) {
				$formElement->setValue($value);
			}
		} elseif ($formElement instanceof UploadFormElement) {
			if ($formElement->hasParent()) {
				$value = $values[$formElement->getParent()->getId()][$formElement->getId()];
			} else {
				$value = $values[$formElement->getId()];
			}
			if (!empty($value)) {
				$formElement->setFileName($value);
			}
		}
	}
}