<?php

namespace CosmoCode\Formserver\FormGenerator\FormElements;

/**
 * Dynamic form elements have an input which the user can fill out
 */
class UploadFormElement extends AbstractDynamicFormElement
{

    public function getAllowedExtensions() {
        return strtolower(
            $this->getConfig()['validation']['fileext'] ?? ''
        );
    }

    public function getAllowedExtensionsAsArray() {
        return explode(',', $this->getAllowedExtensions());
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
            ]
        );
    }
}
