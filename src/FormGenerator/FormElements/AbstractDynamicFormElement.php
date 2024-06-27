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
        return is_array($this->value) ? implode(', ', $this->value) : (string)$this->value;
    }

    /**
     * Sets the value and triggers validation
     *
     * @param mixed $value
     * @return void
     */
    public function setValue($value)
    {
        // to check hasValue(
        if ($value === '' || (is_array($value) && empty($value))) {
            $this->value = null;
        } else {
            $this->value = $value;
        }
    }

    /**
     * @param string $formPath
     */
    public function clearValue(string $formPath): void
    {
        $this->value = null;
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
     * Returns true if config option "readnly" is set to true for this element
     *
     * @return bool
     */
    public function isReadonly()
    {
        return (bool)$this->getConfigValue('readonly') && $this->hasValue();
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
     * Get input placeholder attribute value
     *
     * @return string|null
     */
    public function getPlaceholderValue()
    {
        return $this->getConfigValue('placeholder');
    }

    /**
     * @inheritDoc
     * @return array
     */
    public function getViewVariables(): array
    {
        return array_merge(
            parent::getViewVariables(),
            [
                'id_string' => $this->getFormElementIdStringified(),
                'value' => $this->getValue(),
                'errors' => $this->getErrors(),
                'is_required' => $this->isRequired(),
                'is_readonly' => $this->isReadonly(),
                'placeholder' => $this->getPlaceholderValue()
            ]
        );
    }
}
