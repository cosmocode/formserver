<?php

namespace CosmoCode\Formserver\Service;

use CosmoCode\Formserver\Exceptions\FormException;
use CosmoCode\Formserver\Exceptions\YamlException;
use CosmoCode\Formserver\Helper\FileHelper;

/**
 * Provides access to language strings
 */
class FileExporter
{
    const ROOT_DIR = __DIR__ . '/../../';

    /**
     * @var string
     */
    protected $exportDir;

    /**
     * Pass mail configuration
     *
     * @param array $exportConfiguration
     * @throws \RuntimeException
     */
    public function __construct(array $exportConfiguration)
    {
        if (! isset($exportConfiguration['dir'])) {
            throw new YamlException(
                'Export directory \'dir\' not set.'
            );
        }

        $this->exportDir = FileHelper::sanitizeDirectoryPath(
            self::ROOT_DIR . $exportConfiguration['dir'] ?? ''
        );
    }

    /**
     * Move a file to configured export dir with $newFilename
     *
     * @param string $filePath
     * @param string $newFilename
     * @return void
     * @throws \RuntimeException
     */
    public function moveFile(string $filePath, string $newFilename)
    {

        if (! is_file($filePath)) {
            throw new FormException(
                "Could not move file '$filePath'. File does not exist"
            );
        }

        if (! mkdir($this->exportDir, 0755, true) && ! is_dir($this->exportDir)) {
            throw new FormException(
                'Could not create export directory ' . $this->exportDir
            );
        }

        $fileExtension = FileHelper::getFileExtension($filePath);

        copy($filePath, $this->exportDir . $newFilename . '.' . $fileExtension);
    }
}
