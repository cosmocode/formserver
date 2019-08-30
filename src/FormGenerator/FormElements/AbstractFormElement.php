<?php

namespace CosmoCode\Formserver\FormGenerator\FormElements;


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

	public function __construct($id, array $config, AbstractFormElement $parent = null)
	{
		$this->id = $id;
		$this->config = $config;
		$this->parent = $parent;
	}

	/**
	 * Get id which is unique for all AbstractFormElements in $parent->getChildren()
	 *
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Get id which is unique in complete form
	 *
	 * @return string
	 */
	public function getFormElementId() {
		if ($this->parent !== null) {
			return $this->parent->getFormElementId() . '[' . $this->id . ']';
		}

		return $this->getId();
	}

	public function getType() {
		return $this->getConfig()['type'];
	}

	public function getConfig()
	{
		return $this->config;
	}

	abstract public function getViewVariables();
}