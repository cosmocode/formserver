<?php

namespace CosmoCode\Formserver\Helper;


use CosmoCode\Formserver\Exceptions\YamlException;

class YamlHelper
{
	/**
	 * @param $configDir
	 * @return mixed
	 * @throws YamlException
	 */
	public static function parseYaml($configDir) {
		$config = yaml_parse_file(__DIR__ . "/../../data/$configDir/config.yaml");
		if ($config === false) {
			throw new YamlException("Could not parse config.yaml in directory: '$configDir'");
		}

		return $config;
	}
}