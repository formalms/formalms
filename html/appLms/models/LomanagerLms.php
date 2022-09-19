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

class LomanagerLms extends Model
{
    public const ORGDIRDB = 'organization';
    public const REPODIRDB = 'pubrepo';
    public const HOMEREPODIRDB = 'homerepo';

    public const STORAGE_HOMEREPODIRDB = 'storage_home';
    public const STORAGE_ORGDIRDB = 'storage_course';
    public const STORAGE_REPODIRDB = 'storage_pubrepo';

    private $tdb = null;
    private $treeView = null;
    private $module = null;

    public function __construct()
    {
        $this->setTdb();
        parent::__construct();
    }

    public function setTdb($type = self::ORGDIRDB, $idCourse = false, $idUser = false)
    {
        if ($idUser === false) {
            $idUser = getLogUserId();
        }

        switch ($type) {
            case self::ORGDIRDB:
                require_once Forma::inc(_lms_ . '/modules/organization/orglib.php');
                require_once Forma::inc(_lms_ . '/class.module/class.organization.php');
                $this->tdb = new OrgDirDb($idCourse);
                $this->module = new Module_Organization();
                break;
            case self::REPODIRDB:
                require_once Forma::inc(_lms_ . '/lib/lib.repo.php');
                require_once Forma::inc(_lms_ . '/class.module/class.pubrepo.php');
                $this->tdb = new RepoDirDb($GLOBALS['prefix_lms'] . '_repo', $idUser);
                $this->module = new Module_Pubrepo();
                break;
            case self::HOMEREPODIRDB:
                require_once Forma::inc(_lms_ . '/modules/homerepo/homerepo.php');
                require_once Forma::inc(_lms_ . '/class.module/class.homerepo.php');
                $this->tdb = new HomerepoDirDb($GLOBALS['prefix_lms'] . '_homerepo', $idUser);
                $this->module = new Module_Homerepo();
                break;
            default:
                throw new Error('Missing directory type in self constructor');
        }
        $this->treeView = new Org_TreeView($this->tdb, $type);

        return $this->tdb;
    }

    public function getTdb()
    {
        return $this->tdb;
    }

    public function getTreeView()
    {
        return $this->treeView;
    }

    public function getLearningObjects($rootId)
    {
        $this->treeView->reoderTree();

        return $this->treeView->getChildrensDataById($rootId);
    }

    public function getFolders($collection_id, $id = 0)
    {
        $learning_objects = $this->getLearningObjects($id);
        if (!isUserCourseSubcribed(getLogUserId(), $collection_id)) {
            foreach ($learning_objects as $index => $lo) {
                if (!$lo['isPublic']) {
                    $learning_objects[$index]['isPrerequisitesSatisfied'] = false;
                }
            }
        }

        return $learning_objects;
    }

    public function getFolderTree()
    {
        $root_folder = $this->treeView->tdb->getFolderById(0);
        $tree = [$root_folder->id => []];
        $tree = $this->treeView->getFolderTree($tree);

        return $tree;
    }

    public function getCurrentState($idFolder = 0)
    {
        return $this->treeView->getCurrentState($idFolder);
    }

    public function setCurrentTab($tab)
    {
        $storage = ['tabview_storage_status' => $tab];
        $this->session->set('storage', $storage);
        $this->session->save();

        return serialize($storage);
    }

    public function getCurrentTab()
    {
        $tab = self::STORAGE_ORGDIRDB;
        $storage = $this->session->get('storage');
        if ($storage) {
            $tab = $storage['tabview_storage_status'];
        } else {
            $this->setCurrentTab($tab);
        }

        return $tab;
    }

    public function deleteFolder($id)
    {
        $folder = $this->tdb->getFolderById((string) $id);
        $lo = createLO($folder->otherValues[REPOFIELDOBJECTTYPE]);
        if ($lo) {
            // delete categorized resource
            require_once _lms_ . '/lib/lib.kbres.php';
            $kbres = new KbRes();
            $kbres->deleteResourceFromItem(
                $folder->otherValues[REPOFIELDIDRESOURCE],
                $folder->otherValues[REPOFIELDOBJECTTYPE],
                'course_lo'
            );
            // ---------------------------
            $lo->del($folder->otherValues[REPOFIELDIDRESOURCE]);
        }

        return $this->tdb->_deleteTree($folder);
    }

    public function renameFolder($id, $newName)
    {
        $folder = $this->tdb->getFolderById((string) $id);

        return $this->tdb->renameFolder($folder, $newName);
    }

