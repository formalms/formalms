<?php defined("IN_FORMA") or die("Direct access is forbidden");
  
class AggregatedCertificate {

    protected $db;

    public function AggregatedCertificate(){

        $this->db = DbConn::getInstance();
        $this->table_cert = '_certificate';
        $this->table_cert_tags = '_certificate_tags';

        $this->table_cert_meta_association = '_aggregated_certificate_metadata_association';
        $this->table_cert_meta_association_courses = '_aggregated_certificate_association_course';
        $this->table_cert_meta_association_coursepath = '_aggregated_certificate_association_coursepath';
        $this->table_assign_agg_cert = '_aggregated_certificate_assign';
    }
    
    
    /**
    * Get all Aggregate certificates (or meta cert)
    * 
    * @param mixed $ini  limit rows
    * @param mixed $count if the query has to return only the count of the rows
    *
    * $aggCertArr['id_certificate']         Defines the id of the cert.
    * $aggCertArr['code']                   Defines the code of the cert.
    * $aggCertArr['name']                   Defines the name of the cert.
    * $aggCertArr['description']            Defines the description of the cert.
    *  
    * @return array $aggCertArr
    *           
    */
    function getAllAggregatedCerts($ini = 0, $count = false) {
        //search query of certificates
        $query_certificate = "SELECT ";
                            if ($count) {

                                $query_certificate .= "CAST( COUNT(*) AS UNSIGNED) AS count ";

                           } else {

                                $query_certificate .= "id_certificate, "
                                    . "code, "
                                    . "name, "
                                    . "description ";

                            }

        $query_certificate .= "FROM ".$GLOBALS['prefix_lms']. $this->table_cert
                            . " WHERE meta = 1";

      /*  if (isset($_POST['filter'])) { // Generalize with a filter variable
            if ($_POST['filter_text'] !== '')
                $query_certificate .=    " AND ( name LIKE '%".$_POST['filter_text']."%'"
                                        ." OR code LIKE '%".$_POST['filter_text']."%' )";
        }  */
        
        if(!$count)
            $query_certificate .= " ORDER BY id_certificate"
                . " LIMIT $ini,".Get::sett('visuItem');
        
        $rs = sql_query($query_certificate);
        $k = 0;
        while($rows = sql_fetch_assoc($rs)) {
            $aggCertArr[$k]['count'] = (int) $rows['count'];
            $aggCertArr[$k]['id_certificate'] = (int) $rows['id_certificate'];
            $aggCertArr[$k]['code'] = $rows['code'];
            $aggCertArr[$k]['name'] = $rows['name'];
            $aggCertArr[$k]['description'] = $rows['description'];
            $k++;
        }
                
        return $aggCertArr;
    }
    
    
    
    /**
        * Returns all metadata of aggr. cert
        *    
        * @param mixed $id_cert Certificate id (can be found in (prefix_lms . $this->table_cert) table
        * 
        * 
        * $arr_cert['code']             Defines the code of the cert.
        * $arr_cert['name']             Defines the name of the cert. 
        * $arr_cert['base_language']    Defines the language of the cert. 
        * $arr_cert['description']      Defines the description of the cert.
        * $arr_cert['user_release']     Defines if the cert. will be released to the user
        *  
        * @return array $arr_cert 
        * 
        */
    function getMetadata($id_cert){
            
            $query =      "SELECT "
                        . "code, "
                        . "name, "
                        . "base_language, "
                        . "description, "
                        . "user_release "
                        . "FROM ".$GLOBALS['prefix_lms'].$this->table_cert
                        . " WHERE id_certificate = '".$id_cert."'";
        
            $rs = sql_query($query);
            
            while($rows = sql_fetch_assoc($rs)) {
                $arr_cert = $rows;
            }
            return $arr_cert;
        }  
      
    
    
    
    /**
    * Returns all the metadata on associations related to a cert.
    * If i'm passing the id of the cert., i will get all associations metadata associated on the cert.
    * Instead, if i'm passing the id of the association, i will get the only object with the metadata 
    * 
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
    function getAssociationsMetadata($id_cert = 0, $id_association = 0) {
        
        $query = "SELECT idAssociation, title, description"
                ." FROM ".$GLOBALS['prefix_lms'].$this->table_cert_meta_association
                . ($id_cert != 0 ? " WHERE idCertificate = ".$id_cert : '')
                . ($id_association != 0 ? " WHERE idAssociation = ".$id_association : '');

        $rs = sql_query($query);
        
        $k = 0;
        while($rows = sql_fetch_assoc($rs)) {
            $associationsArr[$k]['idAssociation'] = (int) $rows['idAssociation'];
            $associationsArr[$k]['title'] = $rows['title'];
            $associationsArr[$k]['description'] = $rows['description'];
            $k++;
        }
       
        return $associationsArr; 
    }



    /**
     * Return all technical data about certificate
     *
     * @param mixed $id_cert
     */
    function getLayoutMetacert($id_cert) {

        $query = "SELECT "
            ."cert_structure, "
            ."orientation, "
            ."bgimage "
            ."FROM ".$GLOBALS['prefix_lms'].$this->table_cert
            ." WHERE id_certificate = ".$id_cert."";

        return sql_fetch_assoc(sql_query($query));

    }


