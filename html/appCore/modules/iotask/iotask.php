<?php defined("IN_FORMA") or die('Direct access is forbidden.');

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
 * @package		Docebo
 * @subpackage	ImportExportUI
 * @version 	$id$
 * @author		Emanuele Sandri <emanuele (@) docebo (.) com>
**/

/**
 * Main UI of iotask module. Connectors, Connections and Tasks 
 *  in one tabbed window
 *  @param Module $module a reference to the iotask module
 *  @param string $op the op code  
 **/  
function ioTask_UITab( &$module, $op ) {
	checkPerm('view');
	
	require_once(_base_.'/lib/lib.tab.php' );
	
	$tv = new TabView( 'iotask_ui', '#' );
	$lang =& DoceboLanguage::createInstance('iotask', 'framework');
	
	$tv->addTab( new TabElemDefault( 'connectors', $lang->def('_CONNECTORS'), getPathImage().'iotask/connector.gif' ) );
	$tv->addTab( new TabElemDefault( 'connections', $lang->def('_CONNECTIONS'), getPathImage().'iotask/connection.gif' ) );
	$tv->addTab( new TabElemDefault( 'tasks', $lang->def('_TASKS'), getPathImage().'iotask/task.gif' ) );
	
	$tv->parseInput( $_POST, $_POST );
	if( isset($_GET['gotab']) ) 
		$tv->setActiveTab($_GET['gotab']);
	elseif( $tv->getActiveTab() == '' ) 
		$tv->setActiveTab('connections');
	
	require_once( _base_.'/lib/lib.form.php' );
	$form = new Form();
	
	$GLOBALS['page']->add($form->openForm('iotask_ui' , 'index.php?modname=iotask&amp;op=display'));
	
	$GLOBALS['page']->add($form->getHidden(	$tv->_getStateId(),
											$tv->_getStateId(),
											$tv->getActiveTab()));
	
	$GLOBALS['page']->add( $tv->printTabView_Begin("",FALSE) );

	switch( $tv->getActiveTab() ) {
		case 'connectors':
			ioTask_UIConnectorsList($module);
		break;
		case 'connections':
			ioTask_UIConnectionsList($module);
		break;
		case 'tasks':
			ioTask_UITaskList($module);
		break;
	}
	
	$GLOBALS['page']->add( $tv->printTabView_End() );
	$GLOBALS['page']->add($form->closeForm());
}

/** 
 * The UI with list of connectors
 * @param Module $module a reference to the module 
 **/ 
function ioTask_UIConnectorsList( &$module ) {
	checkPerm('view');
	
	require_once(_base_.'/lib/lib.table.php');
	require_once(_base_.'/lib/lib.form.php');
	
	$connMgr =& $module->get_connMgr(); 
	$lang =& $module->get_lang();
	$out  =& $module->get_out();
	$form = new Form();
	
	$tb_connectors = new Table(Get::sett('visuItem'), $lang->def('_CONNECTORS_TITLE'), $lang->def('_CONNECTORS_TITLE'));
	
	$content_h 	= array(
		$lang->def('_TYPE'), 
		$lang->def('_FILE'),
		$lang->def('_CLASS')
	);
	$type_h	= array('', '', '');

	$tb_connectors->setColsStyle($type_h);
	$tb_connectors->addHead($content_h);
	
	$connectors = $connMgr->get_first_connector();
	if( $connectors === FALSE ) {
		$out->add( "<!-- connection manager error: ". $connMgr->get_last_error() ." -->");
	}
	while( $connectors ) {
		$cont = array();
		$cont[] = $connectors[CONNMGR_CONNTYPE_TYPE];
		$cont[] = $connectors[CONNMGR_CONNTYPE_FILE];
		$cont[] = $connectors[CONNMGR_CONNTYPE_CLASS];
		$tb_connectors->addBody($cont);
		$connectors = $connMgr->get_next_connector();
	}  
	
	$tb_connectors->addActionAdd(
		'<label for="new_connector">'
		.'<img class="valing-middle" src="'.getPathImage().'standard/add.png" alt="'.$lang->def('_ADD').'" />'
		.' '.$lang->def('_ADD_NEW_CONNECTOR').'</label> '
		.$form->getButton('new_connector', 'action[new_connector]', $lang->def('_CREATE'), 'button_nowh')
	);
	
	$out->add($tb_connectors->getTable());
	
}

