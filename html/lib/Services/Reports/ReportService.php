<?php

namespace FormaLms\lib\Services\Reports;


use FormaLms\lib\Interfaces\Accessible;

class ReportService implements Accessible
{

    public const _REPORT_SESSION = 'report_tempdata';

    protected $session;


    public function __construct()
    {
        $this->session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    }

    public function getAccessList($resourceId): array
    {

        if ($this->session->has(self::_REPORT_SESSION) && array_key_exists('rows_filter', $this->session->get(self::_REPORT_SESSION))) {

            if (array_key_exists('users', $this->session->get(self::_REPORT_SESSION)['rows_filter'])) {
                return $this->session->get(self::_REPORT_SESSION)['rows_filter']['users'];
            }

        }
        return [];

    }

    public function setAccessList($resourceId, array $selection): bool
    {

        $reportTempData = $this->session->has(self::_REPORT_SESSION) ? $this->session->get(self::_REPORT_SESSION) : [];

        $keyUsers = 'users';
        $keyAll = 'all_users';
        switch ((int)$resourceId) {
            case 5:
                $keyUsers = 'selection';
                $keyAll = 'select_all';
            case 2:
                $filter = 'rows_filter';
                break;

            case 4:
                $filter = 'columns_filter';
                break;
        }
        $reportTempData[$filter][$keyUsers] = $selection;
        $reportTempData[$filter][$keyAll] = (\FormaLms\lib\Get::req('all_users', DOTY_INT, 0) > 0 ? true : false);
        //non utilizzato la selezione viene inviata per intero
        $reportTempData['id_report'] = $resourceId;
        $this->session->set(self::_REPORT_SESSION, $reportTempData);

        $this->session->set('current_action_platform', 'lms');
        $this->session->save();


        return true;
    }


}