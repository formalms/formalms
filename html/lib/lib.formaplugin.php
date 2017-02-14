<?php
/**
 * FormaPlugin class
 * Interface for Forma Plugins 
 */

class FormaPlugin {
    static function getName(){
        $reflector = new ReflectionClass(static::class);
        $fn = $reflector->getFileName();
        $name=basename(dirname($fn));
        return $name;
    }
    public function addSetting($name, $type, $size, $value="", $sequence=0){
        $pg_adm=new PluginAdm();
        $plugin_info=$pg_adm->getPluginFromDB(self::getName(),'name');
        $query_insert_string="INSERT %adm_setting (param_name,param_value,value_type,max_size,pack,regroup,sequence) VALUES ";
        $query_insert_string.="('".$name."','".$value."','".$type."',".$size.",'".$plugin_info['name']."',".$plugin_info['regroup'].",".$sequence.")";
        sql_query($query_insert_string);
        return true;
    }
}