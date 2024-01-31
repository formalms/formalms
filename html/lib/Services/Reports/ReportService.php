<?php

namespace FormaLms\lib\Services\Reports;


use FormaLms\lib\Interfaces\Accessible;

class ReportService implements Accessible
{

    public const _REPORT_SESSION = 'report_tempdata';

    protected $session;

    protected int $reportType;

    protected ?int $reportId = null;

    protected string $link;

    public function __construct()
    {
        $this->session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    }

    public function getEntity(int $id) {
        return sql_fetch_object(sql_query("SELECT * FROM %lms_report WHERE id_report = $id"));
    }

    public function getAccessList($resourceId): array
    {

        $this->assignReportReferences($resourceId);

        if ($this->session->has(self::_REPORT_SESSION)) {

       
            switch ((int)$this->reportType) {
                case 5:
                case 2:
                    $filter = 'rows_filter';
                    if (array_key_exists('users', $this->session->get(self::_REPORT_SESSION)['rows_filter'] ?? []) ) {
                        return $this->session->get(self::_REPORT_SESSION)['rows_filter']['users'];
                    }
                    break;

                case 4:
                    $filter = 'columns_filter';
                    if ($this->session->has('report_update') && array_key_exists('users', $this->session->get(self::_REPORT_SESSION)[$filter] ?? []) ) {
                        return $this->session->get(self::_REPORT_SESSION)[$filter]['users'];
                    }
                    break;
            }


        }
        return [];

    }

    public function setAccessList($resourceId, array $selection): bool
    {

        $this->assignReportReferences($resourceId);
        $return = true;
        $reportTempData = $this->session->has(self::_REPORT_SESSION) ? $this->session->get(self::_REPORT_SESSION) : [];

        $keyUsers = 'users';
        $keyAll = 'all_users';
        switch ((int)$this->reportType) {
            case 5:
                $keyUsers = 'selection';
                $keyAll = 'select_all';
            case 2:
                $filter = 'rows_filter';
                $link = 'index.php?modname=report&op=modify_cols&modid=';
                break;

            case 4:
                $filter = 'columns_filter';
                $link = 'index.php?modname=report&op=modify_cols&substep=columns_selection&modid=';
                break;
        }
        $reportTempData[$filter][$keyUsers] = $selection;
        $reportTempData[$filter][$keyAll] = (\FormaLms\lib\Get::req('all_users', DOTY_INT, 0) > 0 ? true : false);
        //non utilizzato la selezione viene inviata per intero
        $reportTempData['id_report'] = $this->reportType;
        $this->session->set(self::_REPORT_SESSION, $reportTempData);
        
        $this->session->set('current_action_platform', 'lms');
        $this->session->save();

        if($this->reportId) {
            $return = false;
            $this->setLink($link.$this->reportId);
        }

        return $return;
    }

    private function assignReportReferences($reportReference) {

        $reportReferenceArray = explode('_', $reportReference);
        $this->reportType = array_shift($reportReferenceArray);
        if(count($reportReferenceArray)) {
            $this->reportId = array_shift($reportReferenceArray);
        }
    }


    private function setLink(string $link) : self{

        $this->link = $link;
        return $this;
    }

    public function getLink() : string{

        return $this->link;
    }


    public function toggleUseUserSelection(int $id, bool $value) {
        sql_query("UPDATE %lms_report SET use_user_selection = $value WHERE id_report=$id");
    }


}