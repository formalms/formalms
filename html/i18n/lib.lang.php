<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

/**
 * Return the current charset of the page.
 *
 * @return string the charset
 *
 * @deprecated
 */
function getUnicode()
{
    return Lang::charset();
}

/**
 * Return the current language.
 *
 * @return string
 *
 * @deprecated
 */
function getLanguage()
{
    return Lang::get();
}

/**
 * Return the default language setted.
 *
 * @return string
 *
 * @deprecated
 */
function getDefaultLanguage()
{
    return Lang::getDefault();
}

/**
 * Set the current language.
 *
 * @param string $lang_code the language that need to be setted
 *
 * @return string the language setted
 *
 * @deprecated
 */
function setLanguage($lang_code)
{
    return Lang::set($lang_code);
}

/**
 * This class is a temporary abstractor for the old lang module.
 *
 * @deprecated
 */
class DoceboLanguage
{
    protected static $istances = [];

    public $module = '';

    private function __construct($module)
    {
        $this->module = $module;
    }

    public static function createInstance($module = false, $platform = false, $lang_code = false)
    {
        Lang::init($module);
        if (!isset(self::$istances[$module])) {
            self::$istances[$module] = new DoceboLanguage($module);
        }

        return self::$istances[$module];
    }

    public function def($key, $module = false, $platform = false, $lang_code = false)
    {
        return Lang::t($key, ($module ? $module : $this->module), false, $lang_code);
    }

    public function getLangText($key, $module = false, $platform = false, $lang_code = false)
    {
        return Lang::t($key, ($module ? $module : $this->module), false, $lang_code);
    }

    public function isDef($key, $module = false, $platform = false, $lang_code = false)
    {
        return Lang::isDef($key, ($module ? $module : $this->module), $lang_code);
    }

    public function setGlobal()
    {
    }
}

/**
 * This is the new static class that will manage the language translations.
 */
class Lang
{
    /**
     * Translations loaded.
     *
     * @var array
     */
    public static $translations = [];

    /**
     * Current lang_code for the translation.
     *
     * @var string
     */
    public static $lang_code = false;

    /**
     * The language Model.
     *
     * @var LangAdm
     */
    protected static $_lang = false;

    /**
     * The loaded languages cache.
     *
     * @var LangAdm
     */
    protected static $_lang_cache = [];

    /**
     * Current working module (will be used as default).
     *
     * @var string
     */
    protected static $_module = 'standard';

    /**
     * A list of alredy loaded module translation.
     *
     * @var array
     */
    protected static $_loaded_modules = [''];

    /**
     * Class construnctor, unused because this is a static class.
     */
    private function __construct()
    {
    }

    /**
     * Initialize the static information.
     *
     * @param string $module   module to load
     * @param bool   $override override default module
     */
    public static function init($module, $override = true, $lang_code = false)
    {
        if ($override) {
            self::$_module = $module;
        }
        self::lang_code(Lang::get());
        self::load_module($module, $lang_code);
    }

    /**
     * Set the lang_code.
     *
     * @param string $lang_code the lang code
     *
     * @return string
     */
    public static function lang_code($lang_code = false)
    {
        if ($lang_code != false) {
            self::$lang_code = $lang_code;
        }
        if (!self::$lang_code) {
            self::$lang_code = getLanguage();
        }

        return self::$lang_code;
    }

    /**
     * Load the module translations.
     *
     * @param string $module    the module to load
     * @param string $lang_code the lang code
     */
    public static function load_module($module, $lang_code = false, $includeDisabledPlugins = false)
    {
        if (!$lang_code) {
            $lang_code = self::lang_code();
        }
        //$lang_code = self::lang_code($lang_code);
        if (isset(self::$_loaded_modules[$lang_code][$module]) && $includeDisabledPlugins === false) {
            return true;
        } else {
            self::$_loaded_modules[$lang_code][$module] = $module;
        }

        // load module translations
        if (!self::$_lang) {
            self::$_lang = new LangAdm();
        }
        self::$translations[$lang_code][$module] = self::$_lang->getTranslation($module, $lang_code, $includeDisabledPlugins);
    }

