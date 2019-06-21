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
			'link' => $this->getLink()
		];
	}

	protected function getViewName()
	{
		return sprintf('%s/%s.html.twig', $this->getViewPath(), $this->getViewFile());
	}
}
