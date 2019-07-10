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
abstract class DashboardBlockLms extends Model
{
	const TYPE_BIG = 'big';
	const TYPE_MEDIUM = 'medium';
	const TYPE_SMALL = 'small';
	const TYPE_BUTTON = 'button';
	const TYPE_BANNER = 'banner';

	const ALLOWED_TYPES = [self::TYPE_BIG, self::TYPE_MEDIUM, self::TYPE_SMALL, self::TYPE_BUTTON, self::TYPE_BANNER];

	abstract public function getViewPath(): string;

	abstract public function getViewFile(): string;

	abstract public function getViewData(): array;

	abstract public function getLink(): string;

	abstract public function getRegisteredActions(): array;

	/**
	 * @var string
	 */
	private $type;

	/**
	 * @var int
	 */
	private $order = 0;

	/**
	 * @var bool
	 */
	private $enabled = false;

	/** @var string */
	protected $viewPath;

	/** @var string */
	protected $viewFile;

	public function __construct()
	{
		parent::__construct();
		$this->viewPath = str_replace('DashboardBlock', '', str_replace('Lms', '', get_class($this)));
		$this->viewFile = strtolower(str_replace('DashboardBlock', '', str_replace('Lms', '', get_class($this))));
	}

	/**
	 * @return int
	 */
	public function getOrder(): int
	{
		return $this->order;
	}

	/**
	 * @param int $order
	 * @return DashboardBlockLms
	 */
	public function setOrder(int $order): DashboardBlockLms
	{
		$this->order = $order;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isEnabled(): bool
	{
		return $this->enabled;
	}

	/**
	 * @param bool $enabled
	 * @return DashboardBlockLms
	 */
	public function setEnabled(bool $enabled): DashboardBlockLms
	{
		$this->enabled = $enabled;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * @param string $type
	 * @return DashboardBlockLms
	 */
	public function setType(string $type): DashboardBlockLms
	{
		if (!in_array($type, self::ALLOWED_TYPES)) {
			throw new LogicException(sprintf('Selected type is not allowed : %s', $type));
		}
		$this->type = $type;
		return $this;
	}

	/**
	 * @return array
	 */
	protected function getCommonViewData(): array
	{
		return [
			'view' => $this->getViewName(),
			'order' => $this->getOrder(),
			'type' => $this->getType(),
			'enabled' => $this->isEnabled(),
			'link' => $this->getLink(),
			'ajaxUrl' => 'ajax.adm_server.php?r=lms/dashboard/ajaxAction&block=' . get_class($this),
			'registeredActions' => $this->getRegisteredActions()
		];
	}

	protected function getViewName()
	{
		return sprintf('%s/%s.html.twig', $this->getViewPath(), $this->getViewFile());
	}

	protected function getDataFromCourse($course, $startDate, $endDate)
	{
		$status_list = [
			0 => Lang::t('_CST_PREPARATION', 'course'),
			1 => Lang::t('_CST_AVAILABLE', 'course'),
			2 => Lang::t('_CST_CONFIRMED', 'course'),
			3 => Lang::t('_CST_CONCLUDED', 'course'),
			4 => Lang::t('_CST_CANCELLED', 'course')
		];

		$dateBegin = $course['course_date_begin'];
		if ($dateBegin === '0000-00-00') {
			$dateBegin = $startDate;
		}


		$dateEnd = $course['course_date_end'];
		if ($dateEnd === '0000-00-00') {
			$dateEnd = $endDate;
		}

		$hourBebing = $course['course_hour_begin'];
		$hourBebingString = '';
		if ($hourBebing === '-1') {
			$hourBebing = '00:00:00';
		} else {
			$hourBebing .= ':00';
			$hourBebingString = $course['course_hour_begin'];
		}
		$hourEnd = $course['course_hour_end'];
		$hourEndString = '';
		if ($hourEnd === '-1') {
			$hourEnd = '23:59:59';
		} else {
			$hourEnd .= ':00';
			$hourEndString = $course['course_hour_end'];
		}

		$courseData = [
			'id' => $course['course_id'],
			'title' => $course['course_name'],
			'start' => $dateBegin . 'T' . $hourBebing,
			'end' => $dateEnd . 'T' . $hourEnd,
			'type' => $course['course_type'],
			'status' => $this->calculateCourseStatus($course),
			'nameCategory' => $this->getCategory($course['course_category_id']),
			'courseStatus' => $course['course_status'],
			'courseStatusString' => $status_list[(int)$course['course_status']],
			'description' => $course['course_box_description'],
			'hours' => $hourBebingString . ' ' . $hourEndString,
		];

		return $courseData;
	}

	protected function getDataFromReservation($reservation, $startDate, $endDate)
	{
		$dateBegin = $reservation['date_begin'];
		if ($dateBegin === '0000-00-00') {
			$dateBegin = $startDate;
		}

		$dateEnd = $reservation['date_end'];
		if ($dateEnd === '0000-00-00') {
			$dateEnd = $endDate;
		}

		$hourBebing = $reservation['hour_begin'];
		$hourBebingString = '';
		if ($hourBebing === '-1') {
			$hourBebing = '00:00:00';
		} else {
			$hourBebing .= ':00';
			$hourBebingString = $reservation['hour_begin'];
		}
		$hourEnd = $reservation['hour_end'];
		$hourEndString = '';
		if ($hourEnd === '-1') {
			$hourEnd = '23:59:59';
		} else {
			$hourEnd .= ':00';
			$hourEndString = $reservation['hour_end'];
		}

		$reservationData = [
			'title' => $reservation['name'],
			'start' => $dateBegin . 'T' . $hourBebing,
			'end' => $dateEnd . 'T' . $hourEnd,
			'type' => $reservation['course_type'],
			'status' => true,
			'description' => $reservation['box_description'],
			'hours' => $hourBebingString . ' ' . $hourEndString,
		];

		$reservationData['course'] = $this->getCalendarDataFromCourse($reservation);

		return $reservationData;
	}

	protected function calculateCourseStatus($course)
	{
		if ($course['date_end'] !== '0000-00-00' &&  $course['date_end'] !== '0000-00-00 00:00:00') {

			$earlier = new DateTime();
			$later = new DateTime($course['date_end']);

			$days = $later->diff($earlier)->format("%a");
			
			if($days === 0){
				return false;
			}
		}
		return true;
	}

	protected function getCategory($idCat)
	{
		$db = DbConn::getInstance();
		$query = "select path from %lms_category where idCategory=" . $idCat;
		$res = $db->query($query);
		$path = "";
		if ($res && $db->num_rows($res) > 0) {
			list($path) = $db->fetch_row($res);
		}
		return $path;
	}
}
