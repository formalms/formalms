<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

/**
 * Define class TabView.
 *
 * @author Emanuele Sandri (esandri@tiscali.it)
 *
 * @version $Id: lib.tab.php 113 2006-03-08 18:08:42Z ema $
 **/

/*
 *	NOTE: Require style_tabview.css
 */

/**
 * The class TabItem describe an element of TabView.
 **/
class TabElem
{
    /** @var string the id of the tab element */
    public $id;

    /** @var bool state of the tab active=TRUE not active=FALSE */
    public $active = false;

    /** return the name of the data used to store status.
     *	@return string the name of the data used to store status
     **/
    public function _getStateId()
    {
        return 'tabelem_' . $this->id . '_status';
    }

    /** Activate the TabElem */
    public function activate()
    {
        $this->active = true;
    }

    /** Deactivate the TabElem */
    public function deactivate()
    {
        $this->active = false;
    }

    /** Return the active state of the tab.
     *	@return bool active state of the tab
     **/
    public function isActive()
    {
        return $this->active;
    }

    /** if the tab element is visible.
     *	@return true if the tab element is visible, FALSE otherwise
     **/
    public function isVisible()
    {
        return false;
    }

    /** This funciton return HTML code to print out tab clickable label.
     *	@return HTML code for tab label UI or FALSE
     **/
    public function printLabel()
    {
        return false;
    }

    /** This function extract from input (POST) the state of the tab.
     *	@param array &$data input data (POST)
     *  @param array &$extra_data extra input data
     **/
    public function parseInput(&$data, &$extra_data)
    {
        // $extra_data are ingored in this context
        //$flip = array_flip($data);
        if (isset($data[$this->_getStateId()])) {
            $this->activate();
        }
    }

    /** Return the state array of the TabView.
     *	@return array state array of the TabView
     **/
    public function getState()
    {
        return null;
    }
}

class TabElemDefault extends TabElem
{
    /** @var string */
    public $label;

    /** @var string */
    public $imgsrc = false;

    /** @var string */
    public $className = false;

    /** Constructor.
     *	@param string $id
     *	@param string $label
     *	@param string $imgsrc
     *	@param string $className
     **/
    public function TabElemDefault($id, $label, $imgsrc = false, $className = false)
    {
        $this->id = $id;
        $this->label = $label;
        $this->imgsrc = $imgsrc;
    }

    /**
     * @return bool|true
     */
    public function isVisible()
    {
        return true;
    }

    /**
     * @return HTML|string
     */
    public function printLabel()
    {
        $lout = '<li class="';
        if ($this->isActive()) {
            $lout .= 'TabElemDefault_active';
        } else {
            $lout .= 'TabElemDefault_inactive';
        }
        if ($this->className !== false) {
            $lout .= ' ' . $this->className . '">';
        } else {
            $lout .= '">';
        }
        /*if( $this->imgsrc !== FALSE ) {
            $lout .= '<img src="'.$this->imgsrc.'" alt="'
                    .$this->label.'" title="'
                    .$this->label.( $this->isActive() ? ' '. Lang::t('_ACTIVE_TAB', 'standard') : '' ).'" />';
        }*/
        $lout .= '<div><input type="submit" value="'
                . $this->label . '" name="'
                . $this->_getStateId() . '" class="TabView_hotspot" />'
                . '</div></li>';
        //$lout .= $this->label.'</div>';
        return $lout;
    }
}

/**
 *	The class TabView represent a tabbed UI.
 **/
class TabView
{
    /** @var string the id of the TabView */
    public $id;

    /** @var array array of tab elements */
    public $arrTab = [];

    /** @var int id of the active tab in the array */
    public $activeId = null;

    /** @var string for post tab click requests */
    public $post_url = false;

    /**
     * @var string
     */
    public $method = 'post';

    /** return the name of the data used to store status.
     *	@return string the name of the data used to store status
     **/
    public function _getStateId()
    {
        return 'tabview_' . $this->id . '_status';
    }

