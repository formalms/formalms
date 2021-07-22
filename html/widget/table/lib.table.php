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

define('_YUITABLE_ASC', 'asc');
define('_YUITABLE_DESC', 'desc');

class TableWidget extends Widget {

	public $id = "";
	public $ajaxUrl = "";
	public $rowsPerPage = false;
	public $startIndex = false;
	public $results = false;
	public $sort = false;
	public $dir = false;
	public $columns = array();
	public $fields = array();
	public $languages = array();
	public $paginator = false;
	public $rel_actions = '';
	public $filter = array();
	public $generateRequest = false;
	public $events = array();

	public $row_per_page_select = false;
	public $use_paginator = true;

	//these are used to enable scrollbars
	public $scroll_x = false;
	public $scroll_y = false;

	public $print_table_over = true;
	public $print_table_below = true;

	public $stdSelection = false;
	public $stdSelectionHidden = false;
	public $stdSelectionField = '';
	public $delDisplayField = false;
	public $selectAllAdditionalFilter = false;
	public $initialSelection = false;
	public $stdDialogIcons = false;

	//these can be used to force the loading of standard formatters, it may be useful in particular cases
	public $useStdModifyFormatter = false;
	public $useStdDeleteFormatter = false;

	//these can be used to set events of the table, they contains a reference to a javascript function
	public $stdDeleteCallbackEvent = false;
	public $stdModifyRenderEvent = false;
	public $stdModifyDestroyEvent = false;

	public $caption = '';
	public $summary = '';
	
	public $styles = '';
	public $header = '';
	public $data = '';

	// Event listener
	public $editorSaveEvent = false;

	/**
	 * View that you want to use
	 * @var <string>
	 */
	public $show = 'static';

	protected $json = null;

	/**
	 * Constructor
	 * @param <string> $config the properties of the table
	 */
	public function __construct() {
		parent::__construct();
		$this->_widget = 'table';
		$this->json = new Services_JSON();
	}



	/**
	 * Include the required libraries in order to have all the things ready and working
	 */
	public function init() {

		$libs = 'base,table';
		if ($this->_useStdDeleteFormatter() || $this->_useStdModifyFormatter()) {
			$libs .= ',button,container,selector';
		}

		// load yui
		YuiLib::load($libs);

		// Selector class
		Util::get_js(Get::rel_path('base').'/widget/dialog/dialog.js', true,true);

		// Selector class
		Util::get_js(Get::rel_path('base').'/lib/lib.elem_selector.js', true,true);

		// Commodities functions
		Util::get_js(Get::rel_path('base').'/lib/js_utils.js', true,true);

		// The tableview main file
		Util::get_js(Get::rel_path('base').'/widget/table/table.js', true,true);

		// Datatable css
		Util::get_css('../yui-skin/datatable.css', false, true);
	}

	