    /**
     * Return the status of a translation.
     *
     * @param string $key       the language key
     * @param string $module    the mdoule (if false, the last one will be used)
     * @param string $lang_code the lang_code (if false the last one will be used)
     *
     * @return bool true if a translation is defined, false otherwise
     */
    public static function isDef($key, $module = false, $lang_code = false)
    {
        if (self::$_lang == false) {
            self::init('standard');
        }
        if (!$module) {
            $module = self::$_module;
        }
        if (!$lang_code) {
            $lang_code = self::lang_code();
        }
        //$lang_code = self::lang_code($lang_code);

        $translation = '';
        if (isset(self::$translations[$lang_code][$module][$key])) {
            // translation found
            return true;
        } elseif (isset(self::$translations[$lang_code][$module][$key])) {
            //translation found in the standard module
            return true;
        }

        return false;
    }

    /**
     * @param string $key          the language key
     * @param string $module       the mdoule (if false, the last one will be used)
     * @param array  $substitution an array of key => value of substitution that you need inside the translation
     * @param string $lang_code    the lang_code (if false the last one will be used)
     * @param string $lang_code    the lang_code (if false the last one will be used)
     * @param string $default      the default value if a translation is not found
     *
     * @return string
     */
    public static function t($key, $module = false, $substitution = [], $lang_code = false, $default = false, $includeDisabledPlugins = false)
    {
        if ($key == '') {
            return '';
        }
        if (self::$_lang == false) {
            self::init('standard');
        }
        if (!$module) {
            $module = self::$_module;
        }
        if (!$lang_code) {
            $lang_code = self::lang_code();
        }
        //$lang_code = self::lang_code($lang_code);
        self::load_module($module, $lang_code, $includeDisabledPlugins);

        $translation = '';
        if (FormaLms\lib\Get::cfg('log_missing_translation_level', (int) 0) > 0) {
            // LOG MISSING TRANSLATIONS -->
            $missing = false;
            $missing_in_module = false;
            $found_in_standard = 0;
            // <-- LOG MISSING TRANSLATIONS
        }
        if (isset(self::$translations[$lang_code][$module][$key])) {
            // translation found
            $translation = self::$translations[$lang_code][$module][$key];
        } elseif (!isset(self::$translations[$lang_code]['standard'])) {
            //stadnard module not loaded
            self::load_module('standard', $lang_code);
        } elseif (isset(self::$translations[$lang_code]['standard'][$key])) {
            //translation found in the standard module
            $translation = self::$translations[$lang_code]['standard'][$key];
            if (FormaLms\lib\Get::cfg('log_missing_translation_level', (int) 0) > 1) {
                // LOG MISSING TRANSLATIONS -->
                if ($module !== 'standard') {
                    $missing_in_module = true;
                    $found_in_standard = 1;
                }
                // <-- LOG MISSING TRANSLATIONS
            }
        } elseif ($default == false) {
            //translation not found
            self::undefinedKey($key, $module, $lang_code);
            if (FormaLms\lib\Get::cfg('log_missing_translation_level', (int) 0) > 0) {
                // LOG MISSING TRANSLATIONS -->
                $missing = true;
                // <-- LOG MISSING TRANSLATIONS
            }
        }

        if (FormaLms\lib\Get::cfg('log_missing_translation_level', (int) 0) == 1) {
            // LOG MISSING TRANSLATIONS -->
            if ($missing) {
                $_substitutions = json_encode($substitution ? $substitution : []);
                $log = "KEY: '$key'\t- MODULE: '$module'\t- SUBTITUTIONS: $_substitutions\t \n";
                $date = date('Y_m_d');
                mkdir(_files_ . "/log/missing_translations/$lang_code/");
                file_put_contents(_files_ . "/log/missing_translations/$lang_code/$date.log", $log, FILE_APPEND);
            }
            // <-- LOG MISSING TRANSLATIONS
        } elseif (FormaLms\lib\Get::cfg('log_missing_translation_level', (int) 0) == 2) {
            // LOG MISSING TRANSLATIONS -->
            if ($missing or $missing_in_module) {
                $_substitutions = json_encode($substitution ? $substitution : []);
                $log = "KEY: '$key'\t- MODULE: '$module'\t- SUBTITUTIONS: $_substitutions\t- FOUND IN STANDARD: $found_in_standard\n";
                $date = date('Y_m_d');
                mkdir(_files_ . "/log/missing_translations/$lang_code/");
                file_put_contents(_files_ . "/log/missing_translations/$lang_code/$date.log", $log, FILE_APPEND);
            }
            // <-- LOG MISSING TRANSLATIONS
        }

        if (trim($translation) == '') {
            if ($default != false) {
                $translation = $default;
            } else {
                $translation = (FormaLms\lib\Get::sett('lang_check') == 'on' ? "§($module)" : '') . trim(strtolower(str_replace('_', ' ', $key)));
            }
        }
        if (empty($substitution) || !is_array($substitution)) {
            return $translation;
        }

        return str_replace(array_keys($substitution), array_values($substitution), $translation);
    }

