<?php defined('IN_FORMA') or exit('Direct access is forbidden.');

define('SMS_GROUP', 11);
define('TWIG_GROUP', 13);

class SettingAdm extends Model
{
    protected $db;

    protected $table;

    public function __construct()
    {
        $this->db = DbConn::getInstance();
        $this->table = '%adm_setting';
        parent::__construct();
    }

    public function getPerm()
    {
        return ['view' => 'standard/view.png'];
    }

    /**
     * @return array this array contains association trought id and name of the regroup units
     */
    public function getRegroupUnit()
    {
        $re_regroup = sql_query('SELECT DISTINCT regroup
		FROM ' . $this->table . "
		WHERE hide_in_modify = '0'
		ORDER BY regroup ");

        // $event = new \appCore\Events\Core\ConfigGetRegroupUnitsEvent();

        $names = [
            1 => 'Main_options',
            3 => 'User',
            4 => 'conf_lms',
            6 => 'Videoconference',
            7 => 'Ldap',
            8 => 'Advanced',
            5 => 'Ecommerce',
            9 => 'Api_SSO',
            10 => 'Google',
            11 => 'Sms',
            12 => 'Social',
            13 => 'Twig',
        ];

        if (SmtpAdm::isEnabledDatabase()) {
            $names[SmtpAdm::SMTP_GROUP] = 'Smtp Settings';
        }

        // $event->setGroupUnits($names);
        // \appCore\Events\DispatcherManager::dispatch(\appCore\Events\Core\ConfigGetRegroupUnitsEvent::EVENT_NAME, $event);
        // $names = $event->getGroupUnits();

        $eventNames = Events::trigger('core.config.get_group_units', ['names' => $names]);
        $names = $eventNames['names'];

        $group = [];
        foreach ($re_regroup as $row) {
            if (array_key_exists($row['regroup'], $names)) {
                $group[$row['regroup']] = $names[$row['regroup']];
            }
        }

        //$group['suite_man'] = 'suite_man';

        return $group;
    }

    public function server_info()
    {
        $lang = &DoceboLanguage::createInstance('configuration', 'framework');

        $php_conf = ini_get_all();

        $intest = '<div>'
            . '<div class="label_effect">';

        $html = '<div class="conf_line_title">' . $lang->def('_SERVERINFO') . '</div>'
            . config_line($lang->def('_SERVER_ADDR'), $_SERVER['SERVER_ADDR'])
            . config_line($lang->def('_SERVER_PORT'), $_SERVER['SERVER_PORT'])
            . config_line($lang->def('_SERVER_NAME'), $_SERVER['SERVER_NAME'])
            . config_line($lang->def('_SERVER_ADMIN'), $_SERVER['SERVER_ADMIN'])
            . config_line($lang->def('_SERVER_SOFTWARE'), $_SERVER['SERVER_SOFTWARE'])
            . '<br />'

            . '<div class="conf_line_title">' . $lang->def('_SERVER_MYSQL') . '</div>'
            . config_line($lang->def('_MYSQL_VERS'), sql_get_server_info())
            . '<br />'

            . '<div class="conf_line_title">' . $lang->def('_PHPINFO') . '</div>'
            . config_line($lang->def('_PHPVERSION'), phpversion())
            . config_line($lang->def('_SAFEMODE'), ($php_conf['safe_mode']['local_value']
                ? $lang->def('_ON')
                : $lang->def('_OFF')))
            . config_line($lang->def('_REGISTER_GLOBAL'), ($php_conf['register_globals']['local_value']
                ? $lang->def('_ON')
                : $lang->def('_OFF')))
            . config_line($lang->def('_MAGIC_QUOTES_GPC'), ($php_conf['magic_quotes_gpc']['local_value']
                ? $lang->def('_ON')
                : $lang->def('_OFF')))
            . config_line($lang->def('_UPLOAD_MAX_FILESIZE'), $php_conf['upload_max_filesize']['local_value'])
            . config_line($lang->def('_POST_MAX_SIZE'), $php_conf['post_max_size']['local_value'])
            . config_line($lang->def('_MAX_EXECUTION_TIME'), $php_conf['max_execution_time']['local_value'] . 's')
            . config_line($lang->def('_LDAP'), (extension_loaded('ldap')
                ? $lang->def('_ON')
                : '<span class="font_red">' . $lang->def('_OFF') . ' ' . $lang->def('_USEFULL_ONLY_IF') . '</span>'))
            . config_line($lang->def('_PHP_TIMEZONE'), @date_default_timezone_get());

        if (version_compare(phpversion(), '5.0.0') == -1) {
            echo config_line($lang->def('_DOMXML'), (extension_loaded('domxml')
                ? $lang->def('_ON')
                : '<span class="font_red">' . $lang->def('_OFF') . ' (' . $lang->def('_NOTSCORM') . ')</span>'));
        }
        if (version_compare(phpversion(), '5.2.0', '>')) {
            echo config_line($lang->def('_ALLOW_URL_INCLUDE'), ($php_conf['allow_url_include']['local_value']
                ? '<span class="font_red">' . $lang->def('_ON') . '</span>'
                : $lang->def('_OFF')));
        }
        if (FormaLms\lib\Get::cfg('uploadType') == 'ftp') {
            if (function_exists('ftp_connect')) {
                require_once _base_ . '/lib/lib.upload.php';
                $re_con = sl_open_fileoperations();
                echo config_line($lang->def('_UPLOADFTP'), ($re_con
                    ? $lang->def('_FTPOK')
                    : '<span class="font_red">' . $lang->def('_FTPERR') . '</span>'));
                if ($re_con) {
                    sl_close_fileoperations();
                }
            } else {
                echo config_line($lang->def('_UPLOADFTP'), '<span class="font_red">' . $lang->def('_FTPERR') . '</span>');
            }
        }
        echo '<div class="nofloat"></div><br />';

        return $html;
    }

    /**
     * Draw the mask for the template manager, i hope to remove it from here.
     *
     * @return <string>
     */
    public function _maskSuiteManager()
    {
        require_once _base_ . '/lib/lib.form.php';
        require_once _base_ . '/lib/lib.platform.php';

        $lang = &DoceboLanguage::createInstance('configuration', 'framework');
        $plat_man = &PlatformManager::createInstance();

        $all_platform = $plat_man->getPlatformsInfo();
        $code_list_home = [];

        $html = Form::getOpenFieldset($lang->def('_LOAD_UNLOAD_PLATFORM'));
        reset($all_platform);
        foreach ($all_platform as $code => $info) {
            if ($info['hidden_in_config'] != 'true') {
                $code = $info['platform'];
                echo Form::getCheckbox(
                    $info['name'],
                    'activate_platform_' . $code,
                    'activate_platform[' . $code . ']',
                    1,
                    ($info['is_active'] == 'true'),
                    ($info['mandatory'] == 'true' ? ' disabled="disabled"' : '')
                );

                if ($info['is_active'] == 'true') {
                    $code_list_home[$code] = $info['name'];
                }
            }
        }
        unset($code_list_home['scs']);
        unset($code_list_home['framework']);

        echo Form::getCloseFieldset();
        echo Form::getDropdown(
            $lang->def('_HOME_PLATFORM'),
            'platform_in_home',
            'platform_in_home',
            $code_list_home,
            $plat_man->getHomePlatform()
        );

        return '';
    }

    /**
     * Draw the mask for the template manager, i hope to remove it from here.
     *
     * @return <string>
     */
    public function _saveSuiteManager()
    {
        require_once _base_ . '/lib/lib.platform.php';

        $plat_man = &PlatformManager::createInstance();

        $all_platform = $plat_man->getPlatformsInfo();
        $re = true;

        reset($all_platform);
        foreach ($all_platform as $code => $info) {
            if ($info['hidden_in_config'] != 'true') {
                $code = $info['platform'];
                if (isset($_POST['activate_platform'][$code])) {
                    $re &= $plat_man->activatePlatform($code);
                    $code_list_home[$code] = $info['name'];
                } elseif ($info['mandatory'] == 'false') {
                    $re &= $plat_man->deactivatePlatform($code);
                }
            }
        }
        if (isset($code_list_home[$_POST['platform_in_home']])) {
            $re &= $plat_man->putInHome($_POST['platform_in_home']);
        }

        return $re;
    }

    /**
     * REturnes the displayable information for a selected group.
     *
     * @return string
     */
    public function printPageWithElement($regroup, $includeDisabledPlugins = false)
    {
        $i_after = null;
        $maxlenght = null;
        require_once _base_ . '/lib/lib.form.php';

        if ($regroup == 'suite_man') {
            return $this->_maskSuiteManager();
        }

        $groups = sql_query("SELECT DISTINCT(pack) FROM core_setting WHERE regroup = '$regroup' AND hide_in_modify = '0' ORDER BY pack");
        echo '<div class="row">';
        while (list($groupPack) = sql_fetch_row($groups)) {
            echo '<div class="col-sm-6">';
            if ($groupPack) {
                echo '<h3>' . Lang::t('_' . strtoupper($groupPack), 'configuration') . '</h3>';
            } else {
                echo '<h3>' . Lang::t('MAIN_SET_' . strtoupper($regroup), 'configuration') . '</h3>';
            }

            $reSetting = sql_query('
			SELECT pack, param_name, param_value, value_type, max_size
			FROM ' . $this->table . "
			WHERE pack = '$groupPack' AND regroup = '$regroup' AND hide_in_modify = '0'
			ORDER BY sequence");

            foreach ($reSetting as $row) {
                $pack = $row['pack'];
                $var_name = $row['param_name'];
                $var_value = $row['param_value'];
                $value_type = $row['value_type'];
                $max_size = $row['max_size'];
                //while (list($pack, $var_name, $var_value, $value_type, $max_size) = sql_fetch_row($reSetting)) {
                switch ($value_type) {
                    case 'register_type':
                        $layout = [
                            'self' => Lang::t('_REGISTER_TYPE_SELF', 'configuration'),
                            'self_optin' => Lang::t('_REGISTER_TYPE_SELF_OPTIN', 'configuration'),
                            'moderate' => Lang::t('_REGISTER_TYPE_MODERATE', 'configuration'),
                            'admin' => Lang::t('_REGISTER_TYPE_ADMIN', 'configuration'),
                        ];
                        echo Form::getDropdown(
                            Lang::t('_' . strtoupper($var_name), 'configuration'),
                            $var_name,
                            'option[' . $var_name . ']',
                            $layout,
                            $var_value
                        );
                        break;
                    case 'registration_code_type':
                        $layout = [
                            '0' => Lang::t('_NONE', 'configuration'),
                            'tree_man' => Lang::t('_ASK_FOR_MANUAL_TREE_CODE', 'configuration'),
                            'tree_drop' => Lang::t('_ASK_FOR_DROPDOWN_TREE_CODE', 'configuration'),
                            // "tree_course" => Lang::t('_ASK_FOR_TREE_COURSE_CODE'),
                            'code_module' => Lang::t('_ASK_FOR_CODE_MODULE', 'configuration'),
                        ];
                        echo Form::getDropdown(
                            Lang::t('_' . strtoupper($var_name), 'configuration'),
                            $var_name,
                            'option[' . $var_name . ']',
                            $layout,
                            $var_value
                        );
                        break;
                    case 'save_log_attempt':
                        //on off

                        echo Form::getOpenCombo(Lang::t('_' . strtoupper($var_name), 'configuration'))
                            . Form::getLineRadio(
                                '',
                                'label_bold',
                                Lang::t('_SAVE_LA_ALL', 'configuration'),
                                $var_name . '_all',
                                'option[' . $var_name . ']',
                                'all',
                                ($var_value == 'all')
                            )
                            . Form::getLineRadio(
                                '',
                                'label_bold',
                                Lang::t('_SAVE_LA_AFTER_MAX', 'configuration'),
                                $var_name . '_after_max',
                                'option[' . $var_name . ']',
                                'after_max',
                                ($var_value == 'after_max')
                            )
                            . Form::getLineRadio(
                                '',
                                'label_bold',
                                Lang::t('_NO', 'configuration'),
                                $var_name . '_no',
                                'option[' . $var_name . ']',
                                'no',
                                ($var_value == 'no')
                            )
                            . Form::getCloseCombo($i_after);
                        break;
                    case 'profile_modify':
                        echo Form::getOpenCombo(Lang::t('_' . strtoupper($var_name), 'configuration'))
                            . Form::getLineRadio(
                                '',
                                'label_bold',
                                Lang::t('_ALLOW_FULL_EDIT_PROFILE', 'configuration'),
                                $var_name . '_allow',
                                'option[' . $var_name . ']',
                                'allow',
                                ($var_value == 'allow')
                            )
                            . Form::getLineRadio(
                                '',
                                'label_bold',
                                Lang::t('_MOD_LIMIT_PROFILE', 'configuration'),
                                $var_name . '_limit',
                                'option[' . $var_name . ']',
                                'limit',
                                ($var_value == 'limit')
                            )
                            . Form::getLineRadio(
                                '',
                                'label_bold',
                                Lang::t('_DISALLOW_EDIT_PROFILE', 'configuration'),
                                $var_name . '_disallow',
                                'option[' . $var_name . ']',
                                'disallow',
                                ($var_value == 'disallow')
                            )
                            . Form::getLineRadio(
                                '',
                                'label_bold',
                                Lang::t('_REDIRECT_URL_PROFILE', 'configuration'),
                                $var_name . '_redirect',
                                'option[' . $var_name . ']',
                                'redirect',
                                ($var_value == 'redirect')
                            )
                            . Form::getCloseCombo($i_after);
                        break;
                    case 'language':
                        //drop down language
                        $langs = Docebo::langManager()->getAllLangCode();
                        echo Form::getDropdown(
                            Lang::t('_' . strtoupper($var_name), 'configuration'),
                            $var_name,
                            'option[' . $var_name . ']',
                            $langs,
                            array_search($var_value, $langs),
                            $i_after
                        );
                        break;
                    case 'template':
                        //drop down template
                        $templ = getTemplateList();
                        echo Form::getDropdown(
                            Lang::t('_' . strtoupper($var_name), 'configuration'),
                            $var_name,
                            'option[' . $var_name . ']',
                            $templ,
                            array_search($var_value, $templ),
                            $i_after
                        );
                        break;
                    case 'template_domain_node':
                        //drop down template + domain + node with repeater
                        $templ = getTemplateList();
                        $templates = [];
                        foreach ($templ as $k => $v) {
                            if ($v != 'standard') {
                                $templates[$v] = $v;
                            }
                        }

                        $uma = new UsermanagementAdm();
                        $tree_names = $uma->getAllFolders(false);
                        $nodes = [];
                        foreach ($tree_names as &$node) {
                            $node_name = $node->translation ?: $node->code;
                            $nodes[$node->idOrg] = addslashes($node_name);
                        }

                        asort($nodes);
                        $nodes[0] = Lang::t('_SELECT_NODE', 'configuration');

                        echo '<div id="' . $var_name . '_body" style="margin-top: 2rem;">
								<h3>' . Lang::t('_' . strtoupper($var_name), 'configuration') . '</h3>
								<div class="form_line_l">
									<button id="' . $var_name . '_add" type="button" class="btn btn-primary">' . Lang::t('_ADD', 'standard') . '</button>
								</div>';

                        $row_item = '<br><div class="row form_line_l">'
                            . '<div class="col-md-4"><p><label>' .
                            Lang::t('_URL', 'standard') . '</label></p>'
                            . Form::getInputTextfield(
                                null,
                                null,
                                'option[' . $var_name . '][domain][]',
                                '',
                                null,
                                $maxlenght,
                                null
                            )
                            . '</div>'
                            . Form::getLineDropdown(
                                'col-md-3',
                                null,
                                Lang::t('_TEMPLATE', 'certificate'),
                                'select',
                                null,
                                'option[' . $var_name . '][template][]',
                                $templates,
                                null,
                                null,
                                $i_after,
                                null
                            )
                            . Form::getLineDropdown(
                                'col-md-4',
                                null,
                                Lang::t('_DIRECTORY_MEMBERTYPETREE', 'admin_directory'),
                                'select',
                                null,
                                'option[' . $var_name . '][node][]',
                                $nodes,
                                null,
                                null,
                                $i_after,
                                null
                            )
                            . '<div class="col-md-1"><p><label>&nbsp;</label></p><button type="button" class="btn btn-danger"><i class="fa fa-close"></i></button>'
                            . '</div>'
                            . '</div>';

                        $row_item = str_replace(["\r", "\n"], '', $row_item); ?>
                        </div>

                        <script>
                            var content = $("#<?php echo $var_name; ?>_body");
                            var item = '<?php echo $row_item; ?>';

                            $(function () {
                                <?php $var_value = str_replace(["\r", "\n"], '', $var_value); ?>
                                var values = JSON.parse('<?php echo $var_value ?: '[]'; ?>');
                                var num_rows = values.length;

                                for (var i = 0; i < num_rows; i++) {
                                    var value = values[i];
                                    content.append(item);
                                    $("#template_domain_body .row:last-child select[name='option[template_domain][template][]']").val(value.template);
                                    $("#template_domain_body .row:last-child select[name='option[template_domain][node][]']").val(value.node);
                                    $("#template_domain_body .row:last-child input[name='option[template_domain][domain][]']").val(value.domain);

                                    $("#template_domain_body .row .btn-danger").click(function (e) {
                                        $(e.target).closest('.row').remove();
                                    });
                                }
                                ;

                                $("#<?php echo $var_name; ?>_add").click(function (e) {
                                    var num_rows = $("#template_domain_body .row").length;
                                    content.append(item);

                                    $("#template_domain_body .row .btn-danger").click(function (e) {
                                        $(e.target).closest('.row').remove();
                                    });
                                });
                            });
                        </script>
                        <?php
                        break;
                    case 'hteditor':
                        //drop down hteditor
                        $ht_edit = getHTMLEditorList();
                        echo Form::getDropdown(
                            Lang::t('_' . strtoupper($var_name), 'configuration'),
                            $var_name,
                            'option[' . $var_name . ']',
                            $ht_edit,
                            $var_value,
                            $i_after
                        );
                        break;
                    case 'layout_chooser':
                        //drop down hteditor
                        $layout = [
                            'left' => Lang::t('_LAYOUT_LEFT'),
                            'over' => Lang::t('_LAYOUT_OVER'),
                            'right' => Lang::t('_LAYOUT_RIGHT'),
                        ];
                        echo Form::getDropdown(
                            Lang::t('_' . strtoupper($var_name), 'configuration'),
                            $var_name,
                            'option[' . $var_name . ']',
                            $layout,
                            $var_value,
                            $i_after
                        );
                        break;
                    case 'pubflow_method_chooser':
                        //drop down hteditor
                        $options = [
                            'onestate' => Lang::t('_PUBFLOW_ONESTATE'),
                            'twostate' => Lang::t('_PUBFLOW_TWOSTATE'),
                            'advanced' => Lang::t('_PUBFLOW_ADVANCED'),
                        ];
                        echo Form::getDropdown(
                            Lang::t('_' . strtoupper($var_name), 'configuration'),
                            $var_name,
                            'option[' . $var_name . ']',
                            $options,
                            $var_value,
                            $i_after
                        );
                        break;
                    case 'field_select':
                        require_once _adm_ . '/lib/lib.field.php';

                        $fl = new FieldList();
                        $all_fields = $fl->getAllFields();
                        $fields = [];
                        $fields[''] = Lang::t('_NO_VALUE');
                        foreach ($all_fields as $key => $val) {
                            $fields[$val[FIELD_INFO_ID]] = $val[FIELD_INFO_TRANSLATION];
                        }
                        echo Form::getDropdown(
                            Lang::t('_' . strtoupper($var_name), 'configuration'),
                            $var_name,
                            'option[' . $var_name . ']',
                            $fields,
                            $var_value,
                            $i_after
                        );

                        break;
                    case 'sel_sms_gateway':
                        $options = [
                            '0' => Lang::t('_SMS_GATEWAY_AUTO'),
                            '1' => Lang::t('_SMS_GATEWAY_1'),
                            '2' => Lang::t('_SMS_GATEWAY_2'),
                            '3' => Lang::t('_SMS_GATEWAY_3'),
                            '4' => Lang::t('_SMS_GATEWAY_4'),
                        ];
                        echo Form::getDropdown(
                            Lang::t('_' . strtoupper($var_name), 'configuration'),
                            $var_name,
                            'option[' . $var_name . ']',
                            $options,
                            $var_value,
                            $i_after
                        );

                        break;
                    case 'layout_chooser':
                        //drop down hteditor
                        $layout = [
                            'left' => Lang::t('_LAYOUT_LEFT'),
                            'over' => Lang::t('_LAYOUT_OVER'),
                            'right' => Lang::t('_LAYOUT_RIGHT'),
                        ];
                        echo Form::getDropdown(
                            Lang::t('_' . strtoupper($var_name), 'configuration'),
                            $var_name,
                            'option[' . $var_name . ']',
                            $layout,
                            $var_value,
                            $i_after
                        );
                        break;
                    case 'grpsel_chooser':
                        $layout = [
                            'group' => Lang::t('_GROUPS', 'configuration'),
                            'orgchart' => Lang::t('_ORGCHART', 'configuration'),
                        ];
                        echo Form::getDropdown(
                            Lang::t('_' . strtoupper($var_name), 'configuration'),
                            $var_name,
                            'option[' . $var_name . ']',
                            $layout,
                            $var_value,
                            $i_after
                        );
                        break;
                    case 'tablist_mycourses':
                        $arr_value = explode(',', $var_value);
                        $tab_list = [];
                        $tab_list['status'] = Lang::t('_STATUS');
                        $tab_list['name'] = Lang::t('_NAME');
                        $tab_list['code'] = Lang::t('_CODE');

                        echo '<div class="form_line_l"><p>' .
                            '<label class="floating">' . Lang::t('_' . strtoupper($var_name), 'configuration') . '</label></p>';
                        for ($i = 0; $i < 3; ++$i) {
                            echo Form::getInputDropdown(
                                'dropdown',
                                $var_name . '_' . $i,
                                "mycourses[$i]",
                                $tab_list,
                                (isset($arr_value[$i]) ? $arr_value[$i] : ''),
                                ''
                            );
                        }
                        echo $i_after . '</div>';
                        break;
                    case 'point_field':
                        require_once _adm_ . '/lib/lib.field.php';
                        $fl = new FieldList();
                        $all_fields = $fl->getAllFields();
                        $fields[0] = Lang::t('_NO_VALUE', 'configuration');
                        foreach ($all_fields as $key => $val) {
                            $fields[$val[FIELD_INFO_ID]] = $val[FIELD_INFO_TRANSLATION];
                        }
                        echo Form::getDropdown(
                            Lang::t('_' . strtoupper($var_name), 'configuration'),
                            $var_name,
                            'option[' . $var_name . ']',
                            $fields,
                            $var_value,
                            $i_after
                        );

                        break;
                    case 'rest_auth_sel_method':
                        $value_set = [
                            Lang::t('_REST_AUTH_CODE', 'configuration') => 0,
                            Lang::t('_REST_AUTH_TOKEN', 'configuration') => 1,
                            Lang::t('_REST_AUTH_SECRET_KEY', 'configuration') => 2,
                        ];
                        echo Form::getRadioSet(Lang::t('_REST_AUTH_METHOD', 'configuration'), $var_name, 'option[' . $var_name . ']', $value_set, $var_value, $i_after);

                        break;

                    case 'home_page_option':
                        $tab_list = [];
                        $tab_list['my_courses'] = Lang::t('_MY_COURSES');
                        $tab_list['catalogue'] = Lang::t('_CATALOGUE');
                        $tab_list['dashboard'] = Lang::t('_DASHBOARD');
                        $which_home = $var_value;

                        echo '<div class="form_line_l"><p><b>' .
                            Lang::t('_HOME_PAGE') . '</b></p>';
                        echo Form::getInputDropdown('dropdown', $var_name, $var_name, $tab_list, $var_value, '')
                            . '</div><p>&nbsp;</p>';

                        break;
                    // Common types
                    case 'password':
                        echo Form::getPassword(
                            Lang::t('_' . strtoupper($var_name), 'configuration'),
                            $var_name,
                            'option[' . $var_name . ']',
                            $max_size,
                            $var_value,
                            $i_after
                        );

                        break;
                    case 'textarea':
                        echo Form::getSimpletextarea(
                            Lang::t('_' . strtoupper($var_name), 'configuration'),
                            $var_name,
                            'option[' . $var_name . ']',
                            $var_value,
                            false,
                            false,
                            false,
                            5,
                            22,
                            $i_after
                        );

                        break;

                    case 'on_usercourse_empty':
                        if ($which_home == 'my_courses') {
                            echo Form::getCheckbox(Lang::t('_' . strtoupper($var_name), 'configuration'), $var_name . '_on', 'option[' . $var_name . ']', 'on', ($var_value == 'on'), '', ' ' . $i_after);
                        } else {
                            echo Form::getCheckbox(Lang::t('_' . strtoupper($var_name), 'configuration'), $var_name . '_on', 'option[' . $var_name . ']', 'on', false, 'disabled', '', ' ' . $i_after);
                        }

                        break;

                    case 'menuvoice':
                    case 'menuvoice_course_public':
                    case 'check':
                        echo Form::getCheckbox(Lang::t('_' . strtoupper($var_name), 'configuration'), $var_name, 'option[' . $var_name . ']', 1, ($var_value == 1), '', ' ' . $i_after);
                        break;
                    case 'enum':
                        echo Form::getCheckbox(Lang::t('_' . strtoupper($var_name), 'configuration'), $var_name . '_on', 'option[' . $var_name . ']', 'on', ($var_value == 'on'), '', ' ' . $i_after);
                        break;
                    case 'button':
                        echo '<br/><a class="btn btn-default" role="button" href="' . $var_value . '">' . Lang::t('_' . strtoupper($var_name), 'configuration') . '</a>'; //($var_name,Lang::t('_' . strtoupper($var_name)),Lang::t('_' . strtoupper($var_name)));//Lang::t('_' . strtoupper($var_name)), $var_name . '_on', 'option[' . $var_name . ']', 'on', ($var_value == 'on'), '', ' ' . $i_after);
                        break;
                    case 'password_algorithms':
                        //drop down hteditor
                        $layout = [
                            1 => Lang::t('PASSWORD_BCRYPT'),
                            0 => Lang::t('PASSWORD_MD5'),
                        ];
                        echo Form::getDropdown(
                            Lang::t('_' . strtoupper($var_name), 'configuration'),
                            $var_name,
                            'option[' . $var_name . ']',
                            $layout,
                            $var_value
                        );
                        break;
                    case 'on_off':
                        $layout = [
                            'on' => Lang::t('ON'),
                            'off' => Lang::t('OFF'),
                        ];
                        echo Form::getDropdown(
                            Lang::t('_' . strtoupper($var_name), 'configuration'),
                            $var_name,
                            'option[' . $var_name . ']',
                            $layout,
                            $var_value
                        );

                        break;

                    default:
                        //string or int
                        echo Form::getTextfield(
                            Lang::t('_' . strtoupper($var_name), 'configuration', [], false, false, $includeDisabledPlugins),
                            $var_name,
                            'option[' . $var_name . ']',
                            $max_size,
                            $var_value,
                            false,
                            $i_after
                        );
                }
            }
            echo '<br></div>';
        }
        echo '</div>';

        require_once _base_ . '/lib/lib.utils.php';
        addJs('addons/jquery/dirtyforms/', 'jquery.dirtyforms.min.js');

        echo '<script type="text/javascript">
                window.onload = function() {
                    $("form[id^=conf_option]").dirtyForms();

                    const queryString = window.location.search;
                    const urlParams = new URLSearchParams(queryString);
                            
                    const plugin = urlParams.get("plugin");
                    const r = urlParams.get("r");
                            
                    var url = window.location.origin + window.location.pathname;
                    if (urlParams.has("r")) {
                        url = url + "?r=" + r;
                    }

                    $("#global_conf ul.nav.nav-tabs li a.nav-link").on("click", function(event) {
                        if ($("form[id^=conf_option]").dirtyForms("isDirty")) {
                            if (urlParams.has("r")) {
                                if (urlParams.has("plugin")) {
                                    url = url + "&plugin=" + plugin
                                } else {
                                    var active_tab = $(this).attr("href").split("_")[2];
                                    var active_tab_cur = $("#global_conf .tab-pane.active input[name=active_tab]").val();
                                    
                                    if (active_tab !== "" && active_tab !== active_tab_cur) {
                                        url = url + "&active_tab=" + active_tab;
                                    }
                                }
                                
                                if (url !== (window.location.origin + window.location.pathname + "?r=" + r)) {
                                    event.stopPropagation();
                                    event.preventDefault();
                                    window.location.replace(url);
                                }
                            }
                        }
                    });

                    $("form[id^=conf_option]").on("submit", function(event) {
                        if (!urlParams.has("plugin")) {
                            var submitter_btn = $(event.originalEvent.submitter);

                            if (submitter_btn.attr("name") == "undo") {
                                if ($("form").dirtyForms("isDirty")) {
                                    var active_tab_cur = $("#global_conf .tab-pane.active input[name=active_tab]").val();
                                    if (active_tab_cur !== "") {
                                        url = url + "&active_tab=" + active_tab_cur;
                                    }

                                    if (url !== (window.location.origin + window.location.pathname + "?r=" + r)) {
                                        event.stopPropagation();
                                        event.preventDefault();
                                        window.location.replace(url);
                                    }
                                }
                            }
                        }
                    });
                };
            </script>';

        return;
    }

    /**
     * Save the information recived for a group.
     *
     * @return bool true if the operation was successfull false otherwise
     */
    public function saveElement($regroup)
    {
        if ($regroup == 'suiteman') {
            return $this->_saveSuiteManager();
        }

        $reSetting = sql_query('
		SELECT param_name, value_type, extra_info
		FROM ' . $this->table . "
		WHERE regroup = '" . $regroup . "' AND hide_in_modify = '0' and value_type != 'button'");

        $re = true;
        while (list($var_name, $value_type, $extra_info) = sql_fetch_row($reSetting)) {
            switch ($value_type) {
                case 'menuvoice':
                    require_once _adm_ . '/lib/lib.menu.php';
                    $menu_man = new MenuManager();
                    if (isset($_POST['option'][$var_name]) && $_POST['option'][$var_name] == 1) {
                        $menu_man->addPerm(ADMIN_GROUP_GODADMIN, '/lms/admin' . $extra_info);
                        $new_value = 1;
                    } else {
                        $menu_man->removePerm(ADMIN_GROUP_GODADMIN, '/lms/admin' . $extra_info);
                        $new_value = 0;
                    }
                    break;
                case 'menuvoice_course_public':
                    $after_reload_perm = true;
                    require_once _adm_ . '/lib/lib.menu.php';
                    $menu_man = new MenuManager();
                    if (isset($_POST['option'][$var_name]) && $_POST['option'][$var_name] == 1) {
                        $perm = explode(';', $extra_info);
                        foreach ($perm as $k => $perm_suffix) {
                            $menu_man->addPerm('/oc_0', '/lms/course' . trim($perm_suffix));
                        }
                        $new_value = 1;
                    } else {
                        $perm = explode(';', $extra_info);
                        foreach ($perm as $k => $perm_suffix) {
                            $menu_man->removePerm('/oc_0', '/lms/course' . trim($perm_suffix));
                        }
                        $new_value = 0;
                    }
                    break;
                case 'tablist_coursecatalogue':
                    $tab_selected = [];
                    foreach ($_POST['tablist'] as $tab_code => $v) {
                        $tab_selected[$tab_code] = 1;
                    }
                    $new_value = urlencode(Util::serialize($tab_selected));
                    break;

                case 'tablist_mycourses':
                    $temp_arr = [];
                    for ($i = 0; $i < 3; ++$i) {
                        $temp_var = $_POST['mycourses'][$i];
                        if ($temp_var != '' && !in_array($temp_var, $temp_arr)) { //avoid repeated params
                            $temp_arr[] = $temp_var;
                        }
                    }
                    $new_value = implode(',', $temp_arr);
                    break;
                case 'home_page_option':
                    // setting enabled in middle_area options
                    $new_value = $_POST['home_page_option'];
                    switch ($new_value) {
                        case 'my_courses':
                            CoreMenu::set(CoreMenu::getByMVC('elearning/show')->idMenu, ['is_active' => true]);
                            break;
                        case 'catalogue':
                            CoreMenu::set(CoreMenu::getByMVC('lms/catalog/show')->idMenu, ['is_active' => true]);
                            break;
                    }
                    break;

                //if is int cast it
                case 'language':
                    $lang = Docebo::langManager()->getAllLangCode();
                    $new_value = $lang[$_POST['option'][$var_name]];
                    break;
                case 'template':
                    $templ = getTemplateList();
                    $new_value = $templ[$_POST['option'][$var_name]];
                    break;
                case 'template_domain_node':
                    $values = [];
                    for ($i = 0; $i < count($_POST['option'][$var_name]['template']); ++$i) {
                        $item = $_POST['option'][$var_name];
                        $values[] = [
                            'template' => $item['template'][$i],
                            'node' => $item['node'][$i],
                            'domain' => $item['domain'][$i],
                        ];
                    }

                    $new_value = json_encode($values, true);
                    break;
                case 'int':
                    $new_value = (int)$_POST['option'][$var_name];
                    break;
                //if is enum switch value to on or off
                case 'on_usercourse_empty':
                case 'enum':
                    if (isset($_POST['option'][$var_name])) {
                        $new_value = 'on';
                    } else {
                        $new_value = 'off';
                    }
                    break;
                case 'check':
                    if (isset($_POST['option'][$var_name]) && $_POST['option'][$var_name] == 1) {
                        $new_value = 1;
                    } else {
                        $new_value = 0;
                    }
                    break;
                case 'menuvoice':
                    require_once _adm_ . '/lib/lib.menu.php';
                    $menu_man = new MenuManager();
                    if (isset($_POST['option'][$var_name]) && $_POST['option'][$var_name] == 1) {
                        $menu_man->addPerm(ADMIN_GROUP_GODADMIN, '/framework/admin' . $extra_info);
                        $new_value = 1;
                    } else {
                        $menu_man->removePerm(ADMIN_GROUP_GODADMIN, '/framework/admin' . $extra_info);
                        $new_value = 0;
                    }
                    break;
                //else simple assignament
                case 'html':
                    $new_value = $_POST['option'][$var_name];
                    $new_value = strip_tags($_POST['option'][$var_name], '<a><b><i><sup>');
                    $new_value = str_replace('"', "'", $new_value);
                    break;
                default:
                    $new_value = $_POST['option'][$var_name];
            }
            if (!sql_query('UPDATE ' . $this->table . "
			SET param_value = '$new_value'
			WHERE param_name = '$var_name' AND regroup = '" . $regroup . "'")) {
                $re = false;
            }
        }

        return $re;
    }
}