/**
 * The UI for create (insert) a new connector
 * @param Module $module the caller module
 **/   
function ioTask_UIConnectorNew( &$module ) {
	checkPerm('view');
	
	require_once(_base_.'/lib/lib.form.php');

	$connMgr =& $module->get_connMgr(); 
	$lang =& $module->get_lang();
	$out  =& $module->get_out();
	$form = new Form();
	$count = 0;
	
	$out->setWorkingZone('content');
	$out->add(getTitleArea($lang->def('_CONNECTOR'), 'iotask'));
	$out->add('<div class="std_block">');
	
	$out->add($form->getFormHeader($lang->def('_ADD_NEW_CONNECTOR')));
	$out->add($form->openForm('connector_new', 'index.php?modname=iotask&op=display&addconnector&gotab=connectors'));
	$out->add($form->openElementSpace());
	
	// list all files in connectos directory
	$dir = dir( $GLOBALS['where_framework'].'/lib/connectors' );
	while( FALSE !== ($entry = $dir->read())) {
		if( substr($entry, 0, 10) == 'connector.')
			if( $connMgr->get_connector_byfile($entry) == FALSE ) {
				$count++;
				$out->add( $form->getLabel( 'file_'.$entry, $entry )
							.$form->getButton( 'file_'.$entry, 'file['.$entry.']', $lang->def('_ADD') )
							.'<br/>' );
			}
	}
	
	if( $count == 0 ) 
		$out->add( $lang->def('_NO_NEW_CONNECTORS') );
	
	$out->add($form->closeElementSpace());
	$out->add($form->openButtonSpace());
	$out->add($form->getButton('cancel', 'cancel', $lang->def('_CANCEL')));
	$out->add($form->closeButtonSpace());
	$out->add($form->closeForm());	
	$out->add('</div>');
}

/**
 * The UI that list all the connections
 * @param Module $module the caller module
 **/   
function ioTask_UIConnectionsList( &$module ) {
	checkPerm('view');
	
	require_once(_base_.'/lib/lib.table.php');
	require_once(_base_.'/lib/lib.form.php');
	
	$connMgr =& $module->get_connMgr(); 
	$lang =& $module->get_lang();
	$out  =& $module->get_out();
	$form = new Form();
	
	$tb_connections = new Table(Get::sett('visuItem'), $lang->def('_CONN_TITLE'), $lang->def('_CONN_TITLE'));
	
	$content_h 	= array(
		$lang->def('_NAME'), 
		$lang->def('_DESCRIPTION'),
		$lang->def('_TYPE'),
		'<img src="'.getPathImage().'/standard/edit.png" alt="'.$lang->def('_MOD').'" title="'.$lang->def('_MOD').'"/>',
		'<img src="'.getPathImage().'/standard/cancel.png" alt="'.$lang->def('_DEL').'" title="'.$lang->def('_DEL').'"/>'
	);
	$type_h	= array('', '', '', 'image', 'image');

	$tb_connections->setColsStyle($type_h);
	$tb_connections->addHead($content_h);
	
	$conn = $connMgr->get_first_connection();
	while( $conn ) {
		$cont = array();
		$cont[] = $conn[CONNMGR_CONN_NAME];
		$cont[] = $conn[CONNMGR_CONN_DESCRIPTION];
		$cont[] = $conn[CONNMGR_CONN_TYPE];
		$cont[] = '<input type="image" '
				 .'id="modifiy_'.$conn[CONNMGR_CONN_NAME].'" '
				 .'name="action[edit_connection]['.$conn[CONNMGR_CONN_NAME].']" '
				 .'src="'.getPathImage().'/standard/edit.png"'
				 .'alt="'.$lang->def('_MOD').'"'
				 .'title="'.$lang->def('_MOD').': '.$conn[CONNMGR_CONN_NAME].'" />';
		$cont[] = '<input type="image" '
				 .'id="delete_'.$conn[CONNMGR_CONN_NAME].'" '
				 .'name="action[delete_connection]['.$conn[CONNMGR_CONN_NAME].']" '
				 .'src="'.getPathImage().'/standard/cancel.png"'
				 .'alt="'.$lang->def('_DEL').'"'
				 .'title="'.$lang->def('_DEL').': '.$conn[CONNMGR_CONN_NAME].'" />';
				 

		$tb_connections->addBody($cont);
		$conn = $connMgr->get_next_connection();
	}  
	
	$conn_type = $connMgr->get_first_connector();
	$options = "";
	while( $conn_type ) {
		$options .= '<option value="'.$conn_type[CONNMGR_CONNTYPE_TYPE].'">'.$conn_type[CONNMGR_CONNTYPE_TYPE].'</option>';
		$conn_type = $connMgr->get_next_connector();
	}
	$tb_connections->hide_over = true;
	$tb_connections->addActionAdd(
		'<label for="type_connection">'
		.'<img class="valing-middle" src="'.getPathImage().'standard/add.png" alt="'.$lang->def('_ADD').'" />'
		.' '.$lang->def('_ADD_NEW_CONNECTION').'</label> '
		.'<select id="type_connection" name="type_connection">'
		.$options
		.'</select> '
		.$form->getButton('new_connection', 'action[new_connection]', $lang->def('_CREATE'), 'button_nowh')
	);
	
	$out->add($tb_connections->getTable());
	
}

