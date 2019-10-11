<?php
  
class AggregateCertificate {

    protected $db;

    public function AggregateCertificate(){

        $this->db = DbConn::getInstance();
        $this->table_cert = '_certificate';
        $this->table_cert_meta_association = '_certificate_meta_association';
        $this->table_cert_tags = '_certificate_tags';
        $this->table_cert_meta_association_courses = '_certificate_meta_association_course';
   
    }
    
    
    /**
    * Get all Aggregate certificates (or meta cert)
    * 
    * @param mixed $ini  limit rows
    * 
    * $aggCertArr['id_certificate']         Defines the id of the cert.
    * $aggCertArr['code']                   Defines the code of the cert.
    * $aggCertArr['name']                   Defines the name of the cert.
    * $aggCertArr['description']            Defines the description of the cert.
    *  
    * @return array $aggCertArr
    *           
    */
    function getAllAggregateCerts($ini = 0) {
        //search query of certificates
        $query_certificate = "SELECT "
                            . "CAST( COUNT(*) AS UNSIGNED) AS count, "
                            . "id_certificate, "
                            . "code, "
                            . "name, "
                            . "description "
                            . "FROM ".$GLOBALS['prefix_lms']. $this->table_cert
                            . " WHERE meta = 1";

      /*  if (isset($_POST['filter'])) { // Generalize with a filter variable
            if ($_POST['filter_text'] !== '')
                $query_certificate .=    " AND ( name LIKE '%".$_POST['filter_text']."%'"
                                        ." OR code LIKE '%".$_POST['filter_text']."%' )";
        }  */
        
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
    * If i'm passing the id of the cert., i will get all associations object associated on the cert.
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
    
    
    // ------ Inserting queries
    
    
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
            
           substr($query, 0, -1); //Removing last comma
     
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
                     
                        $query .= $column_header . " = " . $value . "," ;
                        
                    }   
            }
 
            substr($query, 0, -1); //Removing last comma
        
        }
        
        $rs = sql_query($query);
        return $rs;

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
    * @param mixed $idCert
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
    * Deleting associations from cert_table_meta_assoc_course or coursepath
    *   
    * @param mixed $idsArr 
    */
    function deleteAssociations($idsArr) {
       
       $idsArr .= implode(", " , $idsArr);

        
       $query = "DELETE FROM ".$GLOBALS['prefix_lms'].$this->table_cert_meta_association
                ." WHERE idAssociation IN (".$idsArr.")";
        
       $rs = sql_query($query);
        
       if($rs) {
       
            $query2 = "DELETE FROM ".$GLOBALS['prefix_lms'].$this->table_cert_meta_association_course
                       ."WHERE idMetaCertificate IN (".$idsArr.")";
            $rs = sql_query($query2);
            
        }
       

        $rs = sql_query($query);
        return $rs;

    }  
    
  
    
    // ------------ Updating queries
    
    function updateLayout($templateArr) {
        
        $lastElement = end($templateArr);
        
        $query =    "UPDATE ".$GLOBALS['prefix_lms'].$this->table_cert
                    ." SET ";
                    foreach($templateArr as $column_header => $value){
                       
                        if($column_header != "id_certificate"){
                            
                            $query .= $column_header . " = " . $value; 
                            if( $value != $lastElement ) $query .= ', ';   
                        
                        }
                      
                    }
        
        $query .=  " WHERE id_certificate = " . $templateArr['id_certificate'];            
       

        $rs = sql_query($query);
        return $rs;

    }

   
  


}