	public function run() {

		//validate parameters
		if (!$this->id) return false;

		if (!is_numeric($this->rowsPerPage) || $this->rowsPerPage <= 0) $this->rowsPerPage = Get::sett('visu_item', 25);

		if (!is_numeric($this->startIndex)) $this->startIndex = 0;

		if (!is_numeric($this->results) || $this->results <= 0) $this->results = $this->rowsPerPage;

		if (!$this->sort) $this->sort = "";

		if ($this->dir != _YUITABLE_ASC && $this->dir != _YUITABLE_DESC) $this->dir = _YUITABLE_ASC;

		if (!is_array($this->paginator)) {
			$lang =& DoceboLanguage::CreateInstance('standard', 'framework');
			$this->paginator = array(
				'template' => "{FirstPageLink} {PreviousPageLink} {PageLinks} {NextPageLink} {LastPageLink} "
					."{RangeRecords} ".$lang->def('_OF')." <strong>{TotalRecords}</strong>"
					." {RowsPerPageDropdown}",
				'rowsPerPageOptions' => '['.Get::sett('rows_per_page_options', '10, 25, 50, 100').']',
				'containers'  => array($this->id.'_pag_over', $this->id.'_pag_below'),
				'pageLinks' => 5,
				'firstPageLinkLabel' => "&laquo; ".$lang->def('_START'),
				'previousPageLinkLabel' => "&lsaquo; ".$lang->def('_PREV'),
				'nextPageLinkLabel' => $lang->def('_NEXT')." &rsaquo;",
				'lastPageLinkLabel' => $lang->def('_END')." &raquo;"
			);
		}

		//set parameters
		$params = array(
			'id' => $this->id,
			'ajaxUrl' => $this->ajaxUrl,
			'rowsPerPage' => $this->rowsPerPage,
			'startIndex' => $this->startIndex,
			'results' => $this->results,
			'sort' => $this->sort,
			'dir' => $this->dir,
			'columns' => $this->_getColumns(),
			'fields' => $this->_getFields(),
			'languages' => $this->_getLanguages(),
			'rel_actions' => $this->rel_actions,
			'generateRequest' => $this->generateRequest,
			'use_paginator' => $this->use_paginator,
			
			'caption' => $this->caption,
			'summary' => $this->summary,
			'print_table_over' => $this->print_table_over && !empty($this->rel_actions) || $this->use_paginator,
			'print_table_below' => $this->print_table_below && !empty($this->rel_actions) || $this->use_paginator,

			'styles' => $this->styles,
			'header' => $this->header,
			'data' => $this->data,
		);

		$params['filter'] = $this->_getFilter();

		if ($this->_useStdSelectFormatter()) {
			$params['useStdSelectFormatter'] = true;
			if (is_array($this->initialSelection) && count($this->initialSelection)>0)
				$params['initialSelection'] = $this->json->encode($this->initialSelection);
			if ($this->selectAllAdditionalFilter != false)
				$params['selectAllAdditionalFilter'] = $this->selectAllAdditionalFilter;
		} else
			$params['useStdSelectFormatter'] = false;


		if ($this->_useStdModifyFormatter()) {
			$params['useStdModifyFormatter'] = true;
			if ($this->stdModifyRenderEvent) $params['stdModifyRenderEvent'] = $this->stdModifyRenderEvent;
			if ($this->stdModifyDestroyEvent) $params['stdModifyDestroyEvent'] = $this->stdModifyDestroyEvent;
		} else
			$params['useStdModifyFormatter'] = false;

		if($this->_useEditor()) {

			$this->events['rowMouseoverEvent'] = 'oDt.onEventHighlightCell';
			$this->events['rowMouseoutEvent'] = 'oDt.onEventUnhighlightCell';
			$this->events['cellClickEvent'] = 'oDt.onEventShowCellEditor';
		}

		if ($this->_useStdDeleteFormatter()) {
			$params['useStdDeleteFormatter'] = true;
			if ($this->stdDeleteCallbackEvent) $params['stdDeleteCallbackEvent'] = $this->stdDeleteCallbackEvent;
		} else
			$params['useStdDeleteFormatter'] = false;

		if ($this->_useStdDialogFormatter())
			$params['useStdDialogFormatter'] = true;
		else
			$params['useStdDialogFormatter'] = false;

		if ($this->_useDupFormatter())
			$params['useDupFormatter'] = true;
		else
			$params['useDupFormatter'] = false;


		if (is_array($this->paginator))  {
			$paginatorConfig = "";
			if (isset($this->paginator['template'])) $paginatorConfig .= ', template: "'.$this->paginator['template'].'"';
			if (isset($this->paginator['rowsPerPageOptions'])) $paginatorConfig .= ', rowsPerPageOptions: '.( $this->row_per_page_select == false ? $this->paginator['rowsPerPageOptions'] : $this->row_per_page_select ) .'';
			if (isset($this->paginator['containers'])) $paginatorConfig .= ', containers: ["'.implode('","', $this->paginator['containers']).'"]';
			if (isset($this->paginator['pageLinks'])) $paginatorConfig .= ', pageLinks: '.$this->paginator['pageLinks'];
			if (isset($this->paginator['firstPageLinkLabel'])) $paginatorConfig .= ', firstPageLinkLabel: "'.$this->paginator['firstPageLinkLabel'].'"';
			if (isset($this->paginator['previousPageLinkLabel'])) $paginatorConfig .= ', previousPageLinkLabel: "'.$this->paginator['previousPageLinkLabel'].'"';
			if (isset($this->paginator['nextPageLinkLabel'])) $paginatorConfig .= ', nextPageLinkLabel: "'.$this->paginator['nextPageLinkLabel'].'"';
			if (isset($this->paginator['lastPageLinkLabel'])) $paginatorConfig .= ', lastPageLinkLabel: "'.$this->paginator['lastPageLinkLabel'].'"';
			$params['paginatorConfig'] = $paginatorConfig;
		}
		
		//events handler
		$params['editorSaveEvent'] = $this->editorSaveEvent;
		//choose a view by table specification
		switch ($this->show) {
			default: $view = ( $this->ajaxUrl ? 'dynamic' : 'static' ); break;
		}

		$params['events'] = (is_array($this->events) ? $this->events : array());

		if ($this->delDisplayField) $params['delDisplayField'] = $this->delDisplayField;


		if ($this->scroll_x) $params['scroll_x'] = $this->scroll_x;
		if ($this->scroll_y) $params['scroll_y'] = $this->scroll_y;


		if (is_array($this->stdDialogIcons) && !empty($this->stdDialogIcons)) $params['stdDialogIcons'] = $this->stdDialogIcons;

		//render the view
		$this->render($view, $params);
	}



	protected function _getLanguages() {
		return '{}';
	}

	protected function _getFormatters() {
		return '[]';
	}

