<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

/**
 * @version 	$Id: lib.treedb.php 974 2007-02-17 00:25:06Z giovanni $
 */
class Folder
{
    // TreeDb object
    public $tdb;
    public $id;
    public $idParent;
    public $path;
    public $level;
    public $ileft;
    public $iRight;
    public $otherValues;
    public $countChildrens;
    public $nested;

    public function __construct(&$tdb, $arrayValues, $childInfo = false, $nested = false)
    {
        $this->tdb = $tdb;
        $this->id = $arrayValues[0];
        $this->idParent = $arrayValues[1];
        $this->path = $arrayValues[2];
        $this->level = $arrayValues[3];
        $this->nested = $nested;

        if ($nested) {
            $this->iLeft = $arrayValues[4];
            $this->iRight = $arrayValues[5];

            if ($childInfo) {
                $this->otherValues = array_slice($arrayValues, 7);
                $this->countChildrens = $arrayValues[6];
            } else {
                if (is_array($arrayValues)) {
                    $this->otherValues = array_slice($arrayValues, 6);
                    $this->hasChildrens = null;
                }
            }
        } else {
            if ($childInfo) {
                $this->otherValues = array_slice($arrayValues, 5);
                $this->countChildrens = $arrayValues[4];
            } else {
                if (is_array($arrayValues)) {
                    $this->otherValues = array_slice($arrayValues, 4);
                    $this->hasChildrens = null;
                }
            }
        }
    }

    public function countChildrens()
    {
        return $this->countChildrens;
    }

    public function getFolderName()
    {
        if (($pos = strrpos($this->path, '/')) === false) {
            return $this->path;
        }

        return substr($this->path, $pos + 1);
    }

    public function getFolderPath()
    {
        return $this->path;
    }

    public function setFolderPath($path)
    {
        $this->path = $path;
    }

    public function getParentPath()
    {
        if (($pos = strrpos($this->path, '/')) === false) {
            return '';
        }

        return substr($this->path, 0, $pos);
    }

    public function getChildrens()
    {
        $rs = $this->tdb->getChildrens($this);
        $coll = new FoldersCollection($this->tdb, $rs, false, $this->nested);

        return $coll;
    }

    public function rename($newFolderName)
    {
        $this->tdb->renameFolder($this, $newFolderName);
    }

    public function delete()
    {
        $this->tdb->deleteTreeByPath($this->path);
    }

    public function move($newParentFolder)
    {
        return $this->tdb->moveFolder($this, $newParentFolder);
    }

    public function reorder($newParentFolder, $newOrder = [])
    {
        return $this->tdb->reorder($this->id, $newParentFolder, $newOrder);
    }
}

class TreeDb
{
    // table name
    public $table;
    // associative array of field's names
    // id -> id of the record
    // idParent -> id of the parent
    // path -> full path
    // lev -> level starting from 1; 0 is root
    public $fields;

    // database connection
    public $dbconn = null;

    protected $session;

    public function __construct()
    {
        $this->session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    }

    public function _listFields($tname = false)
    {
        return $this->_getBaseFields($tname)
             . $this->_getOtherFields($tname);
    }

    public function isDISTINCT()
    {
        return false;
    }

    public function _getDISTINCT()
    {
        if ($this->isDISTINCT()) {
            return ' DISTINCT ';
        }
    }

    public function _getBaseFields($tname = false)
    {
        if ($tname === false) {
            return $this->fields['id'] . ', '
                 . $this->fields['idParent'] . ', '
                 . $this->fields['path'] . ', '
                 . $this->fields['lev'];
        } else {
            return $tname . '.' . $this->fields['id'] . ', '
                 . $tname . '.' . $this->fields['idParent'] . ', '
                 . $tname . '.' . $this->fields['path'] . ', '
                 . $tname . '.' . $this->fields['lev'];
        }
    }

    public function _getBaseFieldId($tname = false)
    {
        if ($tname === false) {
            return $this->fields['id'];
        } else {
            return $tname . '.' . $this->fields['id'];
        }
    }

    public function _getOtherTables()
    {
        return '';
    }

    public function _getJoinFilter($table = false)
    {
        return false;
    }

    public function _outJoinFilter($table = false)
    {
        $jf = $this->_getJoinFilter($table);
        if ($jf === false) {
            return '';
        } else {
            return ' ON (' . $jf . ')';
        }
    }

    public function _getArrBaseFields($tname)
    {
        return ['id' => $tname . '.' . $this->fields['id'],
                        'idParent' => $tname . '.' . $this->fields['idParent'],
                        'path' => $tname . '.' . $this->fields['path'],
                        'lev' => $tname . '.' . $this->fields['lev'], ];
    }

