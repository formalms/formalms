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

class Module_Public_Forum extends LmsModule
{
    public function useExtraMenu()
    {
        return false;
    }

    public function loadExtraMenu()
    {
        $lang = &DoceboLanguage::createInstance('forum');
        $line = '<div class="legend_line">';
        echo $line . '<img src="' . getPathImage() . 'standard/add.png" /> ' . $lang->def('_REPLY') . '</div>'
            . $line . '<img src="' . getPathImage() . 'standard/edit.png" /> ' . $lang->def('_MOD') . '</div>'
            . $line . '<img src="' . getPathImage() . '/forum/free.gif" /> ' . $lang->def('_FORUMOPEN') . '</div>'
            . $line . '<img src="' . getPathImage() . '/forum/locked.gif" /> ' . $lang->def('_FORUMCLOSED') . '</div>';
        if (checkPerm('mod', true)) {
            $line . '<img src="' . getPathImage() . 'forum/erase.gif" /> ' . $lang->def('_DEL') . '</div>';
            $line . '<img src="' . getPathImage() . 'forum/unerase.gif" /> ' . $lang->def('_RESTOREINSERT') . '</div>';
        }
        if (checkPerm('del', true)) {
            $line . '<img src="' . getPathImage() . 'standard/delete.png" /> ' . $lang->def('_DEL') . '</div>';
        }
    }

    public function loadBody()
    {
        require_once _lms_ . '/modules/' . $this->module_name . '/' . $this->module_name . '.php';
        forumDispatch($GLOBALS['op']);
    }

    public static function getAllToken()
    {
        return [
            'view' => ['code' => 'view',
                                'name' => '_VIEW',
                                'image' => 'standard/view.png', ],
            'write' => ['code' => 'write',
                                'name' => '_REPLY',
                                'image' => 'forum/write.gif', ],
            'upload' => ['code' => 'upload',
                                'name' => '_UPPLOAD',
                                'image' => 'forum/upload.gif', ],
            'add' => ['code' => 'add',
                                'name' => '_ADD',
                                'image' => 'standard/add.png', ],
            'mod' => ['code' => 'mod',
                                'name' => '_MOD',
                                'image' => 'standard/edit.png', ],
            'del' => ['code' => 'del',
                                'name' => '_DEL',
                                'image' => 'standard/delete.png', ],
            'moderate' => ['code' => 'moderate',
                                'name' => '_MODERATE',
                                'image' => 'forum/moderate.gif', ],
        ];
    }
}
