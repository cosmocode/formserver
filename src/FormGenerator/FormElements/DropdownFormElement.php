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
    public function getViewVariables(): array
    {
        $conf = parent::getViewVariables();

        if (empty($conf['multiselect'])) {
            unset($conf['size']);
        }

        // prepare conditional select
        if (isset($conf['conditional_choices']) && is_array($conf['conditional_choices'])) {
            foreach ($conf['conditional_choices'] as $k => $select) {
                isset($select['field']) && $conf['conditional_choices'][$k]['field'] = $this->dottetIdToFormId($select['field']);
                isset($select['value']) && $conf['conditional_choices'][$k]['value'] = json_encode((array)$select['value'], JSON_THROW_ON_ERROR);
            }
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
