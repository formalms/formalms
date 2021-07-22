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

class TreeWidget extends Widget {

	public $id = "";
	public $languages = array();
	public $ajaxUrl = "";
	public $show = false;
	public $dragDrop = false;
	public $styleSheets = array();
	public $rootNodeId = false;
	public $rootActions = false;

	/**
	 * Params for the selector mode
	 */
	public $useCheckboxes = false;
	public $initialSelectorData = array();
	public $hiddenSelection = false;
	public $canSelectRoot = true;
	public $setSelectedNodeOnServer = true;

	public $treeClass = "FolderTree";
	public $treeFile = false;

	public $initialSelectedNode = 0;
	public $rel_action = "";

	public $runtime = false;

	protected $json;
	protected $options = array();

	public function __construct() {
		parent::__construct();
		$this->_widget = 'tree';
		$this->json = new Services_JSON();
	}

	
	/**
	 * Initialize required libraries
	 */
	public function init() {
		YuiLib::load('base,treeview,selector');
		Util::get_js(Get::rel_path('base').'/lib/lib.elem_selector.js', true, true);
		Util::get_js(Get::rel_path('base').'/lib/js_utils.js', true, true);

		Util::get_css('base-folder-tree.css', false, true);

		$js_path = Get::rel_path('base').'/widget/tree/';
		if ($this->dragDrop) Util::get_js($js_path.'ddnode.js', true, true);
		Util::get_js($js_path.'foldernode.js', true, true);
		Util::get_js($js_path.'foldertree.js', true, true);
		if ($this->treeClass != "FolderTree" && $this->treeFile) {
			Util::get_js($this->treeFile, true, true);
		}
	}
	

	/*
	 * set params and render tree view
	 */
	public function run() {

		if (!$this->id) return false;
		
		$params = array(
			'id' => $this->id,
			'treeClass' => $this->treeClass,
			'rel_action' => $this->rel_action
		);

		if ($this->ajaxUrl && is_string($this->ajaxUrl)) $params['ajaxUrl'] = $this->ajaxUrl;

		if (is_array($this->languages) && count($this->languages) > 0)
			$params['languages'] = $this->json->encode($this->languages);
		else
			$params['languages'] = '{}';

		//other options
		$this->setOption('initialSelectedNode', $this->initialSelectedNode);
		$this->setOption('iconPath', Get::tmpl_path().'images/');
		$this->setOption('dragdrop', (bool)$this->dragDrop);

		$this->setOption('useCheckboxes', $this->useCheckboxes);
		$this->setOption('initialSelectorData', $this->initialSelectorData);
		$this->setOption('hiddenSelection', $this->hiddenSelection);
		$this->setOption('canSelectRoot', (bool)$this->canSelectRoot);
		$this->setOption('setSelectedNodeOnServer', (bool)$this->setSelectedNodeOnServer);

		$this->setOption('addFolderButton', $this->id.'_add_folder_button');

		if ($this->rootNodeId !== false) $this->setOption('rootNodeId', $this->rootNodeId);
		if (!empty($this->rootActions) && is_array($this->rootActions)) $this->setOption('rootActions', $this->rootActions);

		$params['options'] = $this->getJsOptions();

		switch ($this->show) {
			case 'explorer': $view = "explorer"; break;
			default: $view = "tree";
		}

		$params['runtime'] = (bool)$this->runtime;

		$this->render($view, $params);
	}


	/**
	 * get option to pass to tree object in the view
	 */
	protected function getJsOptions() {
		$this->setOption('ajax_url', $this->ajaxUrl);
		$this->setOption('langs', $this->languages);
		$arr_js = array();
		foreach ($this->options as $name=>$option) $arr_js[] = $name.':'.$this->json->encode($option);
		return '{'.implode(",", $arr_js).'}';
	}


	/**
	 * add an option
	 */
	public function setOption($name, $value) {
		$this->options[$name] = $value;
	}

}
