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

class forma_TablefilterWidget extends Widget {

	public $id = '';
    public $language = '';
	public $filter_text = "";
	public $auxiliary_filter = "";
    public $list_category = "";
	public $common_options = "";
	public $advanced_filter_content = false;
	public $advanced_filter_active = false;
	public $css_class = false;

    public $inline_filters = '';

	public $js_callback_set = false;
	public $js_callback_reset = false;

	protected $json = null;


	public function __construct() {
		parent::__construct();
		$this->_widget = 'forma_tablefilter';
	}

	public function init() {
        // default value
        $this->language->sSearch = Lang::t('_SEARCH', 'standard');
        $this->language->pagingType = 'full_numbers';
        $j->sFirst =  Lang::t('_START','standard');
        $j->sPrevious = Lang::t('_PREV_B','standard');
        $j->sNext  = Lang::t('_NEXT','standard');
        $j->sLast  = Lang::t('_LAST','standard');
        $this->language->oPaginate = $j;

        
	}

	public function run() {
		if (!$this->id) {
			//..
			return false;
		}
		//render view
		$this->render('forma_tablefilter', array(
			'id' => $this->id,
            'language' => json_encode($this->language),
			'filter_text' => (string)$this->filter_text,
            'list_category' => $this->list_category,
			'auxiliary_filter' => $this->auxiliary_filter,
            'inline_filters' => $this->inline_filters,
			'common_options' => $this->common_options,
			'advanced_filter_content' => $this->advanced_filter_content,
			'advanced_filter_active' => $this->advanced_filter_active,
			'css_class' => $this->css_class,
			'js_callback_set' => $this->js_callback_set,
			'js_callback_reset' => $this->js_callback_reset
		));
	}
}

?>