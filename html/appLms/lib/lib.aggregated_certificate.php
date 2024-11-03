<?php

use FormaLms\lib\Interfaces\Accessible;

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

defined('IN_FORMA') or exit('Direct access is forbidden');

//define('COURSE', 0);
//define('COURSE_PATH', 1);

class AggregatedCertificate implements Accessible

{
    public const AGGREGATE_CERTIFICATE_TYPE_COURSE = 0;
    public const AGGREGATE_CERTIFICATE_TYPE_COURSE_PATH = 1;
    public const ALLOWED_CERTIFICATE_TYPES = [self::AGGREGATE_CERTIFICATE_TYPE_COURSE, self::AGGREGATE_CERTIFICATE_TYPE_COURSE_PATH];

    private $db;
    private $_id_cert;
    private $_title;
    private $_description;
    private $_idAssoc;
    private $_type_assoc;
    public array $assocTypesArr;
    public string $table_assign_agg_cert;
    public string $table_cert_meta_association_coursepath;
    public string $table_cert_meta_association_courses;
    public string $table_cert_meta_association;
    public string $table_cert_tags;
    public string $table_cert;

    protected $op = [
        'del_released' => 'delReleased',
        'del_association' => 'delAssociations',
        'associationusers' => 'associationUsers',
        'associationCourses' => 'associationCourses',
        'saveAssignment' => 'saveAssignment',
        'saveAssignmentUsers' => 'saveAssignmentUsers',
        'view_details' => 'viewdetails',
        'delmetacert' => 'delcertificate',
    ];

    public function __construct()
    {
        $this->db = \FormaLms\db\DbConn::getInstance();
        $this->table_cert = $GLOBALS['prefix_lms'] . '_certificate';  // TODO: remove, inserting appropriate libraries
        $this->table_cert_tags = $GLOBALS['prefix_lms'] . '_certificate_tags';

        $this->table_cert_meta_association = $GLOBALS['prefix_lms'] . '_aggregated_cert_metadata';
        $this->table_cert_meta_association_courses = $GLOBALS['prefix_lms'] . '_aggregated_cert_course';
        $this->table_cert_meta_association_coursepath = $GLOBALS['prefix_lms'] . '_aggregated_cert_coursepath';
        $this->table_assign_agg_cert = $GLOBALS['prefix_lms'] . '_aggregated_cert_assign';

        $this->assocTypesArr = [
            self::AGGREGATE_CERTIFICATE_TYPE_COURSE => $this->table_cert_meta_association_courses,
            self::AGGREGATE_CERTIFICATE_TYPE_COURSE_PATH => $this->table_cert_meta_association_coursepath,
        ];
    }

    /**
     * Get all Aggregate certificates (or meta cert).
     *
     * @param mixed $ini   limit rows
     * @param mixed $count if the query has to return only the count of the rows
     *
     * $aggCertArr['id_certificate']         Defines the id of the cert.
     * $aggCertArr['code']                   Defines the code of the cert.
     * $aggCertArr['name']                   Defines the name of the cert.
     * $aggCertArr['description']            Defines the description of the cert.
     *
     * @return array $aggCertArr
     *               OK
     */
    public function getAllAggregatedCerts($ini = 0, $count = false, $filter = [])
    {
        //search query of certificates
        $query_certificate = 'SELECT id_certificate, code, name, description 
                            FROM ' . $this->table_cert . ' WHERE meta = 1';

        if (!empty($filter)) { // Generalize with a filter variable
            if (isset($filter['filter_text'])) {
                $query_certificate .= " AND ( name LIKE '%" . $filter['filter_text'] . "%'"
                    . " OR code LIKE '%" . $filter['filter_text'] . "%' )";
            }
        }

        if (!$count) {
            $query_certificate .= ' ORDER BY id_certificate'
                . " LIMIT $ini," . FormaLms\lib\Get::sett('visuItem');
        }

        $rs = sql_query($query_certificate);
        $aggCertArr = [];
        $k = 0;
        while ($rows = sql_fetch_assoc($rs)) {
            $aggCertArr[$k]['id_certificate'] = (int) $rows['id_certificate'];
            $aggCertArr[$k]['code'] = $rows['code'];
            $aggCertArr[$k]['name'] = $rows['name'];
            $aggCertArr[$k]['description'] = $rows['description'];
            ++$k;
        }

        return $aggCertArr;
    }

    /**
     * Returns all metadata of aggr. cert.
     *
     * @param mixed $id_cert Certificate id (can be found in (prefix_lms . $this->table_cert) table
     *
     * @return array $arr_cert
     */
    public function getMetadata($id_cert)
    {
        $query = 'SELECT '
            . 'id_certificate, '
            . 'code, '
            . 'name, '
            . 'base_language, '
            . 'description, '
            . 'user_release '
            . 'FROM ' . $this->table_cert
            . ' WHERE id_certificate ' . (is_array($id_cert) ? 'IN (' . implode(', ', $id_cert) . ') ' : ' = ' . $id_cert);

        $rs = sql_query($query);

        return sql_fetch_assoc($rs);
    }

    /**
     * Returns all the metadata on associations related to a cert.
     * If i'm passing the id of the cert., i will get all associations metadata associated on the cert.
     * Instead, if i'm passing the id of the association, i will get the only object with the metadata.
     *
     * @param mixed $id_cert
     * @param mixed $id_association
     *
     * $associationsArr['idAssociation']
     * $associationsArr['title']
     * $associationsArr['description']
     *
     * @return array $associationsMetadataArr
     */
    public function getAssociationsMetadata($id_cert = 0, $id_association = 0, $ini = -1)
    {
        $associationsArr = [];
        $query = 'SELECT idAssociation, title, description'
            . ' FROM ' . $this->table_cert_meta_association
            . ($id_cert != 0 ? ' WHERE idCertificate = ' . $id_cert : '')
            . ($id_association != 0 ? ' WHERE idAssociation = ' . $id_association : '');

        if ($ini != -1) { // Setting offset for pagination
            $query .= ' ORDER BY idAssociation'
                . " LIMIT $ini," . FormaLms\lib\Get::sett('visuItem');
        }

        $rs = sql_query($query);

        $k = 0;
        foreach ($rs as $rows) {
            $associationsArr[$k]['idAssociation'] = (int) $rows['idAssociation'];
            $associationsArr[$k]['title'] = $rows['title'];
            $associationsArr[$k]['description'] = $rows['description'];
            ++$k;
        }

        return $associationsArr;
    }

