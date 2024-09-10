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
    protected array $children;

    /**
     * @var string[]
     */
    protected array $renderedChildViews = [];

    /**
     * @var bool
     */
    protected bool $disabled = false;

    /**
     * @param string $id
     * @param array $config
     * @param AbstractFormElement|null $parent
     * @inheritdoc
     */
    public function __construct(
        string $id,
        array $config,
        FieldsetFormElement $parent = null,
        string $formId = '',
        bool $formIsPersistent = false
    ) {
        unset($config['children']); // Children config not needed
        parent::__construct($id, $config, $parent, $formId, $formIsPersistent);
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
    public function getChild(string $id): AbstractFormElement
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
    public function addChild(AbstractFormElement $child): void
    {
        $this->children[] = $child;
    }


    /**
     * Get the rendered form element views
     *
     * @return string[]
     */
    public function getRenderedChildViews(): array
    {
        return $this->renderedChildViews;
    }

    /**
     * Add a rendered form element view
     *
     * @param string $renderedView
     * @return void
     */
    public function addRenderedChildView(string $renderedView): void
    {
        $this->renderedChildViews[] = $renderedView;
    }

    /**
     * Return bool indicating if this fieldset can be toggled
     *
     * @return bool
     */
    public function hasToggle(): bool
    {
        return ! empty($this->getConfigValue('toggle'));
    }

    /**
     * @return bool
     */
    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    /**
     * @param bool $disabled
     * @return void
     */
    public function setDisabled(bool $disabled): void
    {
        $this->disabled = $disabled;
    }


    /**
     * Get toggle configuration
     *
     * @return array
     */
    public function getToggleVariables(): array
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
    public function getToggleFieldId(): string
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
        $conf = parent::getViewVariables();
        return array_merge(
            $conf,
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
    protected function processBackgroundVariables(): array
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
