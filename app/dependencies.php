<?php
declare(strict_types=1);

use CosmoCode\Formserver\Service\FileExporter;
use CosmoCode\Formserver\Service\Mailer;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        Mailer::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');
            $mailSettings = $settings['email'] ?? [];

            return new Mailer($mailSettings);
        }
    ]);

    $containerBuilder->addDefinitions([
        FileExporter::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');
            $mailSettings = $settings['fileExporter'] ?? [];

            return new FileExporter($mailSettings);
        }
    ]);
};