    /**
     * This method will be used by the t() method when a translation is not found.
     *
     * @param string $key       the language key
     * @param string $module    the mdoule (if false, the last one will be used)
     * @param string $lang_code the lang_code (if false the last one will be used)
     */
    public static function undefinedKey($key, $module, $lang_code)
    {
        if (FormaLms\lib\Get::sett('lang_check') == 'on') {
            $text = '<a id="totranslate-' . $module . '-' . $key . '" href="#">'
                . '' . $module . ' : ' . strtolower($key) . ' </a><br/>';
            if (isset($GLOBALS['page'])) {
                $GLOBALS['page']->add($text, 'def_lang');
            }
        }
    }

    /**
     * Return the current charset of the page.
     *
     * @return string the charset
     */
    public static function charset()
    {
        return 'utf-8';
    }

    /**
     * Return the current language, the following policy is followed
     * if a session preference is found, that one will be used, otherwise :
     * if the user is logged in we find and setup it's default language
     * if the user is anonymous we try to select the languagfe using the browser list of language preferences
     * if all of the previous fail we will use the default setted language.
     *
     * @return string
     */
    public static function get($reset = false)
    {
        $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
        $currentLang = $session->get('current_lang');
        if ($reset && isset($currentLang)) {
            $currentLang = null;
        }

        if (!$currentLang) {
            $currentLang = self::getDefault();
            // we if (!FormaLms\lib\Get::cfg('demo_mode', false) && !Docebo::user()->isAnonymous()) {don't know which language we need
            if (!FormaLms\lib\Get::cfg('demo_mode', false) && !Docebo::user()->isAnonymous()) {
                // load the language from the user setting
                $currentLang = Docebo::user()->preference->getLanguage();
            } else {
                // find the user language looking into the browser info
                $langadm = new LangAdm();
                $all_language = $langadm->getLangListNoStat();
                $browser_lang = FormaLms\lib\Get::user_acceptlang(false);
                foreach ($browser_lang as $code) {
                    foreach ($all_language as $lang) {
                        if ($code) {
                            if (strpos($lang->lang_browsercode, (string) $code) !== false) {
                                $currentLang = $lang->lang_code;
                                break 2;
                            }
                        }
                    } // end foreach
                } // end foreach
            }
        }

        $session->set('current_lang', $currentLang);
        $session->save();

        return $currentLang;
    }

    /**
     * Set the current language.
     *
     * @param string $lang_code the language that need to be setted
     *
     * @return string the language setted
     */
    public static function set($lang_code, $force = true)
    {
        // check lang_code:
        $langadm = new LangAdm();
        $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
        $all_language = $langadm->getLangListNoStat();

        if (!isset($all_language[$lang_code])) {
            return false;
        }

        if (Docebo::user()->isAnonymous()) {
            // if the user is anonymous we will remember it's forced selection and set up the selected language as
            // it's user preference when he login
            if ($force) {
                $session->set('forced_lang', true);
            }
        } else {
            Docebo::user()->preference->setLanguage($lang_code);
        }
        $session->set('current_lang', $lang_code);
        $session->save();

        return $lang_code;
    }

    /**
     * Return the default language setted.
     *
     * @return string
     */
    public static function getDefault()
    {
        return FormaLms\lib\Get::sett('default_language', 'english');
    }

    public static function direction($lang_code = false)
    {
        if (!$lang_code) {
            $lang_code = self::get();
        }
        if (isset(self::$_lang_cache[$lang_code])) {
            return self::$_lang_cache[$lang_code]->lang_direction;
        }

        if (!self::$_lang) {
            self::$_lang = new LangAdm();
        }
        self::$_lang_cache[$lang_code] = self::$_lang->getLanguage($lang_code);

        return self::$_lang_cache[$lang_code]->lang_direction;
    }
}

/**
 * This class is a "de-facto" model for the language db, but the "effective model" will be the LangAdm class.
 */
