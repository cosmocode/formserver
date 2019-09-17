<?php
declare(strict_types=1);

use CosmoCode\Formserver\Helper\YamlHelper;
use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {
    // Global Settings Object
    $settings = array_merge_recursive(
        YamlHelper::parseYaml(__DIR__ . '/../conf/settings.default.yaml'),
        // TODO Put that in settings.default.yaml?
        [
            'settings' => [
                'logger' => [
                    'name' => 'formserver',
                    'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
                    'level' => Logger::DEBUG,
                ],
            ],
        ]
    );

    // merge local app settings
    $localSettings = __DIR__ . '/../conf/settings.local.yaml';
    if(is_file($localSettings)) {
        $settings = array_replace_recursive($settings, YamlHelper::parseYaml($localSettings));
    }

    $containerBuilder->addDefinitions($settings);
};
