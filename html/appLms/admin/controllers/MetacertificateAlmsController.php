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
           
define('COURSE', 0);
define('COURSE_PATH', 1);

           
/**
 * Class MetacertificateAlmsController
 */
Class MetacertificateAlmsController extends AlmsController
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
        
        'create' => 'create',
        
            'preview_cert' => 'preview_cert',
            
            'release_cert' => 'release_cert',
            
            'del_released' => 'del_released',
 
        'assignment' => 'assignment',
        
            'association' => 'association',
                
                'del_association' => 'delAssociations',
                'saveMetadataAssoc' => 'saveMetadataAssoc',
                
                'associationusers' => 'associationUsers',
                'associationCourses' => 'associationCourses',
                
                
                'assignmentUsersCourses' => 'assignmentUsersCourses',
                
                'saveAssignment' => 'saveAssignment',  
                'saveAssignmentUsers' => 'saveAssignmentUsers',
                
            'view_details' => 'viewdetails',
         
        'delmetacert' => 'delcertificate',
                   
    );
    

    
	public function init()
	{
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
             'view'			=> checkPerm('view', true, 'course', 'lms'),
             'add'			=> checkPerm('add', true, 'course', 'lms'),
             'mod'			=> checkPerm('mod', true, 'course', 'lms'),
             'del'			=> checkPerm('del', true, 'course', 'lms'),
             'moderate'		=> checkPerm('moderate', true, 'course', 'lms'),
             'subscribe'		=> checkPerm('subscribe', true, 'course', 'lms'),
             'add_category'	=> checkPerm('add', true, 'coursecategory', 'lms'),
             'mod_category'	=> checkPerm('mod', true, 'coursecategory', 'lms'),
             'del_category'	=> checkPerm('del', true, 'coursecategory', 'lms'),
             'view_cert'		=> checkPerm('view', true, 'certificate', 'lms'),
             'mod_cert'		=> checkPerm('mod', true, 'certificate', 'lms')
         );

        $_SESSION['meta_certificate'] = array(); // testare inizializzazione variabile di sessione.


         */


        require_once(_base_.'/lib/lib.json.php');
        $this->json = new Services_JSON();

        $this->controller_name = strtolower(str_replace('AlmsController','',get_class($this)));
        $this->model = new MetacertificateAlms();
	}

   
    // ---------- Certificate management ------------
    
     /** Default op.
    * 
    * Metacertificate administration panel
    * 
    * Action: show
    *  
    */ 
    public function show() {

        checkPerm('view');

        $tb    = new Table(Get::sett('visuItem'), Lang::t('_META_CERTIFICATE_CAPTION','certificate'), Lang::t('_META_CERTIFICATE_SUMMARY','certificate'));
        $tb->initNavBar('ini', 'link');
        $ini = $tb->getSelectedElement();


        if (isset($_POST['toggle_filter']))
                unset($_POST['filter_text']);

        

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
        
            $cont_h[] =    Get::img('standard/moduser.png', Lang::t('_TITLE_ASSIGN_META_CERTIFICATE', 'certificate'));
            $type_h[] =    'image';

            $cont_h[] =    Get::sprite('subs_print', Lang::t('_TITLE_CREATE_META_CERTIFICATE', 'certificate'));
            $type_h[] =    'image';

            $cont_h[] =    Get::img('standard/edit.png', Lang::t('_MOD'), Lang::t('_MOD'));
            $type_h[] =    'image';

            $cont_h[] =  Get::img('standard/delete.png', Lang::t('_DEL'), Lang::t('_DEL'));
            $type_h[] =    'image';
            
        }

        $tb->setColsStyle($type_h);
        $tb->addHead($cont_h);           


        // Array of all metacertificates to display in the main admin panel
        $metacerts = $this->model->getAllmetacerts($ini);
        
        foreach ($metacerts as $key => $value) {
            $title = strip_tags($value["name"]);

            $cont = array(
                $value["code"],     
                $value["name"],     
                Util::cut($value["description"])
            );


        if($userCanModify)
                $cont[] = '<a href="index.php?r=alms/'.$this->controller_name.'/'.$this->op['layout'].'&amp;id_certificate='.$value["id_certificate"].'&amp;edit=1" '
                .'title="'.Lang::t('_TEMPLATE', 'certificate').'">'
                .Lang::t('_TEMPLATE', 'certificate').'</a>';


                $cont[] = Get::sprite_link(                           
                    'subs_view',
                    'index.php?r=alms/'.$this->controller_name.'/'.$this->op['preview'].'&amp;id_certificate='.$value["id_certificate"],
                    Lang::t('_PREVIEW')
                ); 
                
        if($userCanModify) {
                
                $cont[] = Get::sprite_link(    
                    'subs_admin',
                    'index.php?r=alms/'.$this->controller_name.'/'.$this->op['assignment'].'&amp;id_certificate='.$value["id_certificate"],
                     Lang::t('_TITLE_ASSIGN_META_CERTIFICATE', 'certificate')        
                    );
                    
                $cont[] = Get::sprite_link(
                    'subs_print',
                    'index.php?r=alms/'.$this->controller_name.'/'.$this->op['create'].'&amp;id_certificate='.$value["id_certificate"],
                    Lang::t('_TITLE_CREATE_META_CERTIFICATE', 'certificate')
                    );

                $cont[] = Get::sprite_link(
                    'subs_mod',
                    'index.php?r=alms/'.$this->controller_name.'/'.$this->op['metadata'].'&amp;id_certificate='.$value["id_certificate"],
                    Lang::t('_MOD') . ' : ' . $title  
                    ); 
                       
                $cont[] = Get::sprite_link(
                    'subs_del',
                    'index.php?r=alms/'.$this->controller_name.'/'.$this->op['delmetacert'].'&amp;id_certificate='.$value["id_certificate"],
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
                    title="'.Lang::t('_NEW_METACERTIFICATE','metacertificate').'">
                    <span>'.Lang::t('_NEW_METACERTIFICATE','metacertificate').'</span>
               </a>
           ');  
            
        }
        
        $params = array(
        
            "tb" => $tb,
            "countMetacert" => $this->model->getCountMetacertificates($ini),
            "ini" => $ini,
        );

        $this->render($this->op['home'], $params);

    }

    
    
    public function metadata() {
        
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
            
            $metaCert = $this->model->getMetadata($id_cert);
            $params['metacert'] = $metaCert;
            $params['id_certificate'] = $id_cert;
            
        }
        
        $params['page_title'] = $page_title;
        $params['controller_name'] = $this->controller_name;
        $params['opArr'] = $this->op;
        
        $params['languages'] = $languages;
        $params['form'] = $form;

        
        $this->render($this->op['metadata'], $params);
    }
    
    public function saveMetaData() {
        
        checkPerm('mod');
       
        if(isset($_POST["undo"]))
            Util::jump_to('index.php?r=alms/'.$this->controller_name.'/'.$this->op['home']);
        
        $isModifyingMetadata = isset($_POST['id_certificate']); 
        
        $metaDataArr = array(
            
            "code" => $_POST["code"],
            "name" => $_POST["name"] == '' ? Lang::t('_NOTITLE') : $_POST["name"],
            "base_language" => $_POST["base_language"],
            "descr" => $_POST["descr"],
            "user_release" => $_POST["user_release"],

        );
        
        if ($isModifyingMetadata)
            $metaDataArr['id_certificate'] = Get::req("id_certificate");
        
        $res = $this->model->insertMetaData($metaDataArr);
         
        if($isModifyingMetadata)
            Util::jump_to('index.php?r=alms/'.$this->controller_name.'/'.$this->op['home'].'&result='. ($res ? 'ok' : 'err'));
        else
            Util::jump_to('index.php?r=alms/'.$this->controller_name.'/'.$this->op['layout'].'&id_certificate='.$this->model->getLastInsertedIdCertificate());
                   
    }
    
        
    public function layout() {
        
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
            $template = $this->model->getLayoutMetacert($id_certificate);
         
        $certificate_tags = $this->model->getCertificateTags();
          
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
    
     public function saveLayout() {
        
        checkPerm('mod');
        if(isset($_POST["undo"]))
            Util::jump_to('index.php?r=alms/'.$this->controller_name.'/'.$this->op['home']);
        
        
         if(isset($_POST['structure_certificate']))
        {
            $path     = '/appLms/certificate/';
            $path     = $path.( substr($path, -1) != '/' && substr($path, -1) != '\\' ? '/' : '');

            isset($_POST['file_to_del']['bgimage']) ? $flagDeleteOldImage = $_POST['file_to_del']['bgimage'] : null;
    //TODO: Con la spunta attiva, viene cancellato il vecchio file. Automaticamente, se l'utente vuole caricare un nuovo file, deve poterlo fare contemporaneamente.
            $bgimage = $this->manageCertificateFile(    'bgimage',
                $_POST["old_bgimage"],
                $path,
                $flagDeleteOldImage); // Vedere cosa è, nel caso refactor

            if(!$bgimage)
                $bgimage = '';
        }
        
        $layoutArr = array(
            
            "structure" => $_POST["structure"],
            "orientation" => $_POST["orientation"],
            "bgimage" => $bgimage,
            "id_certificate" => $_POST['id_certificate'],
            
        );
                
        $res = $this->model->updateLayout($layoutArr);
         
        Util::jump_to('index.php?r=alms/'.$this->controller_name.'/'.$this->op['home'].'&result='. ($res ? 'ok' : 'err'));
                   
    }
 
         
    public function delcertificate() {

        checkPerm('mod');
        $id_cert = Get::req('id_certificate');

        if(Get::req('confirm', DOTY_INT, 0) == 1) {
            
            if($this->model->deleteCert($id_cert)) {

                $idsMetaArr = $this->model->getIdsMetaCerts($id_cert);

                if($idsMetaArr != '') // Cert. has some associations  
                    Util::jump_to('index.php?r=alms/'.$this->controller_name.'/'.$this->op['home'].'&result=' . ($this->model->deleteMetaCertDatas($idsMetaArr) ? 'ok' : 'err'));
                else   // There aren't any associations
                    Util::jump_to('index.php?r=alms/'.$this->controller_name.'/'.$this->op['home'].'&result=ok');

            } else  Util::jump_to('index.php?r=alms/'.$this->controller_name.'/'.$this->op['home'].'&result=err');
        }
    }
      
    
 
     // ------------- Metacertificate management ------------------
    
    /**
    * Assignment / metacertificate management
    * 
    * In this action, will be rendered all the tools for managing 
    * association between a certificate with the users and the courses
    * 
    * 
    */
    public function assignment() {
       
        checkPerm('mod');
        require_once(_base_.'/lib/lib.table.php');

        $id_certificate = Get::req('id_certificate',DOTY_INT,0);

        // Creating table...
        $tb = new Table(Get::sett('visuItem'), Lang::t('_META_CERTIFICATE_ASSIGN_CAPTION','certificate'), Lang::t('_META_CERTIFICATE_ASSIGN_CAPTION','certificate'));
        $tb->initNavBar('ini', 'link');
        $tb->setLink('index.php?r=alms/'.$this->controller_name.'/'.$this->op['assignment']);
        $ini = $tb->getSelectedElement();

        // Getting all metacerts belonging to the certificate
        $metacertArr = $this->model->getMetacertificates($id_certificate);

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

        
        
        foreach ($metacertArr as $key => $value) {

            // Getting type of metacert. (if it's a metacert on course, on coursepath...)
            $type_metacert = $this->model->getTypeMetacert( (int) $value["idMetaCertificate"] );
            
            $rows = array();
            
            $rows[] = stripslashes($value["title"]);
            $rows[] = stripslashes($value["description"]);
            $rows[] = Get::sprite_link( 
                        'subs_view',
                        'index.php?r=alms/'.$this->controller_name.'/'.$this->op['view_details'].'&amp;id_metacertificate='.$value["idMetaCertificate"].'&amp;id_certificate='.$id_certificate,
                        Lang::t( '_DETAILS' )  
                    );
            // Depending on the type of the course
            $rows[] = ($type_metacert == COURSE) ? 
                    '<a href="index.php?r=alms/'.$this->controller_name.'/'.$this->op["associationusers"]
                        .'&amp;id_certificate='.$id_certificate
                        .'&amp;id_metacertificate='.$value["idMetaCertificate"]
                        .'&amp;type_course='. COURSE
                        .'&amp;edit=1'
                        .'">
                        <img src="'.getPathImage().'standard/course.png"
                             alt="'.Lang::t( '_COURSE' ).'" 
                             title="'.Lang::t( '_COURSE' ).'" />
                    </a>' : '<img src="'.getPathImage().'standard/course.png"
                                  alt="'.Lang::t( '_COURSE' ).'" 
                                  title="'.Lang::t( '_COURSE' ).'" />'; 
            
            $rows[] = ($type_metacert == COURSE_PATH) ?  
                    '<a href="index.php?r=alms/'.$this->controller_name.'/'.$this->op["associationusers"]
                        .'&amp;id_certificate='.$id_certificate
                        .'&amp;id_metacertificate='.$value["idMetaCertificate"]
                        .'&amp;type_course='. COURSE_PATH
                        .'&amp;edit=1'
                        .'">
                        <img 
                            src="'.getPathImage().'standard/coursepath.png" 
                            alt="'.Lang::t( '_COURSEPATH' ).'" 
                            title="'.Lang::t( '_COURSEPATH' ).'" />
                    </a>' :  
                        '<img 
                            src="'.getPathImage().'standard/coursepath.png" 
                            alt="'.Lang::t( '_COURSEPATH' ).'" 
                            title="'.Lang::t( '_COURSEPATH' ).'" />';
            
            
            $rows[] = Get::sprite_link( 
                        'subs_mod',
                        'index.php?r=alms/'.$this->controller_name.'/'.$this->op['association']
                        .'&amp;id_metacertificate='.$value["idMetaCertificate"]
                        .'&amp;id_certificate='.$id_certificate
                        .'&amp;type_course=' . $type_metacert
                        .'&amp;edit=1',
                        Lang::t('_MOD')
                    );
                   
            $rows[] = Get::sprite_link( 
                        'subs_del',
                        'index.php?r=alms/'.$this->controller_name.'/'.$this->op['del_association'].'&amp;id_metacertificate='.$value["idMetaCertificate"].'&amp;id_certificate='.$id_certificate,
                        Lang::t('_DEL')
                    ); 

            
            $tb->addBody( $rows );

        }

        require_once(_base_.'/lib/lib.dialog.php');
        setupHrefDialogBox('a[href*=delassignmetacertificate]');

        $tb->addActionAdd(    
            '<a class="new_element_link" 
            href="index.php?r=alms/'.$this->controller_name.'/'.$this->op['association'].'&amp;id_certificate='.$id_certificate.'"
            title="'.Lang::t('_NEW_ASSIGN_META_CERTIFICATE', 'metacertificate').'">'
            .Lang::t('_NEW_ASSIGN_META_CERTIFICATE', 'metacertificate')
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
            "arrOps" => $this->op,
            "arr_metacertificate" => $metacertArr,
            "controller_name" => $this->controller_name,
            "tb" => $tb,
        );
        
        $this->render( $this->op['assignment'], $params);
        
    }
  
  
    public function association() {

        $id_certificate = Get::req('id_certificate');
        
        // If the certificate has no metacertificate, the id_metacert will be 0
        $id_metacertificate = Get::req('id_metacertificate',DOTY_INT, 0);
        
        // If the user is modifying the metadata of the assoc. edit will be 1.
        $edit = Get::req('edit', DOTY_INT, 0);
       
        $params = array();
        
        
        $params['html_before_select'] = ''; 
        
        if($id_metacertificate !== 0) { // Metacert already found
          
            if ($edit) { // If i'm editing a metacertAssoc, i need to get all datas.
               
                $associationMetadataArr = $this->model->getAssociationMetadata($id_metacertificate);
                $params['associationMetadataArr'] = $associationMetadataArr;
                $params['html_before_select'] = 'disabled';
                $params['edit'] = $edit;
                
           }
            
        } 
             
        $assoc_types = array(
            COURSE => Lang::t('_COURSE'),
            COURSE_PATH => Lang::t('_COURSEPATH'),
        );
        
        
        $params['id_certificate'] = $id_certificate;
        $params['id_metacertificate'] = $id_metacertificate;
        $params['assoc_types'] = $assoc_types;

        $params["controller_name"] = $this->controller_name;
        $params["arrOps"] = $this->op;

	    $this->render('association', $params);
    }

    public function saveMetadataAssoc(){
           
        checkPerm('mod');
       
        if(isset($_POST["undo_assign"]))
            Util::jump_to('index.php?r=alms/'.$this->controller_name.'/'.$this->op['assignment'].'&id_certificate='.Get::req("id_certificate",DOTY_INT,0));
        
        $isModifyingMetadataAssoc = Get::req("edit", DOTY_INT, 0);
        
        
        $metadataAssocArr = array(
        
            "id_certificate" => $_POST["id_certificate"],
            "title" => $_POST["title"],
            "description" => $_POST["description"],
        
        );
        
        if($isModifyingMetadataAssoc)             
            $metadataAssocArr["id_metacertificate"] = $_POST['id_metacertificate'];
        else {
             $type_course = Get::req('type_course', DOTY_INT, -1);
            
        }
        
        $res = $this->model->insertMetadataAssoc($metadataAssocArr);
         
        if($isModifyingMetadataAssoc !== 0)
            Util::jump_to('index.php?r=alms/'.$this->controller_name.'/'.$this->op['assignment'].'&result='. ($res ? 'ok' : 'err'));
        else
            Util::jump_to(  'index.php?r=alms/'.$this->controller_name.'/'.$this->op['associationusers']
                            .'&amp;id_certificate='.$_POST["id_certificate"]
                            .'&amp;id_metacertificate='.$this->model->getLastInsertedIdCertificateMeta()
                            .'&amp;type_course='.$type_course);
             
    }
    
    public function associationUsers() {

        require_once(_base_.'/lib/lib.userselector.php');
        require_once(_base_.'/lib/lib.form.php');
        
        $id_certificate = Get::req('id_certificate', DOTY_INT, -1);
        $id_metacertificate = Get::req('id_metacertificate', DOTY_INT, 0);
      
        $type_course = Get::req('type_course', DOTY_INT, -1);
        
        if($type_course == -1) return; // TODO: Add error message
       
        $user_selection = new UserSelector();

        $edit = Get::req('edit', DOTY_INT, 0);
        if($edit == 1 && $type_course != -1) {
            $usersArr = $this->model->getUsersBelongsMeta($id_metacertificate, $type_course);
            $user_selection->resetSelection($usersArr);
            $user_selection->addFormInfo('<input type="hidden" name="old_users" value=' . json_encode($usersArr) . ' />');

        }
        
        $user_selection->show_orgchart_simple_selector = FALSE;
        $user_selection->multi_choice = TRUE;

        $user_selection->setPageTitle(getTitleArea(Lang::t('_TITLE_META_CERTIFICATE_ASSIGN','certificate'), 'certificate'));
        
        $user_selection->addFormInfo('<input type="hidden" name="id_certificate" value=' .$id_certificate . ' />');
        $user_selection->addFormInfo('<input type="hidden" name="id_metacertificate" value=' . $id_metacertificate . ' />');
        $user_selection->addFormInfo('<input type="hidden" name="type_course" value=' . $type_course . ' />');
        $user_selection->addFormInfo('<input type="hidden" name="edit" value=' . $edit . ' />');

        // Modifying association certificate <-> users
       
        $params = array(
            "user_selection" => $user_selection,
            
            "type_course" => $type_course,            
            "id_certificate" => $id_certificate,
            "id_metacertificate" => $id_metacertificate,
            "opsArr" => $this->op,
            "controller_name" => $this->controller_name,
        );
        
        $this->render($this->op['associationusers'], $params);
    }


    public function associationCourses() {

        require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
        require_once($GLOBALS['where_lms'].'/lib/lib.course_managment.php');

        if(isset($_POST['cancelselector']))
            Util::jump_to('index.php?r=alms/'.$this->controller_name.'/'.$this->op['assignment'].'&amp;id_certificate='.Get::req('id_certificate', DOTY_INT, 0));
        
        $user_selection = new UserSelector();
        $userSelectionArr = $user_selection->getSelection($_POST);
        
        $_SESSION['meta_certificate']['userSelectionArr'] = $userSelectionArr;
        
        $course_manager = new Course_Manager();
        $course_manager->setLink('index.php?r=alms/'.$this->controller_name.'/'.$this->op['associationCourses']);         

        $treeCat = $this->getTreeCategoryAsArray();
        
        $params = array(

            "course_manager" => $course_manager,
            "treeCat" => $treeCat,
            
            "id_certificate" => Get::req("id_certificate"),
            "id_metacertificate" => Get::req("id_metacertificate"),
            
            "opsArr" => $this->op,
            
            "controller_name" => $this->controller_name,

        );
        
        $id_meta = Get::req('id_metacertificate', DOTY_INT, 0);
        
        $type_course = Get::req('type_course', DOTY_INT, -1);
        
        $coursesIdsArr = $this->model->getIdsCourse($id_meta);
        
        $edit = Get::req('edit', DOTY_INT, 0);
        if($edit == 1 && $type_course != -1) {
            
           // $usersArr = $this->model->getUsersBelongsMeta($id_meta, $type_course);
            $params['edit'] = $edit;
            $params['coursesArr'] = $this->model->getCoursesArrFromId(json_encode($coursesIdsArr));
            
            
            $old_users = explode(",",  str_replace(array( "[", "]" ) , "" , Get::req('old_users'))); 
            $to_delete_users =  array_diff($old_users, $userSelectionArr);
            if(count($to_delete_users))
                $this->model->deleteAssociationsUsers($to_delete_users, $id_meta);
        }
        
        if($type_course != -1){
            
            switch($type_course) {
                
                case COURSE:
                    $params['idsCourses'] = ($id_meta !== 0) ? $coursesIdsArr : '';
                    break;
                case COURSE_PATH:
                    $params['idsCoursePath'] = ($id_meta !== 0) ? $this->model->getIdsCoursePath($id_meta) : '';
                    break;
                default:
                    break;
            }
                 
        }
       
       $this->render( $this->op['associationCourses'], $params);
    }
    
    function assignmentUsersCourses() {
       
        // Loading necessary libraries
        require_once(_base_.'/lib/lib.userselector.php');
        require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
        require_once($GLOBALS['where_lms'].'/lib/lib.coursepath.php');
        require_once($GLOBALS['where_lms'].'/lib/lib.course_managment.php');

        YuiLib::load();
        Util::get_js(Get::rel_path('base').'/lib/js_utils.js', true, true);
        
        $id_metacert = Get::req("id_metacertificate", DOTY_INT);
        $id_certificate = Get::req("id_certificate", DOTY_INT);
        
        $to_delete_courses = explode(",", Get::req("oldestCourses"));
        $new_courses = explode(",", Get::req("idsCourse"));
        
        $todeleteArr = array_diff($to_delete_courses, $new_courses);
        
        if(count($todeleteArr)) {
            $this->model->deleteAssociationsCourses($todeleteArr, $id_metacert); 
        }
        
        if(isset($_POST["undo"]) || isset($_POST["undo_filter"]))
            Util::jump_to('index.php?r=alms/'.$this->controller_name.'/'.$this->op['assignment'].'&amp;id_certificate='.$id_certificate);
        
        $form = new Form();
        $course_man = new Man_Course();

        
        
        unset($_SESSION['meta_certificate']['courses']);
        unset($_SESSION['meta_certificate']['coursepaths']);
        
        $tb    = new Table(0, Lang::t('_META_CERTIFICATE_NEW_ASSIGN_CAPTION','certificate'), Lang::t('_META_CERTIFICATE_NEW_ASSIGN_SUMMARY'));
        $tb->setLink('index.php?r=alms/'.$this->controller_name.'/'.$this->op['home']);

        $form_name = 'new_assign_step_3';

        $type_h = array('', '');
        $cont_h = array(Lang::t('_FULLNAME'), Lang::t('_USERNAME'));

        $_SESSION['meta_certificate']['courses'] = $new_courses;
        $_SESSION['meta_certificate']['coursepaths'] = explode(",", Get::req("idsCoursePath"));
        
        if( 
            $_SESSION['meta_certificate']['coursepaths'] > 0 &&
            $_SESSION['meta_certificate']['coursepaths'][0] != ''
            
            ) {
           
           $coursePath_man = new CoursePath_Manager(); 
           $coursePathInfoArr = $coursePath_man->getCoursepathAllInfo($_SESSION['meta_certificate']['coursepaths']);
           
           foreach($coursePathInfoArr as $coursePathInfo) {
            
               $type_h[] = 'align_center';
               $cont_h[] = $coursePathInfo[COURSEPATH_CODE].' - '.$coursePathInfo[COURSEPATH_NAME];
            
           }
        }
        
        if(
            $_SESSION['meta_certificate']['courses'] > 0 &&
            $_SESSION['meta_certificate']['courses'][0] != ''
            ) {
        
             // $_SESSION['meta_certificate']['course'] = explode(",", Get::req("idscourses"));
            foreach($_SESSION['meta_certificate']['courses'] as $id_course) {
                
                $type_h[] = 'align_center';

                $course_info = $course_man->getCourseInfo($id_course);

                $cont_h[] = $course_info['code'].' - '.$course_info['name'];
                
            }
        }
        
        $type_h[] = 'image';
        $cont_h[] = Lang::t('_SELECT_ALL');

        $type_h[] = 'image';
        $cont_h[] = Lang::t('_UNSELECT_ALL');

        $tb->setColsStyle($type_h);
        $tb->addHead($cont_h);

        reset($_SESSION['meta_certificate']['courses']);
        reset($_SESSION['meta_certificate']['coursepaths']);
        
        $acl_man =& Docebo::user()->getAclManager();
        $aclManager = new DoceboACLManager();
       
        $array_user =& $aclManager->getAllUsersFromIdst($_SESSION['meta_certificate']['userSelectionArr']);
        $array_user = array_unique($array_user);



        $array_user = $this->model->getIdStFromidUser($array_user);

        foreach($array_user as $id_user)
        {
            $cont = array();

            $user_info = $acl_man->getUser($id_user, false);

            $cont[] = $user_info[ACL_INFO_LASTNAME].' '.$user_info[ACL_INFO_FIRSTNAME];

            $cont[] = $acl_man->relativeId($user_info[ACL_INFO_USERID]);

             
            if( $_SESSION['meta_certificate']['courses'] > 0 &&
                $_SESSION['meta_certificate']['courses'][0] != '' ) {
                  //Prechecking checkboxes on load
                foreach($_SESSION['meta_certificate']['courses'] as $id_course)
                {
                    if(
                       // isset($_POST['_'.$id_user.'_'.$id_course.'_']
                        $this->model->userBelongCourseMeta($id_metacert, $id_user,$id_course)
                        ||
                        isset($_POST['select_all'])
                    )
                        $checked = true;
                    else
                        $checked = false;

                    $cont[] = $form->getCheckbox('', '_'.$id_user.'_'.$id_course.'_', '_'.$id_user.'_'.$id_course.'_', 1, $checked);
                }
            }
            
            if( $_SESSION['meta_certificate']['coursepaths'] > 0 &&
                $_SESSION['meta_certificate']['coursepaths'][0] != '') {
                    
                //Prechecking checkboxes on load
                foreach($coursePathInfoArr as $coursePathInfo) {
                    if(
                       // isset($_POST['_'.$id_user.'_'.$id_course.'_']
                        $this->model->userBelongCoursePathMeta($id_metacert, $id_user, $coursePathInfo[COURSEPATH_ID])
                        ||
                        isset($_POST['select_all'])
                    )
                        $checked = true;
                    else
                        $checked = false;

                    $cont[] = $form->getCheckbox('', '_'.$id_user.'_'.$coursePathInfo[COURSEPATH_ID].'_', '_'.$id_user.'_'.$coursePathInfo[COURSEPATH_ID].'_', 1, $checked);
                }   
                    
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

        
          if( $_SESSION['meta_certificate']['courses'] > 0 &&
                $_SESSION['meta_certificate']['courses'][0] != '') {
            foreach($_SESSION['meta_certificate']['courses'] as $id_course) {
                
                $cont[] =   '<a href="javascript:;" onclick="checkall_meta(\''.$form_name.'\', \''.$id_course.'\', true); return false;">'
                            .Lang::t('_SELECT_ALL')
                            .'</a><br/>'
                            .'<a href="javascript:;" onclick="checkall_meta(\''.$form_name.'\', \''.$id_course.'\', false); return false;">'
                            .Lang::t('_UNSELECT_ALL')
                            .'</a>';
            
            }    
        }    
           if( $_SESSION['meta_certificate']['coursepaths'] > 0 &&
                $_SESSION['meta_certificate']['coursepaths'][0] != '') {
            foreach($_SESSION['meta_certificate']['coursepaths'] as $id_coursepath) {
                
                $cont[] =   '<a href="javascript:;" onclick="checkall_meta(\''.$form_name.'\', \''.$id_coursepath.'\', true); return false;">'
                            .Lang::t('_SELECT_ALL')
                            .'</a><br/>'
                            .'<a href="javascript:;" onclick="checkall_meta(\''.$form_name.'\', \''.$id_coursepath.'\', false); return false;">'
                            .Lang::t('_UNSELECT_ALL')
                            .'</a>';
            
            }    
        }
       

        $cont[] = '';
        $cont[] = '';

        $tb->addBody($cont);
        
                    
        $params = array(
        
            "form" => $form,
            "id_certificate" => Get::req("id_certificate"),
            "id_metacertificate" => $id_metacert,
            
            "tb" => $tb,
            "opsArr" => $this->op,
            
        );
        
        $this->render($this->op['assignmentUsersCourses'], $params);
    
    }
    
    
    function saveAssignment() {
        
        if(isset($_POST["undo_assign"]))
            Util::jump_to('index.php?r=alms/'.$this->controller_name.'/'.$this->op['assignment'].'&id_certificate='.Get::req("id_certificate",DOTY_INT,0));
        
        $id_meta_certificate = Get::req("id_metacertificate", DOTY_INT, 0);
               
        if($id_meta_certificate !== 0 ) {
        
            $aclManager = new DoceboACLManager();
            $array_user =& $aclManager->getAllUsersFromIdst($_SESSION['meta_certificate']['userSelectionArr']);

            $array_user = array_unique($array_user);
            
            if(
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
                                $query_course .= "('".$id_meta_certificate."', '".$id_user."', '".$id_course."')";
                                $first = false;
                            }
                            else
                                $query_course .= ", ('".$id_meta_certificate."', '".$id_user."', '".$id_course."')";

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
                                $query_coursepath .= "('".$id_meta_certificate."', '".$id_user."', '".$id_coursepath."')";
                                $first = false;
                            }
                            else
                                $query_coursepath .= ", ('".$id_meta_certificate."', '".$id_user."', '".$id_coursepath."')";

                $res = sql_query($query_coursepath);
            }
            
        }
        Util::jump_to('index.php?r=alms/'.$this->controller_name.'/'.$this->op['assignment'].'&id_certificate='.Get::req("id_certificate", DOTY_INT, 0).'&res='. ($res ? 'ok' : 'err'));
    
        
    }

    /**
    * This op. is called when you click on the assignment icon (multiple people),
    * then click on modify assignment
    * 
    * 
    */
    public function modAssignment() {
        
        checkPerm('mod');
        require_once(_base_.'/lib/lib.form.php');
        require_once($GLOBALS['where_framework'].'/lib/lib.directory.php');
        require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
        require_once($GLOBALS['where_lms'].'/lib/lib.course_managment.php');
        
        $id_cert = Get::req('id_certificate', DOTY_INT, 0);
        $id_meta = Get::req('idmeta', DOTY_INT, 0);
        $step = Get::req('step', DOTY_INT, 0);
        
        $acl_man =& Docebo::user()->getAclManager();
        $aclManager = new DoceboACLManager();
        
        require_once(_base_.'/lib/lib.userselector.php');
        $user_selection = new UserSelector();
        
        
        $form = new Form();
        $sel = new Course_Manager();
        $course_man = new Man_Course();
          
         
        $user_selection->show_orgchart_simple_selector = FALSE;
        $user_selection->multi_choice = TRUE;
        
        $user_selection->addFormInfo(
               $form->getHidden('step', 'step', 0)
                .$form->getHidden('id_certificate', 'id_certificate', $id_cert)
                .$form->getHidden('idmeta', 'idmeta', $id_meta)
                .$form->getHidden('user_reload', 'user_reload', 1)
            );

        $user_selection->setPageTitle(
                getTitleArea(
                    Lang::t('_TITLE_META_CERTIFICATE_ASSIGN','certificate'))
                                    .'<div class="std_block">'
        );
        
        if(!isset($_POST['user_reload'])) {
        
            $arr_id_users = $this->model->getUsersFromIdMeta($id_meta);
            $user_selection->resetSelection($arr_id_users);
            
        }
    
        
        $params = array();
        
        $params["user_selection"] = $user_selection; 
        $this->render('modassign', $params);
    
    }

    public function viewdetails() {
    
        $acl_man =& Docebo::user()->getAclManager();
        
        $id_certificate = Get::req('id_certificate', DOTY_INT, 0);
        $id_meta = Get::req('id_metacertificate', DOTY_INT, 0);
        
        $usersArr = $this->model->getUsersFromIdMeta($id_meta);
        $coursesArr = $this->model->getCoursesFromIdMeta($id_meta);
        
        
        //Table creation
        $tb    = new Table(0, Lang::t("_META_CERTIFICATE_DETAILS_CAPTION",'certificate'), Lang::t("_META_CERTIFICATE_DETAILS_CAPTION",'certificate'));
        $tb->setLink('index.php?r=alms/'.$this->controller_name.'/'.$this->op['viewdetails'].'&amp;id_certificate='.$id_certificate.'&amp;id_metacertificate='.$id_meta);

        $type_h = array('', '');
        $cont_h = array(Lang::t('_FULLNAME'), Lang::t('_USERNAME'));

        require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
        $course_man = new Man_Course();

        foreach($coursesArr as $id_course) {
            
            $type_h[] = 'align_center';

            $course_info = $course_man->getCourseInfo($id_course);

            $cont_h[] = $course_info['code'].' - '.$course_info['name'];
            
        }
                    
        $type_h[] = 'align_center';
        $cont_h[] = Lang::t('_META_CERTIFICATE_PROGRESS','certificate');
        
        $tb->setColsStyle($type_h);
        $tb->addHead($cont_h);
        
        reset($coursesArr);
        
        $array_user = $this->model->getIdStFromidUser($usersArr);
        
        foreach($array_user as $id_user) {
            
            $cont = array();

            $user_info = $acl_man->getUser($id_user, false);

            $cont[] = $user_info[ACL_INFO_LASTNAME].' '.$user_info[ACL_INFO_FIRSTNAME];

            $cont[] = $acl_man->relativeId($user_info[ACL_INFO_USERID]);

            $total_course_assigned = 0;
            $total_course_ended = 0;

            $status = $this->model->getUserAndCourseFromIdMeta($id_meta);
            
            foreach($coursesArr as $id_course) {
                
                if(!isset($status[$id_user][$id_course]))
                    $cont[] = Lang::t('_NOT_ASSIGNED');
                else
                {
                    $total_course_assigned++;

                    $control = $this->model->getCountCoursesCompleted($id_course,$id_user);
                    if($control) {
                        
                        $total_course_ended++;
                        $cont[] = Lang::t('_END', 'course');
                    
                    } else
                        $cont[] = Lang::t('_NOT_ENDED','certificate');
                }
            }

            $cont[] = $total_course_ended.' / '.$total_course_assigned;

            $tb->addBody($cont);
        }
        
        $params = array(
            
            "controller_name" => $this->controller_name,
            "id_certificate" => $id_certificate,
            "tb" => $tb,
            "opsArr" => $this->op,
            
        );
        
        $this->render('viewdetails', $params);
        
        
    }

    public function delAssociations(){
    
        checkPerm('mod');
        
        $id_metacertificate = Get::req('id_metacertificate', DOTY_INT, 0);
        $id_certificate = Get::req('id_certificate', DOTY_INT, 0);
        
        if(Get::req('confirm', DOTY_INT, 0) == 1) {

            Util::jump_to('index.php?r=alms/'.$this->controller_name.'/'.$this->op['assignment'].'&id_certificate='.$id_certificate.'&res='
            .($id_metacertificate != 0 && $this->model->deleteMetaCertDatas(strval($id_metacertificate)) ? 'ok' : 'err'));
            
            
            
        }
    }
    
    
    public function preview() {
              
        checkPerm('view');

        require_once(Forma::inc(_lms_.'/lib/lib.certificate.php'));

        $id_certificate = Get::req('id_certificate', DOTY_INT, 0);


        $cert = new Certificate();
        $cert->send_preview_certificate($id_certificate, array());      
                      
    }
    
    /**
    * Create operations:
    *   
    *   - Preview certificate
    *   - Release certificate
    *   - Delete released certificate
    * 
    */
    
    public function create() {
        
        checkPerm('mod');
        require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
        require_once(_base_.'/lib/lib.table.php');
        require_once(_base_.'/lib/lib.form.php');


        $acl_man =& Docebo::user()->getAclManager();
        $first = true;
        $tot_element = 0;
        $id_certificate = Get::req('id_certificate', DOTY_INT, 0);

        
        
        $tb = new Table(Get::sett('visuItem'), Lang::t('_META_CERTIFICATE_CREATE_CAPTION','certificate'), Lang::t('_META_CERTIFICATE_CREATE_CAPTION','certificate'));
        $tb->initNavBar('ini', 'button');
        $ini = $tb->getSelectedElement();

     

        // Getting an array of all metacerts belonging to the current certificate
        $idsMetaCertArr = $this->model->getIdsMetaCertificate($id_certificate);

        // Building array [idUser] => array( id_courses_completed )
        $usersWithCourseCompletedArr = $this->getUsersWithCourseCompleted();

        // Getting array of associations titles 
        $titleCoursesArr = $this->getAssociationsTitleArr();

        // Getting control array
        $controlArr = $this->getCountMetaCertsArr();


        if(isset($_POST['undo_filter_create'])) {
            unset($_POST['filter_username']);
            unset($_POST['filter_firstname']);
            unset($_POST['filter_lastname']);
            unset($_POST['filter_release_status']);
        }
        
        
                
        $form = '';
        $usersInMetaCertsArr = $this->model->getDataUsersInMetaCerts($idsMetaCertArr);

        foreach ($usersInMetaCertsArr as $k => $user) {
            
            foreach ($idsMetaCertArr as $idMeta) {
                
                if (isset($controlArr[$user['idUser']][$idMeta]) && $controlArr[$user['idUser']][$idMeta]) {
                    
                    $title = strip_tags($titleCoursesArr[$idMeta]);
                    
                    $idCourseArr = $this->model->getIdCourseFromIdUserAndIdMeta($user['idUser'], $idMeta);
                    $control = true;

                    foreach ($idCourseArr as $idCourse) {
                        if (!isset($usersWithCourseCompletedArr[$user['idUser']][$idCourse]))
                            $control = false;
                    }

                    if ($control) {

                        $tot_element++;
                        if ($tot_element > $ini && $tot_element <= ($ini + Get::sett('visuItem'))) {

                            $is_released = $this->model->getTotalCoursesAssign($user['idUser'], $idMeta);

                            if (
                                !isset($_POST['filter_release_status']) ||
                                (isset($_POST['filter_release_status']) && $_POST['filter_release_status'] == 0) ||
                                (isset($_POST['filter_release_status']) && $_POST['filter_release_status'] == '1' && $is_released == 1) ||
                                (isset($_POST['filter_release_status']) && $_POST['filter_release_status'] == '2' && $is_released == 0)
                            ) {

                                if($first) { 
                                
                                    $first = false;

                                    // Setting table and columns header for the table
                                    $type_h = array('', '', '', 'image', 'image', 'image');

                                    $cont_h = array(Lang::t('_FULLNAME'),
                                        Lang::t('_USERNAME'),
                                        Lang::t('_TITLE'),
                                        Get::img('standard/view.png', Lang::t('_PREVIEW', 'certificate')),
                                        Get::img('course/certificate.png', Lang::t('_TAKE_A_COPY', 'certificate')),
                                        '<img src="' . getPathImage('lms') . 'standard/delete.png" alt="' . Lang::t('_ALT_REM_META_CERT') . ' : ' . strip_tags($title) . '" />');

                                    $tb->setColsStyle($type_h);
                                    $tb->addHead($cont_h);

                                }
                                    
                                $cont = array();
                                $cont[] = $user['lastname'] . ' ' . $user['firstname'];
                                $cont[] = $acl_man->relativeId($user['username']);
                                $cont[] = $title;
                                $cont[] = '<a href="index.php?r=alms/'.$this->controller_name.'/'.$this->op['preview_cert'].'&amp;id_certificate=' . $id_certificate . '&amp;id_metacertificate=' . $idMeta . '&amp;username=' . $user['username'] . '">'
                                    . Get::img('standard/view.png', Lang::t('_PREVIEW', 'certificate') . ' : ' . strip_tags($title)) . '</a>';
                                $cont[] = '<a href="index.php?r=alms/'.$this->controller_name.'/'.$this->op['release_cert'].'&amp;id_certificate=' . $id_certificate . '&amp;id_metacertificate=' . $idMeta . '&amp;username=' . $user['username'] . '">'
                                    . Get::img('course/certificate.png', Lang::t('_TAKE_A_COPY', 'certificate') . ' : ' . strip_tags($title)) . '</a>';

                                if ($is_released)
                                    $cont[] = '<a href="index.php?r=alms/'.$this->controller_name.'/'.$this->op['release_cert'].'&amp;id_certificate=' . $id_certificate . '&amp;id_metacertificate=' . $idMeta . '&amp;username=' . $user['username'] . '">'
                                        . '<img src="' . getPathImage('lms') . 'standard/delete.png" alt="' . Lang::t('_ALT_REM_META_CERT') . ' : ' . strip_tags($title) . '" /></a>';
                                else
                                    $cont[] = '';

                                $tb->addBody($cont);
    
                            }
                        }
                    }
                }
            }
        }

        require_once(_base_.'/lib/lib.dialog.php');
        setupHrefDialogBox('a[href*='.$this->op['del_released'].']');
         
        $array_release_status = array(
            Lang::t('_ALL') => '0',
            Lang::t('_ONLY_RELEASED','certificate') => '1',
            Lang::t('_ONLY_NOT_RELEASED','certificate') => '2'
        );

        if($first) {
          
          if(isset($_POST['filter']))
              
               $form .=
                	Form::openForm('meta_certificate_filter', 'index.php?modname=meta_certificate&op=create&id_certificate='.$id_certificate)
                    .Form::openElementSpace()
                    .Form::getTextfield(Lang::t('_USERNAME'), 'filter_username', 'filter_username', '255', isset($_POST['filter_username']) ? $_POST['filter_username'] : '')
                    .Form::getTextfield(Lang::t('_FIRSTNAME'), 'filter_firstname', 'filter_firstname', '255', isset($_POST['filter_firstname']) ? $_POST['filter_firstname'] : '')
                    .Form::getTextfield(Lang::t('_LASTNAME'), 'filter_lastname', 'filter_lastname', '255', isset($_POST['filter_lastname']) ? $_POST['filter_lastname'] : '')
                    .Form::getRadioSet(Lang::t('_RELEASE_STATUS_FILTER'), 'filter_release_status', 'filter_release_status', $array_release_status, isset($_POST['filter_release_status']) ? $_POST['filter_release_status'] : '0')
                    .Form::closeElementSpace()
                    .Form::openButtonSpace()
                    .Form::getButton('filter', 'filter', Lang::t('_FILTER'))
                    .Form::getButton('undo_filter_create', 'undo_filter_create', Lang::t('_UNDO_FILTER'))
                    .Form::closeButtonSpace()
                    .Form::closeForm();
                    
          $form .=  Lang::t('_NO_USER_FOUND', 'report')
                    .getBackUi('index.php?r=alms/'.$this->controller_name.'/'.$this->op['home'], Lang::t('_BACK'))
                    .'</div>';

        } else {

            if(isset($_GET['result']))
            {
                switch($_GET['result'])
                {
                    case "ok":
                        cout(getResultUi(Lang::t('_OPERATION_SUCCESSFUL')));
                        break;
                    case "err_del_cert":
                       cout(getErrorUi(Lang::t('_OPERATION_FAILURE')));
                        break;
                }
            }
          
            $form .= 
                
                Form::openForm('meta_certificate_filter', 'index.php?modname=meta_certificate&op=create&id_certificate='.$id_certificate)
                .Form::openElementSpace()
                .Form::getTextfield(Lang::t('_USERNAME'), 'filter_username', 'filter_username', '255', isset($_POST['filter_username']) ? $_POST['filter_username'] : '')
                .Form::getTextfield(Lang::t('_FIRSTNAME'), 'filter_firstname', 'filter_firstname', '255', isset($_POST['filter_firstname']) ? $_POST['filter_firstname'] : '')
                .Form::getTextfield(Lang::t('_LASTNAME'), 'filter_lastname', 'filter_lastname', '255', isset($_POST['filter_lastname']) ? $_POST['filter_lastname'] : '')
                .Form::getRadioSet(Lang::t('_RELEASE_STATUS_FILTER'), 'filter_release_status', 'filter_release_status', $array_release_status, isset($_POST['filter_release_status']) ? $_POST['filter_release_status'] : '0')
                .Form::closeElementSpace()
                .Form::openButtonSpace()
                .Form::getButton('filter', 'filter', Lang::t('_FILTER'))
                .Form::getButton('undo_filter_create', 'undo_filter_create',Lang::t('_UNDO_FILTER'))
                .Form::closeButtonSpace()
                .$tb->getTable()
                .$tb->getNavBar($ini, $tot_element)
                .Form::closeForm()
                .getBackUi('index.php?modname=meta_certificate&amp;op=meta_certificate', Lang::t('_BACK'))
                .'</div>'; 
            
            
        }

      $params = array(
            "release_status_arr" => $array_release_status,
            "id_certificate" => $id_certificate,
            "tb" => $tb,
            "type_h" => $type_h,
            "tot_element" => $tot_element,
            "cont_h" => $cont_h,
            "ini" => $ini,
            
            "form" => $form,
            "opsArr" => $this->op,
            );
        
        $this->render($this->op['create'],$params);
    }
   
        function preview_cert() {
            
            checkPerm('view');

            require_once(Forma::inc(_lms_.'/lib/lib.certificate.php'));

            $id_certificate =  Get::req('id_certificate', DOTY_INT, 0);
            $id_meta = Get::req('id_metacertificate', DOTY_INT, 0);

            $id_course =  Get::req('id_course', true, 0);
            $id_user = Get::req('username', DOTY_INT, 0);

            $cert = new Certificate();
            $subs = $cert->getSubstitutionArray($id_user, $id_course, $id_meta);
            $cert->send_facsimile_certificate($id_certificate, $id_user, $id_course, $subs);
        
        }
       
        function release_cert() {
            checkPerm('view');

            require_once(Forma::inc(_lms_.'/lib/lib.certificate.php'));

            
            $id_certificate =  Get::req('id_certificate', DOTY_INT, 0);
            $id_meta = Get::req('id_metacertificate', DOTY_INT, 0);

            $id_course =  Get::req('id_course', true, 0);
            $id_user = Get::req('username', DOTY_INT, 0);


            $cert = new Certificate();
            $subs = $cert->getSubstitutionArray($id_user, $id_course, $id_meta);
            $cert->send_certificate($id_certificate, $id_user, $id_course, $subs);
        }
       
        function delReleased() {
            
            checkPerm('mod');

            require_once(_base_.'/lib/lib.form.php');
            require_once(_base_.'/lib/lib.upload.php');
            
            $id_certificate =  Get::req('id_certificate', DOTY_INT, 0);
            $id_meta = Get::req('id_metacertificate', DOTY_INT, 0);
            $id_user = Get::req('id_user', DOTY_INT, 0);

            $acl_man =& Docebo::user()->getAclManager();

            if(Get::req('confirm', DOTY_INT, 0) == 1) {

               $cert_file = $this->model->getCertFile($id_user,$id_meta);

               $path = '/appLms/certificate/';

               sl_open_fileoperations();
               $res = sl_unlink($path.$cert_file);
               sl_close_fileoperations();

               if(!$res)
                    Util::jump_to('index.php?r=alms/'.$this->controller_name.'/'.$this->op['create'].'&id_certificate='.$id_certificate.'&result=err_del_cert');

               $res = $this->model->deleteReleasedCert($id_user, $id_meta);
               
               Util::jump_to('index.php?r=alms/'.$this->controller_name.'/'.$this->op['create'].'&id_certificate='.$id_certificate.'&result='. ($res ? 'ok' : 'err_del_cert'));

            }
            else
            {
                list($name, $descr) = sql_fetch_row(sql_query("
                SELECT name, description
                FROM ".$GLOBALS['prefix_lms']."_certificate
                WHERE id_certificate = '".$id_certificate."'"));

                $user_info = $acl_man->getUser($id_user, false);

                $user = $user_info[ACL_INFO_LASTNAME].' '.$user_info[ACL_INFO_FIRSTNAME].' ('.$acl_man->relativeId($user_info[ACL_INFO_USERID]).')';

                $form = new Form();
                $page_title = array(
                    'index.php?modname=meta_certificate&amp;op=meta_certificate' => $lang->def('_TITLE_CERTIFICATE'),
                    $lang->def('_DEL_RELEASED')
                );
                $GLOBALS['page']->add(
                    getTitleArea($page_title, 'certificate')
                    .'<div class="std_block">'
                    .$form->openForm('del_certificate', 'index.php?modname=meta_certificate&amp;op=del_released')
                    .$form->getHidden('id_certificate', 'id_certificate', $id_certificate)
                    .$form->getHidden('idmeta', 'idmeta', $id_meta)
                    .$form->getHidden('iduser', 'iduser', $id_user)
                    .getDeleteUi(    $lang->def('_AREYOUSURE'),
                                    '<span>'.$lang->def('_NAME').' : </span>'.$name.'<br />'
                                    .'<span>'.$lang->def('_DESCRIPTION').' : </span>'.$descr.'<br />'
                                    .'<span>'.$lang->def('_USER').' : </span>'.$user,
                                    false,
                                    'confirm',
                                    'undo'    )
                    .$form->closeForm()
                    .'</div>', 'content');
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
        $return = array(	'filename' => $old_file,
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

        $nodesArr = $this->model->getPathsFromIdParent($idParent); // getting nodes with idParent
        $countNodes = count($nodesArr);

        if ($countNodes > 0) {

            $node = 0;
            while ($node < $countNodes) { // Processing all nodes with idParent

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

            echo $this->json->encode($this->model->getCourseListFromIdCat($_POST["nodesArr"]));

        }

    }

    /**
     * Ajax call from view
     */
    function getCoursePathListTask() {

        echo $this->json->encode($this->model->getCoursePathList());

    } 
    
    /**
     * Ajax call from view
     */
    function getCatalogCourseListTask() {

        echo $this->json->encode($this->model->getCatalogCourse());

    }

}    