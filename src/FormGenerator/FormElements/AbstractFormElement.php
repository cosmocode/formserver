<?php

namespace CosmoCode\Formserver\FormGenerator\FormElements;

use Michelf\MarkdownExtra;

/**
 * Base class for every FormElement.
 * It has
 *  - a unique $id (at least inside its $parent)
 *  - a $config containing all params from config.yaml
 *  - a $type defining which Twig View it will be rendered with
 *  - a function getViewVariables() which passes parameters to the view
 */
abstract class AbstractFormElement
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var AbstractFormElement
     */
    protected $parent;

    /**
     * @var string
     */
    protected $formId;

    /**
     * Inheriting child elements must call this constructor
     *
     * @param string $id
     * @param array $config
     * @param FieldsetFormElement|null $parent
     * @param string $formId
     */
    public function __construct(
        string $id,
        array $config,
        FieldsetFormElement $parent = null,
        string $formId = ''
    ) {
        $this->id = $id;
        $this->config = $config;
        $this->parent = $parent;
        $this->formId = $formId;
    }

    /**
     * Get id which is unique for all AbstractFormElements in $parent->getChildren()
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get id which is unique in complete form
     *
     * @return string
     */
    public function getFormElementId()
    {
        if ($this->parent !== null) {
            return $this->parent->getFormElementId() . '[' . $this->id . ']';
        }

        return $this->getId();
    }

    /**
     * Get unique id as a string instead of an array, with dash as default separator.
     * For use in data attributes with default dash
     *
     * @param string $separator Separator character
     * @return string
     */
    public function getFormElementIdStringified(string $separator = '-'): string
    {
        return str_replace(['[', ']'], [$separator, ''], $this->getFormElementId());
    }

    /**
     * Get the type of the form element
     *
     * @return mixed|null
     */
    public function getType()
    {
        return $this->getConfigValue('type');
    }

    /**
     * Get the complete config of this form element
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Get a specific value of the config
     *
     * @param string $key
     * @return mixed|null
     */
    public function getConfigValue(string $key)
    {
        return $this->config[$key] ?? null;
    }

    /**
     * Get the parent of this form element
     * If none is set return null
     *
     * @return AbstractFormElement|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Return boolean if the form element has a parent
     *
     * @return bool
     */
    public function hasParent(): bool
    {
        return $this->parent !== null;
    }

    /**
     * Parses tooltip.
     * Transform newlines (\n) to NCR representation.
     * If no tooltip given return empty string.
     *
     * @return string
     */
    protected function parseTooltip(): string
    {
        $tooltip = $this->getConfigValue('tooltip') ?? '';

        return str_replace("\n", '&#10;&#013;', $tooltip);
    }

    /**
     * Applies markdown to modals and corrects image paths
     *
     * @return string
     */
    protected function parseModal(): string
    {
        $modal = $this->getConfigValue('modal') ?? '';
        if ($modal) {
            $modal = MarkdownExtra::defaultTransform($modal);
            $modal = str_replace('<img src="', '<img src="/download/' . $this->formId . '?file=', $modal);
        }
        return $modal;
    }

    /**
     * Prepare variables array for twig view
     *
     * @return array
     */
    public function getViewVariables(): array
    {
        return array_merge(
            $this->config,
            [
                'id' => $this->getFormElementId(),
                'tooltip' => $this->parseTooltip(),
                'modal' => $this->parseModal(),
                ]
        );
    }

    /**
     * Generate html form id from dotted id
     * e.g. 'fieldset1.fieldset2.textarea1' --> 'fieldset1[fieldset2][textarea1]'
     *
     * @param string $id
     * @return string
     */
    protected function dottedIdToFormId(string $id): string
    {
        $toggleIdPath = explode('.', $id);
        $togglePathCount = count($toggleIdPath);
        $toggleViewId = $toggleIdPath[0];

        for ($i = 1; $i < $togglePathCount; $i++) {
            $toggleViewId .= '[' .$toggleIdPath[$i] . ']';
        }

        return $toggleViewId;
    }
}
