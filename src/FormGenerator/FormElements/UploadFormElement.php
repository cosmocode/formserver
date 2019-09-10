<?php

namespace CosmoCode\Formserver\FormGenerator\FormElements;

/**
 * Dynamic form elements have an input which the user can fill out
 */
class UploadFormElement extends AbstractDynamicFormElement
{

    /**
     * Get allowed extension for this upload
     *
     * @return string
     */
    public function getAllowedExtensionsAsString() {
        return strtolower(
            $this->getConfig()['validation']['fileext'] ?? ''
        );
    }

    /**
     * Get allowed extension for this upload (as array)
     * @return array
     */
    public function getAllowedExtensionsAsArray() {
        $allowedExtensions = explode(',', $this->getAllowedExtensionsAsString());
        // remove possible whitespaces after comma (e.g. "pdf, txt, png")
        foreach ($allowedExtensions as &$allowedExtension) {
            $allowedExtension = trim($allowedExtension);
        }
        return $allowedExtensions;
    }

    /**
     * @inheritDoc
     */
    public function getViewVariables()
    {
        return array_merge(
            $this->getConfig(),
            [
                'id' => $this->getFormElementId(),
                'is_uploaded' => $this->hasValue(),
                'errors' => $this->getErrors(),
                'allowed_extensions' => $this->getAllowedExtensionsAsArray()
            ]
        );
    }
}
