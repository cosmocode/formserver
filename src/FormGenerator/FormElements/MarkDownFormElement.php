<?php

namespace CosmoCode\Formserver\FormGenerator\FormElements;


use Michelf\MarkdownExtra;

/**
 * Renders markdown
 */
class MarkDownFormElement extends AbstractFormElement
{
    /**
     * Get markdown defined in the config
     *
     * @return string
     */
    protected function getMarkdown()
    {
        return $this->getConfig()['markdown'] ?? '';
    }

    /**
     * Prepare variables array for twig view.
     *
     * @return array
     */
    public function getViewVariables()
    {
        $markdown = MarkdownExtra::defaultTransform($this->getMarkdown());

        return array_merge(
            $this->getConfig(),
            [
                'id' => $this->getFormElementId(),
                'markdown' => $markdown
            ]
        );
    }
}
