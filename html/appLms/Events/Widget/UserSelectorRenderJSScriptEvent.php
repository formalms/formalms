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

namespace appLms\Events\Widget;

use Symfony\Contracts\EventDispatcher\Event;

class UserSelectorRenderJSScriptEvent extends Event
{
    public const EVENT_NAME = 'widget.user_selector.render_js_script';
    protected $prependScript = '';

    /**
     * @param $toPrepend
     */
    public function prependScript($toPrepend)
    {
        $this->prependScript .= $toPrepend;
    }

    /**
     * @return string
     */
    public function getPrependScript()
    {
        return $this->prependScript;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->prependScript;
    }
}
