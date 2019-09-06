<?php

namespace CosmoCode\Formserver\FormGenerator\FormElements;

/**
 * Dynamic form elements have an input which the user can fill out
 */
class InputFormElement extends AbstractFormElement
{
    /**
     * @var mixed
     */
    protected $value;

    public function hasValue()
    {
        return !empty($this->value);
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getValidationRules()
    {
        return $this->getConfigValue('validation') ?? [];
    }

    public function getViewVariables()
    {
        return array_merge($this->getConfig(),[ 'id' => $this->getFormElementId(), 'value' => $this->getValue()]);
    }
}