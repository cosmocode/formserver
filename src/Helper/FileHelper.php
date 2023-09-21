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

        return self::isValidUpload($newUpload);
    }

    /**
     * Returns true if there are actual uploads, all error free
     *
     * @param array $uploads
     * @return bool
     */
    public static function isValidUpload(array $uploads)
    {
        return array_reduce(
            $uploads,
            function ($carry, $file) {
                /** @var UploadedFile $file */
                $ok = $file->getError() === UPLOAD_ERR_OK;
                return $carry && $ok;
            },
            true
        );
    }

    /**
     * Calculate byte size from human readable format
     *
     * @param string $size
     * @return int
     */
    public static function humanToBytes($size)
    {
        $post = preg_match('/(\D*?)$/', $size, $matches);
        if ($post) {
            $size = str_replace($matches[0], '', $size);
        }

        switch (strtolower($matches[0])) {
            case 'k':
            case 'kb':
                $size *= 1024;
                break;
            case 'm':
            case 'mb':
                $size *= 1024 * 1024;
                break;
            case 'g':
            case 'gb':
                $size *= 1024 * 1024 * 1024;
                break;
        }

        return (int)$size;
    }

    /**
     * Return byte size in human readable format
     *
     * @param int $size
     * @return string
     */
    public static function getMaxSizeHuman(int $size)
    {
        if ($size >= 1024**3) {
            $fileSize = round($size / 1024 / 1024 / 1024, 1) . 'GB';
        } elseif ($size >= 1024**2) {
            $fileSize = round($size / 1024 / 1024, 1) . 'MB';
        } elseif ($size >= 1024) {
            $fileSize = round($size / 1024, 1) . 'KB';
        } else {
            $fileSize = $size . ' bytes';
        }
        return $fileSize;
    }
}
