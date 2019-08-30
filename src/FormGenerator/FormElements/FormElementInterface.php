<?php

namespace CosmoCode\Formserver\FormGenerator\FormElements;


interface FormElementInterface
{
	public function getId();
	public function getType();
	public function getConfig();
	public function getValue();
	public function getViewVariables();
}