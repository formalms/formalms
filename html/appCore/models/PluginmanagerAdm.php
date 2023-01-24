<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

class PluginmanagerAdm extends Model
{
    protected $db;
    protected $table;
    protected $plugin_core;
    public static $plugins_active;

    public function __construct()
    {
        $this->db = DbConn::getInstance();
        $this->table = '%adm_plugin';
        $this->plugin_core = [
            'FormaAuth',
        ];
        parent::__construct();
    }

    public function getPerm()
    {
        return [
            'view' => 'standard/view.png',
        ];
    }

    /**
     * Read specified plugin manifest.
     *
     * @param $plugin_name
     * @param bool $key
     *
     * @return bool|mixed
     */
    public static function readPluginManifest($plugin_name, $key = false)
    {
        $plugin_file = _plugins_ . '/' . $plugin_name . '/manifest.xml';
        if (!file_exists($plugin_file)) {
            return false;
        }
        if ($xml = simplexml_load_string(file_get_contents($plugin_file))) {
            $man_json = json_encode($xml);
            $man_array = json_decode($man_json, true);

            if (!$xml->name) {
                return false;
            } else {
                if (is_array($man_array) && $key !== false) {
                    if (array_key_exists($key, $man_array)) {
                        return $man_array[(string)$key];
                    }
                }
                return $man_array;
            }
        } else {
            return false;
        }
    }

    /**
     * Get specified plugin information giving [plugin_id | code | name].
     *
     * @param $id
     * @param string $type_id
     *
     * @return array
     */
    public function getPluginFromDB($id, $type_id = 'plugin_id')
    {
        switch ($type_id) {
            case 'plugin_id':
                $where_id = '  plugin_id = ' . FormaLms\lib\Get::filter($id, DOTY_INT);
                break;
            case 'name':
                $where_id = "  name = '" . FormaLms\lib\Get::filter($id, DOTY_MIXED) . "' ";
                break;
        }

        $query = 'SELECT * FROM ' . $this->table . ' WHERE  ' . $where_id;
        $re = $this->db->query($query);

        $row = sql_fetch_assoc($re);

        return $row;
    }

    /**
     * Compare two plugin's versions.
     *
     * @param $old
     * @param $new
     *
     * @return bool
     */
    public function isNewerVersion($old, $new)
    {
        return version_compare($old, $new) < 0;
    }

    /**
     * Check if there are online updates for specified plugin.
     *
     * @param $name
     *
     * @return bool
     */
    public function checkOnlineUpdate($name)
    {
        $info = self::readPluginManifest($name);
        if (isset($info['update'])) {
            $last_version = file_get_contents($info['update'] . '/?action=manifest&plugin=' . $name);
            $last_version_parsed = @json_decode(@json_encode(simplexml_load_string($last_version)), 1);
            if ($this->isNewerVersion($info['version'], $last_version_parsed['version'])) {
                return true;
            }
        }

        return false;
    }

    private static function scan_dir()
    {
        return array_diff(scandir(_plugins_), ['..', '.']);
    }

    private function check_dependencies($manifest, $dependence = false)
    {
        if ($dependence) {
            $manifest = self::readPluginManifest($dependence);
        }
        $forma_version = FormaLms\lib\Get::sett('core_version');
        $check['dependencies'] = [];
        $check['forma_version'] = [];
        if (array_key_exists('forma_version', $manifest)) {
            if (array_key_exists('min', $manifest['forma_version'])) {
                if (version_compare($forma_version, $manifest['forma_version']['min']) < 0) {
                    $check['forma_version'][] = ['name' => 'forma.lms', 'version' => $manifest['forma_version']['min']];
                }
            }
            if (array_key_exists('max', $manifest['forma_version'])) {
                if (version_compare($manifest['forma_version']['max'], $forma_version) < 0) {
                    $check['forma_version'][] = ['name' => 'forma.lms', 'version' => $manifest['forma_version']['max']];
                }
            }
        }

        if (array_key_exists('dependencies', $manifest)) {
            $dependencies = $manifest['dependencies'];

            $plugin_list = $this->getActivePlugins();
            if ($dependence) {
                unset($plugin_list[$dependence]);
            }
            if (isset($dependencies)) {
                foreach ($dependencies as $dependency) {
                    $name = $dependency['name'];
                    $version = $dependency['version'];
                    if (array_key_exists($name, $plugin_list)) {
                        $dependant_manifest = $this->getPluginFromDB($name, 'name');
                        $dependency['active'] = $dependant_manifest['active'];
                        if (version_compare($version, $dependant_manifest['version']) > 0 || $dependant_manifest['active']) {
                            $check['dependencies'][] = $dependency;
                            break;
                        }
                    } else {
                        $check['dependencies'][] = $dependency;

                        break;
                    }
                }
            }
        }

        return $check;
    }

