<?php
declare(strict_types=1);

use CosmoCode\Formserver\Helper\YamlHelper;
use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
    // Global Settings Object
    $settings = YamlHelper::parseYaml(__DIR__ . '/../conf/settings.default.yaml');

    // merge local app settings
    $localSettings = __DIR__ . '/../conf/settings.local.yaml';
    if (is_file($localSettings)) {
        $settings = array_replace_recursive($settings, YamlHelper::parseYaml($localSettings));
    }

    // finally merge from environment
    if (!empty($_ENV['DATA_DIR'])) {
        $settings['settings']['dataDir'] = $_ENV['DATA_DIR'];
    }

    $containerBuilder->addDefinitions($settings);
};
