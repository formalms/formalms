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

abstract class FormaPlugin
{
    public static function install()
    {
        //code executed after install
    }

    public static function uninstall()
    {
        //code executed after uninstall
    }

    public static function activate()
    {
        //code executed after activation
    }

    public static function deactivate()
    {
        //code executed after deactivation
    }

    public static function img($plugin_name)
    {
        return FormaLms\lib\Get::rel_path('plugins') . '/' . $plugin_name . '/images';
    }

    public static function css($plugin_name, $css_name)
    {
        Util::get_css(FormaLms\lib\Get::rel_path('plugins') . '/' . $plugin_name . '/style/' . $css_name, true, true);
    }

    public static function js($plugin_name, $js_name)
    {
        Util::get_js(FormaLms\lib\Get::rel_path('plugins') . '/' . $plugin_name . '/js/' . $js_name, true, true);
    }

    public static function getPath($file = '')
    {
        return _plugins_ . '/' . static::getName() . '/' . $file;
    }

    public static function getURL($file = '')
    {
        return \FormaLms\lib\Get::site_url() . _folder_plugins_ . '/' . static::getName() . '/' . $file;
    }

    public static function getName()
    {
        $reflector = new ReflectionClass(get_called_class());
        $fn = $reflector->getFileName();
        $name = basename(dirname($fn));

        return $name;
    }

    public static function getID()
    {
        $pg_adm = new PluginmanagerAdm();
        $plugin_info = $pg_adm->getPluginFromDB(self::getName(), 'name');

        return (int) $plugin_info['plugin_id'];
    }

    public static function addSetting($name, $type, $size, $value = '', $sequence = 0)
    {
        $pg_adm = new PluginmanagerAdm();
        $plugin_info = $pg_adm->getPluginFromDB(self::getName(), 'name');
        $query_insert_string = 'INSERT %adm_setting (param_name,param_value,value_type,max_size,pack,regroup,sequence,extra_info) VALUES ';
        $query_insert_string .= "('" . $name . "','" . $value . "','" . $type . "'," . $size . ",'" . $plugin_info['name'] . "'," . $plugin_info['regroup'] . ',' . $sequence . ", '')";
        $res = sql_query($query_insert_string);

        return true;
    }

    /**
     * Add a route to the specified controller to handle the request.
     *
     * @param $app
     * @param $name
     * @param $controller
     * @param $model
     *
     * @return bool
     */
    public static function addRequest($app, $name, $controller, $model)
    {
        $query_insert_string = 'INSERT %adm_requests (app, name, controller, model, plugin) VALUES ';
        $query_insert_string .= "('" . $app . "','" . $name . "','" . $controller . "','" . $model . "'," . self::getID() . ')';
        sql_query($query_insert_string);

        return true;
    }

    /**
     * Add a role.
     *
     * @param string $role
     *
     * @return void
     */
    public static function addRole($role)
    {
        $am = \FormaLms\lib\Forma::getAclManager();;
        if ($role_info = $am->getRole($role, false)) {
            $idst = $role_info[ACL_INFO_IDST];
        } else {
            $idst = $am->registerRole($role, '', self::getID());
        }

        return $idst;
    }

    /**
     * Add new menu item and create the required role using the plug-in ID reference.
     *
     * @param array      $menu
     *                                string $name
     *                                string|null $image
     *                                int|null $sequence
     *                                bool|null $isActive
     *                                bool|null $collapse
     *                                int|null $idParent
     *                                string|null $ofPlatform
     * @param array|null $menuUnder
     *                                string $defaultName
     *                                string $moduleName
     *                                string $associatedToken
     *                                string|null $defaultOp
     *                                string|null $ofPlatform
     *                                int|null $sequence
     *                                string|null $classFile
     *                                string|null $className
     *                                string|null $mvcPath
     * @param array      $roleMembers
     *
     * @return int|false
     */
    public static function addMenu($menu, $menuUnder = null, $roleMembers = [])
    {
        return CoreMenu::addMenu($menu, $menuUnder, $roleMembers, self::getID());
    }

    /**
     * Add menu item in admin area using plug-in ID reference.
     *
     * @deprecated
     *
     * @param string $name
     * @param string $mvcPath
     * @param bool   $parent
     * @param string $icon
     * @param bool   $is_active
     * @param string $of_platform
     *
     * @return void
     */
    public static function addCoreMenu($name, $mvcPath, $parent = false, $icon = '', $is_active = false, $of_platform = 'framework')
    {
        CoreMenu::addMenuChild($name, $mvcPath, 'framework', $of_platform, $parent, $icon, $is_active, self::getID());
    }

    /**
     * Add menu item in LMS area using plug-in ID reference.
     *
     * @deprecated
     *
     * @param string $name
     * @param string $mvcPath
     * @param bool   $parent
     * @param string $icon
     * @param bool   $is_active
     * @param string $of_platform
     *
     * @return void
     */
    public static function addLmsMenu($name, $mvcPath, $parent = false, $icon = '', $is_active = false, $of_platform = 'lms')
    {
        CoreMenu::addMenuChild($name, $mvcPath, 'lms', $of_platform, $parent, $icon, $is_active, self::getID());
    }
}
