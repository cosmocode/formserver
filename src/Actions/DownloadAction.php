<?php

namespace CosmoCode\Formserver\Actions;


use DI\NotFoundException;
use Mimey\MimeTypes;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Stream;

/**
 * Action to download files from form data directory
 */
class DownloadAction extends AbstractAction
{
    const DATA_DIRECTORY = __DIR__ . '/../../data/';

    /**
     * Action to download file
     *
     * @return Response
     * @throws NotFoundException
     * @throws \Slim\Exception\HttpBadRequestException
     */
    protected function action(): Response
    {
        $file = $this->request->getQueryParams()['file'] ?? null;
        $directory = $this->resolveArg('id');
        $filePath = self::DATA_DIRECTORY . $directory . '/' . $file;

        if (! file_exists($filePath)) {
            throw new NotFoundException();
        }

        $mimes = new MimeTypes();
        $extension = strtolower(
            pathinfo(
                $filePath,
                PATHINFO_EXTENSION
            )
        );
        $mimeType = $mimes->getMimeType($extension);

        $file = fopen($filePath, 'rb');
        $fileStream = new Stream($file);
        $fileSize = filesize($filePath);

        return $this->response
            ->withHeader('Content-Type', $mimeType)
            ->withHeader('Content-Length', $fileSize)
            ->withBody($fileStream);
    }
}
