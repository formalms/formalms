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

class LomanagerorganizationLmsController extends LomanagerLmsController
{
    public $name = 'lo-manager-organization';

    protected function setTab()
    {
        checkPerm('lesson', false, 'storage');
        $this->model->setTdb(LomanagerLms::ORGDIRDB, $_SESSION['idCourse']);
    }

    public function setCurrentTab()
    {
        $this->model->setCurrentTab(LomanagerLms::STORAGE_ORGDIRDB);
        echo json_encode(true);
        exit;
    }

    public function getTab()
    {
        if (checkPerm('lesson', true, 'storage')) {
            return [
                'active' => $this->model->getCurrentTab() === LomanagerLms::STORAGE_ORGDIRDB,
                'type' => LomanagerLms::ORGDIRDB,
                'controller' => 'lomanagerorganization',
                'type' => $this->model::ORGDIRDB,
                'edit' => true,
                'title' => Lang::t('_ORGROOTNAME', 'storage'),
                'data' => $this->getFolders($_SESSION['idCourse'], false),
                'currentState' => serialize([$this->getCurrentState(0)]),
                'scormPlayerEnabled' => true,
            ];
        } else {
            return null;
        }
    }

    public static function formatLoData($loData)
    {
        $results = [];
        foreach ($loData as $lo) {
            $type = $lo['typeId'];
            $id = $lo['id'];
            $lo['image_type'] = self::getLearningObjectIcon($lo);
            $lo['actions'] = [];
            if (!$lo['is_folder']) {
                if ($lo['play'] && !$lo['canEdit']) {
                    $lo['actions'][] = [
                        'name' => 'play',
                        'active' => true,
                        'type' => 'link',
                        'content' => "index.php?modname=organization&op=custom_playitem&id_item=$id",
                        'showIcon' => false,
                        'icon' => 'icon-play',
                        'label' => 'Play',
                    ];
                } elseif ($lo['canEdit']) {
                    $lo['actions'][] = [
                        'name' => 'play',
                        'active' => true,
                        'type' => 'link',
                        'content' => "index.php?modname=organization&op=custom_playitem&edit=1&id_item=$id",
                        'showIcon' => false,
                        'icon' => 'icon-play',
                        'label' => 'Play',
                    ];
                }
            }
            if ($lo['canEdit']) {
                if (!$lo['is_folder']) {
                    $lo['actions'][] = [
                        'name' => 'edit',
                        'active' => true,
                        'type' => 'link',
                        'content' => "index.php?r=lms/lomanagerorganization/edit&id=$id&type=$type",
                        'showIcon' => true,
                        'icon' => 'icon-edit',
                        'label' => 'Edit',
                    ];
                }

                $lo['actions'][] = [
                    'name' => 'properties',
                    'active' => true,
                    'type' => 'submit',
                    'content' => "${type}[org_opproperties][$id]",
                    'showIcon' => true,
                    'icon' => 'icon-properties',
                    'label' => 'Properties',
                ];

                $lo['actions'][] = [
                    'name' => 'access',
                    'active' => true,
                    'type' => 'submit',
                    'content' => "${type}[org_opaccess][$id]",
                    'showIcon' => true,
                    'icon' => 'icon-access',
                    'label' => 'Access',
                ];

                if ($lo['canBeCategorized']) {
                    $lo['actions'][] = [
                        'name' => 'categorize',
                        'active' => true,
                        'type' => 'submit',
                        'content' => "${type}[org_opcategorize][$id]",
                        'showIcon' => true,
                        'icon' => 'icon-categorize',
                        'label' => 'Categorize',
                    ];
                }

                if (!$lo['is_folder']) {
                    $lo['actions'][] = [
                        'name' => 'copy',
                        'active' => true,
                        'type' => 'ajax',
                        'content' => "index.php?r=lms/lomanagerorganization/copy&id=$id&type=$type&newType=",
                        'showIcon' => true,
                        'icon' => 'icon-copy',
                        'label' => 'Copy',
                    ];
                }

                $lo['actions'][] = [
                    'name' => 'delete',
                    'active' => true,
                    'type' => 'link',
                    'content' => "index.php?r=lms/lomanagerorganization/delete&id=$id&type=$type",
                    'showIcon' => true,
                    'icon' => 'icon-delete',
                    'label' => 'Delete',
                ];
            }
            $results[] = $lo;
        }

        return $results;
    }
}
