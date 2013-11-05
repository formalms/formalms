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
 * Define class TabView
 * @package admin-library
 * @subpackage interaction
 * @author Emanuele Sandri (esandri@tiscali.it)
 * @version $Id: lib.tab.php 113 2006-03-08 18:08:42Z ema $
**/

/*
 *	NOTE: Require style_tabview.css
 */

/**
 * The class TabItem describe an element of TabView
**/
class TabElem {
	/** @var string $id the id of the tab element */
	var $id;

	/** @var bool $active state of the tab active=TRUE not active=FALSE */
	var $active = FALSE;

	/** return the name of the data used to store status
	 *	@return string the name of the data used to store status
	**/
	function _getStateId() { return 'tabelem_'.$this->id.'_status'; }

	/** Activate the TabElem */
	function activate() { $this->active = TRUE; }

	/** Deactivate the TabElem */
	function deactivate() { $this->active = FALSE; }

	/** Return the active state of the tab
	 *	@return bool active state of the tab
	**/
	function isActive() { return $this->active; }

	/** if the tab element is visible
	 *	@return TRUE if the tab element is visible, FALSE otherwise
	**/
	function isVisible() { return FALSE; }

	/** This funciton return HTML code to print out tab clickable label.
	 *	@return HTML code for tab label UI or FALSE
	**/
	function printLabel() { return FALSE; }

	/** This function extract from input (POST) the state of the tab
	 *	@param array &$data input data (POST)
	 *  @param array &$extra_data extra input data
	**/
	function parseInput( &$data, &$extra_data ) {
		// $extra_data are ingored in this context
		//$flip = array_flip($data);
		if( isset( $data[$this->_getStateId()] ) ) {
			$this->activate();
		}
	}

	/** Return the state array of the TabView
	 *	@return array state array of the TabView
	**/
	function getState() {
		return NULL;
	}
}

class TabElemDefault extends TabElem {
	/** @var string $label */
	var $label;

	/** @var string $imgsrc */
	var $imgsrc = FALSE;

	/** @var string $className */
	var $className = FALSE;

	/** Constructor
	 *	@param string $id
	 *	@param string $label
	 *	@param string $imgsrc
	 *	@param string $className
	**/
	function TabElemDefault( $id, $label, $imgsrc = FALSE, $className = FALSE) {
		$this->id = $id;
		$this->label = $label;
		$this->imgsrc = $imgsrc;
	}

	function isVisible() { return TRUE; }

	function printLabel() {
		$lout = '<li class="';
		if( $this->isActive() )
			$lout .= 'TabElemDefault_active';
		else
			$lout .= 'TabElemDefault_inactive';
		if( $this->className !== FALSE )
			$lout .= ' '.$this->className.'">';
		else
			$lout .= '">';
		/*if( $this->imgsrc !== FALSE ) {
			$lout .= '<img src="'.$this->imgsrc.'" alt="'
					.$this->label.'" title="'
					.$this->label.( $this->isActive() ? ' '. Lang::t('_ACTIVE_TAB', 'standard') : '' ).'" />';
		}*/
		$lout .= '<div><input type="submit" value="'
				.$this->label.'" name="'
				.$this->_getStateId().'" class="TabView_hotspot" />'
				.'</div></li>';
		//$lout .= $this->label.'</div>';
		return $lout;
	}
}

/**
 *	The class TabView represent a tabbed UI
**/
class TabView {
	/** @var string $id the id of the TabView */
	var $id;
	/** @var array $arrTab array of tab elements*/
	var $arrTab = array();
	/** @var int $activeId id of the active tab in the array */
	var $activeId = NULL;
	/** @var string $url for post tab click requests */
	var $post_url = FALSE;

	var $method = 'post';


	/** return the name of the data used to store status
	 *	@return string the name of the data used to store status
	**/
	function _getStateId() { return 'tabview_'.$this->id.'_status'; }

	/** Constructor
	 *	@param string $id the unique id of the TabView object
	**/
	function TabView( $id, $post_url ) {
		$this->id = $id;
		$this->post_url = $post_url;
	}

	/** This function add a tab to list of managed tab
	 * @param TabElem $tab tab element to add
	**/
	function addTab( $tab ) {
		$this->arrTab[$tab->id] = $tab;
	}

	/** Set the active tab.
	 *	Set all other tabs to inactive
	 *	@param $tabId the id of the tab to acivate
	**/
	function setActiveTab( $tabId ) {
		while( ($key = key($this->arrTab)) != "" ) {
			if( $key == $tabId ) {
				$this->activeId = $tabId;
				$this->arrTab[$key]->activate();
			} else
				$this->arrTab[$key]->deactivate();
			next( $this->arrTab );
		}
		reset( $this->arrTab );
	}

	/** Get the active tab element.
	 *	@return string the id of the active tab
	**/
	function getActiveTab() {
		return $this->activeId;
	}

	/** This function extract from input (POST) the state of the tab
	 *	@param array &$data input data (POST)
	 *  @param array &$extra_data extra input data (SESSION)
	**/
	function parseInput( &$data, &$extra_data ) {
		// $extra_data are ingored in this context
		while( ($key = key($this->arrTab)) != "" ) {
			$this->arrTab[$key]->parseInput( $data, $extra_data );
			if( $this->arrTab[$key]->isActive() )
				$this->activeId = $key;
			next( $this->arrTab );
		}
		reset( $this->arrTab );
		if( $this->activeId === NULL ) {
			if( isset( $data[$this->_getStateId()] ) ) {
				$this->setActiveTab( $data[$this->_getStateId()] );
			} elseif ( isset( $extra_data[$this->_getStateId()] ) ) {
				$this->setActiveTab( $extra_data[$this->_getStateId()] );
			}
		}
	}

	/** Return the state array of the TabView
	 *	@return array state array of the TabView
	**/
	function getState() {
		return array( $this->_getStateId() => $this->getActiveTab() );
	}

	/* ********************************** PRINT OUT FUNCTIONS ************************/
	/** This function return a string with start output for print
	 *	tabview and related content
	 *	@return string the output of the TabView
	**/
	function printTabView_Begin( $url_param = "", $print_form = TRUE ) {

		$tvout = '<div class="TabView_container">';

		//open form
		if($print_form)
			$tvout .= '<form action="'.$this->post_url.( $url_param != "" ? '&amp;'.$url_param : '' ).'" method="'.$this->method.'">'
				.'<input type="hidden" id="authentic_request_tv" name="authentic_request" value="'.Util::getSignature().'" />';

		// print tab
		$tvout .= '<ul class="TabView_tabspace">';
		while(($tab = current($this->arrTab)) !== FALSE) {

			$tvout .= $tab->printLabel();
			next( $this->arrTab );
		}
		reset($this->arrTab);
		$tvout .= '</ul>';

		// close form
		if($print_form)
			$tvout .= '</form>';

		$tvout .= '<div class="TabView_content"><br />'."\n";
		return $tvout;
	}

	/** This function return a string with all output for print
	 *	tabview and related content
	 *	@param stirng $content the content of the tab
	 *	@return string the output of the TabView
	**/
	function printTabView( $content ) {
		return $this->printTabView_Begin().$content.$this->printTabView_End();
	}

	/** This function return a string with end output for print
	 *	tabview and related content
	 *	@return string the output of the TabView
	**/
	function printTabView_End( ) {
		return "</div>\n"
			."</div>\n";
	}

}

?>