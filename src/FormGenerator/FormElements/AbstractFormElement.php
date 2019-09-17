<?php

namespace CosmoCode\Formserver\FormGenerator\FormElements;

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
     * Inheriting child elements must call this constructor
     *
     * @param string $id
     * @param array $config
     * @param AbstractFormElement|null $parent
     */
    public function __construct(
        string $id,
        array $config,
        AbstractFormElement $parent = null
    ) {
        $this->id = $id;
        $this->config = $config;
        $this->parent = $parent;
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
    public function getConfig()
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
    public function hasParent()
    {
        return $this->parent !== null;
    }

    /**
     * Prepare variables array for twig view
     *
     * @return array
     */
    abstract public function getViewVariables();
}