	/**
	 * Compile the js configuration for the yui datatable colum definition
	 * based on the php array populated by the __construct
	 * @return <string>
	 */
	protected function _getColumns() {
		if ($this->stdSelection) {
			$label = Form::getInputCheckbox($this->id.'_head_select', '', 1, false, '');
			$columnsList = array( array('key'=>'_select', 'label'=>$label, 'formatter'=>'doceboSelect', 'className'=>'img-cell') );
			for ($i=0; $i<count($this->columns); $i++) {
				$columnsList[] = $this->columns[$i];
			}
		} else {
			$columnsList = $this->columns;
		}


		
		if(!is_array($columnsList)) return '[]';

		$cols = array();
		foreach ($columnsList as $column) {

			$col_config = array();
			foreach ($column as $key => $value) {
				switch ($key) {
					/*case 'sortable' : {
						$col_config[] = 'sortable:'.($value ? 'true' : 'false');
					};break;*/
					case 'formatter' : {
						switch ($value) {
							case 'doceboSelect': $formatter = '"stdSelect"'; break;
							case 'doceboModify': $formatter = '"stdModify"'; break;
							case 'doceboDelete': $formatter = '"stdDelete"'; break;//'YAHOO.widget.DataTable.Formatter.stdDelete'; break;
							case 'doceboDialog': $formatter = '"stdDialog"'; break;
							case 'stdSelect': $formatter = '"stdSelect"'; break;
							case 'stdModify': $formatter = '"stdModify"'; break;
							case 'stdDelete': $formatter = '"stdDelete"'; break;
							case 'stdDialog': $formatter = '"stdDialog"'; break;
							case 'dup': $formatter = '"dup"'; break;
							default: $formatter = $value; break;
						}
						$col_config[] = 'formatter:'.$formatter;
					};break;
					case 'editor' : {
						$col_config[] = 'editor:'.$value;
					};break;
					case 'hidden' : {
						$col_config[] = 'hidden:'.((bool)$value ? 'true' : 'false');
					};break;
					default : {
						$col_config[] = $key.':'.$this->json->encode($value);//$key.':"'.$value.'"';
					}
				} // end switch
			}
			$cols[] = implode(",", $col_config);
		} // end colums while

		return '['.(count($cols)>0 ? '{'.implode("},\n{", $cols).'}' : '').']';
	}

	//return json for fields list for the datasource
	protected function _getFields() {
		if ($this->stdSelection && $this->stdSelectionField) $this->fields[] = $this->stdSelectionField;
		$converted = $this->json->encode($this->fields);
		return ($converted ? $converted : '[]' );
	}

	protected function _getFilter() {
		$output = "";
		if (is_array($this->filter)) {
			$f_arr = array();
			foreach ($this->filter as $key=>$value) {
				$f_arr[] = $key.':'.$this->json->encode($value);
			}
			$output .= '{'.implode(',', $f_arr).'}';
		} else {
			$output .= '{}';
		}
		return $output;
	}

	protected function _useStdDeleteFormatter() {
		if ($this->useStdDeleteFormatter) return true;
		$result = false;
		if (is_array($this->columns)) {
			foreach ($this->columns as $column) {
				if (isset($column['formatter']) && ($column['formatter']=='"stdDelete"' || $column['formatter']=='stdDelete' || $column['formatter']=='doceboDelete')) {
					$result = true;
					break;
				}
			}
		}
		return $result;
	}

	protected function _useStdModifyFormatter() {
		if ($this->useStdModifyFormatter) return true;
		$result = false;
		if (is_array($this->columns)) {
			foreach ($this->columns as $column) {
				if (isset($column['formatter']) && ($column['formatter']=='"stdModify"' || $column['formatter']=='stdModify' || $column['formatter']=='doceboModify')) {
					$result = true;
					break;
				}
			}
		}
		return $result;
	}

	protected function _useEditor() {
		$result = false;
		if (is_array($this->columns)) {
			foreach ($this->columns as $column) {
				if (isset($column['editor']) && $column['editor'] != '') {
					return true;
				}
			}
		}
		return $result;
	}

	protected function _useStdDialogFormatter() {
		$result = false;
		if (is_array($this->columns)) {
			foreach ($this->columns as $column) {
				if (isset($column['formatter']) && ($column['formatter']=='"stdDialog"' || $column['formatter']=='stdDialog' || $column['formatter']=='doceboDialog')) {
					$result = true;
					break;
				}
			}
		}
		return $result;
	}

	protected function _useDupFormatter() {
		$result = false;
		if (is_array($this->columns)) {
			foreach ($this->columns as $column) {
				if (isset($column['formatter']) && ($column['formatter']=='"dup"' || $column['formatter']=='dup' || $column['formatter']=='dup')) {
					$result = true;
					break;
				}
			}
		}
		return $result;
	}

	protected function _useStdSelectFormatter() { return $this->stdSelection;
		/*
		$result = false;
		if (is_array($this->columns)) {
			foreach ($this->columns as $column) {
				if (isset($column['formatter']) && $column['formatter']=="doceboSelect") {
					$result = true;
					break;
				}
			}
		}
		return $result;
		*/
	}

}

?>