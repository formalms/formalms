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
 * @package appLms
 * @subpackage reservation 
 * @author Marco Valloni
 */

if(!Docebo::user()->isAnonymous())
{
	require_once($GLOBALS['where_lms'].'/lib/lib.reservation.php');
	
	function viewEvent()
	{
		checkPerm('view');
		
		require_once(_base_.'/lib/lib.navbar.php');
		require_once(_base_.'/lib/lib.table.php');
		
		$lang =& DoceboLanguage::createInstance('reservation');
		
		$mod_perm = checkPerm('mod', true);
		
		$out = $GLOBALS['page'];
		$out->setWorkingZone('content');
		
		$man_res = new Man_Reservation();
		
		$acl =& Docebo::user()->getAcl();
		$user_idst = getLogUserId();
		
		$events = array();
		
		$events = $man_res->viewEvents();
		
		$out->add(getTitleArea($lang->def('_RESERVATION')).'<div class="std_block">', 'content');
		
		$error = importVar('error', false, '');
		
		if ($error !== '')
		{
			switch ($error)
			{
				case 'del_event':
					$out->add(getErrorUi($lang->def('_DEL_EVENT_ERROR')));
				break;
			}
		}
		
		if(count($events))
		{
			$tb = new Table(10, $lang->def('_EVENT_CAPTION'), $lang->def('_EVENT_CAPTION'));
			$tb->initNavBar('ini', 'button');
			
			$ini = $tb->getSelectedElement();
			
			$cont_h = array
			(
				$lang->def('_TITLE'),
				$lang->def('_CATEGORY'),
				$lang->def('_DATE'),
				$lang->def('_FROM_TIME'),
				$lang->def('_TO_TIME'),
				$lang->def('_NUMBER_SUBSCRIBED'),
				$lang->def('_AVAILABLE_PLACES'),
				$lang->def('_DEADLINE'),
			);
			$type_h = array('', '', '', '', '', '', '', '');
			if ($mod_perm)
			{
				$type_h = array('', '', '', '', '', '', '', '','image', 'image', 'image');//,'image');
				$cont_h = array
				(
					$lang->def('_TITLE'),
					$lang->def('_CATEGORY'),
					$lang->def('_DATE'),
					$lang->def('_FROM_TIME'),
					$lang->def('_TO_TIME'),
					$lang->def('_NUMBER_SUBSCRIBED'),
					$lang->def('_AVAILABLE_PLACES'),
					$lang->def('_DEADLINE'),
					'<img src="'.getPathImage().'standard/moduser.png" title="'.$lang->def('_SET_ROOM_VIEW_PERM').'" alt="'.$lang->def('_SET_ROOM_VIEW_PERM').'" />',
					//'<img src="'.getPathImage().'/standard/add.png" title="'.$lang->def('_ADD_USER').'" alt="'.$lang->def('_ALT_ADD_USER').'" />',
					'<img src="'.getPathImage().'/standard/edit.png" title="'.$lang->def('_MOD').'" alt="'.$lang->def('_MOD').'" />',
					'<img src="'.getPathImage().'/standard/delete.png" title="'.$lang->def('_DEL').'" alt="'.$lang->def('_DEL').'" />'
				);
			}
			$tb->setColsStyle($type_h);
			$tb->addHead($cont_h);
			if ($events)
			{
				foreach($events as $event)
				{

					$count = array();
					
					$count[] = $event[EVENT_TITLE];
					$count[] = $event[EVENT_CATEGORY_NAME];
					$count[] = Format::date($event[EVENT_DATE], 'date');
					$count[] =  $event[EVENT_FROM_TIME];
					$count[] =  $event[EVENT_TO_TIME];
					$count[] = $event[EVENT_USER_SUBSCRIBED].'/'.$event[EVENT_MAX_USER];
					$count[] = $event[EVENT_AVAILABLE_PLACES];
					$count[] = Format::date($event[EVENT_DEADLINE], 'date');
					if ($mod_perm)
					{
						$count[] = '<a href="index.php?modname=reservation&amp;op=set_room_view_perm&amp;id_event='.$event[EVENT_ID].'"><img src="'.getPathImage().'standard/moduser.png" alt="'.$lang->def('_SET_ROOM_VIEW_PERM').'" title="'.$lang->def('_SET_ROOM_VIEW_PERM').'" /></a>';
						//$count[] = '<a href="index.php?modname=reservation&amp;op=add_registration&amp;id_event='.$event[EVENT_ID].'&amp;id_course='.$event[EVENT_ID_COURSE].'"><img src="'.getPathImage().'/standard/add.png" title="'.$lang->def('_ADD_USER').'" alt="'.$lang->def('_ALT_ADD_USER').'" /></a>';
						$count[] = '<a href="index.php?modname=reservation&amp;op=mod_event&amp;id_event='.$event[EVENT_ID].'"><img src="'.getPathImage().'/standard/edit.png" title="'.$lang->def('_MOD').'" alt="'.$lang->def('_MOD').'" /></a>';
						$count[] = '<a href="index.php?modname=reservation&amp;op=del_event&amp;id_event='.$event[EVENT_ID].'"><img src="'.getPathImage().'/standard/delete.png" title="'.$lang->def('_DEL').'" alt="'.$lang->def('_DEL').'" /></a>';
					}
					$tb->addBody($count);
				}
				
				require_once(_base_.'/lib/lib.dialog.php');
				setupHrefDialogBox('a[href*=del_event]');
			}
			if($mod_perm)
			{
				$tb->addActionAdd('<a href="index.php?modname=reservation&amp;op=add_event">
					<img src="'.getPathImage().'standard/add.png" title="'.$lang->def('_NEW_EVENT').'" alt="'.$lang->def('_NEW_EVENT').'" /> '
					.$lang->def('_NEW_EVENT').'</a>');
			}
			$out->add($tb->getTable()
			.$tb->getNavBar($ini, count($events))
			.'</div>'
			);
		}
		else
		{
			if($mod_perm)
			{
				$out->add('<div class="events_action_top"><p><a href="index.php?modname=reservation&amp;op=add_event">'.$lang->def('_NEW_EVENT').'</a></p></div>', 'content');
			}
			$out->add($lang->def('_NO_RESERVATION_FOUND'), 'content');
		}
		
		$out->add('</div>', 'content');
	}
	
	function addEvent()
	{
		checkPerm('view');
		
		require_once(_base_.'/lib/lib.form.php');
		require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
		
		$lang =& DoceboLanguage::createInstance('reservation');
		
		$out = $GLOBALS['page'];
		$out->setWorkingZone('content');
		
		$man_res = new Man_Reservation();
		$man_course = new Man_Course();
		
		if (isset($_GET['confirm']))
		{
			$confirm = importVar('confirm', true, 0);
			$id_course = importVar('id_course', true, 0);
			$id_laboratory = importVar('id_laboratory', true, 0);
			$id_category = importVar('id_category', true, 0);
			$title = importVar('title', false, '');
			$description = importVar('description', false, '');
			$date = importVar('date', false, '');
			$max_user = importVar('max_user', true, 0);
			$deadline = importVar('deadline', false, '');
			$from_time_h = importVar('from_time_h', false, '');
			$from_time_m = importVar('from_time_m', false, '');
			$to_time_h = importVar('to_time_h', false, '');
			$to_time_m = importVar('to_time_m', false, '');
			
			$date = Format::dateDb($date, 'date');
			
			$deadline = Format::dateDb($deadline, 'date');
			
			$from_time = $from_time_h.':'.$from_time_m.':00';
			
			$to_time = $to_time_h.':'.$to_time_m.':00';
			
		if ($date < date('Y-m-d') || $date < $deadline)
				Util::jump_to('index.php?modname=reservation&op=add_event&amp;error=date');
			
			if ($from_time >= $to_time)
				Util::jump_to('index.php?modname=reservation&op=add_event&amp;error=time');
			
			if ($confirm)
				$result = $man_res->addEvents($id_course, $id_laboratory, $id_category, $title, $description, $date, $max_user, $deadline, $from_time, $to_time);
			if ($result)
				Util::jump_to('index.php?modname=reservation&op=view_event');
			Util::jump_to('index.php?modname=reservation&op=add_event&amp;error=laboratory');
		}
		
		$out->add
		(
			getTitleArea($lang->def('_NEW_EVENT'), '', $lang->def('_EVENT'))
			.'<div class="std_block">'
		);
		
		$error = importVar('error', false, '');
		
		if ($error !== '')
		{
			switch ($error)
			{
				case 'date':
					$out->add(getErrorUi($lang->def('_WRONG_DATE')));
				break;
				
				case 'time':
					$out->add(getErrorUi($lang->def('_WRONG_TIME')));
				break;
				
				case 'laboratory':
					$out->add(getErrorUi($lang->def('_LOCATION_BUSY')));
				break;
				
			} 
		}
		
		$course = array();
		$course = $man_course->getAllCourses();
		$course_id = array();
		foreach ($course as $sources)
			$course_id[$sources['id_course']] = $sources['name'];
		
		$out->add
		(
			Form::openForm('form_event', 'index.php?modname=reservation&amp;op=add_event&amp;confirm=1')
			.Form::openElementSpace()
			.Form::getTextfield($lang->def('_TITLE'), 'title', 'title', 255)
			.Form::getTextarea($lang->def('_DESCRIPTION'), 'description', 'description')
			.Form::getDropdown($lang->def('_COURSE'), 'id_course', 'id_course', $course_id)
			.Form::getDropdown($lang->def('_LOCATION'), 'id_laboratory', 'id_laboratory', $man_res->getLaboratories())
			.Form::getDropdown($lang->def('_CATEGORY'), 'id_category', 'id_category', $man_res->getCategory())
			.Form::getDateField($lang->def('_DATE'), 'date', 'date')
			.Form::getDateField($lang->def('_DEADLINE'), 'deadline', 'deadline')
			.Form::getTextfield($lang->def('_MAX_USER'), 'max_user', 'max_user', 255)
			.Form::getLineBox($lang->def('_FROM_TIME'), Form::getInputDropdown('', 'from_time_h', 'from_time_h', $man_res->getHours(), false, false).' : '.Form::getInputDropdown('', 'from_time_m', 'from_time_m', $man_res->getMinutes(), false, false))
			.Form::getLineBox($lang->def('_TO_TIME'), Form::getInputDropdown('', 'to_time_h', 'to_time_h', $man_res->getHours(), false, false).' : '.Form::getInputDropdown('', 'to_time_m', 'to_time_m', $man_res->getMinutes(), false, false))
			.Form::closeElementSpace()
			.Form::openButtonSpace()
			.Form::getButton('send_event', 'send_event', $lang->def('_NEW_EVENT'))
			.Form::getButton('undo', 'undo', $lang->def('_UNDO'))
			.Form::closeButtonSpace()
			.Form::closeForm()
		);
		
		$out->add('</div>', 'content');
	}
	
	function modEvent()
	{
		checkPerm('view');
		
		require_once(_base_.'/lib/lib.form.php');
		
		$id_event = importVar('id_event', true, 0);
		
		$lang =& DoceboLanguage::createInstance('reservation');
		
		$out = $GLOBALS['page'];
		$out->setWorkingZone('content');
		
		$man_res = new Man_Reservation();
		
		if (isset($_GET['confirm']))
		{
			$confirm = importVar('confirm', true, 0);
			$id_course = importVar('id_course', true, 0);
			$id_laboratory = importVar('id_laboratory', true, 0);
			$id_category = importVar('id_category', true, 0);
			$title = importVar('title', false, '');
			$description = importVar('description', false, '');
			$date = importVar('date', false, '');
			$max_user = importVar('max_user', true, 0);
			$deadline = importVar('deadline', false, '');
			$from_time_h = importVar('from_time_h', false, '');
			$from_time_m = importVar('from_time_m', false, '');
			$to_time_h = importVar('to_time_h', false, '');
			$to_time_m = importVar('to_time_m', false, '');
			
			$date = Format::dateDb($date, 'date');
			
			$deadline = Format::dateDb($deadline, 'date');
			
			$from_time = $from_time_h.':'.$from_time_m.':00';
			
			$to_time = $to_time_h.':'.$to_time_m.':00';
			
			if ($date < date('Y-m-d') || $date < $deadline)
				Util::jump_to('index.php?modname=reservation&op=mod_event&amp;id_event='.$id_event.'&amp;error=date');
			
			if ($from_time >= $to_time)
				Util::jump_to('index.php?modname=reservation&op=mod_event&amp;id_event='.$id_event.'&amp;error=time');
			
			if ($confirm)
				$result = $man_res->modEvent($id_event, $id_course, $id_laboratory, $id_category, $title, $description, $date, $max_user, $deadline, $from_time, $to_time);
			if ($result)
				Util::jump_to('index.php?modname=reservation&op=view_event');
			Util::jump_to('index.php?modname=reservation&op=mod_event&amp;id_event='.$id_event.'&amp;error=laboratory');
		}
		
		$out->add
		(
			getTitleArea($lang->def('_SAVE'), '', $lang->def('_EVENT'))
			.'<div class="std_block">'
		);
		
		$error = importVar('error', false, '');
		
		if ($error !== '')
		{
			switch ($error)
			{
				case 'date':
					$out->add(getErrorUi($lang->def('_WRONG_DATE')));
				break;
				
				case 'time':
					$out->add(getErrorUi($lang->def('_WRONG_TIME')));
				break;
				
				case 'laboratory':
					$out->add(getErrorUi($lang->def('_LOCATION_BUSY')));
				break;
				
			}
		}
		
		$event = array();
		
		$event = $man_res->getEventInfo($id_event);
		
		$date = Format::date($event[EVENT_DATE], 'date');
		$deadline = Format::date($event[EVENT_DEADLINE], 'date');
		
		$from_time_h = $event[EVENT_FROM_TIME]{0}.$event[EVENT_FROM_TIME]{1};
		$from_time_m = $event[EVENT_FROM_TIME]{3}.$event[EVENT_FROM_TIME]{4};
		
		$to_time_h = $event[EVENT_TO_TIME]{0}.$event[EVENT_TO_TIME]{1};
		$to_time_m = $event[EVENT_TO_TIME]{3}.$event[EVENT_TO_TIME]{4};
		
		$out->add
		(
			Form::openForm('form_event', 'index.php?modname=reservation&amp;op=mod_event&amp;confirm=1')
			.Form::openElementSpace()
			.Form::getHidden('id_event', 'id_event', $event[EVENT_ID])
			.Form::getHidden('id_course', 'id_course', $event[EVENT_ID_COURSE])
			.Form::getTextfield($lang->def('_TITLE'), 'title', 'title', 255, $event[EVENT_TITLE])
			.Form::getTextarea($lang->def('_DESCRIPTION'), 'description', 'description', $event[EVENT_DESCRIPTION])
			.Form::getDropdown($lang->def('_LOCATION'), 'id_laboratory', 'id_laboratory', $man_res->getLaboratories(), $event[EVENT_ID_LABORATORY])
			.Form::getDropdown($lang->def('_CATEGORY'), 'id_category', 'id_category', $man_res->getCategory(), $event[EVENT_ID_CATEGORY])
			.Form::getDateField($lang->def('_DATE'), 'date', 'date', $date)
			.Form::getDateField($lang->def('_DEADLINE'), 'deadline', 'deadline', $deadline)
			.Form::getTextfield($lang->def('_MAX_USER'), 'max_user', 'max_user', 255, $event[EVENT_MAX_USER])
			.Form::getLineBox($lang->def('_FROM_TIME'), Form::getInputDropdown('', 'from_time_h', 'from_time_h', $man_res->getHours(), $from_time_h, false).' : '.Form::getInputDropdown('', 'from_time_m', 'from_time_m', $man_res->getMinutes(), $from_time_m, false))
			.Form::getLineBox($lang->def('_TO_TIME'), Form::getInputDropdown('', 'to_time_h', 'to_time_h', $man_res->getHours(), $to_time_h, false).' : '.Form::getInputDropdown('', 'to_time_m', 'to_time_m', $man_res->getMinutes(), $to_time_m, false))
			.Form::closeElementSpace()
			.Form::openButtonSpace()
			.Form::getButton('mod_event', 'mod_event', $lang->def('_SAVE'))
			.Form::getButton('undo', 'undo', $lang->def('_UNDO'))
			.Form::closeButtonSpace()
			.Form::closeForm()
		);
		$out->add('</div>', 'content');
	}
	
	function delEvent()
	{
		checkPerm('view');
		
		require_once(_base_.'/lib/lib.form.php');
		
		$id_event = importVar('id_event', true, 0);
		
		$lang =& DoceboLanguage::createInstance('reservation');
		
		$out = $GLOBALS['page'];
		$out->setWorkingZone('content');
		
		$man_res = new Man_Reservation();
		
		if (Get::req('confirm', DOTY_INT, 0) == 1)
		{
			$confirm = importVar('confirm', true, 0);
			
			if($confirm)
			{
				$result = $man_res->delEvent($id_event);
			}
			if ($result)
				Util::jump_to('index.php?modname=reservation&op=view_event');
			else
				Util::jump_to('index.php?modname=reservation&op=view_event&error=del_event');
		}
		
		$GLOBALS['page']->add
		(
			getTitleArea($lang->def('_DEL'), '', $lang->def('_EVENT'))
			.'<div class="std_block">'
			.getDeleteUi(	$lang->def('_AREYOUSURE'),
							$lang->def('_EVENT'),
							true,
							'index.php?modname=reservation&amp;op=del_event&amp;id_event='.$id_event.'&amp;confirm=1',
							'index.php?modname=reservation&amp;op=view_event'
						)
			.'</div>', 'content'
		);
	}
	
	function viewCategoy()
	{
		checkPerm('view');
		
		require_once(_base_.'/lib/lib.form.php');
		require_once(_base_.'/lib/lib.table.php');
		
		$lang =& DoceboLanguage::createInstance('reservation');
		
		$mod_perm = checkPerm('mod', true);
		
		$out = $GLOBALS['page'];
		$out->setWorkingZone('content');
		
		$man_res = new Man_Reservation();
		
		$category = array();
		$category = $man_res->viewCategory();
		
		$out->add(getTitleArea($lang->def('_CATEGORY')).'<div class="std_block">', 'content');
		
		$error = importVar('error', false, '');
		
		if ($error !== '')
		{
			switch ($error)
			{
				case 'del_category':
					$out->add(getErrorUi($lang->def('_DEL_CATEGORY_ERROR')));
				break;
			}
		}
		
		$tb = new Table(10, $lang->def('_CATEGORY_CAPTION'), $lang->def('_CATEGORY_CAPTION'));
			$tb->initNavBar('ini', 'button');
			
			$ini = $tb->getSelectedElement();
			
			$cont_h = array
			(
				$lang->def('_NAME'),
				$lang->def('_DESCRIPTION')
			);
			$type_h = array('', '');
			if ($mod_perm)
			{
				$type_h = array('', '', 'image', 'image');
				$cont_h = array
				(
					$lang->def('_NAME'),
					$lang->def('_DESCRIPTION'),
					'<img src="'.getPathImage().'/standard/edit.png" title="'.$lang->def('_MOD').'" alt="'.$lang->def('_MOD').'" />',
					'<img src="'.getPathImage().'/standard/delete.png" title="'.$lang->def('_DEL').'" alt="'.$lang->def('_DEL').'" />'
				);
			}
			
			$tb->setColsStyle($type_h);
			$tb->addHead($cont_h);
			if ($category)
			{
				foreach($category as $categ)
				{
					$count = array();
					
					$count[] = $categ[CATEGORY_NAME];
					
					if ($categ[CATEGORY_MAX_SUBSCRIPTION])
						$count[] = $categ[CATEGORY_MAX_SUBSCRIPTION];
					else
						$count[] = $lang->def('_UNLIMITED_SUBSCRIPTION');
					
					if ($mod_perm)
					{
						$count[] = '<a href="index.php?modname=reservation&amp;op=mod_category&amp;id_category='.$categ[CATEGORY_ID].'"><img src="'.getPathImage().'/standard/edit.png" title="'.$lang->def('_MOD').'" alt="'.$lang->def('_MOD').'" /></a>';
						$count[] = '<a href="index.php?modname=reservation&amp;op=del_category&amp;id_category='.$categ[CATEGORY_ID].'"><img src="'.getPathImage().'/standard/delete.png" title="'.$lang->def('_DEL').'" alt="'.$lang->def('_DEL').'" /></a>';
					}
					$tb->addBody($count);
				}
				
				require_once(_base_.'/lib/lib.dialog.php');
				setupHrefDialogBox('a[href*=del_category]');
			}
			if($mod_perm)
			{
				$tb->addActionAdd('<a class="ico-wt-sprite subs_add" href="index.php?modname=reservation&amp;op=add_category" title="'.$lang->def('_ADD').'">'
					.'<span>'.$lang->def('_ADD').'</span></a>');
			}
			$out->add($tb->getTable()
			.$tb->getNavBar($ini, count($category))
			.'</div>'
			);
	}
	
	function addCategoy()
	{
		checkPerm('view');
		
		require_once(_base_.'/lib/lib.form.php');
		
		$lang =& DoceboLanguage::createInstance('reservation');
		
		$out = $GLOBALS['page'];
		$out->setWorkingZone('content');
		
		$man_res = new Man_Reservation();
		
		if (isset($_GET['confirm']))
		{
			$confirm = importVar('confirm', true, 0);
			
			$name = importVar('name', false, '');
			$max_subscription = importVar('max_subscription', true, 0);
			
			if ($name == '')
				Util::jump_to('index.php?modname=reservation&op=add_category&amp;error=name');
			
			if ($confirm)
				$result = $man_res->addCategory($name, $max_subscription);
			if ($result)
				Util::jump_to('index.php?modname=reservation&op=view_category');
			Util::jump_to('index.php?modname=reservation&op=add_category&amp;error=category');
		}
		
		$out->add
		(
			getTitleArea($lang->def('_NEW_CATEGORY'), '', $lang->def('_NEW_CATEGORY'))
			.'<div class="std_block">'
		);
		
		$error = importVar('error', false, '');
		
		if ($error !== '')
		{
			switch ($error)
			{
				case 'name':
					$out->add(getErrorUi($lang->def('_WRONG_NAME_CATEGORY')));
				break;
				
				case 'category':
					$out->add(getErrorUi($lang->def('_WRONG_INSERT_CATEGORY')));
				break;
			} 
		}
		
		$out->add
		(
			Form::openForm('form_event', 'index.php?modname=reservation&amp;op=add_category&amp;confirm=1')
			.Form::openElementSpace()
			.Form::getTextfield($lang->def('_NAME'), 'name', 'name', 255)
			.Form::getTextfield($lang->def('_CATEGORY_MAX_SUBSCRIPTION'), 'max_subscription', 'max_subscription', 255, '0')
			.Form::closeElementSpace()
			.Form::openButtonSpace()
			.Form::getButton('send_category', 'send_category', $lang->def('_NEW_CATEGORY'))
			.Form::getButton('undo_cat', 'undo_cat', $lang->def('_UNDO'))
			.Form::closeButtonSpace()
			.Form::closeForm()
		);
		
		$out->add('</div>', 'content');
	}
	
	function modCategoy()
	{
		checkPerm('view');
		
		require_once(_base_.'/lib/lib.form.php');
		
		$id_category = importVar('id_category', true, 0);
		
		$lang =& DoceboLanguage::createInstance('reservation');
		
		$out = $GLOBALS['page'];
		$out->setWorkingZone('content');
		
		$man_res = new Man_Reservation();
		
		if (isset($_GET['confirm']))
		{
			$confirm = importVar('confirm', true, 0);
			
			$name = importVar('name', false, '');
			$max_subscription = importVar('max_subscription', true, 0);
			
			if ($name == '')
				Util::jump_to('index.php?modname=reservation&op=mod_category&amp;error=name&amp;id_category='.$id_category);
			
			if ($confirm)
				$result = $man_res->modCategory($id_category, $name, $max_subscription);
			if ($result)
				Util::jump_to('index.php?modname=reservation&op=view_category');
			Util::jump_to('index.php?modname=reservation&op=mod_category&amp;id_category='.$id_category.'&amp;error=category');
		}
		
		$out->add
		(
			getTitleArea($lang->def('_MOD'), '', $lang->def('_NEW_CATEGORY'))
			.'<div class="std_block">'
		);
		
		$error = importVar('error', false, '');
		
		if ($error !== '')
		{
			switch ($error)
			{
				case 'name':
				case 'category':
					$out->add(getErrorUi($lang->def('_OPERATION_FAILURE')));
				break;
			} 
		}
		
		$out->add
		(
			Form::openForm('form_event', 'index.php?modname=reservation&amp;op=mod_category&amp;confirm=1')
			.Form::openElementSpace()
			.Form::getTextfield($lang->def('_NAME'), 'name', 'name', 255, $man_res->getCategoryName($id_category))
			.Form::getTextfield($lang->def('_CATEGORY_MAX_SUBSCRIPTION'), 'max_subscription', 'max_subscription', 255, $man_res->getCategoryMaxSubscription($id_category))
			.Form::getHidden('id_category', 'id_category', $id_category)
			.Form::closeElementSpace()
			.Form::openButtonSpace()
			.Form::getButton('mod_category', 'mod_category', $lang->def('_SAVE'))
			.Form::getButton('undo_cat', 'undo_cat', $lang->def('_UNDO'))
			.Form::closeButtonSpace()
			.Form::closeForm()
		);
		
		$out->add('</div>', 'content');
	}
	
	function delCategoy()
	{
		checkPerm('view');
		
		require_once(_base_.'/lib/lib.form.php');
		
		$id_category = importVar('id_category', true, 0);
		
		$lang =& DoceboLanguage::createInstance('reservation');
		
		$out = $GLOBALS['page'];
		$out->setWorkingZone('content');
		
		$man_res = new Man_Reservation();
		
		if (Get::req('confirm', DOTY_INT, 0) == 1)
		{
			$confirm = importVar('confirm', true, 0);
			
			if($confirm)
			{
				$result = $man_res->delCategory($id_category);
			}
			if ($result)
				Util::jump_to('index.php?modname=reservation&op=view_category');
			else
				Util::jump_to('index.php?modname=reservation&op=view_category&error=del_category');
		}
		
		$GLOBALS['page']->add
		(
			getTitleArea($lang->def('_DEL_CATEGORY_TITLE'), '', $lang->def('_NEW_CATEGORY'))
			.'<div class="std_block">'
			.getDeleteUi(	$lang->def('_AREYOUSURE'),
							$lang->def('_DEL_CATEGORY_INFO'),
							true,
							'index.php?modname=reservation&amp;op=del_category&amp;id_category='.$id_category.'&amp;confirm=1',
							'index.php?modname=reservation&amp;op=view_category'
						)
			.'</div>', 'content'
		);
	}
	
	function viewRegistration()
	{
		checkPerm('view');
		
		require_once(_base_.'/lib/lib.navbar.php');
		require_once(_base_.'/lib/lib.table.php');
		
		$lang =& DoceboLanguage::createInstance('reservation');
		
		$mod_perm 	= checkPerm('mod', true);
		
		$out = $GLOBALS['page'];
		$out->setWorkingZone('content');
		
		$man_res = new Man_Reservation();
		
		$acl =& Docebo::user()->getAcl();
		$user_idst = getLogUserId();
		
		$events = array();
		
		$events = $man_res->viewEventsForSubscribedTab();
		
		$out->add(getTitleArea($lang->def('_RESERVATION')).'<div class="std_block">', 'content');
		
		$error = importVar('error', false, '');
		
		if ($error !== '')
		{
			switch ($error)
			{
				case 'del_registration':
					$out->add(getErrorUi($lang->def('_DEL_REGISTRATION_ERROR')));
				break;
				
				case 'insert':
					$out->add(getErrorUi($lang->def('_INS_REGISTRATION_ERROR')));
				break;
			}
		}
		
		if(count($events))
		{
			$tb = new Table(10, $lang->def('_RESERVATION_CAPTION'), $lang->def('_RESERVATION_SUMMARY'));
			$tb->initNavBar('ini', 'button');
			
			$ini = $tb->getSelectedElement();
			
			$cont_h = array
			(
				$lang->def('_TITLE'),
				$lang->def('_DATE'),
				$lang->def('_NUMBER_SUBSCRIBED'),
				$lang->def('_DEADLINE'),
			);
			$type_h = array('', '', '', '');
			if ($mod_perm)
			{
				$type_h = array('', '', '', '', 'image','image', 'image', 'image');
				$cont_h = array
				(
					$lang->def('_TITLE'),
					$lang->def('_DATE'),
					$lang->def('_NUMBER_SUBSCRIBED'),
					$lang->def('_DEADLINE'),
					'<img src="'.getPathImage().'/standard/identity.png" title="'.$lang->def('_VIEW_USER_SUBSCRIBED').'" alt="'.$lang->def('_VIEW_USER_SUBSCRIBED').'" />',
					'<img src="'.getPathImage().'/standard/add.png" title="'.$lang->def('_ADD_USER').'" alt="'.$lang->def('_ADD_USER').'" />',
					'<img src="'.getPathImage().'/standard/edit.png" title="'.$lang->def('_MOD').'" alt="'.$lang->def('_MOD').'" />',
					'<img src="'.getPathImage().'/standard/delete.png" title="'.$lang->def('_DEL').'" alt="'.$lang->def('_DEL').'" />'
				);
			}
			$tb->setColsStyle($type_h);
			$tb->addHead($cont_h);

			if ($events) {
			foreach($events as $event)
			{
				$count = array();
				
				$count[] = $event[EVENT_TITLE];
				$count[] = Format::date($event[EVENT_DATE], 'date'); 
				$count[] = $event[EVENT_USER_SUBSCRIBED].'/'.$event[EVENT_MAX_USER];
				$count[] = Format::date($event[EVENT_DEADLINE], 'date');
				if ($mod_perm)
				{
					$count[] = '<a href="index.php?modname=reservation&amp;op=view_user_event&amp;id_event='.$event[EVENT_ID].'"><img src="'.getPathImage().'/standard/identity.png" title="'.$lang->def('_VIEW_USER_SUBSCRIBED').'" alt="'.$lang->def('_VIEW_USER_SUBSCRIBED').'" /></a>';
					$count[] = '<a href="index.php?modname=reservation&amp;op=add_registration&amp;id_event='.$event[EVENT_ID].'&amp;id_course='.$event[EVENT_ID_COURSE].'"><img src="'.getPathImage().'/standard/add.png" title="'.$lang->def('_ADD_USER').'" alt="'.$lang->def('_ADD_USER').'" /></a>';
					$count[] = '<a href="index.php?modname=reservation&amp;op=mod_event&amp;id_event='.$event[EVENT_ID].'"><img src="'.getPathImage().'/standard/edit.png" title="'.$lang->def('_MOD').'" alt="'.$lang->def('_MOD').'" /></a>';
					$count[] = '<a href="index.php?modname=reservation&amp;op=del_event&amp;id_event='.$event[EVENT_ID].'"><img src="'.getPathImage().'/standard/delete.png" title="'.$lang->def('_DEL').'" alt="'.$lang->def('_DEL').'" /></a>';
				}
				$tb->addBody($count);
			}}
			if($mod_perm)
			{
				$tb->addActionAdd('<a href="index.php?modname=reservation&amp;op=add_event">
					<img src="'.getPathImage().'standard/add.png" title="'.$lang->def('_NEW_EVENT').'" alt="'.$lang->def('_NEW_EVENT').'" /> '
					.$lang->def('_NEW_EVENT').'</a>');
			}
			$out->add($tb->getTable()
			.$tb->getNavBar($ini, count($events))
			.'</div>'
			);
		}
		else
		{
			if($mod_perm)
			{
				$out->add('<div class="events_action_top"><p><a href="index.php?modname=reservation&amp;op=add_event">'.$lang->def('_NEW_EVENT').'</a></p></div>', 'content');
			}
			$out->add($lang->def('_NO_RESERVATION_FOUND'), 'content');
		}
		
		$out->add('</div>', 'content');
	}
	
	function viewUserEvent()
	{
		checkPerm('view');
		
		require_once(_base_.'/lib/lib.navbar.php');
		require_once(_base_.'/lib/lib.table.php');
		
		$lang =& DoceboLanguage::createInstance('reservation');
		
		$id_event = importVar('id_event', true, 0);
		
		$mod_perm = checkPerm('mod', true);
		
		$out = $GLOBALS['page'];
		$out->setWorkingZone('content');
		
		$man_res = new Man_Reservation();
		
		$acl =& Docebo::user()->getAcl();
		
		$acl_man =& Docebo::user()->getAclManager();
		
		$user_idst = getLogUserId();
		
		$user_subscribed = array();
		
		$user_sunscribed = $man_res->getSubscribedUserIdst($id_event);
		
		$user_info = array();
		
		$user_info =& $acl_man->getUsers($user_sunscribed);
		
		$out->add
		(
			getTitleArea($lang->def('_VIEW_EVENT_USER'), '', $lang->def('_EVENT'))
			.'<div class="std_block">'
		);
		
		$error = importVar('error', false, '');
		
		if ($error !== '')
		{
			switch ($error)
			{
				case 'del_registration':
					$out->add(getErrorUi($lang->def('_DEL_REGISTRATION_ERROR')));
				break;
			}
		}
		
		if ($user_info)
		{
			$tb = new Table(10, $lang->def('_RESERVATION_CAPTION'), $lang->def('_RESERVATION_SUMMARY'));
			$tb->initNavBar('ini', 'button');
			
			$ini = $tb->getSelectedElement();
			
			$cont_h = array
			(
				$lang->def('_USERNAME'),
				$lang->def('_FIRSTNAME'),
				$lang->def('_LASTNAME'),
				$lang->def('_EMAIL'),
			);
			$type_h = array('', '', '', '');
			
			if ($mod_perm)
			{
				$cont_h = array
				(
					$lang->def('_USERNAME'),
					$lang->def('_FIRSTNAME'),
					$lang->def('_LASTNAME'),
					$lang->def('_EMAIL'),
					'<img src="'.getPathImage().'/standard/delete.png" title="'.$lang->def('_REM_USER').'" alt="'.$lang->def('_REM_USER').'" />'
				);
				$type_h = array('', '', '', '', 'img');
			}
			
			$tb->setColsStyle($type_h);
			$tb->addHead($cont_h);
			if ($user_info)
			{
				foreach ($user_info as $info_user)
				{
					$count = array();
					
					$count[] = $acl_man->relativeId($info_user[ACL_INFO_USERID]);
					$count[] = $info_user[ACL_INFO_FIRSTNAME];
					$count[] = $info_user[ACL_INFO_LASTNAME];
					$count[] = $info_user[ACL_INFO_EMAIL];
					$count[] = '<a href="index.php?modname=reservation&amp;op=del_registration&amp;id_user='.$info_user[ACL_INFO_IDST].'&amp;id_event='.$id_event.'"><img src="'.getPathImage().'/standard/delete.png" title="'.$lang->def('_REM_USER').'" alt="'.$lang->def('_REM_USER').'" /></a>';
					
					$tb->addBody($count);
				}
			}
			if($mod_perm)
			{
				$tb->addActionAdd('<a href="index.php?modname=reservation&amp;op=add_event">
					<img src="'.getPathImage().'standard/add.png" title="'.$lang->def('_NEW_EVENT').'" alt="'.$lang->def('_NEW_EVENT').'" /> '
					.$lang->def('_NEW_EVENT').'</a>');
			}
			$out->add($tb->getTable()
			.$tb->getNavBar($ini, count($user_info))
			);
			$out->add('<a href="index.php?modname=reservation&amp;op=excel&amp;id_event='.$id_event.'" target="_blank">'.$lang->def('_EXPORT_XLS').'</a>', 'content');
		}
		else
		{
			$out->add($lang->def('_NO_USERS_FOUND'), 'content');
		}
		
		$out->add(getBackUi('index.php?modname=reservation&amp;op=view_registration', $lang->def('_BACK')), 'content');
		$out->add('</div>', 'content');
	}
	
	function getExcelFile()
	{
		
		$lang =& DoceboLanguage::createInstance('reservation');
		
		$id_event = importVar('id_event', true, 0);
		
		$man_res = new Man_Reservation();
		
		$acl_man =& Docebo::user()->getAclManager();
		
		$user_subscribed = array();
		
		$user_sunscribed = $man_res->getSubscribedUserIdst($id_event);
		
		$user_info = array();
		
		$user_info =& $acl_man->getUsers($user_sunscribed);
		
		$excel_header = '"'.$lang->def('_USERNAME').'"'."\t";
		$excel_header .= '"'.$lang->def('_FIRSTNAME').'"'."\t";
		$excel_header .= '"'.$lang->def('_LASTNAME').'"'."\t";
		$excel_header .= '"'.$lang->def('_EMAIL').'"'."\n";
		
		if ($user_info)
		{
			$excel_data = '';
			
			foreach ($user_info as $info_user)
			{
				$excel_data .= '"'.$acl_man->relativeId($info_user[ACL_INFO_USERID]).'"'."\t";
				$excel_data .= '"'.$info_user[ACL_INFO_FIRSTNAME].'"'."\t";
				$excel_data .= '"'.$info_user[ACL_INFO_LASTNAME].'"'."\t";
				$excel_data .= '"'.$info_user[ACL_INFO_EMAIL].'"'."\n";
			}
		}
		
		$excel_header = str_replace("\r","",$excel_header);
		$excel_data = str_replace("\r","",$excel_data);
		
		$excel = $excel_header.$excel_data;
		
		header("Content-type: application/x-msdownload");
		header("Content-Disposition: attachment; filename=students.xls");
		header("Pragma: no-cache");
		header("Expires: 0");
		
		ob_end_clean();
		
		echo $excel;
		
		exit(0);
	}
	
	function addRegistration()
	{
		checkperm('mod');
		
		require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
		require_once(_base_.'/lib/lib.form.php');
		require_once(_base_.'/lib/lib.userselector.php');
	
		$id_course = importVar('id_course', true, 0);
		$id_event = importVar('id_event', true, 0);
		
		$man_res = new Man_Reservation();
		
		$subscribed = array();
		$subscribed = $man_res->getSubscribedUserIdst($id_event);
		
		$subscribed_empty = array();
		
		$lang =& DoceboLanguage::CreateInstance('reservation');
		$out =& $GLOBALS['page'];
	
		$user_select = new UserSelector();
		$user_select->show_user_selector = TRUE;
		$user_select->show_group_selector = TRUE;
		$user_select->show_orgchart_selector = FALSE;
		$user_select->show_orgchart_simple_selector = FALSE;
		
		// ema -- add requested_tab to show user selector
		$user_select->requested_tab = PEOPLEVIEW_TAB;
		if (!$subscribed)
			$user_select->resetSelection($subscribed_empty);
		else
			$user_select->resetSelection($subscribed);
		
		$acl_man =& Docebo::user()->getAclManager();
		$user_select->setUserFilter('exclude', array($acl_man->getAnonymousId()));
		
		$user_select->loadSelector('index.php?modname=reservation&amp;op=add_registration&amp;id_course='.$id_course.'&amp;id_event='.$id_event,
				$lang->def('_SUBSCRIBE_EVENT'),
				$lang->def('_CHOOSE_SUBSCRIBE'),
				true);
	}
	
	function sendRegistration()
	{
		checkPerm('view');
		
		require_once(_base_.'/lib/lib.userselector.php');
		
		$id_course = importVar('id_course', true, 0);
		$id_event = importVar('id_event', true, 0);
		
		$lang =& DoceboLanguage::CreateInstance('reservation');
		$out =& $GLOBALS['page'];
		
		$man_res = new Man_Reservation();
		
		$id_category = $man_res->getEventCategory($id_event);
		
		$user_select = new UserSelector();
		
		$user_subscribed = array();
		$user_subscribed = $man_res->getSubscribedUserIdst($id_event);
		
		$user_selected = array();
		$user_selected = $user_select->getSelection($_POST);
		
		$wrong_result = false;
		
		$user_deleted = array_diff($user_subscribed, $user_selected);
		
		$wrong_result = false;
		
		foreach ($user_deleted as $del)
		{
			$result = $man_res->delSubscription($del, $id_event);
			
			if (!$result)
					$wrong_result = true;
		}
		
		foreach ($user_selected as $user)
		{
			if ($man_res->controlMaxSubscriptionForCategory($id_category, $user))
			{
				$result = $man_res->addSubscription($user, $id_event);
				
				if (!$result)
					$wrong_result = true;
			}
			else
				$wrong_result = true;
		}
		
		if ($wrong_result)
			Util::jump_to('index.php?modname=reservation&op=view_registration&error=insert');
		Util::jump_to('index.php?modname=reservation&op=view_registration');
	}
	
	function delRegistration()
	{
		checkPerm('view');
		
		require_once(_base_.'/lib/lib.form.php');
		
		$id_event = importVar('id_event', true, 0);
		$id_user = importVar('id_user', true, 0);
		
		$lang =& DoceboLanguage::createInstance('reservation');
		
		$out = $GLOBALS['page'];
		$out->setWorkingZone('content');
		
		$man_res = new Man_Reservation();
		
		if (Get::req('confirm', DOTY_INT, 0) == 1)
		{
			$confirm = importVar('confirm', true, 0);
			
			if($confirm)
			{
				$result = $man_res->delSubscription($id_user, $id_event);
			}
			if ($result)
				Util::jump_to('index.php?modname=reservation&op=view_user_event&id_event='.$id_event);
			Util::jump_to('index.php?modname=reservation&op=view_user_event&id_event='.$id_event.'&amp;error=del_registration');
		}
		
		$GLOBALS['page']->add
		(
			getTitleArea($lang->def('_DEL_SUBSCRIPTION_TITLE'), '', $lang->def('_EVENT'))
			.'<div class="std_block">'
			.getDeleteUi(	$lang->def('_AREYOUSURE_DEL_SUBSCRIPTION'),
							$lang->def('_DEL_SUBSCRIPTION_INFO'),
							true,
							'index.php?modname=reservation&amp;op=del_registration&amp;id_event='.$id_event.'&amp;id_user='.$id_user.'&amp;confirm=1',
							'index.php?modname=reservation&amp;op=view_user_event&id_event='.$id_event
						)
			.'</div>', 'content'
		);
	}
}

