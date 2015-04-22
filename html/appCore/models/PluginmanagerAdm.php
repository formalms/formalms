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


class PluginmanagerAdm extends PluginAdm {

	protected $db;

	protected $table;
	
	public $plugins;

  	public $CATEGORY;

	public function  __construct() {
		$this->db = DbConn::getInstance();
		$this->table = $GLOBALS['prefix_fw'].'_plugin';
	}

	public function getPerm()
	{
		return array(	'view' => 'standard/view.png');
	}

	/**
	 * @return 	array 	this array contains valid plugins installed on formalms
	 *
	 * @access 	public
	 */
	public function getInstalledPlugins() {

		$sorted_plugins=array();
		
		$reSetting = sql_query("
			SELECT *
			FROM ".$this->table."
			order by active desc, priority");
		
		while(list($plugin_id, $name, $code, $versione, $path, $author, $link, $priority, $description, $active ) = sql_fetch_row( $reSetting )){
			$sorted_plugins[]=$name;
		}
		
		$unsorted_plugins=array();
		$dp=opendir(_base_."/plugins/");
		while ($file = readdir($dp)){
			if(!preg_match("/^\./",$file) && preg_match("/^\w{3,}$/",$file))$unsorted_plugins[$file]=1;
		}
		
		closedir($dp);

		$plugins=array();
		
		foreach ($sorted_plugins as $order => $name){
			if($unsorted_plugins[$name]){
				$plugins[]=$name;
				unset($unsorted_plugins[$name]);
			}
			else{
				$this->deactivePlugin($name);
			}
		}
		
		$index=0;
		foreach ($unsorted_plugins as $name => $val){
			$res=$this->insertPlugin($name,$index);
			if($res){
				$plugins[]=$name;
				$index++;
			}
		}
		
		return $plugins;
	}
	
	function insertPlugin($plugin_name, $priority){
		$plugin_info=$this->readManifest($plugin_name);
		$query = "insert into ".$this->table."
				values(null,'".addslashes($plugin_name)."', '".addslashes($plugin_info['title'])."', '".addslashes($plugin_info['code'])."', '".addslashes($plugin_info['category'])."',
					'".addslashes($plugin_info['version'])."', '".addslashes($plugin_info['author'])."', '".addslashes($plugin_info['link'])."', $priority, 
					'".addslashes($plugin_info['description'])."', 0 )";
		if($plugin_info){
			$result = sql_query($query);
			
			return $plugin_info;
		}
		else{
			return false;
		}
	}
	
	function readManifest($plugin_name){
		$plugin_file=_base_."/plugins/".$plugin_name."/manifest.xml";
		
		if($xml = simplexml_load_file($plugin_file)){
			$man_json = json_encode($xml);
			$man_array = json_decode($man_json,TRUE);

			if(!$xml->title || !$xml->code || !$xml->version || !$xml->author || !$xml->description){
				return false;
			}
			else{
				return $man_array;
			}
		}
		else{
			return false;
		}
	}
	
	function deactivePlugin($name){
		$reSetting = sql_query("
			UPDATE ".$this->table."
			SET active=0
			WHERE name like '".$name."'");
		
		return $reSetting;
	}
	
	public function activatePlugin($plugin_id){
		$res=sql_query("select name from ".$this->table."
					where plugin_id = ".$plugin_id);
		
		list($name) = sql_fetch_row( $res );
		
		$plugin_class=ucfirst($name)."Plugin";
		require_once(_plugins_."/".$name."/".$plugin_class.".php");
				
		$pluginApp=new $plugin_class();
		if(method_exists($pluginApp, 'activate')){
			$pluginApp->activate();
		}
	}
	
	public function deactivatePlugin($plugin_id){
		$res=sql_query("select name from ".$this->table."
					where plugin_id = ".$plugin_id);
		
		list($name) = sql_fetch_row( $res );
		
		$plugin_class=ucfirst($name)."Plugin";
		require_once(_plugins_."/".$name."/".$plugin_class.".php");
				
		$pluginApp=new $plugin_class();
		if(method_exists($pluginApp, 'deactivate')){
			$pluginApp->deactivate();
		}
	}
	
	public function setupPlugin($plugin_id, $active){
		if($active == 1){
			$this->activatePlugin($plugin_id);
		}
		else{
			$this->deactivatePlugin($plugin_id);
		}
		
		$reSetting = sql_query("
			UPDATE ".$this->table."
			SET active=".$active."
			WHERE plugin_id = '".$plugin_id."'");
		
		return $reSetting;
	}

	public function printPageWithElement($id, $canonical_name){
		require_once(_base_.'/lib/lib.form.php');
		
		$lang =& DoceboLanguage::createInstance('configuration', 'framework');

		$reSetting = sql_query("
			SELECT *
			FROM ".$this->table."
			WHERE name like '".$canonical_name."'");
		
		list($plugin_id, $name, $title, $code, $category, $version, $author, $link, $priority, $description, $active ) = sql_fetch_row( $reSetting );
		
		$active_option = array(
						'1' => $lang->def('_ACTIVE_PLUGIN'),
						'0' => $lang->def('_NOT_ACTIVE_PLUGIN')
					);
		
		$priority_option = array_keys($this->plugins);
		
		$active=(empty($active) ? 0 : 1);

		echo '<h2>'.$title.'</h2>'
			 .Form::openElementSpace()
			 .Form::getHidden('active_tab_'.$plugin_id, 'active_tab', $plugin_id);

		echo Form::getLineBox($lang->def('_PLUGIN_AUTHOR'), $author);
		echo Form::getLineBox($lang->def('_PLUGIN_LINK'), $link);
		echo Form::getLineBox($lang->def('_PLUGIN_DESCR'), $description);
		echo Form::getLineBox($lang->def('_PLUGIN_VERSION'), $version);
		echo Form::getDropdown( $lang->def('_PLUGIN_PRIORITY') ,
									strtolower("priority_".$canonical_name),
									'priority['.$plugin_id.']',
									$priority_option,
									$id);
		echo Form::getDropdown( $lang->def('_ACTIVE_STATUS') ,
									strtolower($canonical_name),
									'active['.$plugin_id.']',
									$active_option,
									$active);
	}
	
	public function saveElement($plugin_id){
		$old_status=$this->getPluginStatus($plugin_id);
		
		$active=$_POST["active"][$plugin_id];
		$priority=$_POST["priority"][$plugin_id];
		
		$info=array('priority' => $priority);
		
		if($active !== $old_status){
			return $this->setupPlugin($plugin_id, $active) && $this->saveConf($plugin_id, $info);
		}
		else{
			return $this->saveConf($plugin_id, $info);
		}
	}
	
	public function saveConf($plugin_id, $info){
		$priority=$info['priority'];
		$old_priority=$this->get_priority($plugin_id);
		
		if($priority>$old_priority){
			$resPr=sql_query("UPDATE ".$this->table."
						SET priority=priority-1
						WHERE priority<=$priority AND priority>$old_priority");
		}
		elseif ($priority<$old_priority){
			$resPr=sql_query("UPDATE ".$this->table."
						SET priority=priority+1
						WHERE priority>=$priority and priority<$priority");
		}
		else $resPr=true;
		
		$res=sql_query("UPDATE ".$this->table."
						SET priority = $priority
						WHERE plugin_id=".$plugin_id);
		return $res && $resPr;
	}
	
	public function get_priority($plugin_id){
		$res=sql_query("SELECT priority from ".$this->table."
						WHERE plugin_id=".$plugin_id);
		
		list($priority)=sql_fetch_row( $res );
		
		return $priority;
	}
	
	public function getPluginStatus($plugin_id){
		$reSetting = sql_query("
			SELECT active
			FROM ".$this->table."
			WHERE plugin_id =".$plugin_id);
		
		list($active ) = sql_fetch_row( $reSetting );
		
		return $active;
		
	}
	
	public function getPluginsInfo($plugins){
		$plugin_info=array();
		
		$plugin_list=join("','",$plugins);
		
		$reSetting = sql_query("
			SELECT *
			FROM ".$this->table."
			where name in ('".$plugin_list."')");
		
		while(list($plugin_id, $name, $title, $code, $version, $author, $link, $priority, $description, $active ) = 
				sql_fetch_row( $reSetting )){
			$plugin_info[$name]['id']=$plugin_id;
			$plugin_info[$name]['title']=$title;
			$plugin_info[$name]['code']=$code;
			$plugin_info[$name]['version']=$version;
			$plugin_info[$name]['author']=$author;
			$plugin_info[$name]['link']=$link;
			$plugin_info[$name]['description']=$description;
			$plugin_info[$name]['active']=$active;
			$plugin_info[$name]['priority']=$priority;
		}
		
		return $plugin_info;
	}
	
	public function getMainView($name){
		switch ($name){
			case 'all':
				$plugins=$this->getInstalledPlugins();
				foreach ($plugins as $key => $plugin_name) {
					$this->getMainView($plugin_name);
				}
			break;
			default:
				$plugin_class=ucfirst($name)."Plugin";
				require_once(_plugins_."/".$name."/".$plugin_class.".php");
				
				$pluginApp=new $plugin_class();
				if(method_exists($pluginApp, 'getMainView')){
					echo "<ul class='yui-nav'><li class='first'><strong>Plugin ".$name."</strong></li></ul>";
					$pluginApp->getMainView();
				}
			break;
		}
	}
	/**
	 * @return 	array 	this array contains valid plugins conference installed on formalms
	 *
	 * @access 	public
	 */
	public function getList($params = Array()) {
		if (isset($params['active'])){
			$extrawhere = " and active = ".$params['active']." ";
		}

    $query = "SELECT * FROM ".$this->table." WHERE 0=0 and category = '".$this->CATEGORY."' $extrawhere ORDER BY active desc, priority";    
    $re = $this->db->query($query);

		while($row = sql_fetch_assoc($re)){
			$plugins[]=$row;
		}

		return $plugins;
	}

	/**
	 * @return 	array 	this array contains valid plugins conference installed on formalms
	 *
	 * @access 	public
	 */
	public function getElement($id, $type_id="plugin_id") {
    switch($type_id){
      case "plugin_id":
        $where_id = " and plugin_id = ".$id;
        break;
      case "code":
        $where_id = " and code = '".$id."' ";        
        break;
    }

    $query = "SELECT * FROM ".$this->table." WHERE category = '".$this->CATEGORY."' ".$where_id;    
    $re = $this->db->query($query);

		$row = sql_fetch_assoc($re);

		return $row;
	}
}

?>