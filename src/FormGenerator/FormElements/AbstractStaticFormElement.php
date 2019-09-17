<?php

namespace CosmoCode\Formserver\FormGenerator\FormElements;

/**
 * Static form elements have no user input
 */
abstract class AbstractStaticFormElement extends AbstractFormElement
{
    /**
     * @inheritdoc
     * @return array
     */
    public function getViewVariables()
    {
        return array_merge(
            $this->getConfig(),
            [
                'id' => $this->getFormElementId()
            ]
        );
    }
}