function setRoomViewPerm()
{
	checkPerm('view');

	$id_event = importVar('id_event', true, 0);

	require_once($GLOBALS['where_lms'].'/lib/lib.reservation_perm.php');
	require_once(_base_.'/lib/lib.userselector.php');
	$mdir=new UserSelector();

	$out=& $GLOBALS["page"];
	$out->setWorkingZone("content");
	$lang=& DoceboLanguage::createInstance('reservation', 'lms');

	$roomperm = new ReservationRoomPermissions();

	//$res = getTitleArea($lang->def('_RESERVATION_TITLE'), 'reservation');

	$back_url = 'index.php?modname=reservation&op=view_event';
	
	if( isset($_POST['okselector']) )
	{
		$arr_selection=$mdir->getSelection($_POST);
		//$arr_unselected=$mdir->getUnselected();
		
		$roomperm->addReservationPerm("view", $id_event, $arr_selection);
		$roomperm->removeReservationPerm("view", $id_event, $arr_unselected);
		
		Util::jump_to(str_replace("&amp;", "&", $back_url));
	}
	else if( isset($_POST['cancelselector']) ) {
		Util::jump_to(str_replace("&amp;", "&", $back_url));
	}
	else {

		if( !isset($_GET['stayon']) ) {
			$all_perm = $roomperm->getAllReservationPerm($id_event);
			if(isset($all_perm["view"])) $mdir->resetSelection($all_perm["view"]);
		}
		
		$acl_manager =& Docebo::user()->getAclManager();

		$url='index.php?modname=reservation&amp;op=set_room_view_perm&amp;id_event='.$id_event;
		//$mdir->setNFields(0);
		$mdir->show_group_selector=TRUE;
		$mdir->show_orgchart_selector=FALSE;
		
		list($id_course) = sql_fetch_row(sql_query("SELECT idCourse FROM ".$GLOBALS['prefix_lms']."_reservation_events WHERE idEvent = '".$id_event."'"));
		
		$arr_idstGroup = $acl_manager->getGroupsIdstFromBasePath('/lms/course/'.$id_course.'/subscribed/');
		$me = array(getLogUserId());
		$mdir->setUserFilter('exclude', $me);
		$mdir->setUserFilter('group',$arr_idstGroup);
		//$mdir->setGroupFilter('path', '/lms/course/'.$_SESSION['idCourse'].'/group');
		
		$mdir->loadSelector($url,
			$lang->def( '_VIEW_PERMISSION' ), "", TRUE);
	}

}

