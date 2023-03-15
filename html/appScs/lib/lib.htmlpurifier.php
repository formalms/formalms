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

require_once _adm_ . '/addons/htmlpurifier/HTMLPurifier.auto.php';

/**
 * Extension of HTMLPurifier for a couple of reason such as easy mantainance and commutation to singleton class.
 */
class DbPurifier extends HTMLPurifier
{
    /**
     * constructor, this is a singleton class please don't use this but make a call like this : $var =& DBPurifier::getInstance().
     */
    public function DbPurifier()
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Core', 'Encoding', 'UTF-8');
        $config->set('Core', 'XHTML', true);
        $config->set('Cache.SerializerPath', _files_ . '/cache/twig');
        parent::HTMLPurifier($config);
    }

    /**
     * return the instance of the DbPurifier.
     */
    public function &getInstance()
    {
        if (!isset($GLOBALS['html_purifier'])) {
            $GLOBALS['html_purifier'] = new DBPurifier();
        }

        return $GLOBALS['html_purifier'];
    }

    /**
     * remove all the html from the string.
     */
    public function text($string)
    {
        return strip_tags($this->purify($string));
    }
}
