<?php

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
        $config = \Spyc::YAMLLoad($yamlPath);
        //TODO catch yaml parse exceptions
        if (empty($config)) {
            throw new YamlException(
                "Could not parse config.yaml in directory: '$yamlPath'"
            );
        }

        return $config;
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
