<?php

namespace CosmoCode\Formserver\Service;

use CosmoCode\Formserver\Helper\YamlHelper;

/**
 * Provides access to language strings
 */
class LangManager
{
    const LANG_FILE_PATH_GLOBAL = __DIR__ . '/../../conf/language.default.yaml';
    const LANG_FILE_PATH_LOCAL = __DIR__ . '/../../conf/language.local.yaml';
    
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
            self::$translations = YamlHelper::parseYaml(self::LANG_FILE_PATH_GLOBAL);
            // allow overriding of app defaults
            $localFile = self::LANG_FILE_PATH_LOCAL;
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
