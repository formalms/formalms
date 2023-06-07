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

class YuilogWidget extends Widget
{
    public $div = '';

    /**
     * Constructor.
     *
     * @param string $config the properties of the table
     */
    public function __construct()
    {
        parent::__construct();
        $this->_widget = 'yuilog';
    }

    public function run()
    {
        if (FormaLms\lib\Get::cfg('do_debug')) {
            $this->div = (!empty($this->div) ? $this->div : 'yui_log_container');

            $this->render('yuilog',
                [
                    'div' => $this->div,
                ]
            );
        } else {
            $this->render('yuilog_off');
        }
    }

    /**
     * Include the required libraries in order to have all the things ready and working.
     */
    public function init()
    {
        Util::get_js(FormaLms\lib\Get::rel_path('base') . '/addons/yui/logger/logger-min.js', true, true);
        Util::get_css($GLOBALS['where_templates_relative'] . '/standard/yui-skin/logger.css', true, true);
    }
}
