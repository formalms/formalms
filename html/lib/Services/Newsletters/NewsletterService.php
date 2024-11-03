<?php

namespace FormaLms\lib\Services\Newsletters;


use FormaLms\lib\Interfaces\Accessible;

class NewsletterService implements Accessible
{

    protected int $totalSent;

    public const _ANY_LANG_CODE = '-any-';

    public function getAccessList($resourceId) : array {

        //non si può modificare quindi torno sempre vuoto
        return [];
    }

    public function setAccessList($resourceId, array $selection) : bool {

        $send_to_idst = [];
        foreach ($selection as $idstMember) {
            $arr = \FormaLms\lib\Forma::getAclManager()->getGroupAllUser($idstMember);
            if ((is_array($arr)) && (count($arr) > 0)) {
                $send_to_idst = array_merge($arr, $send_to_idst);
                $send_to_idst = array_unique($send_to_idst);
            } else {
                $send_to_idst[] = $idstMember;
            }

            //non c'è bisogno perchè lo userselector già torna utenti prefiltrati dalle admin preference
           // if (\FormaLms\lib\FormaUser::getCurrentUser()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
           //     $send_to_idst = array_intersect($send_to_idst, $admin_users);
           // }
        }

        foreach ($send_to_idst as $key => $val) {
            $qtxt = 'INSERT INTO %adm_newsletter_sendto (id_send, idst, stime) ';
            $qtxt .= "VALUES ('" . (int) $resourceId . "', '" . (int) $val . "', NOW())";
            $q = sql_query($qtxt);
        }

        $qtxt = 'SELECT language FROM %adm_newsletter WHERE id="' . $resourceId . '"';
        $q = sql_query($qtxt);

        list($lang) = sql_fetch_row($q);

        if ($lang != static::_ANY_LANG_CODE) {
            $tot = count(\FormaLms\lib\Forma::getAclManager()->getUsersIdstByLanguage($lang, $send_to_idst));
        } else {
            $tot = count($send_to_idst);
        }

        $qtxt = 'UPDATE %adm_newsletter SET tot="' . $tot . '" WHERE id="' . $resourceId . '"';
        $q = sql_query($qtxt);

        $this->setTotalSent($tot);
       return true;
    }


    public function getTotalSent() : int {
        return $this->totalSent;
    }

    private function setTotalSent(int $totalSent) : self {
        $this->totalSent = $totalSent;
        return $this;
    }
}