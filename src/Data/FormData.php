<?php

declare(strict_types=1);

namespace CosmoCode\Formserver\Data;

/**
 * Combination of config and values
 */
class FormData
{
    /**
     * Combine form data and config into a new array
     * with keys consisting of full field paths, and type + label in addition to value.
     * Used in mailer.
     *
     * @param array $data
     * @param array $config
     * @param string $parentPath
     * @return array
     */
    public static function getFormData(array $data, array $config, string $parentPath = ''): array
    {
        $formData = [];

        foreach ($data as $key => $value) {
            $currentPath = $parentPath ? $parentPath . '.' . $key : $key;

            // find corresponding config
            $pathParts = explode('.', $currentPath);
            $currentConfig = $config;
            foreach ($pathParts as $part) {
                if (isset($currentConfig[$part])) {
                    $currentConfig = $currentConfig[$part];
                } elseif (isset($currentConfig['children'][$part])) {
                    $currentConfig = $currentConfig['children'][$part];
                } else {
                    $currentConfig = null;
                    break;
                }
            }

            if ($currentConfig) {
                $formData[$currentPath] = [
                    'type' => $currentConfig['type'] ?? null,
                    'label' => $currentConfig['label'] ?? null,
                    'value' => $value
                ];
            }

            if (is_array($value)) {
                $formData = array_merge($formData, self::getFormData($value, $config, $currentPath));
            }
        }

        return $formData;
    }
}
