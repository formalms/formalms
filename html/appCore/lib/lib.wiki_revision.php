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

/**
 * @author   Giovanni Derks <virtualdarkness[AT]gmail-com>
 *
 * @version  $Id: $
 */
// ----------------------------------------------------------------------------

require_once _adm_ . '/lib/lib.revision.php';

class WikiRevisionManager extends RevisionManager
{
    public function WikiRevisionManager($default_keys_val = [], $prefix = false, $dbconn = null)
    {
        $this->prefix = ($prefix !== false ? $prefix : $GLOBALS['prefix_fw']);
        $this->dbconn = $dbconn;

        $this->table_keys = [
            'wiki_id',
            'page_id',
            'language',
        ];
        $this->table_extra_fields = [
            'content',
        ];

        parent::RevisionManager($default_keys_val);
    }

    public function _getRevisionTable()
    {
        return $this->prefix . '_wiki_revision';
    }

    public function cleanInput($arr)
    {
        if (isset($arr['wiki_id'])) {
            $arr['wiki_id'] = (int) $arr['wiki_id'];
        }

        if (isset($arr['page_id'])) {
            $arr['page_id'] = (int) $arr['page_id'];
        }

        $arr = parent::cleanInput($arr);

        return $arr;
    }

    public function getLatestRevisionList($search = false, $ini = false, $vis_item = false)
    {
        $default_keys_val = $this->cleanInput($this->getDefaultKeysVal());
        $table_keys = $this->getTableKeys();
        $table_extra_fields = $this->getTableExtraFields();

        $query = 'SELECT page_id, MAX(version) as version'
                    . ' FROM ' . $this->_getRevisionTable()
                    . " WHERE wiki_id='" . $default_keys_val['wiki_id'] . "'"
                    . " AND language='" . $default_keys_val['language'] . "'"
                    . ' GROUP BY page_id';

        $result = sql_query($query);

        $fields = 'author, rev_date, version, content';
        $fields .= (count($table_keys) > 0 ? ', ' . implode(', ', $table_keys) : '');
        $fields .= (count($table_extra_fields) > 0 ? ', ' . implode(', ', $table_extra_fields) : '');

        $data_info = [];
        $data_info['data_arr'] = [];
        $data_info['data_tot'] = 0;
        $data_info['user'] = [];

        while (list($page_id, $max_version) = sql_fetch_row($result)) {
            $qtxt = 'SELECT ' . $fields . ' FROM ' . $this->_getRevisionTable() . ' ';
            $qtxt .= "WHERE wiki_id='" . $default_keys_val['wiki_id'] . "' ";
            $qtxt .= "AND language='" . $default_keys_val['language'] . "' ";
            $qtxt .= ($search !== false ? "AND content LIKE '%" . $search . "%' " : '');
            $qtxt .= " AND page_id = '" . $page_id . "'";
            $qtxt .= " AND version = '" . $max_version . "'";
            //$qtxt .= " GROUP BY wiki_id, page_id, language ";
            //$qtxt .= "ORDER BY version DESC";

            list($author, $rev_date, $version, $content) = sql_fetch_row(sql_query($qtxt));

            if ($content) {
                $data_info['data_arr'][]['author'] = $author;
                $data_info['data_arr'][]['rev_date'] = $rev_date;
                $data_info['data_arr'][]['version'] = $version;
                $data_info['data_arr'][]['wiki_id'] = $default_keys_val['wiki_id'];
                $data_info['data_arr'][]['page_id'] = $page_id;
                $data_info['data_arr'][]['language'] = $default_keys_val['language'];
                $data_info['data_arr'][]['content'] = $content;

                ++$data_info['data_tot'];

                $acl_manager = Docebo::user()->getAclManager();
                $user_info = $acl_manager->getUser($author);
                $data_info['user'][$author] = $acl_manager->relativeId($user_info[$author][ACL_INFO_USERID]);
            }
        }

        return $data_info;
    }

    public function searchInLatestRevision($return_val, $search, $ini = false, $vis_item = false)
    {
        $data = $this->getLatestRevisionList($search, $ini, $vis_item);

        $res = $this->searchInLatestRevisionData($return_val, $data);

        return $res;
    }
}
