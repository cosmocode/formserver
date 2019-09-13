<?php

namespace CosmoCode\Formserver\FormGenerator\FormElements;

/**
 * Dynamic form elements have an input which the user can fill out
 */
class InputFormElement extends AbstractDynamicFormElement
{
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
