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
 * This widget is used inorder to render and use the user selectors.
 * In it you can find a selector for : user, groups, organization chart folders and functional role
 * The output selected can be a list of user or a list of selected "entities"
 * @since 4.0
 */
class UserselectorWidget extends Widget {

	/**
	 * The id of the selector, facoltative (necessary if a page uses more than one selector)
	 * @var id
	 */
	public $id = '';

	public $id_org = null;
	public $org_type = null;

	/**
	 * Display the user selector tab
	 * @var bool
	 */
	public $show_user_selector = true;

	/**
	 * Display the groups selector tab
	 * @var bool
	 */
	public $show_group_selector = true;

	/**
	 * Display the organization chart selector tab
	 * @var bool
	 */
	public $show_orgchart_selector = true;
        
        
        
        
        
	public $show_orgchart_simple_selector = false;

	/**
	 * Display the functional role selector tab
	 * @var bool
	 */
	public $show_fncrole_selector = true;

	/**
	 * Show suspended user from
	 * @var bool
	 */
	public $use_suspended = false;

	/**
	 * optional: The first active tab ('user', 'group', 'orgchart', 'fncrole')
	 * @var string
	 */
	public $selected_tab = false;
	
	/**
	 * Use the widget as a form input (creates an input hidden in which store the selection with json)
	 * @var bool
	 */
	public $use_form_input = true; //
	public $separate_input = false; //...
	public $initial_selection = false; //the initial selected users/groups/orgcharts

	public $can_select_root = true; //this allow the radiobuttons on the root node of the orgchart selector

	public $admin_filter = true;

	public $learning_filter = 'none';
	public $nFields = 3;

	protected $util = null;
	protected $json = null;

	public function __construct() {
		parent::__construct();
		$this->_widget = 'userselector';
	}


	/*
	 * load necessary libraries and javascript code
	 */
	public function init() {
		require_once(_base_.'/lib/lib.userselector.php');
		$this->util = new UserSelectorUtil();
		$this->json = new Services_JSON();

		YuiLib::load('base,tabview,tree,datatable,selector');
		Util::get_js(Get::rel_path('base').'/lib/lib.elem_selector.js', true, true);
		Util::get_js(Get::rel_path('base').'/lib/js_utils.js', true, true);

		Util::get_css('base-folder-tree.css', false, true);

		$js_path = Get::rel_path('base').'/widget/tree/';
		Util::get_js($js_path.'foldernode.js', true, true);
		Util::get_js($js_path.'foldertree.js', true, true);
		Util::get_js($js_path.'selectortree.js', true, true);

		$js_path = Get::rel_path('base').'/widget/userselector/';
		Util::get_js($js_path.'userselector.js', true, true);
	}


