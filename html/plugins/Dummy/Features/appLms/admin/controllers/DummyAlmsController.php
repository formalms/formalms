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

defined('IN_FORMA') or exit('Direct access is forbidden');

include _plugins_ . '/Dummy/Features/Events/DummyEvent.php';

class DummyAlmsController extends AlmsController
{
    public const mod_name = 'dummy';

    protected $model;

    public function init()
    {
        $this->model = new DummyAlms();
    }

    /**
     * Return the path to the views.
     *
     * @return string
     */
    public function viewPath()
    {
        return _plugins_ . '/Dummy/Features/appLms/admin/views';
    }

    public function show()
    {
        $params['sett'] = FormaLms\lib\Get::sett('dummy.foo', 'dummy');
        $this->render('show', $params);
    }

    public function render_call()
    {
        // $event = new DummyEvent();

        // \appCore\Events\DispatcherManager::dispatch(DummyEvent::EVENT_NAME, $event);

        // $params['foo']=$event->getFoo();

        $params['foo'] = 'bar';

        $this->render('render_call', $params);
    }
}
