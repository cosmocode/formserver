<?php

namespace CosmoCode\Formserver\Helper;


use CosmoCode\Formserver\Exceptions\YamlException;

class YamlHelper
{
	/**
	 * @param $yamlPath
	 * @return mixed
	 * @throws YamlException
	 */
	public static function parseYaml($yamlPath) {
		$config = \Spyc::YAMLLoad($yamlPath);
		//TODO catch yaml parse exceptions
		if (empty($config)) {
			throw new YamlException("Could not parse config.yaml in directory: '$yamlPath'");
		}

		return $config;
	}

	public static function persistYaml(array $values, string $yamlPath)
	{
		$yaml = \Spyc::YAMLDump($values);
		file_put_contents($yamlPath, $yaml);
	}
}