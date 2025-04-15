<?php

declare(strict_types=1);

namespace CosmoCode\Formserver\Helper;

use CosmoCode\Formserver\Exceptions\YamlException;

/**
 * Helper class to load and persist YAML files
 *
 * @package CosmoCode\Formserver\Helper
 */
class YamlHelper
{
    /**
     * Loads and parses a YAML
     *
     * @param string $yamlPath
     * @return mixed
     * @throws YamlException
     */
    public static function parseYaml(string $yamlPath)
    {
        if (! is_file($yamlPath)) {
            throw new YamlException("Could not load yaml: $yamlPath");
        }

        return \Spyc::YAMLLoad($yamlPath);
    }

    /**
     * Saves a YAML to $yamlPath
     *
     * @param array $values
     * @param string $yamlPath
     * @return void
     */
    public static function persistYaml(array $values, string $yamlPath)
    {
        $yaml = \Spyc::YAMLDump($values);
        file_put_contents($yamlPath, $yaml);
    }
}
