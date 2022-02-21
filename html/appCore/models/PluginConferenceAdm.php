<?php defined("IN_FORMA") or die('Direct access is forbidden.');



include_once(dirname(__FILE__)."/PluginmanagerAdm.php");

class PluginConferenceAdm extends PluginManagerAdm {


	public function  __construct() {
		parent::__construct();
  		$this->CATEGORY = 'conference';
	}
}

?>