class DoceboLangManager
{
    public $globTranslation = null;
    public $globLangModule = null;
    /** prefix for the database */
    public $prefix;

    protected static $instance = false;

    /**
     * @static
     * This function encapsulate the computation of a cross module key from
     *    a key and a module
     *
     * @param string $key1 the first key
     * @param string $key2 the second key
     * @param mixed  $key3 Optional. The third key or FALSE
     *
     * @return string composed key
     **/
    public static function composeKey($key1, $key2, $key3 = false)
    {
        return $key2 . '&' . $key1;
    }

    /**
     * @static
     * This function encapsulate the computation of a cross module key from
     *    a key and a module
     *
     * @param string $composed_key the module key composed
     *
     * @return array array with 0=>key1 1=>key2 2=>key3
     **/
    public function decomposeKey($composed_key)
    {
        $composed_key = str_replace('&amp;', '&', $composed_key);

        return array_reverse(explode('&', $composed_key, 2));
    }

    public function _getTableText()
    {
        return $this->prefix . '_lang_text';
    }

    public function _getTableTranslation()
    {
        return $this->prefix . '_lang_translation';
    }

    public function _getTableLanguage()
    {
        return $this->prefix . '_lang_language';
    }

    public function _executeInsert($query)
    {
        if (!sql_query($query)) {
            return false;
        }

        return sql_insert_id();
    }

    /**
     * DoceboLangManager constructor.
     *
     * @param string   $param_prefix the prefix for the tables names
     *                               if not given global $prefix variable is used
     * @param resource $dbconn       the connection to the database
     *                               if not given last connection will be used
     */
    private function __construct($param_prefix = false, $dbconn = null)
    {
        if ($param_prefix === false) {
            $this->prefix = FormaLms\lib\Get::cfg('prefix_fw');
        } else {
            $this->prefix = $param_prefix;
        }
    }

    public static function getInstance()
    {
        if (self::$instance == false) {
            self::$instance = new DoceboLangManager();
        }

        return self::$instance;
    }

    /**
     * return an array with all modules in translations table.
     *
     * @param string $platform
     *
     * @return array array with all modules
     */
    public function getAllModules($platform = false)
    {
        if ($platform === false) {
            $platform = FormaLms\lib\Get::cur_plat();
        }
        $query = 'SELECT text_module'
            . ' FROM ' . $this->_getTableText()
            . ' WHERE 1'
            . ' GROUP BY text_module';
        $rs = sql_query($query);
        $result = [];
        while (list($text_module) = sql_fetch_row($rs)) {
            $result[] = $text_module;
        }

        return $result;
    }

    /**
     * return an array with all modules loaded.
     *
     * @return array with all modules loaded
     */
    public function getLoadedModules()
    {
        global $globLangModule;

        return array_keys($globLangModule);
    }

    /**
     * return an array with all lang_code loaded for a given module.
     *
     * @param string $module name of the module
     *
     * @return array with all lang_code loaded for a given module
     *               FALSE if the module is not loaded
     */
    public function getLoadedModulesLanguages($module)
    {
        global $globLangModule;
        if (isset($globLangModule[$module])) {
            return array_keys($globLangModule[$module]);
        } else {
            return false;
        }
    }

    /**
     * return the text translation for a given $lang_code, $key, $module, $platform.
     *
     * @param string $lang_code the lang code to get translation
     * @param string $key       the key to search or the composed key if $module is FALSE
     * @param mixed  $module    the module to search or FALSE if $key is composed key
     * @param mixed  $platform  the platform to search or FALSE if $key or $module are composed key
     *
     * @return mixed string with text translation or FALSE if not found
     */
    public function getLangTranslationText($lang_code, $key, $module = false, $platform = false)
    {
        if ($module === false) {
            list($key, $module, $platform) = $this->decomposeKey($key);
        } elseif ($platform === false) {
            list($module, $platform) = $this->decomposeKey($module);
        }

        $query = 'SELECT tran.translation_text'
            . '  FROM ' . $this->_getTableText() . ' AS tt'
            . '  JOIN ' . $this->_getTableTranslation() . ' AS tran'
            . ' ON (tt.id_text=tran.id_text) '
            . " WHERE tt.text_key = '" . $key . "'"
            . "   AND tt.text_module = '" . $module . "'"
            . "   AND tran.lang_code = '" . $lang_code . "'";
        $rs = sql_query($query);
        if (sql_num_rows($rs) < 1) {
            return false;
        }
        list($translation_text) = sql_fetch_row($rs);

        return $translation_text;
    }

