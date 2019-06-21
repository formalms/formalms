<?php


defined("IN_FORMA") or die('Direct access is forbidden.');

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
 * Class DashboardBlockWelcomeLms
 */
class DashboardBlockProfileLms extends DashboardBlockLms
{

	public function __construct()
	{
		parent::__construct();
		$this->setEnabled(true);
		$this->setType(DashboardBlockLms::TYPE_BUTTON);
	}

	public function getViewData(): array
	{

		$data = $this->getCommonViewData();
		$data['user'] = $this->getUser();

		return $data;
	}

	/**
	 * @return string
	 */
	public function getViewPath(): string
	{
		return $this->viewPath;
	}

	/**
	 * @return string
	 */
	public function getViewFile(): string
	{
		return $this->viewFile;
	}

	private function getUser()
	{
		$user = Docebo::user();
		$acl_man = Docebo::user()->getAclManager();
		$user_info = $acl_man->getUser($user->getIdSt(), false);

		return [
			'userId' => $user->getIdSt(),
			'firstname' => $user_info[ACL_INFO_FIRSTNAME],
			'lastname' => $user_info[ACL_INFO_LASTNAME],
			'email' => $user_info[ACL_INFO_EMAIL],
			'avatar' => $user_info[ACL_INFO_AVATAR]
		];
	}

	public function getLink(): string
	{
		return 'index.php?r=lms/profile/show';
	}
}