    /** Constructor.
     *	@param string $id the unique id of the TabView object
     **/
    public function TabView($id, $post_url)
    {
        $this->id = $id;
        $this->post_url = $post_url;
    }

    /**
     *  This function add a tab to list of managed tab.
     *
     *  @param TabElem $tab tab element to add
     **/
    public function addTab($tab)
    {
        $this->arrTab[$tab->id] = $tab;
    }

    /** Set the active tab.
     *	Set all other tabs to inactive.
     *
     *	@param $tabId the id of the tab to acivate
     **/
    public function setActiveTab($tabId)
    {
        while (($key = key($this->arrTab)) != '') {
            if ($key == $tabId) {
                $this->activeId = $tabId;
                $this->arrTab[$key]->activate();
            } else {
                $this->arrTab[$key]->deactivate();
            }
            next($this->arrTab);
        }
        reset($this->arrTab);
    }

    /** Get the active tab element.
     *	@return string the id of the active tab
     **/
    public function getActiveTab()
    {
        return $this->activeId;
    }

    /** This function extract from input (POST) the state of the tab.
     *	@param array &$data input data (POST)
     *  @param \Symfony\Component\HttpFoundation\Session\Session &$extra_data extra input data (SESSION)
     **/
    public function parseInput(&$data, $extra_data)
    {
        if ($extra_data instanceof \Symfony\Component\HttpFoundation\Session\Session) {
            $extra_data = iterator_to_array($extra_data->getIterator());
        }
        // $extra_data are ingored in this context
        while (($key = key($this->arrTab)) != '') {
            $this->arrTab[$key]->parseInput($data, $extra_data);
            if ($this->arrTab[$key]->isActive()) {
                $this->activeId = $key;
            }
            next($this->arrTab);
        }
        reset($this->arrTab);
        if ($this->activeId === null) {
            if (isset($data[$this->_getStateId()])) {
                $this->setActiveTab($data[$this->_getStateId()]);
            } elseif (isset($extra_data[$this->_getStateId()])) {
                $this->setActiveTab($extra_data[$this->_getStateId()]);
            }
        }
    }

    /** Return the state array of the TabView.
     *	@return array state array of the TabView
     **/
    public function getState()
    {
        return [$this->_getStateId() => $this->getActiveTab()];
    }

    /* ********************************** PRINT OUT FUNCTIONS ************************/
    /** This function return a string with start output for print tabview and related content.
     *  @param $url_param string Unique string containing all the parameters to be set in the form
     *
     *  @return string the output of the TabView
     **/
    public function printTabView_Begin($url_param = '', $print_form = true)
    {
        $tvout = '<div class="TabView_container">';

        //open form
        if ($print_form) {
            $tvout .= '<form action="' . $this->post_url . ($url_param != '' ? '&amp;' . $url_param : '') . '" method="' . $this->method . '">'
                . '<input type="hidden" id="authentic_request_tv" name="authentic_request" value="' . Util::getSignature() . '" />';
        }

        // print tab
        $tvout .= '<ul class="TabView_tabspace">';
        while (($tab = current($this->arrTab)) !== false) {
            $tvout .= $tab->printLabel();
            next($this->arrTab);
        }
        reset($this->arrTab);
        $tvout .= '</ul>';

        // close form
        if ($print_form) {
            $tvout .= '</form>';
        }

        $tvout .= '<div class="TabView_content"><br />' . "\n";

        return $tvout;
    }

    /** This function return a string with all output for print
     *	tabview and related content.
     *
     *	@param stirng $content the content of the tab
     *
     *	@return string the output of the TabView
     **/
    public function printTabView($content)
    {
        return $this->printTabView_Begin() . $content . $this->printTabView_End();
    }

    /** This function return a string with end output for print
     *	tabview and related content.
     *
     *	@return string the output of the TabView
     **/
    public function printTabView_End()
    {
        return "</div>\n"
            . "</div>\n";
    }
}
