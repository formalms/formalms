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

abstract class LmsPluginController extends LmsController {

	protected $_mvc_name = 'lmsplugincontroller';
	protected $_plugin_name = '';
    
	public function viewPath() {
		return _plugins_.'/'.$this->_plugin_name.'/'._folder_lms_.'/views/';
	}

	public function viewCustomscriptsPath() {
		return _plugins_.'/'.$this->_plugin_name.'/customscripts/'._folder_lms_.'/views/';
	}

}
