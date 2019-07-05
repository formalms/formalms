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
 * Class DashboardLms
 */
class DashboardLms extends Model
{

	private $registeredblocks;

	public function __construct()
	{
		parent::__construct();
		$blocks = [
			DashboardBlockWelcomeLms::class,
			DashboardBlockProfileLms::class,
			DashboardBlockCertificatesLms::class,
			DashboardBlockMessagesLms::class,
			DashboardBlockCalendarLms::class,
			DashboardBlockCoursesLms::class,
			DashboardBlockCourseAdviceLms::class,
		];

		foreach ($blocks as $block) {

			$this->registeredblocks[] = new $block;
		}
	}

	/**
	 * @return mixed
	 */
	public function getRegisteredblocks()
	{
		return $this->registeredblocks;
	}

	public function getBlocksViewData()
	{
		$data = [];
		/** @var DashboardBlockLms $registeredblock */
		foreach ($this->registeredblocks as $registeredblock) {
			if ($registeredblock->isEnabled()) {
				$data[] = $registeredblock->getViewData();
			}
		}

		return $data;
	}

	/**
	 * @param string $block
	 * @return bool|DashboardBlockLms
	 */
	public function getRegisteredBlock(string $block)
	{
		foreach ($this->registeredblocks as $registeredblock) {

			if (get_class($registeredblock) === $block) {
				return $registeredblock;
			}
		}
		return null;
	}
}
