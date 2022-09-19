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
 * Class DashboardLayoutLms.
 */
class DashboardLayoutLms extends Model
{
    public const LAYOUT_STATUS_DRAFT = 'draft';
    public const LAYOUT_STATUS_PUBLISH = 'publish';

    protected $id;

    protected $name;

    protected $caption;

    protected $status;

    protected $default = false;

    protected array $permissionList = [];

    public function __construct()
    {
        parent::__construct();
    }

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
        if (in_array($status, [self::LAYOUT_STATUS_DRAFT, self::LAYOUT_STATUS_PUBLISH], true)) {
            $this->status = $status;
        }
    }

    public function isDefault(): bool
    {
        return $this->default;
    }

    public function setDefault(bool $default)
    {
        $this->default = $default;
    }

    public function getPermissionList(): array
    {
        return $this->permissionList;
    }

    public function setPermissionList(array $permissionList): DashboardLayoutLms
    {
        $this->permissionList = $permissionList;

        return $this;
    }

    public function userCanAccess(DoceboUser $user)
    {
        $canAccess = true;

        if (!empty($this->permissionList)) {
            $canAccess = !empty(array_intersect($user->getArrSt(), $this->permissionList));
        }

        return $canAccess;
    }
}