    private function is_dependence($name)
    {
        $dependencies = [];
        foreach ($this->getInstalledPlugins() as $file => $content) {
            $manifest = self::readPluginManifest($file);

            $dependencies = $this->check_dependencies($manifest, $name);
        }

        return $dependencies;
    }

    public function getInstalledPlugins()
    {
        $query = 'SELECT * FROM ' . $this->table;
        $re = $this->db->query($query);
        $plugins = [];
        while ($row = sql_fetch_assoc($re)) {
            if ($row['core'] == 1) {
                if ($row['active'] == 1) {
                    $plugins[$row['name']] = $row;
                } else {
                    $plugins[$row['name']] = false;
                }
            } else {
                $plugins[$row['name']] = $row;
            }
        }
        foreach ($this->plugin_core as $core_name) {
            if (!array_key_exists($core_name, $plugins)) {
                $manifest = self::readPluginManifest($core_name);
                $plugins[$manifest['name']] = $manifest;
            }
        }

        return array_filter($plugins);
    }

    public function getActivePlugins()
    {
        $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();

        if (!isset(self::$plugins_active)) {

            if ($session && $session->has('notuse_plugin') && $session->get('notuse_plugin') === true) {
                $query = 'SELECT * FROM ' . $this->table . ' WHERE core=1 ORDER BY priority ASC';
            } else {
                $query = 'SELECT * FROM ' . $this->table . ' WHERE  active=1 or core=1 ORDER BY priority ASC';
            }
            $re = $this->db->query($query);
            $plugins = [];
            if (is_iterable($re)) {
                foreach ($re as $row) {
                    if ($row['core'] == 1) {
                        if ($row['active'] == 1) {
                            $plugins[$row['name']] = $row;
                        } else {
                            $plugins[$row['name']] = false;
                        }
                    } else {
                        $plugins[$row['name']] = $row;
                    }
                    $plugins[$row['name']]['missing'] = !file_exists(_base_ . '/plugins/' . $row['name']);
                }
            }
            foreach ($this->plugin_core as $core_name) {
                if (!array_key_exists($core_name, $plugins)) {
                    $manifest = self::readPluginManifest($core_name);
                    $plugins[$manifest['name']] = $manifest;
                }
            }
            self::$plugins_active = array_filter($plugins);
        }

        return self::$plugins_active;
    }

    /**
     * Get plugins list (if parameter true returns only active plugins).
     *
     * @param bool $onlyActive
     *
     * @return array
     */
    public function getPlugins($onlyActive = false)
    {
        $plugins = [];
        $arrayDep = [];
        $dp = opendir(_plugins_);
        //read each plugin in folder
        while ($file = readdir($dp)) {
            if (!preg_match("/^\./", $file)) {
                $tmpDependencies = [];
                $manifest = self::readPluginManifest($file);
                //accept only plugins where manifest name is the folder name
                if ($manifest['name'] == $file) {
                    $info = $this->getPluginFromDB($file, 'name');
                    //if plugin is installed
                    if ($info) {
                        $info['version_error'] = false;
                        // check plugin version
                        if ($this->isNewerVersion($info['version'], $manifest['version'])) {
                            $info['update'] = true;
                            $info['online'] = false;
                        } elseif ($info['version'] != $manifest['version']) {
                            $info['version_error'] = true;
                        } elseif ($this->checkOnlineUpdate($file)) {
                            $info['update'] = true;
                            $info['online'] = true;
                        }
                        if (!$onlyActive) {
                            //check if plugin is a dependence for other plugins
                            $tmpResults = $this->is_dependence($info['name']);
                            $tmpDependencies = $tmpResults['dependencies'];

                            if ($info['active']) {
                                foreach ($tmpDependencies as $tmpDependency) {
                                    $arrayDep[$tmpDependency['name']]['dependence_of'][$info['name']] = $tmpDependency['version'];
                                }
                            } else {
                                foreach ($tmpDependencies as $tmpDependency) {
                                    if (!$tmpDependency['active']) {
                                        $arrayDep[$info['name']]['dependence_of'][$tmpDependency['name']] = $tmpDependency['name'];
                                    }
                                }
                            }

                            if (count($tmpResults['forma_version'])) {
                                $info['unsuitable_forma'] = true;
                                $info['active'] = 0;
                                $this->setupPlugin($info['name'], false);
                            }

                            $plugins[$file] = $info;
                        } else {
                            if ($info['active'] == 1) {
                                $plugins[$file] = $info;
                            }
                        }
                        // if plugin is not installed
                    } elseif (!$onlyActive) {
                        // check if plugin depends from other plugins
                        if (!count($dependencies = $this->check_dependencies($manifest)['dependencies'])) {
                            $manifest['dependencies_unsatisfied'] = false;
                        } else {
                            $manifest['dependencies_unsatisfied'] = $dependencies;
                        }

                        $plugins[$file] = $manifest;
                    }
                }
            }
        }

        foreach ($plugins as $name => $manifest) {
            if (isset($arrayDep[$name])) {
                $plugins[$name]['dependence_of'] = $arrayDep[$name]['dependence_of'];
            }
        }
        closedir($dp);

        return $plugins;
    }

