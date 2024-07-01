<?php

namespace CosmoCode\Formserver\FormGenerator\FormElements;

use CosmoCode\Formserver\Exceptions\FormException;
use CosmoCode\Formserver\FormGenerator\FormValidator;

/**
 * Representation of a fieldset.
 * This is a special form element. It contains other form elements in $children.
 * It also can be disabled (when the toggle condition does not match)
 */
class FieldsetFormElement extends AbstractFormElement
{

    /**
     * @var AbstractFormElement[]
     */
    protected $children;

    /**
     * @var string[]
     */
    protected $renderedChildViews = [];

    /**
     * @var bool
     */
    protected $disabled = false;

    /**
     * @param string $id
     * @param array $config
     * @param AbstractFormElement|null $parent
     * @inheritdoc
     */
    public function __construct(
        string $id,
        array $config,
        AbstractFormElement $parent = null
    ) {
        unset($config['children']); // Children config not needed
        parent::__construct($id, $config, $parent);
    }

    /**
     * Get a list of containing child form elements
     *
     * @return AbstractFormElement[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Get child by id
     *
     * @param string $id
     * @return AbstractFormElement
     */
    public function getChild(string $id)
    {
        foreach ($this->getChildren() as $fieldsetChild) {
            if ($fieldsetChild->getId() === $id) {
                return $fieldsetChild;
            }
        }

        throw new FormException(
            "Could not get child '$id' in fieldset '" . $this->getId() . "'"
        );
    }

    /**
     * Add a form element to this fieldet form element
     *
     * @param AbstractFormElement $child
     * @return void
     */
    public function addChild(AbstractFormElement $child)
    {
        $this->children[] = $child;
    }


    /**
     * Get the rendered form element views
     *
     * @return string[]
     */
    public function getRenderedChildViews()
    {
        return $this->renderedChildViews;
    }

    /**
     * Add a rendered form element view
     *
     * @param string $renderedView
     * @return void
     */
    public function addRenderedChildView(string $renderedView)
    {
        $this->renderedChildViews[] = $renderedView;
    }

    /**
     * Return bool indicating if this fieldset can be toggled
     *
     * @return bool
     */
    public function hasToggle()
    {
        return ! empty($this->getConfigValue('toggle'));
    }

    /**
     * @return bool
     */
    public function isDisabled()
    {
        return $this->disabled;
    }

    /**
     * @param bool $disabled
     * @return void
     */
    public function setDisabled(bool $disabled)
    {
        $this->disabled = $disabled;
    }


    /**
     * Get toggle configuration
     *
     * @return array
     */
    public function getToggleVariables()
    {
        if ($this->hasToggle()) {
            $toggleId = $this->getToggleFieldId();
            $toggleViewId = $this->dottedIdToFormId($toggleId);
            $toggleValue = $this->getToggleValue();

            return [
                'toggle' => [
                    'id' => $toggleViewId,
                    'value' => $toggleValue
                ]
            ];
        }

        return ['toggle' => null];
    }

    /**
     * Get id of the form element which toggles this fieldset
     *
     * @return string
     */
    public function getToggleFieldId()
    {
        $toggleConfig = $this->getConfigValue('toggle');

        return $toggleConfig['field'];
    }

    /**
     * Array of values which trigger the toggle (show)
     *
     * @return mixed
     */
    public function getToggleValue()
    {
        $toggleConfig = $this->getConfigValue('toggle');

        return htmlspecialchars(json_encode((array)$toggleConfig['value'], JSON_THROW_ON_ERROR));
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function getViewVariables(): array
    {
        return array_merge(
            $this->getConfig(),
            [
                'id' => $this->getFormElementId(),
                'rendered_child_views' => $this->renderedChildViews,
            ],
            $this->getToggleVariables(),
            $this->processBackgroundVariables()
        );
    }

    /**
     * Check if the background value from config can be used as a Bulma color
     * or a valid CSS color definition and return the appropriate view variable.
     *
     * @return array
     */
    protected function processBackgroundVariables()
    {
        $background = $this->getConfigValue('background');
        if (!$background) {
            return [];
        }

        if (in_array($background, FormValidator::COLORS_BULMA)) {
            return ['backgroundName' => $background];
        }
        if (preg_match(FormValidator::COLORS_REGEX, $background)) {
            return ['backgroundNumber' => $background];
        }
        return [];
    }
}
