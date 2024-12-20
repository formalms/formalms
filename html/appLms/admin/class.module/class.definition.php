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

/**
 * @version  $Id: class.definition.php 573 2006-08-23 09:38:54Z fabio $
 *
 * @category Module
 */
class LmsAdminModule
{
    public $module_name;

    public $version;

    public $authors;

    public $mantainers;

    public $descr_short;

    public $descr_long;

    public function __construct($module_name = '')
    {
        if ($module_name == '') {
            $this->module_name = $GLOBALS['modname'];
        } else {
            $this->module_name = $module_name;
        }

        $this->version = '1.0';

        $this->authors = ['Forma LMS Association ', 'https://www.formalms.org'];
        $this->mantainers = ['Forma LMS Association','https://www.formalms.org' ];

        $this->descr_short = 'General module : ' . $this->module_name;
        $this->descr_long = 'General module : ' . $this->module_name;
    }

    public function getName()
    {
        return $this->module_name;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getAuthors()
    {
        return $this->authors;
    }

    public function getMantainers()
    {
        return $this->mantainers;
    }

    public function getDescription($get_long = false)
    {
        return $this->descr_short;
    }

    public function useStdHeader()
    {
        return true;
    }

    public function useHeaderImage()
    {
        return true;
    }

    public function getTitle()
    {
        return 'Forma 2.1 LMS - ' . $this->module_name;
    }

    public function loadHeader()
    {
        return;
    }

    public function loadBody()
    {
        //include(dirname(__FILE__).'/../modules/'.$this->module_name.'/'.$this->module_name.'.php');
        include \FormaLms\lib\Forma::inc(_lms_ . '/admin/modules/' . $this->module_name . '/' . $this->module_name . '.php');
    }

    public function loadFooter()
    {
        return;
    }

    public function getVoiceMenu()
    {
        return [];
    }

    public function useExtraMenu()
    {
        return false;
    }

    public function loadExtraMenu()
    {
        return;
    }

    // Function for permission managment

    public function getAllToken($op)
    {
        return [
            'view' => ['code' => 'view',
                                'name' => '_VIEW',
                                'image' => 'standard/view.png', ],
        ];
    }

    public function getPermissionUi($module_name, $modname, $op, $form_name, $perm, $all_perm_tokens)
    {
        $lang = &FormaLanguage::createInstance('manmenu');
        $lang_perm = &FormaLanguage::createInstance('permission');

        $tokens = $this->getAllToken($op);

        $c_body = [$module_name];

        foreach ($all_perm_tokens as $k => $token) {
            if (isset($tokens[$k])) {
                $c_body[] = '<input class="check" type="checkbox" '
                                . 'id="perm_' . $modname . '_' . $op . '_' . $tokens[$k]['code'] . '" '
                                . 'name="perm[' . $modname . '][' . $op . '][' . $tokens[$k]['code'] . ']" value="1"'
                                . (isset($perm[$tokens[$k]['code']]) ? ' checked="checked"' : '') . ' />'

                        . '<label class="access-only" for="perm_' . $modname . '_' . $op . '_' . $tokens[$k]['code'] . '">'
                        . $lang_perm->def($token['name']) . '</label>' . "\n";
            } else {
                $c_body[] = '';
            }
        }

        return $c_body;
    }

    public function getSelectedPermission($source_array, $modname, $op)
    {
        $tokens = $this->getAllToken($op);
        $perm = [];

        foreach ($tokens as $k => $token) {
            if (isset($source_array['perm'][$modname][$op][$token['code']])) {
                $perm[$token['code']] = 1;
            }
        }

        return $perm;
    }
}
