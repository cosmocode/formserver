<?php

namespace CosmoCode\Formserver\Service;

use CosmoCode\Formserver\Helper\YamlHelper;

/**
 * Provides access to language strings
 */
class LangManager
{
    const LANG_FILE_PATH = __DIR__ . '/../../conf/language.default.yaml';
    /**
     * Language strings.
     * All defaults from conf/language.default.yaml can be overridden
     * in conf/language.local.yaml
     *
     * @var array
     */
    protected static $translations;

    /**
     * Returns a language string for a given string id
     *
     * @param string $id
     * @return string
     */
    public static function getString($id)
    {
        if (! self::$translations) {
            self::$translations = YamlHelper::parseYaml(self::LANG_FILE_PATH);
            // allow overriding of app defaults
            $localFile = __DIR__ . '/../../conf/language.local.yaml';
            if (is_file($localFile)) {
                self::$translations = array_replace_recursive(
                    self::$translations,
                    YamlHelper::parseYaml($localFile)
                );
            }
        }

        return self::$translations[$id] ?? '';
    }
}
