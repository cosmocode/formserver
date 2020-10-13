<?php

namespace CosmoCode\Formserver\FormGenerator\FormElements;

/**
 * Representation of a file upload
 */
class UploadFormElement extends AbstractDynamicFormElement
{
    /**
     * @var string
     */
    protected $previousValue = '';

    /**
     * Update previous value on every upload
     *
     * @param mixed $value
     */
    public function setValue($value)
    {
        parent::setValue($value);
        $this->setPreviousValue($value);
    }

    public function getPreviousValue()
    {
        return $this->previousValue;
    }

    public function setPreviousValue($value)
    {
        $this->previousValue = $value;
    }

    /**
     * Remove old file before resetting the value, useful when clearing the form after submit
     *
     * @param $formPath
     */
    public function setDefaultValue($formPath)
    {
        if ($this->value && is_file($formPath . $this->value)) {
            unlink($formPath . $this->value);
            unset($this->value);
        }
    }

    /**
     * Get allowed extension for this upload
     *
     * @return string
     */
    public function getAllowedExtensionsAsString()
    {
        return strtolower(
            $this->getConfig()['validation']['fileext'] ?? ''
        );
    }

    /**
     * Get allowed extension for this upload (as array)
     *
     * @return array
     */
    public function getAllowedExtensionsAsArray()
    {
        $allowedExtensions = explode(',', $this->getAllowedExtensionsAsString());
        // remove possible whitespaces after comma (e.g. "pdf, txt, png")
        foreach ($allowedExtensions as &$allowedExtension) {
            $allowedExtension = trim($allowedExtension);
        }
        return $allowedExtensions;
    }

    /**
     * @inheritDoc
     * @return array
     */
    public function getViewVariables()
    {
        return array_merge(
            $this->getConfig(),
            [
                'id' => $this->getFormElementId(),
                'is_uploaded' => $this->hasValue(),
                'errors' => $this->getErrors(),
                'allowed_extensions' => $this->getAllowedExtensionsAsArray(),
                'value' => $this->getValue(),
                'previous_value' => $this->getPreviousValue(),
                'is_required' => $this->isRequired()
            ]
        );
    }
}
