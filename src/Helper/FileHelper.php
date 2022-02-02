<?php

namespace CosmoCode\Formserver\Helper;

use Slim\Psr7\UploadedFile;

/**
 * Helper class for file operations
 */
class FileHelper
{

    /**
     * Get file extension from file path
     *
     * @param string $filePath
     * @return string
     */
    public static function getFileExtension(string $filePath)
    {
        return strtolower(
            pathinfo(
                $filePath,
                PATHINFO_EXTENSION
            )
        );
    }

    /**
     * Returns a proper directory path
     *
     * @param string $dirPath
     * @return string
     */
    public static function sanitizeDirectoryPath($dirPath)
    {
        return self::endsWith($dirPath, '/')
            ? $dirPath
            : $dirPath . '/';
    }

    /**
     * Helper function to determine if string ends with given substring
     *
     * @param string $string
     * @param string $end
     * @return bool
     */
    protected static function endsWith(string $string, string $end)
    {
        $length = strlen($end);

        return substr($string, -$length) === $end;
    }

    /**
     * Returns true if both new and old upload data are found and are error free
     *
     * @param UploadedFile[] $newUpload
     * @param array $previousUpload
     * @return bool
     */
    public static function isReupload(array $newUpload, array $previousUpload)
    {
        if (empty($previousUpload)) {
            return false;
        }

        // true if there are actual uploads, all error free
        return array_reduce(
            $newUpload,
            function ($carry, $file) {
                /** @var UploadedFile $file */
                $ok = $file->getError() === UPLOAD_ERR_OK;
                return $carry && $ok;
            },
            true
        );
    }
}
