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

class YuilogWidget extends Widget {

	public $div ='';

	/**
	 * Constructor
	 * @param <string> $config the properties of the table
	 */
	public function __construct() {
		parent::__construct();
		$this->_widget = 'yuilog';
	}


	public function run() {
		if (Get::cfg('do_debug')) {

			$this->div =(!empty($this->div) ? $this->div : 'yui_log_container');

			$this->render('yuilog',
				array(
					'div'=>$this->div,
				)
			);
		}
		else {
			$this->render('yuilog_off');
		}
	}


	/**
	 * Include the required libraries in order to have all the things ready and working
	 */
	public function init() {
		Util::get_js(Get::rel_path('base').'/addons/yui/logger/logger-min.js', true, true);
		Util::get_css(Get::rel_path('base').'/templates/standard/yui-skin/logger.css', true, true);
	}


}

?>