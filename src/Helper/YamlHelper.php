<?php

namespace CosmoCode\Formserver\Helper;


class YamlHelper
{
	public static function parseYaml($configDir) {
		$config = yaml_parse_file(__DIR__ . "/../../data/$configDir/config.yaml");
		if ($config === false) {
			throw new YamlException("Could not parse config.yaml in directory: '$configDir'");
		}

		return $config;
	}
}