    /**
     * return an array with all the translations for a given
     *    platform module lang_code triple.
     *
     * @param string $platform       the platform
     * @param mixed  $module         the module name
     *                               if FALSE all modules will be returned
     * @param string $lang_code      the code of the language
     * @param string $trans_contains the text contains this string
     *
     * @return array with index numeric values are arrays with
     *               - 0=>module,
     *               - 1=>key,
     *               - 2=>translation or NULL if don't exist translation
     *               - 3=>attributes
     *               for that key language pair
     */
    public function getModuleLangTranslations($platform, $module, $lang_code, $trans_contains = '', $attributes = false, $order_by = false, $get_date = false, $text_items = null)
    {
        $db = DbConn::getInstance();
        $part = [];
        if (!empty($attributes) && is_array($attributes)) {
            foreach ($attributes as $value) {
                $part[] = " text_attributes LIKE '%" . $value . "%' ";
            }
        }
        if (!empty($text_items) && is_array($text_items)) {
            $part[] = ' tx.id_text IN (' . implode(',', $text_items) . ') ';
        }

        $query = 'SELECT tx.text_module, tx.text_key, tx.id_text, tx.text_attributes, tran.translation_text '
            . ($get_date === true ? ', tran.save_date ' : '')
            . ' FROM ' . $this->_getTableText() . ' AS tx JOIN ' . $this->_getTableTranslation() . ' AS tran '
            . '	ON (tx.id_text = tran.id_text ) '
            . " WHERE tran.lang_code = '" . $lang_code . "' "
            . ($module === false ? '' : "  AND tx.text_module = '" . $module . "'")
            . (!empty($part) ? ' AND ' . implode(' AND ', $part) . ' ' : '')
            . ($trans_contains != '' ? " AND tran.translation_text LIKE '%" . $trans_contains . "%'" : '')
            . ' ORDER BY text_module, text_key';
        $rs = $db->query($query);

        $text_result = [];
        while ($obj = $db->fetch_obj($rs)) {
            $text_result[$obj->id_text] = [$obj->text_module, $obj->text_key, $obj->translation_text, $obj->text_attributes];
            if ($get_date === true) {
                $text_result[$obj->id_text][] = $obj->save_date;
            }
        }

        return $text_result;
    }

    /**
     * return a key description.
     *
     * @param string $key      the key to search or the composed key if $module is FALSE
     * @param mixed  $module   the module to search or FALSE if $key is composed key
     * @param mixed  $platform the platform to search or FALSE if $key or $module are composed key
     *
     * @return mixed
     *               - string description for given key module platform triple
     *               - FALSE if key module platform is not found
     */
    public function getKeyDescription($key, $module = false, $platform = false)
    {
        return '';
    }

    /**
     * return the key attributes.
     *
     * @param string $key      the key to search or the composed key if $module is FALSE
     * @param mixed  $module   the module to search or FALSE if $key is composed key
     * @param mixed  $platform the platform to search or FALSE if $key or $module are composed key
     *
     * @return mixed
     *               - string attributes for given key module platform triple
     *               - FALSE if key module platform is not found
     */
    public function getKeyAttributes($key, $module = false, $platform = false)
    {
        if ($module === false) {
            list($key, $module, $platform) = $this->decomposeKey($key);
        } elseif ($platform === false) {
            list($module, $platform) = $this->decomposeKey($module);
        }

        $query = 'SELECT text_attributes FROM ' . $this->_getTableText()
            . " WHERE text_key = '" . $key . "' "
            . "   AND text_module = '" . $module . "'";
        $rs = sql_query($query);
        if (sql_num_rows($rs) == 0) {
            return false;
        }
        list($attributes) = sql_fetch_row($rs);

        return $attributes;
    }

