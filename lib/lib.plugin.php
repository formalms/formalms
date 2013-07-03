<?php defined("IN_DOCEBO") or die("You cannot access this file directly");

/* ======================================================================== \
|	DOCEBO - The E-Learning Suite											|
| 																			|
|	Copyright (c) 2011 (Docebo)												|
|	http://www.docebo.com													|
|	License 	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt		|
\ ======================================================================== */

class  Plugin {
	
	public static function img($plugin_name) {
		
		return Get::rel_path('plugins').'/'.$plugin_name.'/images';
	}
	
	public static function css($plugin_name, $css_name) {
		
		Util::get_css(Get::rel_path('plugins').'/'.$plugin_name.'/style/'.$css_name , true, true);
	}
	
	public static function js($plugin_name, $js_name) {
		
		Util::get_js(Get::rel_path('plugins').'/'.$plugin_name.'/js/'.$js_name , true, true);
	}
	
	public function install() {}
	
	public function uninstall() {}
	
}

class PluginException extends Exception {}
