<?php

namespace CosmoCode\Formserver\FormGenerator\FormElements;


/**
 * FieldsetFormElement is a special AbstractFormElement.
 * It contains child formElements
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
     * @param string $id
     * @param array $config
     * @param AbstractFormElement|null $parent
     * @inheritdoc
     */
    public function __construct(
        string $id, array $config,
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
    public function hasToggle() {
        return !empty($this->getConfigValue('toggle'));
    }

    /**
     * Get toggle configuration
     *
     * @return array
     */
    public function getToggleVariables() {
        if ($this->hasToggle()) {
            $toggleId = $this->getToggleFieldId();
            $toggleValue = $this->getToggleValue();
            if (strpos($toggleId, '.')) {
                $toggleId = str_replace('.', '[', $toggleId) . ']';
            }

            return [
                'toggle' => [
                    'id' => $toggleId,
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
    public function getToggleFieldId() {
        $toggleConfig = $this->getConfigValue('toggle');

        return $toggleConfig['field'];
    }

    /**
     * Value which triggeres the toggle (show)
     *
     * @return mixed
     */
    public function getToggleValue() {
        $toggleConfig = $this->getConfigValue('toggle');

        return $toggleConfig['value'];
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function getViewVariables()
    {
        return array_merge(
            $this->getConfig(),
            [
                'id' => $this->getFormElementId(),
                'rendered_child_views' => $this->renderedChildViews,
            ],
            $this->getToggleVariables()
        );
    }
}