    /**
     * Return all technical data about certificate.
     *
     * @param mixed $id_cert
     */
    public function getLayoutMetacert($id_cert)
    {
        $query = 'SELECT '
            . 'cert_structure, '
            . 'orientation, '
            . 'bgimage '
            . 'FROM ' . $this->table_cert
            . ' WHERE id_certificate = ' . $id_cert . '';

        return sql_fetch_assoc(sql_query($query));
    }

    /**
     * Return an array of 1 or more certs id depending on the idAssociation passed.
     *
     * @param int|array $idAssociation
     */
    public function getIdCertificate($idAssociation)
    {
        $q = 'SELECT idCertificate '
            . ' FROM ' . $this->table_cert_meta_association
            . ' WHERE idAssociation ' . (is_array($idAssociation) ? 'IN (' . implode(', ', $idAssociation) . ')' : ' = ' . $idAssociation);

        $rs = $this->db->query($q);

        while ($row = $this->db->fetch_assoc($rs)) {
            $idCertsArr[] = (int) $row['idCertificate'];
        }

        return $idCertsArr;
    }

    /**
     * Returns an array of id/s associations (object).
     *
     * @param int $idCert
     */
    public function getIdAssociations($idCert)
    {
        $query = 'SELECT idAssociation FROM ' . $this->table_cert_meta_association
            . ' WHERE idCertificate = ' . $idCert;

        $rs = sql_query($query);

        while ($rows = sql_fetch_assoc($rs)) {
            $idsArr[] = (int) $rows['idAssociation'];
        }

        return $idsArr;
    }

    /**
     * Returning all associations between idAssociation or,
     * if i pass an array of user, the ids of the link to whom the users belong.
     *
     * @param array|int $id_association
     * @param mixed     $type_assoc
     * @param mixed     $userIdsArr     optional (for filtering query with users)
     *
     * @return array $linksArr an array of 0 or more rows with the link ids
     */
    public function getAssociationLink($id_association = -1, $type_assoc, $userIdsArr = [], $distinct = false)
    {
        switch ($type_assoc) {
            case self::AGGREGATE_CERTIFICATE_TYPE_COURSE:
                $table = $this->table_cert_meta_association_courses;
                $field_link = 'idCourse';
                break;
            case self::AGGREGATE_CERTIFICATE_TYPE_COURSE_PATH:
                $table = $this->table_cert_meta_association_coursepath;
                $field_link = 'idCoursePath';
                break;
            default:
                return;
        }

        $q = 'SELECT '
            . ($distinct ? 'DISTINCT ' : '')
            . $field_link
            . ' FROM ' . $table
            . ' WHERE 1 = 1 '
            . ($id_association != -1 ? ' AND idAssociation ' . (is_array($id_association) ? ' IN (' . implode(', ', $id_association) . ')' : ' = ' . $id_association) : '')
            . (!empty($userIdsArr) ? ' AND idUser ' . (is_array($userIdsArr) ? ' IN (' . implode(', ', $userIdsArr) . ') ' : ' = ' . $userIdsArr) : '');

        $rs = sql_query($q);

        $assocArr = [];

        while ($row = sql_fetch_array($rs)) {
            $assocArr[] = (int) $row[$field_link];
        }

        return $assocArr;
    }

    /**
     * @param array|int $id_assoc
     * @param $type_assoc
     *
     * @return array|void
     */
    public function getAllUsersFromIdAssoc($id_assoc, $type_assoc)
    {
        $id_users = [];
        switch ($type_assoc) {
            case self::AGGREGATE_CERTIFICATE_TYPE_COURSE:
                $table = $this->table_cert_meta_association_courses;
                break;
            case self::AGGREGATE_CERTIFICATE_TYPE_COURSE_PATH:
                $table = $this->table_cert_meta_association_coursepath;
                break;
            default:
                return;
        }

        $q = 'SELECT '
            . 'DISTINCT idUser' // If i'm passing array of id user and array of links, i want to check if there are any assoc
            . ' FROM ' . $table
            . ' WHERE idAssociation ' . (is_array($id_assoc) ? ' IN (' . implode(', ', $id_assoc) . ')' : ' = ' . $id_assoc)
            . ' AND idUser != 0 '; // skip course / path placeholder

        $rs = sql_query($q);

        while ($rows = sql_fetch_assoc($rs)) {
            $id_users[] = (int) $rows['idUser'];
        }

        return $id_users;
    }

    public function getAllLinksFromIdAssoc($id_assoc, $type_assoc)
    {
        switch ($type_assoc) {
            case self::AGGREGATE_CERTIFICATE_TYPE_COURSE:
                $table = $this->table_cert_meta_association_courses;
                $field_link = 'idCourse';
                break;
            case self::AGGREGATE_CERTIFICATE_TYPE_COURSE_PATH:
                $table = $this->table_cert_meta_association_coursepath;
                $field_link = 'idCoursePath';
                break;
            default:
                return;
        }

        $q = 'SELECT'
            . " DISTINCT({$field_link})" // If i'm passing array of id user and array of links, i want to check if there are any assoc
            . ' FROM ' . $table
            . ' WHERE idAssociation = ' . $id_assoc
            . " AND idUser = 0 order by {$field_link}";

        $rs = sql_query($q);

        while ($rows = sql_fetch_assoc($rs)) {
            $idLinkField[] = (int) $rows[$field_link];
        }

        return $idLinkField;
    }

