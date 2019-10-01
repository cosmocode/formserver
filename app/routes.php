<?php
declare(strict_types=1);

use CosmoCode\Formserver\Actions\DownloadAction;
use CosmoCode\Formserver\Actions\FormAction;
use Slim\App;

return function (App $app) {
    $app->get('/download/{id}', DownloadAction::class);
    $app->any('/{id}', FormAction::class);
    $app->get('/', FormAction::class);
};