function checkRoomPerm($perm_arr, $user_idst) {

	if ((!is_array($perm_arr)) || (count($perm_arr) < 1))
		$res=TRUE;
	else if (in_array($user_idst, $perm_arr))
		$res=TRUE;
	else
		$res=FALSE;

	return $res;
}

function reservationDispatch($op)
{
	if (isset($_POST['undo']))
		$op = 'view_event';
	if (isset($_POST['undo_cat']))
		$op = 'view_category';
	if (isset($_POST['undo_lab']))
		$op = 'view_laboratory';
	if (isset($_POST['okselector']))
		if(isset($_GET['id_course']))
			$op = 'send_registration';
	if (isset($_POST['cancelselector']))
		if(isset($_GET['id_course']))
			$op = 'view_registration';
	switch ($op)
	{
		case 'view_event':
			viewEvent();
		break;
		
		case 'add_event':
			addEvent();
		break;
		
		case 'mod_event':
			modEvent();
		break;
		
		case 'del_event':
			delEvent();
		break;
		
		case 'view_category':
			viewCategoy();
		break;
		
		case 'add_category':
			addCategoy();
		break;
		
		case 'mod_category':
			modCategoy();
		break;
		
		case 'del_category':
			delCategoy();
		break;
		
		/*case 'view_laboratory':
			viewLaboratories();
		break;
		
		case 'add_laboratory':
			addLaboratory();
		break;
		
		case 'mod_laboratory':
			modLaboratory();
		break;
		
		case 'del_laboratory':
			delLaboratory();
		break;*/
		
		case 'view_registration':
			viewRegistration();
		break;
		
		case 'view_user_event':
			viewUserEvent();
		break;
		
		case 'excel':
			getExcelFile();
		break;
		
		case 'add_registration':
			addRegistration();
		break;
		
		case 'send_registration':
			sendRegistration();
		break;
		
		case 'del_registration':
			delRegistration();
		break;
		
		case 'set_room_view_perm':
			setRoomViewPerm();
		break;
	}
}
?>