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
 * @version  $Id: class.definition.php 420 2006-06-03 12:32:54Z fabio $
 *
 * @category Module
 */
class ScsAdminModule
{
    public $module_name;

    public $version;

    public $authors;

    public $mantainers;

    public $descr_short;

    public $descr_long;

    public function ScsAdminModule($module_name = '')
    {
        if ($module_name == '') {
            $this->module_name = $GLOBALS['modname'];
        } else {
            $this->module_name = $module_name;
        }

        $this->version = '1.0';

        $this->authors = ['Fabio Pirovano <fabio@docebo.it)',
                                'Emanuele Sandri <esandri@tiscali.it>', ];
        $this->mantainers = ['Fabio Pirovano <fabio@docebo.it)',
                                    'Emanuele Sandri <esandri@tiscali.it>', ];

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
        return 'Docebo 3.0 SCS (Conference) - ' . $this->module_name;
    }

    public function loadHeader()
    {
        return;
    }

    public function loadBody()
    {
        //global $op, $modname, $prefix;

        include dirname(__FILE__) . '/../modules/' . $this->module_name . '/' . $this->module_name . '.php';
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

    public function getPermissionUi($module_name, $modname, $op, $form_name, $perm)
    {
        require_once _base_ . '/lib/lib.table.php';

        $lang = &DoceboLanguage::createInstance('manmenu');
        $lang_perm = &DoceboLanguage::createInstance('permission');

        $tokens = $this->getAllToken($op);
        $tb = new Table(0, '', $lang->def('_EDIT_SETTINGS'));

        $type = ['align_left'];
        $c_head = [''];
        $c_body = [$module_name];

        foreach ($tokens as $k => $token) {
            $type[] = 'image';
            $c_head[] = '<label for="perm_' . $modname . '_' . $op . '_' . $token['code'] . '">'
                        . '<img src="' . getPathImage('scs') . $token['image'] . '" alt="' . $lang_perm->def($token['name']) . '"'
                        . ' title="' . $lang_perm->def($token['name']) . '" /></label>';

            $c_body[] = '<input class="check" type="checkbox" '
                            . 'id="perm_' . $modname . '_' . $op . '_' . $token['code'] . '" '
                            . 'name="perm[' . $modname . '][' . $op . '][' . $token['code'] . ']" value="1"'
                            . (isset($perm[$modname][$token['code']]) ? ' checked="checked"' : '') . ' />'

                    . '<label class="access-only" for="perm_' . $modname . '_' . $op . '_' . $token['code'] . '">'
                        . $lang_perm->def($token['name']) . '</label>' . "\n";
        }

        $tb->setColsStyle($type);
        $tb->addBody($c_head);
        $tb->addBody($c_body);

        return $tb->getTable();
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