/**
 * The UI for create a new connections
 * @param Module $module the caller module
 **/   
function ioTask_UIConnectionNew( &$module, $action, $subop ) {
	checkPerm('view');
	
	require_once(_base_.'/lib/lib.form.php');

	$connMgr =& $module->get_connMgr(); 
	$lang =& $module->get_lang();
	$out  =& $module->get_out();
	$form = new Form();

	if( $subop == 'edit_connection' ) {
		$connection = $connMgr->create_connection_byname(key($action));
	} else {
		$connection = $connMgr->create_connector_bytype($_POST['type_connection']);
	}
	$connectionUI = $connection->get_configUI();
	$connectionUI->set_lang($lang);
	$connectionUI->set_form($form);
	$connectionUI->parse_input($_GET, $_POST);
	
	if( is_array($action) ) {
		if( key($action) == 'finish' ) {
			$connectionUI->go_finish();
			if( $connMgr->save_connection( $connectionUI->get_old_name(), $connection ) ) {
				Util::jump_to( 'index.php?modname=iotask&op=display&addconnectionok&gotab=connections' );
			} else {
				$out->add(getErrorUi($lang->def('_OPERATION_FAILURE').'<br />'.$connMgr->get_last_error()));
			}
		} elseif( key($action) == 'next' ) {
			$connectionUI->go_next();
		} elseif( key($action) == 'prev' ) {
			$connectionUI->go_prev();
		}
	}
	
	$out->setWorkingZone('content');
	$out->add(getTitleArea($lang->def('_CONNECTION'), 'iotask'));
	$out->add('<div class="std_block">');
	
	$out->add($form->getFormHeader($lang->def('_ADD_NEW_CONNECTION')));
	$out->add($form->openForm('connector_new', 'index.php?modname=iotask&op=display&addconnection&gotab=connections'));
	$out->add($form->openElementSpace());

	if( $connection === FALSE ) 
		$out->add('connessione non creata');
	
	$out->add($form->getHidden('type_connection', 'type_connection', $connection->get_type_name()));

	// output connector UI
	$out->add($connectionUI->get_htmlheader(),'page_head');
	$out->add($connectionUI->get_html());
	
	$out->add($form->closeElementSpace());
	$out->add($form->openButtonSpace());

	if( $connectionUI->show_prev())
		$out->add($form->getButton('prev', 'action[new_connection][prev]', $lang->def('_PREV')));
	
	if( $connectionUI->show_next())
		$out->add($form->getButton('next', 'action[new_connection][next]', $lang->def('_NEXT')));

	if( $connectionUI->show_finish())
		$out->add($form->getButton('finish', 'action[new_connection][finish]', $lang->def('_FINISH')));
	$out->add($form->getButton('cancel', 'cancel', $lang->def('_CANCEL')));
	$out->add($form->closeButtonSpace());
	$out->add($form->closeForm());	
	$out->add('</div>');
}


