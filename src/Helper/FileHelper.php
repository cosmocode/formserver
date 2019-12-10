<?php

namespace CosmoCode\Formserver\Helper;

use CosmoCode\Formserver\Exceptions\YamlException;

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
}
