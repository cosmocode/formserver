<?php

namespace CosmoCode\Formserver\FormGenerator\FormElements;


/**
 * FieldsetFormElement is a special StaticFormElement, since it contains child formElements
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

	public function __construct($id, array $config, AbstractFormElement $parent = null)
	{
		unset($config['children']); // Children config not needed
		parent::__construct($id, $config, $parent);
	}

	/**
	 * @return AbstractFormElement[]
	 */
	public function getChildren() {
		return $this->children;
	}

	public function addChild(AbstractFormElement $child) {
		$this->children[] = $child;
	}


	/**
	 * @return string[]
	 */
	public function getRenderedChildViews(): array
	{
		return $this->renderedChildViews;
	}

	/**
	 * @param string $renderedView
	 */
	public function addRenderedChildView(string $renderedView)
	{
		$this->renderedChildViews[] = $renderedView;
	}


	public function getViewVariables()
	{
		return array_merge($this->getConfig(),
			[
				'id' => $this->getFormElementId(),
				'renderedChildViews' => $this->renderedChildViews,
			]
		);
	}
}