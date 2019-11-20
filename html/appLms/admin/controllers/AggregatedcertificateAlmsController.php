<?php defined("IN_FORMA") or die("Direct access is forbidden");

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

// Defining Constants necessaries in creation of a new assoc.
           

          
           
/**
 * Class MetacertificateAlmsController
 */
Class AggregatedcertificateAlmsController extends AlmsController
{

    /*
    protected $acl_man;
    protected $model;

    protected $data;

    protected $permissions;

    protected $base_link_course;
    protected $base_link_classroom;
    protected $base_link_edition;
    protected $base_link_subscription;
    protected $base_link_competence;
    */

    
    
    protected $json;
    protected $model;
    protected $controller_name;

    protected $aggCertLib;
    /** 
    *   In this array there are all the operations called in the module.
    *
    *   "key" => "link"
    */
    protected $op = array(
    
        'home' => 'show',
        
        'metadata' => 'metadata',
            'saveMetaData' => 'saveMetaData',
 
        'layout' => 'layout',
            'saveLayout' => 'saveLayout',
        
        'preview' => 'preview',
        
        'assignmentManagement' => 'assignmentManagement',
        
            'preview_cert' => 'preview_cert',
            
            'release_cert' => 'release_cert',
            
            'del_released' => 'delReleased',
 
        'associationsManagement' => 'associationsManagement',
        
            'metaDataAssoc' => 'metaDataAssoc',
                
                'del_association' => 'delAssociations',
                'saveMetadataAssoc' => 'saveMetadataAssoc',
                
                'associationusers' => 'associationUsers',
                'associationCourses' => 'associationCourses',
                
                
                'associationUsersCourses' => 'associationUsersCourses',
                
                'saveAssignment' => 'saveAssignment',  
                'saveAssignmentUsers' => 'saveAssignmentUsers',
                
            'view_details' => 'viewdetails',
          
        'delmetacert' => 'delcertificate',
                   
    );

    function init() {
        parent::init();

        /*

         $this->acl_man =& Docebo::user()->getAclManager();
         $this->model = new CourseAlms();

         $this->base_link_course = 'alms/course';
         $this->base_link_classroom = 'alms/classroom';
         $this->base_link_edition = 'alms/edition';
         $this->base_link_subscription = 'alms/subscription';
         $this->base_link_competence = 'adm/competences';

         $this->lo_types_cache = false;
                    
                    
                    // DA AGGIUNGERE!
                                        
         $this->permissions = array(
             'view'            => checkPerm('view', true, 'course', 'lms'),
             'add'            => checkPerm('add', true, 'course', 'lms'),
             'mod'            => checkPerm('mod', true, 'course', 'lms'),
             'del'            => checkPerm('del', true, 'course', 'lms'),
             'moderate'        => checkPerm('moderate', true, 'course', 'lms'),
             'subscribe'        => checkPerm('subscribe', true, 'course', 'lms'),
             'add_category'    => checkPerm('add', true, 'coursecategory', 'lms'),
             'mod_category'    => checkPerm('mod', true, 'coursecategory', 'lms'),
             'del_category'    => checkPerm('del', true, 'coursecategory', 'lms'),
             'view_cert'        => checkPerm('view', true, 'certificate', 'lms'),
             'mod_cert'        => checkPerm('mod', true, 'certificate', 'lms')
         );

        $_SESSION['meta_certificate'] = array(); // testare inizializzazione variabile di sessione.


         */


        require_once(_base_.'/lib/lib.json.php');
        $this->json = new Services_JSON();

        $this->controller_name = strtolower(str_replace('AlmsController','',get_class($this)));
        
        require_once(_files_lms_.'/'._folder_lib_.'/lib.aggregated_certificate.php');
        $this->aggCertLib = new AggregatedCertificate();

      //  $this->model = new AggregatedcertificateAlms();
    }

   
    // ---------- Certificate management ------------
    
    /** Default op.
    * 
    * Metacertificate administration panel
    * 
    * Action: show
    *  
    */ 
    function show() {

        $params = array();
        
        checkPerm('view');

        $tb    = new Table(Get::sett('visuItem'), Lang::t('_AGGREGATE_CERTIFICATE_LIST'), Lang::t('_AGGREGATE_CERTIFICATE_LIST'));
        $tb->initNavBar('ini', 'link');
        $tb->setLink("index.php?r=alms/".$this->controller_name."/".$this->op['home']);        
        $ini = $tb->getSelectedElement();

        
        $filter_text = Get::req('filter_text', DOTY_STRING, '');
        
        if (Get::req('toggle_filter', DOTY_STRING, '') != '')
                unset($filter_text);

        if ( $filter_text != '' ){
            $filter['filter_text'] = $filter_text;
            $params['filter_text'] = $filter_text;
        }
               

        // Type for all columns
        $type_h = array('', '', '', 'image', 'image');

        // Label for all columns
        $cont_h    = array(
            Lang::t('_CODE'),
            Lang::t('_NAME'),
            Lang::t('_DESCRIPTION')
        );

        $userCanModify = checkPerm('mod', true);

        if($userCanModify) 
            $cont_h[] = Lang::t('_TEMPLATE', 'certificate');

        $cont_h[] = Get::img('standard/view.png', Lang::t( '_PREVIEW' ));

        if($userCanModify) {
        
            $cont_h[] =    Get::img('standard/moduser.png', Lang::t('_ASSOCIATES_AGGREGATE_CERTIFICATE'));
            $type_h[] =    'image';

            $cont_h[] =    Get::sprite('subs_print', Lang::t('_ASSIGN_AGGREGATE_CERTIFICATE'));
            $type_h[] =    'image';

            $cont_h[] =    Get::img('standard/edit.png', Lang::t('_MOD'), Lang::t('_MOD'));
            $type_h[] =    'image';

            $cont_h[] =  Get::img('standard/delete.png', Lang::t('_DEL'), Lang::t('_DEL'));
            $type_h[] =    'image';
            
        }

        $tb->setColsStyle($type_h);
        $tb->addHead($cont_h);           


        // Array of all metacertificates to display in the main admin panel
        $aggregateCertsArr = $this->aggCertLib->getAllAggregatedCerts($ini, false, $filter);

            foreach ($aggregateCertsArr as $aggregate_cert) {
                $title = strip_tags($aggregate_cert["name"]);

                $cont = array(
                    $aggregate_cert["code"],     
                    $aggregate_cert["name"],     
                    Util::cut($aggregate_cert["description"])
                );


            if($userCanModify)
                    $cont[] = '<a href="index.php?r=alms/'.$this->controller_name.'/'.$this->op['layout'].'&amp;id_certificate='.$aggregate_cert["id_certificate"].'&amp;edit=1" '
                    .'title="'.Lang::t('_TEMPLATE', 'certificate').'">'
                    .Lang::t('_TEMPLATE', 'certificate').'</a>';


                    $cont[] = Get::sprite_link(                           
                        'subs_view',
                        'index.php?r=alms/'.$this->controller_name.'/'.$this->op['preview'].'&amp;id_certificate='.$aggregate_cert["id_certificate"],
                        Lang::t('_PREVIEW')
                    ); 
                    
            if($userCanModify) {
                    
                    $cont[] = Get::sprite_link(    
                        'subs_admin',
                        'index.php?r=alms/'.$this->controller_name.'/'.$this->op['associationsManagement'].'&amp;id_certificate='.$aggregate_cert["id_certificate"],
                         Lang::t('_ASSOCIATES_AGGREGATE_CERTIFICATE')        
                        );
                        
                    $cont[] = Get::sprite_link(
                        'subs_print',
                        'index.php?r=alms/'.$this->controller_name.'/'.$this->op['assignmentManagement'].'&amp;id_certificate='.$aggregate_cert["id_certificate"],
                        Lang::t('_ASSIGN_AGGREGATE_CERTIFICATE')
                        );

                    $cont[] = Get::sprite_link(
                        'subs_mod',
                        'index.php?r=alms/'.$this->controller_name.'/'.$this->op['metadata'].'&amp;id_certificate='.$aggregate_cert["id_certificate"],
                        Lang::t('_MOD') . ' : ' . $title  
                        ); 
                           
                    $cont[] = Get::sprite_link(
                        'subs_del',
                        'index.php?r=alms/'.$this->controller_name.'/'.$this->op['delmetacert'].'&amp;id_certificate='.$aggregate_cert["id_certificate"],
                        Lang::t('_DEL') . ' : ' . $title  
                        ); 
                }

                $tb->addBody($cont);
            }     

        
        require_once(_base_.'/lib/lib.dialog.php');
        
        setupHrefDialogBox('a[href*='.$this->op['delmetacert'].']');

        if($userCanModify) {
           $tb->addActionAdd('
               <a   class="ico-wt-sprite subs_add" 
                    href="index.php?r=alms/'.$this->controller_name.'/'.$this->op['metadata'].'" 
                    title="'.Lang::t('_NEW_AGGREGATE_CERTIFICATE').'">
                    <span>'.Lang::t('_NEW_AGGREGATE_CERTIFICATE').'</span>
               </a>
           ');
        }
        
      
       
        
            $params["tb"] = $tb;
            $params["ini"] = $ini;
            $params["countAggrCerts"] = count($this->aggCertLib->getAllAggregatedCerts($ini, true, $filter));
            
            $params["controller_name"] = $this->controller_name;
            $params["opsArr"] = $this->op;
       
        $this->render($this->op['home'], $params);

    }

    function metadata() {
        
        checkPerm('mod');
        
        $all_languages     = Docebo::langManager()->getAllLanguages();
        $languages = array();
        
        foreach($all_languages as $k => $v) { 
             $languages[$v[0]] = $v[1];
        }
          
        require_once(_base_.'/lib/lib.form.php');
        $form = new Form();
        
        
            
        $params = array();
          
        $id_cert = Get::req('id_certificate',DOTY_INT, 0);
        
        $isModifyingMetaData = ($id_cert !== 0);
        $page_title = array(
        'index.php?r=alms/'.$this->controller_name.'/metadata' => Lang::t('_TITLE_META_CERTIFICATE','certificate'),
        $isModifyingMetaData ? Lang::t('_MOD_METACERTIFICATE','metacertificate') : Lang::t('_NEW_METACERTIFICATE','metacertificate') 
        );
        
        if($isModifyingMetaData) {
            
            $params['metacert'] = $this->aggCertLib->getMetadata($id_cert)[0];
            $params['id_certificate'] = $id_cert;
            
        }
        
        $params['page_title'] = $page_title;
        $params['controller_name'] = $this->controller_name;
        $params['opArr'] = $this->op;
        
        $params['languages'] = $languages;
        $params['form'] = $form;

        
        $this->render($this->op['metadata'], $params);
    }
    
        function saveMetaData() {
        
        checkPerm('mod');
       
        if(isset($_POST["undo"]))
            Util::jump_to('index.php?r=alms/'.$this->controller_name.'/'.$this->op['home']);
        
        $isModifyingMetadata = isset($_POST['id_certificate']); 
        
        $metaDataCertArr = array(
            
            "code" => $_POST["code"],
            "name" => $_POST["name"] == '' ? Lang::t('_NOTITLE') : $_POST["name"],
            "base_language" => $_POST["base_language"],
            "descr" => $_POST["descr"],
            "user_release" => $_POST["user_release"],
            "meta" => 1,

        );
        
        if ($isModifyingMetadata)
            $metaDataCertArr['id_certificate'] = Get::req("id_certificate");
        
        $res = $this->aggCertLib->insertMetaDataCert($metaDataCertArr);
        
        if($res){
            if($isModifyingMetadata)
                Util::jump_to('index.php?r=alms/'.$this->controller_name.'/'.$this->op['home'].'&result='. ($res ? 'ok' : 'err'));
            else
                Util::jump_to('index.php?r=alms/'.$this->controller_name.'/'.$this->op['layout'].'&id_certificate='. $this->aggCertLib->getLastInsertedIdCertificate());
          
        } else Util::jump_to('index.php?r=alms/'.$this->controller_name.'/'.$this->op['home'].'&result=err');
 
                  
    }
    
        
    function layout() {
        
        checkPerm('view');
        checkPerm('mod', true);
        
        $id_certificate = Get::req('id_certificate', DOTY_INT, 0);
       
        // If the user want to edit layout, then in the page will be loaded the datas from the db
        $edit = Get::req('edit', DOTY_INT, 0);
         
        $page_title = array(
            'index.php?r=alms/'.$this->controller_name.'/'.$this->op['layout'] => Lang::t('_TITLE_META_CERTIFICATE','certificate'),
            Lang::t('_STRUCTURE_META_CERTIFICATE','certificate')
        );

        if($edit && $id_certificate !== 0)
            $template = $this->aggCertLib->getLayoutMetacert($id_certificate);
         
        $certificate_tags = $this->aggCertLib->getCertificateTags();
          
        $params = array(
            "controller_name" => $this->controller_name,
            "page_title" => $page_title,

            "id_certificate" => $id_certificate,
            "certificate_tags" => $certificate_tags,
        );
        
        if(isset($template))
            $params['template'] = $template;
        
        $this->render( $this->op['layout'],$params);
    }
    
        function saveLayout() {
        
            checkPerm('mod');
            
            if(isset($_POST["undo"]))
                Util::jump_to('index.php?r=alms/'.$this->controller_name.'/'.$this->op['home']);
            
            
            if(isset($_POST['structure_certificate'])) {
                $path     = '/appLms/certificate/';
                $path     = $path.( substr($path, -1) != '/' && substr($path, -1) != '\\' ? '/' : '');

                isset($_POST['file_to_del']['bgimage']) ? $flagDeleteOldImage = $_POST['file_to_del']['bgimage'] : null;

                $bgimage = $this->manageCertificateFile(    'bgimage',
                    $_POST["old_bgimage"],
                    $path,
                    $flagDeleteOldImage); 

                if(!$bgimage)
                    $bgimage = "";
            }
            
            $layoutArr = array(
                
                "id_certificate" => $_POST['id_certificate'],
                "cert_structure" => "'" . $_POST["structure"] . "'",
                "orientation" => "'" . $_POST["orientation"] . "'" ,
                "bgimage" => "'" . $bgimage . "'",
                
                
            );
                    
            $res = $this->aggCertLib->updateLayout($layoutArr);
             
            Util::jump_to('index.php?r=alms/'.$this->controller_name.'/'.$this->op['home'].'&result='. ($res ? 'ok' : 'err'));
                       
        }

 
    // ------------- Associations management ------------------
    
    /**
    * Assignment / metacertificate management
    * 
    * In this action, will be rendered all the tools for managing 
    * association between a certificate with the users and the courses
    * 
    * 
    */
    function associationsManagement() {
    
        checkPerm('mod');
        require_once(_base_.'/lib/lib.table.php');

        $id_certificate = Get::req('id_certificate',DOTY_INT,0);

        // Creating table...
        $tb = new Table(Get::sett('visuItem'), Lang::t('_AGGREGATE_CERTIFICATES_ASSOCIATION_CAPTION'), Lang::t('_AGGREGATE_CERTIFICATES_ASSOCIATION_CAPTION'));
        $tb->initNavBar('ini', 'link');
        $tb->setLink('index.php?r=alms/'.$this->controller_name.'/'.$this->op['associationsManagement'].'&id_certificate='.$id_certificate);
        $ini = $tb->getSelectedElement();

        // Getting all metacerts belonging to the certificate
        $associationsMetadataArr = $this->aggCertLib->getAssociationsMetadata($id_certificate, 0, $ini);

        $type_h = array('',  // Name
                        '',  // Description
                        'image',  // View details img
                        'image',  // Edit Metadata Metacert. img
                        'image',  // Course edit  img
                        'image',  // Coursepath edit img
                        'image' );// Delete metacert. img

        $cont_h = array(

                Lang::t('_NAME'),
                Lang::t('_DESCRIPTION'),
                '<img src="'.getPathImage().'standard/view.png" alt="'.Lang::t( '_DETAILS' ).'" title="'.Lang::t( '_DETAILS' ).'" />',
                '<img src="'.getPathImage().'standard/course.png" title="'.Lang::t('_COURSE').'" alt="'.Lang::t('_COURSE').'" />',
                '<img src="'.getPathImage().'standard/coursepath.png" title="'.Lang::t('_COURSEPATH').'" alt="'.Lang::t('_COURSEPATH').'" />',
                '<img src="'.getPathImage().'standard/edit.png" title="'.Lang::t('_MOD').'" alt="'.Lang::t('_MOD').'" />',
                '<img src="'.getPathImage().'standard/delete.png" title="'.Lang::t('_DEL').'" alt="'.Lang::t('_DEL').'"" />'
                
            );

        $tb->setColsStyle($type_h);
        $tb->addHead($cont_h);

        
        
        foreach ($associationsMetadataArr as $k => $association) {

            // Getting type of metacert. (if it's a metacert on course, on coursepath...)
            $type_association = $this->aggCertLib->getTypeAssoc($association["idAssociation"] );
            
            $rows = array();
            
            $rows[] = stripslashes($association["title"]);
            $rows[] = stripslashes($association["description"]);
            $rows[] = Get::sprite_link( 
                        'subs_view',
                        'index.php?r=alms/'.$this->controller_name.'/'.$this->op['view_details'].'&amp;id_association='.$association["idAssociation"].'&amp;id_certificate='.$id_certificate.'&amp;type_assoc='.$type_association,
                        Lang::t( '_DETAILS' )  
                    );
            // Depending on the type of the course
            $rows[] = ($type_association == COURSE) ? 
                    '<a href="index.php?r=alms/'.$this->controller_name.'/'.$this->op["associationCourses"]
                        .'&amp;id_certificate='.$id_certificate
                        .'&amp;id_association='.$association["idAssociation"]
                        .'&amp;type_assoc='. COURSE
                        .'&amp;edit=1'
                        .'">
                        <img src="'.getPathImage().'standard/course_active.png"
                             alt="'.Lang::t( '_MODIFY_COURSE_ASSOCIATIONS' ).'" 
                             title="'.Lang::t( '_MODIFY_COURSE_ASSOCIATIONS' ).'" />
                    </a>' : '<img src="'.getPathImage().'standard/course.png"
                                  alt="'.Lang::t( '_COURSE' ).'" 
                                  title="'.Lang::t( '_COURSE' ).'" />'; 
            
            $rows[] = ($type_association == COURSE_PATH) ?  
                    '<a href="index.php?r=alms/'.$this->controller_name.'/'.$this->op["associationCourses"]
                        .'&amp;id_certificate='.$id_certificate
                        .'&amp;id_association='.$association["idAssociation"]
                        .'&amp;type_assoc='. COURSE_PATH
                        .'&amp;edit=1'
                        .'">
                        <img 
                            src="'.getPathImage().'standard/coursepath_active.png" 
                            alt="'.Lang::t( '_MODIFY_COURSEPATH_ASSOCIATIONS' ).'" 
                            title="'.Lang::t( '_MODIFY_COURSEPATH_ASSOCIATIONS' ).'" />
                    </a>' :  
                        '<img 
                            src="'.getPathImage().'standard/coursepath.png" 
                            alt="'.Lang::t( '_MODIFY_COURSEPATH_ASSOCIATIONS' ).'" 
                            title="'.Lang::t( '_MODIFY_COURSEPATH_ASSOCIATIONS' ).'" />';
            
            
            $rows[] = Get::sprite_link( 
                        'subs_mod',
                        'index.php?r=alms/'.$this->controller_name.'/'.$this->op['metaDataAssoc']
                        .'&amp;id_association='.$association["idAssociation"]
                        .'&amp;id_certificate='.$id_certificate
                        .'&amp;type_assoc=' . $type_association
                        .'&amp;edit=1',
                        Lang::t('_MOD')
                    );
                   
            $rows[] = Get::sprite_link( 
                        'subs_del',
                        'index.php?r=alms/'.$this->controller_name.'/'.$this->op['del_association']
                        .'&amp;id_association='.$association["idAssociation"]
                        .'&amp;id_certificate='.$id_certificate
                        .'&amp;type_assoc='.$type_association,
                        Lang::t('_DEL')
                    ); 

            
            $tb->addBody( $rows );

        }

        require_once(_base_.'/lib/lib.dialog.php');
        setupHrefDialogBox('a[href*=delassignmetacertificate]');

        $tb->addActionAdd(    
            '<a class="new_element_link" 
            href="index.php?r=alms/'.$this->controller_name.'/'.$this->op['metaDataAssoc'].'&amp;id_certificate='.$id_certificate.'"
            title="'.Lang::t('_NEW_ASSOCIATION_AGGREGATE_CERTIFICATE').'">'
            .Lang::t('_NEW_ASSOCIATION_AGGREGATE_CERTIFICATE')
            .'</a>'
        );
        
        setupHrefDialogBox('a[href*='. $this->op['del_association'].']');

        // Aggiungere messaggi di errore con dettagli
        if(isset($_GET['res']))
        {
            switch($_GET['res'])
            {
                case "ok":
                    cout(getResultUi(Lang::t('_OPERATION_SUCCESSFUL')));
                break;
                case "err":
                    cout(getErrorUi(Lang::t('_OPERATION_FAILURE')));
                break;
                case "err_del":
                    cout(getErrorUi(Lang::t('_OPERATION_FAILURE')));
                break;
                case "err_info":
                    cout(getErrorUi(Lang::t('_OPERATION_FAILURE')));
                break;
                case "err_mod_info":
                    cout(getErrorUi(Lang::t('_OPERATION_FAILURE')));
                break;
                case "error_mod_assign":
                    cout(getErrorUi(Lang::t('_OPERATION_FAILURE')));
                break;
            }
        }
        
        $params = array(
            "id_certificate" => $id_certificate,
            "countAssociations" => count($this->aggCertLib->getAssociationsMetadata($id_certificate)),
            "ini" => $ini,
            
            "arrOps" => $this->op,
            "controller_name" => $this->controller_name,
            "tb" => $tb,
        );
        
        $this->render( $this->op['associationsManagement'], $params);
        
    }

    function metaDataAssoc() {

        $id_certificate = Get::req('id_certificate');

        // If the user is modifying the metadata of the assoc. edit will be 1.
        $edit = Get::req('edit', DOTY_INT, 0);
       
        $params = array();
        
        // necessary for passing additional parameters to the form (ex. disabled to type selector)
        $params['html_before_select'] = ''; 
        

        if ($edit) { // If i'm editing an association, i need to get all datas. of that assoc.

            // If the certificate has no association, the id_association will be 0
            $id_association = Get::req('id_association', DOTY_INT, 0);
            
            if ($id_association !== 0 ) {

                $associationMetadataArr = $this->aggCertLib->getAssociationsMetadata(0, $id_association);

                $params['associationMetadataArr'] = $associationMetadataArr[0];
                $params['html_before_select'] = 'disabled';

                $params['type_assoc'] = Get::req('type_assoc', DOTY_INT, -1);

                $params['id_association'] = $id_association;
                $params['edit'] = $edit;
            }

       }

       $assoc_types = array(
       
            COURSE => Lang::t('_COURSE'),
            COURSE_PATH => Lang::t('_COURSEPATH'),
            
       );
        
        
       $params['id_certificate'] = $id_certificate;

       $params['assoc_types'] = $assoc_types;

       $params["controller_name"] = $this->controller_name;
       $params["arrOps"] = $this->op;

       $this->render($this->op['metaDataAssoc'], $params);
    }


        function saveMetaDataAssoc(){
           
            checkPerm('mod');
           
            if(isset($_POST["undo_assign"]))
                Util::jump_to('index.php?r=alms/'.$this->controller_name.'/'.$this->op['associationsManagement'].'&id_certificate='.Get::req("id_certificate",DOTY_INT,0));
            
            $isModifyingMetadataAssoc = Get::req("edit", DOTY_INT, 0);
            
            
            $association = array(
            
                "idCertificate" => $_POST["id_certificate"],
                "title" => "'" . $_POST["title"] . "'",         // strip hyphen
                "description" => "'" . $_POST["description"] . "'", // strip hyphen

            );
            
            $type_assoc = Get::req('type_assoc', DOTY_INT, -1);

            if($isModifyingMetadataAssoc)             
                $association["idAssociation"] = $_POST['id_association']; // need to get assoc. id for update
           
            
            $res = $this->aggCertLib->insertMetadataAssoc($association);
             
            if($isModifyingMetadataAssoc !== 0)
                Util::jump_to('index.php?r=alms/'.$this->controller_name.'/'.$this->op['associationsManagement']
                    .'&id_certificate='. $association['id_certificate']
                    .'&res='. ($res ? 'ok' : 'err'));
            else
                Util::jump_to(  'index.php?r=alms/'.$this->controller_name.'/'.$this->op['associationCourses']
                                .'&amp;id_certificate='.$_POST["id_certificate"]
                                .'&amp;id_association='.$this->aggCertLib->getLastInsertedAssociationId()
                                .'&amp;type_assoc='.$type_assoc);
                 
    }

    /**
     * Creating an association between users and courses
     *
     * Selecting the courses/coursepaths for the association
     *
     */

    function associationCourses() {

        require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
        require_once($GLOBALS['where_lms'].'/lib/lib.course_managment.php');

        if(isset($_POST['cancelselector']))
            Util::jump_to('index.php?r=alms/'.$this->controller_name.'/'.$this->op['associationsManagement'].'&amp;id_certificate='.Get::req('id_certificate', DOTY_INT, 0));


        $id_association = Get::req('id_association', DOTY_INT, 0);
        $type_association = Get::req('type_assoc', DOTY_INT, -1);

        $params = array(

            "id_certificate" => Get::req("id_certificate", DOTY_INT),
            "id_association" => $id_association,
            "type_assoc" => $type_association,

            "opsArr" => $this->op,
            "controller_name" => $this->controller_name,

        );

        $edit = Get::req('edit', DOTY_INT, 0);
        if($edit != 1)
            $edit = 0;
        $params['edit'] = $edit;

        // loading courses tree / course path list
        switch ($type_association) {

            case COURSE:

                $treeCat = $this->getTreeCategoryAsArray();

                //$course_manager = new Course_Manager();
                //$course_manager->setLink('index.php?r=alms/'.$this->controller_name.'/'.$this->op['associationCourses']);

                // If type association is course, then get all course ids from the assoc
              //  $coursesIdsArr =

                $params['idsCourses'] = $this->aggCertLib->getIdsCourse($id_association);


                //$params["course_manager"] = $course_manager;
                $params['treeCat'] = $treeCat;

                if($edit){

                    $params['coursesArr'] = (!empty($params['idsCourses']) ? $this->aggCertLib->getCoursesArrFromId($params['idsCourses']) : []);

                }

                break;

            case COURSE_PATH:


                $coursePathIdsArr = $this->aggCertLib->getIdsCoursePath($id_association);
                $params['idsCoursePath'] = ($id_association !== 0) ? $coursePathIdsArr : '';

                if($edit) {
                    if(!empty($coursePathIdsArr)) {

                        require_once($GLOBALS['where_lms'].'/lib/lib.coursepath.php');
                        $coursePath_man = new CoursePath_Manager();
                        $params['coursePathsArr'] = $coursePath_man->getCoursepathAllInfo($coursePathIdsArr);

                    }
                }

                break;

            default:
                break;
        }

        $this->render( $this->op['associationCourses'], $params);
    }
    
    function associationUsers() {

        require_once(_base_.'/lib/lib.userselector.php');
        require_once(_base_.'/lib/lib.form.php');

        $id_certificate = Get::req('id_certificate', DOTY_INT, -1);
        $id_association = Get::req('id_association', DOTY_INT, 0);
        $type_assoc = Get::req('type_assoc', DOTY_INT, -1);

        if(isset($_POST["undo"]) || isset($_POST["undo_filter"]) || isset($_POST["cancelselector"]))
            Util::jump_to('index.php?r=alms/'.$this->controller_name.'/'.$this->op['associationsManagement'].'&amp;id_certificate='.$id_certificate);

        $edit = Get::req('edit', DOTY_INT, 0);

        if($type_assoc == -1) cout(getErrorUi(Lang::t('Association Type not recognized'))); // TODO: Add error message

        $user_selection = new UserSelector();


        // Getting link ids selected
        switch($type_assoc) {

            case COURSE:

                // Arr. of courses sent by form
                $new_courses = array_map('intval', explode(",", Get::req("idsCourse")));

                // Already selected courses
                $alreadySelectedCourses = array_map('intval', explode(",", Get::req("alreadySelCourses", DOTY_JSONENCODE, "")));

                $todeleteArr = array_diff($alreadySelectedCourses, $new_courses);
                $toAddCoursesArr = array_diff($new_courses, $alreadySelectedCourses);

                if(!empty($todeleteArr)) {
                        $res = $this->aggCertLib->deleteAssociationLinks($id_association, $type_assoc, [], $todeleteArr);
                        if (!$res) cout(getErrorUi(Lang::t("Error deleting associations")));
                }

                //if(!empty($toAddCoursesArr))
                    //  $user_selection->addFormInfo('<input type="hidden" name="toAddCourses" value=' . json_encode($toAddCoursesArr) . ' />');


                /*if(!empty($toAddCoursesArr)) {

                            $userCourses = array();
                            foreach ($array_user as $user_id) {
                                $userCourses[(int) $user_id] = $toAddCoursesArr;
                            }

                            $assocArr = array(

                                $id_association => $userCourses

                            );

                            $res = $this->aggCertLib->insertAssociationLink($type_assoc, $assocArr );
                            if (!$res) return; //TODO: add error message
                }*/

                unset($_SESSION['meta_certificate']['courses']);
                $_SESSION['meta_certificate']['courses'] = $new_courses;

                // $_SESSION['meta_certificate']['course'] = explode(",", Get::req("idscourses"));


                break;

            case COURSE_PATH:
                
                // Arr. of coursepaths sent by form
                $new_coursepaths = array_map('intval', explode(",", Get::req("idsCoursePaths")));

                // Already selected courses
                $alreadySelCoursepaths = array_map('intval', explode(",", Get::req("alreadySelCoursepaths", DOTY_JSONENCODE, "")));

                $todeleteArr = array_diff($alreadySelCoursepaths, $new_coursepaths);
                //$toAddCoursesArr = array_diff($new_coursepaths, $alreadySelCoursepaths);

                if(!empty($todeleteArr)) {
                    $res = $this->aggCertLib->deleteAssociationLinks($id_association, $type_assoc, [], $todeleteArr);
                    if (!$res) cout(getErrorUi(Lang::t("Error deleting associations")));
                }
                
                unset($_SESSION['meta_certificate']['coursepaths']);
                $_SESSION['meta_certificate']['coursepaths'] = array_map('intval', explode(",", Get::req("idsCoursePath")));


                break;

            default:
                break;

        }

        if($edit == 1 && $type_assoc != -1) {

            /*
             * Editing assoc.
             *
             * Need to compare old users and new, to add or to remove users from assoc.
             */
            $usersArr = $this->aggCertLib->getAllUsersFromIdAssoc($id_association, $type_assoc);
            
            // Need to pass all the idst of the users / groups / org_chart
            $user_selection->resetSelection($usersArr);
          
        
            
            $user_selection->addFormInfo('<input type="hidden" name="old_users" value=' . json_encode($usersArr) . ' />');

        }
        
        $user_selection->show_orgchart_simple_selector = FALSE;
        $user_selection->show_user_selector = TRUE;
        $user_selection->show_group_selector = TRUE;
        $user_selection->show_orgchart_selector = TRUE;
        $user_selection->show_fncrole_selector = FALSE;
        $user_selection->multi_choice = TRUE;

        $user_selection->setPageTitle(getTitleArea(Lang::t('_TITLE_META_CERTIFICATE_ASSIGN','certificate'), 'certificate'));
        
        $user_selection->addFormInfo('<input type="hidden" name="id_certificate" value=' .$id_certificate . ' />');
        $user_selection->addFormInfo('<input type="hidden" name="id_association" value=' . $id_association . ' />');
        $user_selection->addFormInfo('<input type="hidden" name="type_assoc" value=' . $type_assoc . ' />');
        $user_selection->addFormInfo('<input type="hidden" name="edit" value=' . $edit . ' />');





        $params = array(
            "user_selection" => $user_selection,

            "opsArr" => $this->op,
            "controller_name" => $this->controller_name,
        );
        
        $this->render($this->op['associationusers'], $params);
    }
    
    function associationUsersCourses() {
       
        // Loading necessary libraries
        require_once(_base_.'/lib/lib.userselector.php');
        require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
        require_once($GLOBALS['where_lms'].'/lib/lib.coursepath.php');
        require_once($GLOBALS['where_lms'].'/lib/lib.course_managment.php');

        YuiLib::load();
        Util::get_js(Get::rel_path('base').'/lib/js_utils.js', true, true);


        $id_certificate = Get::req("id_certificate", DOTY_INT);
        $id_association = Get::req("id_association", DOTY_INT);
        $type_assoc = Get::req('type_assoc', DOTY_INT, -1);

        if(isset($_POST["undo"]) || isset($_POST["undo_filter"]) || isset($_POST["cancelselector"]))
            Util::jump_to('index.php?r=alms/'.$this->controller_name.'/'.$this->op['associationsManagement'].'&amp;id_certificate='.$id_certificate);


        // Users selected on loading of page assoc. users
        $old_users = array_map('intval', explode(",", str_replace(array("[", "]"), "", Get::req('old_users', DOTY_JSONENCODE, ''))));

        // Users after editing (there may be the same users, new users added, or user to delete)

        $user_selection = new UserSelector();
        $userSelectionArr = array_map('intval',$user_selection->getSelection($_POST));

        $_SESSION['meta_certificate']['userSelectionArr'] = $userSelectionArr;

        sort($old_users);
        sort($userSelectionArr);

        if($old_users != $userSelectionArr){ // There are some changes with the users...

            $to_delete_users = array_diff($old_users, $userSelectionArr);
            if (!empty($to_delete_users)) {
                $res = $this->aggCertLib->deleteAssociationLinks($id_association, $type_assoc, $to_delete_users);
                //TODO: add error message, can't delete deselected users.
                if(!$res) cout(getErrorUi(Lang::t('Can\'t delete deselected users')));
            }

            $to_add_users = array_diff($userSelectionArr, $old_users );
            /*if (!empty($to_add_users)) {

                $userCourses = array();
                foreach ($to_add_users as $user) {
                    $userCourses[$user] = $coursesIdsArr;
                }


                $assocArr = array(
                    $id_association => $userCourses
                );

                $res = $this->aggCertLib->insertAssociationLink($type_assoc, $assocArr);
                if(!$res) cout(getErrorUi(Lang::t('Error adding association')));  //TODO: add error message, can't add new selected users.


            }*/
        }



        $form = new Form();
        $form_name = 'new_assign_step_3';


        $tb    = new Table(0, Lang::t('_META_CERTIFICATE_NEW_ASSIGN_CAPTION','certificate'), Lang::t('_META_CERTIFICATE_NEW_ASSIGN_SUMMARY'));
        $tb->setLink('index.php?r=alms/'.$this->controller_name.'/'.$this->op['home']);
        $tb->setTableId("tb_AssocLinks");

        // Setting table header

        $type_h = array('', '');
        $cont_h = array(Lang::t('_FULLNAME'), Lang::t('_USERNAME'));


        $acl_man =& Docebo::user()->getAclManager();
        $aclManager = new DoceboACLManager();

        $array_user =& $aclManager->getAllUsersFromIdst($_SESSION['meta_certificate']['userSelectionArr']);
        $array_user = array_unique($array_user);

        //$array_user = $this->model->getIdStFromidUser($array_user);
        $array_user = $aclManager->getArrUserST($array_user);

        switch($type_assoc){

            case COURSE:

              /*  $new_courses = array_map('intval', explode(",", Get::req("idsCourse")));

                $oldselectedCourses = Get::req("oldestCourses", DOTY_JSONENCODE, "");
                if(!empty($oldselectedCourses)) { // I've changed the courses in the assoc.

                    $to_delete_courses = array_map('intval', explode(",", $oldselectedCourses));

                    $todeleteArr = array_diff($to_delete_courses, $new_courses);
                    $toAddCoursesArr = array_diff($new_courses, $to_delete_courses);

                    if(!empty($todeleteArr)) {
                       $res = $this->aggCertLib->deleteAssociationLinks($id_association, $type_assoc, [], $todeleteArr);
                        if (!$res) cout(getErrorUi(Lang::t("Error deleting associations"))); 
                    }

               /*     if(!empty($toAddCoursesArr)) {

                        $userCourses = array();
                        foreach ($array_user as $user_id) {
                            $userCourses[(int) $user_id] = $toAddCoursesArr;
                        }

                        $assocArr = array(

                            $id_association => $userCourses

                        );

                        $res = $this->aggCertLib->insertAssociationLink($type_assoc, $assocArr );
                        if (!$res) return; //TODO: add error message
                    }




                }

                unset($_SESSION['meta_certificate']['courses']);
                $_SESSION['meta_certificate']['courses'] = $new_courses;
              */

                // $_SESSION['meta_certificate']['course'] = explode(",", Get::req("idscourses"));
                foreach($_SESSION['meta_certificate']['courses'] as $id_course) {

                    $type_h[] = 'align_center';

                    $course_man = new Man_Course();
                    $course_info = $course_man->getCourseInfo($id_course);

                    $cont_h[] = $course_info['code'].' - '.$course_info['name'];


                    $cont_footer[] =   '<a href="javascript:;" onclick="checkall_meta(\''.$form_name.'\', \''.$id_course.'\', true); return false;">'
                        .Lang::t('_SELECT_ALL')
                        .'</a><br/>'
                        .'<a href="javascript:;" onclick="checkall_meta(\''.$form_name.'\', \''.$id_course.'\', false); return false;">'
                        .Lang::t('_UNSELECT_ALL')
                        .'</a>';

                }

                break;

            case COURSE_PATH:

              /*  unset($_SESSION['meta_certificate']['coursepaths']);
                $_SESSION['meta_certificate']['coursepaths'] = explode(",", Get::req("idsCoursePath"));*/

                $coursePath_man = new CoursePath_Manager();
                $coursePathInfoArr = $coursePath_man->getCoursepathAllInfo($_SESSION['meta_certificate']['coursepaths']);

                foreach($coursePathInfoArr as $coursePathInfo) {

                    $type_h[] = 'align_center';
                    $cont_h[] = $coursePathInfo[COURSEPATH_CODE].' - '.$coursePathInfo[COURSEPATH_NAME];

                    $cont_footer[] =   '<a href="javascript:;" onclick="checkall_meta(\''.$form_name.'\', \''.$coursePathInfo[COURSEPATH_ID].'\', true); return false;">'
                        .Lang::t('_SELECT_ALL')
                        .'</a><br/>'
                        .'<a href="javascript:;" onclick="checkall_meta(\''.$form_name.'\', \''.$coursePathInfo[COURSEPATH_ID].'\', false); return false;">'
                        .Lang::t('_UNSELECT_ALL')
                        .'</a>';
                }

                break;

            default:
                break;

        }


        $type_h[] = 'image';
        $cont_h[] = Lang::t('_SELECT_ALL');

        $type_h[] = 'image';
        $cont_h[] = Lang::t('_UNSELECT_ALL');

        $tb->setColsStyle($type_h);
        $tb->addHead($cont_h);

        reset($_SESSION['meta_certificate']['courses']);
        reset($_SESSION['meta_certificate']['coursepaths']);


        foreach($array_user as $username =>  $id_user)  {

            $cont = array();

            $user_info = $acl_man->getUser($id_user, false);

            $cont[] = $user_info[ACL_INFO_LASTNAME].' '.$user_info[ACL_INFO_FIRSTNAME];

            $cont[] = $acl_man->relativeId($user_info[ACL_INFO_USERID]);

            switch($type_assoc){

                case COURSE:

                    // $_SESSION['meta_certificate']['course'] = explode(",", Get::req("idscourses"));
                    foreach($_SESSION['meta_certificate']['courses'] as $id_course) {

                        if(
                            // isset($_POST['_'.$id_user.'_'.$id_course.'_']
                            $this->aggCertLib->getAssociationLink($id_association, $type_assoc,(int) $id_user, (int) $id_course)
                            ||
                            isset($_POST['select_all'])
                        )
                            $checked = true;
                        else
                            $checked = false;

                        $cont[] = $form->getCheckbox('', '_'.$id_user.'_'.$id_course.'_', '_'.$id_user.'_'.$id_course.'_', 1, $checked);



                    }

                    break;

                case COURSE_PATH:
                    foreach($_SESSION['meta_certificate']['coursepaths'] as $id_coursepath) {

                        if (
                            // isset($_POST['_'.$id_user.'_'.$id_course.'_']
                            $this->aggCertLib->getAssociationLink($id_association, $type_assoc, (int)$id_user, (int)$id_coursepath)
                            ||
                            isset($_POST['select_all'])
                        )
                            $checked = true;
                        else
                            $checked = false;

                        $cont[] = $form->getCheckbox('', '_' . $id_user . '_' . $id_coursepath . '_', '_' . $id_user . '_' . $id_coursepath . '_', 1, $checked);

                    }
                        break;

                default:
                    break;

            }

            $cont[] =    '<a href="javascript:;" onclick="checkall_fromback_meta(\''.$form_name.'\', \''.$id_user.'\', true); return false;">'
                .Lang::t('_SELECT_ALL')
                .'</a>';
            $cont[] =    '<a href="javascript:;" onclick="checkall_fromback_meta(\''.$form_name.'\', \''.$id_user.'\', false); return false;">'
                .Lang::t('_UNSELECT_ALL')
                .'</a>';

            $tb->addBody($cont);
        }

        reset($_SESSION['meta_certificate']['courses']);
        reset($_SESSION['meta_certificate']['coursepaths']);


        $cont = array();

        $cont[] = '';
        $cont[] = '';

        foreach($cont_footer as $footer){
            $cont[] = $footer;
        }


        $cont[] = '';
        $cont[] = '';

        $tb->addBody($cont);
        
                    
        $params = array(
        
            "form" => $form,
            "id_certificate" => Get::req("id_certificate"),
            "id_association" => $id_association,
            "type_assoc" => $type_assoc,

            "tb" => $tb,
            "opsArr" => $this->op,
            
        );
        
        $this->render($this->op['associationUsersCourses'], $params);
    
    }

        function saveAssignment() {
        
        if(isset($_POST["undo_assign"]))
            Util::jump_to('index.php?r=alms/'.$this->controller_name.'/'.$this->op['associationsManagement'].'&id_certificate='.Get::req("id_certificate",DOTY_INT,0));
        
        $id_assoc = Get::req("id_assoc", DOTY_INT, 0);
        $type_assoc = Get::req('type_assoc', DOTY_INT, -1);

        $associationsArr = array();

        if($id_assoc !== 0 ) {
        
            $aclManager = new DoceboACLManager();
            $array_user =& $aclManager->getAllUsersFromIdst($_SESSION['meta_certificate']['userSelectionArr']);

            $array_user = array_unique($array_user);
            
          /*  if(
            $_SESSION['meta_certificate']['courses'] > 0 &&
            $_SESSION['meta_certificate']['courses'][0] != ''
            ) {
            
                $query_course = "INSERT INTO ".$GLOBALS['prefix_lms']."_certificate_meta_course"
                                ." (idMetaCertificate, idUser, idCourse)"
                                ." VALUES ";

                $first = true;
                foreach($array_user as $id_user)
                
                    foreach($_SESSION['meta_certificate']['courses'] as $id_course)
                        if(isset($_POST['_'.$id_user.'_'.$id_course.'_']))
                            if ($first)
                            {
                                $query_course .= "('".$id_assoc."', '".$id_user."', '".$id_course."')";
                                $first = false;
                            }
                            else
                                $query_course .= ", ('".$id_assoc."', '".$id_user."', '".$id_course."')";

                $res = sql_query($query_course);
            }
            
            if( $_SESSION['meta_certificate']['coursepaths'] > 0 &&
                $_SESSION['meta_certificate']['coursepaths'][0] != '' ) {
            
                $query_coursepath = "INSERT INTO ".$GLOBALS['prefix_lms']."_certificate_meta_coursepath "
                                ." (idMetaCertificate, idUser, idCoursePath)"
                                ." VALUES ";
                
                $first = true;
                foreach($array_user as $id_user)
                
                    foreach($_SESSION['meta_certificate']['coursepaths'] as $id_coursepath)
                        if(isset($_POST['_'.$id_user.'_'.$id_coursepath.'_']))
                            if ($first)
                            {
                                $query_coursepath .= "('".$id_assoc."', '".$id_user."', '".$id_coursepath."')";
                                $first = false;
                            }
                            else
                                $query_coursepath .= ", ('".$id_assoc."', '".$id_user."', '".$id_coursepath."')";

                $res = sql_query($query_coursepath);
            }*/

            foreach($array_user as $id_user){

                switch($type_assoc){

                    case COURSE:


                        foreach($_SESSION['meta_certificate']['courses'] as $id_course){
                            if(isset($_POST['_'.$id_user.'_'.$id_course.'_'])) {

                                $associationsArr[$id_assoc][$id_user][] = $id_course;
                            }

                        }

                    break;

                    case COURSE_PATH:


                        foreach($_SESSION['meta_certificate']['coursepaths'] as $id_coursepath){
                            if(isset($_POST['_'.$id_user.'_'.$id_coursepath.'_'])){

                                $associationsArr[$id_assoc][$id_user][] = $id_coursepath;

                            }

                        }


                    break;

                    default:
                        break;

                }

            }


            $res = $this->aggCertLib->insertAssociationLink($type_assoc, $associationsArr);

        }
        Util::jump_to('index.php?r=alms/'.$this->controller_name.'/'.$this->op['associationsManagement'].'&id_certificate='.Get::req("id_certificate", DOTY_INT, 0).'&res='. ($res ? 'ok' : 'err'));
    
        
    }

    function viewdetails() {

        require_once($GLOBALS['where_lms'].'/lib/lib.coursepath.php');
        require_once($GLOBALS['where_lms'].'/lib/lib.course.php');

        $acl_man =& Docebo::user()->getAclManager();
        
        $id_certificate = Get::req('id_certificate', DOTY_INT, 0);
        $id_association = Get::req('id_association', DOTY_INT, 0);


        // Getting type of metacert. (if it's a metacert on course, on coursepath...)
        $type_assoc = Get::req('type_assoc', DOTY_INT, -1);

        $usersArr = $this->aggCertLib->getAllUsersFromIdAssoc($id_association, $type_assoc);
        $linksArr = $this->aggCertLib->getAllLinksFromIdAssoc($id_association, $type_assoc);


        //Table creation
        $tb    = new Table(0, Lang::t("_META_CERTIFICATE_DETAILS_CAPTION",'certificate'), Lang::t("_META_CERTIFICATE_DETAILS_CAPTION",'certificate'));
        $tb->setLink('index.php?r=alms/'.$this->controller_name.'/'.$this->op['viewdetails'].'&amp;id_certificate='.$id_certificate.'&amp;id_association='.$id_association);

        $type_h = array('', '');
        $cont_h = array(Lang::t('_FULLNAME'), Lang::t('_USERNAME'));


        foreach($linksArr as $id_link) {
            
            $type_h[] = 'align_center';

            switch ($type_assoc) {

                case COURSE:


                    $course_man = new Man_Course();

                    $course_info = $course_man->getCourseInfo($id_link);

                    $cont_h[] = $course_info['code'].' - '.$course_info['name'];

                    break;

                case COURSE_PATH:

                    $coursePath_man = new CoursePath_Manager();
                    $coursePathInfoArr = $coursePath_man->getCoursepathAllInfo($linksArr);

                    foreach($coursePathInfoArr as $coursePathInfo) {

                        $cont_h[] = $coursePathInfo[COURSEPATH_CODE] . ' - ' . $coursePathInfo[COURSEPATH_NAME];

                    }

                    break;

                default:
                    break;
            }
        }
                    
        $type_h[] = 'align_center';
        $cont_h[] = Lang::t('_META_CERTIFICATE_PROGRESS','certificate');
        
        $tb->setColsStyle($type_h);
        $tb->addHead($cont_h);
        
        reset($linksArr);

        $aclManager = new DoceboACLManager();
        $usersArr =  array_map('intval', $aclManager->getArrUserST($usersArr));
        
        foreach($usersArr as $id_user) {

            $cont = array();

            $user_info = $acl_man->getUser($id_user, false);

            $cont[] = $user_info[ACL_INFO_LASTNAME] . ' ' . $user_info[ACL_INFO_FIRSTNAME];

            $cont[] = $acl_man->relativeId($user_info[ACL_INFO_USERID]);

            $total_course_assigned = 0;
            $total_course_ended = 0;

            $status = $this->aggCertLib->getUserAndCourseFromIdAssoc($id_association, $type_assoc);

            foreach ($linksArr as $id_link) {

                if (!isset($status[$id_user][$id_link]))
                    $cont[] = Lang::t('_NOT_ASSIGNED');
                else {

                    $total_course_assigned++;

                    switch ($type_assoc) {

                        case COURSE:
                            //  Se è un percorso form. devo prendere tutti i corsi associati ad esso
                            $control = $this->aggCertLib->getCountCoursesCompleted($id_link, $id_user);

                            break;

                        case COURSE_PATH:

                            $cp_m = new CoursePath_Manager();
                            $courseIdsFromPath = $cp_m->getPathCourses($id_link);


                            $man_courseuser = new Man_CourseUser(DbConn::getInstance());
                            $control = $man_courseuser->hasCompletedCourses($id_user, $courseIdsFromPath);


                            break;

                        default;

                            break;
                    }

                    if ($control) {

                        $total_course_ended++;
                        $cont[] = Lang::t('_END', 'course');

                    } else
                        $cont[] = Lang::t('_NOT_ENDED', 'certificate');

                }    
            }

           $cont[] = $total_course_ended . ' / ' . $total_course_assigned; 
           $tb->addBody($cont);

        }

        $params = array(
            
            "controller_name" => $this->controller_name,
            "id_certificate" => $id_certificate,
            "tb" => $tb,
            "opsArr" => $this->op,
            
        );
        
        $this->render($this->op['view_details'], $params);

    }

        function delAssociations(){

            checkPerm('mod');

            $id_association = Get::req('id_association', DOTY_INT, 0);
            $id_certificate = Get::req('id_certificate', DOTY_INT, 0);

            $type_assoc = Get::req('type_assoc', DOTY_INT, -1);


            if(Get::req('confirm', DOTY_INT, 0) == 1 && ($id_association != 0) ) {

                Util::jump_to('index.php?r=alms/'.$this->controller_name.'/'.$this->op['associationsManagement'].'&id_certificate='.$id_certificate.'&res='
                    .( $this->aggCertLib->deleteAssociations( $id_association, $type_assoc ) ? 'ok' : 'err'));

            }
    }
    
    
    function preview() {
              
        checkPerm('view');

        require_once(Forma::inc(_lms_.'/lib/lib.certificate.php'));

        $id_certificate = Get::req('id_certificate', DOTY_INT, 0);


        $cert = new Certificate();
        $cert->send_preview_certificate($id_certificate, array());      
                      
    }
   
    
    /**
    * Assignment Management:
    *   
    *   - Preview certificate
    *   - Release certificate
    *   - Delete released certificate
    * 
    * 
    * 
    */

    function assignmentManagement() {
        
        checkPerm('mod');
        require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
        require_once($GLOBALS['where_lms'].'/lib/lib.coursepath.php');
        require_once(_base_.'/lib/lib.table.php');
        require_once(_base_.'/lib/lib.form.php');
 
        $acl_man =& Docebo::user()->getAclManager(); //necessary to obtain users data

        $tot_element = 0;
 
        $id_cert = Get::req('id_certificate', DOTY_INT, 0);
        if($id_cert == 0) cout(getErrorUi(Lang::t('Id certificate not valid')));; // you'll never know.. TODO: Add error message

        $certificate = $this->aggCertLib->getMetadata($id_cert);

        //Creating table with the users data and cert. release, preview or deleting operations
        $tb = new Table(Get::sett('visuItem'), Lang::t('_META_CERTIFICATE_CREATE_CAPTION','certificate'), Lang::t('_META_CERTIFICATE_CREATE_CAPTION','certificate'));
        $tb->initNavBar('ini', 'button');
        $ini = $tb->getSelectedElement();
 
        /* Steps:
        
            - Obtain all the associations from the cert. (table lms_aggregated_cert_meta)
            - Get all the users in the associations (both from lms_certificate_meta_course and from meta_coursepath)
           
            - Get all the courses belonging to the user in the associations (if coursepath, get all course in coursepath)
            - If the user has completed all the courses, then add in the final array
           
 
        */
 
        $usersCert = array(); // all users to whom the cert can be released 


        // Getting an array of all associations belonging to the cert.
        $assocIdsArr = $this->aggCertLib->getIdAssociations($id_cert);

        if(empty($assocIdsArr)) cout(getErrorUi(Lang::t('There aren\'t any association'))); // TODO: add error message, there aren't any assoc.


        $usersInAssociationArr = array();

        foreach ($this->aggCertLib->assocTypesArr as $type_assoc => $name_tab) { // getting all user ids from all the assoc.
            $tempArrUser = $this->aggCertLib->getAllUsersFromIdAssoc($assocIdsArr, $type_assoc);
            foreach ($tempArrUser as $idUser) {

                if(!in_array($idUser, $usersInAssociationArr, true))
                    array_push($usersInAssociationArr, $idUser);


            }
        }

        if( !empty($usersInAssociationArr) ) {
            
            foreach($usersInAssociationArr as $idUser) {

                $result = false;

                // Getting courses belonging to the user
                //$coursesIdsArr = $this->model->getUserCoursesFromIdsMeta($idUser, $assocIdsArr, COURSE); // Get user's associated course

                $coursesIdsArr = $this->aggCertLib->getAssociationLink($assocIdsArr, COURSE, $idUser); // Get user's associated course

                // If user belongs to some coursepaths, get courses in coursepath
                $coursePathIdsArr = $this->aggCertLib->getAssociationLink($assocIdsArr, COURSE_PATH, $idUser);

                foreach($coursePathIdsArr as $cp_id){

                    $cp_m = new CoursePath_Manager();
                    $courseIdsFromPath = $cp_m->getPathCourses($cp_id);

                    foreach($courseIdsFromPath as $coursefrompath)
                        $coursesIdsArr[] = $coursefrompath;


                }

                $coursesIdsArr = array_unique($coursesIdsArr);

                $man_courseuser = new Man_CourseUser(DbConn::getInstance());
                if (!empty($coursesIdsArr))
                    $result = $man_courseuser->hasCompletedCourses($idUser,$coursesIdsArr);



                if($result)
                    $usersCert[] = $acl_man->getUser($idUser);
            }
               
        }
        
        if(isset($_POST['undo_filter_create'])) {
            unset($_POST['filter_username']);
            unset($_POST['filter_firstname']);
            unset($_POST['filter_lastname']);
            unset($_POST['filter_release_status']);
        }
        
        
        // Setting table and columns header for the table
        $type_h = array('', '', '', 'image', 'image', 'image');

        $cont_h = array(Lang::t('_FULLNAME'),
            Lang::t('_USERNAME'),
            Lang::t('_TITLE'),
            Get::img('standard/view.png', Lang::t('_PREVIEW', 'certificate')),
            Get::img('course/certificate.png', Lang::t('_TAKE_A_COPY', 'certificate')),
            '<img src="' . getPathImage('lms') . 'standard/delete.png" alt="' . Lang::t('_ALT_REM_META_CERT') . ' : ' . strip_tags($certificate["name"]) . '" />');

        $tb->setColsStyle($type_h);
        $tb->addHead($cont_h);

        

        foreach($usersCert as $user){

            // Adding content to table...
            $cont = array();

            $cont[] = $user[ACL_INFO_LASTNAME] . ' ' . $user[ACL_INFO_FIRSTNAME];
            $cont[] = $acl_man->relativeId($user[ACL_INFO_USERID]);
            $cont[] = strip_tags($certificate["name"]);
            $cont[] = '<a href="index.php?r=alms/'.$this->controller_name.'/'.$this->op['preview_cert'].'&amp;id_certificate=' . $id_cert . '&amp;id_user=' . $user[ACL_INFO_IDST] . '">'
                . Get::img('standard/view.png', Lang::t('_PREVIEW', 'certificate') . ' : ' . strip_tags($certificate["name"]) ) . '</a>';
            $cont[] = '<a href="index.php?r=alms/'.$this->controller_name.'/'.$this->op['release_cert']
                . '&amp;id_certificate=' . $id_cert
                . '&amp;id_user=' . $user[ACL_INFO_IDST]
                . '&amp;aggCert=1'
                . '">'
            . Get::img('course/certificate.png', Lang::t('_TAKE_A_COPY', 'certificate') . ' : ' . strip_tags($certificate["name"]) ) . '</a>';

            $aggCertReleasedCount = $this->aggCertLib->hasUserAggCertsReleased($user[ACL_INFO_IDST], $id_cert);

            if ($aggCertReleasedCount > 0)
                $cont[] = '<a href="index.php?r=alms/'.$this->controller_name.'/'.$this->op['del_released'].'&amp;id_certificate=' . $id_cert  . '&amp;id_user=' . $user[ACL_INFO_IDST] . '">'
                . '<img src="' . getPathImage('lms') . 'standard/delete.png" alt="' . Lang::t('_ALT_REM_META_CERT') . ' : ' . strip_tags($certificate["name"]) . '" /></a>';
            else
                $cont[] = '';

            $tb->addBody($cont);

        }

        require_once(_base_.'/lib/lib.dialog.php');
        setupHrefDialogBox('a[href*='.$this->op['del_released'].']');
         
        $array_release_status = array(
            Lang::t('_ALL') => '0',
            Lang::t('_ONLY_RELEASED','certificate') => '1',
            Lang::t('_ONLY_NOT_RELEASED','certificate') => '2'
        );

       //TODO: add formatable with cert to release
        
        
        $params = array(
            "release_status_arr" => $array_release_status,
            "id_certificate" => $id_cert,
            "tb" => $tb,
            "type_h" => $type_h,
            "tot_element" => $tot_element,
            "cont_h" => $cont_h,
            "ini" => $ini,
            
            "opsArr" => $this->op,
            );

        $params["controller_name"] = $this->controller_name;

        $this->render($this->op['assignmentManagement'], $params);
       
 
    }
   
        function preview_cert() {
            
            checkPerm('view');

            require_once(Forma::inc(_lms_.'/lib/lib.certificate.php'));

            $id_certificate =  Get::req('id_certificate', DOTY_INT, 0);
            $id_association = Get::req('id_association', DOTY_INT, 0);

            $id_course =  Get::req('id_course', true, 0);
            $id_user = Get::req('id_user', DOTY_INT, 0);

            $cert = new Certificate();
            $subs = $cert->getSubstitutionArray($id_user, $id_course, $id_association);
            $cert->send_facsimile_certificate($id_certificate, $id_user, $id_course, $subs);
        
        }
       
        function release_cert() {
            checkPerm('view');

            require_once(Forma::inc(_lms_.'/lib/lib.certificate.php'));

            
            $id_certificate =  Get::req('id_certificate', DOTY_INT, 0);
            $id_association = Get::req('id_association', DOTY_INT, 0);

            $id_course =  Get::req('id_course', true, 0);
            $id_user = Get::req('id_user', DOTY_INT, 0);


            $cert = new Certificate();
            $subs = $cert->getSubstitutionArray($id_user, $id_course, $id_association);
            $rs = $cert->send_certificate($id_certificate, $id_user, $id_course, $subs);
            
            if($rs)
                Util::jump_to('index.php?r=alms/'.$this->controller_name.'/'.$this->op['assignmentManagement']
                .'&amp;id_certificate='.$id_certificate);

        }
       
        function delReleased() {
            
            checkPerm('mod');

            require_once(_base_.'/lib/lib.form.php');
            require_once(_base_.'/lib/lib.upload.php');
            
            $id_certificate =  Get::req('id_certificate', DOTY_INT, 0);
            $id_association = Get::req('id_association', DOTY_INT, 0);
            $id_user = Get::req('id_user', DOTY_INT, 0);

            if(Get::req('confirm', DOTY_INT, 0) == 1) {

               $cert_file = $this->aggCertLib->getAggregatedCertFileName($id_user,$id_certificate);

               $path = '/appLms/certificate/';

               sl_open_fileoperations();
               $res = sl_unlink($path.$cert_file);
               sl_close_fileoperations();

               if(!$res)
                    Util::jump_to('index.php?r=alms/'.$this->controller_name.'/'.$this->op['assignmentManagement'].'&id_certificate='.$id_certificate.'&result=err_del_cert');

               $res = $this->aggCertLib->deleteReleasedCert($id_user, $id_certificate);
               
               Util::jump_to('index.php?r=alms/'.$this->controller_name.'/'.$this->op['assignmentManagement'].'&id_certificate='.$id_certificate.'&result='. ($res ? 'ok' : 'err_del_cert'));

            }
        }
     
    /**
    * Delete a cert. means that we have to delete:
    *   
    *   - The cert. in the table_cert
    *   - All the associations (objects) of the certificate to the users (table certificate_meta)
    *   - All the associations (link between users and courses) (table certificate_meta_association_course or meta_association_coursepath)
    *   - All the assignment (table cert.-assign)
    * 
    */
    function delcertificate() {

        checkPerm('mod');
        $id_cert = Get::req('id_certificate', DOTY_INT);

        if(Get::req('confirm', DOTY_INT, 0) == 1) {
            
            if($this->aggCertLib->deleteCert($id_cert)) {

                // Get all the associations with the cert.
                $idsAssocArr = $this->aggCertLib->getIdAssociations($id_cert);

                if( !empty($idsAssocArr) ) { // Cert. has some associations METADATA

                    $res = $this->aggCertLib->deleteAssociations($idsAssocArr);
                    Util::jump_to('index.php?r=alms/'.$this->controller_name.'/'.$this->op['home'].'&result=' . ($res ? 'ok' : 'err') );

                }  else   // There aren't any associations
                        Util::jump_to('index.php?r=alms/'.$this->controller_name.'/'.$this->op['home'].'&result=ok');

            } else  Util::jump_to('index.php?r=alms/'.$this->controller_name.'/'.$this->op['home'].'&result=err');
        }
    }  
                
    // Other operations  
       
    /**
    * Load background image into folders correctly, or delete
    *    
    * @param mixed $new_file_id
    * @param mixed $old_file
    * @param mixed $path
    * @param mixed $delete_old
    * @param mixed $is_image
    * @return mixed
    */
    function manageCertificateFile($new_file_id, $old_file, $path, $delete_old, $is_image = false) {
        require_once(_base_.'/lib/lib.upload.php');
        $arr_new_file = ( isset($_FILES[$new_file_id]) && $_FILES[$new_file_id]['tmp_name'] != '' ? $_FILES[$new_file_id] : false );
        $return = array(    'filename' => $old_file,
            'new_size' => 0,
            'old_size' => 0,
            'error' => false,
            'quota_exceeded' => false);
        sl_open_fileoperations();
        if(($delete_old || $arr_new_file !== false) && $old_file != '') {

            // the flag for file delete is checked or a new file was uploaded ---------------------
            sl_unlink($path.$old_file);
        }

        if(!empty($arr_new_file)) {

            // if present load the new file --------------------------------------------------------
            $filename = $new_file_id.'_'.mt_rand(0, 100).'_'.time().'_'.$arr_new_file['name'];

            if(!sl_upload($arr_new_file['tmp_name'], $path.$filename)) {

                return false;
            }
            else return $filename;
        }
        sl_close_fileoperations();
        return '';
    }

    /**
     * Create a category tree with arrays. All nodes are retrieved from query with idParent
     *
     * The array has params needed for the bootstrap-treeview.
     *
     * @param int $idParent Needed for query to db.
     * @return array $nodesArr
     */
    function getTreeCategoryAsArray($idParent = 0 ) {

        // if courses have no parent category (like all the courses are under root), they will not be found!
       
        $nodesArr = $this->model->getPathsFromIdParent($idParent); // getting nodes with idParent

        if (count($nodesArr) > 0) {

            $node = 0;
            while ($node < count($nodesArr)) { // Processing all nodes with idParent

                $nodesArr[$node]['text'] = end(explode('/', $nodesArr[$node]['text']));
                //$nodesArr[$node]['backColor'] = '#000000';
/*                $nodesArr[$node]['selectable'] = 'false';*/
/*                $nodesArr[$node]["selectedBackColor"] = "red";*/
           /*     $nodesArr[$node]["highlightSelected"] = "false";
                $nodesArr[$node]["highlightSearchResults"] = "false";*/


                if (!$nodesArr[$node]['isLeaf'])
                    $nodesArr[$node]['nodes'] = $this->getTreeCategoryAsArray($nodesArr[$node]['idCategory']);

                $node++;

            }

            return $nodesArr;

        }
    }

    function getUsersWithCourseCompleted(){

        $usersWithCourseCompletedArr = array();

        $arrUsersCourseCompleted = $this->model->getUsersCourseCompleted();
        foreach($arrUsersCourseCompleted as $key => $value){
            $usersWithCourseCompletedArr[$value["idUser"]][$value["idCourse"]] = $value["idCourse"];
        }

        return $usersWithCourseCompletedArr;
    }

    function getAssociationsTitleArr(){
        $assocTitleArr = array();
        $tempassocTitleArr = $this->model->getTitleAssociationsArr();
        foreach($tempassocTitleArr as $key => $val){
            $assocTitleArr[$val['idMetaCertificate']] = $val['title'];
        }

        return $assocTitleArr;
    }

    function getCountMetaCertsArr(){

        $controlArr = array();

        $tempArrCount = $this->model->getCountMetaCertUsers();
        foreach($tempArrCount as $k => $v){
            $controlArr[$v['idUser']][$v['idMetaCertificate']] = $v['COUNT(*)'];
            }

        return $controlArr;

    }
    
    
    // ------------------ Ajax calls for datatable -------------------
    
    /**
     * Ajax call from view
     */
    function getCourseListTask() {

        if (isset($_POST["nodesArr"])) {

            echo $this->json->encode($this->aggCertLib->getCourseListFromIdCat($_POST["nodesArr"]));

        }

    }

    /**
     * Ajax call from view
     */
    function getCoursePathListTask() {

        echo $this->json->encode($this->aggCertLib->getCoursePathList());
      /*  require_once($GLOBALS['where_lms'].'/lib/lib.coursepath.php');

        $coursepathMan = new CoursePath_Manager();
        echo $this->json->encode($coursepathMan->getCoursepathList());
*/

    } 
    
    /**
     * Ajax call from view
     */
    function getCatalogCourseListTask() {

        echo $this->json->encode($this->model->getCatalogCourse());

    }

}    