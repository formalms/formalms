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

class PageWriter
{
    protected $_cont = ['main' => ''];
    protected $_zone = 'main';
    protected $_id = '';

    public function __construct()
    {
        $this->_id = uniqid();
    }

    public static function init()
    {
        if (!isset($GLOBALS['page'])) {
            $GLOBALS['page'] = new PageWriter();
        }
    }

    public function add($txt, $zone = false, $add_nl = true)
    {
        $zone = (empty($zone) ? $this->_zone : $zone);
        if (!isset($this->_cont[$zone])) {
            $this->_cont[$zone] = '';
        }
        $this->_cont[$zone] .= $txt . ($add_nl ? "\n" : '');
    }

    public function getZoneContent($zone = false)
    {
        $zone = (empty($zone) ? $this->_zone : $zone);

        return $this->_cont[$zone];
        echo '<!--- [pagewriter:' . $this->_id . '_' . $zone . '] --->';
    }

    public function setZone($zone)
    {
        $this->_zone = $zone;
    }

    public function render($contents)
    {
        foreach ($this->_cont as $key => $val) {
            $contents = str_replace('<!--- [pagewriter:' . $this->_id . '_' . $key . '] --->', $val, $contents);
        }

        return $contents;
    }
}

function cout($txt, $zone = 'main')
{
    $GLOBALS['page']->add($txt);
}

function getZoneContent($zone = false)
{
    return $GLOBALS['page']->getZoneContent($zone);
}