    public function _getOtherFields($tname = false)
    {
        return '';
    }

    // As the previous one, but used only in the select fields list:
    public function _getOtherSelectFields($tname = false)
    {
        return '';
    }

    public function _getOtherValues()
    {
        return '';
    }

    public function _getOtherUpdates()
    {
        return '';
    }

    public function _getParentJoinFilter($t1name, $t2name)
    {
        return '';
    }

    public function _getFilter()
    {
        return '';
    }

    public function _getOrderBy($tname)
    {
        $fields = $this->_getArrBaseFields($tname);

        return $fields['path'];
    }

    public function _executeQuery($query)
    {
        if ($this->dbconn === null) {
            $rs = sql_query($query);
        } else {
            $rs = sql_query($query, $this->dbconn);
        }

        return $rs;
    }

    public function _executeInsert($query)
    {
        $res = null;
        if ($this->dbconn === null) {
            $res = sql_query($query);
        } else {
            $res = sql_query($query, $this->dbconn);
        }

        if (!$res) {
            return false;
        }
        if ($this->dbconn === null) {
            return sql_insert_id();
        } else {
            return sql_insert_id($this->dbconn);
        }
    }

    public function _printSQLError($funcname)
    {
        if ($this->dbconn === null) {
            exit("Error on $funcname " . sql_error());
        } else {
            exit("Error on $funcname " . sql_error($this->dbconn));
        }
    }

    public function getChildrensIdById($idFolder, $onlyFolders = false)
    {
        $fields = $this->_getArrBaseFields($this->table);
        $query = 'SELECT ' . $this->_getDISTINCT() . $fields['id']
                . ' FROM ' . $this->table . $this->_getOtherTables()
                . $this->_outJoinFilter($this->table)
                . ' WHERE ((' . $fields['idParent'] . " = '" . (int) $idFolder . "')"
                . $this->_getFilter($this->table)
                . ') ' . ($onlyFolders ? " AND objectType = ''" : '') . ' ORDER BY ' . $this->_getOrderBy($this->table);
        $rs = $this->_executeQuery($query)
                or exit(sql_error() . " [ $query ]");
        // or $this->_printSQLError( 'getChildrensById' );
        if (sql_num_rows($rs) === 0) {
            return false;
        } else {
            $result = [];
            while (list($id) = sql_fetch_row($rs)) {
                $result[$id] = $id;
            }
        }

        return $result;
    }

    public function getDescendantsId($folder)
    {
        $fields = $this->_getArrBaseFields($this->table);
        $query = 'SELECT ' . $this->_getDISTINCT() . $fields['id']
                . ' FROM ' . $this->table . $this->_getOtherTables()
                . $this->_outJoinFilter($this->table)
                . " WHERE ((path LIKE '" . (($folder->id == 0) ? '' : sql_escape_string($folder->path)) . "/%')"
                . '   AND (' . $fields['id'] . " != '" . $folder->id . "') "
                . $this->_getFilter($this->table)
                . ') ORDER BY ' . $this->_getOrderBy($this->table);
        $rs = $this->_executeQuery($query)
                or exit(sql_error() . " [ $query ]");
        // or $this->_printSQLError( 'getChildrensById' );
        if (sql_num_rows($rs) === 0) {
            return false;
        } else {
            $result = [];
            while (list($id) = sql_fetch_row($rs)) {
                $result[] = $id;
            }
        }

        return $result;
    }

    /**
     * return a record set with all childrens of given folder.
     *
     * @param $idFolder id of parent folder
     *
     * @return ResultSet of all childrens
     **/
    public function getChildrensById($idFolder)
    {
        $fields = $this->_getArrBaseFields($this->table);
        $query = 'SELECT ' . $this->_getDISTINCT() . $this->_listFields($this->table)
                . ' FROM ' . $this->table . $this->_getOtherTables()
                . $this->_outJoinFilter($this->table)
                . ' WHERE ((' . $fields['idParent'] . " = '" . (int) $idFolder . "')"
                . $this->_getFilter($this->table)
                . ') ORDER BY ' . $this->_getOrderBy($this->table);
        $rs = $this->_executeQuery($query)
            or $this->_printSQLError('getChildrensById');

        return $rs;
    }

    /**
     * return a record set with all childrens of given folder.
     *
     * @param $foder parent folder
     *
     * @return ResultSet of all childrens
     **/
    public function getChildrensByFolder(&$folder)
    {
        return $this->getChildrensById($folder->id);
    }

