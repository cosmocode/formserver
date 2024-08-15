<?php

namespace CosmoCode\Formserver\Actions;

use CosmoCode\Formserver\Helper\FileHelper;
use DI\NotFoundException;
use Mimey\MimeTypes;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Stream;

/**
 * Action to download files from form data directory
 */
class DownloadAction extends AbstractAction
{
    public const DATA_DIRECTORY = __DIR__ . '/../../data/';

    /**
     * Action to download file
     *
     * @return Response
     * @throws NotFoundException
     * @throws \Slim\Exception\HttpBadRequestException
     */
    protected function action(): Response
    {
        $file = $this->escapePath(
            $this->request->getQueryParams()['file'] ?? ''
        );

        $directory = $this->escapePath(
            $this->resolveArg('id')
        );
        $filePath = self::DATA_DIRECTORY . $directory . '/' . $file;

        if (file_exists($filePath)) {
            $mimes = new MimeTypes();
            $extension = FileHelper::getFileExtension($filePath);
            $mimeType = $mimes->getMimeType($extension);

            $file = fopen($filePath, 'rb');
            $fileStream = new Stream($file);
            $fileSize = filesize($filePath);

            return $this->response
                ->withHeader('Content-Type', $mimeType)
                ->withHeader('Content-Length', $fileSize)
                ->withBody($fileStream);
        }

        return $this->response->withStatus(404);
    }

    /**
     * Escape malicious characters like '/'
     *
     * @param string $filename
     * @return string|string[]|null
     */
    protected function escapePath(string $filename)
    {
        return preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $filename);
    }
}
