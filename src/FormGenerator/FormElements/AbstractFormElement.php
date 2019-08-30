<?php

namespace CosmoCode\Formserver\FormGenerator\FormElements;


abstract class AbstractFormElement implements FormElementInterface
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
	 * @var mixed
	 */
	protected $value;

	public function __construct($id, array $config)
	{
		$this->id = $id;

		$this->config = $config;
		unset($this->config['children']); // Children config not needed
	}

	public function getId() {
		return $this->id;
	}

	public function getType() {
		return $this->getConfig()['type'];
	}

	public function getConfig()
	{
		return $this->config;
	}

	public function getValue() {
		return $this->value;
	}
}