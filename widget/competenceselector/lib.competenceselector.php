<?php defined("IN_DOCEBO") or die('Direct access is forbidden.');

/* ======================================================================== \
| 	DOCEBO - The E-Learning Suite											|
| 																			|
| 	Copyright (c) 2010 (Docebo)												|
| 	http://www.docebo.com													|
|   License 	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt		|
\ ======================================================================== */

class CompetenceselectorWidget extends Widget {

	public $id = '';
	public $selected_category = 0;
	public $filter_text = "";
	public $show_descendants = false;
	public $selection = array();

	protected $json = null;


	public function __construct() {
		parent::__construct();
		$this->_widget = 'competenceselector';
	}

	public function init() {
		require_once(_base_.'/lib/lib.json.php');
		$this->json = new Services_JSON();

		Util::get_js(Get::rel_path('base').'/lib/lib.elem_selector.js', true, true);
		Util::get_js(Get::rel_path('base').'/lib/js_utils.js', true, true);

		//$js_path = Get::rel_path('base').'/widget/competenceselector/';
		//Util::get_js($js_path.'competenceselector.js', true, true);
	}

	public function run() {
		if (!$this->id) {
			//..
			return false;
		}
		
		//render view
		$this->render('competenceselector', array(
			'id' => $this->id,
			'selected_node' => (int)$this->selected_category,
			'filter_text' => (string)$this->filter_text,
			'show_descendants' => $this->show_descendants,
			'selection' => $this->selection
		));
	}

}

?>