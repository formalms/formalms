<?php

namespace FormaLms\lib\Services\Advices;

use FormaLms\lib\Interfaces\Accessible;
use FormaLms\lib\Services\BaseService;



class AdviceService extends BaseService implements Accessible
{

    public function getAccessList($resourceId) : array {

                $query_reader = '
                SELECT idUser
                FROM %lms_adviceuser
                WHERE idAdvice = "' . $resourceId . '"';
            $re_reader = sql_query($query_reader);
            $users = [];
            $all_reader = false;
            while (list($id_user) = sql_fetch_row($re_reader)) {
                if ($id_user == 'all') {
                    $all_reader = true;
                }
                $users[] = $id_user;
            }
            if ($all_reader == true) {
                $query_reader = "
                    SELECT idUser
                    FROM %lms_courseuser
                    WHERE idCourse = '" . $this->session->get('idCourse') . "'";
                $re_reader = sql_query($query_reader);
                $users = [];
                foreach ($re_reader as $row) {
                    $users[] = $row['idUser'];
                }
            }
        return $users;
    }

    public function setAccessList($resourceId, array $selection) : bool {

        $old_users = $this->getAccessList($resourceId);

 
        $add_reader = array_diff($selection, $old_users);
        $del_reader = array_diff($old_users,  $selection);

     
        $dest = [];
        if (count($add_reader)) {
          
            foreach ($add_reader as $idst) {
                $query_insert = '
                    INSERT INTO %lms_adviceuser
                    ( idUser, idAdvice ) VALUES
                    ( 	"' . $idst . '",
                        "' . $resourceId . '" )';
            
                $dest[] = $idst;
                $res = sql_query($query_insert);
            }  
           
           
        }
        
        if (count($del_reader)) {
           
            foreach ($del_reader as $idst) {
                $query_delete = '
                    DELETE FROM %lms_adviceuser
                    WHERE idUser="' . $idst . '" AND idAdvice="' . $resourceId . '";';
                    $res = sql_query($query_delete);
            } 
          
        }
       return true;
    }


    public function send($idAdvice, $destinations) {

        if (is_array($destinations)) {
            require_once _base_ . '/lib/lib.eventmanager.php';
    
            $query_advice = '
                SELECT title, description, important
                FROM %lms_advice
                WHERE idAdvice="' . (int) $idAdvice . '"';
            list($title, $description, $impo) = sql_fetch_row(sql_query($query_advice));
    
            $msg_composer = new \EventMessageComposer();
    
            $msg_composer->setSubjectLangText('email', '_ALERT_SUBJECT', false);
            $msg_composer->setBodyLangText('email', '_ALERT_TEXT', [
                '[url]' => \FormaLms\lib\Get::site_url(),
                '[course]' => $GLOBALS['course_descriptor']->getValue('name'),
                '[title]' => stripslashes($title),
                '[text]' => stripslashes($description),
            ]);
    
            $msg_composer->setBodyLangText('sms', '_ALERT_TEXT_SMS', [
                '[url]' => \FormaLms\lib\Get::site_url(),
                '[course]' => $GLOBALS['course_descriptor']->getValue('name'),
                '[title]' => stripslashes($title),
                '[text]' => stripslashes($description),
            ]);
    
            createNewAlert(
                'AdviceNew',
                'advice',
                'add',
                '1',
                'Inserted advice ' . $title . ' in course ' . $this->session->get('idCourse'),
                $destinations,
                $msg_composer
            );
        }
   

    }
}