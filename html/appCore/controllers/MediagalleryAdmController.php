<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */

class MediagalleryAdmController extends AdmController
{

    public function show()
    {
        require_once Forma::inc(_lib_ . '/formatable/include.php');

        $type = Get::req("type", DOTY_STRING, null);
        $authentic_request = Util::getSignature();

        /* if (!Docebo::user()->isAnonymous()) {
            die("You can't access!");
        }*/

        $this->render("show", [
            'type' => $type,
            'authentic_request' => $authentic_request,
        ]);
    }

    private function canAccessPersonalMedia()
    {
        $level_id = Docebo::user()->getUserLevelId();
        if (Docebo::user()->isAnonymous()) return false;

        if ((Get::sett("htmledit_image_godadmin") && $level_id == ADMIN_GROUP_GODADMIN) ||
            ((Get::sett("htmledit_image_admin")) && ($level_id == ADMIN_GROUP_ADMIN)) ||
            ((Get::sett("htmledit_image_user")) && ($level_id == ADMIN_GROUP_USER))
        ) {
            return true;
        } else {
            return false;
        }
    }

    public function deleteTask()
    {
        $user_id  = (int)Docebo::user()->getIdSt();
        $id = Get::req("id", DOTY_STRING, null);

        define("_USER_FPATH_INTERNAL", "/common/users/");
        define("_USER_FPATH", $GLOBALS["where_files_relative"] . _USER_FPATH_INTERNAL);

        require_once(_base_ . '/lib/lib.mimetype.php');
        require_once(_base_ . '/lib/lib.multimedia.php');

        if (!$this->canAccessPersonalMedia()) {
            die("You can't access!");
        }

        $results = null;

        $qtxt = "SELECT * FROM " . $GLOBALS["prefix_fw"] . "_user_file WHERE user_idst='" . $user_id . "' AND id = $id";
        $q = sql_query($qtxt);

        if (($q) && (sql_num_rows($q) > 0)) {
            if ($row = sql_fetch_array($q)) {
                $file = empty($row["media_url"]) ? _USER_FPATH . rawurlencode($row["real_fname"]) : $row["media_url"];

                $results = [
                    'id' => $row['id'],
                    'file' => $file,
                ];

                // Delete file
                @unlink($file);
                $qtxt = "DELETE FROM " . $GLOBALS["prefix_fw"] . "_user_file WHERE user_idst='" . $user_id . "' AND id = $id";
                $q = sql_query($qtxt);
            }
        }

        die(json_encode($results));
    }

    public function listTask()
    {
        $type = Get::req("type", DOTY_STRING, null);

        define("_USER_FPATH_INTERNAL", "/common/users/");
        define("_USER_FPATH", $GLOBALS["where_files_relative"] . _USER_FPATH_INTERNAL);

        // define("POPUP_MOD_NAME", "mod_media");
        // $lang = &DoceboLanguage::createInstance('popup_' . POPUP_MOD_NAME, 'framework');

        require_once(_base_ . '/lib/lib.mimetype.php');
        require_once(_base_ . '/lib/lib.multimedia.php');

        if (!$this->canAccessPersonalMedia()) {
            die("You can't access!");
        }

        $user_id     = (int)Docebo::user()->getIdSt();
        $qtxt = "SELECT * FROM " . $GLOBALS["prefix_fw"] . "_user_file WHERE user_idst='" . $user_id . "'";
        $q = sql_query($qtxt);

        $path = (strlen(dirname($_SERVER['PHP_SELF'])) != 1 ? dirname($_SERVER['PHP_SELF']) : '') . '/';
        $path .= $GLOBALS["where_files_relative"];
        $results = [
            'data' => [],
        ];

        if (($q) && (sql_num_rows($q) > 0)) {
            while ($row = sql_fetch_array($q)) {
                $site_url = "http://" . $_SERVER['HTTP_HOST'] . $path . '/common/users/';

                if (empty($row["media_url"])) {
                    $file = _USER_FPATH . rawurlencode($row["real_fname"]);
                }

                if (!empty($row["media_url"])) {
                    $type = getMediaType($row["media_url"]);
                    $site_url = $row["media_url"];
                } else {
                    $type = getMediaType($row["fname"]);
                }

                $results['data'][] = [
                    'id' => $row['id'],
                    'user_idst' => $row['user_idst'],
                    'type' => $type,
                    'fname' => $row['fname'],
                    'real_fname' => $row['real_fname'],
                    'size' => $row['size'],
                    'uldate' => $row['uldate'],
                    'file' => $file,
                    'url' => $site_url . $row['real_fname'],
                ];
            }
            $results['recordsFiltered'] = sql_num_rows($q);
            $results['recordsTotal'] = sql_num_rows($q);
        }

        die(json_encode($results));
    }
}
