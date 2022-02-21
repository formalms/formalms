<?php


defined("IN_FORMA") or die('Direct access is forbidden.');



/**
 * Class DashboardLayoutLms
 */
class DashboardLayoutLms extends Model
{
    const LAYOUT_STATUS_DRAFT = 'draft';
    const LAYOUT_STATUS_PUBLISH = 'publish';

    protected $id;

    protected $name;

    protected $caption;

    protected $status;

    protected $default = false;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getCaption()
    {
        return $this->caption;
    }

    /**
     * @param mixed $caption
     */
    public function setCaption($caption)
    {
        $this->caption = $caption;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        if (in_array($status, [self::LAYOUT_STATUS_DRAFT, self::LAYOUT_STATUS_PUBLISH])) {
            $this->status = $status;
        }
    }

    /**
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->default;
    }

    /**
     * @param bool $default
     */
    public function setDefault(bool $default)
    {
        $this->default = $default;
    }
}