    /**
     * @param Folder $folder il folder di cui si vogliono tutti gli avi
     * @param TreeDb $tdb    l'albero cui $folder appartiene
     *
     * @return array un array contenente tutti gli id degli avi di $folder
     *               nella posizione 0 c'e' il padre di $folder, nella
     *               posizione 1 c'e' il nonno etc etc
     **/
    public function getAllParentId(&$folder, &$tdb)
    {
        $path = $folder->getParentPath();
        $arr_ancestors = [];
        while ($path != '') {
            $parentFolder = &$tdb->getFolderByPath($path);
            if ($parentFolder !== null && $parentFolder->id != false) {
                $arr_ancestors[] = $parentFolder->id;
                $path = $parentFolder->getParentPath();
            } else {
                $path = '';
            }
        }

        return $arr_ancestors;
    }

    /**
     * Propagate change in a folder in all chidrens
     * The changes to propagate are path and level.
     *
     * @param $prevFolder previous folder
     * @param $newFolder new folder
     **/
    public function _propagateChange($prevFolder, $newFolder)
    {
        $len = strlen($prevFolder->path) + 1;
        $levDiff = $newFolder->level - $prevFolder->level;
        $query = 'UPDATE ' . $this->table
                . ' SET '
                . $this->fields['path']
                . " = CONCAT('" . $newFolder->path . "', SUBSTRING( path, " . $len . ' )),'
                . $this->fields['lev'] . ' = ( ' . $this->fields['lev'] . ' + (' . $levDiff . ')) '
                . " WHERE ((path LIKE '" . sql_escape_string($prevFolder->path) . "/%')"
                . '   AND (' . $this->fields['id'] . " != '" . $prevFolder->id . "')) "
                . $this->_getFilter();

        return $this->_executeQuery($query);
    }

    public function &getRootFolder()
    {
        $folder = new Folder($this, [0, 0, '/root', 0]);

        return $folder;
    }

    public function getFoldersCollection(&$arrayId)
    {
        $query = 'SELECT ' . $this->_getDISTINCT() . $this->_getBaseFields('t1') . ', count(t2.' . $this->fields['id'] . ') '
                . $this->_getOtherSelectFields('t1')
                . $this->_getOtherFields('t1')
                . ' FROM ' . $this->table . ' AS t1 LEFT JOIN ' . $this->table . ' AS t2'
                . '		ON (t1.' . $this->fields['id'] . ' = t2.' . $this->fields['idParent']
                . $this->_getParentJoinFilter('t1', 't2') . ')'
                . $this->_getOtherTables('t1')
                . $this->_outJoinFilter('t1');
        if ($arrayId === null) {
            $query .= ' WHERE ((1) '
                    . $this->_getFilter('t1');
        } else {
            $query .= ' WHERE ((t1.' . $this->fields['id'] . ' IN ('
                    . (!empty($arrayId) ? implode(',', $arrayId) : 'NULL')
                    . ')) '
                    //."   AND ((t1.".$this->fields['id']." = t2.".$this->fields['idParent'] .")"
                    //."    OR  (t1.".$this->fields['id']." = 0 ))"
                    . $this->_getFilter('t1');
        }
        $query .= ') ' . ' AND t1.' . $this->fields['id'] . '<>0 '
                . 'GROUP BY ' . $this->_getBaseFields('t1')
                . $this->_getOtherFields('t1')
                . ' ORDER BY ';
        if ($arrayId === null) {
            $query .= $this->_getOrderBy('t1');
        } else {
            $query .= !empty($arrayId) ? 'FIELD(' . $this->_getBaseFieldId('t1') . ', ' . implode(',', $arrayId) . ')' : $this->_getOrderBy('t1');
        }

        $rs = $this->_executeQuery($query)
                or $this->_printSQLError('getFoldersCollection: ' . $query);

        $coll = new FoldersCollection($this, $rs, true);

        return $coll;
    }

    /**
     * Get folder by id.
     *
     * @param $id id of the folder to retrieve
     *
     * @return Folder object or NULL if not found
     **/
    public function &getFolderById($id)
    {
        if ($id == 0) {
            $folder = &$this->getRootFolder();
        } else {
            $fields = $this->_getArrBaseFields($this->table);
            $query = 'SELECT ' . $this->_getDISTINCT() . $this->_listFields($this->table)
                    . ' FROM ' . $this->table . $this->_getOtherTables()
                    . $this->_outJoinFilter($this->table)
                    . ' WHERE (' . $fields['id'] . " = '" . (int) $id . "')"
                    . $this->_getFilter($this->table);
            $rs = $this->_executeQuery($query)
                    or $this->_printSQLError('getFolderById');
            if (sql_num_rows($rs) == 0) {
                $false_var = null;

                return $false_var;
            }
            $folder = new Folder($this, sql_fetch_row($rs));
        }

        return $folder;
    }

