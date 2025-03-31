<?php

namespace CosmoCode\Formserver\FormGenerator;

use CosmoCode\Formserver\Exceptions\FormException;
use CosmoCode\Formserver\FormGenerator\FormElements\AbstractDynamicFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\AbstractFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\ChecklistFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\DropdownFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\FieldsetFormElement;
use CosmoCode\Formserver\FormGenerator\FormElements\UploadFormElement;
use CosmoCode\Formserver\Helper\FileHelper;
use CosmoCode\Formserver\Helper\LegacyHelper;
use CosmoCode\Formserver\Helper\YamlHelper;
use CosmoCode\Formserver\Service\LangManager;

/**
 * Contains the form and provides basic functionality
 *
 * @package CosmoCode\Formserver\FormGenerator
 */
class Form
{
    public const DATA_DIR = __DIR__ . '/../../data/';

    public const MODE_SHOW = 'show';
    public const MODE_SAVE = 'save';
    public const MODE_SEND = 'send';

    /**
     * @var string
     */
    protected string $id;

    protected array $elements;

    protected array $meta;

    protected array $values;

    protected array $lang;

    /**
     * @var string
     */
    protected string $mode = self::MODE_SHOW;

    protected bool $isPersistent;

    /**
     * Build a form from YAML
     *
     * @param string $formId
     */
    public function __construct(string $formId)
    {
        $this->id = $formId;
        $config = YamlHelper::parseYaml($this->getFormDirectory() . 'config.yaml');
        $this->elements = $config['form'];
        $this->meta = $config['meta'] ?? [];
        $this->isPersistent = (bool)$this->getMeta('saveButton');

        LangManager::init($this->getMeta('language'));
        $this->lang = LangManager::getTranslations();

        try {
            $this->values = YamlHelper::parseYaml($this->getFormDirectory() . 'values.yaml');
        } catch (\Exception $exception) {
            // no stored values.yaml
            $this->values = [];
        }

    }

    /**
     * Get the meta config
     *
     * @param string $key
     * @return mixed|null
     */
    public function getMeta(string $key)
    {
        return $this->meta[$key] ?? null;
    }

    /**
     * Get the directory path of current form
     *
     * @return string
     */
    public function getFormDirectory(): string
    {
        return self::DATA_DIR . $this->id . '/';
    }

    /**
     * Get the id of current form
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Returns the current mode (user intend)
     *
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * Submit data to the form
     *
     * @param array $data $_POST
     * @param array $files $_FILES
     * @return void
     * @throws \JsonException
     */
    public function submit(array $data, array $files): void
    {
        // submit data
        foreach ($this->formElements as $formElement) {
            $this->submitFormElement($formElement, $data, $files);
        }

        // En-/disable fieldsets depending on toggle value
        // This must happen after all POST data was submitted
        foreach ($this->formElements as $formElement) {
            $this->toggleFieldsets($formElement);
        }

        $this->setMode($data);
    }

    /**
     * Persist all submitted data to YAML
     *
     * @return void
     */
    public function persist()
    {
        $values = [];
        foreach ($this->formElements as $formElement) {
            $this->injectValueToArray($values, $formElement);
        }

        if (! empty($values)) {
            YamlHelper::persistYaml(
                $values,
                $this->getFormDirectory() . 'values.yaml'
            );
        }
    }

    /**
     * Restore all persisted data or load defaults
     *
     * @return void
     */
    public function restore()
    {
        // Form was saved before. Restore data
        if (is_file($this->getFormDirectory() . 'values.yaml')) {
            $values = YamlHelper::parseYaml(
                $this->getFormDirectory() . 'values.yaml'
            );

            foreach ($this->formElements as $formElement) {
                $this->restoreValue($values, $formElement);
            }
        } else {
            // Form was never saved before. Set default values
            foreach ($this->formElements as $formElement) {
                $this->setDefaultValues($formElement);
            }
        }
    }

    /**
     * Returns boolean if all form elements of this form are valid
     *
     * @return bool
     */
    public function isValid()
    {
        foreach ($this->formElements as $formElement) {
            if (! $this->isFormElementValid($formElement)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Reset all form elements to either empty or default values
     *
     * @param $formElements
     */
    public function reset($formElements)
    {
        foreach ($formElements as $formElement) {
            if ($formElement instanceof FieldsetFormElement) {
                $childElements = $formElement->getChildren();
                $this->reset($childElements);
            } elseif ($formElement instanceof AbstractDynamicFormElement) {
                $formElement->clearValue($this->getFormDirectory());
            }
        }

        foreach ($formElements as $formElement) {
            $this->setDefaultValues($formElement);
        }
    }

    /**
     * Sets the current mode
     *
     * @param array $data
     * @return void
     */
    protected function setMode(array $data)
    {
        if (isset($data['formcontrol']['send'])) {
            $this->mode = self::MODE_SEND;
        } elseif (isset($data['formcontrol']['save'])) {
            $this->mode = self::MODE_SAVE;
        } else {
            $this->mode = self::MODE_SHOW;
        }
    }

    /**
     * Form JSON to be processed in FE
     * @return string
     */
    public function getJSON()
    {
        return json_encode([
            'meta' => $this->meta,
            'form' => LegacyHelper::transform($this->elements),
            'lang' => $this->lang,
            'values' => $this->values,
            ],
        JSON_PRETTY_PRINT);
    }
}
