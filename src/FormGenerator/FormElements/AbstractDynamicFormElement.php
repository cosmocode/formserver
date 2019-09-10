<?php

namespace CosmoCode\Formserver\FormGenerator\FormElements;

use Respect\Validation\Validator as Validator;

/**
 * Base class for every InputFormElement
 */
abstract class AbstractDynamicFormElement extends AbstractFormElement
{
    /**
     * @var mixed
     */
    protected $value;

    /** @var array */
    protected $errors = [];

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
     * Sets the value and triggers validation
     *
     * @param string|null $value
     */
    public function setValue($value)
    {
        $this->value = $value;
        $this->validateValue($value);
    }

    /**
     * Returns the field's configured validation rules
     *
     * @return array
     */
    public function getValidationRules()
    {
        return $this->getConfigValue('validation') ?? [];
    }

    /**
     * Validates the input value
     *
     * @param string $value
     */
    public function validateValue($value)
    {
        foreach ($this->getValidationRules() as $rule) {
            $validation = key($rule);
            $allowed = $rule[$validation];

            switch ($validation) {
                case 'min':
                    if (!Validator::intVal()->min($allowed)->validate($value)) {
                        $this->addError('value smaller than ' . $allowed);
                    }
                    break;
                case 'max':
                    if (!Validator::intVal()->max($allowed)->validate($value)) {
                        $this->addError('value larger than ' . $allowed);
                    }
                    break;
                case 'match':
                    if (!Validator::regex($allowed)->validate($value)) {
                        $this->addError('value does not match ' . $allowed);
                    }
                    break;
            }
        }
    }

    /**
     * Attaches an error to the form element
     *
     * @param string $error
     */
    public function addError($error)
    {
        $this->errors[] = $error;
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

    public function isValid() {
        return empty($this->errors);
    }
}