    /**
     * Get folder by path.
     *
     * @param int $id id of the folder to retrieve
     *
     * @return Folder object or NULL if not found
     **/
    public function &getFolderByPath($path)
    {
        $fields = $this->_getArrBaseFields($this->table);
        $query = 'SELECT ' . $this->_getDISTINCT() . $this->_listFields($this->table)
                . ' FROM ' . $this->table . $this->_getOtherTables()
                . $this->_outJoinFilter($this->table)
                . ' WHERE (' . $fields['path'] . "='" . sql_escape_string($path) . "')"
                . $this->_getFilter($this->table);
        $rs = $this->_executeQuery($query)
                or $this->_printSQLError('getFolderByPath: ' . $query);
        if (sql_num_rows($rs) == 0) {
            $false_var = null;

            return $false_var;
        }
        $folder = new Folder($this, sql_fetch_row($rs));

        return $folder;
    }

    /**
     * Get folder path array from id array.
     *
     * @param array $arr_id array of id
     *
     * @return array the array of path
     **/
    public function getPathFromFolderId($arr_id)
    {
        $query = 'SELECT ' . $this->fields['id'] . ', ' . $this->fields['path']
                . ' FROM ' . $this->table
                . ' WHERE ' . $this->fields['id'] . " IN ('" . implode("','", $arr_id) . "')";
        $rs = $this->_executeQuery($query)
                or $this->_printSQLError('getPathFromFolderId: ' . $query);
        $arr_result = [];
        while (list($idFolder, $path) = sql_fetch_row($rs)) {
            $arr_result[$idFolder] = $path;
        }

        return $arr_result;
    }

    /**
     * Add a folder children of folder identified by id.
     *
     * @param idParent id of parent folder
     * @param folderName name of folder to add
     **/
    public function _addFolder($idParent, $path, $level)
    {
        $query = 'INSERT into ' . $this->table
            . '( ' . $this->_listFields()
            . ') VALUES ('
            . "NULL,'" . (int) $idParent . "','" . $path . "','" . (int) $level . "'"
            . $this->_getOtherValues()
            . ')';
        $id = $this->_executeInsert($query)
                or $this->_printSQLError('_addFolder: ' . $query);

        return $id;
        //$folder = new Folder( $this, array( $id, $idParent, $path, $level) );
    }

    /**
     * Add a folder children of folder identified by id.
     *
     * @param idParent id of parent folder
     * @param folderName name of folder to add
     **/
    public function addFolderById($idParent, $folderName)
    {
        $parent = $this->getFolderById($idParent);
        $path = sql_escape_string($parent->path) . '/' . $folderName;
        $level = $parent->level + 1;

        return $this->_addFolder($idParent, $path, $level);
    }

    public function addFolderByPath($fullPath, $createAll)
    {
        $fields = $this->_getArrBaseFields($this->table);
        // search most near folder
        $query = 'SELECT ' . $this->_getDISTINCT() . $this->_listFields($this->table)
                . ' FROM ' . $this->table
                . " WHERE (('" . $fullPath . "' LIKE CONCAT(" . $fields['path'] . ",'%'))"
                . $this->_getFilter($this->table)
                . ') ORDER BY ' . $this->_getOrderBy($this->table)
                . ' LIMIT 0,1';
        $rs = $this->_executeQuery($query)
                or $this->_printSQLError('addFolderByPath');
        $parentFolder = new Folder($this, sql_fetch_row($rs));

        // get path tokens
        $pathTokens = explode('/', $fullPath);

        // verify level
        if (count($pathTokens) <= $parentFolder->level) {	// directory exist
            return false;
        }
        if (count($pathTokens) > ($parentFolder->level + 1) && !$createAll) { // only one level
            return false;
        }

        $newFolder = $parentFolder;
        for ($index = $parentFolder->level + 1; $index < count($pathTokens); ++$index) {
            $newFolder = $this->_addFolder($newFolder->id,
                                            sql_escape_string($newFolder->path) . $pathTokens[$index],
                                            $newFolder->level + 1);
        }

        return $newFolder;
    }