    public function moveFolder($id, $newParentId)
    {
        $folder = $this->tdb->getFolderById((string) $id);
        $newParent = $this->tdb->getFolderById((string) $newParentId);

        return $folder->move($newParent);
    }

    public function reorder($idToMove, $newParent, $newOrder)
    {
        $folder = $this->tdb->getFolderById((string) $idToMove);

        return $folder->reorder($newParent, $newOrder);
    }

    public function copy($id, $fromType)
    {
        require_once _adm_ . '/lib/lib.sessionsave.php';
        $saveObj = new Session_Save();
        $saveName = $saveObj->getName('crepo', true);
        $folder = $this->tdb->getFolderById((string) $id);
        $saveData = [
            'repo' => $fromType,
            'id' => $id,
            'objectType' => $folder->otherValues[REPOFIELDOBJECTTYPE],
            'name' => $folder->otherValues[REPOFIELDTITLE],
            'idResource' => $folder->otherValues[REPOFIELDIDRESOURCE],
        ];
        $saveObj->save($saveName, $saveData);

        return true;
    }

    public function paste($folderId)
    {
        require_once _adm_ . '/lib/lib.sessionsave.php';
        $saveObj = new Session_Save();
        if ($saveObj->nameExists('crepo')) {
            $saveData = &$saveObj->load('crepo');

            if ($saveData['objectType']) {
                $lo = createLO($saveData['objectType']);
                $idResource = $lo->copy((int) $saveData['idResource']);
                if ($idResource != 0) {
                    $idReference = $this->tdb->addItem($folderId,
                        $saveData['name'],
                        $saveData['objectType'],
                        $idResource,
                        0, /* idCategory */
                        0, /* idUser */
                        getLogUserId(), /* idAuthor */
                        '1.0' /* version */,
                        '_DIFFICULT_MEDIUM', /* difficult */
                        '', /* description */
                        '', /* language */
                        '', /* resource */
                        '', /* objective */
                        date('Y-m-d H:i:s')
                    );

                    return $idReference;
                }
            } elseif ($this->session->get('idCourse')) {
                // It's a directory
                return $this->tdb->addFolderById(0, $saveData['name'], $this->session->get('idCourse'));
            }
        }

        return false;
    }

    public function addFolderById($selectedNode, $folderName, $idCourse)
    {
        return $this->tdb->addFolderById($selectedNode, $folderName, $idCourse);
    }

    public function getLoTypes()
    {
        $query = 'SELECT objectType AS type FROM %lms_lo_types';
        $rs = sql_query($query);

        $lo_types = [
            [
                'type' => 'folder',
                'title' => Lang::t('_DIRECTORY', 'organization_chart'),
            ],
        ];
        while ($lo_type = sql_fetch_assoc($rs)) {
            $lo_type['title'] = Lang::t("_LONAME_{$lo_type['type']}", 'storage');
            $lo_types[] = $lo_type;
        }

        return $lo_types;
    }

    public function formatLoData($loData)
    {
        $results = [];
        foreach ($loData as $lo) {
            $type = $lo['typeId'];
            $id = $lo['id'];
            $lo['actions'] = [];
            if (!$lo['is_folder']) {
                if ($lo['play'] && !$lo['canEdit']) {
                    $lo['actions'][] = [
                        'name' => 'play',
                        'active' => true,
                        'type' => 'link',
                        'content' => 'index.php?modname=' . $this->module->module_name . "&op=custom_playitem&id_item=$id",
                        'showIcon' => false,
                        'icon' => 'icon-play',
                        'label' => 'Play',
                    ];
                } elseif ($lo['canEdit']) {
                    $lo['actions'][] = [
                        'name' => 'play',
                        'active' => true,
                        'type' => 'link',
                        'content' => 'index.php?modname=' . $this->module->module_name . "&op=custom_playitem&edit=1&id_item=$id",
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
                        'content' => "index.php?r=lms/lomanager/edit&id=$id&type=$type",
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
                        'content' => "index.php?r=lms/lomanager/copy&id=$id&type=$type&newType=",
                        'showIcon' => true,
                        'icon' => 'icon-copy',
                        'label' => 'Copy',
                    ];
                }

                $lo['actions'][] = [
                    'name' => 'delete',
                    'active' => true,
                    'type' => 'link',
                    'content' => "index.php?r=lms/lomanager/delete&id=$id&type=$type",
                    'showIcon' => true,
                    'icon' => 'icon-delete',
                    'label' => 'Delete',
                ];
            }
            $results[] = $lo;
        }

        return $results;
    }

    public function completeAction()
    {
        $this->module->initialize();
    }
}
