<?php

namespace CosmoCode\Formserver\Helper;

use Michelf\MarkdownExtra;


/**
 * Legacy config transformations for toggle -> visible, tablestyle -> table, conditional_choices.
 * Field name conversion minus -> undescore
 * Markdown parsing
 * FIXME Image and download paths?
 *
 * @package CosmoCode\Formserver\Helper
 */
class ConfigTransformHelper
{
    /**
     * Apply transformations to config array
     *
     * @param array $elements
     * @param string $formId
     * @return array
     */
    public static function transform(array $elements, string $formId = ''): array
    {
        // rewrite field names first
        $elements = self::minus($elements);
        $elements = self::toggle($elements);
        $elements = self::options($elements);
        $elements = self::tablestyle($elements);
        $elements = self::markdown($elements, $formId);

        // recursively apply the transform to children
        foreach ($elements as $key => $conf) {
            if (is_array($conf)) {
                $elements[$key] = self::transform($conf, $formId);
            }
        }
        return $elements;
    }

    /**
     * @param array|string $element
     * @return string
     */
    protected static function getOperator(array|string $element): string
    {
        return is_array($element['value']) ? ' in ' : ' == ';
    }

    /**
     * @param array|string $value
     * @return string
     */
    protected static function getRightOperand(array|string $value): string
    {
        if (is_array($value)) {
            $val = array_map(static fn($item) => "'$item'", $value);
            $operand = '[' . implode(', ', $val) . ']';
        } else {
            $operand = "'" . str_replace("'", "\\'", $value) . "'";
        }
        return $operand;
    }

    /**
     * Rewrite fieldset toggles
     * from toggle => [field, value] to visible => field == / in value
     *
     * @param array $elements
     * @return array
     */
    protected static function toggle(array $elements): array
    {
        if (isset($elements['toggle'])) {
            $transformed = [];
            // we want to keep the keys and the order
            foreach ($elements as $key => $conf) {
                if ($key === 'toggle') {
                    $transformed['visible'] = $conf['field'] . self::getOperator($conf) . self::getRightOperand($conf['value']);
                } else {
                    $transformed[$key] = $conf;
                }
            }
            $elements = $transformed;
        }

        return $elements;
    }

    /**
     * Rewrite conditional options
     * from [field, value] to visible => field == / in value
     *
     * @param array $elements
     * @return array
     */
    protected static function options(array $elements): array
    {
        if (isset($elements['conditional_choices'])) {
            $transformed = [];
            foreach ($elements['conditional_choices'] as $key => $conf) {
                $transformed['choices'] = $conf['choices'];
                if (isset($conf['field']) && isset($conf['value'])) {
                    $transformed['visible'] = $conf['field'] . self::getOperator($conf) . self::getRightOperand($conf['value']);
                }
                $elements['conditional_choices'][$key] = $transformed;
            }
        }

        return $elements;
    }

    /**
     * Transform "tablestyle" fieldsets into table elements
     *
     * @FIXME implement
     * @param array $elements
     * @return array
     */
    protected static function tablestyle(array $elements): array
    {
        return $elements;
    }

    /**
     * @param array $elements
     * @return array
     */
    protected static function markdown(array $elements, string $formId): array
    {
        if (isset($elements['markdown'])) {
            $elements['markdown'] = MarkdownExtra::defaultTransform($elements['markdown']);
        }

        if (isset($elements['modal'])) {
            $elements['modal'] = MarkdownExtra::defaultTransform($elements['modal']);
            $elements['modal'] = str_replace('<img src="', '<img src="/download/' . $formId . '?file=', $elements['modal']);
        }

        return $elements;
    }

    /**
     * Replace minus with underscore in fieldnames,
     * otherwise they will be split by the expression parser
     *
     * @param array $elements
     * @return array
     */
    protected static function minus(array $elements): array
    {
        $transformed = [];

        foreach ($elements as $key => $element) {
            $newKey = str_replace('-', '_', $key);

            if (is_array($element)) {
                if (isset($element['field'])) {
                    $element['field'] = str_replace('-', '_', $element['field']);
                }
                $newElement = self::minus($element);
            } else {
                $newElement = $element;
            }

            $transformed[$newKey] = $newElement;
        }

        return $transformed;
    }
}
