<?php defined("IN_FORMA") or die("Direct access is forbidden");

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

include _plugins_.'/Dummy/Features/Events/DummyEvent.php';

class DummyAlmsController extends AlmsController {
    
    const mod_name = 'dummy';
    
    const dummy_url = 'index.php?r=alms/invoice/show';
    //const templates_url = 'index.php?r=alms/dummy/templates';
    
    protected $json;
    protected $model;
    
    protected $permissions;

    public function init() {        
        $this->json = new Services_JSON();
        $this->model = new DummyAlms();

        $this->permissions = array(
            'view'      =>  checkPerm('view', TRUE, self::mod_name, 'lms'),
            'mod'       =>  checkPerm('mod', TRUE, self::mod_name, 'lms')
        );
    }
    
    // TODO: automatismo
	public function viewPath() {

//		return _plugins_.'/dummy/features/'._folder_alms_.'/views';
		return _plugins_.'/Dummy/Features/appLms/admin/views';
    }

    public function show() {
/*
        if(!$this->permissions['view']) {
            getErrorUi(Lang::t('_NO_PERMISSION', 'dummy'));
            return;
        }
*/
        $params['sett']=Get::sett("dummy.foo","dummy");
        $this->render('show', $params);
    }
    public function render_call() {
/*
        if(!$this->permissions['view']) {
            getErrorUi(Lang::t('_NO_PERMISSION', 'dummy'));
            return;
        }
*/

		$event = new DummyEvent();
		
		\appCore\Events\DispatcherManager::dispatch(DummyEvent::EVENT_NAME, $event);

        $this->render('render_call', $params);
    }
    
}

?>