    /**
     * Import plugin's sql files.
     *
     * @param $fn
     *
     * @return array
     */
    public function importSqlFile($fn)
    {
        $res = ['ok' => true, 'log' => ''];

        $handle = fopen($fn, 'rb');
        if ($handle == false) {
            $res = ['ok' => false, 'log' => 'error opening file'];
        } else {
            $fileSz = filesize($fn);
            if ($fileSz > 0) {
                $content = fread($handle, $fileSz);
                fclose($handle);
                // This two regexp works fine; don't edit them! :)
                $content = preg_replace('/--(.*)[^$]/', '', $content);
                $sql_arr = preg_split("/;([\s]*)[\n\r]/", $content);
                foreach ($sql_arr as $sql) {
                    $qtxt = trim($sql);
                    if (!empty($qtxt)) {
                        $q = sql_query($qtxt);
                        if (!$q) {
                            $res['log'] .= sql_error() . "\n";
                            Forma::addError(sql_error());
                            $res['ok'] = false;
                        }
                    }
                }
            }
        }
        return $res;
    }

    /**
     * Run specified plugin standard methods.
     *
     * @param $plugin_id
     * @param $method
     *
     * @return mixed
     */
    public function callPluginMethod($plugin_id, $method)
    {
        $res = sql_query('select name, version from ' . $this->table . "
					where name = '" . $plugin_id . "'");
        $plugin_name = $plugin_id;
        $plugin_version = null;
        if (sql_num_rows($res) > 0) {
            [$plugin_name, $plugin_version] = sql_fetch_row($res);
        }

        $plugin_class = 'Plugin';
        require_once _plugins_ . '/' . $plugin_name . '/' . $plugin_class . '.php';
        $res = $this->importSqlFile(_plugins_ . '/' . $plugin_name . '/db/' . $method . '.sql');
        if (!$res['ok']) {
            return false;
        }
        if (method_exists('Plugin\\' . $plugin_name . '\\' . $plugin_class, $method)) {
            $fnResult = call_user_func(['Plugin\\' . $plugin_name . '\\' . $plugin_class, $method], $plugin_name, $plugin_version);

            return $fnResult === false ? false : true;
        }
    }

    /**
     * Remove plugin's settings from forma's settings.
     *
     * @param $plugin_name
     *
     * @return bool
     */
    private function removeSettings($plugin_name)
    {
        return (bool)sql_query('DELETE FROM %adm_setting WHERE pack="' . $plugin_name . '"');
    }

    /**
     * Remove plugin's requests from forma's requests.
     *
     * @param $plugin_name
     *
     * @return bool
     */
    private function removeRequests($plugin_name)
    {
        $plugin_info = $this->getPluginFromDB($plugin_name, 'name');

        return (bool)sql_query('DELETE FROM %adm_requests WHERE plugin="' . $plugin_info['plugin_id'] . '"');
    }

    private function getIdMenu($plugin_name)
    {
        $plugin_info = $this->getPluginFromDB($plugin_name, 'name');
        $idMenu = null;
        // Get idMenu
        $idMenuQuery = "SELECT idMenu FROM %adm_menu WHERE idPlugin = '" . $plugin_info['plugin_id'] . "'";
        $idMenuResult = sql_query($idMenuQuery);
        if ($idMenuResult) {
            if ($idMenuRow = sql_fetch_row($idMenuResult)) {
                return $idMenuRow[0];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Remove plugin's menu.
     *
     * @param $plugin_name
     *
     * @return bool
     */
    private function removeMenu($plugin_name)
    {
        $plugin_info = $this->getPluginFromDB($plugin_name, 'name');
        $plugin_id = $plugin_info['plugin_id'];
        if (sql_query("DELETE FROM %adm_menu_under WHERE idMenu IN ( SELECT idMenu FROM %adm_menu WHERE idPlugin = $plugin_id ) ")) {
            if (sql_query("DELETE FROM %adm_menu WHERE idPlugin = $plugin_id ")) {
                return true;
            }
        }

        return false;
    }

    public function installTranslations($plugin_name)
    {
        $plugin_info = $this->getPluginFromDB($plugin_name, 'name');
        $check = true;
        $path = _plugins_ . '/' . $plugin_name . '/translations/';
        $model = new LangAdm();

        $installedLangs = $model->getLangCodeList();
        foreach ($installedLangs as $installedLang) {
            $lang_file = $path . 'lang[' . $installedLang . '].xml';

            if (file_exists($lang_file)) {
                $check = $model->importTranslation($lang_file, true, false, (int)$plugin_info['plugin_id']);
            }
        }

        return $check !== false;
    }

    public function removeTranslations($plugin_name)
    {
        $plugin_info = $this->getPluginFromDB($plugin_name, 'name');
        $idPlugin = $plugin_info['plugin_id'];
        $plugin_name = strtoupper($plugin_name);
        $queryKey = " DELETE FROM %adm_lang_text WHERE plugin_id = $idPlugin ";

        return sql_query($queryKey) ? true : false;
    }

    /**
     * Insert specified plugin in forma.
     *
     * @param $plugin_name
     * @param int $priority
     * @param bool $update
     * @param int $core
     *
     * @return bool|mixed
     */
    public function installPlugin($plugin_name, $priority = 0, $update = false, $core = 0)
    {
        $plugin_info = self::readPluginManifest($plugin_name);
        if ($plugin_info['core'] == 'true') {
            $core = 1;
        }
        //FORMA_PLUGIN: QUI AGGIUNGERE IL CONTROLLO DELLA VERSIONE
        $query = 'insert into ' . $this->table . "
				values(null,'" . addslashes($plugin_name) . "', '" . addslashes($plugin_info['title']) . "', '" . addslashes($plugin_info['category']) . "',
					'" . addslashes($plugin_info['version']) . "', '" . addslashes($plugin_info['author']) . "', '" . addslashes($plugin_info['link']) . "', $priority,
					'" . addslashes($plugin_info['description']) . "'," . time() . ' ,0,' . (int)$core . ' )';
        if ($plugin_info) {
            $result = sql_query($query);
            if ($result) {
                if (!$update) {
                    if (
                        $this->callPluginMethod($plugin_name, 'install') &&
                        $this->installTranslations($plugin_name)
                    ) {
                        return true;
                    } else {
                        return false;
                    }
                }

                return $plugin_info;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Set specified priority for specified plugin.
     *
     * @param $plugin_name
     * @param int $priority
     *
     * @return bool
     */
    public function setPriority($plugin_name, $priority = 0)
    {
        $updateQuery = sql_query('
        UPDATE ' . $this->table . '
        SET priority=' . (int)$priority . "
        WHERE name = '" . $plugin_name . "'");
        if ($updateQuery) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Uninstall specified plugin.
     *
     * @param $plugin_id
     * @param bool $update
     *
     * @return bool
     */
    public function uninstallPlugin($plugin_id, $update = false)
    {
        $reSetting = true;
        if (!$update) {
            if (
                $this->setupPlugin($plugin_id, false) &&
                $this->callPluginMethod($plugin_id, 'uninstall') &&
                $this->removeSettings($plugin_id) &&
                $this->removeRequests($plugin_id) &&
                $this->removeTranslations($plugin_id) &&
                $this->removeMenu($plugin_id)
            ) {
                $reSetting = true;
            } else {
                $reSetting = false;
            }
        }

        sql_query('
			DELETE FROM ' . $this->table . "
			WHERE name='" . $plugin_id . "'");

        return $reSetting;
    }

    /**
     * Activate or deactivate specified plugin.
     *
     * @param $plugin_id
     * @param $active
     *
     * @return mixed
     */
    public function setupPlugin($plugin_id, $active)
    {
        $reSetting = true;
        if ($active == 1) {
            $reSetting = $this->callPluginMethod($plugin_id, 'activate');
        } else {
            $reSetting = $this->callPluginMethod($plugin_id, 'deactivate');
        }

        sql_query('
			UPDATE ' . $this->table . '
			SET active=' . (int)$active . "
			WHERE name = '" . $plugin_id . "'");

        return $reSetting;
    }

    /**
     * Download plugin's last version online.
     *
     * @param $name
     *
     * @return bool
     */
    public function downloadPlugin($name)
    {
        $info = self::readPluginManifest($name);
        if (!isset($info['update'])) {
            return false;
        }
        $link = $info['update'] . '?action=download&plugin=' . $name;
        $f = file_put_contents(_plugins_ . '/' . 'temp_update.zip', fopen($link, 'r'), LOCK_EX);
        if (false === $f) {
            return false;
        }

        return $this->unpackPlugin('temp_update.zip', $name);
    }

    /**
     * Unpack package zip and optionally rename given plugin folder adding ".old".
     *
     * @param $package_name
     * @param $rename
     *
     * @return bool
     */
    public function unpackPlugin($package_name, $rename = false)
    {
        $zip = new ZipArchive();
        $res = $zip->open(_plugins_ . '/' . $package_name);
        if ($res === true) {
            if ($rename) {
                $rename_file = _plugins_ . '/' . $rename;
                $rename_file_time = $rename_file;
                if (file_exists($rename_file_time)) {
                    $rename_file_time .= '.' . time();
                }
                rename($rename_file, $rename_file_time);
            }
            $zip->extractTo(_plugins_);
            $zip->close();
            fclose(_plugins_ . '/' . $package_name);
            unlink(_plugins_ . '/' . $package_name);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Upload a plugin to forma.
     *
     * @param $file_uploaded ($_FILES['plugin_file_upload'])
     *
     * @return bool
     */
    public function uploadPlugin($file_uploaded)
    {
        require_once _base_ . '/lib/lib.upload.php';
        if ($file_uploaded['name'] == '') {
            return false;
        } else {
            $path = '/';
            $savefile = $file_uploaded['name'];
            if (!file_exists(_plugins_ . '/' . $savefile)) {
                sl_open_fileoperations();
                if (!sl_upload($file_uploaded['tmp_name'], $path . $savefile, 'zip', _plugins_)) {
                    sl_close_fileoperations();

                    return false;
                }
                $name = pathinfo($file_uploaded['name'], PATHINFO_FILENAME);

                return $this->unpackPlugin($savefile, $name);
                sl_close_fileoperations();
            } else {
                return false;
            }
        }
    }

    public static function removeDirectory($path)
    {
        $files = scandir($path . '/');
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                if (!(is_dir($path . '/' . $file) ? self::removeDirectory($path . '/' . $file) : unlink($path . '/' . $file))) {
                    return false;
                }
            }
        }

        return rmdir($path);
    }

    /**
     * Delete all plugins files.
     *
     * @param $name
     *
     * @return bool
     */
    public function delete_files($name)
    {
        $path = _plugins_ . '/' . $name;
        if (file_exists($path)) {
            return self::removeDirectory($path);
        } else {
            return false;
        }
    }

    /**
     * Update specified plugin locally.
     *
     * @param $plugin_id
     * @param bool $online
     *
     * @return bool
     */
    public function updatePlugin($plugin_id, $online = false)
    {
        if ($online) {
            if (!$this->downloadPlugin($plugin_id)) {
                return false;
            }
        }
        if ($this->callPluginMethod($plugin_id, 'update') !== false) {
            $plugin_db = $this->getPluginFromDB($plugin_id, 'name');
            $plugin_info = self::readPluginManifest($plugin_id);
            $query = 'UPDATE ' . $this->table . "
                    SET 
                        title = '" . addslashes($plugin_info['title']) . "',
                        category = '" . addslashes($plugin_info['category']) . "',
                        version = '" . addslashes($plugin_info['version']) . "',
                        author = '" . addslashes($plugin_info['author']) . "',
                        link = '" . addslashes($plugin_info['link']) . "',
                        description = '" . addslashes($plugin_info['description']) . "'
                    WHERE
                        plugin_id = " . $plugin_db['plugin_id'];
            $result = sql_query($query);
            if ($result) {
                $this->installTranslations($plugin_id);

                return true;
            }
        }

        return false;
    }

    public static function getPluginCore()
    {
        $plugins = array_diff(scandir(_plugins_), ['..', '.']);
        $plugin_list = [];
        foreach ($plugins as $plugin) {
            if (self::readPluginManifest($plugin, 'core') == 'true') {
                $plugin_list[] = $plugin;
            }
        }

        return $plugin_list;
    }
}
