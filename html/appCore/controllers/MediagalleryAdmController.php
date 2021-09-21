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

    public function show($type = null, $msg = null)
    {
        require_once Forma::inc(_lib_ . '/formatable/include.php');

        if (!$type) {
            $type = Get::req("type", DOTY_STRING, null);
        }
        $authentic_request = Util::getSignature();

        switch ($type) {
            case 'image':
                $accepted_mime = 'image/x-png,image/gif,image/jpeg';
                break;
            case 'media':
                $accepted_mime = 'video/mp4,video/x-m4v,video/*';
                break;
            default:
                $accepted_mime = '*';
        }

        if (Docebo::user()->isAnonymous()) {
            die("You can't access!");
        }

        $p_size = (int)ini_get('post_max_size');
        $u_size = (int)ini_get('upload_max_filesize');
        $comparison = array($p_size, $u_size);
        if (!is_null($max_size)) {
            $comparison[] = (int)$max_size;
        }
        $max_kb = min($comparison);

        $this->render('show', [
            'type' => $type,
            'authentic_request' => $authentic_request,
            'accepted_mime' => $accepted_mime,
            'max_upload_size' => $max_kb,
            'msg' => $msg,
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

    public function uploadTask()
    {
        if (!$this->canAccessPersonalMedia()) {
            die("You can't access!");
        }

        include_once(_base_ . '/lib/lib.upload.php');
        include_once(_base_ . '/lib/lib.multimedia.php');

        $user_id = Docebo::user()->getIdSt();
        $type = Get::req("type", DOTY_STRING, null);
        $msg = $error = null;

        if ((isset($_FILES["file"]["name"])) && (!empty($_FILES["file"]["name"]))) {
            $fname             = $_FILES["file"]["name"];
            $size             = $_FILES["file"]["size"];
            $tmp_fname         = $_FILES["file"]["tmp_name"];
            $real_fname     = $user_id . '_' . mt_rand(0, 100) . '_' . time() . '_' . $fname;

            $upload_whitelist =Get::sett('file_upload_whitelist', 'rar,exe,zip,jpg,gif,png,txt,csv,rtf,xml,doc,docx,xls,xlsx,ppt,pptx,odt,ods,odp,pdf,xps,mp4,mp3,flv,swf,mov,wav,ogg,flac,wma,wmv,jpeg');
            $upload_whitelist_arr =explode(',', trim($upload_whitelist, ','));
            $array = explode(".", $fname);
            $ext = strtolower(end($array));
            if (!in_array($ext, $upload_whitelist_arr)) {
                $error = Lang::t('_INVALID_EXTENSION', 'standard');
            } else {
                sl_open_fileoperations();

                define("_USER_FPATH_INTERNAL", "/common/users/");
                define("_USER_FPATH", $GLOBALS["where_files_relative"] . _USER_FPATH_INTERNAL);

                $f1 = sl_upload($tmp_fname, _USER_FPATH_INTERNAL . $real_fname);
                sl_close_fileoperations();
                if (!$f1) {
                    // upload error
                    $error = Lang::t('_ERROR_UPLOAD', 'standard');
                }
            }
        } else {
            $media_url = $_POST["media_url"];
            $fname = "";
            $real_fname = "";

            if (!empty($media_url)) {
                if (isYouTube($media_url)) {
                    $fname = str_replace("http://www.", "", strtolower($media_url));
                } else {
                    $fname = basename($media_url);
                    $fname = (strpos($fname, "?") !== FALSE ? preg_replace("/(\?.*)/", "", $fname) : $fname);
                }
            }
        }

        if ($error) {
            $msg = ['class' => 'danger', 'text' => $error];
        } else {
            $qtxt = "INSERT INTO " . $GLOBALS["prefix_fw"] . "_user_file ";
            $qtxt .= " ( user_idst, type, fname, real_fname, media_url, size, uldate ) VALUES ";
            $qtxt .= " ($user_id, '$type', '$fname', '" . addslashes($real_fname) . "', '$media_url', '$size', NOW())";
            sql_query($qtxt);

            $msg = ['class' => 'success', 'text' => 'Media caricato con successo'];
        }

        return $this->show($type, $msg);
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

        require_once(_base_ . '/lib/lib.mimetype.php');
        require_once(_base_ . '/lib/lib.multimedia.php');

        if (!$this->canAccessPersonalMedia()) {
            die("You can't access!");
        }

        $user_id     = (int)Docebo::user()->getIdSt();
        $qtxt = "SELECT * FROM " . $GLOBALS["prefix_fw"] . "_user_file WHERE user_idst='" . $user_id . "' AND type = '$type'";
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
                    // $type = getMediaType($row["media_url"]);
                    $site_url = $row["media_url"];
                } else {
                    // $type = getMediaType($row["fname"]);
                }

                $results['data'][] = [
                    'id' => $row['id'],
                    'user_idst' => $row['user_idst'],
                    'type' => $type,
                    'fname' => $row['fname'],
                    'real_fname' => $row['real_fname'],
                    'size' => str_replace('.', ',', round($row['size'] / 1024, 2)) . ' Kb',
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