    /**
     * Getting all ids of the course from a metacertificate
     * Func. called by 'associationCourse' op.
     *
     * @param mixed $id_association
     */
    public function getIdsCourse($id_association, $id_user = 0)
    {
        $q = 'SELECT acc.idCourse FROM '
            . $this->table_cert_meta_association_courses . ' AS acc'
            . ' INNER JOIN learning_course c ON c.idCourse = acc.idCourse'
            . ' WHERE idAssociation = ' . $id_association
            . ' AND acc.idUser = ' . $id_user . ' AND acc.idCourse <> 0'
            . ' ORDER BY c.name ASC';

        $rs = sql_query($q);

        $idsCourseArr = [];
        while ($row = sql_fetch_array($rs)) {
            $idsCourseArr[] = (int) $row['idCourse'];
        }

        return $idsCourseArr;
    }

    public function getUserAndCourseFromIdAssoc($idAssoc, $type_assoc)
    {
        switch ($type_assoc) {
            case self::AGGREGATE_CERTIFICATE_TYPE_COURSE:
                $table = $this->table_cert_meta_association_courses;
                $field = 'idCourse';
                break;
            case self::AGGREGATE_CERTIFICATE_TYPE_COURSE_PATH:
                $table = $this->table_cert_meta_association_coursepath;
                $field = 'idCoursePath';
                break;
            default:
                return;
        }
        if (!is_array($idAssoc)) {
            $query = "SELECT idUser, {$field}"
                . ' FROM ' . $table
                . " WHERE idAssociation = '" . $idAssoc . "' and idUser <> 0 order by {$field}";
        } else {
            $idAssoc_str = implode(',', $idAssoc);
            $query = "SELECT idUser, {$field}"
                . ' FROM ' . $table
                . ' WHERE idAssociation in (' . $idAssoc_str . ") and idUser <> 0 order by {$field}";
        }

        $rs = sql_query($query);

        while ($rows = sql_fetch_assoc($rs)) {
            $status[$rows['idUser']][] = $rows[$field];
        }

        return $status;
    }

    public function getIdsCoursePath($id_association, $id_user = 0)
    {
        $q = 'SELECT idCoursePath FROM '
            . $this->table_cert_meta_association_coursepath
            . ' WHERE idAssociation = ' . $id_association
            . ' and idUser = ' . $id_user . ' and idCoursePath <> 0';

        $rs = sql_query($q);

        $idsCoursePathArr = [];
        while ($row = sql_fetch_array($rs)) {
            $idsCoursePathArr[] = (int) $row['idCoursePath'];
        }

        return $idsCoursePathArr;
    }

    public function getCoursePathList()
    {
        $q = ' SELECT id_path, path_name, path_descr
             FROM %lms_coursepath ';
        $rs = sql_query($q);

        $coursepathArr['data'] = [];
        $i = 0;
        while ($rows = sql_fetch_assoc($rs)) {
            $coursepathArr['data'][$i]['idCoursePath'] = $rows['id_path'];
            $coursepathArr['data'][$i]['nameCoursePath'] = $rows['path_name'];
            $coursepathArr['data'][$i]['descriptionCoursePath'] = $rows['path_descr'];
            ++$i;
        }
        $coursepathArr['recordsTotal'] = count($coursepathArr['data']);
        $coursepathArr['recordsFiltered'] = count($coursepathArr['data']);
        $coursepathArr['draw'] = 1;

        return $coursepathArr;
    }

