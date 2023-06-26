<?php

namespace FormaLms\lib\Services\Courses;

use FormaACLManager;
use FormaLms\lib\Services\BaseService;
use FormaLms\lib\Interfaces\Accessible;



require_once _lms_.'/lib/lib.course.php';


class LearningGroupService extends BaseService implements Accessible
{

    protected FormaACLManager $aclManager;

    public function __construct() {

        $this->aclManager = new FormaACLManager();
        parent::__construct();
    }

    public function getAccessList($resourceId) : array {

        return $this->aclManager->getGroupUMembers($resourceId);
    }

    public function setAccessList($resourceId, array $selection) : bool {

        $old_users = $this->aclManager->getGroupUMembers($resourceId);

        $add_members = array_diff($selection, $old_users);
        $del_members = array_diff($old_users, $selection);

        if ($selection === $old_users) {
           return true;
        }

        if (count($add_members)) {
            foreach ($add_members as $idst_user) {
                $this->aclManager->addToGroup($resourceId, $idst_user);
            }
        }
        if (count($del_members)) {
            foreach ($del_members as $idst_user) {
                $this->aclManager->removeFromGroup($resourceId, $idst_user);
            }
        }

        return true;
    }
}