<?php

namespace CosmoCode\Formserver\FormGenerator\FormElements;

/**
 * Representation of a select input
 */
class DropdownFormElement extends AbstractDynamicFormElement
{
    /**
     * On multiselect elements discard the size attribute
     *
     * @return array
     */
    public function getViewVariables()
    {
        $conf = parent::getViewVariables();

        if (empty($conf['multiselect'])) {
            unset($conf['size']);
        }

        return $conf;
    }

    /**
     * Sets the default value defined in the config.
     * if none given do nothing
     *
     * @return void
     */
    public function setDefaultValue()
    {
        if ($this->getValue()) {
            return;
        }

        $defaultValue = $this->getConfigValue('default');

        if ($defaultValue) {
            $this->setValue($defaultValue);
        }
    }
}