    /**
     * delete a key and all associated translations.
     *
     * @param string $key      the key to search or the composed key if $module is FALSE
     * @param mixed  $module   the module to search or FALSE if $key is composed key
     * @param mixed  $platform the platform to search or FALSE if $key or $module are composed key
     *
     * @return bool TRUE if success, FALSE otherwise
     */
    public function deleteKey($key, $module = false, $platform = false)
    {
        if ($module === false) {
            list($key, $module, $platform) = $this->decomposeKey($key);
        } elseif ($platform === false) {
            list($module, $platform) = $this->decomposeKey($module);
        }

        $query = 'SELECT id_text FROM ' . $this->_getTableText()
            . " WHERE text_key = '" . $key . "' "
            . "   AND text_module = '" . $module . "''";
        $rs = sql_query($query);
        if (sql_num_rows($rs) == 0) {
            return false;
        }
        list($id_text) = sql_fetch_row($rs);

        $query = 'DELETE FROM ' . $this->_getTableTranslation()
            . ' WHERE id_text=' . $id_text;
        sql_query($query);

        $query = 'DELETE FROM ' . $this->_getTableText()
            . " WHERE id_text='" . $id_text . "'";
        sql_query($query);

        return true;
    }

    /**
     * update a key.
     *
     * @param string $key         the key to search or the composed key if $module is FALSE
     * @param mixed  $module      the module to search or FALSE if $key is composed key
     * @param mixed  $platform    the platform to search or FALSE if $key or $module are composed key
     * @param mixed  $description the description of the key of FALSE for skip
     * @param mixed  $attributes  the attributes of key (accessibility,sms)
     *
     * @return bool TRUE if success, FALSE otherwise
     */
    public function updateKey($key, $module = false, $platform = false, $description = false, $attributes = false, $overwrite = true, $no_add = false)
    {
        if ($module === false) {
            list($key, $module, $platform) = $this->decomposeKey($key);
        } elseif ($platform === false) {
            list($module, $platform) = $this->decomposeKey($module);
        }

        $query = 'SELECT id_text FROM ' . $this->_getTableText()
            . " WHERE text_key = '" . $key . "' "
            . "   AND text_module = '" . $module . "'";
        $rs = sql_query($query);
        if (sql_num_rows($rs) == 0) {
            if ($no_add === true) {
                return true;
            }
            $query = 'INSERT INTO ' . $this->_getTableText()
                . ' (text_key, text_module, text_platform, text_attributes ) VALUES '
                . " ('" . $key . "','" . $module . "','" . $platform . "','" . $attributes . "') ";

            return sql_query($query);
        } elseif ($description !== false) {
            if ($overwrite === true && $attributes !== false) {
                list($id_text) = sql_fetch_row($rs);
                $query = 'UPDATE ' . $this->_getTableText()
                    . " SET text_attributes  = '" . $attributes . "' ";
                $query .= " WHERE id_text = '" . $id_text . "'";

                return sql_query($query);
            }
        }

        return true;
    }

    public function updateTranslationC($composed_key, $translation, $lang_code)
    {
        list($key, $module) = $this->decomposeKey($composed_key);

        return $this->updateTranslation($key, $module, false, $translation, $lang_code);
    }

    public function updateTranslation($key, $module, $platform, $translation, $lang_code, $save_date = false)
    {
        if ($save_date === false) {
            $save_date = date('Y-m-d H:i:s');
        }
        $query = 'SELECT id_text '
            . ' FROM ' . $this->_getTableText() . ' AS text'
            . " WHERE text.text_module = '" . $module . "'"
            . "  AND text.text_key = '" . $key . "'";
        $rs = sql_query($query);
        if ($rs === false) {
            return false;
        }

        list($id_text) = sql_fetch_row($rs);

        // update save_date only if the content is changed ------------
        $query = 'UPDATE ' . $this->_getTableTranslation()
            . " SET save_date  = '" . $save_date . "' "
            . " WHERE id_text='" . $id_text . "'"
            . "		AND lang_code = '" . $lang_code . "'";
        sql_query($query);

        $query = 'UPDATE ' . $this->_getTableTranslation()
            . "   SET translation_text='" . $translation . "' "
            . " WHERE id_text='" . $id_text . "'"
            . "		AND lang_code = '" . $lang_code . "'";

        return sql_query($query);
    }