    public function _deleteTree($folder)
    {
        if ($folder === null) {
            return false;
        }
        if (trim($folder->path) == '') { // this remove all!!
            return false;
        }
        $query = 'DELETE FROM ' . $this->table
                . ' WHERE ((' . $this->fields['path'] . " LIKE '" . addslashes($folder->path) . "/%')"
                . '    OR  (' . $this->fields['id'] . " = '" . (int) $folder->id . "'))"
                . $this->_getFilter();
        $this->_executeQuery($query)
            or $this->_printSQLError('_deleteTree');

        return true;
    }

    public function deleteAllTree()
    {
        $query = 'DELETE FROM ' . $this->table
                . ' WHERE 1 ' . $this->_getFilter();
        $this->_executeQuery($query)
            or $this->_printSQLError('deleteAllTree');

        return true;
    }

    public function deleteTreeById($id)
    {
        $folder = $this->getFolderById($id);

        return $this->_deleteTree($folder);
    }

    public function deleteTreeByPath($path)
    {
        $folder = $this->getFolderByPath($path);

        return $this->_deleteTree($folder);
    }

    /**
     * Return TRUE if a folderA is ancestor of folderB
     * NOTE: this function don't query the db
     * NOTE: if folderA === folderB return is TRUE.
     *
     * @param folderA possible ancestor folder
     * @param folderB possible descentant
     *
     * @return - TRUE if folderA is ancestr of folderB
     *           - FALSE otherwise
     **/
    public function checkAncestor($folderA, $folderB)
    {
        if (strpos($folderB->path, strval($folderA->path)) !== false) {
            return true;
        } else {
            return false;
        }
    }

    public function moveFolder(&$folder, &$parentFolder, $newfoldername = false)
    {
        $oldFolder = clone $folder;

        $folder->idParent = $parentFolder->id;
        if ($parentFolder->id == 0) {
            $folder->path = '/root/';
        } else {
            $folder->path = $parentFolder->path . '/';
        }
        if ($newfoldername !== false) {
            $folder->path .= $newfoldername;
        } else {
            $folder->path .= $oldFolder->getFolderName();
        }

        $folder->level = $parentFolder->level + 1;
        $query = 'UPDATE ' . $this->table
                . ' SET '
                . $this->fields['idParent'] . " = '" . $folder->idParent . "',"
                . $this->fields['path'] . " = '" . addslashes($folder->path) . "',"
                . $this->fields['lev'] . " = '" . $folder->level . "'"
                . ' WHERE (' . $this->fields['id'] . " = '" . $folder->id . "')"
                . $this->_getFilter();
        $this->_executeQuery($query)
            or $this->_printSQLError('moveFolder');
        $this->_propagateChange($oldFolder, $folder);
    }

    public function renameFolder(&$folder, $newName)
    {
        $oldFolder = ((version_compare(phpversion(), '5.0') < 0) ? $folder : clone $folder);

        $folder->path = $oldFolder->getParentPath() . '/' . $newName;
        $query = 'UPDATE ' . $this->table
                . ' SET '
                . $this->fields['path'] . " = '" . addslashes($folder->path) . "'"
                . ' WHERE ((' . $this->fields['id'] . " = '" . $folder->id . "')"
                . $this->_getFilter() . ')';
        $res = $this->_executeQuery($query);
        if (!$res) {
            $this->_printSQLError('renameFolder [' . $query . ']');
        }
        $this->_propagateChange($oldFolder, $folder);

        return $res;
    }

    public function changeOtherData(&$folder)
    {
        $query = 'UPDATE ' . $this->table
                . ' SET '
                . $this->_getOtherUpdates()
                . ' WHERE (' . $this->fields['id'] . " = '" . $folder->id . "')"
                . $this->_getFilter();

        return $this->_executeQuery($query)
            or $this->_printSQLError('changeOtherData');
    }
}

class FoldersCollection
{
    public $rs;
    public $tdb;
    public $childInfo;
    public $nested;

    public function FoldersCollection(&$tdb, $rs, $childInfo = false, $nested = false)
    {
        $this->tdb = $tdb;
        $this->rs = $rs;
        $this->childInfo = $childInfo;
        $this->nested = $nested;
    }

    public function count()
    {
        return sql_num_rows($this->rs);
    }

    public function getFirst()
    {
        if (!sql_data_seek($this->rs, 0)) {
            return false;
        }

        return $this->getNext();
    }

    public function getNext()
    {
        $array = sql_fetch_row($this->rs);
        if ($array === false) {
            return false;
        }
        $folder = new Folder($this->tdb, $array, $this->childInfo, $this->nested);

        return $folder;
    }
}
