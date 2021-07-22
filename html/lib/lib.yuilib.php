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

/**
 * The purpose of this class is to contain method that will help in the use of
 * ajax and web 2.0 interface using the yui libraries.
 * Will inclued load function (based on module ? i haven't takena  decision yet)
 * Some util function such as add a confirm dialog on all the elemnt that match
 * a reg exp and so on.
 */

class YuiLib {

	private static $_use_skin = 'docebo';

	private static $_css_loaded = array();

	private static $_js_loaded = array();

	private static $_css_map = array(
		'base' => array(
			'button.css',
			'calendar.css',
			'container.css',
			'menu.css',
			'resize.css'
		),
		'autocomplete' => array(
			'autocomplete.css'
		),
		'charts' => array(

		),
		'tabview' => array(
			'tabview.css'
		),
		'treeview' => array(
			'treeview.css'
		),
		'table' => array(
			'datatable.css',
			'paginator.css'
		),
		'colorpicker' => array(
			'colorpicker.css'
		)
	);

	private static $_js_map = array(
		'base' => array(
			'utilities/utilities.js',
			'json/json-min.js',
			'animation/animation-min.js',
			'animation/my_animation.js',
			'button/button-min.js',
			'calendar/calendar-min.js',
			'container/container-min.js',
			'menu/menu-min.js',
			'resize/resize-min.js',
			'selector/selector-min.js',
			'event-delegate/event-delegate-min.js',
		),
		'autocomplete' => array(
			'datasource/datasource-min.js',
			'autocomplete/autocomplete-min.js'
		),
		'charts' => array(
			'datasource/datasource-min.js',
			'charts/charts-min.js',
			'swf/swf-min.js'
		),
		'tabview' => array(
			// 'tabview/tabview-min.js'
			'tabview/tabview.js'
		),
		'treeview' => array(
			'treeview/treeview-min.js'
		),
		'table' => array(
			'datasource/datasource-min.js',
			'paginator/paginator-min.js',
			'datatable/datatable-min.js',
			'editor/editor-min.js'
		),
		'colorpicker' => array(
			'slider/slider-min.js',
			'colorpicker/colorpicker-min.js'
		)
	);

	/**
	 * Load css and yui file
	 * @return null
	 * @param $js Array[optional]
	 * @param $css Array[optional]
	 */
	public static function load($module_list = false, $noprint = false) {

		$module_list = 'base,autocomplete,charts,tabview,table,treeview,colorpicker';
		if(strpos($module_list, 'base') !== false) $module_list = 'base,'.$module_list;
		$list = explode(',', $module_list);

		$js_load = array();
		$css_load = array();
		foreach($list as $k => $module) {

			if(isset(self::$_css_map[$module])) $css_load = array_unique(array_merge($css_load, self::$_css_map[$module]));
			if(isset(self::$_js_map[$module])) $js_load = array_unique(array_merge($js_load, self::$_js_map[$module]));
		}
		// remove js alredy loaded
		$css_load = array_diff($css_load, self::$_css_loaded);
		$js_load = array_diff($js_load, self::$_js_loaded);

		if(empty($css_load) && empty($js_load)) return '';

		// load new css
		$to_load = '';
		if(!empty($css_load)) {
			$to_load .= '<!-- yui css -->';
			foreach($css_load as $k => $filename) {

				$to_load .= Util::get_css(Get::tmpl_path('base').'yui-skin/'.$filename, true);
			}
		}
		// load new js
		if(!empty($js_load)) {
			$to_load .= '<!-- yui js -->';
			foreach($js_load as $k => $filename) {

				$to_load .= Util::get_js('/addons/yui/'.$filename);
				if($filename == 'utilities/utilities.js') $to_load .= "\n".'<script type="text/javascript"> YAHOO.util.Connect.initHeader(\'X-Signature\',\''.Util::getSignature().'\'); YAHOO.util.Connect.startEvent.subscribe(function() { YAHOO.util.Connect.initHeader(\'X-Signature\',\''.Util::getSignature().'\'); });</script>';
				if($filename == 'charts/charts-min.js') $to_load .= "\n".'<script type="text/javascript"> YAHOO.widget.Chart.SWFURL = "'.Get::rel_path('base').'/addons/yui/charts/assets/charts.swf"; </script>';
			}
			if(Lang::direction() == 'rtl') $to_load .= Util::get_js('/addons/yui/yui-rtl.js');;
		}
		// add loaded file to the cache
		if(!empty($css_load)) self::$_css_loaded = array_merge(self::$_css_loaded, $css_load);
		if(!empty($js_load)) self::$_js_loaded = array_merge(self::$_js_loaded, $js_load);

		if(function_exists('cout') && !$noprint) cout($to_load, 'page_head');
		else return $to_load;
	}

	/**
	 * @return html to attach to the output
	 * @param $pattern String patter for the selector
	 * @param $title String[optional] the dialog title
	 * @param $text String[optional] the dialog text
	 * @param $confirm String[optional] the confirm button label
	 * @param $undo String[optional] the undo button label
	 */
	public static function attachHrefDialog($pattern, $title = false, $text = false, $confirm = false, $undo = false) {}


	public static function activateConnectLoadingBox() {
		$content = '<img src="'.getPathImage().'standard/loading_circle.gif" /><span>'. Lang::t('_LOADING').'</span>';
		cout('<script type="text/javascript">
				$C = YAHOO.util.Connect;
				$C.startEvent.subscribe(function() {
					var el = document.createElement("DIV");
					el.id = "container-loadbox";
					el.className = "container-loadbox";
					el.innerHTML = "'.addslashes($content).'";
					document.body.appendChild(el);
				});
				$C.completeEvent.subscribe(function() {
					var el = YAHOO.util.Dom.get("container-loadbox");
					el.parentNode.removeChild(el);
				});
		</script>', 'scripts');
	}

}

?>