    // TODO: Generalize in lib.course.php
    public function getCoursesArrFromId($idsArr)
    {
        $coursesList = [];
        $idsStr = implode(',', $idsArr);

        $q = "SELECT  %lms_course.code, %lms_course.name, if(%lms_category.path IS NULL, '/root/', path) as path
              from %lms_course 
              LEFT JOIN  %lms_category on %lms_course.idCategory = %lms_category.idCategory
              WHERE %lms_course.idCourse IN(" . $idsStr . ')';
        $i = 0;
        $rs = sql_query($q);
        while ($rows = sql_fetch_assoc($rs)) {
            $coursesList[$i]['codeCourse'] = $rows['code'];
            $coursesList[$i]['nameCourse'] = $rows['name'];
            $coursesList[$i]['pathCourse'] = substr($rows['path'], 6); // deleting '/root/' string part
            ++$i;
        }

        return $coursesList;
    }

    public function getAggregatedCertFileName($idUser, $idCertificate, $id_association)
    {
        $query = 'SELECT cert_file'
            . ' FROM ' . $this->table_assign_agg_cert
            . ' WHERE idUser = ' . intval($idUser)
            . ' AND idCertificate = ' . intval($idCertificate)
            . ' AND idAssociation = ' . intval($id_association);

        return sql_fetch_row(sql_query($query));
    }

    public function getCourseListFromIdCategory($idCategory = null)
    {
        $idCategory = (int) $idCategory;
        if (!isset($idCategory)) {
            $idCategory = 0;
        }

        $filter = [
            'id_category' => $idCategory,
            'classroom' => false,
            'descendants' => true,
        ];

        $courseModel = new CourseAlms();
        $courseResult = $courseModel->loadCourse(0, 9999, 'name', 'asc', $filter);
        $coursesList = [];
        foreach ($courseResult as $rows) {
            $courseRow = [];
            $courseRow['idCourse'] = $rows['idCourse'];
            $courseRow['codeCourse'] = $rows['code'];
            $courseRow['nameCourse'] = $rows['name'];
            if ($idCategory !== 0) {
                $courseRow['pathCourse'] = array_key_exists('path', $rows) ? substr($rows['path'], 6) : '';
            } else {
                $courseRow['pathCourse'] = Lang::t('_ALT_ROOT');
            }
            // deleting '/root/' string part

            switch ($rows['status']) {  // SOSTITUIRE ASSOLUTAMENTE CON qualche
                // riferimento al db o con un unico entry point
                case 0:
                    $courseRow['stateCourse'] = Lang::t('_CST_PREPARATION', 'course');
                    break;
                case 1:
                    $courseRow['stateCourse'] = Lang::t('_CST_AVAILABLE', 'course');
                    break;
                case 2:
                    $courseRow['stateCourse'] = Lang::t('_CST_CONFIRMED', 'course');
                    break;
                case 3:
                    $courseRow['stateCourse'] = Lang::t('_CST_CONCLUDED', 'course');
                    break;
                case 4:
                    $courseRow['stateCourse'] = Lang::t('_CST_CANCELLED', 'course');
                    break;
                default:
                    break;
            }
            $coursesList[] = $courseRow;
        }

        return $coursesList;
    }

    /**
     *  Return type of association (if the assoc. is btw courses, coursepath...
     *
     * @param $id_assoc
     *
     * @return int|string
     */
    public function getTypeAssoc($id_assoc)
    {
        $type_assoc = -1; // Assoc. not found.

        foreach ($this->assocTypesArr as $key => $table) {
            $q = 'SELECT * 
                FROM ' . $table . '
                WHERE idAssociation = ' . $id_assoc . ' AND idUser = 0';

            if (sql_num_rows(sql_query($q))) {
                $type_assoc = $key;
            }
        }

        return $type_assoc;
    }

    public function getCertificateTags()
    {
        $query = 'SELECT file_name, class_name FROM ' . $this->table_cert_tags;

        $rs = sql_query($query);

        while ($rows = sql_fetch_assoc($rs)) {
            $certificate_tags[] = $rows;
        }

        return $certificate_tags;
    }

    public function getLastInsertedIdCertificate()
    {
        return sql_fetch_row(sql_query('SELECT LAST_INSERT_ID() FROM ' . $this->table_cert))[0];
    }

    // ------------------------ Inserting queries ------------------------------

    /**
     * Inserting a new aggregate certificate.
     *
     * $metaDataArr['id_certificate']
     * $metaDataArr['code']
     * $metaDataArr['name']
     * $metaDataArr['base_language']
     * $metaDataArr['description']
     * $metaDataArr['meta']
     * $metaDataArr['user_release']
     *
     * @param mixed $metaDataArr
     */
    public function insertMetaDataCert($metaDataArr)
    {
        $fields = [];
        $values = [];

        if (empty($metaDataArr)) {
            return false;
        } // You never know...

        if (isset($metaDataArr['code'])) {
            $fields[] = 'code';
            $values[] = "'" . $metaDataArr['code'] . "'";
        }

        if (isset($metaDataArr['name'])) {
            $fields[] = 'name';
            $values[] = "'" . $metaDataArr['name'] . "'";
        }

        if (isset($metaDataArr['base_language'])) {
            $fields[] = 'base_language';
            $values[] = "'" . $metaDataArr['base_language'] . "'";
        }

        if (isset($metaDataArr['descr'])) {
            $fields[] = 'description';
            $values[] = "'" . $metaDataArr['descr'] . "'";
        }

        if (isset($metaDataArr['meta'])) {
            $fields[] = 'meta';
            $values[] = $metaDataArr['meta'];
        }

        if (isset($metaDataArr['user_release'])) {
            $fields[] = 'user_release';
            $values[] = $metaDataArr['user_release'];
        }

        if (isset($metaDataArr['id_certificate'])) {
            $fields[] = 'id_certificate';
            $values[] = $metaDataArr['id_certificate'];
        }

        $query = 'INSERT INTO '
            . $this->table_cert
            . ' ('
            . implode(', ', $fields)
            . ' )'
            . ' VALUES ('
            . implode(', ', $values)
            . ')';

        if (isset($metaDataArr['id_certificate'])) {
            $query .= ' ON DUPLICATE KEY UPDATE ';

            foreach ($fields as $key => $field) {
                $query .= $field . ' = ' . $values[$key] . ',';
            }

            $query = substr($query, 0, -1); //Removing last comma
        }

        $rs = sql_query($query);

        return $rs;
    }

    /**
     *  updating an existent association
     * (Table learning_certificate_meta_association).
     *
     * $metaDataAssocArr['id_certificate']
     * $metaDataAssocArr['idAssociation']
     * $metaDataAssocArr['title']
     * $metaDataAssocArr['description']
     *
     * @param mixed $metaDataArr
     */
    public function updateMetaDataAssoc($metaDataAssocArr)
    {
        if (empty($metaDataAssocArr)) {
            return false;
        } // You never know...

        $query = 'UPDATE  ' . $this->table_cert_meta_association
            . " SET description = '" . $metaDataAssocArr['description'] . "', title ='" . $metaDataAssocArr['title']
            . "' WHERE idAssociation = " . $metaDataAssocArr['idAssociation'] . ' AND'
            . ' idCertificate = ' . $metaDataAssocArr['idCertificate'];

        $rs = sql_query($query);

        return $rs;
    }

    public function saveCertAggregatedCert($assocArr)
    {
        $this->_id_cert = FormaLms\lib\Get::req('id_certificate', DOTY_INT);
        $this->_title = addslashes(FormaLms\lib\Get::req('title', DOTY_STRING));
        $this->_description = addslashes(FormaLms\lib\Get::req('description', DOTY_STRING));
        $this->_idAssoc = intval(FormaLms\lib\Get::req('id_assoc', DOTY_INT));
        $this->_type_assoc = FormaLms\lib\Get::req('type_assoc', DOTY_INT, -1);

        if ($this->_type_assoc == self::AGGREGATE_CERTIFICATE_TYPE_COURSE) {
            $table = $this->table_cert_meta_association_courses;
        } else {
            $table = $this->table_cert_meta_association_coursepath;
        }
        if ($this->_idAssoc == 0) {
            $sql1 = 'INSERT INTO ' . $this->table_cert_meta_association . ' (idCertificate, title, description) VALUES ('
                . $this->_id_cert . ',\'' . $this->_title . '\',\'' . $this->_description . '\')';
            if (sql_query($sql1)) {
                $sql2 = 'SELECT LAST_INSERT_ID() as idAssociation';
                $row = sql_fetch_assoc(sql_query($sql2));
                $this->_idAssoc = $row['idAssociation'];
                if ($this->_type_assoc == self::AGGREGATE_CERTIFICATE_TYPE_COURSE) {
                    $sql1 = 'INSERT INTO ' . $table . ' (idAssociation, idUser, idCourse, idCourseEdition) VALUES ';
                    // insert placeholder for no user selected case
                    $array_course = explode(',', FormaLms\lib\Get::req('selected_courses', DOTY_NUMLIST));
                    $r = [];
                    foreach ($array_course as $the_course) {
                        $r[] = '(' . $this->_idAssoc . ',0,' . $the_course . ', 0)';
                    }
                } else {
                    $sql1 = 'INSERT INTO ' . $table . ' (idAssociation, idUser, idCoursePath) VALUES ';
                    // insert placeholder for no user selected case
                    $array_path = explode(',', FormaLms\lib\Get::req('selected_idsCoursePath', DOTY_NUMLIST));
                    $r = [];
                    foreach ($array_path as $the_path) {
                        $r[] = '(' . $this->_idAssoc . ',0,' . $the_path . ')';
                    }
                }
                $sql1 .= implode(',', $r);
                if (!sql_query($sql1)) {
                    return false;
                }
            } else {
                return false;
            }
        } else { // update, deleting old association
            $sql0 = 'UPDATE ' . $this->table_cert_meta_association . ' SET '
                . " title = '" . $this->_title . "', "
                . " description ='" . $this->_description . "'"
                . ' WHERE idAssociation = ' . $this->_idAssoc;
            $sql1 = 'DELETE FROM ' . $table . ' WHERE idAssociation =' . $this->_idAssoc;
            if ($this->_type_assoc == self::AGGREGATE_CERTIFICATE_TYPE_COURSE) {
                $sql2 = 'INSERT INTO ' . $table . ' (idAssociation, idUser, idCourse, idCourseEdition) VALUES ';
                // insert placeholder for no user selected case
                $array_course = explode(',', FormaLms\lib\Get::req('selected_courses', DOTY_NUMLIST));
                $r = [];
                foreach ($array_course as $the_course) {
                    $r[] = '(' . $this->_idAssoc . ',0,' . $the_course . ', 0)';
                }
            } else {
                $sql2 = 'INSERT INTO ' . $table . ' (idAssociation, idUser, idCoursePath) VALUES ';
                // insert placeholder for no user selected case
                $array_path = explode(',', FormaLms\lib\Get::req('selected_idsCoursePath', DOTY_NUMLIST));
                $r = [];
                foreach ($array_path as $the_path) {
                    $r[] = '(' . $this->_idAssoc . ',0,' . $the_path . ')';
                }
            }

            $sql2 .= implode(',', $r);
            sql_query('START TRANSACTION');
            if (sql_query($sql0) && sql_query($sql1) && sql_query($sql2)) {
                sql_query('COMMIT');
            } else {
                sql_query('ROLLBACK');

                return false;
            }
        }

        if (count($assocArr) > 0) {
            if ($this->_type_assoc == self::AGGREGATE_CERTIFICATE_TYPE_COURSE) {
                return $this->saveCertRowCourse($assocArr);
            } else {
                return $this->saveCertRowPath($assocArr);
            }
        }

        return true;
    }

    private function saveCertRowPath($assocArr)
    {
        $table = $this->table_cert_meta_association_coursepath;
        $sql1 = 'INSERT INTO ' . $table
            . ' ( idAssociation, idUser, idCoursePath)'
            . ' VALUES ';

        foreach ($assocArr as $pathId => $idUsers) {
            foreach ($idUsers as $id => $assoc) {
                if ($assoc) {
                    $q[] = '(' . $this->_idAssoc . ',' . $id . ',' . $pathId . ')';
                    $user_paths[$id][] = $pathId;
                } else {
                    $q[] = '(' . $this->_idAssoc . ',' . $id . ',0)';
                }
            }
        }
        $sql1 .= implode(',', $q);
        sql_query('START TRANSACTION');
        if (sql_query($sql1)) {
            sql_query('COMMIT');
            $this->checkIstantCertification($user_paths, self::AGGREGATE_CERTIFICATE_TYPE_COURSE_PATH);

            return true;
        } else {
            sql_query('ROLLBACK');

            return false;
        }
    }

    /**
     * Inserting association.
     *
     * @param mixed $type_assoc
     * @param array $assocArr
     *
     * assocArr[]
     *
     * @return reouce_id
     */
    private function saveCertRowCourse($assocArr)
    {
        $table = $this->table_cert_meta_association_courses;
        $sql1 = 'INSERT INTO ' . $table
            . ' ( idAssociation, idUser, idCourse, idCourseEdition )'
            . ' VALUES ';
        $user_courses = [];
        foreach ($assocArr as $courseId => $idUsers) {
            foreach ($idUsers as $id => $assoc) {
                if ($assoc) {
                    $q[] = '(' . $this->_idAssoc . ',' . $id . ',' . $courseId . ',0)';
                    $user_courses[$id][] = $courseId;
                } else {
                    $q[] = '(' . $this->_idAssoc . ',' . $id . ',0,0)';
                }
            }
        }
        $sql1 .= implode(',', $q);
        sql_query('START TRANSACTION');
        if (sql_query($sql1)) {
            sql_query('COMMIT');
            $this->checkIstantCertification($user_courses, self::AGGREGATE_CERTIFICATE_TYPE_COURSE);

            return true;
        } else {
            sql_query('ROLLBACK');

            return false;
        }
    }

    // checking if the just assigned courses trigger new certificate
    private function checkIstantCertification($user_association, $type_association)
    {
        foreach ($user_association as $id_user => $association) {
            $p['id_user'] = $id_user;
            if ($type_association == self::AGGREGATE_CERTIFICATE_TYPE_COURSE) {
                $p['id_course'] = $association[0];
                $this->releaseNewAggrCertCourses($p);
            } else {
                $p['id_paths'] = $association[0];
                $this->releaseNewAggrCertPaths($p);
            }
        }
    }

    // ---------------- Deleting queries

    /**
     *  Delete certificate from table_cert.
     *
     * @param mixed $idCert
     */
    public function deleteCert($idCert)
    {
        $query = ' DELETE FROM ' . $this->table_cert
            . ' WHERE id_certificate = ' . $idCert;

        return sql_query($query);
    }

    public function deleteReleasedCert($id_user, $id_cert, $id_assoc)
    {
        $query = 'UPDATE ' . $this->table_assign_agg_cert
            . " SET on_date = '', cert_file = ''
                      WHERE idUser = " . intval($id_user)
            . ' AND idCertificate = ' . intval($id_cert)
            . ' AND idAssociation = ' . intval($id_assoc);

        return sql_query($query);
    }

    /**
     * @param $idsArr can be single int or array of int ids
     *
     * @return reouce_id
     */
    public function deleteAssociationsMetadata($idsArr)
    {
        $query = 'DELETE FROM ' . $this->table_cert_meta_association
            . ' WHERE idAssociation '
            . (is_array($idsArr) ? ' IN (' . implode(', ', $idsArr) . ') ' : ' = ' . $idsArr);

        return sql_query($query);
    }

    /**
     * Deleting associations from cert_table_meta_assoc_course or coursepath.
     *
     * @param array of integer | int $idsArr
     * @param $idLinksArr
     *
     * @return reouce_id
     */
    public function deleteAssociations($idsArr, $type_assoc = -1, $idLinksArr = [])
    {
        $rs = $this->deleteAssociationsMetadata($idsArr);

        if ($rs) {
            if ($type_assoc != -1) {
                $rs = $this->deleteAssociationLinks($idsArr, $type_assoc);
            } else {
            }
            foreach ($idsArr as $id_assoc) {
                $type_association = $this->getTypeAssoc($id_assoc);

                if ($type_association != -1) { // Exists at least one link assoc.
                    $rs = $this->deleteAssociationLinks($id_assoc, $type_association, $idLinksArr);
                }
            }
        }

        return $rs;
    }

    /**
     * @param       $id_association (mandatory)
     * @param       $type_assoc     (mandatory)
     * @param array $usersIdsArr    If i want to delete passing an array of user ids, then i need to pass this param
     * @param array $LinkIdsArr     Instead if i want to delete by the association (courses, coursepath...) ...
     *
     * @return result of the delete query
     */
    public function deleteAssociationLinks($id_association, $type_assoc, $userIdsArr = [], $linkIdsArr = [])
    {
        switch ($type_assoc) {
            case self::AGGREGATE_CERTIFICATE_TYPE_COURSE:
                $table = $this->table_cert_meta_association_courses;
                $field_link = 'idCourse';
                break;
            case self::AGGREGATE_CERTIFICATE_TYPE_COURSE_PATH:
                $table = $this->table_cert_meta_association_coursepath;
                $field_link = 'idCoursePath';
                break;
            default:
                return;
        }

        $query2 = 'DELETE FROM ' . $table
            . ' WHERE idAssociation = ' . $id_association
            . (!empty($userIdsArr) ? ' AND idUser IN (' . implode(', ', $userIdsArr) . ')' : '')
            . (!empty($linkIdsArr) ? ' AND ' . $field_link . ' IN (' . implode(', ', $linkIdsArr) . ')' : '');

        $rs = sql_query($query2);

        return $rs;
    }

    // ------------ Updating queries

    public function updateLayout($templateArr)
    {
        $query = 'UPDATE ' . $this->table_cert
            . ' SET ';
        foreach ($templateArr as $column_header => $value) {
            $query .= $column_header . ' = ' . $value . ',';
        }

        $query = substr($query, 0, -1); //Removing last comma

        $query .= ' WHERE id_certificate = ' . $templateArr['id_certificate'];

        $rs = sql_query($query);

        return $rs;
    }

    public function releaseNewCertificate($id_user, $id_certificate, $id_association)
    {
        $query = 'INSERT INTO ' . $this->table_assign_agg_cert
            . ' ( idUser, idCertificate, idAssociation ) '
            . ' VALUES '
            . ' ( '
            . intval($id_user) . ','
            . intval($id_certificate) . ', '
            . intval($id_association)
            . ') ';
        $rs = sql_query($query);

        return $rs;
    }

    public function isCertIssued($user, $cert, $association)
    {
        $q = 'SELECT COUNT(*) FROM ' . $this->table_assign_agg_cert
            . ' WHERE idUSer=' . $user
            . ' AND idCertificate=' . $cert
            . ' AND idAssociation=' . $association;
        $row = sql_fetch_row(sql_query($q));

        return $row[0] > 0;
    }

    /**
     * given an id_course and a username, returns all the association and certificates that
     * containing both user and course - used to check if an assign has been completed
     * input: userid, id course
     * output array[id_association] => courses associated (related to the user)
     * for a given association the associated courses can be different for different users.
     */
    public function getIdAssocForUserCourse($id_user, $id_course)
    {
        if ($id_user == null || $id_course == null) {
            return 0;
        }
        $id_associations_courses = [];

        $q = 'SELECT assoc_meta.idCertificate, assoc_course.idAssociation, assoc_course.idCourse  FROM '
            . $this->table_cert_meta_association_courses . ' as assoc_course,' . $this->table_cert_meta_association . ' as assoc_meta 
         WHERE assoc_course.idUser = ' . intval($id_user) . ' AND assoc_course.idCourse <> 0 AND assoc_course.idAssociation in 
        (SELECT idAssociation FROM ' . $this->table_cert_meta_association_courses . ' WHERE idUser =' . intval($id_user) . ' AND  idCourse = ' . intval($id_course) . ')
         AND assoc_course.idAssociation = assoc_meta.idAssociation';
        $rs = sql_query($q);
        while ($row = sql_fetch_assoc($rs)) {
            $id_associations_courses[$row['idCertificate']][$row['idAssociation']][] = $row['idCourse'];
        }

        return $id_associations_courses;
    }

    /**
     * given an id_path and a username, returns all the association and certificates that
     * containing both user and path - used to check if an assign has been completed
     * input: userid, id_path
     * output array[id_association] => path associated (related to the user)
     * for a given association the associated pathcourses can be different for different users.
     */
    public function getIdAssocForUserPath($id_user, $id_path)
    {
        if ($id_user == null || $id_path == null) {
            return 0;
        }
        $id_associations_paths = [];
        $id_path_str = (is_array($id_path) ? implode(',', $id_path) : $id_path);

        $q = 'SELECT assoc_meta.idCertificate, assoc_path.idAssociation, assoc_path.idCoursePath FROM '
            . $this->table_cert_meta_association . ' as assoc_meta, ' . $this->table_cert_meta_association_coursepath . ' as assoc_path
             WHERE assoc_path.idUser =' . intval($id_user) . ' AND assoc_path.idCoursePath <> 0 AND assoc_path.idAssociation in 
             (SELECT idAssociation FROM ' . $this->table_cert_meta_association_coursepath . ' WHERE idUser = ' . intval($id_user) . ' AND idCoursePath in (' . $id_path_str . '))
              AND assoc_path.idAssociation = assoc_meta.idAssociation';
        $rs = sql_query($q);
        while ($row = sql_fetch_assoc($rs)) {
            $id_associations_paths[$row['idCertificate']][$row['idAssociation']][] = $row['idCoursePath'];
        }

        return $id_associations_paths;
    }

    public function getIssuedCertificates($certificate)
    {
        $q = 'SELECT ' . $this->table_assign_agg_cert . '.idCertificate,'
            . $this->table_assign_agg_cert . ".idAssociation, (CASE WHEN cert_file = '' then false else true END) as released,
                idst, firstname, lastname, TRIM(LEADING '/' FROM userid ) as userid, title FROM  %adm_user," . $this->table_assign_agg_cert . ',' . $this->table_cert_meta_association
            . ' WHERE ' . $this->table_assign_agg_cert . '.idCertificate=' . intval($certificate)
            . ' AND idst = idUser'
            . ' AND ' . $this->table_assign_agg_cert . '.idAssociation = ' . $this->table_cert_meta_association . '.idAssociation';
        $rs = sql_query($q);
        while ($r = sql_fetch_assoc($rs)) {
            $row[] = $r;
        }

        return $row;
    }

    public function releaseNewAggrCertCourses($params)
    {
        require_once _lms_ . '/lib/lib.course.php';
        $man_courseuser = new Man_CourseUser(\FormaLms\db\DbConn::getInstance());
        $associated_aggr_cert_courses = $this->getIdAssocForUserCourse($params['id_user'], $params['id_course']);

        foreach ($associated_aggr_cert_courses as $idcert => $associations) {
            foreach ($associations as $id_association => $courses) {
                if ($man_courseuser->hasCompletedCourses($params['id_user'], $courses)) {
                    if (!$this->isCertIssued($params['id_user'], $idcert, $id_association)) {
                        $this->releaseNewCertificate($params['id_user'], $idcert, $id_association);
                    }
                }
            }
        }
    }

    public function releaseNewAggrCertPaths($params)
    {
        require_once _lms_ . '/lib/lib.coursepath.php';
        $man_pathuser = new CoursePath_Manager();

        $associated_aggr_cert_paths = $this->getIdAssocForUserPath($params['id_user'], $params['id_paths']);
        foreach ($associated_aggr_cert_paths as $idcert => $associations) {
            foreach ($associations as $id_association => $path) {
                if ($man_pathuser->isCoursePathCompleted($params['id_user'], $path)) {
                    if (!$this->isCertIssued($params['id_user'], $idcert, $id_association)) {
                        $this->releaseNewCertificate($params['id_user'], $idcert, $id_association);
                    }
                }
            }
        }
    }

    public function getAggrCertName($idcert)
    {
        $rs = sql_query('SELECT name from %lms_certificate WHERE id_certificate = ' . intval($idcert));
        if ($rs) {
            $r = sql_fetch_row($rs);

            return is_array($r) ? $r[0] : false;
        }

        return '';
    }


    public function getAssociationView($params)
    {
        
          // Loading necessary libraries
          require_once _base_ . '/lib/lib.userselector.php';
          require_once _lms_ . '/lib/lib.course.php';
          require_once _lms_ . '/lib/lib.course_managment.php';
          require_once _lms_ . '/lib/lib.coursepath.php';

  
          YuiLib::load();
          Util::get_js(FormaLms\lib\Get::rel_path('base') . '/lib/js_utils.js', true, true);
  
          $type_assoc = array_key_exists('type_assoc', $params) ? $params['type_assoc'] : -1;


          $acl_man = \FormaLms\lib\Forma::getAclManager();
          $aclManager = new FormaACLManager();
          $userSelectionArr = array_map('intval', $params['selection']);
          $userSelectionArr = $aclManager->getAllUsersFromIdst($userSelectionArr);
          $array_user = $aclManager->getArrUserST($userSelectionArr);
          $selected_course = array_key_exists('idsCourse', $params) ? explode(',', $params['idsCourse']) : [];
          $idsCP_array = array_key_exists('idsCoursePath', $params) ?  explode(',', $params['idsCoursePath']) : [];
          sort($idsCP_array);
          $coursePath_man = new CoursePath_Manager();
          $coursePathInfoArr = $coursePath_man->getCoursepathAllInfo($idsCP_array);
  
 
       
  
          $form = new Form();
          $form_name = 'new_assign_step_3';
  
          $tb = new Table(0, Lang::t('_META_CERTIFICATE_NEW_ASSIGN_CAPTION', 'certificate'), Lang::t('_META_CERTIFICATE_NEW_ASSIGN_SUMMARY'));
          $tb->setLink('index.php?r=alms/aggregatedcertificate/show');
          $tb->setTableId('tb_AssocLinks');
  
          //  Table header
          $type_h = ['', ''];
          $cont_h = [Lang::t('_FULLNAME'), Lang::t('_USERNAME')];
          $course_man = new Man_Course();
          foreach ($selected_course as $id_course) {
              $type_h[] = 'align_center';
              $course_info = Man_Course::getCourseInfo($id_course);
              $cont_h[] = $course_info['code'] . ' - ' . $course_info['name'];
              $cont_footer[] = '<a href="javascript:;" onclick="checkall_meta(\'' . $form_name . '\', \'' . $id_course . '\', true); return false;">'
                  . Lang::t('_SELECT_ALL')
                  . '</a><br/>'
                  . '<a href="javascript:;" onclick="checkall_meta(\'' . $form_name . '\', \'' . $id_course . '\', false); return false;">'
                  . Lang::t('_UNSELECT_ALL')
                  . '</a>';
          }

          foreach ($coursePathInfoArr as $coursePathInfo) {
            $type_h[] = 'align_center';
            $cont_h[] = $coursePathInfo[COURSEPATH_CODE] . ' - ' . $coursePathInfo[COURSEPATH_NAME];

            $cont_footer[] = '<a href="javascript:;" onclick="checkall_meta(\'' . $form_name . '\', \'' . $coursePathInfo[COURSEPATH_ID] . '\', true); return false;">'
                . Lang::t('_SELECT_ALL')
                . '</a><br/>'
                . '<a href="javascript:;" onclick="checkall_meta(\'' . $form_name . '\', \'' . $coursePathInfo[COURSEPATH_ID] . '\', false); return false;">'
                . Lang::t('_UNSELECT_ALL')
                . '</a>';
        }
          $type_h[] = 'image';
          $cont_h[] = Lang::t('_SELECT_ALL');
          $type_h[] = 'image';
          $cont_h[] = Lang::t('_UNSELECT_ALL');
  
          $tb->setColsStyle($type_h);
          $tb->addHead($cont_h);
  
          foreach ($array_user as $username => $id_user) {
              $cont = [];
              $user_info = $acl_man->getUser($id_user, false);
              $cont[] = $user_info[ACL_INFO_LASTNAME] . ' ' . $user_info[ACL_INFO_FIRSTNAME];
              $cont[] = $acl_man->relativeId($user_info[ACL_INFO_USERID]);
              $check_assoc = $this->getAssociationLink($params['id_association'], $type_assoc, (int) $id_user);
              foreach ($selected_course as $id_course) {
                  $checked = in_array($id_course, $check_assoc);
                  $cont[] = Form::getCheckbox('', '_' . $id_user . '_' . $id_course . '_', '_' . $id_user . '_' . $id_course . '_', 1, $checked);
              }
              $cont[] = '<a href="javascript:;" onclick="checkall_fromback_meta(\'' . $form_name . '\', \'' . $id_user . '\', true); return false;">'
                  . Lang::t('_SELECT_ALL')
                  . '</a>';
              $cont[] = '<a href="javascript:;" onclick="checkall_fromback_meta(\'' . $form_name . '\', \'' . $id_user . '\', false); return false;">'
                  . Lang::t('_UNSELECT_ALL')
                  . '</a>';
              $tb->addBody($cont);
          }
  
          $cont = [];
  
          $cont[] = '';
          $cont[] = '';
  
          foreach ($cont_footer as $footer) {
              $cont[] = $footer;
          }
  
          $cont[] = '';
          $cont[] = '';
  
          $tb->addBody($cont);
  
          $viewParams = [
              'form' => $form,
              'id_certificate' => $params['id_certificate'],
              'id_association' => $params['id_association'],
              'type_assoc' => $type_assoc,
              'title' => $params['title'],
              'description' => $params['description'],
              'selected_courses' => array_key_exists('idsCourse', $params) ? $params['idsCourse'] : '',
              'selected_idsCoursePath' => array_key_exists('idsCoursePath', $params) ? $params['idsCoursePath'] : null,
              'selected_users' => implode(',', $userSelectionArr),
              'controllerName' => 'aggregatedcertificate',
              'tb' => $tb,
              'opsArr' => $this->op,
              'cert_name' => $this->getAggrCertName($params['id_certificate']),
          ];

          return $viewParams;
    }



    public function getOps() :array{
        return $this->op;
    }


    public function getAccessList($resourceId) : array {

        
        $arrayInstanceId = explode('_', $resourceId);
        $idAssociation = $arrayInstanceId[0];
        $typeAssoc = $arrayInstanceId[1];
        $selection = $this->getAllUsersFromIdAssoc($idAssociation, $typeAssoc);

        return $selection;
    }

    public function setAccessList($resourceId, array $selection) : bool {
        
        //handeld by session
        return true;
     
    }
}
