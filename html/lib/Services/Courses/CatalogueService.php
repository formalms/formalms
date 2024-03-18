<?php

namespace FormaLms\lib\Services\Courses;

use FormaLms\lib\Interfaces\Accessible;
use FormaLms\lib\Services\BaseService;



class CatalogueService extends BaseService implements Accessible
{

    public function getAccessList($resourceId) : array {

        $members = [];
        $re_members = sql_query('
            SELECT idst_member
            FROM ' . $GLOBALS['prefix_lms'] . "_catalogue_member
            WHERE idCatalogue = '" . $resourceId . "'");

        while (list($id_members) = sql_fetch_row($re_members)) {
            $members[$id_members] = $id_members;
        }

        return array_values($members);
    }

    public function setAccessList($resourceId, array $selection) : bool {

        $old_members = [];
        $re_members = sql_query('
    SELECT idst_member
    FROM ' . $GLOBALS['prefix_lms'] . "_catalogue_member
    WHERE idCatalogue = '" . $resourceId . "'");
        while (list($id_members) = sql_fetch_row($re_members)) {
            $old_members[$id_members] = $id_members;
        }
      
        $to_add = array_diff($selection, $old_members);
        $to_del = array_diff($old_members, $selection);
        $this->addToCatologue($to_add, $resourceId);
        $this->removeFromCatologue($to_del, $resourceId);
       
       return true;
    }


    private function addToCatologue($members, $id_catalogue)
    {
        $re = true;
        reset($members);
        foreach ($members as $id_m) {
            $query_insert = '
		INSERT INTO ' . $GLOBALS['prefix_lms'] . "_catalogue_member
		( idCatalogue, idst_member ) VALUES
		( '" . $id_catalogue . "', '" . $id_m . "' )";
            $re = sql_query($query_insert);

            // add event member, user in core_group_member
            $data = \Events::trigger('lms.catalog.member.assigned', [
            'idCatalogue' => $id_catalogue,
            'idst_member' => $id_m,
        ]);
        }
        reset($members);

        return $re;
    }

    private function removeFromCatologue($memebers, $id_catalogue)
    {
        $re = true;
        reset($memebers);
        foreach ($memebers as $id_m) {
            $query_delete = '
		DELETE FROM ' . $GLOBALS['prefix_lms'] . "_catalogue_member
		WHERE idCatalogue = '" . $id_catalogue . "' AND idst_member = '" . $id_m . "'";
            $re = sql_query($query_delete);

            // remove event member
            $data = \Events::trigger('lms.catalog.member.unassigned', [
          'idCatalogue' => $id_catalogue,
          'idst_member' => $id_m,
      ]);
        }
        reset($memebers);

        return $re;
    }


   
}