    /**
     * Returns an array of id/s associations (object)
     *
     * @param int $idCert
     */
    function getIdAssociations($idCert) {

        $query = "SELECT idAssociation FROM ".$GLOBALS['prefix_lms'].$this->table_cert_meta_association
            ." WHERE idCertificate = ".$idCert;

        $rs = sql_query($query);


        while($rows = sql_fetch_assoc($rs)) {

            $idsArr[] = (int) $rows['idAssociation'];

        }

        return $idsArr;

    }


    /**
     * Returning all associations between idAssociation or,
     * if i pass an array of user, the ids of the link whom the users belong
     *
     * @param array | int $id_association
     * @param mixed $type_assoc
     * @param mixed $userIdsArr optional (for filtering query with users)
     *
     * @return array $usersArr an array of 0 or more rows with the link ids
     */

    function getAssociationLink($id_association, $type_assoc, $userIdsArr = [] ){

        switch($type_assoc) {
            case COURSE:
                $table = $this->table_cert_meta_association_courses;
                $field_link = 'idCourse';
                break;
            case COURSE_PATH:
                $table = $this->table_cert_meta_association_coursepath;
                $field_link = 'idCoursePath';
                break;
            default:
                return;
        }

        $q =      "SELECT "
                . $field_link
                . " FROM ". $GLOBALS['prefix_lms'] . $table
                . " WHERE idAssociation ". ( (is_array($id_association) && count($id_association) > 1) ? " IN (" . implode(", ", $id_association) . ")" : " = " . $id_association)
                . (!empty($userIdsArr) ? " AND idUser ". ( is_array($userIdsArr) ? " IN (" . implode( ", ", $userIdsArr) . ") " : " = " . $userIdsArr) : "");

        $rs = sql_query($q);

        $assocArr = array();


        while($row = sql_fetch_array($rs))
            $assocArr[] = (int) $row[$field_link];


        return $assocArr;
    }

    /**
     * @param array | int $id_assoc
     * @param $type_assoc
     *
     * @return array|void
     */
    function getAllUsersFromIdAssoc($id_assoc, $type_assoc) {

        switch($type_assoc) {
            case COURSE:
                $table = $this->table_cert_meta_association_courses;
                break;
            case COURSE_PATH:
                $table = $this->table_cert_meta_association_coursepath;
                break;
            default:
                return;
        }

        $q =    "SELECT "
            .   "DISTINCT idUser" // If i'm passing array of id user and array of links, i want to check if there are any assoc
            .   " FROM ". $GLOBALS['prefix_lms'] . $table
            .   " WHERE idAssociation ". ( is_array($id_assoc) ? " IN (" . implode(", ", $id_assoc) . ")" : " = " . $id_assoc);


        $rs = sql_query($q);

        while($rows = sql_fetch_assoc($rs)) {
            $id_users[] = (int) $rows['idUser'];
        }
        return $id_users;

    }

