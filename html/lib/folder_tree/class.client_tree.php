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


Util::get_js(Get::rel_path('base').'/lib/lib.elem_selector.js',true, true);

class ClientTree {

	public $id = '';

	private $styleSheets = array();

	protected $jsClassName = 'FolderTree';
	protected $serverUrl = '';

	public $useDOMReady = false;
	public $isGlobalVariable = false;

	protected $langs = array();
	protected $options = array();

	public function __construct($id) {
		$this->id = $id;
	}

	//libraries
	public function initLibraries() {
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
			'selector'=>'selector-beta-min.js'),
				array(
			'assets/skins/sam' => 'skin.css'
				)
			);
			Util::get_js(Get::rel_path('base').'/lib/lib.elem_selector.js', true, true);
			Util::get_js(Get::rel_path('base').'/lib/js_utils.js', true,true);

			$js_path = Get::rel_path('base').'/lib/folder_tree/';

			Util::get_js($js_path.'ddnode.js', true,true);
			Util::get_js($js_path.'foldernode.js', true,true);
			Util::get_js($js_path.'foldertree.js', true,true);


			//addCss('folder_tree', 'framework');
			cout(Util::get_css( 'base-folder-tree.css'), 'page_head');
			foreach ($this->styleSheets as $sheet) { cout(Util::get_css( $sheet .'.css'), 'page_head'); }
	}

	public function addStyleSheet($sheet) { $this->styleSheets[] = $sheet; }

	public function addLangKey($key, $text) {
		if (!(is_string($key) && (is_string($text) || is_numeric($text)))) return false;
		$this->langs[$key] = ''.$text;
		return true;
	}

	//to override
	protected function _getJsOptions() {
		$this->setOption('ajax_url', $this->serverUrl);
		$this->setOption('langs', $this->langs);
		require_once(_base_.'/lib/lib.json.php');
		$json = new Services_JSON();
		$arr_js = array();
		foreach ($this->options as $name=>$option) $arr_js[] = $name.':'.$json->encode($option);
		return '{'.implode(",", $arr_js).'}';
	}

	//to override
	protected function _getHtml() {
		return '';
	}


	public function setOption($name, $value) {
		$this->options[$name] = $value;
	}

	public function setJSClassName($name) {
		$this->jsClassName = $name;
	}

	public function setServerUrl($url) {
		$this->serverUrl = $url;
	}

	public function get($noPrint = true) {

		$js_code = '';
		if ($this->jsClassName != '') {
			$jsOptions = $this->_getJsOptions();
			$treeName = 'tree_'.$this->id;
			if ($this->isGlobalVariable) $js_code = 'var '.$treeName.';'; else $js_code = '';
			$js_code .= ($this->useDOMReady ? 'YAHOO.util.Event.onDOMReady(function(e){' : '').'
				'.($this->isGlobalVariable ? '' :	'var ').$treeName.' = new '.$this->jsClassName.'("'.$this->id.'"'.($jsOptions != '' ? ', '.$jsOptions : '').');
				'.($this->useDOMReady ? '});' : '');
		}

		$output = array(
			'js' => '<script type="text/javascript">'.$js_code.'</script>',
			'html' => '<div class="folder_tree" id="'.$this->id.'">'.$this->_getHtml().'</div>',
			'options' => $jsOptions
		);

		if ($noPrint) {
			return $output;
		} else {
			cout($output['js'], 'page_head');
			cout($output['html'], 'content');
		}
	}



}

?>