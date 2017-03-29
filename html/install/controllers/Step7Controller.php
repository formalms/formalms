<?php

require_once(dirname(__FILE__).'/StepController.php');

Class Step7Controller extends StepController {

	var $step=7;
	function __construct(){
        include_once _lib_."/mvc/lib.model.php";
	    include_once _adm_."/models/PluginmanagerAdm.php";
    }

    public function validate() {
		return true;
	}
    private static function manifest($name,$property=false){

    }
	public static function plugin_core(){
        $plugins = PluginmanagerAdm::getPluginCore();
        foreach ($plugins as $plugin){
            echo $plugin."<br>";
            echo Form::getCheckbox("","plugins_".$plugin,"plugins[]",$plugin,true," style='display:none;' ");

        }
    }
    public static function plugin_list(){
        $plugins = new PluginmanagerAdm();
        foreach ($plugins->getPlugins() as $plugin){
            if ($plugin['core']!="true"){
                echo Form::getCheckbox($plugin['title'],"plugins_".$plugin['name'],"plugins[]",$plugin['name']);
            }
        }
    }

}


?>