    function getAllLinksFromIdAssoc($id_assoc, $type_assoc) {

        switch($type_assoc) {
            case COURSE:
                $table = $this->table_cert_meta_association_courses;
                $field_link = 'idCourse';
                break;
            case COURSE_PATH:
                $table = $this->table_cert_meta_association_coursepath;
                $field_link = 'idCoursePath';
                break;
            default:
                return;
        }


        $q =    "SELECT "
            .   "DISTINCT {$field_link}" // If i'm passing array of id user and array of links, i want to check if there are any assoc
            .   " FROM ". $GLOBALS['prefix_lms'] . $table
            .   " WHERE idAssociation = ".$id_assoc;


        $rs = sql_query($q);

        while($rows = sql_fetch_assoc($rs)) {
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
    public function getIdsCourse($id_association){

        $q = "SELECT idCourse FROM "
            . $GLOBALS['prefix_lms'] . $this->table_cert_meta_association_courses
            . " WHERE idAssociation = " . $id_association
            . " GROUP BY idCourse";

        $rs = sql_query($q);

        $idsCourseArr = array();
        while($row = sql_fetch_array($rs)){

            $idsCourseArr[] = (int) $row['idCourse'];

        }

        return $idsCourseArr;
    }


    function getUserAndCourseFromIdAssoc($idAssoc, $type_assoc){

        switch($type_assoc) {
            case COURSE:
                $table = $this->table_cert_meta_association_courses;
                $field = 'idCourse';
                break;
            case COURSE_PATH:
                $table = $this->table_cert_meta_association_coursepath;
                $field = 'idCoursePath';
                break;
            default:
                return;
        }

        $query =    "SELECT idUser, {$field}"
            ." FROM ".$GLOBALS['prefix_lms'].$table
            ." WHERE idAssociation = '".$idAssoc."'";

        $rs = sql_query($query);

        while($rows = sql_fetch_assoc($rs)) {
            $status[$rows['idUser']][$rows[$field]] = 1;
        }
        return $status;
    }

    function getCountCoursesCompleted($id_course, $id_user){

        $query =    "SELECT COUNT(*)"
            ." FROM ".$GLOBALS['prefix_lms']."_courseuser"
            ." WHERE idCourse = '".$id_course."'"
            ." AND idUser = '".$id_user."'"
            ." AND status = '"._CUS_END."'";

        $rs = sql_query($query);

        while($rows = sql_fetch_row($rs)) {

            $count = $rows[0];

        }

        return $count;
    }

    function hasUserAggCertsReleased($id_user, $id_cert){


            $q = "SELECT * "
                ." FROM ". $GLOBALS['prefix_lms']. $this->table_assign_agg_cert
                ." WHERE idUser = ".$id_user
                ." AND idCertificate = ".$id_cert;

           return sql_num_rows(sql_query($q));


        }

    /**
     *
     *
     */
    function getIdsCoursePath($id_association){

        $q = "SELECT idCoursePath FROM "
            . $GLOBALS['prefix_lms'] . $this->table_cert_meta_association_coursepath
            . " WHERE idAssociation = " . $id_association
            . " GROUP BY idCoursePath";

        $rs = sql_query($q);

        $idsCoursePathArr = array();
        while($row = sql_fetch_array($rs)){

            $idsCoursePathArr[] = (int) $row['idCoursePath'];

        }

        return $idsCoursePathArr;
    }


    function getCoursePathList() {

        $q = " SELECT id_path, path_name, path_descr
             FROM ".$GLOBALS['prefix_lms']."_coursepath ";
        $rs = sql_query($q);

        $coursepathArr['data'] = [];
        $i = 0;
        while($rows = sql_fetch_assoc($rs)) {

            $coursepathArr['data'][$i]["idCoursePath"]    = $rows["id_path"];
            $coursepathArr['data'][$i]["nameCoursePath"]  = $rows["path_name"];
            $coursepathArr['data'][$i]["descriptionCoursePath"]  = $rows["path_descr"];
            $i += 1;
        }
        $coursepathArr["recordsTotal"] = count($coursepathArr['data']);
        $coursepathArr["recordsFiltered"] = count($coursepathArr['data']);
        $coursepathArr["draw"] = 1;


        return $coursepathArr;
    }


    // TODO: Generalize in lib.course.php
    function getCoursesArrFromId($idsArr) {

        $idsArr = json_encode($idsArr);

        $q = "SELECT  cour.code, cour.name, cat.path "
            . "FROM " . $GLOBALS["prefix_lms"] . "_course AS cour "
            . "JOIN " . $GLOBALS["prefix_lms"] . "_category AS cat "
            . "WHERE cour.idCourse IN (" . str_replace(array( "[", "]" ) , "" , $idsArr) . ") "
            . "AND cour.idCategory = cat.idCategory ";

        $rs = sql_query($q);
        $i = 0;
        while($rows = sql_fetch_assoc($rs)) {

            $coursesList[$i]["codeCourse"]  = $rows["code"];
            $coursesList[$i]["nameCourse"]  = $rows["name"];
            $coursesList[$i]["pathCourse"]  = substr($rows["path"], 6); // deleting '/root/' string part

            $i += 1;
        }

        return $coursesList;

    }


    function getCourseListFromIdCat($idCategoryArr) {

        // Need to know if i'm requesting courses from the root category (idParent 0)
        $req_root = ($idCategoryArr == "0");

        $q = "SELECT cour.name, cour.idCourse, cour.code, cour.description, cour.status "
            . (($req_root) ? '' : ", cat.path ")
            . "FROM " . $GLOBALS["prefix_lms"] . "_course AS cour "
            . (($req_root) ? '' : "JOIN " . $GLOBALS["prefix_lms"] . "_category AS cat ")
            . "WHERE cour.idCategory IN (" . str_replace(array( "[", "]" ) , "" , $idCategoryArr) . ")"
            . (($req_root) ? '' : " AND cour.idCategory = cat.idCategory ");


        $rs = sql_query($q);

        $coursesList = array();

        $i = 0;
        while($rows = sql_fetch_assoc($rs)) {

            $coursesList[$i]["idCourse"]    = $rows["idCourse"];
            $coursesList[$i]["codeCourse"]  = $rows["code"];
            $coursesList[$i]["nameCourse"]  = $rows["name"];
            $coursesList[$i]["pathCourse"]  = ($req_root ? Lang::t('_ALT_ROOT') : substr($rows["path"], 6)); // deleting '/root/' string part

            switch ($rows["status"]) {  // SOSTITUIRE ASSOLUTAMENTE CON qualche
                // riferimento al db o con un unico entry point
                case 0:
                    $coursesList[$i]["stateCourse"] = Lang::t("_CST_PREPARATION","course");
                    break;
                case 1:
                    $coursesList[$i]["stateCourse"] = Lang::t("_CST_AVAILABLE","course");
                    break;
                case 2:
                    $coursesList[$i]["stateCourse"] = Lang::t("_CST_CONFIRMED","course");
                    break;
                case 3:
                    $coursesList[$i]["stateCourse"] = Lang::t("_CST_CONCLUDED","course");
                    break;
                case 4:
                    $coursesList[$i]["stateCourse"] = Lang::t("_CST_CANCELLED","course");
                    break;
            };
            $i += 1;
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
    function getTypeAssoc($id_assoc){

        $assocTypesArr = array(
            COURSE => $this->table_cert_meta_association_courses,
            COURSE_PATH =>  $this->table_cert_meta_association_coursepath
        );

        $type_assoc = -1; // Assoc. not found.

        foreach($assocTypesArr as $key => $table) {

            $q = "SELECT * 
                FROM ". $GLOBALS['prefix_lms']. $table ."
                WHERE idAssociation = ".$id_assoc;

            if(sql_num_rows(sql_query($q)))
                $type_assoc = $key;

        }

        return $type_assoc;

    }


    function getCertificateTags(){

        $query = "SELECT file_name, class_name FROM ".$GLOBALS['prefix_lms'].$this->table_cert_tags;

        $rs = sql_query($query);

        while($rows = sql_fetch_assoc($rs)) {
            $certificate_tags[] = $rows;
        }
        return $certificate_tags;
    }



    function getLastInsertedIdCertificate() {

        return sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()FROM ".$GLOBALS['prefix_lms'].$this->table_cert ))[0];

    }

    function getLastInsertedAssociationId() {

        return sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()FROM ".$GLOBALS['prefix_lms'].$this->table_cert_meta_association ))[0];

    }


    
    // ------------------------ Inserting queries ------------------------------
    
    
    /**
    * Inserting a new aggregate certificate
    *  
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
    function insertMetaDataCert($metaDataArr) {
    
        $fields = array();
        $values = array();
        
        if(empty($metaDataArr)) return false; // You never know...
                   
        if(isset($metaDataArr['code'])){
           
           $fields[] = 'code'; 
           $values[] =  "'" . $metaDataArr['code'] . "'" ;

        }
        
        if(isset($metaDataArr['name'])){
        
            $fields[] = 'name';             
            $values[] =  "'" . $metaDataArr['name'] . "'" ;

        }
        
        if(isset($metaDataArr['base_language'])){
            
            $fields[] = 'base_language';
            $values[] =  "'" . $metaDataArr['base_language'] .  "'";

        }
                         
        
        if(isset($metaDataArr['descr'])){
            
            $fields[] = 'description';
            $values[] = "'" . $metaDataArr['descr'] . "'";
            
        }
                         
        
        if(isset($metaDataArr['meta'])){
            
            $fields[] = 'meta';            
            $values[] = $metaDataArr['meta'];             
            
        }
            
                     
        if(isset($metaDataArr['user_release'])){
            
            $fields[] = 'user_release'; 
            $values[] = $metaDataArr['user_release'];             

        }
          
        if(isset($metaDataArr['id_certificate'])){
           
           $fields[] = 'id_certificate'; 
           $values[] = $metaDataArr['id_certificate'];
           
            
        }

        
       $query = "INSERT INTO "
                .$GLOBALS['prefix_lms'] . $this->table_cert 
                ." ("
                .implode(", ", $fields)
                ." )"
                ." VALUES ("
                .implode(", ", $values)
                . ")";
       
       if(isset($metaDataArr['id_certificate'])){
           $query .= " ON DUPLICATE KEY UPDATE ";
          
           foreach($fields as $key => $field){
                $query .= $field . " = " . $values[$key] . ',';
           
           }

           $query = substr($query, 0, -1); //Removing last comma
     
       }

       $rs = sql_query($query);
       return $rs;

    }
     
    /**
    * Inserting / updating a new / existent association
    * (Table learning_certificate_meta_association)
    * 
    * $metaDataAssocArr['id_certificate']
    * $metaDataAssocArr['idAssociation']
    * $metaDataAssocArr['code']
    * $metaDataAssocArr['name']
    * $metaDataAssocArr['base_language']
    * $metaDataAssocArr['description']
    * $metaDataAssocArr['meta']
    * $metaDataAssocArr['user_release']
    * 
    * @param mixed $metaDataArr
    */
    function insertMetaDataAssoc($metaDataAssocArr) {
        
        $fields = array();
        $values = array();
        
        if(empty($metaDataAssocArr)) return false; // You never know...
        
        foreach($metaDataAssocArr as $columnHeader => $value){
        
            $fields[] = $columnHeader;
            $values[] = $value;
            
        }

        $query = "INSERT INTO ".$GLOBALS['prefix_lms'].$this->table_cert_meta_association 
                ." ("
                .implode(", ", $fields)
                ." )"
                ." VALUES ("
                .implode(", ", $values)
                . ")";
        
        
        if(isset($metaDataAssocArr['idAssociation'])) { // Updating already existent assoc.
             
            $query .= " ON DUPLICATE KEY UPDATE ";
                
            foreach($metaDataAssocArr as $columnHeader => $value) {
            
                    if ( $columnHeader == "title" || $columnHeader == "description" ) {
                     
                        $query .= $columnHeader . " = " . $value . "," ;
                        
                    }   
            }

            $query = substr($query, 0, -1); //Removing last comma
        
        }
        
        $rs = sql_query($query);
        return $rs;

    }  
      

    function insertAssociationLink($type_assoc, $assocArr){

        switch($type_assoc) {
            case COURSE:
                $table = $this->table_cert_meta_association_courses;
                $field_link = "idCourse";
                break;
            case COURSE_PATH:
                $table = $this->table_cert_meta_association_coursepath;
                $field_link = "idCoursePath";
                break;
            default:
                return;
        }

        $query = "INSERT INTO " . $GLOBALS["prefix_lms"].$table
                . " ( idAssociation, idUser, {$field_link} )"
                . " VALUES ";


        foreach ($assocArr as $associationId => $user) {
            foreach ($user as $idUser => $field) {
                foreach ($field as $fieldId => $fieldIdd) {

                    $query .= "({$associationId}, {$idUser}, {$fieldIdd}),";
                }
            }
        }

        $query = substr($query, 0, -1); //Removing last comma


        $rs = sql_query($query);
        return $rs;
    }


    // ---------------- Deleting queries 
    
    
    
    /**
    *  Delete certificate from table_cert
    * 
    * @param mixed $idCert
    */
    function deleteCert($idCert) {
        
        $query = " DELETE FROM ".$GLOBALS['prefix_lms'].$this->table_cert
                 ." WHERE id_certificate = ".$idCert;

        return sql_query($query);
        
    }

    /**
     * @param $idsArr can be single int or array of int ids
     *
     * @return reouce_id
     */
    function deleteAssociationsMetadata($idsArr){

        $query = "DELETE FROM ".$GLOBALS['prefix_lms'].$this->table_cert_meta_association
            ." WHERE idAssociation "
            . ( is_array($idsArr) ? " IN (" . implode( ", ", $idsArr) . ") " : " = " . $idsArr);


        return sql_query($query);

    }

    /**
     * Deleting associations from cert_table_meta_assoc_course or coursepath
     *
     * @param array of integer | int $idsArr
     * @param $idLinksArr
     *
     * @return reouce_id
     */
    function deleteAssociations($idsArr, $idLinksArr = []) {

       $rs = $this->deleteAssociationsMetadata($idsArr);
        
       if($rs) {

           foreach ($idsArr as $id_assoc) {

               $type_association = $this->getTypeAssoc($id_assoc);

                if($type_association != -1) // Exists at least one link assoc.
                    $rs = $this->deleteAssociationLinks($id_assoc, $type_association, $idLinksArr);

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
     * @return      result of the delete query
     */
    function deleteAssociationLinks($id_association, $type_assoc, $userIdsArr = [],  $linkIdsArr = []) {

        switch($type_assoc) {
            case COURSE:
                $table = $this->table_cert_meta_association_courses;
                $field_link = "idCourse";
                break;
            case COURSE_PATH:
                $table = $this->table_cert_meta_association_coursepath;
                $field_link = "idCoursePath";
                break;
            default:
                return;
        }

        $query2 =   "DELETE FROM ".$GLOBALS['prefix_lms'].$table
                    . " WHERE idAssociation = " . $id_association
                    . ( !empty($userIdsArr) ? " AND idUser IN (" . implode(", ", $userIdsArr) . ")" : '')
                    . ( !empty($linkIdsArr) ? " AND " .$field_link." IN (" . implode(", ", $linkIdsArr) . ")" : '');


        $rs = sql_query($query2);
        return $rs;

    }

  
    
    // ------------ Updating queries
    
    function updateLayout($templateArr) {
        

        $query =    "UPDATE ".$GLOBALS['prefix_lms'].$this->table_cert
                    ." SET ";
                    foreach($templateArr as $column_header => $value){

                            $query .= $column_header . " = " . $value .",";

                    }

        $query = substr($query, 0, -1); //Removing last comma


        $query .=  " WHERE id_certificate = " . $templateArr['id_certificate'];            
       

        $rs = sql_query($query);
        return $rs;

    }

   
  


}

