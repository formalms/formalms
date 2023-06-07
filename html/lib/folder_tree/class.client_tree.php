<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

Util::get_js(FormaLms\lib\Get::rel_path('base') . '/lib/lib.elem_selector.js', true, true);

class ClientTree
{
    public $id = '';

    private $styleSheets = [];

    protected $jsClassName = 'FolderTree';
    protected $serverUrl = '';

    public $useDOMReady = false;
    public $isGlobalVariable = false;

    protected $langs = [];
    protected $options = [];

    protected $session;

    public function __construct($id)
    {
        $this->id = $id;
        $this->session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    }

    //libraries
    public function initLibraries()
    {
        YuiLib::load([
            'yahoo-dom-event' => 'yahoo-dom-event.js',
            'connection' => 'connection-min.js',
            'dragdrop' => 'dragdrop-min.js',
            'element' => 'element-beta-min.js',
            'animation' => 'animation-min.js',
            'json' => 'json-min.js',
            'container' => 'container_core-min.js', //menu
            'menu' => 'menu-min.js', //menu
            'button' => 'button-min.js', //dialog
            'container' => 'container-min.js', //dialog
            'button' => 'button-min.js', //dialog
            'treeview' => 'treeview-min.js',
            'resize' => 'resize-beta-min.js',
            'selector' => 'selector-beta-min.js', ],
                [
            'assets/skins/sam' => 'skin.css',
                ]
            );
        Util::get_js(FormaLms\lib\Get::rel_path('base') . '/lib/lib.elem_selector.js', true, true);
        Util::get_js(FormaLms\lib\Get::rel_path('base') . '/lib/js_utils.js', true, true);

        $js_path = FormaLms\lib\Get::rel_path('base') . '/lib/folder_tree/';

        Util::get_js($js_path . 'ddnode.js', true, true);
        Util::get_js($js_path . 'foldernode.js', true, true);
        Util::get_js($js_path . 'foldertree.js', true, true);

        //addCss('folder_tree', 'framework');
        cout(Util::get_css('base-folder-tree.css'), 'page_head');
        foreach ($this->styleSheets as $sheet) {
            cout(Util::get_css($sheet . '.css'), 'page_head');
        }
    }

    public function addStyleSheet($sheet)
    {
        $this->styleSheets[] = $sheet;
    }

    public function addLangKey($key, $text)
    {
        if (!(is_string($key) && (is_string($text) || is_numeric($text)))) {
            return false;
        }
        $this->langs[$key] = '' . $text;

        return true;
    }

    //to override
    protected function _getJsOptions()
    {
        $this->setOption('ajax_url', $this->serverUrl);
        $this->setOption('langs', $this->langs);
        require_once _base_ . '/lib/lib.json.php';
        $json = new Services_JSON();
        $arr_js = [];
        foreach ($this->options as $name => $option) {
            $arr_js[] = $name . ':' . $json->encode($option);
        }

        return '{' . implode(',', $arr_js) . '}';
    }

    //to override
    protected function _getHtml()
    {
        return '';
    }

    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
    }

    public function setJSClassName($name)
    {
        $this->jsClassName = $name;
    }

    public function setServerUrl($url)
    {
        $this->serverUrl = $url;
    }

    public function get($noPrint = true)
    {
        $js_code = '';
        if ($this->jsClassName != '') {
            $jsOptions = $this->_getJsOptions();
            $treeName = 'tree_' . $this->id;
            if ($this->isGlobalVariable) {
                $js_code = 'var ' . $treeName . ';';
            } else {
                $js_code = '';
            }
            $js_code .= ($this->useDOMReady ? 'YAHOO.util.Event.onDOMReady(function(e){' : '') . '
				' . ($this->isGlobalVariable ? '' : 'var ') . $treeName . ' = new ' . $this->jsClassName . '("' . $this->id . '"' . ($jsOptions != '' ? ', ' . $jsOptions : '') . ');
				' . ($this->useDOMReady ? '});' : '');
        }

        $output = [
            'js' => '<script type="text/javascript">' . $js_code . '</script>',
            'html' => '<div class="folder_tree" id="' . $this->id . '">' . $this->_getHtml() . '</div>',
            'options' => $jsOptions,
        ];

        if ($noPrint) {
            return $output;
        } else {
            cout($output['js'], 'page_head');
            cout($output['html'], 'content');
        }
    }
}