	/*
	 * render the selector
	 */
	public function run() {

		$num_tabs = 0;
		$selected_tab = false;
		if ($this->show_fncrole_selector) { $num_tabs++; $selected_tab = 'fncrole'; }
		if ($this->show_orgchart_selector) { $num_tabs++; $selected_tab = 'orgchart'; }
		if ($this->show_group_selector) { $num_tabs++; $selected_tab = 'group'; }
		if ($this->show_user_selector) { $num_tabs++; $selected_tab = 'user'; }

		if ($num_tabs <= 0 || !$selected_tab) return; //no tabs to display: do nothing
		
		//set selected tab, if specified a valid one
		$_check_tab = strtolower($this->selected_tab);
		switch ($_check_tab) {
			case 'user':
			case 'group':
			case 'orgchart':
			case 'fncrole': $selected_tab = $_check_tab; break;
		}

		$_selection = array();
		if (is_array($this->initial_selection) && count($this->initial_selection)>0) {
			$_selection = $this->util->getInitialSelFromIdst($this->initial_selection);
		}

		//validate tab configuration
		$initial_selection = array(
			'user' => (isset($_selection['user']) ? $_selection['user'] : array()),
			'group' => (isset($_selection['group']) ? $_selection['group'] : array()),
			'orgchart' => (isset($_selection['orgchart']) ? $this->_filterOrgchartSelection($_selection['orgchart']) : array()),
			'fncrole' => (isset($_selection['fncrole']) ? $_selection['fncrole'] : array()),
		);

		
		//set view parameters
		$params = array(
			'id' => $this->id,
			'show_user_selector' => $this->show_user_selector,
			'show_group_selector' => $this->show_group_selector,
			'show_orgchart_selector' => $this->show_orgchart_selector,
			'show_orgchart_simple_selector' => $this->show_orgchart_simple_selector,
			'show_fncrole_selector' => $this->show_fncrole_selector,
			'selected_tab' => $selected_tab,
			'use_form_input' => (bool)$this->use_form_input,
			'separate_input' => (bool)$this->separate_input,
			'initial_selection' => $initial_selection,
			'ajax_url' => '../widget/ajax.widget.php?r=userselector/gettreedata' //fixed param
		);


		//single tabs specific parameters:

		//--- USER SELECTOR PARAMETERS ---------------------------------------------

		if ($this->show_user_selector) {
			require_once(_adm_.'/lib/lib.field.php');

			$fman = new FieldList();
			$fields = $fman->getFlatAllFields(array('framework', 'lms'));

			$f_list = array(
				'email'			=> Lang::t('_EMAIL', 'standard'),
				'lastenter'		=> Lang::t('_DATE_LAST_ACCESS', 'profile'),
				'register_date' => Lang::t('_DIRECTORY_FILTER_register_date', 'admin_directory'),
				'language' => Lang::t('_LANGUAGE', 'standard'),
				'level' => Lang::t('_LEVEL', 'standard')
			);
			$f_list = $f_list + $fields;
			$f_selected = $this->json->decode(Docebo::user()->getPreference('ui.directory.custom_columns'));
			if($f_selected == false) $f_selected = array('email', 'lastenter', 'register_date');

			$js_arr = array();
			foreach ($f_list as $key=>$value) $js_arr[] = $key.': '.$this->json->encode($value);
			$f_list_js = '{'.implode(',', $js_arr).'}';

			require_once(_adm_.'/lib/user_selector/lib.dynamicuserfilter.php');
			$dyn_filter = new DynamicUserFilter("user_dyn_filter");
			$dyn_filter->init();
			
			$user_config = new stdClass();
			$user_config->num_var_fields = $this->nFields; //$this->numVarFields,
			$user_config->fieldlist = $f_list;
			$user_config->fieldlist_js = $f_list_js;
			$user_config->selected = $f_selected;
			$user_config->use_suspended = $this->use_suspended;
			$user_config->show_suspended = false; //$this-> _getSuspendedFilter(),
			$user_config->filter_text = ""; //$this->_getTextFilter();
			$user_config->dynamic_filter = $dyn_filter;

			$params['user_config'] = $user_config;
		}

		//--- GROUP SELECTOR PARAMETERS --------------------------------------------

		if ($this->show_group_selector) {
			//...
		}

		//--- ORGCHART SELECTOR PARAMETERS -----------------------------------------

		if ($this->show_orgchart_selector) {
			$acl_man = Docebo::user()->getACLManager();
			$arr_idst = $acl_man->getArrGroupST(array('/oc_0', '/ocd_0'));

			$orgchart_config = new stdClass();
			$orgchart_config->root_node_id = $arr_idst['/oc_0'].'_'.$arr_idst['/ocd_0'];//$acl_man->getGroupST('oc_0').'_'.$acl_man->getGroupST('ocd_0');
			$orgchart_config->can_select_root = (bool)$this->can_select_root;

			$params['orgchart_config'] = $orgchart_config;
		}

		//--- FNCROLES SELECTOR PARAMETERS -----------------------------------------

		if ($this->show_fncrole_selector) {
			//...
		}

		
		$this->render('userselector', $params);
	}


	/*
	 * removes ambiguities between oc_* and ocd_* groups
	 */
	protected function _filterOrgchartSelection(&$selection) {
		//TO DO ...
		return $selection;
	}

}


?>