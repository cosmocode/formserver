<?php

namespace CosmoCode\Formserver\FormGenerator\FormElements;

use Respect\Validation\Validator as v;

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
	/** @var array */
    protected $errors = [];


    /**
     * Sets the value and triggers validation
     *
     * @param string $value
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
            switch (key($rule)) {
                case 'min':
                    if (!v::intVal()->min($rule['min'])->validate($value)) {
                        $this->addError('wrong min');
                    }
                    break;
                case 'max':
                    if (!v::intVal()->max($rule['max'])->validate($value)) {
                        $this->addError('wrong max');
                    }
                    break;
                case 'match':
                    if (!v::regex($rule['match'])->validate($value)) {
                        $this->addError('no match');
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

	public function getViewVariables()
	{
		return array_merge(
		    $this->getConfig(),
            [
                'id' => $this->getFormElementId(),
                'value' => $this->getValue(),
                'errors' => $this->getErrors()
            ]
        );
	}
}
