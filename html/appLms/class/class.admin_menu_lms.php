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

define('_LMS_STATS_MENU', 9);

require_once _adm_ . '/class/class.admin_menu.php';
//require_once(_i18n_.'/lib.lang.php');

class Admin_Lms extends Admin
{
    /**
     * class constructor.
     *
     * @param FormaUser $user the object of the Forma User, for permission control
     *
     * @return nothing
     */
    public function __construct(&$user)
    {
        $this->user = &$user;
        $this->platform = 'lms';
        $this->table_level_one = $GLOBALS['prefix_lms'] . '_menu';
        $this->table_level_two = $GLOBALS['prefix_lms'] . '_menu_under';

        $lang = &DoceboLanguage::createInstance('menu', 'lms');

        $query_menu = '
		SELECT idMenu, idUnder, module_name, default_name, default_op, associated_token, of_platform, mvc_path
		FROM ' . $this->table_level_two . '
		WHERE 1
		ORDER BY sequence';
        $re_menu = sql_query($query_menu);

        $this->menu = [];
        while (list($idm, $id, $module_name, $name, $op, $token, $of_platform, $mvc_path) = sql_fetch_row($re_menu)) {
            if ($this->user->matchUserRole('/' . ($of_platform === null ? $this->platform : $of_platform) . '/admin/' . $module_name . '/' . $token)) {
                $this->menu[$idm][$id] = ['modname' => $module_name,
                                    'op' => $op,
                                    'link' => ($mvc_path == ''
                                        ? 'index.php?modname=' . $module_name . '&op=' . $op . '&of_platform=' . ($of_platform === null ? $this->platform : $of_platform)
                                        : 'index.php?r=' . $mvc_path
                                    ),
                                    'name' => ($name != '' ? $lang->def($name, 'menu', 'lms') : $lang->def('_' . strtoupper($module_name), 'menu', 'lms')),
                                    'of_platform' => ($of_platform === null ? $this->platform : $of_platform), ];
            }
        }
    }

    /**
     * @return mixed a list of the first level menu
     *               [id] (	[link]
     *               [image]
     *               [name]  )
     */
    public function getLevelOne()
    {
        $lang = &DoceboLanguage::createInstance('menu', 'lms');

        $query_under = '
		SELECT tab.idMenu, menu.module_name, menu.associated_token, tab.name, tab.image, tab.collapse, menu.of_platform
		FROM ' . $this->table_level_one . ' AS tab JOIN ' . $this->table_level_two . ' AS menu
		WHERE tab.idMenu = menu.idMenu
		ORDER BY tab.sequence';
        $re_under = sql_query($query_under);

        $menu = [];
        while (list($id_main, $module_name, $token, $name, $image, $collapse, $of_platform) = sql_fetch_row($re_under)) {
            if (!isset($menu[$id_main]) && checkPerm($token, true, $module_name, ($of_platform === null ? $this->platform : $of_platform))) {
                $menu[$id_main] = ['link' => 'index.php?op=change_main&new_main=' . $id_main . '&of_platform=' . ($of_platform === null ? $this->platform : $of_platform),
                                    'name' => ($name != '' ? $lang->def($name, 'menu', 'lms') : ''),
                                    'image' => 'area_title/' . $image,
                                    'collapse' => ($collapse == 'true' ? true : false),
                                    'of_platform' => ($of_platform === null ? $this->platform : $of_platform), ];
            }
        }

        return $menu;
    }

    public function getLevelOneIntest($idMenu)
    {
        $lang = &DoceboLanguage::createInstance('menu', 'lms');

        $query_menu = '
		SELECT name, image
		FROM ' . $this->table_level_one . "
		WHERE idMenu = '" . (int) $idMenu . "'";
        $re_menu = sql_query($query_menu);

        list($name, $image) = sql_fetch_row($re_menu);

        return [
            'name' => ($name != '' ? $lang->def($name, 'menu') : ''),
            'image' => getPathImage('framework') . 'area_title/' . $image,
        ];
    }

    /**
     * @param int $id_level_one the id of a level one menu voice
     *
     * @return mixed a list of the second level menu of a passed first level menu,
     *               if not passed return all the voice of the second level
     *               [id] (	[link]
     *               [name]  )
     */
    public function getLevelTwo($id_level_one = false)
    {
        return $this->menu[$id_level_one];
    }
}

class Admin_Managment_Lms extends Admin_Managment
{
    /**
     * class constructor.
     *
     * @return nothing
     */
    public function __construct()
    {
        $this->platform = 'lms';
        $this->table_level_one = $GLOBALS['prefix_lms'] . '_menu';
        $this->table_level_two = $GLOBALS['prefix_lms'] . '_menu_under';

        $this->lang_over = &DoceboLanguage::createInstance('menu', 'lms');
        $this->lang = &DoceboLanguage::createInstance('menu', 'lms');
        $this->lang_perm = &DoceboLanguage::createInstance('permission');
    }
}
