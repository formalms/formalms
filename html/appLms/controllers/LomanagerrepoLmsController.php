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

class LomanagerrepoLmsController extends LomanagerLmsController
{
    public $name = 'lo-manager-repo';

    protected function setTab()
    {
        checkPerm('public', false, 'storage');
        $this->model->setTdb(LomanagerLms::REPODIRDB, $this->idCourse);
    }

    public function setCurrentTab()
    {
        $this->model->setCurrentTab(LomanagerLms::STORAGE_REPODIRDB);
        echo json_encode(true);
        exit;
    }

    public function getTab()
    {
        if (checkPerm('public', true, 'storage')) {
            return [
                'active' => $this->model->getCurrentTab() === LomanagerLms::STORAGE_REPODIRDB,
                'type' => LomanagerLms::REPODIRDB,
                'controller' => 'lomanagerrepo',
                'type' => $this->model::REPODIRDB,
                'edit' => true,
                'title' => Lang::t('_PUBREPOROOTNAME', 'storage'),
                'data' => $this->getFolders($this->idCourse, false),
                'currentState' => serialize([$this->getCurrentState(0)]),
                'scormPlayerEnabled' => false,
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
                $lo['actions'][] = [
                    'name' => 'play',
                    'active' => true,
                    'type' => 'submit',
                    'content' => "pubrepo[treeview_opplayitem_pubrepo][$id]",
                    'showIcon' => true,
                    'icon' => 'icon-play',
                    'label' => 'Play',
                ];
            }
            if ($lo['canEdit']) {
                if (!$lo['is_folder']) {
                    $lo['actions'][] = [
                        'name' => 'edit',
                        'active' => true,
                        'type' => 'link',
                        'content' => "index.php?r=lms/lomanagerrepo/edit&id=$id&type=$type",
                        'showIcon' => true,
                        'icon' => 'icon-edit',
                        'label' => 'Edit',
                    ];
                }

                if (!$lo['is_folder']) {
                    $lo['actions'][] = [
                        'name' => 'copy',
                        'active' => true,
                        'type' => 'ajax',
                        'content' => "index.php?r=lms/lomanagerrepo/copy&id=$id&type=$type&newType=",
                        'showIcon' => true,
                        'icon' => 'icon-copy',
                        'label' => 'Copy',
                    ];
                }

                $lo['actions'][] = [
                    'name' => 'delete',
                    'active' => true,
                    'type' => 'link',
                    'content' => "index.php?r=lms/lomanagerrepo/delete&id=$id&type=$type",
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
