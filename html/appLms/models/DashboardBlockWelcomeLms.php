<?php


defined("IN_FORMA") or die('Direct access is forbidden.');




/**
 * Class DashboardBlockWelcomeLms
 */
class DashboardBlockWelcomeLms extends DashboardBlockLms
{

    public function __construct($jsonConfig)
    {
        parent::__construct($jsonConfig);
    }

    public function parseConfig($jsonConfig)
	{
		$this->parseBaseConfig($jsonConfig);
	}

    public function getAvailableTypesForBlock()
    {
        return [
            DashboardBlockLms::TYPE_1COL,
            DashboardBlockLms::TYPE_2COL,
            DashboardBlockLms::TYPE_3COL,
            DashboardBlockLms::TYPE_4COL
        ];
    }

    public function getForm()
    {
        $form = parent::getForm();

        $form[] = DashboardBlockForm::getFormItem($this, 'welcome_text', DashboardBlockForm::FORM_TYPE_TEXT, false);

        return $form;
    }

    public function getViewData()
    {
        $data = $this->getCommonViewData();
        $acl_man = Docebo::user()->getAclManager();
        $user = $acl_man->getUser(Docebo::user()->idst, null);
        $data['data'] = [
            'firstname' => $user[2],
            'lastname' => $user[3],
            'platform' => Get::sett('page_title'),
        ];

        $msg = Lang::t($this->data['welcome_text'] ?: '_DASHBOARD_WELCOME_MESSAGE', 'dashboardsetting');
        $placeholders = array_keys($data['data']);
        foreach ($placeholders as $placeholder) {
            $msg = str_replace("[$placeholder]", $data['data'][$placeholder], $msg);
        }
        $data['msg'] = $msg;

        return $data;
    }

    /**
     * @return string
     */
    public function getViewPath()
    {
        return $this->viewPath;
    }

    /**
     * @return string
     */
    public function getViewFile()
    {
        return $this->viewFile;
    }

    public function getLink()
    {
        return;
    }

    public function getRegisteredActions()
    {
        return [];
    }
}