function ioTask_UIConnectionDelete( &$module, $action ) {
	checkPerm('view');
	
	require_once(_base_.'/lib/lib.form.php');

	$connMgr =& $module->get_connMgr(); 
	$lang =& $module->get_lang();
	$out  =& $module->get_out();
	$form = new Form();

	if( is_array($action) ) {
		if( key($action) == '--confirm--' ) {
			if( $connMgr->delete_connection_byname( $_POST['connection_name'] ) )
				Util::jump_to( 'index.php?modname=iotask&op=display&deleteconnectionok&gotab=connections' );
			else
				Util::jump_to( 'index.php?modname=iotask&op=display&deleteconnectionerror&gotab=connections' );
		}
	}
	
	$connection = $connMgr->create_connection_byname(key($action));
	$connection_name = key($action);
	// TODO: verify if this connection is used
	$out->setWorkingZone('content');
	$out->add(getTitleArea($lang->def('_CONNECTION'), 'iotask'));
	$out->add('<div class="std_block">');
	
	
	$out->add($form->getFormHeader($lang->def('_DEL')));
	$out->add($form->openForm('connector_delete', 'index.php?modname=iotask&op=display&gotab=connections'));
	$out->add($form->getHidden('connection_name', 'connection_name', key($action)));
	$out->add(getDeleteUi(	$lang->def('_CONFIRM_DELETION'),
							str_replace('%name%',$connection_name,$lang->def('_AREYOUSURE') ), 
							FALSE,
							'action[delete_connection][--confirm--]', 
							'cancel')
			 );

	$out->add($form->closeForm());	
	$out->add('</div>');

}

/**
 * The UI that list all the task
 * @param Module $module the caller module
 **/   
