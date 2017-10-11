<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */


class PluginmanagerAdm extends Model {

	protected $db;
    protected $table;
    protected $plugin_core;

    public function  __construct() {
        $this->db = DbConn::getInstance();
        $this->table = $GLOBALS['prefix_fw'].'_plugin';
        $this->plugin_core = array(
            "FormaAuth"
        );
    }

    public function getPerm()	{
        return array(
            'view'						=> 'standard/view.png'
        );
    }

    /**
     * Read specified plugin manifest
     * @param $plugin_name
     * @param bool $key
     * @return bool|mixed
     */
    function readPluginManifest($plugin_name,$key=false){
        $plugin_file=_base_."/plugins/".$plugin_name."/manifest.xml";
        if (!file_exists($plugin_file)){
            return false;
        }
        if($xml = simplexml_load_file($plugin_file)){
            $man_json = json_encode($xml);
            $man_array = json_decode($man_json,TRUE);

            if(!$xml->name){
                return false;
            }
            else{
                if (key_exists($key, $man_array)){
                    return $man_array[$key];
                }
                return $man_array;
            }
        }
        else{
            return false;
        }
    }

    /**
     * Get specified plugin information giving [plugin_id | code | name]
     * @param $id
     * @param string $type_id
     * @return array
     */
    public function getPluginFromDB($id, $type_id="plugin_id") {
        switch($type_id){
            case "plugin_id":
                $where_id = "  plugin_id = ".Get::filter($id, DOTY_INT);
                break;
            case "name":
                $where_id = "  name = '".Get::filter($id, DOTY_MIXED)."' ";
                break;
        }

        $query = "SELECT * FROM ".$this->table." WHERE  ".$where_id;
        $re = $this->db->query($query);

        $row = sql_fetch_assoc($re);

        return $row;
    }

    /**
     * Compare two plugin's versions
     * @param $old
     * @param $new
     * @return bool
     */
    public function isNewerVersion($old, $new){
        return version_compare($old, $new) < 0;
    }

    /**
     * Check if there are online updates for specified plugin
     * @param $name
     * @return bool
     */
    public function checkOnlineUpdate($name){
        $info=$this->readPluginManifest($name);
        if (isset($info['update'])){
            $last_version = file_get_contents($info['update']."/?action=manifest&plugin=".$name);
            $last_version_parsed = @json_decode(@json_encode(simplexml_load_string($last_version)),1);
            if ($this->isNewerVersion($info['version'],$last_version_parsed['version'])) {
                return true;
            }
        }
        return false;
    }

    private static function scan_dir(){
        return array_diff(scandir(_base_.'/plugins/'), array('..', '.'));
    }

    private function check_dependencies($manifest, $dependence = false){
        if ($manifest) {
            $dependencies=@$manifest['dependencies'];
            $plugin_list = self::getActivePlugins();
            if ($dependence){
                unset($plugin_list[$dependence]);
            }
            $check = true;
            if (isset($dependencies)) {
                foreach ($dependencies as $name => $version) {
                    if (key_exists($name, $plugin_list)) {
                        $dependant_manifest=$this->readPluginManifest($name);
                        if (version_compare($version, $dependant_manifest['version']) > 0) {
                            $check = $manifest['dependencies'];
                            break;
                        }
                    } else {
                        $check = $manifest['dependencies'];
                        if ($dependence){
                            $check = array("name"=> $manifest['name'], "version" => $manifest['version']);
                        }
                        break;
                    }
                }
            }
            return $check;
        }
    }

    private function is_dependence($name){
        $dependencies = array();
        foreach ( self::getInstalledPlugins() as $file => $content){

            $manifest = $this->readPluginManifest($file);
            if (is_array($dependence = $this->check_dependencies($manifest, $name) ) ){
                $dependencies[$dependence['name']] = $dependence['version'];
            }
        }
        if (count($dependencies)>0){
            return $dependencies;
        } else {
            return false;
        }
    }

    public function getInstalledPlugins(){
        $query = "SELECT * FROM ".$this->table;
        $re = $this->db->query($query);
        $plugins=array();
        while($row = sql_fetch_assoc($re)){
            if ($row['core']==1){
                if ( $row['active']==1 ){
                    $plugins[$row['name']]=$row;
                } else {
                    $plugins[$row['name']]=false;
                }
            } else {
                $plugins[$row['name']]=$row;
            }
        }
        foreach ( $this->plugin_core as $core_name ){
            if (!key_exists($core_name, $plugins)){
                $manifest = $this->readPluginManifest($core_name);
                $plugins[$manifest['name']]=$manifest;
            }
        }
        return array_filter($plugins);
    }

