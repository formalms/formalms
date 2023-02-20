<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

namespace appLms\Events\Lms;

use Symfony\Contracts\EventDispatcher\Event;

class MenuOverEvent extends Event
{
    public const EVENT_NAME = 'lms.menuover';

    /**
     * @var
     */
    protected $menu;

    /**
     * @var
     */
    protected $menu_i;

    public function __construct($menu, $menu_i)
    {
        $this->menu = $menu;
        $this->menu_i = $menu_i;
    }

    /**
     * @param mixed $menu
     */
    public function setMenu($menu)
    {
        $this->menu = $menu;
    }

    /**
     * @return mixed
     */
    public function getMenu()
    {
        return $this->menu;
    }

    /**
     * @param mixed $menu_i
     */
    public function setMenuI($menu_i)
    {
        $this->menu_i = $menu_i;
    }

    /**
     * @return mixed
     */
    public function getMenuI()
    {
        return $this->menu_i;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'menu' => $this->menu,
            'menu_i' => $this->menu_i,
        ];
    }
}
