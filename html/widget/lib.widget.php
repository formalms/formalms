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

class Widget {

	protected $_widget = '';

	public function  __construct() {}

	public function init() {}

	public function render($view_name, $data_for_view = false) {

		if(is_array($data_for_view)) {
			extract($data_for_view, EXTR_SKIP);
		} else {
			$data_for_view = $data_for_view;
		}
		include( dirname(__FILE__).'/'.$this->_widget.'/views/'.$view_name.'.php' );
	}

	public function beginWidget() {}

	public function endWidget() {}

	public function run() {}


	/**
	 * Loads another widget
	 * @param <type> $widget_name
	 * @param <type> $params
	 */
	public function widget($widget_name, $params = null) {

		$widget_name = strtolower($widget_name);
		require_once(_base_.'/widget/'.$widget_name.'/lib.'.$widget_name.'.php');

		$widget_class = ucfirst($widget_name.'Widget');
		// Instantiate the widget class
		$widget_obj = new $widget_class();
		// Set the params for the widget in the class properties
		if (is_array($params)) {
			foreach ($params as $key => $value) {
				$widget_obj->$key = $value;
			}
		}
		// Initialize the widget
		$widget_obj->init();
		// Run the the widget (will print the view)
		$widget_obj->run();
		// Return the widget for further use
		return $widget_obj;
	}


}
