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


	public function __construct($id, array $config)
	{
		$this->id = $id;

		$this->config = $config;
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

	abstract public function getViewVariables();
}