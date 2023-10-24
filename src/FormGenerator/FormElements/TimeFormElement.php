<?php

namespace CosmoCode\Formserver\FormGenerator\FormElements;

/**
 * Representation of a text input containing a time
 */
class TimeFormElement extends AbstractDynamicFormElement
{
    /**
     * Handle values in a clonable field
     *
     * @return array
     */
    public function getViewVariables()
    {
        $conf = parent::getViewVariables();
        if (!empty($conf['clone']) && !is_array($conf['value'])) {
            $conf['value'] = [$conf['value']];
        }

        return $conf;
    }
}
