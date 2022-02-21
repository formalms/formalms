<?php defined("IN_FORMA") or die("Direct access is forbidden");



include _plugins_.'/Dummy/Features/Events/DummyEvent.php';

class DummyAlmsController extends AlmsController {
    
    const mod_name = 'dummy';

    protected $model;

    public function init() {

        $this->model = new DummyAlms();
    }

    /**
     * Return the path to the views
     * @return string
     */
	public function viewPath() {

		return _plugins_.'/Dummy/Features/appLms/admin/views';
    }

    public function show() {

        $params['sett']=Get::sett("dummy.foo","dummy");
        $this->render('show', $params);
    }

    public function render_call() {

		// $event = new DummyEvent();

		// \appCore\Events\DispatcherManager::dispatch(DummyEvent::EVENT_NAME, $event);

        // $params['foo']=$event->getFoo();

        $params['foo'] = 'bar';

        $this->render('render_call', $params);
    }
    
}
