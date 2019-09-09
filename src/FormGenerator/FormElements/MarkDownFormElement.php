<?php

namespace CosmoCode\Formserver\FormGenerator\FormElements;


use Michelf\Markdown;

/**
 * Renders markdown
 */
class MarkDownFormElement extends AbstractFormElement
{
    /**
     * Prepare variables array for twig view.
     *
     * @return array
     */
    public function getViewVariables()
    {
        $markdown = Markdown::defaultTransform($this->getConfig()['markdown'] ?? '');

        return array_merge($this->getConfig(),[ 'id' => $this->getFormElementId(), 'markdown' => $markdown]);
    }
}