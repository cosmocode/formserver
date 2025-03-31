<?php

namespace CosmoCode\Formserver\Helper;

/**
 * Legacy config transformations for toggle -> visible, tablestyle -> table, conditional_chioces
 *
 * @package CosmoCode\Formserver\Helper
 */
class LegacyHelper
{
    /**
     * @FIXME can we quickly check if transformations are necessary?
     * @FIXME create separate functions for toggle, tablestyle and conditional options
     * @param array $elements
     * @return array
     */
    public static function transform(array $elements): array
    {
        if (isset($elements['toggle'])) {
            $newElements = [];
            foreach ($elements as $key => $value) {
                if ($key === 'toggle') {
                    $escapedValue = "'" . str_replace("'", "\\'", $elements['toggle']['value']) . "'"; // Escape internal quotes
                    $newElements['visible'] = $elements['toggle']['field'] . ' == ' . $escapedValue;
                } else {
                    $newElements[$key] = $value;
                }
            }
            $elements = $newElements;
        }

        // Recursively apply the transform to the children of the array.
        foreach ($elements as $key => $value) {
            if (is_array($value)) {
                $elements[$key] = self::transform($value);
            }
        }
        return $elements;
    }
}