function ioTask_UITaskList( &$module ) {
	checkPerm('view');
	
	require_once(_base_.'/lib/lib.table.php');
	require_once(_base_.'/lib/lib.form.php');
	
	$connMgr =& $module->get_connMgr(); 
	$lang =& $module->get_lang();
	$out  =& $module->get_out();
	$form = new Form();
	
	$tb_tasks = new Table(Get::sett('visuItem'), $lang->def('_TASKS'), $lang->def('_TASKS'));
	
	$content_h 	= array(
		$lang->def('_NAME'), 
		$lang->def('_DESCRIPTION'),
		$lang->def('_SOURCE'),
		$lang->def('_DESTINATION'),
		$lang->def('_SCHEDULE'),
		$lang->def('_LAST_EXECUTION'),
		'<img src="'.getPathImage().'/standard/play.png" alt="'.$lang->def('_TASK_RUN').'" title="'.$lang->def('_TASK_RUN').'"/>',
		'<img src="'.getPathImage().'/standard/edit.png" alt="'.$lang->def('_MOD').'" title="'.$lang->def('_MOD').'"/>',
		'<img src="'.getPathImage().'/standard/cancel.png" alt="'.$lang->def('_TASK_DEL').'" title="'.$lang->def('_DEL').'"/>'
	);
	$type_h	= array('', '', '', '', '', '', 'image', 'image', 'image');

	$tb_tasks->setColsStyle($type_h);
	$tb_tasks->addHead($content_h);
	
	$conn = $connMgr->get_first_task();
	while( $conn ) {
		$cont = array();
		$cont[] = $conn[CONNMGR_TASK_NAME];
		$cont[] = $conn[CONNMGR_TASK_DESCRIPTION];
		$cont[] = $conn[CONNMGR_TASK_SOURCE];
		$cont[] = $conn[CONNMGR_TASK_DESTINATION];
		if( $conn[CONNMGR_TASK_SCHEDTYPE] == 'at' ) {
			$cont[] = str_replace('%time%',$conn[CONNMGR_TASK_SCHEDULE]['qt'],$lang->def('_SCHEDULE_TYPE_AT_DATA'));
		} else {
			if( $conn[CONNMGR_TASK_SCHEDULE]['um'] == 'hour' )
				$conn[CONNMGR_TASK_SCHEDULE]['um'] = $lang->def('_HOUR');
			elseif( $conn[CONNMGR_TASK_SCHEDULE]['um'] == 'day' )
				$conn[CONNMGR_TASK_SCHEDULE]['um'] = $lang->def('_DAY');
			$cont[] = str_replace(	array('%interval%','%unit%'),
									array_values($conn[CONNMGR_TASK_SCHEDULE]),
									$lang->def('_SCHEDULE_TYPE_INTERVAL_DATA'));
		}
		if( $conn[CONNMGR_TASK_LAST_EXECUTION] !== null ) { 
			//$last_execution = strtotime($conn[CONNMGR_TASK_LAST_EXECUTION]);
			$cont[] = $conn[CONNMGR_TASK_LAST_EXECUTION];
		} else 
			$cont[] = '';
		$cont[] = '<input type="image" '
				 .'id="run_'.$conn[CONNMGR_TASK_NAME].'" '
				 .'name="action[run_task]['.$conn[CONNMGR_TASK_SEQUENCE].']" '
				 .'src="'.getPathImage().'/standard/play.png"'
				 .'alt="'.$lang->def('_TASK_RUN').'"'
				 .'title="'.$lang->def('_TASK_RUN').': '.$conn[CONNMGR_TASK_NAME].'" />';
		$cont[] = '<input type="image" '
				 .'id="modifiy_'.$conn[CONNMGR_TASK_NAME].'" '
				 .'name="action[edit_task]['.$conn[CONNMGR_TASK_SEQUENCE].']" '
				 .'src="'.getPathImage().'/standard/edit.png"'
				 .'alt="'.$lang->def('_MOD').'"'
				 .'title="'.$lang->def('_MOD').': '.$conn[CONNMGR_TASK_NAME].'" />';
		$cont[] = '<input type="image" '
				 .'id="delete_'.$conn[CONNMGR_TASK_NAME].'" '
				 .'name="action[delete_task]['.$conn[CONNMGR_TASK_SEQUENCE].']" '
				 .'src="'.getPathImage().'/standard/cancel.png"'
				 .'alt="'.$lang->def('_TASK_DEL').'"'
				 .'title="'.$lang->def('_TASK_DEL').': '.$conn[CONNMGR_TASK_NAME].'" />';
				 
		$tb_tasks->addBody($cont);
		$conn = $connMgr->get_next_task();
	}  
	
	$tb_tasks->addActionAdd(
		'<label for="new_task">'
		.'<img class="valing-middle" src="'.getPathImage().'standard/add.png" alt="'.$lang->def('_ADD').'" />'
		.' '.$lang->def('_ADD_NEW_TASK').'</label> '
		.$form->getButton('new_task', 'action[new_task]', $lang->def('_CREATE'), 'button_nowh')
	);
	
	$out->add($tb_tasks->getTable());
	
}

