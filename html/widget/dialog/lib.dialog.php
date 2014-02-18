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

class DialogWidget extends Widget {

	public $id = ""; //id of the dialog and global variable reference

	//dynamic parameters
	public $dynamicContent = false; //if the dialog is filled dynamically via ajax or if the content is directly passed
	public $ajaxUrl = ""; //if the dialog content is dynamically loaded, than read it from this url through an ajax request
	public $dynamicAjaxUrl = false; //if the ajax url should be read directly as a string or through some js variable/procedure
	public $directSubmit = false; //if set to true, make a direct submit of the dialog's form instead doing the ajax request

	//static parameters
	public $header = ""; //the title of the dialog, if it isn't dynamically loaded
	public $body = ""; //the body content, if the dialog is not dynamic
	public $buttons = array(); //deprecated, button are automatically set
	public $script = 'false'; //deprecated, use renderEvent and destroyEvent instead

	//dialog's behaviour parameters
	public $width = false;
	public $height = false;
	public $modal = true;
	public $fixedCenter = true;
	public $constrainToViewport = true;
	public $draggable = true;
	public $visible = false;
	public $close = true;
	public $hideAfterSubmit = false;
	public $confirmOnly = false;
	public $callback = false;

	//dialog's events
	public $renderEvent = false;
	public $destroyEvent = false;

	//calling events
	public $callEvents = false;

	//internal fields
	protected $json = null;

	/**
	 * Constructor
	 * @param <string> $config the properties of the table
	 */
	public function __construct() {
		parent::__construct();
		$this->_widget = 'dialog';
		$this->json = new Services_JSON();
	}

	public function run() {

		if (!$this->id) return false;

		//set parameters
		$params = array(
			'id' => $this->id,
		);

		//validate parameters

		if ($this->dynamicContent) {
			$params['dynamicContent'] = true;
			$params['ajaxUrl'] = (is_string($this->ajaxUrl) ? $this->ajaxUrl : "");
			$params['dynamicAjaxUrl'] = $this->dynamicAjaxUrl ? true : false;
		} else {
			$params['dynamicContent'] = false;
			$params['header'] = $this->json->encode((string)$this->header);
			$params['body'] = $this->json->encode((string)$this->body);
			//$params['buttons'] = $this->_getButtonsCode();
			//$params['script'] = $this->script;//$this->json->encode((string)$this->script);
		}

		if ($this->directSubmit) $params['directSubmit'] = true;

		if ($this->width) $params['width'] = $this->width;
		if ($this->height) $params['height'] = $this->height;

		$params['modal'] = $this->modal ? true : false;
		$params['fixedCenter'] = $this->fixedCenter ? true : false;
		$params['constrainToViewport'] = $this->constrainToViewport ? true : false;
		$params['draggable'] = $this->draggable ? true : false;
		$params['visible'] = $this->visible ? true : false;
		$params['close'] = $this->close ? true : false;
		$params['hideAfterSubmit'] = $this->hideAfterSubmit ? true : false;
		$params['confirmOnly'] = $this->confirmOnly ? true : false;
		if ($this->callback) $params['callback'] = $this->callback;

		//dialog internal events
		if ($this->renderEvent) $params['renderEvent'] = $this->renderEvent;
		if ($this->destroyEvent) $params['destroyEvent'] = $this->destroyEvent;

		//set calling events and event scripts
		$callEvents = "";
		if (!is_array($this->callEvents)) {
			$this->callEvents = false;
		} else {
			$arrEvents = array();
			foreach ($this->callEvents as $val) {
				if(isset($val['expression']) && $val['expression'] = true )
					$arrEvents[$val['event']] = $val['caller'];
				else
					$arrEvents[$val['event']][] = $val['caller'];
			}
			foreach ($arrEvents as $event=>$list) {
				
				if(is_array($list)) $callEvents .= 'YAHOO.util.Event.addListener('.$this->json->encode($list).', "'.$event.'", dialogEvent);'."\n";
				else $callEvents .= 'YAHOO.util.Event.addListener('.$list.', "'.$event.'", dialogEvent);'."\n";
			}
		}
		$params['callEvents'] = $callEvents;

		//choose a view by parameters specification
		if ($this->dynamicContent) {
			$view = 'dynamic';
		} else {
			$view = 'static';
		}

		//render the view
		$this->render($view, $params);
	}


	/**
	 * Include the required libraries in order to have all the things ready and working
	 */
	public function init() {

		// load yui
		YuiLib::load('base,container');

		// Commodities functions
		Util::get_js(Get::rel_path('base').'/lib/js_utils.js', true,true);
		Util::get_js(Get::rel_path('base').'/widget/dialog/dialog.js', true,true);

		require_once(_base_.'/lib/lib.dialog.php');
		initDialogs();
	}


	protected function _getButtonsCode() {
		if (!is_array($this->buttons)) return '[]';
		$buttons = array();
		foreach ($this->buttons as $button) {
			$buttons[] = '{text:'.$this->json->encode((string)$button['text']).', handler:'
				.(string)$button['handler'].(isset($button['isDefault']) && $button['isDefault'] ? ', isDefault: true' : '').'}';
		}
		return '['.implode(',', $buttons).']';
	}

}
?>