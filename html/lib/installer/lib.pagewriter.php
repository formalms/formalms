<?php

Class PageWriter {

	protected $_cont = array('main'=>'');
	protected $_zone ='main';
	protected $_id ='';

	
	function  __construct() {
		$this->_id=uniqid();
	}
	
	
	public static function init() {
		if (!isset($GLOBALS['page'])) {
			$GLOBALS['page'] =new PageWriter();
		}
	}
	
	
	public function add($txt, $zone=false, $add_nl=true) {
		$zone =(empty($zone) ? $this->_zone : $zone);
		if (!isset($this->_cont[$zone])) {
			$this->_cont[$zone] ='';
		}
		$this->_cont[$zone] .=$txt.($add_nl ? "\n" : '');
	}

	public function getZoneContent($zone=false) {
		$zone =(empty($zone) ? $this->_zone : $zone);
		return ($this->_cont[$zone]);
		echo ('<!--- [pagewriter:'.$this->_id.'_'.$zone.'] --->');
	}
	
	
	public function setZone($zone) {
		$this->_zone =$zone;
	}
	
	
	public function render($contents) {
		foreach($this->_cont as $key=>$val) {
			$contents =str_replace('<!--- [pagewriter:'.$this->_id.'_'.$key.'] --->', $val, $contents);
		}
		
		return $contents;
	}
	
}


function cout($txt, $zone='main') {	
	$GLOBALS['page']->add($txt);
}


function getZoneContent($zone=false) {
	return ($GLOBALS['page']->getZoneContent($zone));
}


?>