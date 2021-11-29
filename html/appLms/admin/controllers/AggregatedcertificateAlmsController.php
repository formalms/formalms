<?php defined('IN_FORMA') or die('Direct access is forbidden');

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

/**
 * Class MetacertificateAlmsController
 */
class AggregatedcertificateAlmsController extends AlmsController
{
    /** @var AggregatedcertificateAlms */
    protected $model;

    protected $controller_name;

    /** @var Services_JSON */
    protected $json;

    /** @var AggregatedCertificate */
    protected $aggCertLib;

    public $cert_name;
    /**
     *   In this array there are all the operations called in the module.
     *
     *   "key" => "link"
     */
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

    function init()
    {
        parent::init();

        $this->controller_name = strtolower(str_replace('AlmsController', '', get_class($this)));
        $this->json = new Services_JSON();
        require_once Forma::inc(_lms_ . '/' . _folder_lib_ . '/lib.aggregated_certificate.php');
        $this->aggCertLib = new AggregatedCertificate();

        $this->model = new AggregatedcertificateAlms();

        $this->id_certificate = importVar('id_certificate', true, 0);
        $this->id_association = importVar('id_association', true, 0);
        $this->cert_name = $this->aggCertLib->getAggrCertName($this->id_certificate);
        if ($this->id_association > 0) {
            $associationsMeta = $this->aggCertLib->getAssociationsMetadata(0, $this->id_association);
            $this->association_name = $associationsMeta[0]['title'];
        } else {
            $this->association_name = '';
        }
        $this->back_url = 'index.php?r=alms/' . $this->controller_name . '/associationsManagement&id_certificate=' . $this->id_certificate;

    }


    // ---------- Certificate management ------------