function ioTask_UITaskNew( &$module, $action, $subop ) {

	checkPerm('view');
	
	require_once(_base_.'/lib/lib.form.php');

	$connMgr =& $module->get_connMgr(); 
	$lang =& $module->get_lang();
	$out  =& $module->get_out();
	$form = new Form();
	
	$old_name = "";
	if($subop == 'edit_task') {
		$params = $connMgr->get_task_byID(key($action));
        $old_name = $params[0];
	} else {
		$params = array(CONNMGR_TASK_NAME => $lang->def('_TASK_NAME_EXAMPLE'),
						CONNMGR_TASK_DESCRIPTION => '',
						CONNMGR_TASK_SOURCE => '',
						CONNMGR_TASK_DESTINATION => '',
						CONNMGR_TASK_SCHEDTYPE => 'at',
						CONNMGR_TASK_SCHEDULE => array('qt' => '12:27', 'um' => 'hour'),
						CONNMGR_TASK_IMPORT_TYPE => TASK_IMPORT_TYPE_INSERTONLY,
						CONNMGR_TASK_MAP => array()
						);
	}
	
	if( isset($_POST['step'])) { 
		$step = $_POST['step'];
		$old_name = $_POST['old_name'];
		$post_params = $_POST['task_params'];

		$params = Util::unserialize(urldecode($post_params['memory']));
		foreach($post_params as $key => $val) {
			if( $key !== 'memory' ) {
				$params[$key] = $val;
			}
		}
		if( $step == 1 ) {
			// load the map from DoceboImport object
			$dimport = new DoceboImport();
			$params[CONNMGR_TASK_MAP] = $dimport->parse_map();
		}
	} else
		$step = 0;

	if( is_array($action) ) {
		if( key($action) == 'finish' ) {
			if( $connMgr->save_task($old_name, $params) )
				Util::jump_to('index.php?modname=iotask&op=display&addtaskok&gotab=tasks');
			else {
				$out->add(getErrorUi($lang->def('_ERROR_SAVE_TASK').'<br />'.$connMgr->get_last_error()));
			}
		} elseif( key($action) == 'next' ) {
			$step++;
		} elseif( key($action) == 'prev' ) {
			$step--;
		}
	}
	$out->setWorkingZone('content');
	$out->add(getTitleArea($lang->def('_TASKS'), 'iotask'));
	$out->add('<div class="std_block">');
	
	$out->add($form->getFormHeader($lang->def('_ADD_NEW_TASK')));
	$out->add($form->openForm('task_new', 'index.php?modname=iotask&op=display&addtask&gotab=tasks'));
	$out->add($form->openElementSpace());

	$out->add($form->getHidden('task_memory','task_params[memory]',urlencode(Util::serialize($params))));
	$out->add($form->getHidden('step','step',$step));
	$out->add($form->getHidden('old_name','old_name',$old_name));

	switch( $step ) {
		case 0:	ioTask_UITaskNew_step0( $module, $params ); break;
		case 1:	ioTask_UITaskNew_step1( $module, $params ); break;
		case 2:	ioTask_UITaskNew_step2( $module, $params ); break;
	}
	
	$out->add($form->closeElementSpace());
	$out->add($form->openButtonSpace());
	if( $step > 0 ) 
		$out->add($form->getButton('prev', 'action[new_task][prev]', $lang->def('_PREV')));

	if( $step < 2 ) 
		$out->add($form->getButton('next', 'action[new_task][next]', $lang->def('_NEXT')));
	
	if( $step == 2 ) 
		$out->add($form->getButton('finish', 'action[new_task][finish]', $lang->def('_FINISH')));
	
	$out->add($form->getButton('cancel', 'cancel', $lang->def('_CANCEL')));
	$out->add($form->closeButtonSpace());
	$out->add($form->closeForm());	
	$out->add('</div>');

}
	
	


/**
 * The UI for create a new task
 * @param Module $module the caller module
 **/   
