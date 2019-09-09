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
    protected $value;

    /**
     * @return bool
     */
    public function hasValue()
    {
        return !empty($this->value);
    }

    /**
     * @param string|null $value
     */
    public function setValue(string $value = null)
    {
        $this->value = $value;
    }

    /**
     * @return string|null
     */
    public function getValue()
    {
        return $this->value;
    }

    public function getValidationRules()
    {
        return $this->getConfigValue('validation') ?? [];
    }

    public function getViewVariables()
    {
        return array_merge($this->getConfig(),[ 'id' => $this->getFormElementId(), 'is_uploaded' => $this->hasValue()]);
    }
}