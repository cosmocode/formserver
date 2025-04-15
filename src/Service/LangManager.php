<?php

declare(strict_types=1);

namespace CosmoCode\Formserver\Service;

use CosmoCode\Formserver\Exceptions\LanguageException;
use CosmoCode\Formserver\Helper\YamlHelper;

/**
 * Provides access to language strings
 */
class LangManager
{
    public const LANG_FILE_PATH_GLOBAL = __DIR__ . '/../../conf/language.default.yaml';
    public const LANG_FILE_PATH_LOCAL = __DIR__ . '/../../conf/language.local.yaml';
    public const LANG_FILE_PATH_DYNAMIC = __DIR__ . '/../../conf/language.{{language}}.yaml';

    /**
     * Language strings.
     * All defaults from conf/language.default.yaml can be overridden
     * in conf/language.local.yaml
     *
     * @var array
     */
    protected static $translations;


    /**
     * Initialize LangManager - load specific language
     *
     * @param string|null $language
     * @return void
     * @throws LanguageException
     */
    public static function init(string $language = null)
    {
        if (! empty(self::$translations)) {
            throw new LanguageException('LangManager already initialized.');
        }

        $translations = YamlHelper::parseYaml(self::LANG_FILE_PATH_GLOBAL);

        if (is_file(self::LANG_FILE_PATH_LOCAL)) {
            $translations = array_replace_recursive(
                $translations,
                YamlHelper::parseYaml(self::LANG_FILE_PATH_LOCAL)
            );
        }

        if (! empty($language)) {
            $languageFile = str_replace(
                '{{language}}',
                $language,
                self::LANG_FILE_PATH_DYNAMIC
            );

            if (! is_file($languageFile)) {
                throw new LanguageException(
                    "LangManager could not load language file: '$languageFile'"
                );
            }

            $translations = array_replace_recursive(
                $translations,
                YamlHelper::parseYaml($languageFile)
            );
        }

        self::$translations = $translations;
    }

    /**
     * Returns a language string for a given string id
     *
     * @param string $id
     * @return string
     */
    public static function getString(string $id)
    {
        return self::$translations[$id] ?? '';
    }

    public static function getTranslations()
    {
        return self::$translations;
    }
}
