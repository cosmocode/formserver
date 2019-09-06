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

    /**
     * @return bool
     */
    public function hasValue()
    {
        return !empty($this->value);
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
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