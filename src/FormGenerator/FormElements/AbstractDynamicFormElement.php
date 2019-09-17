<?php

namespace CosmoCode\Formserver\FormGenerator\FormElements;

use CosmoCode\Formserver\Service\LangManager;

/**
 * Dynamic form elements have an input which the user can enter.
 */
abstract class AbstractDynamicFormElement extends AbstractFormElement
{
    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * Returns true if this form element has a value
     *
     * @return bool
     */
    public function hasValue()
    {
        return $this->value !== null;
    }

    /**
     * Returns raw value (array in case of multiselect elements)
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Element's value as string
     *
     * @return string
     */
    public function getValueString()
    {
        return is_string($this->value) ? $this->value : implode(', ', $this->value);
    }

    /**
     * Sets the value and triggers validation
     *
     * @param string|null $value
     * @return void
     */
    public function setValue($value)
    {
        // to check hasValue(
        if ($value === '') {
            $this->value = null;
        } else {
            $this->value = $value;
        }
    }

    /**
     * Returns the field's configured validation rules
     *
     * @return array
     */
    public function getValidationRules()
    {
        $validationRules = $this->getConfigValue('validation') ?? [];

        // Enforce required by default
        if (! isset($validationRules['required'])) {
            $validationRules['required'] = true;
        }

        return $validationRules;
    }

    /**
     * Return true if this form element is required
     *
     * @return bool
     */
    public function isRequired()
    {
        return $this->getValidationRules()['required'];
    }

    /**
     * Attaches an error to the form element
     *
     * @param string $error
     * @param string $allowed
     * @return void
     */
    public function addError($error, $allowed = '')
    {
        $format = '%s';
        if ($allowed) {
            $format .= ': "%s"';
        }
        $this->errors[] = sprintf($format, LangManager::getString($error), $allowed);
    }

    /**
     * Returns the field's errors
     *
     * @return string[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Return true if the value of this form element is marked as valid
     *
     * @return bool
     */
    public function isValid()
    {
        return empty($this->errors);
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
                'value' => $this->getValue(),
                'errors' => $this->getErrors(),
                'is_required' => $this->isRequired()
            ]
        );
    }
}
