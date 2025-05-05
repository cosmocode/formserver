<?php

declare(strict_types=1);

namespace CosmoCode\Formserver\FormGenerator;

use CosmoCode\Formserver\Helper\ConfigTransformHelper;
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
    public const MODE_SAVE = 'save';
    public const MODE_SEND = 'send';

    /**
     * @var string
     */
    protected string $id;

    protected array $elementsConfig;

    protected array $meta;

    protected array|\stdClass $values;

    protected array $lang;

    protected bool $persistent;

    /**
     * Build a form from YAML
     *
     * @param string $formId
     */
    public function __construct(string $formId)
    {
        $this->id = $formId;
        $config = YamlHelper::parseYaml($this->getFormDirectory() . 'config.yaml');
        $this->elementsConfig = $config['form'];
        $this->meta = $config['meta'] ?? [];
        $this->persistent = (bool)$this->getMeta('saveButton');

        LangManager::init($this->getMeta('language'));
        $this->lang = LangManager::getTranslations();

        try {
            $this->values = YamlHelper::parseYaml($this->getFormDirectory() . 'values.yaml');
        } catch (\Exception $exception) {
            // no stored values.yaml, an empty object in JSON
            $this->values = new \stdClass();
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
     * Is saving data enabled in this form?
     *
     * @return bool
     */
    public function isPersistent(): bool
    {
        return $this->persistent;
    }

    /**
     * Persist all submitted data to YAML
     *
     * @param array $data
     * @return void
     */
    public function persist(array $data): void
    {
        if (! empty($data)) {
            YamlHelper::persistYaml(
                $data,
                $this->getFormDirectory() . 'values.yaml'
            );
        }
    }

    /**
     * Part of form config that contains the elements
     *
     * @return array
     */
    public function getElementsConfig(): array
    {
        return $this->elementsConfig;
    }

    /**
     * Form JSON to be processed in FE
     * @return string
     */
    public function getJSON(): string
    {
        return json_encode(
            [
                'meta' => $this->meta,
                'form' => ConfigTransformHelper::transform($this->elementsConfig, $this->id),
                'lang' => $this->lang,
                'values' => $this->values,
            ],
            JSON_PRETTY_PRINT
        );
    }
}
