<?php
namespace appLms\Events\Widget;

use Symfony\Component\EventDispatcher\Event;

class UserSelectorRenderJSScriptEvent extends Event
{
    const EVENT_NAME = 'widget.user_selector.render_js_script';
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