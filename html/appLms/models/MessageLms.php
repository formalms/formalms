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

class MessageLms extends Model
{
    protected $db;

    public function __construct()
    {
        $this->db = \FormaLms\db\DbConn::getInstance();
        parent::__construct();
    }

    public function deleteAttachment($attachment)
    {
        require_once _base_ . '/lib/lib.upload.php';
        $path = _PATH_MESSAGE;
        sl_open_fileoperations();
        $re = sl_unlink($path . $attachment);
        sl_close_fileoperations();

        return $re;
    }

    public function saveAttachment($attach)
    {
        require_once _base_ . '/lib/lib.upload.php';
        $path = _PATH_MESSAGE;
        $file = '';
        sl_open_fileoperations();
        if (isset($attach['tmp_name']['attach']) && $attach['tmp_name']['attach'] != '') {
            $file = \FormaLms\lib\FormaUser::getCurrentUser()->getIdSt() . '_' . mt_rand(0, 100) . '_' . time() . '_' . $attach['name']['attach'];
            if (!sl_upload($attach['tmp_name']['attach'], $path . $file)) {
                $error = 1;
                $file = '';
            }
        }
        sl_close_fileoperations();
        if (!$error) {
            return $file;
        }

        return false;
    }

    public function deleteMessage($id)
    {
        require_once _adm_ . '/lib/lib.message.php';

        $del_query = "UPDATE %adm_message_user SET deleted = '" . _OPERATION_SUCCESSFUL . "' WHERE idUser='" . \FormaLms\lib\FormaUser::getCurrentUser()->getIdSt() . "' AND idMessage = '" . (int) $id . "'";
        if (!$this->db->query($del_query)) {
            return false;
        }

        $query = "SELECT idMessage FROM %adm_message_user WHERE idMessage = '" . (int) $id . "'";
        $res = $this->db->query($query);
        if ($res && $this->db->num_rows($res) > 0) {
            $query = "SELECT attach FROM %adm_message WHERE idMessage = '" . (int) $id . "'";
            $res = $this->db->query($query);
            list($attach) = $this->db->fetch_row($res);
            if ($attach != '') {
                if (!$this->deleteAttachment($attach)) {
                    return false;
                }
            }

            if (!$this->db->query("DELETE FROM %adm_message_user WHERE idMessage = '" . (int) $id . "'")) {
                return false;
            }
            if (!$this->db->query("DELETE FROM %adm_message WHERE idMessage = '" . (int) $id . "'")) {
                return false;
            }
        }

        return true;
    }
}