    /** Default op.
     *
     * Metacertificate administration panel
     *
     * Action: show
     * OK
     *
     */
    function show()
    {

        $params = [];

        checkPerm('view');

        $tb = new Table(Get::sett('visuItem'), Lang::t('_META_CERTIFICATE_SUMMARY', 'certificate'), Lang::t('_META_CERTIFICATE_SUMMARY', 'certificate'));
        $tb->initNavBar('ini', 'link');
        $tb->setLink('index.php?r=alms/' . $this->controller_name . '/show');
        $ini = $tb->getSelectedElement();

        $ini = importVar('ini', true, 0);

        $filter_text = Get::req('filter_text', DOTY_STRING, '');

        if (Get::req('toggle_filter', DOTY_STRING, '') !== '') {
            unset($filter_text);
        }

        if (!empty($filter_text)) {

            $params['filter_text'] = $filter_text;
        }


        // Type for all columns
        $type_h = ['', '', '', 'image', 'image'];

        // Label for all columns
        $cont_h = [
            Lang::t('_CODE'),
            Lang::t('_NAME'),
            Lang::t('_DESCRIPTION')
        ];

        $userCanModify = checkPerm('mod', true);

        if ($userCanModify)
            $cont_h[] = Lang::t('_TEMPLATE', 'certificate');

        $cont_h[] = Get::img('standard/view.png', Lang::t('_PREVIEW'));

        if ($userCanModify) {

            $cont_h[] = Get::img('standard/moduser.png', Lang::t('_CERTIFICATE_AGGREGATE_ASSOCIATION', 'certificate'));
            $type_h[] = 'image';

            $cont_h[] = Get::sprite('subs_print', Lang::t('_CERTIFICATE_AGGREGATE_ASSOCIATION', 'certificate'));
            $type_h[] = 'image';

            $cont_h[] = Get::img('standard/edit.png', Lang::t('_MOD'), Lang::t('_MOD'));
            $type_h[] = 'image';

            $cont_h[] = Get::img('standard/delete.png', Lang::t('_DEL'), Lang::t('_DEL'));
            $type_h[] = 'image';

        }

        $tb->setColsStyle($type_h);
        $tb->addHead($cont_h);


        // Array of all metacertificates to display in the main admin panel
        $aggregateCertsArr = $this->aggCertLib->getAllAggregatedCerts($ini, false, $filter);

        $aggregateCertsArrTot = $this->aggCertLib->getAllAggregatedCerts(0, true);

        foreach ($aggregateCertsArr as $aggregate_cert) {
            $title = strip_tags($aggregate_cert['name']);

            $cont = [
                $aggregate_cert['code'],
                $aggregate_cert['name'],
                Util::cut($aggregate_cert['description'])
            ];


            if ($userCanModify) {
                $cont[] = '<a href="index.php?r=alms/' . $this->controller_name . '/layout&amp;id_certificate=' . $aggregate_cert['id_certificate'] . '&amp;edit=1" '
                    . 'title="' . Lang::t('_TEMPLATE', 'certificate') . '">'
                    . Lang::t('_TEMPLATE', 'certificate') . '</a>';
            }


            $cont[] = Get::sprite_link(
                'subs_view',
                'index.php?r=alms/' . $this->controller_name . '/release_cert&amp;id_certificate=' . $aggregate_cert['id_certificate'],
                Lang::t('_PREVIEW') . ' : ' . $title
            );

            if ($userCanModify) {

                $cont[] = Get::sprite_link(
                    'subs_admin',
                    'index.php?r=alms/' . $this->controller_name . '/associationsManagement&amp;id_certificate=' . $aggregate_cert['id_certificate'],
                    Lang::t('_CERTIFICATE_AGGREGATE_ASSOCIATION', 'certificate') . ' : ' . $title
                );

                $cont[] = Get::sprite_link(
                    'subs_print',
                    'index.php?r=alms/' . $this->controller_name . '/assignmentManagement&amp;id_certificate=' . $aggregate_cert['id_certificate'],
                    Lang::t('_TITLE_ASSIGN_META_CERTIFICATE', 'certificate') . ' : ' . $title
                );

                $cont[] = Get::sprite_link(
                    'subs_mod',
                    'index.php?r=alms/' . $this->controller_name . '/metadata&amp;id_certificate=' . $aggregate_cert['id_certificate'],
                    Lang::t('_MOD') . ' : ' . $title
                );

                $cont[] = Get::sprite_link(
                    'subs_del',
                    'index.php?r=alms/' . $this->controller_name . '/' . $this->op['delmetacert'] . '&amp;id_certificate=' . $aggregate_cert['id_certificate'],
                    Lang::t('_DEL') . ' : ' . $title
                );
            }

            $tb->addBody($cont);
        }


        require_once(_base_ . '/lib/lib.dialog.php');

        setupHrefDialogBox('a[href*=' . $this->op['delmetacert'] . ']');

        if ($userCanModify) {
            $tb->addActionAdd('
               <a   class="ico-wt-sprite subs_add" 
                    href="index.php?r=alms/' . $this->controller_name . '/metadata" title=' . Lang::t('_NEW_CERTIFICATE', 'certificate') . '">
                    <span>' . Lang::t('_NEW_CERTIFICATE', 'certificate') . '</span>
               </a>
           ');
        }


        $params['tb'] = $tb;
        $params['ini'] = $ini;
        $params['countAggrCerts'] = count($aggregateCertsArr);

        $params['aggregateCertsArrTot'] = $aggregateCertsArrTot;

        $params['controller_name'] = $this->controller_name;
        $params['opsArr'] = $this->op;

        $this->render('show', $params);

    }

    /**
     * OK
     */
    function metadata()
    {

        checkPerm('mod');

        $all_languages = Docebo::langManager()->getAllLanguages();
        $languages = [];

        foreach ($all_languages as $k => $v) {
            $languages[$v[0]] = $v[1];
        }

        require_once(_base_ . '/lib/lib.form.php');
        $form = new Form();


        $params = [];

        $id_cert = Get::req('id_certificate', DOTY_INT, 0);

        $isModifyingMetaData = $id_cert !== 0;
        $page_title = [
            'index.php?r=alms/' . $this->controller_name . '/show' => Lang::t('_TITLE_META_CERTIFICATE', 'certificate'),
            $isModifyingMetaData ? Lang::t('_MOD') : Lang::t('_NEW_CERTIFICATE', 'certificate')
        ];

        if ($isModifyingMetaData) {

            $params['metacert'] = $this->aggCertLib->getMetadata($id_cert);
            $params['id_certificate'] = $id_cert;

        }

        $params['page_title'] = $page_title;
        $params['controller_name'] = $this->controller_name;
        $params['languages'] = $languages;
        $params['form'] = $form;


        $this->render('metadata', $params);
    }

    /**
     *  OK
     */
    function saveMetaData()
    {

        checkPerm('mod');

        if (isset($_POST['undo'])) {
            Util::jump_to('index.php?r=alms/' . $this->controller_name . '/show');
        }

        $isModifyingMetadata = isset($_POST['id_certificate']);

        $metaDataCertArr = [

            'code' => $_POST['code'],
            'name' => ($_POST['name'] === '') ? Lang::t('_NOTITLE') : $_POST['name'],
            'base_language' => $_POST['base_language'],
            'descr' => $_POST['descr'],
            'user_release' => $_POST['user_release'],
            'meta' => 1,

        ];

        if ($isModifyingMetadata) {
            $metaDataCertArr['id_certificate'] = Get::req('id_certificate');
        }

        $res = $this->aggCertLib->insertMetaDataCert($metaDataCertArr);

        if ($res) {
            if (!$isModifyingMetadata) {
                Util::jump_to('index.php?r=alms/' . $this->controller_name . '/layout&id_certificate=' . $this->aggCertLib->getLastInsertedIdCertificate());
            } else {
                Util::jump_to('index.php?r=alms/' . $this->controller_name . '/show&result=' . ($res ? 'ok' : 'err'));
            }

        } else {
            Util::jump_to('index.php?r=alms/' . $this->controller_name . '/show&result=err');
        }


    }


    /**
     *  New Certificate Layout
     * ok
     */
    function layout()
    {

        checkPerm('view');
        checkPerm('mod', true);

        // If the user want to edit layout, then in the page will be loaded the datas from the db
        $edit = Get::req('edit', DOTY_INT, 0);

        $page_title = [
            'index.php?r=alms/' . $this->controller_name . '/' . $this->op['layout'] => Lang::t('_TITLE_META_CERTIFICATE', 'certificate') . ':&nbsp;' . $this->cert_name,
            ($edit ? Lang::t('_MOD') : Lang::t('_NEW'))
        ];

        if ($edit && $this->id_certificate !== 0) {
            $template = $this->aggCertLib->getLayoutMetacert($this->id_certificate);
        }

        $certificate_tags = $this->aggCertLib->getCertificateTags();

        $params = [
            'controller_name' => $this->controller_name,
            'page_title' => $page_title,

            'id_certificate' => $this->id_certificate,
            'certificate_tags' => $certificate_tags,
        ];

        if (isset($template)) {
            $params['template'] = $template;
        }

        $this->render('layout', $params);
    }

    /**
     *  OK
     */
    function saveLayout()
    {

        checkPerm('mod');

        if (isset($_POST['undo'])) {
            Util::jump_to('index.php?r=alms/' . $this->controller_name . '/show');
        }


        if (isset($_POST['structure_certificate'])) {
            $path = '/appLms/certificate/';
            $path = $path . (substr($path, -1) != '/' && substr($path, -1) != '\\' ? '/' : '');

            $flagDeleteOldImage = $_POST['file_to_del']['bgimage'] ?? null;

            $bgimage = $this->manageCertificateFile('bgimage',
                $_POST['old_bgimage'],
                $path,
                $flagDeleteOldImage);

            if (!$bgimage) {
                $bgimage = '';
            }
        }

        $layoutArr = [

            'id_certificate' => $_POST['id_certificate'],
            'cert_structure' => "'" . $_POST['structure'] . "'",
            'orientation' => "'" . $_POST['orientation'] . "'",
            'bgimage' => "'" . $bgimage . "'",


        ];

        $res = $this->aggCertLib->updateLayout($layoutArr);

        Util::jump_to('index.php?r=alms/' . $this->controller_name . '/show&result=' . ($res ? 'ok' : 'err'));

    }


    // ------------- Associations management ------------------

    /**
     * Assignment / metacertificate management
     *
     * In this action, will be rendered all the tools for managing
     * association between a certificate with the users and the courses
     * ok
     *
     */
    function associationsManagement()
    {

        checkPerm('mod');
        require_once(_base_ . '/lib/lib.table.php');

        // Creating table...
        $tb = new Table(Get::sett('visuItem'), Lang::t('_META_CERTIFICATE_ASSIGN_CAPTION', 'certificate'), Lang::t('_META_CERTIFICATE_ASSIGN_CAPTION', 'certificate'));
        $tb->initNavBar('ini', 'link');
        $tb->setLink($this->back_url);
        $ini = $tb->getSelectedElement();
        $tb->addActionAdd(
            '<a class="new_element_link" 
            href="index.php?r=alms/' . $this->controller_name . '/modAssoc&amp;id_certificate=' . $this->id_certificate . '"
            title="' . Lang::t('_NEW_ASSING_META_CERTIFICATE', 'certificate') . '">'
            . Lang::t('_NEW_ASSING_META_CERTIFICATE', 'certificate')
            . '</a>'
        );

        // Getting all metacerts belonging to the certificate
        $associationsMetadataArr = $this->aggCertLib->getAssociationsMetadata($this->id_certificate, 0, $ini);

        $type_h = ['',  // Name
            '',  // Description
            'image',  // View details img
            'image',  // Course edit  img
            'image',  // Coursepath edit img
            'image'];// Delete metacert. img


        $cont_h = [

            Lang::t('_NAME'),
            Lang::t('_DESCRIPTION'),
            '<img src="' . getPathImage() . 'standard/view.png" alt="' . Lang::t('_DETAILS') . '" title="' . Lang::t('_DETAILS') . '" />',
            Lang::t('_TYPE'),
            '<img src="' . getPathImage() . 'standard/edit.png" title="' . Lang::t('_MOD') . '" alt="' . Lang::t('_MOD') . '" />',
            '<img src="' . getPathImage() . 'standard/delete.png" title="' . Lang::t('_DEL') . '" alt="' . Lang::t('_DEL') . '"" />'

        ];

        $tb->setColsStyle($type_h);
        $tb->addHead($cont_h);


        foreach ($associationsMetadataArr as $k => $association) {

            // Getting type of metacert. (if it's a metacert on course, on coursepath...)
            $type_association = $this->aggCertLib->getTypeAssoc($association['idAssociation']);
            $rows = [];
            if (in_array($type_association, AggregatedCertificate::ALLOWED_CERTIFICATE_TYPES, true)) {

                $rows[] = stripslashes($association['title']);
                $rows[] = stripslashes($association['description']);
                $rows[] = Get::sprite_link(
                    'subs_view',
                    'index.php?r=alms/' . $this->controller_name . '/' . $this->op['view_details'] . '&amp;id_association=' . $association['idAssociation'] . '&amp;id_certificate='
                    . $this->id_certificate . '&amp;type_assoc=' . $type_association,
                    Lang::t('_DETAILS')
                );
                switch ($type_association) {
                    case AggregatedCertificate::AGGREGATE_CERTIFICATE_TYPE_COURSE:
                        $rows[] = Lang::t('_COURSE');
                        $rows[] = '<a href="index.php?r=alms/' . $this->controller_name . '/modAssoc'
                            . '&amp;id_certificate=' . $this->id_certificate
                            . '&amp;id_association=' . $association['idAssociation']
                            . '&amp;type_assoc=' . AggregatedCertificate::AGGREGATE_CERTIFICATE_TYPE_COURSE
                            . '">
                        <img src="' . getPathImage() . 'standard/edit.png"
                             alt="' . Lang::t('_MOD') . '" 
                             title="' . Lang::t('_MOD') . '" />
                    </a>';
                        break;
                    case AggregatedCertificate::AGGREGATE_CERTIFICATE_TYPE_COURSE_PATH:
                        $rows[] = Lang::t('_COURSEPATH');
                        $rows[] = '<a href="index.php?r=alms/' . $this->controller_name . '/modAssoc'
                            . '&amp;id_certificate=' . $this->id_certificate
                            . '&amp;id_association=' . $association['idAssociation']
                            . '&amp;type_assoc=' . AggregatedCertificate::AGGREGATE_CERTIFICATE_TYPE_COURSE_PATH
                            . '&amp;edit=1'
                            . '">
                        <img 
                            src="' . getPathImage() . 'standard/edit.png" 
                            alt="' . Lang::t('_MOD') . '" 
                            title="' . Lang::t('_MOD') . '" />
                    </a>';
                        break;
                    default:
                }

                $rows[] = Get::sprite_link(
                    'subs_del',
                    'index.php?r=alms/' . $this->controller_name . '/' . $this->op['del_association']
                    . '&amp;id_association=' . $association['idAssociation']
                    . '&amp;id_certificate=' . $this->id_certificate
                    . '&amp;type_assoc=' . $type_association,
                    Lang::t('_DEL')
                );
                $tb->addBody($rows);
            }
        }

        require_once(_base_ . '/lib/lib.dialog.php');
        setupHrefDialogBox('a[href*=delassignmetacertificate]');


        setupHrefDialogBox('a[href*=' . $this->op['del_association'] . ']');

        // Aggiungere messaggi di errore con dettagli
        if (isset($_GET['res'])) {
            switch ($_GET['res']) {
                case 'ok':
                    cout(getResultUi(Lang::t('_OPERATION_SUCCESSFUL')));
                    break;
                case 'err_del':
                case 'err_info':
                case 'err_mod_info':
                case 'error_mod_assign':
                case 'err':
                    cout(getErrorUi(Lang::t('_OPERATION_FAILURE')));
                    break;
                default:

            }
        }


        $params = [
            'id_certificate' => $this->id_certificate,
            'countAssociations' => count($this->aggCertLib->getAssociationsMetadata($this->id_certificate)),
            'ini' => $ini,
            'cert_name' => $this->cert_name,
            'arrOps' => $this->op,
            'controller_name' => $this->controller_name,
            'tb' => $tb,
        ];

        $this->render('associationsManagement', $params);

    }


    function modAssocDesc()
    {

        checkPerm('mod');

        $association = [

            'idCertificate' => $this->id_certificate,
            'title' => Get::req('title', DOTY_STRING),
            'description' => Get::req('description', DOTY_STRING),
            'idAssociation' => $this->id_association
        ];


        $res = $this->aggCertLib->updateMetaDataAssoc($association);
        Util::jump_to($this->back_url . '&res=' . ($res ? 'ok' : 'err'));
    }

    /**
     * ok
     */
    function modAssoc()
    {
        $params = [];

        // necessary for passing additional parameters to the form (ex. disabled to type selector)
        $params['html_before_select'] = '';


        if ($this->id_association > 0) { // If i'm editing an association, i need to get all datas. of that assoc.
            $associationMetadataArr = $this->aggCertLib->getAssociationsMetadata(0, $this->id_association);
            $params['associationMetadataArr'] = $associationMetadataArr[0];
            $params['html_before_select'] = 'disabled';
            $params['type_assoc'] = Get::req('type_assoc', DOTY_INT, -1);
            $params['id_association'] = $this->id_association;
        }

        $assoc_types = [

            AggregatedCertificate::AGGREGATE_CERTIFICATE_TYPE_COURSE => Lang::t('_COURSE'),
            AggregatedCertificate::AGGREGATE_CERTIFICATE_TYPE_COURSE_PATH => Lang::t('_COURSEPATH'),

        ];


        $params['cert_name'] = $this->cert_name;
        $params['id_certificate'] = $this->id_certificate;
        $params['assoc_types'] = $assoc_types;
        $params['operation'] = 'newassociation';

        $this->render('metaDataAssoc', $params);
    }

    /**
     * ok
     *
     */
    function newAssociation()
    {
        if (isset($_POST['undo_assign'])) {
            Util::jump_to($this->back_url);
        }

        $typeAssociation = (int)Get::req('type_assoc', DOTY_INT);
        $operation = Get::req('nextOperation');
        if ($operation === Lang::t('_NEXT')) {
            if ($typeAssociation === AggregatedCertificate::AGGREGATE_CERTIFICATE_TYPE_COURSE) {
                $this->associationCourses();
                return;
            }
            if ($typeAssociation === AggregatedCertificate::AGGREGATE_CERTIFICATE_TYPE_COURSE_PATH) {
                $this->associationPaths();
                return;
            }
        } else {
            $this->modAssocDesc();
        }
    }


    /**
     * Creating an association between users and courses
     *
     * Selecting the courses/coursepaths for the association
     *
     */

    function associationCourses()
    {

        require_once($GLOBALS['where_lms'] . '/lib/lib.course.php');
        require_once($GLOBALS['where_lms'] . '/lib/lib.course_managment.php');

        if (isset($_POST['undo_assign'])) {
            Util::jump_to($this->back_url);
        }

        $params = [
            'id_certificate' => Get::req('id_certificate', DOTY_INT),
            'id_association' => $this->id_association,
            'type_assoc' => Get::req('type_assoc', DOTY_INT, -1),
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'cert_name' => $this->cert_name
        ];


        // loading courses tree / course path list
        $treeCat = [
            'text' => Lang::t('_ROOT'),
            'level' => 0,
            'idCategory' => 0,
            'nodes' => $this->getTreeCategoryAsArray()
        ];


        //$params["course_manager"] = $course_manager;
        $params['treeCat'] = [$treeCat];
        if ($this->id_association > 0) {
            $idsC = $this->aggCertLib->getIdsCourse($this->id_association);
            $params['idsCourses'] = '[' . implode(',', $idsC) . ']';
            $params['coursesArr'] = $this->aggCertLib->getCoursesArrFromId($idsC);
        } else {
            $params['idsCourses'] = '[]';
        }
        $this->render($this->op['associationCourses'], $params);
    }


    function associationPaths()
    {

        require_once($GLOBALS['where_lms'] . '/lib/lib.course.php');
        require_once($GLOBALS['where_lms'] . '/lib/lib.course_managment.php');

        if (isset($_POST['cancelselector'])) {
            Util::jump_to($this->back_url);
        }


        $params = [
            'id_certificate' => $this->id_certificate,
            'id_association' => $this->id_association,
            'type_assoc' => Get::req('type_assoc', DOTY_INT, -1),
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'cert_name' => $this->cert_name
        ];


        if ($this->id_association > 0) {
            $coursePathIdsArr = $this->aggCertLib->getIdsCoursePath($this->id_association);
            $params['idsCoursePath'] = '[' . implode(',', $coursePathIdsArr) . ']';
            require_once($GLOBALS['where_lms'] . '/lib/lib.coursepath.php');
            $coursePath_man = new CoursePath_Manager();
            $params['coursePathsArr'] = $coursePath_man->getCoursepathAllInfo($coursePathIdsArr);
        } else {
            $params['idsCoursePath'] = '[]';
            $params['coursePathsArr'] = '[]';
        }

        $this->render('associationPath', $params);
    }


    function associationUsers()
    {

        require_once(_base_ . '/lib/lib.userselector.php');
        require_once(_base_ . '/lib/lib.form.php');

        if (isset($_POST['undo']) || isset($_POST['undo_filter']) || isset($_POST['cancelselector'])) {
            Util::jump_to($this->back_url);
        }


        $type_assoc = importVar('type_assoc', true, 0);
        $user_selection = new UserSelector();
        if ($_POST['id_association'] > 0) {

            /*
             * Editing assoc.
             *
             * Need to compare old users and new, to add or to remove users from assoc.
             */
            $usersArr = $this->aggCertLib->getAllUsersFromIdAssoc($this->id_association, $type_assoc);

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

        $user_selection->setPageTitle(getTitleArea(Lang::t('_TITLE_META_CERTIFICATE_ASSIGN', 'certificate'), 'certificate'));

        $user_selection->addFormInfo('<input type="hidden" name="id_certificate" value=' . $this->id_certificate . ' />');
        $user_selection->addFormInfo('<input type="hidden" name="id_association" value=' . $this->id_association . ' />');
        $user_selection->addFormInfo('<input type="hidden" name="type_assoc" value=' . $type_assoc . ' />');
        $user_selection->addFormInfo('<input type="hidden" name="selected_courses" value=' . Get::req('idsCourse', DOTY_NUMLIST) . ' />');
        $user_selection->addFormInfo('<input type="hidden" name="title" value="' . $_POST['title'] . '"/>');
        $user_selection->addFormInfo('<input type="hidden" name="description" value="' . $_POST['description'] . '"/>');
        $user_selection->addFormInfo('<input type="hidden" name="selected_idsCoursePath" value=' . get::req('idsCoursePath', DOTY_NUMLIST) . ' />');


        $params = [
            'user_selection' => $user_selection,
            'opsArr' => $this->op,
            'controller_name' => $this->controller_name,
            'cert_name' => $this->cert_name
        ];

        $this->render($this->op['associationusers'], $params);
    }

    function associationUsersPath()
    {

        // Loading necessary libraries
        require_once(_base_ . '/lib/lib.userselector.php');
        require_once($GLOBALS['where_lms'] . '/lib/lib.course.php');
        require_once($GLOBALS['where_lms'] . '/lib/lib.coursepath.php');
        require_once($GLOBALS['where_lms'] . '/lib/lib.course_managment.php');

        YuiLib::load();
        Util::get_js(Get::rel_path('base') . '/lib/js_utils.js', true, true);
        $type_assoc = Get::req('type_assoc', DOTY_INT, -1);

        if (isset($_POST['undo']) || isset($_POST['undo_filter']) || isset($_POST['cancelselector'])) {
            Util::jump_to($this->back_url);
        }


        // Users after editing (there may be the same users, new users added, or user to delete)

        $user_selection = new UserSelector();
        $acl_man =& Docebo::user()->getAclManager();
        $aclManager = new DoceboACLManager();
        $userSelectionArr = array_map('intval', $user_selection->getSelection($_POST));
        $userSelectionArr = $aclManager->getAllUsersFromIdst($userSelectionArr);
        $array_user = $aclManager->getArrUserST($userSelectionArr);

        $form = new Form();
        $form_name = 'new_assign_step_3';

        $tb = new Table(0, Lang::t('_META_CERTIFICATE_NEW_ASSIGN_CAPTION', 'certificate'), Lang::t('_META_CERTIFICATE_NEW_ASSIGN_SUMMARY'));
        $tb->setLink('index.php?r=alms/' . $this->controller_name . '/show');
        $tb->setTableId('tb_AssocLinks');

        // Setting table header
        $type_h = ['', ''];
        $cont_h = [Lang::t('_FULLNAME'), Lang::t('_USERNAME')];

        $selected_idsCoursePath = Get::req('selected_idsCoursePath', DOTY_NUMLIST);
        $idsCP_array = explode(',', $selected_idsCoursePath);
        sort($idsCP_array);
        $coursePath_man = new CoursePath_Manager();
        $coursePathInfoArr = $coursePath_man->getCoursepathAllInfo($idsCP_array);

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

            $check_assoc = $this->aggCertLib->getAssociationLink($this->id_association, $type_assoc, (int)$id_user);
            foreach ($idsCP_array as $id_coursepath) {
                $checked = in_array($id_coursepath, $check_assoc);
                $cont[] = $form->getCheckbox('', '_' . $id_user . '_' . $id_coursepath . '_', '_' . $id_user . '_' . $id_coursepath . '_', 1, $checked);

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


        $params = [
            'form' => $form,
            'id_certificate' => $this->id_certificate,
            'id_association' => $this->id_association,
            'type_assoc' => $type_assoc,
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'selected_idsCoursePath' => $selected_idsCoursePath,
            'selected_users' => implode(',', $userSelectionArr),
            'tb' => $tb,
            'opsArr' => $this->op,
            'cert_name' => $this->cert_name

        ];


        $this->render('associationCreate', $params);


    }


    function associationUsersCourses()
    {

        // Loading necessary libraries
        require_once(_base_ . '/lib/lib.userselector.php');
        require_once($GLOBALS['where_lms'] . '/lib/lib.course.php');
        require_once($GLOBALS['where_lms'] . '/lib/lib.course_managment.php');

        YuiLib::load();
        Util::get_js(Get::rel_path('base') . '/lib/js_utils.js', true, true);

        $type_assoc = Get::req('type_assoc', DOTY_INT, -1);

        if (isset($_POST['undo']) || isset($_POST['undo_filter']) || isset($_POST['cancelselector'])) {
            Util::jump_to($this->back_url);
        }


        // Users after editing (there may be the same users, new users added, or user to delete)

        $user_selection = new UserSelector();
        $acl_man =& Docebo::user()->getAclManager();
        $aclManager = new DoceboACLManager();
        $userSelectionArr = array_map('intval', $user_selection->getSelection($_POST));
        $userSelectionArr = $aclManager->getAllUsersFromIdst($userSelectionArr);
        $array_user = $aclManager->getArrUserST($userSelectionArr);
        $selected_course = explode(',', $_POST['selected_courses']);

        $form = new Form();
        $form_name = 'new_assign_step_3';

        $tb = new Table(0, Lang::t('_META_CERTIFICATE_NEW_ASSIGN_CAPTION', 'certificate'), Lang::t('_META_CERTIFICATE_NEW_ASSIGN_SUMMARY'));
        $tb->setLink('index.php?r=alms/' . $this->controller_name . '/show');
        $tb->setTableId('tb_AssocLinks');

        //  Table header
        $type_h = ['', ''];
        $cont_h = [Lang::t('_FULLNAME'), Lang::t('_USERNAME')];
        $course_man = new Man_Course();
        foreach ($selected_course as $id_course) {
            $type_h[] = 'align_center';
            $course_info = $course_man->getCourseInfo($id_course);
            $cont_h[] = $course_info['code'] . ' - ' . $course_info['name'];
            $cont_footer[] = '<a href="javascript:;" onclick="checkall_meta(\'' . $form_name . '\', \'' . $id_course . '\', true); return false;">'
                . Lang::t('_SELECT_ALL')
                . '</a><br/>'
                . '<a href="javascript:;" onclick="checkall_meta(\'' . $form_name . '\', \'' . $id_course . '\', false); return false;">'
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
            $check_assoc = $this->aggCertLib->getAssociationLink($this->id_association, $type_assoc, (int)$id_user);
            foreach ($selected_course as $id_course) {
                $checked = in_array($id_course, $check_assoc);
                $cont[] = $form->getCheckbox('', '_' . $id_user . '_' . $id_course . '_', '_' . $id_user . '_' . $id_course . '_', 1, $checked);
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

        $params = [

            'form' => $form,
            'id_certificate' => $this->id_certificate,
            'id_association' => $this->id_association,
            'type_assoc' => $type_assoc,
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'selected_courses' => $_POST['selected_courses'],
            'selected_users' => implode(',', $userSelectionArr),
            'tb' => $tb,
            'opsArr' => $this->op,
            'cert_name' => $this->cert_name

        ];

        $this->render('associationCreate', $params);

    }

    /**
     *  ok
     */
    function saveAssignment()
    {

        if (isset($_POST['undo_assign'])) {
            Util::jump_to($this->back_url);
        }

        $type_assoc = Get::req('type_assoc', DOTY_INT, -1);
        $selected_courses = explode(',', $_POST['selected_courses']);
        $selected_path = explode(',', $_POST['selected_idsCoursePath']);
        $selected_users = [];
        $associationsArr = [];
        if ($_POST['selected_users'] != '') {
            $selected_users = explode(',', $_POST['selected_users']);
        }
        switch ($type_assoc) {
            case AggregatedCertificate::AGGREGATE_CERTIFICATE_TYPE_COURSE:
                foreach ($selected_courses as $id_course) {
                    foreach ($selected_users as $id_user) {
                        if (isset($_POST['_' . $id_user . '_' . $id_course . '_'])) {
                            $associationsArr[$id_course][$id_user] = 1;
                        } else {
                            $associationsArr[$id_course][$id_user] = 0;
                        }
                    }
                }
                break;

            case AggregatedCertificate::AGGREGATE_CERTIFICATE_TYPE_COURSE_PATH:
                foreach ($selected_path as $id_coursepath) {
                    foreach ($selected_users as $id_user) {
                        if (isset($_POST['_' . $id_user . '_' . $id_coursepath . '_'])) {
                            $associationsArr[$id_coursepath][$id_user] = 1;
                        } else {
                            $associationsArr[$id_coursepath][$id_user] = 0;
                        }
                    }
                }
                break;
            default:
                break;
        }


        $res = $this->aggCertLib->saveCertAggregatedCert($associationsArr);

        Util::jump_to($this->back_url . '&res=' . ($res ? 'ok' : 'err'));


    }

    /**
     * ok
     *
     */
    function viewdetails()
    {

        require_once($GLOBALS['where_lms'] . '/lib/lib.coursepath.php');
        require_once($GLOBALS['where_lms'] . '/lib/lib.course.php');

        $acl_man =& Docebo::user()->getAclManager();


        $id_association = Get::req('id_association', DOTY_INT, 0);


        // Getting type of metacert. (if it's a metacert on course, on coursepath...)
        $type_assoc = Get::req('type_assoc', DOTY_INT, -1);

        $usersArr = $this->aggCertLib->getAllUsersFromIdAssoc($this->id_association, $type_assoc);
        $linksArr = $this->aggCertLib->getAllLinksFromIdAssoc($this->id_association, $type_assoc);

        //Table creation
        $tb = new Table(0, $this->association_name, $this->association_name);
        $tb->setLink('index.php?r=alms/' . $this->controller_name . '/' . $this->op['viewdetails'] . '&amp;id_certificate=' . $this->id_certificate . '&amp;id_association=' . $this->id_association);

        $type_h = ['', ''];
        $cont_h = [Lang::t('_FULLNAME'), Lang::t('_USERNAME')];
        $type_h[] = 'align_center';

        $man_courseuser = new Man_CourseUser(DbConn::getInstance());
        $coursePath_man = new CoursePath_Manager();
        if ($type_assoc === AggregatedCertificate::AGGREGATE_CERTIFICATE_TYPE_COURSE) {
            $course_man = new Man_Course();
            $course_info = $course_man->getAllCourses(false, false, $linksArr);
            foreach ($course_info as $course) {
                $cont_h[] = $course['code'] . ' - ' . $course['name'];
            }
        } else {

            $coursePathInfoArr = $coursePath_man->getCoursepathAllInfo($linksArr);
            foreach ($coursePathInfoArr as $coursePathInfo) {
                $cont_h[] = $coursePathInfo[COURSEPATH_CODE] . ' - ' . $coursePathInfo[COURSEPATH_NAME];
            }

        }
        $type_h[] = 'align_center';
        $cont_h[] = Lang::t('_META_CERTIFICATE_PROGRESS', 'certificate');

        $tb->setColsStyle($type_h);
        $tb->addHead($cont_h);


        $aclManager = new DoceboACLManager();
        $usersArr = array_map('intval', $aclManager->getArrUserST($usersArr));
        $status = $this->aggCertLib->getUserAndCourseFromIdAssoc($this->id_association, $type_assoc);

        foreach ($usersArr as $id_user) {

            $cont = [];

            $user_info = $acl_man->getUser($id_user, false);

            $cont[] = $user_info[ACL_INFO_LASTNAME] . ' ' . $user_info[ACL_INFO_FIRSTNAME];

            $cont[] = $acl_man->relativeId($user_info[ACL_INFO_USERID]);

            $total_course_assigned = 0;
            $total_course_ended = 0;

            foreach ($linksArr as $id_link) {
                if (!in_array($id_link, $status[$id_user])) {
                    $cont[] = Lang::t('_NOT_ASSIGNED');
                } else {
                    $total_course_assigned++;
                    switch ($type_assoc) {
                        case AggregatedCertificate::AGGREGATE_CERTIFICATE_TYPE_COURSE:
                            if ($man_courseuser->isEnrolled($id_user, $id_link)) {
                                if ($man_courseuser->hasCompletedCourses($id_user, [$id_link])) {
                                    $total_course_ended++;
                                    $cont[] = Lang::t('_CST_CONCLUDED', 'course');
                                } else {
                                    $cont[] = Lang::t('_NOT_ENDED', 'certificate');
                                }
                            } else {
                                $cont[] = Lang::t('_NOT_ENROLLED', 'certificate');
                            }
                            break;
                        case AggregatedCertificate::AGGREGATE_CERTIFICATE_TYPE_COURSE_PATH:
                        default:
                            if ($coursePath_man->isEnrolled($id_user, $id_link)) {
                                $courseIdsFromPath = $coursePath_man->getPathCourses($id_link);
                                if ($man_courseuser->hasCompletedCourses($id_user, $courseIdsFromPath)) {
                                    $total_course_ended++;
                                    $cont[] = Lang::t('_CST_CONCLUDED', 'course');
                                } else {
                                    $cont[] = Lang::t('_NOT_ENDED', 'certificate');
                                }
                            } else {
                                $cont[] = Lang::t('_NOT_ENROLLED', 'certificate');
                            }
                            break;
                    }
                }
            }

            $cont[] = $total_course_ended . ' / ' . $total_course_assigned;
            $tb->addBody($cont);

        }

        $params = [

            'controller_name' => $this->controller_name,
            'id_certificate' => $this->id_certificate,
            'tb' => $tb,
            'opsArr' => $this->op,
            'cert_name' => $this->cert_name

        ];

        $this->render($this->op['view_details'], $params);

    }

    function delAssociations()
    {
        checkPerm('mod');
        $type_assoc = Get::req('type_assoc', DOTY_INT, -1);
        if (Get::req('confirm', DOTY_INT, 0) === 1 && ($this->id_association !== 0)) {
            Util::jump_to($this->back_url . '&res=' . ($this->aggCertLib->deleteAssociations($this->id_association, $type_assoc) ? 'ok' : 'err'));
        }
    }


    /**
     * Assignment Management:
     *
     *   - Preview certificate
     *   - Release certificate
     *   - Delete released certificate
     *
     * ok
     *
     */
    function assignmentManagement()
    {
        checkPerm('mod');
        if ($this->id_certificate == 0) {
            cout(getErrorUi());
        }

        $certificate_assoc = $this->aggCertLib->getIssuedCertificates($this->id_certificate);
        if (!$certificate_assoc) {
            cout(getErrorUi(Lang::t('_NO_CERT_AVAILABLE', 'certificate')));
        }

        $type_h = ['', '', '', 'image', 'image', 'image'];

        $cont_h = [Lang::t('_FULLNAME'),
            Lang::t('_USERNAME'),
            Lang::t('_TITLE'),
            Get::img('course/certificate.png', Lang::t('_TAKE_A_COPY', 'certificate')),
            '<img src="' . getPathImage('lms') . 'standard/delete.png" alt="' . Lang::t('_DEL') . ' : ' . strip_tags($certificate['name']) . '" />'];
        $cert_name_caption = $this->cert_name;
        $tb = new Table(Get::sett('visuItem'), $cert_name_caption, $cert_name_caption);
        $tb->initNavBar('ini', 'button');
        $ini = $tb->getSelectedElement();
        $tb->setColsStyle($type_h);
        $tb->addHead($cont_h);
        $i = 0;
        foreach ($certificate_assoc as $the_cert) {
            $cell[$i][] = $the_cert['lastname'] . ' ' . $the_cert['firstname'];
            $cell[$i][] = $the_cert['userid'];
            $cell[$i][] = $the_cert['title'];
            $cell[$i][] = '<a href="index.php?r=alms/' . $this->controller_name . '/release_cert'
                . '&amp;id_certificate=' . $this->id_certificate
                . '&amp;id_user=' . $the_cert['idst']
                . '&amp;id_association=' . $the_cert['idAssociation']
                . '&amp;aggCert=1'
                . '">'
                . Get::img('course/certificate.png', Lang::t('_TAKE_A_COPY', 'certificate')) . '</a>';
            if ($the_cert['released'] > 0) {
                $cell[$i][] = '<a href="index.php?r=alms/' . $this->controller_name . '/' . $this->op['del_released']
                    . '&amp;id_certificate=' . $this->id_certificate
                    . '&amp;id_user=' . $the_cert['idst']
                    . '&amp;id_association=' . $the_cert['idAssociation']
                    . '">'
                    . Get::img('standard/delete.png', Lang::t('_DEL')) . '</a>';
            } else {
                $cell[$i][] = '';
            }
            $tb->addBody($cell[$i++]);
        }
        require_once(_base_ . '/lib/lib.dialog.php');
        setupHrefDialogBox('a[href*=' . $this->op['del_released'] . ']');

        $array_release_status = [
            Lang::t('_ALL') => '0',
            Lang::t('_ONLY_RELEASED', 'certificate') => '1',
            Lang::t('_ONLY_NOT_RELEASED', 'certificate') => '2'
        ];

        $params = [
            'release_status_arr' => $array_release_status,
            'id_certificate' => $this->id_certificate,
            'tb' => $tb,
            'type_h' => $type_h,
            'tot_element' => $tot_element,
            'cont_h' => $cont_h,
            'ini' => $ini,

            'opsArr' => $this->op,
        ];

        $params['controller_name'] = $this->controller_name;
        $this->render('assignmentManagement', $params);
    }


    /**
     *  ok
     *
     */
    function release_cert()
    {
        checkPerm('view');

        require_once Forma::inc(_lms_ . '/lib/lib.certificate.php');

        $id_user = Get::req('id_user', DOTY_INT, 0);
        $cert = new Certificate();
        $subs = $cert->getSubstitutionArray($id_user, $id_course, $this->id_association);
        $rs = $cert->send_certificate($this->id_certificate, $id_user, 0, $subs, true, false, $this->id_association);

        // the next nstruction is not called because of previous cert download; this functin need to be called trough Ajax Call, as soon as the main
        // cert table is build trough jquery datatable instead of the actuala table widget
        Util::jump_to($this->back_url);
    }

    /**
     * ok
     */
    function delReleased()
    {

        checkPerm('mod');

        require_once(_base_ . '/lib/lib.form.php');
        require_once(_base_ . '/lib/lib.upload.php');
        $id_user = Get::req('id_user', DOTY_INT, 0);

        if (Get::req('confirm', DOTY_INT, 0) === 1) {

            $cert_file = $this->aggCertLib->getAggregatedCertFileName($id_user, $this->id_certificate, $this->id_association);

            $path = '/appLms/certificate/';

            sl_open_fileoperations();
            $res = sl_unlink($path . $cert_file);
            sl_close_fileoperations();

            if (!$res) {
                Util::jump_to('index.php?r=alms/' . $this->controller_name . '/assignmentManagement&id_certificate=' . $this->id_certificate . '&result=err_del_cert');
            }

            $res = $this->aggCertLib->deleteReleasedCert($id_user, $this->id_certificate, $this->id_association);

            $back = 'index.php?r=alms/' . $this->controller_name . '/assignmentManagement&id_certificate=' . $this->id_certificate;
            Util::jump_to($back . '&result=' . ($res ? 'ok' : 'err_del_cert'));

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
     * OK
     */
    function delcertificate()
    {

        checkPerm('mod');

        if (Get::req('confirm', DOTY_INT, 0) == 1) {

            if ($this->aggCertLib->deleteCert($this->id_certificate)) {

                // Get all the associations with the cert.
                $idsAssocArr = $this->aggCertLib->getIdAssociations($this->id_certificate);

                if (!empty($idsAssocArr)) { // Cert. has some associations METADATA

                    $res = $this->aggCertLib->deleteAssociations($idsAssocArr);
                    Util::jump_to('index.php?r=alms/' . $this->controller_name . '/show&result=' . ($res ? 'ok' : 'err'));

                } else   // There aren't any associations
                {
                    Util::jump_to('index.php?r=alms/' . $this->controller_name . '/show&result=ok');
                }

            } else {
                Util::jump_to('index.php?r=alms/' . $this->controller_name . '/show&result=err');
            }
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
     *
     * ok
     */
    function manageCertificateFile($new_file_id, $old_file, $path, $delete_old, $is_image = false)
    {
        require_once(_base_ . '/lib/lib.upload.php');
        $arr_new_file = (isset($_FILES[$new_file_id]) && $_FILES[$new_file_id]['tmp_name'] != '' ? $_FILES[$new_file_id] : false);
        $return = ['filename' => $old_file,
            'new_size' => 0,
            'old_size' => 0,
            'error' => false,
            'quota_exceeded' => false];
        sl_open_fileoperations();
        if (($delete_old || $arr_new_file !== false) && $old_file != '') {

            // the flag for file delete is checked or a new file was uploaded ---------------------
            sl_unlink($path . $old_file);
        }

        if (!empty($arr_new_file)) {

            // if present load the new file --------------------------------------------------------
            $filename = $new_file_id . '_' . mt_rand(0, 100) . '_' . time() . '_' . $arr_new_file['name'];

            if (!sl_upload($arr_new_file['tmp_name'], $path . $filename)) {

                return false;
            } else {
                return $filename;
            }
        }

        // aggiungo condizione per evitare che vada a cancellare l'immagine pre esistente se non la si aggiorna.
        if (!$delete_old && $old_file) {
            return $old_file;
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
    function getTreeCategoryAsArray($idParent = 0)
    {

        // if courses have no parent category (like all the courses are under root), they will not be found!

        $nodesArr = $this->model->getPathsFromIdParent($idParent); // getting nodes with idParent

        if (count($nodesArr) > 0) {


            foreach ($nodesArr as $index => $node){ // Processing all nodes with idParent

                $nodesArr[$index]['text'] = end(explode('/', $nodesArr[$index]['text']));
                $nodesArr[$index]['idCategory'] = (int)$nodesArr[$index]['idCategory'];
                $nodesArr[$index]['level'] = (int)$nodesArr[$index]['level'];
                if (!$nodesArr[$index]['isLeaf']) {
                    $nodesArr[$index]['nodes'] = $this->getTreeCategoryAsArray($nodesArr[$index]['idCategory']);
                }
            }

            return $nodesArr;

        }
    }

    // ------------------ Ajax calls for datatable -------------------

    /**
     * Ajax call from view
     */
    function getCourseListTask()
    {

        if (isset($_POST['node'])) {

            echo $this->json->encode($this->aggCertLib->getCourseListFromIdCategory($_POST['node']));

        }

    }

    /**
     * Ajax call from view
     */
    function getCoursePathListTask()
    {

        echo $this->json->encode($this->aggCertLib->getCoursePathList());
        /*  require_once($GLOBALS['where_lms'].'/lib/lib.coursepath.php');

          $coursepathMan = new CoursePath_Manager();
          echo $this->json->encode($coursepathMan->getCoursepathList());
  */

    }

    /**
     * Ajax call from view
     */
    function getCatalogCourseListTask()
    {
        echo $this->json->encode($this->model->getCatalogCourse());
    }
}    