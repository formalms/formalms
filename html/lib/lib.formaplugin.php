<?php
/**
 * FormaPlugin class
 * Interface for Forma Plugins 
 */

abstract class FormaPlugin {

    public function install()
    {
        //code executed after install
    }

    public function uninstall()
    {
        //code executed after uninstall
    }

    public function activate()
    {
        //code executed after activation
    }

    public function deactivate()
    {
        //code executed after deactivation
    }

    public static function getPath($file = '') {

        return _plugins_ . '/' . static::getName() . '/' . $file;
    }

    public static function getURL($file = '') {

        return \Get::site_url() . _folder_plugins_ . '/' . static::getName() . '/' . $file;
    }

    public static function getName(){
        $reflector = new ReflectionClass( get_called_class() );
        $fn = $reflector->getFileName();
        $name=basename(dirname($fn));
        return $name;
    }

    public static function getID() {        
        $pg_adm=new PluginmanagerAdm();
        $plugin_info=$pg_adm->getPluginFromDB(self::getName(),'name');
        return (int)$plugin_info['plugin_id'];
    }

    public static function addSetting($name, $type, $size, $value="", $sequence=0){
        $pg_adm=new PluginmanagerAdm();
        $plugin_info=$pg_adm->getPluginFromDB(self::getName(),'name');
        $query_insert_string="INSERT %adm_setting (param_name,param_value,value_type,max_size,pack,regroup,sequence) VALUES ";
        $query_insert_string.="('".$name."','".$value."','".$type."',".$size.",'".$plugin_info['name']."',".$plugin_info['regroup'].",".$sequence.")";
        sql_query($query_insert_string);
        return true;
    }

    /**
     * Add a route to the specified controller to handle the request
     *
     * @param $app
     * @param $name
     * @param $controller
     * @param $model
     *
     * @return bool
     */
    public static function addRequest($app, $name, $controller, $model){
        $query_insert_string="INSERT %adm_requests (app, name, controller, model, plugin) VALUES ";
        $query_insert_string.="('".$app."','".$name."','".$controller."','".$model."',".self::getID().")";
        sql_query($query_insert_string);
        return true;
    }

    /**
     * Add a role.
     *
     * @param string $role
     * @return void
     */
    public static function addRole($role) {
        $am = Docebo::user()->getACLManager();
        if($role_info = $am->getRole($role)) {
            $idst = $role_info[ACL_INFO_IDST];
        } else {
            $idst = $am->registerRole($role, '', self::getID());
        }
        return $idst;
    }

    /**
     * Add new menu item and create the required role using the plug-in ID reference.
     *
     * @param array $menu
     *    string $name
     *    string|null $image
     *    int|null $sequence
     *    bool|null $isActive
     *    bool|null $collapse
     *    int|null $idParent
     *    string|null $ofPlatform
     * @param array|null $menuUnder
     *    string $defaultName
     *    string $moduleName
     *    string $associatedToken
     *    string|null $defaultOp
     *    string|null $ofPlatform
     *    int|null $sequence
     *    string|null $classFile
     *    string|null $className
     *    string|null $mvcPath
     * @param array $roleMembers
     * @return int|false
     */
    public static function addMenu($menu, $menuUnder = null, $roleMembers = array()) {
        return CoreMenu::addMenu($menu, $menuUnder, $roleMembers, self::getID());
    }

    /**
     * Add menu item in admin area using plug-in ID reference.
     * 
     * @deprecated
     *
     * @param string $name
     * @param string $mvcPath
     * @param boolean $parent
     * @param string $icon
     * @param boolean $is_active
     * @param string $of_platform
     * @return void
     */
    public static function addCoreMenu($name, $mvcPath, $parent=false, $icon='', $is_active=false, $of_platform = 'framework'){
        CoreMenu::addMenuChild($name, $mvcPath, 'framework', $of_platform, $parent, $icon, $is_active, self::getID());
    }

    /**
     * Add menu item in LMS area using plug-in ID reference.
     * 
     * @deprecated
     *
     * @param string $name
     * @param string $mvcPath
     * @param boolean $parent
     * @param string $icon
     * @param boolean $is_active
     * @param string $of_platform
     * @return void
     */
    public static function addLmsMenu($name, $mvcPath, $parent=false, $icon='', $is_active=false, $of_platform = 'lms'){
        CoreMenu::addMenuChild($name, $mvcPath, 'lms', $of_platform, $parent, $icon, $is_active, self::getID());
    }

}