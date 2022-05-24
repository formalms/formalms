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

require_once Forma::inc(_lms_ . '/class.module/learning.object.php');

define('_scorm_basepath', $GLOBALS['where_lms'] . '/modules/scorm/');

class Learning_ScormOrg extends Learning_Object
{
    public $idParams;
    public $obj_type;
    public $back_url;

    /**
     * object constructor.
     **/
    public function __construct($id = null, $environment = false)
    {
        parent::__construct($id, $environment);

        $title = '';
        $res = $this->db->query('SELECT title FROM %lms_scorm_organizations WHERE idscorm_organization = ' . (int) $id . '');
        if ($res && $this->db->num_rows($res) > 0) {
            $this->isPhysicalObject = true;
            list($title) = $this->db->fetch_row($res);
        }

        $this->idAuthor = '';
        $this->title = $title;
        $this->obj_type = 'scormorg';
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getObjectType()
    {
        return $this->obj_type;
    }

    public function getBackUrl()
    {
        return $this->back_url;
    }

    public function getParamInfo()
    {
        $params = parent::getParamInfo();

        $params = array_merge($params, [
            ['label' => 'Autoplay', 'param_name' => 'autoplay'], ['label' => 'Template', 'param_name' => 'playertemplate'],
        ]);

        return $params;
    }

    public function renderCustomSettings($arrParams, $form, $lang)
    {
        $out = parent::renderCustomSettings($arrParams, $form, $lang);

        $autoplay = isset($arrParams['autoplay']) ? $arrParams['autoplay'] : '1';
        if ($arrParams['autoplay'] == '') {
            $autoplay = '1';
        }

        $out .= $form->getRadioSet($lang->def('_AUTOPLAY'),
                                    'autoplay',
                                    'autoplay',
                                    [$lang->def('_NO') => '0',
                                            $lang->def('_YES') => '1', ],
                                    $autoplay
                                );

        /* ------ dropdown template choiche ----- */
        $arr_templates = [];

        $path = _templates_ . '/' . getTemplate() . '/player_scorm/';
        $templ = @dir($path);
        if ($templ) {
            while ($elem = $templ->read()) {
                if ((is_dir($path . $elem)) && ($elem != '.') && ($elem != '..') && ($elem != '.svn') && $elem[0] != '_') {
                    $arr_templates[$elem] = $elem;
                }
            }
            closedir($templ->handle);
        }
        $template = isset($arrParams['playertemplate']) ? $arrParams['playertemplate'] : 'default';
        $out .= $form->getDropdown(Lang::t('_PLAYERTEMPLATE', 'scorm', 'lms'),//$lang->def( '_PLAYERTEMPLATE'),
                                    'playertemplate',
                                    'playertemplate',
                                    $arr_templates,
                                    $template
                                );
        /* -------------------------------------- */
        return $out;
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
     **/
    public function create($back_url)
    {
        $this->back_url = $back_url;

        Forma::removeErrors();

        require_once Forma::inc(_lms_ . '/modules/scorm/scorm.php');
        additem($this);
    }

    /**
     * function edit.
     *
     * @param int    $id       contains the resource id
     * @param string $back_url contains the back url
     *
     * @return nothing
     *                 attach in back_url id_lo that is passed $id and attach edit_result with the result of operation in boolean format
     **/
    public function edit($id, $back_url)
    {
        $this->id = $id;
        $this->back_url = $back_url;

        Forma::removeErrors();

        require_once Forma::inc(_lms_ . '/modules/scorm/scorm.php');
        moditem($this);
    }

    /**
     * function play( $id, $id_param, $back_url ).
     *
     * @param int    $id       contains the resource id
     * @param int    $id_param contains the id needed for params retriving
     * @param string $back_url contain the back url
     *
     * @return nothing return
     **/
    public function play($id, $id_param, $back_url)
    {
        require_once Forma::inc(_lms_ . '/lib/lib.param.php');
        require_once Forma::inc(_lms_ . '/modules/scorm/scorm.php');
        $idReference = getLOParam($id_param, 'idReference');
        $autoplay = getLOParam($id_param, 'autoplay');
        $playertemplate = getLOParam($id_param, 'playertemplate');
        play($id, $idReference, $back_url, $autoplay, $playertemplate);
        //Util::jump_to( 'index.php?modname=scorm&op=play&idscorm_organization='.$this->idResource
        //		.'&idReference='.$idReference);
    }

    public function env_play($id_reference, $back_url, $options = [])
    {
        require_once Forma::inc(_lms_ . '/modules/scorm/scorm.php');
        $this->id_reference = $id_reference;
        $this->back_url = $back_url;
        play($this->id, $id_reference, $back_url, true, 'default', $this->environment);
    }

    /**
     * function del.
     *
     * @param int    $id       contains the resource id
     * @param string $back_url contains the back url (not used yet)
     *
     * @return false if fail, else return the id lo
     **/
    public function del($id, $back_url = null)
    {
        require_once Forma::inc(_lms_ . '/modules/scorm/scorm.php');

        list($idscorm_package) = sql_fetch_row(sql_query('
		SELECT idscorm_package 
		FROM ' . $GLOBALS['prefix_lms'] . "_scorm_organizations
		WHERE idscorm_organization = '" . (int) $id . "'"));

        _scorm_deleteitem($idscorm_package, (int) $id);

        return $id;
    }

    /**
     * function copy( $id, $back_url ).
     *
     * @param int    $id       contains the resource id
     * @param string $back_url contain the back url (not used yet)
     *
     * @return int $id if success FALSE if fail
     **/
    public function copy($id, $back_url = null)
    {
        require_once Forma::inc(_lms_ . '/modules/scorm/scorm.php');

        list($idscorm_package) = sql_fetch_row(sql_query('
		SELECT idscorm_package 
		FROM ' . $GLOBALS['prefix_lms'] . "_scorm_organizations
		WHERE idscorm_organization = '" . (int) $id . "'"));

        return _scorm_copyitem($idscorm_package, $id);
    }

    /**
     * function getMultipleResource( $idMultiResource ).
     *
     * @param int $idMultiResource identifier of the multi resource
     *
     * @return array an array with the ids of all resources
     **/
    public function getMultipleResource($idMultiResource)
    {
        $arrMultiResources = [];
        $rs = sql_query('SELECT idscorm_organization '
                            . ' FROM %lms_scorm_organizations '
                            . " WHERE idscorm_package = '" . (int) $idMultiResource . "'");
        while (list($idscorm_organization) = sql_fetch_row($rs)) {
            $arrMultiResources[] = $idscorm_organization;
        }

        return $arrMultiResources;
    }

    public function canBeMilestone()
    {
        return true;
    }

    /**
     * function search( $key ).
     *
     * @param string $key contains the keyword to search
     *
     * @return array with results found
     **/
    public function search($key)
    {
        $output = false;
        $query = "SELECT * FROM %lms_scorm_organizations WHERE title LIKE '%" . $key . "%' ORDER BY title";
        $res = $this->db->query($query);
        $results = [];
        if ($res) {
            $output = [];
            while ($row = $this->db->fetch_obj($res)) {
                $output[] = [
                    'id' => $row->idscorm_organization,
                    'title' => $row->title,
                    'description' => '',
                ];
            }
        }

        return $output;
    }

    public function showInLightbox()
    {
        return true;
    }

    public function hasDetailedTrackings()
    {
        return true;
    }

    public function trackDetails($user, $org)
    {
        require_once Forma::inc(_lms_ . '/modules/organization/orgresults.php');
        getTrackingTable($user, $org);
    }
}
