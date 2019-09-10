<?php

namespace CosmoCode\Formserver\Actions;


use DI\NotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Stream;

class DownloadAction extends AbstractAction
{
    const DATA_DIRECTORY = __DIR__ . '/../../data/';

    /**
     * Main action method
     *
     * @return Response
     */
    protected function action(): Response
    {
        $file = $this->request->getQueryParams()['file'] ?? null;
        $directory = $this->resolveArg('id');
        $filePath = self::DATA_DIRECTORY . $directory . '/' . $file;

        if (!file_exists($filePath)) {
            throw new NotFoundException();
        }

        $mimeType = mime_content_type($filePath);
        $file = fopen($filePath, 'rb');
        $fileStream = new Stream($file);
        $fileSize = filesize($filePath);

        return $this->response
            ->withHeader('Content-Type', $mimeType)
            ->withHeader('Content-Length', $fileSize)
            ->withBody($fileStream);
    }
}