    public function getActivePlugins(){
        $query = "SELECT * FROM ".$this->table." WHERE  active=1 or core=1";
        $re = $this->db->query($query);
        $plugins=array();
        while($row = sql_fetch_assoc($re)){
            if ($row['core']==1){
                if ( $row['active']==1 ){
                    $plugins[$row['name']]=$row;
                } else {
                    $plugins[$row['name']]=false;
                }
            } else {
                $plugins[$row['name']]=$row;
            }
        }
        foreach ( $this->plugin_core as $core_name ){
            if (!key_exists($core_name, $plugins)){
                $manifest = $this->readPluginManifest($core_name);
                $plugins[$manifest['name']]=$manifest;
            }
        }
        return array_filter($plugins);
    }

    /**
     * Get plugins list (if parameter true returns only active plugins)
     * @param bool $onlyActive
     * @return array
     */
    public function getPlugins($onlyActive=false){
        $plugins=array();
        $dp=opendir(_base_."/plugins/");
        //read each plugin in folder
        while ($file = readdir($dp)){
            if(!preg_match("/^\./",$file)) {
                $manifest=$this->readPluginManifest($file);
                //accept only plugins where manifest name is the folder name
                if ($manifest['name']==$file){
                    $info=$this->getPluginFromDB($file,'name');
                    //if plugin is installed
                    if ($info){
                        $info['version_error']=false;
                        // check plugin version
                        if ($this->isNewerVersion($info['version'],$manifest['version'])){
                            $info['update']=true;
                            $info['online']=false;
                        } else if ($info['version']!=$manifest['version']){
                            $info['version_error']=true;
                        } else if ($this->checkOnlineUpdate($file)){
                            $info['update']=true;
                            $info['online']=true;
                        }
                        if (!$onlyActive){
                            //check if plugin is a dependence for other plugins
                            $info['dependence_of'] = $this->is_dependence($info['name']);

                            $plugins[$file]=$info;
                        } else {
                            if ($info['active']==1){
                                $plugins[$file]=$info;
                            }
                        }
                    // if plugin is not installed
                    } else if (!$onlyActive){
                        // check if plugin depends from other plugins
                        if (!is_array($dependencies = $this->check_dependencies($manifest))){
                            $manifest['dependencies_unsatisfied']=false;
                        } else {
                            $manifest['dependencies_unsatisfied']=$dependencies;
                        }
                        $plugins[$file]=$manifest;
                    }
                }
            }
        }
        closedir($dp);
        return $plugins;
    }

    /**
     * Import plugin's sql files
     * @param $fn
     * @return array
     */
    public function importSqlFile($fn) {
        $res =array('ok'=>true, 'log'=>'');

        $handle = fopen($fn, "r");
        $content = fread($handle, filesize($fn));
        fclose($handle);

        // This two regexp works fine; don't edit them! :)
        $content=preg_replace("/--(.*)[^\$]/", "", $content);
        $sql_arr=preg_split("/;([\s]*)[\n\r]/", $content);
        foreach ($sql_arr as $sql) {
            $qtxt=trim($sql);
            if (!empty($qtxt)) {

                $q=sql_query($qtxt);
                if (!$q) {
                    $res['log'].=sql_error()."\n";
                    $res['ok'] =FALSE;
                }
            }
        }
        return $res;
    }

