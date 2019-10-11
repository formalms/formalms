 <?php
 
 /**
    * Create operations:
    *   
    *   - Preview certificate
    *   - Release certificate
    *   - Delete released certificate
    * 
    * 
    * 
    */
    
    function create() {
        
        checkPerm('mod');
        require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
        require_once(_base_.'/lib/lib.table.php');
        require_once(_base_.'/lib/lib.form.php');
 
        $acl_man =& Docebo::user()->getAclManager(); //necessary to obtain users data
        $first = true;
        $tot_element = 0;
 
        $id_cert = Get::req('id_certificate', DOTY_INT, 0);
        
        //Creating table with the users data and cert. release, preview or deleting operations
        $tb = new Table(Get::sett('visuItem'), Lang::t('_META_CERTIFICATE_CREATE_CAPTION','certificate'), Lang::t('_META_CERTIFICATE_CREATE_CAPTION','certificate'));
        $tb->initNavBar('ini', 'button');
        $ini = $tb->getSelectedElement();
 
        /* Steps:
        
            - Obtain all the associations from the cert. (table lms_certificate_meta)
            - For each association, get all the users (both from lms_certificate_meta_course and from meta_coursepath)
            
            Case type: courses
            - If the user has completed all the courses belonging to the association, then the cert can be released
            - For each user, get all the courses belonging to, and check if it's completed.
                      
            Case type: coursepath
            The cert will be released if the user belonging to the association has completed all the courses belonging to the coursepath
            
            - Get the course path ids belonging to the association
            - For each course path, get all the courses belonging to it (table lms_coursepath_courses)
            - For each course belonging to the course path, check if the user has completed it.
            
 
        */
 
         // Getting an array of all associations belonging to the cert.
        $idsMetaCertArr = $this->model->getIdsMetaCertificate($id_cert);
        
        $usersCert = array(); // all users to whom the cert can be released 
        
        
                
        foreach($idsMetaCertArr as $id_meta) { // For each assoc.
         
            $userCourses = array();
            
            // what type is the association? 
            if ( !empty($usersIdArr = $this->model->getUsersBelongsMeta($id_meta, COURSE) ) ){ // Type assoc. : course
                
                // Getting all the courses from the user in the assoc.
                foreach($usersIdArr as $idUser) {
            
                    $coursesArr = $this->model->getCoursesInAssociationFromUser($id_meta, $idUser, COURSE);   
   
                    $man_courseuser = new Man_CourseUser(DbConn::getInstance());
                    $result = $man_courseuser->hasCompletedCourses($idUser,$coursesArr);
                }       
            
            }      
            
        }
 
    }