function ioTask_UITaskNew_step0( &$module, &$params ) {
	checkPerm('view');
	
	require_once(_base_.'/lib/lib.form.php');

	$connMgr =& $module->get_connMgr(); 
	$lang =& $module->get_lang();
	$out  =& $module->get_out();
	$form = new Form();
	
	$arr_connections = $connMgr->get_all_connections_name();
	$arr_options = array();
	foreach( $arr_connections as $conn_name ) 
		$arr_options[$conn_name] = $conn_name;
	
	$out->add($form->getTextfield($lang->def('_NAME'),'task_name','task_params['.CONNMGR_TASK_NAME.']', 50, $params[CONNMGR_TASK_NAME]));
	$out->add($form->getTextfield($lang->def('_DESCRIPTION'),'task_description', 'task_params['.CONNMGR_TASK_DESCRIPTION.']', 255, $params[CONNMGR_TASK_DESCRIPTION]));
	$out->add($form->getDropdown($lang->def('_SOURCE'),'task_source','task_params['.CONNMGR_TASK_SOURCE.']',$arr_options,$params[CONNMGR_TASK_SOURCE]));
	$out->add($form->getDropdown($lang->def('_DESTINATION'),'task_destination','task_params['.CONNMGR_TASK_DESTINATION.']',$arr_options,$params[CONNMGR_TASK_DESTINATION]));
	
}

/**
 * The UI for create a new task
 * @param Module $module the caller module
 **/   
function ioTask_UITaskNew_step1( &$module, &$params ) {
	checkPerm('view');
	
	require_once(_base_.'/lib/lib.form.php');

	$connMgr =& $module->get_connMgr(); 
	$lang =& $module->get_lang();
	$out  =& $module->get_out();
	$form = new Form();
	
	$out->add($form->getLineBox( 	$lang->def('_NAME'), $params[CONNMGR_TASK_NAME] ));

	$dimport = new DoceboImport();
	$source =& $connMgr->create_connection_byname($params[CONNMGR_TASK_SOURCE]);

	if( $source->is_raw_producer() ) {
		$out->add( $lang->def('_MAP_NOT_REQUIRED') );
	} else {
		$destination =& $connMgr->create_connection_byname($params[CONNMGR_TASK_DESTINATION]);
		$source->connect();
		$destination->connect();
		$dimport->set_source($source);
		$dimport->set_destination($destination);
		$dimport->set_map($params[CONNMGR_TASK_MAP]);
		$out->add( $dimport->getUIMap());
	}	
}

/**
 * The UI for create a new task
 * @param Module $module the caller module
 **/   
function ioTask_UITaskNew_step2( &$module, &$params ) {
	checkPerm('view');
	
	require_once(_base_.'/lib/lib.form.php');

	$connMgr =& $module->get_connMgr(); 
	$lang =& $module->get_lang();
	$out  =& $module->get_out();
	$form = new Form();
	
	$out->add($form->getLineBox( $lang->def('_NAME'), $params[CONNMGR_TASK_NAME] ));

	$out->add($form->getRadioSet( 	$lang->def('_IMPORT_TYPE'), 
		  							'task_import_type', 
									'task_params['.CONNMGR_TASK_IMPORT_TYPE.']',
									array( 	$lang->def('_IMPORT_TYPE_INSERTONLY')  => TASK_IMPORT_TYPE_INSERTONLY, 
											$lang->def('_IMPORT_TYPE_INSERTREMOVE')  => TASK_IMPORT_TYPE_INSERTREMOVE), 
									$params[CONNMGR_TASK_IMPORT_TYPE]));
	$out->add($form->getRadioSet( 	$lang->def('_SCHEDULE'),
		  							'task_schedule_type', 
									'task_params['.CONNMGR_TASK_SCHEDTYPE.']',
									array( 	$lang->def('_SCHEDULE_TYPE_AT')  => 'at', 
											$lang->def('_SCHEDULE_TYPE_INTERVAL')  => 'interval'), 
									$params[CONNMGR_TASK_SCHEDTYPE]));
	$tmp_um = $form->getInputListbox('',
									'task_schedule_um',
									'task_params['.CONNMGR_TASK_SCHEDULE.'][um]',
									array('hour'=>$lang->def('_HOUR'),'day'=>$lang->def('_DAY')),
									array($params[CONNMGR_TASK_SCHEDULE]['um']),
									FALSE,
									'');
	$out->add($form->getTextfield(	$lang->def('_SCHEDULE'),
									'task_schedule_qt',
									'task_params['.CONNMGR_TASK_SCHEDULE.'][qt]', 
									10, 
									$params[CONNMGR_TASK_SCHEDULE]['qt'],
									'',
									$tmp_um,
									''));
	$out->add( 	"\n<script type='text/javascript'>\n" .
				"var field_um = document.getElementById('task_schedule_um');\n" .
				"var field = document.getElementById('task_schedule_type_0');\n" .
				"field.onclick = function() {\n" .
				"	field_um.style.display = 'none';\n" .
				"}\n" .
				"field = document.getElementById('task_schedule_type_1');\n" .
				"field.onclick = function() {\n" .
				"	field_um.style.display = 'inline';\n" .
				"}\n" .
				"</script>\n" 
			);

}