    /**
     * test for a lang_code exist.
     *
     * @param string $lang_code code of lang to test
     *
     * @return true if language exist, FALSE otherwise
     **/
    public function existLanguage($lang_code)
    {
        $query = 'SELECT lang_code'
            . ' FROM ' . $this->_getTableLanguage()
            . " WHERE lang_code='" . $lang_code . "'";
        $rs = sql_query($query);
        if (sql_num_rows($rs) !== 1) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * return an array with all the lang_codes presents on system.
     *
     * @return array with all language codes in system (index in array is numeric
     *               starting from 0, value is lang_code)
     */
    public function getAllLangCode()
    {
        $query = 'SELECT lang_code'
            . ' FROM ' . $this->_getTableLanguage();
        $rs = sql_query($query);

        $result = [];
        while (list($lang_code) = sql_fetch_row($rs)) {
            $result[] = $lang_code;
        }

        return $result;
    }

    /**
     * return an array with all the languages presents on system.
     *
     * @return array with all language codes in system (index in array is numeric
     *               starting from 0, value is an array with (0=> lang_code, 1=> description )
     *               return an empty array if no languages is present
     */
    public function getAllLanguages($keys = false)
    {
        $query = 'SELECT lang_code, lang_description, lang_direction '
            . ' FROM ' . $this->_getTableLanguage();
        $rs = sql_query($query);

        $result = [];
        while (list($lang_code, $lang_description, $lang_direction) = sql_fetch_row($rs)) {
            if ($keys) {
                $result[$lang_code] = ['code' => $lang_code, 'description' => $lang_description, 'direction' => $lang_direction];
            } else {
                $result[] = [$lang_code, $lang_description, $lang_direction];
            }
        }

        return $result;
    }

    /**
     * return language description for a given lang_code.
     *
     * @param string $lang_code
     *
     * @return string language description
     **/
    public function getLanguageDescription($lang_code)
    {
        $query = 'SELECT lang_description'
            . ' FROM ' . $this->_getTableLanguage()
            . " WHERE lang_code='" . $lang_code . "'";
        $rs = sql_query($query);
        if (sql_num_rows($rs) !== 1) {
            return false;
        }

        list($description) = sql_fetch_row($rs);

        return $description;
    }

    /**
     * return language charset for a given lang_code.
     *
     * @param string $lang_code
     *
     * @return string language charset
     **/
    public function getLanguageCharset($lang_code)
    {
        return 'utf-8';
    }

    /**
     * return language browsercode for a given lang_code.
     *
     * @param string $lang_code
     *
     * @return string language browser code
     **/
    public function getLanguageBrowsercode($lang_code)
    {
        $query = 'SELECT lang_browsercode'
            . ' FROM ' . $this->_getTableLanguage()
            . " WHERE lang_code='" . $lang_code . "'";
        $rs = sql_query($query);
        if (sql_num_rows($rs) !== 1) {
            return false;
        }

        list($lang_browsercode) = sql_fetch_row($rs);

        return $lang_browsercode;
    }

    /**
     * return language browsercode for a given lang_code.
     *
     * @param string $lang_code
     *
     * @return string language browser code
     **/
    public function getLanguageDirection($lang_code)
    {
        $query = 'SELECT lang_direction'
            . ' FROM ' . $this->_getTableLanguage()
            . " WHERE lang_code='" . $lang_code . "'";
        $rs = sql_query($query);
        if (sql_num_rows($rs) !== 1) {
            return false;
        }

        list($lang_direction) = sql_fetch_row($rs);

        return $lang_direction;
    }

    public function findLanguageFromBrowserCode()
    {
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            return getDefaultLanguage();
        }
        $accept_language = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        $al_arr = explode(',', $accept_language);

        $i = 0;
        $res = '';
        foreach ($al_arr as $value) {
            $bl_arr = explode(';', $value);
            $browser_language = $bl_arr[0];
            $browser_language = sql_escape_string(substr($browser_language, 0, 5));

            $query = 'SELECT lang_code '
                . ' FROM ' . $this->_getTableLanguage()
                . " WHERE lang_browsercode LIKE '%" . $browser_language . "%'";
            $rs = sql_query($query);
            if (sql_num_rows($rs) != 0) {
                list($lang_code) = sql_fetch_row($rs);

                return $lang_code;
            }
        }

        return getDefaultLanguage();
    }

    public function setLanguage($lang_code, $lang_description = false, $lang_charset = false, $lang_brosercode = false, $lang_direction = false)
    {
        $query = 'SELECT lang_code'
            . ' FROM ' . $this->_getTableLanguage()
            . " WHERE lang_code='" . $lang_code . "'";
        $rs = sql_query($query);
        if (sql_num_rows($rs) !== 1) {
            return $this->insertLanguage($lang_code, $lang_description, $lang_charset, $lang_brosercode, $lang_direction);
        } elseif ($lang_description !== false) {
            return $this->updateLanguage($lang_code, $lang_description, $lang_charset, $lang_brosercode, $lang_direction);
        }

        return true;
    }

