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

require_once _lms_ . '/lib/lib.levels.php';

class LmsModule
{
    //module name
    public $module_name;
    //module version
    public $version;
    //module authors
    public $authors;
    //module mantainers
    public $mantainers;
    //module short description
    public $descr_short;
    //module long description
    public $descr_long;

    protected $session;

    //class constructor
    public function __construct($module_name = '')
    {
        //EFFECTS: if a module_name is passed use it else use global reference
        global $modname;

        $this->session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();

        if ($module_name == '') {
            $this->module_name = $modname;
        } else {
            $this->module_name = $module_name;
        }

        $this->version = '1.0';

        $this->authors = ['Pirovano Fabio (gishell@tiscali.it)', 'Sandri Emanuele (emanuele@sandri.it)'];
        $this->mantainers = ['Pirovano Fabio (gishell@tiscali.it)', 'Sandri Emanuele (emanuele@sandri.it)'];

        $this->descr_short = 'General module ' . $modname;
        $this->descr_long = 'General module ' . $modname;
    }

    public function getName()
    {
        //EFFECTS: return the name of the module
        return $this->module_name;
    }

    public function getVersion()
    {
        //EFFECTS: return the module version
        return $this->version;
    }

    public function getAuthors()
    {
        //EFFECTS: return an array with the authors info
        return $this->authors;
    }

    public function getMantainers()
    {
        //EFFECTS: return an array with the mantainers info
        return $this->mantainers;
    }

    public function getDescription($get_long = false)
    {
        //EFFECTS: if $getLong == true return long description else return short description
        if ($get_long) {
            return $this->descr_long;
        }

        return $this->descr_short;
    }

    public function beforeLoad()
    {
        return;
    }

    public function useStdHeader()
    {
        //EFFECTS: if return false the file header.php will be not included
        return true;
    }

    public function hideLateralMenu()
    {
        return false;
    }

    public function useHeaderImage()
    {
        //EFFECTS: if return false the header images will not be loaded
        return true;
    }

    public function getTitle()
    {
        //EFFECTS: return a string with the title for the current page
        return $GLOBALS['page_title'] . ' - ' . $this->module_name;
    }

    public function loadHeader()
    {
        //EFFECTS: write in standard output extra header information
        return;
    }

    public function loadBody()
    {
        //EFFECTS: include module language and module main file

        include \FormaLms\lib\Forma::inc(_lms_ . '/modules/' . $this->module_name . '/' . $this->module_name . '.php');
    }

    public function loadFooter()
    {
        //EFFECTS: write in standard output extra footer information
        return;
    }

    public function getVoiceMenu()
    {
        //EFFECTS : return an array with extra menu voice for this module
        //			or an empty array(display only if this is the active module)

        return [];
    }

    public function useExtraMenu()
    {
        //EFFECTS: return true if this module need an extra menu
        return false;
    }

    public function legendLine($image, $name, $alt = false)
    {
        if ($alt === false) {
            $alt = strip_tags($name);
        }

        return '<div class="legend_line">'
            . '<img src="' . getPathImage() . $image . '" alt="' . $alt . '" />'
            . $name
            . '</div>' . "\n";
    }

    public function loadExtraMenu()
    {
        //REQUIRES: that this function is called in a div block
        //EFFECTS : write in standard output an extra menu
        return;
    }

    public static function getAllToken()
    {
        return [
            'view' => ['code' => 'view',
                                'name' => '_VIEW',
                                'image' => 'standard/view.png', ],
            'view_all' => ['code' => 'view_all',
                        'name' => '_VIEW_ALL',
                        'image' => 'standard/moduser.png', ],
        ];
    }

