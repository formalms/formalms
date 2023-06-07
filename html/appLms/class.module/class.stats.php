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

class Module_Stats extends LmsModule
{
    public function loadBody()
    {
        $GLOBALS['page']->setWorkingZone('page_head');

        switch ($GLOBALS['op']) {
            case 'statuser':
                $GLOBALS['page']->add('<link href="' . getPathTemplate() . 'style/base-old-treeview.css" rel="stylesheet" type="text/css" />' . "\n");
                //$GLOBALS['page']->add( '<link href="'.getPathTemplate().'style/style_scormplayer.css" rel="stylesheet" type="text/css" />'."\n" );
             break;
            case 'statitem':
            case 'statcourse':
            case 'statoneuser':
            case 'statoneuseroneitem':
                $GLOBALS['page']->add('<link href="' . getPathTemplate() . 'style/base-old-treeview.css" rel="stylesheet" type="text/css" />' . "\n");
                $GLOBALS['page']->add('<link href="' . getPathTemplate() . 'style/report/style_report_general.css" rel="stylesheet" type="text/css" />' . "\n");
                //$GLOBALS['page']->add( '<link href="'.getPathTemplate().'style/style_organizations.css" rel="stylesheet" type="text/css" />'."\n" );
                //$GLOBALS['page']->add( '<link href="'.getPathTemplate().'style/style_scormplayer.css" rel="stylesheet" type="text/css" />'."\n" );
        }
        require _lms_ . '/modules/stats/stats.php';
    }

    public static function getAllToken($op = '')
    {
        if ($op == 'statuser') {
            return ['view_user' => ['code' => 'view_user',
                                'name' => '_VIEW',
                                'image' => 'standard/view.png', ],
                    'view_all_statuser' => ['code' => 'view_all_statuser',
                                'name' => '_VIEW_ALL',
                                'image' => 'standard/moduser.png', ],
            ];
        } else {
            return ['view_course' => ['code' => 'view_course',
                                'name' => '_VIEW',
                                'image' => 'standard/view.png', ],
                    'view_all_statcourse' => ['code' => 'view_all_statcourse',
                                'name' => '_VIEW_ALL',
                                'image' => 'standard/moduser.png', ],
            ];
        }
    }

    public function getPermissionsForMenu($op)
    {
        return [
            1 => $this->selectPerm($op, ''),
            2 => $this->selectPerm($op, ''),
            3 => $this->selectPerm($op, ''),
            4 => $this->selectPerm($op, 'view_user,view_course'),
            5 => $this->selectPerm($op, 'view_user,view_course'),
            6 => $this->selectPerm($op, 'view_user,view_course,view_all_statuser,view_all_statcourse'),
            7 => $this->selectPerm($op, 'view_user,view_course,view_all_statuser,view_all_statcourse'),
        ];
    }
}
