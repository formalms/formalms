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

    static function getName(){
        $reflector = new ReflectionClass( get_called_class() );
        $fn = $reflector->getFileName();
        $name=basename(dirname($fn));
        return $name;
    }
    public function addSetting($name, $type, $size, $value="", $sequence=0){
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
    public function addRequest($app, $name, $controller, $model){
        $pg_adm=new PluginmanagerAdm();
        $plugin_info=$pg_adm->getPluginFromDB(self::getName(),'name');
        $query_insert_string="INSERT %adm_requests (app, name, controller, model, plugin) VALUES ";
        $query_insert_string.="('".$app."','".$name."','".$controller."','".$model."',".$plugin_info['plugin_id'].")";
        sql_query($query_insert_string);
        return true;
    }

    function addCoreMenu($name, $mvcPath, $parent=false, $icon='', $is_active=false){
        $pg_adm=new PluginmanagerAdm();
        $plugin_info=$pg_adm->getPluginFromDB(self::getName(),'name');
        include_once _lib_.'/lib.menu.php';
        MenuManager::addMenuChild($name, $mvcPath, $parent, $icon, $is_active, $plugin_info['plugin_id']);
    }

}