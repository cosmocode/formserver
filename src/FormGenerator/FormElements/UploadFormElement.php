<?php

namespace CosmoCode\Formserver\FormGenerator\FormElements;

/**
 * Dynamic form elements have an input which the user can fill out
 */
class UploadFormElement extends AbstractFormElement
{
	/**
	 * @var string
	 */
	protected $fileName;

	/**
	 * @return bool
	 */
	public function isUploaded()
	{
		return !empty($this->fileName);
	}

	/**
	 * @param string $fileName
	 */
	public function setFileName(string $fileName)
	{
		$this->fileName = $fileName;
	}

	/**
	 * @return string
	 */
	public function getFileName()
	{
		return $this->fileName;
	}

	public function getValidationRules()
	{
		return $this->getConfigValue('validation') ?? [];
	}

	public function getViewVariables()
	{
		return array_merge($this->getConfig(),[ 'id' => $this->getFormElementId(), 'is_uploaded' => $this->isUploaded()]);
	}
}