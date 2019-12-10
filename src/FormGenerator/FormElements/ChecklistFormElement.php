<?php

namespace CosmoCode\Formserver\FormGenerator\FormElements;

use Michelf\MarkdownExtra;

/**
 * Representation of a checkbox group
 */
class ChecklistFormElement extends AbstractDynamicFormElement
{
    /**
     * Override parent to transform markdown choice labels
     *
     * @return array
     */
    public function getViewVariables()
    {
        $choices = $this->getConfigValue('choices');
        $transformedChoices = [];

        foreach ($choices as $choiceValue => $choiceLabel) {
            $transformedChoiceLabel = MarkdownExtra::defaultTransform($choiceLabel);

            // Markdown lib always wraps the content in a <p>...</p>
            // https://github.com/michelf/php-markdown/issues/230
            $transformedChoiceLabel = str_replace(
                ['<p>', '</p>'],
                '',
                $transformedChoiceLabel
            );

            $transformedChoices[$transformedChoiceLabel] = $choiceValue;
        };

        return array_merge(
            parent::getViewVariables(),
            [
                'choices' => $transformedChoices
            ]
        );
    }
}