function ioTask_UITaskDelete( &$module, $action ) {
	checkPerm('view');
	
	require_once(_base_.'/lib/lib.form.php');

	$connMgr =& $module->get_connMgr(); 
	$lang =& $module->get_lang();
	$out  =& $module->get_out();
	$form = new Form();

	if( is_array($action) ) {
		if( key($action) == '--confirm--' ) {
            if( $connMgr->delete_task_byid( $_POST['task_id'] ) )
				Util::jump_to( 'index.php?modname=iotask&op=display&deletetaskok&gotab=tasks' );
			else
				Util::jump_to( 'index.php?modname=iotask&op=display&deletetaskerror&gotab=tasks' );
		}
	}
	
    $task_id = key($action);

	$out->setWorkingZone('content');
	$out->add(getTitleArea($lang->def('_TASKS'), 'iotask'));
	$out->add('<div class="std_block">');
	
	
	$out->add($form->getFormHeader($lang->def('_TASK_DEL')));
	$out->add($form->openForm('task_delete', 'index.php?modname=iotask&op=display&gotab=tasks'));
    $out->add($form->getHidden('task_id', 'task_id', $task_id));
	$out->add(getDeleteUi(	$lang->def('_CONFIRM_DELETION'),
							str_replace('%name%',$task_name,$lang->def('_AREYOUSURE') ), 
							FALSE,
							'action[delete_task][--confirm--]', 
							'cancel')
			 );

	$out->add($form->closeForm());	
	$out->add('</div>');

}

function ioTask_UITaskRun( &$module, $action ) {
	checkPerm('view');
	
	require_once(_base_.'/lib/lib.form.php');

	$connMgr =& $module->get_connMgr(); 
	$lang =& $module->get_lang();
	$out  =& $module->get_out();
	$form = new Form();
	$dimport = new DoceboImport();

	$params = $connMgr->get_task_byID(key($action));
	$task_name = $params[CONNMGR_TASK_NAME];

	$out->setWorkingZone('content');
	$out->add(getTitleArea($lang->def('_TASKS'), 'iotask'));
	$out->add('<div class="std_block">');
	
	
	$out->add($form->getFormHeader($lang->def('_TASK_RUNNED')));
	$out->add($form->openForm('task_delete', 'index.php?modname=iotask&op=display&gotab=tasks'));
	$out->add($form->openElementSpace());
	$out->add($form->getHidden('task_name', 'task_name', $task_name));

	$report = $dimport->execute_task(key($action));
	if( !is_array($report) ) {
		$out->add($report);
	} else {
		$out->add($form->getLineBox( $lang->def('_TASK_INSERTED'), $report[0]['inserted']));
		$out->add($form->getLineBox( $lang->def('_OPERATION_SUCCESSFUL'), $report[0]['removed']));
		$out->add($form->getLineBox( $lang->def('_OPERATION_FAILURE'), count($report)-1));
		
		foreach( $report as $index => $elem_report ) {
			if( $index !== 0 ) {
				$out->add(	$index
							.' - '
							.'('.implode(', ',$report[$index][0]).')'
							.' - '
							.$report[$index][1]
						);
			}
		}
	}
	$out->add($form->closeElementSpace());
	$out->add($form->openButtonSpace());	
	$out->add($form->getButton('close', 'close', $lang->def('_CLOSE')));
	$out->add($form->closeButtonSpace());	
	$out->add($form->closeForm());	
	$out->add('</div>');

}


?>