    public static function getPermissionUi($form_name, $perm, $module_op)
    {
        require_once _base_ . '/lib/lib.table.php';

        $lang = &FormaLanguage::createInstance('manmenu', 'framework');
        $lang_perm = &FormaLanguage::createInstance('permission', 'framework');

        $tokens = self::getAllToken($module_op);
        $levels = CourseLevel::getTranslatedLevels();
        $tb = new Table(0, $lang->def('_VIEW_PERMISSION'), $lang->def('_EDIT_SETTINGS'));

        $c_head = [$lang->def('_LEVELS')];
        $t_head = [''];
        foreach ($tokens as $k => $token) {
            if (isset($token['image'])) {
                $c_head[] = '<img src="' . getPathImage() . $token['image'] . '" alt="' . $lang_perm->def($token['name']) . '"'
                            . ' title="' . $lang_perm->def($token['name']) . '" />';
            } else {
                $c_head[] = $lang_perm->def($token['name']);
            }
            $t_head[] = 'image';
        }
        if (count($tokens) > 1) {
            $c_head[] = '<img src="' . getPathImage() . 'standard/checkall.png" alt="' . $lang->def('_CHECKALL') . '" />';
            $c_head[] = '<img src="' . getPathImage() . 'standard/uncheckall.png" alt="' . $lang->def('_UNCHECKALL') . '" />';
            $t_head[] = 'image';
            $t_head[] = 'image';
        }
        $tb->setColsStyle($t_head);
        $tb->addHead($c_head);
        foreach ($levels as $lv => $levelname) {
            $c_body = [$levelname];

            foreach ($tokens as $k => $token) {
                $c_body[] = '<input class="check" type="checkbox" '
                            . 'id="perm_' . $lv . '_' . $token['code'] . '" '
                            . 'name="perm[' . $lv . '][' . $token['code'] . ']" value="1"'
                            . (isset($perm[$lv][$token['code']]) ? ' checked="checked"' : '') . ' />'
                        . '<label class="access-only" for="perm_' . $lv . '_' . $token['code'] . '">'
                        . $lang_perm->def($token['name']) . '</label>' . "\n";
            }
            if (count($tokens) > 1) {
                $c_body[] = '<img class="handover"'
                    . ' onclick="checkall(\'' . $form_name . '\', \'perm[' . $lv . ']\', true); return false;"'
                    . ' src="' . getPathImage() . 'standard/checkall.png" alt="' . $lang->def('_CHECKALL') . '" />';
                $c_body[] = '<img class="handover"'
                    . ' onclick="checkall(\'' . $form_name . '\', \'perm[' . $lv . ']\', false); return false;"'
                    . ' src="' . getPathImage() . 'standard/uncheckall.png" alt="' . $lang->def('_UNCHECKALL') . '" />';
            }
            $tb->addBody($c_body);
        }
        $c_select_all = [''];
        foreach ($tokens as $k => $token) {
            $c_select_all[] = '<img class="handover"'
                    . ' onclick="checkall_fromback(\'' . $form_name . '\', \'[' . $token['code'] . ']\', true); return false;"'
                    . ' src="' . getPathImage() . 'standard/checkall.png" alt="' . $lang->def('_CHECKALL') . '" />'
                . '<img class="handover"'
                    . ' onclick="checkall_fromback(\'' . $form_name . '\', \'[' . $token['code'] . ']\', false); return false;"'
                    . ' src="' . getPathImage() . 'standard/uncheckall.png" alt="' . $lang->def('_UNCHECKALL') . '" />';
        }
        if (count($tokens) > 1) {
            $c_select_all[] = '';
            $c_select_all[] = '';
        }
        $tb->addBody($c_select_all);

        return $tb->getTable();
    }

    public function getSelectedPermission()
    {
        $tokens = $this->getAllToken();
        $levels = CourseLevel::getTranslatedLevels();
        $perm = [];
        foreach ($levels as $lv => $levelname) {
            $perm[$lv] = [];
            foreach ($tokens as $k => $token) {
                if (isset($_POST['perm'][$lv][$token['code']])) {
                    $perm[$lv][$token['code']] = 1;
                }
            }
        }

        return $perm;
    }

    public function selectPerm($op, $list)
    {
        $output = [];
        if (is_string($list)) {
            $list = explode(',', $list);
        }
        if (!is_array($list)) {
            return $output;
        }

        $tokens = $this->getAllToken($op);
        foreach ($list as $code) {
            $index = trim($code);
            if (isset($tokens[$index])) {
                $output[$index] = $tokens[$index];
            }
        }

        return $output;
    }

    public function getPermissionsForMenu($op)
    {
        return [
            1 => $this->selectPerm($op, 'view'),
            2 => $this->selectPerm($op, 'view'),
            3 => $this->selectPerm($op, 'view'),
            4 => $this->selectPerm($op, 'view'),
            5 => $this->selectPerm($op, 'view'),
            6 => $this->selectPerm($op, 'view'),
            7 => $this->selectPerm($op, 'view'),
        ];
    }
}
