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

    public function getValue()
    {
        return $this->value;
    }

    /**
     * Update previous value on every upload
     *
     * @param mixed $value
     */
    public function setValue($value)
    {
        if (! empty($value)) {
            if (is_array($value)) {
                $this->value = $value;
            } else {
                $this->value[] = $value;
            }
        }
        $this->setPreviousValue($this->value);
    }

    public function getPreviousValue()
    {
        return json_decode($this->previousValue);
    }

    public function setPreviousValue($value)
    {
        $this->previousValue = json_encode($value);
    }

    /**
     * Remove old file before resetting the value, useful when clearing the form after submit
     *
     * @param $formPath
     */
    public function clearValue($formPath)
    {
        if ($this->value && is_file($formPath . $this->value)) {
            unlink($formPath . $this->value);
        }
        $this->setValue(null);
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
                'previous_value' => json_encode($this->getPreviousValue()),
                'is_required' => $this->isRequired()
            ]
        );
    }
}