    /**
     * Run specified plugin standard methods
     * @param $plugin_id
     * @param $method
     * @return mixed
     */
    public function callPluginMethod($plugin_id, $method){
        $res=sql_query("select name, version from ".$this->table."
					where name = '".$plugin_id."'");
        $plugin_name=$plugin_id;
        $plugin_version=null;
        if (sql_num_rows( $res )>0){
            list($plugin_name, $plugin_version) = sql_fetch_row( $res );
        }

        $plugin_class="Plugin";
        require_once(_plugins_."/".$plugin_name."/".$plugin_class.".php");
        $this->importSqlFile(_plugins_."/".$plugin_name."/db/".$method.".sql");
        if (method_exists('Plugin\\'.$plugin_name.'\\'.$plugin_class, $method)){
            return call_user_func(array('Plugin\\'.$plugin_name.'\\'.$plugin_class, $method), $plugin_name, $plugin_version);
        }
    }

    /**
     * Remove plugin's settings from forma's settings
     * @param $plugin_name
     * @return reouce_id
     */
    private function removeSettings($plugin_name){
        return sql_query('DELETE FROM %adm_setting WHERE pack="'.$plugin_name.'"');
    }

    /**
     * Remove plugin's requests from forma's requests
     * @param $plugin_name
     * @return reouce_id
     */
    private function removeRequests($plugin_name){
        $plugin_info = $this->getPluginFromDB($plugin_name,'name');
        return sql_query('DELETE FROM %adm_requests WHERE plugin="'.$plugin_info['plugin_id'].'"');
    }


    /**
     * Remove plugin's requests from forma's requests
     * @param $plugin_name
     * @return reouce_id
     */
    /*
    private function removeMenu($plugin_name){
        $plugin_info = $this->getPluginFromDB($plugin_name,'name');
        sql_query('DELETE FROM %lms_menu WHERE plugin_id="'.$plugin_info['plugin_id'].'"');
        sql_query('DELETE FROM %lms_menu_under WHERE plugin_id="'.$plugin_info['plugin_id'].'"');
        sql_query('DELETE FROM %adm_menu WHERE plugin_id="'.$plugin_info['plugin_id'].'"');
        sql_query('DELETE FROM %adm_menu_under WHERE plugin_id="'.$plugin_info['plugin_id'].'"');
    }
    */

    /**
     * Insert specified plugin in forma
     * @param $plugin_name
     * @param int $priority
     * @param bool $update
     * @param int $core
     * @return bool|mixed
     */
    function installPlugin($plugin_name, $priority=0, $update=false, $core=0){
        $plugin_info=$this->readPluginManifest($plugin_name);
        if ($plugin_info['core']=="true"){
            $core=1;
        }
        //FORMA_PLUGIN: QUI AGGIUNGERE IL CONTROLLO DELLA VERSIONE
        $query = "insert into ".$this->table."
				values(null,'".addslashes($plugin_name)."', '".addslashes($plugin_info['title'])."', '".addslashes($plugin_info['category'])."',
					'".addslashes($plugin_info['version'])."', '".addslashes($plugin_info['author'])."', '".addslashes($plugin_info['link'])."', $priority,
					'".addslashes($plugin_info['description'])."',".time()." ,0,".(int)$core." )";
        if($plugin_info){
            $result = sql_query($query);
            if ($result){
                if (!$update){
                    $this->callPluginMethod($plugin_name, 'install');
                }
                return $plugin_info;
            } else {
                return false;
            }
        }
        else{
            return false;
        }
    }

    /**
     * Uninstall specified plugin
     * @param $plugin_id
     * @param bool $update
     * @return reouce_id
     */
    public function uninstallPlugin($plugin_id, $update=false){
        if (!$update){
            $this->setupPlugin($plugin_id, false);
            $this->callPluginMethod($plugin_id, 'uninstall');
            $this->removeSettings($plugin_id);
            $this->removeRequests($plugin_id);
        }

        $reSetting = sql_query("
			DELETE FROM ".$this->table."
			WHERE name='".$plugin_id."'");

        return $reSetting;
    }

    /**
     * Activate or deactivate specified plugin
     * @param $plugin_id
     * @param $active
     * @return reouce_id
     */
    public function setupPlugin($plugin_id, $active){
        if($active == 1){
            $this->callPluginMethod($plugin_id,'activate');
        }
        else{
            //$this->removeMenu($plugin_id);
            $this->callPluginMethod($plugin_id,'deactivate');
        }

        $reSetting = sql_query("
			UPDATE ".$this->table."
			SET active=".$active."
			WHERE name = '".$plugin_id."'");

        return $reSetting;
    }

    /**
     * Download plugin's last version online
     * @param $name
     * @return bool
     */
    function downloadPlugin($name){
        $info=$this->readPluginManifest($name);
        $link=$info['update']."?action=download&plugin=".$name;
        $f = file_put_contents(_base_."/plugins/"."temp_update.zip", fopen($link, 'r'), LOCK_EX);
        if(FALSE === $f){
            die("Couldn't write to file.");
        }
        $zip = new ZipArchive;
        $res = $zip->open(_base_."/plugins/"."temp_update.zip");
        if ($res === TRUE) {
            rename(_base_."/plugins/".$name,_base_."/plugins/".$name.".old");
            $zip->extractTo(_base_."/plugins/");
            $zip->close();
            fclose(_base_."/plugins/"."temp_update.zip");
            unlink(_base_."/plugins/"."temp_update.zip");
            return true;
        } else {
            return false;
        }
    }

    /**
     * Update specified plugin locally
     * @param $plugin_id
     * @param bool $online
     * @return bool
     */
    public function updatePlugin($plugin_id, $online=false){
        if ($online){
            $this->downloadPlugin($plugin_id);
        }
        $this->callPluginMethod($plugin_id,'update');
        $res0=$this->uninstallPlugin($plugin_id, true);
        if ($res0){
            $res1=$this->installPlugin($plugin_id, 0, true);
            if ($res1){
                return true;
            }
        }
        return false;
    }

    public static function getPluginCore(){
        $plugins = array_diff(scandir(_base_.'/plugins/'), array('..', '.'));
        $plugin_list=array();
        foreach ($plugins as $plugin){
            if (self::readPluginManifest($plugin,'core')=="true"){
                $plugin_list[]=$plugin;
            }
        }
        return $plugin_list;
    }
}

?>