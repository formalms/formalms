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

class TableView {

	public $id;
	public $useDOMReady = false;
	public $isGlobalVariable = false;

	protected $jsClassName = 'TableView';
	protected $serverUrl = '';

	protected $columns = null;
	protected $fields = null;
	protected $initialData = null;

	protected $otherOptions = array();
	protected $jsLanguages = array();

	protected $json = null;

	protected $formatters = array();


	protected $html_before = '';
	protected $html_after = '';

	/**
	 * Constructor
	 * @param <string> $id the identifier of the table
	 */
	public function __construct($id) {
		$this->id = $id;
		$this->json = new Services_JSON();

		$this->addFormatter("delete", 'deleteFormatter');
	}

	/**
	 * Include the required libraries in order to have all the things ready and working
	 */
	public function initLibraries() {

		// load yui
		YuiLib::load(array(
			'yahoo-dom-event'=>'yahoo-dom-event.js',
			'connection'=>'connection-min.js',
			'dragdrop'=>'dragdrop-min.js',
			'element'=>'element-beta-min.js',
			'animation'=>'animation-min.js',
			'json'=>'json-min.js',
			'container'=>'container_core-min.js', //menu
			'menu'=>'menu-min.js', //menu
			'button'=>'button-min.js', //dialog
			'container'=>'container-min.js', //dialog
			'button'=>'button-min.js', //dialog
			'treeview'=>'treeview-min.js',
			'resize'=>'resize-beta-min.js',
			'selector'=>'selector-beta-min.js'
		), array(
			'assets/skins/sam' => 'skin.css'
		));

		// Selector class
		Util::get_js(Get::rel_path('base').'/lib/lib.elem_selector.js', true,true);

		// Commodities functions
		Util::get_js(Get::rel_path('base').'/lib/js_utils.js', true,true);

		// The tableview main file
		Util::get_js(Get::rel_path('base').'/lib/table_view/tableview.js', true,true);

		// Datatable css
		Util::get_css('../yui-skin/datatable.css', false, true);
	}

	/**
	 * Set the url of the ajax server, used for ajax pagination and sorting
	 * @param <string> $url
	 * @return <type>
	 */
	public function setRequestUrl($url) {
		$this->serverUrl = $url;
	}

	/**
	 * Set the initial datatset for the yui datatable, the data must be
	 * populated in this way:
	 *	'startIndex' => <int>,
	 *	'results' => <int>,
	 *	'sort' => <string>,
	 *	'dir' => <string>,
	 *	'pageSize' => <int>,
	 *	'totalRecords' => <int>,
	 *	'recordsReturned' => <array>
	 * @param <array> $data
	 * @return <bool>
	 */
	public function setInitialData($data) {
		if (!is_array($data)) return false;
		//if (!is_array($this->initialData)) $this->initialData = array();
		//foreach ($data as $key=>$val)	$this->initialData[$key] = $val;
		$this->initialData = $data;
	}

	/**
	 * Compile the js configuration for the yui datatable colum definition
	 * based on the php array populated by the __construct
	 * @return <string>
	 */
	protected function createColumnsData() {

		//$converted = $this->json->encode($this->columns); //unable to convert formatters
		//return ($converted ? $converted : '[]' );
		
		if(!is_array($this->columns)) return '[]';

		$cols = array();
		foreach ($this->columns as $column) {

			$col_config = array();
			foreach ($column as $key => $value) {
				switch ($key) {
					/*case 'sortable' : {
						$col_config[] = 'sortable:'.($value ? 'true' : 'false');
					};break;*/
					case 'formatter' : {
						$col_config[] = 'formatter:'.$value;
					};break;
					case 'editor' : {
						$col_config[] = 'editor:'.$value;
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


	protected function createFieldsData() {

		$converted = $this->json->encode($this->fields);
		return ($converted ? $converted : '[]' );
		/*
		if (!is_array($this->fields)) {
			if (!is_array($this->columns)) {
				return '';
			} else {
				$this->fields = array();
				foreach ($this->columns as $column) {
					$this->fields[] = $column['key'];
				}
			}
		}
		
		$temp = array();
		foreach ($this->fields as $key=>$value) {
			$temp[] = $value;
		}
		return '"'.implode('","', $temp).'"';
		 */
	}


	protected function addFormatter($code, $function, $inObject = true) {
		$this->formatters[$code] = array('function'=>$function, 'in_object'=>$inObject);
	}

	protected function getCellFormatter($name) {
		$output = false;
		if (array_key_exists($name, $this->formatters)) {
			if ($this->formatters[$name]['in_object'])
				$output = "function(elCell, oRecord, oColumn, oData) { this._oTableView.".$this->formatters[$name]['function'].".call(this._oTableView, elCell, oRecord, oColumn, oData); }";
			else
				$output = $this->formatters[$name]['function'];
		}
		return $output;
	}

	protected function addOption($name, $value) {
		$this->otherOptions[$name] = $value;
	}


	//basic table drawing functions
	protected function _getJsOptions() {
		$options = array();

		$options[] = 'serverUrl:"'.$this->serverUrl.'"';

		//check if table has a initial set of values
		if (is_array($this->initialData)) {
			$json = new Services_JSON();
			$options[] = 'initialData:'.$json->encode($this->initialData);//'initialData:['.implode(",", $temp).']';
			$options[] = 'sendInitialRequest:false';
		} else {
			$options[] = 'sendInitialRequest:true';
		}

		//columns definition object
		$options[] = 'columns:'.$this->createColumnsData();
		$options[] = 'fields:'.$this->createFieldsData();

		//pagination
		$options[] = 'rowsPerPage:'.( isset($this->initialData['results']) ? $this->initialData['results'] : Get::sett('visuItem') );
		$options[] = 'initialPage:'.'0';


		if(count($this->otherOptions)>0) {
			foreach($this->otherOptions as $key => $value) {

				$options[] = $key.':'.$this->json->encode($value);
			}
		}

		return '{'.implode(",", $options).'}';
	}

	protected function _getHtml() {
		return '';
	}


	protected function setHtmlBefore($html) {
		$this->html_before = $html;
	}

	protected function setHtmlAfter($html) {
		$this->html_after = $html;
	}

	public function get($print = false) {

		$js_code = '';
		if ($this->jsClassName != '') {
			$jsOptions = $this->_getJsOptions();
			$tableName = 'table_'.$this->id;
			if ($this->isGlobalVariable) $js_code = 'var '.$tableName.';'; else $js_code = '';
			$js_code .= ($this->useDOMReady ? 'YAHOO.util.Event.onDOMReady(function(e){' : '').'
				'.($this->isGlobalVariable ? '' :	'var ').$tableName.' = new '.$this->jsClassName.'("'.$this->id.'"'.($jsOptions != '' ? ', '.$jsOptions : '').');
				'.($this->useDOMReady ? '});' : '');
		}

		$output = array(
			'js' => '<script type="text/javascript">'.$js_code.'</script>',
			'html' => $this->html_before.'<div id="pag-above"></div>'
			.'<div id="'.$this->id.'">'.$this->_getHtml().'</div>'
			.'<div id="pag-below"></div>'.$this->html_after,
			'options' => $jsOptions
		);

		if (!$print) {
			return $output;
		} else {
			cout($output['js'], 'page_head');
			cout($output['html'], 'content');
		}
	}

}

?>
