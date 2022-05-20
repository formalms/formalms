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

/**
 * @version    $Id$
 *
 * @author    Emanuele Sandri <emanuele[AT@AT]docebo[dot.dot]com>
 */
require_once dirname(__FILE__) . '/class.definition.php';

class Module_IOTask extends Module
{
    public $out = null;
    public $connMgr = null;
    public $lang = null;

    public function &get_out()
    {
        if ($this->out === null) {
            $this->out = &$GLOBALS['page'];
        }

        return $this->out;
    }

    public function &get_lang()
    {
        if ($this->lang === null) {
            //require_once(_i18n_.'/lib.lang.php');
            $this->lang = &DoceboLanguage::createInstance('iotask', 'framework');
        }

        return $this->lang;
    }

    public function &get_connMgr()
    {
        if ($this->connMgr === null) {
            require_once _adm_ . '/lib/lib.iotask.php';
            $this->connMgr = new DoceboConnectionManager();
        }

        return $this->connMgr;
    }

    public function useExtraMenu()
    {
        return true;
    }

    public function loadExtraMenu()
    {
        loadAdminModuleLanguage($this->module_name);
    }

    public function loadBody()
    {
        require_once _adm_ . '/modules/' . $this->module_name . '/' . $this->module_name . '.php';
        $out = &$this->get_out();
        $lang = &$this->get_lang();

        if (isset($_GET['addconnector']) && !isset($_POST['cancel'])) {
            require_once _adm_ . '/lib/lib.iotask.php';
            $connMgr = &$this->get_connMgr();

            $filename = key($_POST['file']);
            if ($connMgr->add_connector($filename)) {
                $out->add(getResultUi($lang->def('_OPERATION_SUCCESSFUL')));
            } else {
                $out->add(getErrorUi($lang->def('_OPERATION_FAILURE'))
                    . $connMgr->get_last_error());
            }
        }
        if (isset($_GET['addconnectionok']) && !isset($_POST['cancel'])) {
            $out->add(getResultUi($lang->def('_OPERATION_SUCCESSFUL')));
        }
        if (isset($_GET['addtaskok']) && !isset($_POST['cancel'])) {
            $out->add(getResultUi($lang->def('_OPERATION_SUCCESSFUL')));
        }
        if (isset($_GET['deleteconnectionok']) && !isset($_POST['cancel'])) {
            $out->add(getResultUi($lang->def('_OPERATION_SUCCESSFUL')));
        } elseif (isset($_GET['deleteconnectionerror']) && !isset($_POST['cancel'])) {
            $out->add(getErrorUi($lang->def('_ERR_FAIL_DELETE_CONNECTOR')));
        }
        if (isset($_GET['deletetaskok']) && !isset($_POST['cancel'])) {
            $out->add(getResultUi($lang->def('_OPERATION_SUCCESSFUL')));
        } elseif (isset($_GET['deletetakserror']) && !isset($_POST['cancel'])) {
            $out->add(getErrorUi($lang->def('_ERR_FAIL_DELETE_TASK')));
        }

        if (isset($_POST['action'])) {
            switch (key($_POST['action'])) {
                case 'new_connector':
                    ioTask_UIConnectorNew($this);
                    break;
                case 'run_task':
                    ioTask_UITaskRun($this, current($_POST['action']), key($_POST['action']));
                    break;
                case 'new_task':
                case 'edit_task':
                    ioTask_UITaskNew($this, current($_POST['action']), key($_POST['action']));
                    break;
                case 'delete_task':
                    ioTask_UITaskDelete($this, current($_POST['action']), key($_POST['action']));
                    break;
                case 'new_connection':
                case 'edit_connection':
                    ioTask_UIConnectionNew($this, current($_POST['action']), key($_POST['action']));
                    break;
                case 'delete_connection':
                    ioTask_UIConnectionDelete($this, current($_POST['action']), key($_POST['action']));
                    break;
            }
        } elseif (isset($_GET['addconnection']) && !isset($_POST['cancel'])) {
            ioTask_UIConnectionNew($this, '', '');
        } else {
            ioTask_UITab($this, $GLOBALS['op']);
        }
    }

    // Function for permission managment
    public function getAllToken($op)
    {
        return [
            'view' => ['code' => 'view',
                'name' => '_VIEW',
                'image' => 'standard/view.png', ],
        ];
        $op = $op;
    }

    public function doTasks()
    {
        $out = '';
        $connMgr = &$this->get_connMgr();
        $taskParams = $connMgr->get_first_task();
        $dimport = new DoceboImport();
        while ($taskParams !== false) {
            $canExecuteResponse = $connMgr->is_task_todo($taskParams, true);
            $out .= '<iotask name="' . $taskParams[CONNMGR_TASK_NAME] . '" executed="' . ($canExecuteResponse['is_task_todo'] ? 'TRUE' : 'FALSE') . '" executionDate="' . $canExecuteResponse['executionDate'] . '" scheduledDate="' . $canExecuteResponse['scheduledDate'] . '"';
            if ($canExecuteResponse['is_task_todo']) {
                $report = $dimport->execute_task($taskParams[CONNMGR_TASK_SEQUENCE]);
                if (!is_array($report)) {
                    $out .= '" inserted = "0" removed = "0" > ';
                    $out .= '<result > ' . $report . '</result > ';
                } else {
                    $out .= ' inserted = "' . $report[0]['inserted'] . '"' .
                        ' removed = "' . $report[0]['removed'] . '" > ';
                    foreach ($report as $index => $elem_report) {
                        if ($index !== 0) {
                            $out .= '<row index = "' . $index . '" > ';
                            $out .= '<pk > ' . implode(', ', $report[$index][0]) . ' </pk > ';
                            $out .= '<error > ' . $report[$index][1] . '</error > ';
                            $out .= '</row > ';
                        }
                    }
                }
            }
            $out .= '</iotask> ';

            $taskParams = $connMgr->get_next_task();
        }

        return $out;
    }
}
