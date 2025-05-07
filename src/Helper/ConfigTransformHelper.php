<?php

declare(strict_types=1);

namespace CosmoCode\Formserver\Helper;

use CosmoCode\Formserver\FormGenerator\FormValidator;
use Michelf\MarkdownExtra;

/**
 * Config transformations
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
        $elements = self::fieldsetStyle($elements);
        $elements = self::markdown($elements, $formId);
        $elements = self::fileDispatcher($elements, $formId);
        $elements = self::filesize($elements);

        // recursively apply the transform to children
        foreach ($elements as $key => $conf) {
            if (is_array($conf)) {
                $elements[$key] = self::transform($conf, $formId);
            }
        }
        return $elements;
    }

    /**
     * Transform fieldset background config.
     * Both Bulma colors and CSS colors are allowed. Invalid definitions are ignored.
     *
     * @param array $elements
     * @return array
     */
    protected static function fieldsetStyle(array $elements): array
    {
        if (isset($elements['background'])) {
            if (in_array($elements['background'], FormValidator::COLORS_BULMA)) {
                $elements['backgroundName'] = $elements['background'];
            }
            if (preg_match(FormValidator::COLORS_REGEX, $elements['background'])) {
                $elements['backgroundNumber'] = $elements['background'];
            }
            unset($elements['background']);
        }
        return $elements;
    }

    /**
     * Prepends download url to files if needed
     *
     * @param array $elements
     * @param string $formId
     * @return array
     */
    protected static function fileDispatcher(array $elements, string $formId): array
    {
        if (isset($elements['href'])) {
            $elements['href'] = self::prepareFilePath($elements['href'], $formId);
        }
        if (isset($elements['src'])) {
            $elements['src'] = self::prepareFilePath($elements['src'], $formId);
        }
        return $elements;
    }

    protected static function prepareFilePath($file, $formId)
    {
        if (str_starts_with($file, 'http') || str_starts_with($file, '/')) {
            return $file;
        }
        return '/download/' . $formId . '?file=' . $file;
    }

    /**
     * @param array $elements
     * @param string $formId
     * @return array
     */
    protected static function markdown(array $elements, string $formId): array
    {
        if (isset($elements['markdown'])) {
            $elements['markdown'] = MarkdownExtra::defaultTransform($elements['markdown']);
        }

        if (isset($elements['modal'])) {
            $elements['modal'] = MarkdownExtra::defaultTransform($elements['modal']);
            $elements['modal'] = str_replace(
                '<img src="',
                '<img src="/download/' . $formId . '?file=',
                $elements['modal']
            );
        }
        if (isset($elements['choices'])) {
            // Markdown lib always wraps the content in a <p>...</p>
            // https://github.com/michelf/php-markdown/issues/230
            $elements['transformed_choices'] = array_map(
                fn($choice) => trim(str_replace(['<p>', '</p>'], '', MarkdownExtra::defaultTransform($choice))),
                $elements['choices']
            );
        }

        return $elements;
    }

    protected static function filesize(array $elements)
    {
        if (isset($elements['filesize'])) {
            $elements['filesize'] = FileHelper::humanToBytes($elements['filesize']);
        }

        return $elements;
    }
}
