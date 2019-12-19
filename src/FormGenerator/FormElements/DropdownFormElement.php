<?php

namespace CosmoCode\Formserver\FormGenerator\FormElements;

/**
 * Representation of a select input
 */
class DropdownFormElement extends AbstractDynamicFormElement
{
    /**
     * Sets the default value defined in the config.
     * if none given do nothing
     *
     * @return void
     */
    public function setdefaultvalue()
    {
        $defaultValue = $this->getConfigValue('default');

        if ($defaultValue) {
            $this->setValue($defaultValue);
        }
    }
}
