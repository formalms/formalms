<?php

namespace FormaLms\lib\Services\Reports;


use FormaLms\lib\Interfaces\Accessible;

class ReportScheduleService implements Accessible
{

    protected $session;


    public function __construct() {
        $this->session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    }

    public function getAccessList( $resourceId) : array{

    
        return [];

    }

    public function setAccessList( $resourceId, array $selection) : bool{

        include_once _lms_.'/lib/lib.report.php';
        $scheduleTempData = $this->session->get('schedule_tempdata', []);
        $scheduleUpdate = $this->session->get('schedule_update', 0);

        //$_temp = $ref['recipients'];
        $_name = $scheduleTempData['name'];
        $_time = $scheduleTempData['time'];
        $_period = $scheduleTempData['period'] . ',' . $scheduleTempData['period_info'];

        //get current saved report ID from session (check if report is saved, otherwise -> error)

        if ($scheduleUpdate) {
            $sched = report_update_schedulation($scheduleUpdate, $_name, $_period, $_time, $selection);
        } else {
            $sched = report_save_schedulation($resourceId, $_name, $_period, $_time, $selection);
        }

        $this->session->set('current_action_platform', 'lms');
        $this->session->save();
        return $sched;
    }



}