    /**
     * update a lang_code.
     *
     * @param string $lang_code        code of lang to test
     * @param string $lang_description optional
     * @param string $lang_charset     optional
     * @param string $lang_brosercode  optional
     *
     * @return true if success, FALSE otherwise
     **/
    public function getLanguageInfo($lang_code)
    {
        $query = 'SELECT lang_code, lang_description, lang_browsercode, lang_direction '
            . 'FROM ' . $this->_getTableLanguage() . ' '
            . "WHERE lang_code='" . $lang_code . "'";
        $re = sql_query($query);
        if (!$re) {
            return false;
        }

        return sql_fetch_object($re);
    }

    /**
     * update a lang_code entry.
     *
     * @param string $lang_code
     * @param string $lang_description
     * @param string $lang_charset     optional
     * @param string $lang_brosercode  optional
     *
     * @return bool TRUE if success, FALSE otherwise
     **/
    public function updateLanguage($lang_code, $lang_description, $lang_charset = false, $lang_brosercode = false, $lang_direction = false)
    {
        $query = 'UPDATE ' . $this->_getTableLanguage()
            . " SET lang_description='" . $lang_description . "'"
            . (($lang_brosercode !== false) ? ", lang_browsercode='" . $lang_brosercode . "'" : '')
            . (($lang_direction !== false) ? ", lang_direction ='" . $lang_direction . "'" : '')
            . " WHERE lang_code='" . $lang_code . "'";

        return sql_query($query);
    }

    /**
     * insert a lang_code entry.
     *
     * @param string $lang_code
     * @param string $lang_description
     *
     * @return bool TRUE if success, FALSE otherwise
     **/
    public function insertLanguage($lang_code, $lang_description, $lang_charset = false, $lang_brosercode = false, $lang_direction = false)
    {
        $query = 'INSERT INTO ' . $this->_getTableLanguage()
            . ' ( lang_code, lang_description '
            . (($lang_brosercode !== false) ? ', lang_browsercode' : '')
            . (($lang_direction !== false) ? ', lang_direction' : '')
            . ' )'
            . " VALUES ('" . $lang_code . "','" . $lang_description . "'"
            . (($lang_brosercode !== false) ? ",'" . $lang_brosercode . "'" : '')
            . (($lang_direction !== false) ? ",'" . $lang_direction . "'" : '')
            . ')';

        return sql_query($query);
    }

    /**
     * delete a lang_code entry.
     *
     * @param string $lang_code
     *
     * @return bool TRUE if success, FALSE otherwise
     **/
    public function deleteLanguage($lang_code)
    {
        $control = true;

        $query = 'DELETE FROM ' . $this->_getTableLanguage()
            . " WHERE lang_code='" . $lang_code . "'";
        if (!sql_query($query)) {
            $control = false;

            return $control;
        }

        $query = ' DELETE FROM ' . $this->_getTableTranslation() . ''
            . " WHERE lang_code = '" . $lang_code . "'";
        if (!sql_query($query)) {
            $control = false;

            return $control;
        }

        return $control;
    }

    public function getLangStat()
    {
        require_once _base_ . '/lib/lib.platform.php';
        $pl_man = &PlatformManager::createInstance();
        $platform_list = array_keys($pl_man->getActivePlatformList());

        $stats = [];
        $lang_stat = ''
            . ' SELECT COUNT(*)'
            . ' FROM ' . $this->_getTableText() . ' '
            . ' WHERE 0 ';
        foreach ($platform_list as $plat) {
            $lang_stat .= " OR text_platform = '" . $plat . "' ";
        }

        list($stats['tot_lang']) = sql_fetch_row(sql_query($lang_stat));

        $lang_stat = ''
            . 'SELECT lang_code, COUNT(*) '
            . 'FROM ' . $this->_getTableTranslation() . ' '
            . "WHERE translation_text <> '' "
            . 'GROUP BY lang_code';
        $re_stat = sql_query($lang_stat);
        while (list($lc, $tot) = sql_fetch_row($re_stat)) {
            $stats[$lc] = $tot;
        }

        return $stats;
    }
}
