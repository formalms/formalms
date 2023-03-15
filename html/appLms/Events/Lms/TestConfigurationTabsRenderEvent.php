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

class TestConfigurationTabsRenderEvent extends Event
{
    public const EVENT_NAME = 'lms.test.configuration_tabs_render';
    protected $configTabs;

    protected $object_test = null;
    protected $url_encode = null;
    protected $lang = null;

    public function __construct($object_test, $url_encode, $lang)
    {
        $this->object_test = $object_test;
        $this->url_encode = $url_encode;
        $this->lang = $lang;
        $this->configTabs = [];
    }

    /**
     * @return array
     */
    public function addTab($key, $value)
    {
        $this->configTabs[$key] = $value;
    }

    /**
     * @return array
     */
    public function getTab($key)
    {
        return $this->configTabs[$key];
    }

    /**
     * @return array
     */
    public function getTabs()
    {
        return $this->configTabs;
    }

    public function removeTab($key)
    {
        unset($this->configTabs[$key]);
    }

    /**
     * @return null
     */
    public function getObjectTest()
    {
        return $this->object_test;
    }

    /**
     * @return null
     */
    public function getUrlEncode()
    {
        return $this->url_encode;
    }

    /**
     * @return null
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'configTabs' => $this->configTabs,
            'object_test' => $this->object_test,
            'url_encode' => $this->url_encode,
            'lang' => $this->lang,
        ];
    }
}
