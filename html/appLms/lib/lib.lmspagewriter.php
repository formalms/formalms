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
 * this class is the default page for docebolms.
 */
class LmsPageWriter extends PageWriter
{
    public function __construct()
    {
        parent::__construct();
        $this->addZone('page_head');
        $this->addZone('blind_navigation');
        $this->addZone('feedback');
        $this->addZone('header');
        $this->addZone('quickbar');
        $this->addZone('menu_over');
        $this->addZone('menu', true);
        $this->addZone('content', true);
        $this->addZone('footer');
        $this->addZone('scripts');
        $this->addZone('debug');
        $this->_zones['def_lang'] = new PageZoneLang('def_lang', false);

        $this->addStart('<ul id="blind_avigation" class="container-blindnav">', 'blind_navigation');
        $this->addEnd('</ul>' . "\n", 'blind_navigation');
     }

    /**
     * Create an instance of LmsPageWriter.
     *
     * @static
     *
     * @return an istance of LmsPageWriter
     */
    public static function createInstance()
    {
        if ($GLOBALS['page'] === null) {
            $GLOBALS['page'] = new LmsPageWriter();
        }

        return $GLOBALS['page'];
    }
}
