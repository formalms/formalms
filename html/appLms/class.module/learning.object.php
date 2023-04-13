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

class Learning_Object
{
    public $id;
    public $environment = 'course_lo';
    public $id_reference = false;

    public $idAuthor;

    public $title;
    public $obj_type = 'object';

    public $db;
    public $aclManager;
    public $table;

    public $isPhysicalObject = false;
    public $no_restrictions = false;

    public $plugin_manager;

    public $session;

    /**
     * function learning_Object()
     * class constructor.
     **/
    public function __construct($id = null, $environment = false)
    {
        $this->id = $id;
        $this->environment = ($environment ? $environment : 'course_lo');

        $this->idAuthor = '';
        $this->title = '';

        $this->db = DbConn::getInstance();
        $this->aclManager = Forma::user()->getAclManager();
        $this->table = '';

        $this->plugin_manager = new PluginManager('LearningObject');
        $this->session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    }

    public function getIdParam($env = false)
    {
        $res = false;

        $env = ($env ? $env : $this->environment);
        switch ($env) {
            case 'communication' :
                return 0;
             break;
            case 'games' :
                return 0;
             break;
            case 'course_lo' :
            default:
                $qtxt = 'SELECT idParam FROM %lms_organization WHERE idResource = ' . (int) $this->id . " AND objectType = '" . $this->getObjectType() . "' ";
             break;
        }

        if (!empty($qtxt)) {
            $re = $this->db->query($qtxt);
            list($id_param) = $this->db->fetch_row($re);
            $res = $id_param;
        }

        return $res;
    }

    /**
     * function getId().
     *
     * @return int resource id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * function getIdAuthor().
     *
     * @return int resource author id
     */
    public function getIdAuthor()
    {
        return $this->idAuthor;
    }

    /**
     * function getTitle().
     *
     * @return string title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * function getObjectType().
     *
     * @return string Learning_Object type
     */
    public function getObjectType()
    {
        return $this->obj_type;
    }

    /**
     * function create( $back_url ).
     *
     * @param string $back_url contains the back url
     *
     * @return bool TRUE if success FALSE if fail
     *              attach the id of the created object at the end of back_url with the name id_lo
     *
     * static
     */
    public function create($back_url)
    {
    }

    /**
     * function edit.
     *
     * @param int    $id       contains the resource id
     * @param string $back_url contains the back url
     *
     * @return bool TRUE if success FALSE if fail
     *              attach in back_url id_lo that is passed $id and attach edit_result with the result of operation in boolean format
     */
    public function edit($id, $back_url)
    {
    }

    /**
     * function del.
     *
     * @param int    $id       contains the resource id
     * @param string $back_url contains the back url (not used yet)
     *
     * @return int $id if success FALSE if fail
     */
    public function del($id, $back_url = null)
    {
    }

    /**
     * function copy( $id, $back_url ).
     *
     * @param int    $id       contains the resource id
     * @param string $back_url contain the back url (not used yet)
     *
     * @return int $id if success FALSE if fail
     */
    public function copy($id, $back_url = null)
    {
    }

    /**
     * function getParamInfo()
     * return array of require params for play.
     *
     * @return an example of associative array returned is:
     *            [0] => (
     *            ['label'] => _DEFINITION,
     *            ['param_name'] => parameter name;
     *            ),
     *            [1] = >(
     *            ['label'] => _DEFINITION,
     *            ['param_name'] => parameter name;
     *            ) ...
     */
    public function getParamInfo()
    {
        $params = [];
        $this->plugin_manager->run('getParamInfo', [$this, &$params]);

        return $params;
    }

    public function renderCustomSettings($arrParams, $form, $lang)
    {
        $out = '';
        $this->plugin_manager->run('renderCustomSettings', [$this, $arrParams, &$out]);

        require_once _adm_ . '/lib/lib.customfield.php';
        $fman = new CustomFieldList();
        $fman->setFieldArea('LO_OBJECT');
        $id_obj = $arrParams['idReference'];
        $fields_mask = $fman->playFields($id_obj);
        $out .= $fields_mask;

        return $out;
    }

    /**
     * function play( $id, $id_param, $back_url ).
     *
     * @param int    $id       contains the resource id
     * @param int    $id_param contains the id needed for params retriving
     * @param string $back_url contain the back url
     *
     * @return nothing return
     */
    public function play($id, $id_param, $back_url)
    {
    }

    /**
     * function import( $source, $back_url ) NOT IMPLEMENTED YET.
     *
     * @param string $source contains the filename
     *
     * @return bool TRUE if success FALSE if fail
     *              if operation success attach the new id at the back url with the name id_lo
     */
    public function import($source, $back_url)
    {
    }

    /**
     * function export( $id, $format, $back_url ) NOT IMPLEMENTED YET.
     *
     * @param string $id       contain resource id
     * @param string $format   contain output format
     * @param string $back_url contain the back url
     *
     * @return bool TRUE if success FALSE if fail
     */
    public function export($id, $format, $back_url)
    {
    }

    /**
     * function getMultipleResource( $idMultiResource ).
     *
     * @param int $idMultiResource identifier of the multi resource
     *
     * @return array an array with the ids of all resources
     */
    public function getMultipleResource($idMultiResource)
    {
        return [];
    }

    /**
     * function canBeMilestone().
     *
     * @return true if this object can be a milestone
     *              FALSE otherwise
     */
    public function canBeMilestone()
    {
        return false;
    }

    /**
     * @param string $key contains the keyword to search
     *
     * @return array with results found
     */
    public function search($key)
    {
        return [];
    }

    public function setNoRestrictions($val)
    {
        $this->no_restrictions = (bool) $val;
    }

    public function checkObjPerm()
    {
        if (!$this->no_restrictions) {
            //die();
        }
    }

    /**
     * @param $visible
     */
    public function setVisibileInCoursereportDetail($visible)
    {
    }

    public function showInLightbox()
    {
        return false;
    }

    public function canBeCategorized()
    {
        return true;
    }
}
