<?php

namespace CosmoCode\Formserver\FormGenerator\FormElements;

use Michelf\MarkdownExtra;

/**
 * Representation of a checkbox group
 */
class ChecklistFormElement extends AbstractDynamicFormElement
{
    /**
     * Sets the default value defined in the config.
     * If none given do nothing
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

    /**
     * Override parent to transform markdown choice labels
     *
     * @return array
     */
    public function getViewVariables()
    {
        $choices = $this->getConfigValue('choices');
        $transformedChoices = [];

        foreach ($choices as $choice) {
            $transformedChoice= MarkdownExtra::defaultTransform($choice);

            // Markdown lib always wraps the content in a <p>...</p>
            // https://github.com/michelf/php-markdown/issues/230
            $transformedChoices[] = str_replace(
                ['<p>', '</p>'],
                '',
                $transformedChoice
            );
        };

        return array_merge(
            parent::getViewVariables(),
            [
                'transformed_choices' => $transformedChoices
            ]
        );
    }
}
