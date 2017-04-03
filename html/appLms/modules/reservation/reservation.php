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
	require_once($GLOBALS['where_lms'].'/lib/lib.classroom.php');
	
	function reservation()
	{
		checkPerm('view');
		
		require_once(_base_.'/lib/lib.tab.php');
		require_once(_base_.'/lib/lib.urlmanager.php');
		require_once(_base_.'/lib/lib.form.php');
		require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
		
		$mod_perm 	= checkPerm('mod', true);
		
		$lang =& DoceboLanguage::createInstance('reservation');
		$um =& UrlManager::getInstance("reservation");
		
		$out = $GLOBALS['page'];
		$out->setWorkingZone('content');
		
		$man_res = new Man_Reservation(); 
		
		$tab_man = new TabView('reservation', '');
		
		$tab_events = new TabElemDefault('events', 
							$lang->def('_RESERVATION_EVENTS'));
		
		$tab_my_events = new TabElemDefault('my_events', 
								$lang->def('_RESERVATION_MY_EVENTS'));
		
		$tab_past_event = new TabElemDefault('past_events', 
								$lang->def('_RESERVATION_PAST_EVENTS'));
		
		if ($mod_perm)
		{
			$tab_subscribed_user = new TabElemDefault('subscribed_user', 
										$lang->def('_RESERVATION_SUBSCRIBED_USER'));
		}
		
		$tab_man->addTab($tab_events);
		$tab_man->addTab($tab_my_events);
		$tab_man->addTab($tab_past_event);
		
		if ($mod_perm)
			$tab_man->addTab($tab_subscribed_user);
		
		$tab_man->parseInput($_POST, $_SESSION);
		$active_tab = $tab_man->getActiveTab();
		
		if($active_tab != 'events' && $active_tab != 'my_events' && $active_tab != 'past_events' && $active_tab != 'subscribed_user')
		{
			$active_tab = importVar('active_tab', false, 'events');
			$tab_man->setActiveTab($active_tab);
		}
		
		$out->add(getTitleArea(Lang::t('_RESERVATION', 'reservation')).'<div class="std_block">', 'content');
		
		$out->add(Form::openForm('tab_reservation', 'index.php?modname=reservation&amp;op=reservation&amp;confirm=1&amp;ap=mod_profile&amp;from=2')
				.$tab_man->printTabView_Begin('', false), 'content'
			);
		
		switch ($active_tab)
		{
			case 'events':
				events();
			break;
			
			case 'my_events':
				myEvents();
			break;
			
			case 'past_events':
				pastEvents();
			break;
			
			case 'subscribed_user':
				subscribedUser();
			break;
		}
		
		$out->add($tab_man->printTabView_End().Form::closeForm(), 'content');
		
		$out->add('</div>', 'content');
	}
	
	function events()
	{
		checkPerm('view');
		
		require_once(_base_.'/lib/lib.navbar.php');
		require_once(_base_.'/lib/lib.table.php');
		
		require_once($GLOBALS['where_lms'].'/lib/lib.reservation_perm.php');
		$roomperm = new ReservationRoomPermissions();
		$perm = 'view';
		
		$lang =& DoceboLanguage::createInstance('reservation');
		
		$order_by = importVar('order_by', false, 'c.name, e.title, e.date, e.deadLine');
		
		$mod_perm = checkPerm('mod', true);
		
		$id_course = $_SESSION['idCourse'];
		
		$out = $GLOBALS['page'];
		$out->setWorkingZone('content');
		
		$man_res = new Man_Reservation();
		
		$acl =& Docebo::user()->getAcl();
		$user_idst = getLogUserId();
		
		$events = array();
		
		$events = $man_res->viewEvents($id_course, $order_by);
		
		if($events)
		{
			$cont_h = array
			(
				(isset($_GET['order_by']) && $_GET['order_by'] == 'c.name, e.title' ? '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=events&amp;order_by=c.name, e.title DESC">'.$lang->def('_TITLE').'</a>' : '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=events&amp;order_by=c.name, e.title">'.$lang->def('_TITLE').'</a>'),
				(isset($_GET['order_by']) && $_GET['order_by'] == 'c.name, l.name' ? '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=events&amp;order_by=c.name, l.name DESC">'.$lang->def('_LOCATION').'</a>' : '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=events&amp;order_by=c.name, l.name">'.$lang->def('_LOCATION').'</a>'),
				(isset($_GET['order_by']) && $_GET['order_by'] == 'c.name, e.date' ? '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=events&amp;order_by=c.name, e.date DESC">'.$lang->def('_DATE').'</a>' : '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=events&amp;order_by=c.name, e.date">'.$lang->def('_DATE').'</a>'),
				(isset($_GET['order_by']) && $_GET['order_by'] == 'c.name, e.fromTime' ? '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=events&amp;order_by=c.name, e.fromTime DESC">'.$lang->def('_FROM_TIME').'</a>' : '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=events&amp;order_by=c.name, e.fromTime">'.$lang->def('_FROM_TIME').'</a>'),
				(isset($_GET['order_by']) && $_GET['order_by'] == 'c.name, e.toTime' ? '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=events&amp;order_by=c.name, e.toTime DESC">'.$lang->def('_TO_TIME').'</a>' : '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=events&amp;order_by=c.name, e.toTime">'.$lang->def('_TO_TIME').'</a>'),
				$lang->def('_NUMBER_SUBSCRIBED'),
				$lang->def('_AVAILABLE_PLACES'),
				(isset($_GET['order_by']) && $_GET['order_by'] == 'c.name, e.deadLine' ? '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=events&amp;order_by=c.name, e.deadLine DESC">'.$lang->def('_DEADLINE').'</a>' : '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=events&amp;order_by=c.name, e.deadLine">'.$lang->def('_DEADLINE').'</a>'),
				$lang->def('_REGISTRATION')
			);
			$type_h = array('', '', '', '', '', '', '', '', '');
			
			$tb = new Table(100000);
			$tb->setColsStyle($type_h);
			$tb->addHead($cont_h);
			
			$temp_cat = '';
			
			foreach($events as $event)
			{
				$all_perm = $roomperm->getAllReservationPerm($event[EVENT_ID]);
				$can_view = ( isset($all_perm[$perm]) ? checkRoomPerm($all_perm[$perm], $user_idst) : TRUE );
				
				if ($can_view || $mod_perm)
				{
					if ($temp_cat != $event[EVENT_CATEGORY_NAME])
					{
						if ($temp_cat != '')
						{
							$out->add($tb->getTable());
							$out->add('<br/>');
						}
						
						$temp_cat = $event[EVENT_CATEGORY_NAME];
						
						$table_caption = $event[EVENT_CATEGORY_NAME];
						$max_subscription = $man_res->getMaxSubscriptionForCategory($event[EVENT_ID_CATEGORY]);
						if ($max_subscription)
							$table_caption .= ' : '.$lang->def('_MAX_SUBSCRIPTION_FOR_CATEGORY').' : '.$max_subscription; 
						
						$tb = new Table(100000, $table_caption);
						$tb->setColsStyle($type_h);
						$tb->addHead($cont_h);
					}
					
					$count = array();
					
					$count[] = $event[EVENT_TITLE];
					if ($event[EVENT_ID_LABORATORY])
						$count[] = '<a href="index.php?modname=reservation&amp;op=info_location&amp;active_tab=events&amp;id_location='.$event[EVENT_ID_LABORATORY].(isset($_GET['order_by']) ? '&amp;order_by='.$_GET['order_by'] : '').'">'.$event[EVENT_LABORATORY_NAME].'</a>';
					else
						$count[] = $event[EVENT_LABORATORY_NAME];
					$count[] = Format::date($event[EVENT_DATE], 'date');
					$count[] =  $event[EVENT_FROM_TIME];
					$count[] =  $event[EVENT_TO_TIME];
					$count[] = $event[EVENT_USER_SUBSCRIBED].'/'.$event[EVENT_MAX_USER];
					if($event[EVENT_AVAILABLE_PLACES] > 0)
						$count[] = $event[EVENT_AVAILABLE_PLACES];
					else
						$count[] = '0';
					$count[] = Format::date($event[EVENT_DEADLINE], 'date');
					if ($man_res->controlUserSubscription(getLogUserId(), $event[EVENT_ID]))
						$count[] = '<a href="index.php?modname=reservation&amp;op=del_subscription&amp;id_event='.$event[EVENT_ID].'">'.$lang->def('_CANCEL_REGISTRATION').'</a>';
					else
					{
						if ($event[EVENT_AVAILABLE_PLACES] > 0)
						{
							if ($man_res->controlMaxSubscriptionForCategory($event[EVENT_ID_CATEGORY], getLogUserId()))
								$count[] = '<a href="index.php?modname=reservation&amp;op=add_subscription&amp;id_event='.$event[EVENT_ID].'&amp;confirm=1&amp;ap=mod_profile&amp;from=2">'.$lang->def('_REGISTER').'</a>';
							else
							{
								if ($man_res->controlSwitchPossibility($event[EVENT_ID_COURSE], $event[EVENT_ID_CATEGORY], getLogUserId()))
									$count[] = '<a href="index.php?modname=reservation&amp;op=switch_subscription&amp;id_event='.$event[EVENT_ID].'&amp;id_course='.$id_course.'&amp;id_category='.$event[EVENT_ID_CATEGORY].'">'.$lang->def('_SWITCH_REGISTRATION').'</a>';
								else
									$count[] = $lang->def('_ROOM_FULL');
							}
						}
						else
							$count[] = $lang->def('_ROOM_FULL');
					}
					$tb->addBody($count);
				}
			}
			$out->add($tb->getTable()
			.'</div>'
			);
		}
		else
		{
			$out->add($lang->def('_NO_RESERVATION_FOUND'), 'content');
		}
	}
	
	function myEvents()
	{
		checkPerm('view');
		
		require_once(_base_.'/lib/lib.navbar.php');
		require_once(_base_.'/lib/lib.table.php');
		
		require_once($GLOBALS['where_lms'].'/lib/lib.reservation_perm.php');
		$roomperm = new ReservationRoomPermissions();
		$perm = 'view';
		
		$lang =& DoceboLanguage::createInstance('reservation');
		
		$mod_perm 	= checkPerm('mod', true);
		
		$id_course = $_SESSION['idCourse'];
		
		$order_by = importVar('order_by', false, 'c.name, e.title, e.date, e.deadLine');
		
		$out = $GLOBALS['page'];
		$out->setWorkingZone('content');
		
		$man_res = new Man_Reservation();
		
		$acl =& Docebo::user()->getAcl();
		$user_idst = getLogUserId();
		
		$events = array();
		
		$events = $man_res->viewMyEvents($id_course, getLogUserId(), $order_by);
		
		if($events)
		{
			$cont_h = array
			(
				(isset($_GET['order_by']) && $_GET['order_by'] == 'c.name, e.title' ? '<a href="index.php?modname=reservation&amp;op=reservation&amp;active_tab=my_events&amp;order_by=c.name, e.title DESC">'.$lang->def('_TITLE').'</a>' : '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=my_events&amp;order_by=c.name, e.title">'.$lang->def('_TITLE').'</a>'),
				(isset($_GET['order_by']) && $_GET['order_by'] == 'c.name, l.name' ? '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=my_events&amp;order_by=c.name, l.name DESC">'.$lang->def('_LOCATION').'</a>' : '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=my_events&amp;order_by=c.name, l.name">'.$lang->def('_LOCATION').'</a>'),
				(isset($_GET['order_by']) && $_GET['order_by'] == 'c.name, e.date' ? '<a href="index.php?modname=reservation&amp;op=reservation&amp;active_tab=my_events&amp;order_by=c.name, e.date DESC">'.$lang->def('_DATE').'</a>' : '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=my_events&amp;order_by=c.name, e.date">'.$lang->def('_DATE').'</a>'),
				(isset($_GET['order_by']) && $_GET['order_by'] == 'c.name, e.fromTime' ? '<a href="index.php?modname=reservation&amp;op=reservation&amp;active_tab=my_events&amp;order_by=c.name, e.fromTime DESC">'.$lang->def('_FROM_TIME').'</a>' : '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=my_events&amp;order_by=c.name, e.fromTime">'.$lang->def('_FROM_TIME').'</a>'),
				(isset($_GET['order_by']) && $_GET['order_by'] == 'c.name, e.toTime' ? '<a href="index.php?modname=reservation&amp;op=reservation&amp;active_tab=my_events&amp;order_by=c.name, e.toTime DESC">'.$lang->def('_TO_TIME').'</a>' : '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=my_events&amp;order_by=c.name, e.toTime">'.$lang->def('_TO_TIME').'</a>'),
				$lang->def('_NUMBER_SUBSCRIBED'),
				$lang->def('_AVAILABLE_PLACES'),
				(isset($_GET['order_by']) && $_GET['order_by'] == 'c.name, e.deadLine' ? '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=my_events&amp;order_by=c.name, e.deadLine DESC">'.$lang->def('_DEADLINE').'</a>' : '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=my_events&amp;order_by=c.name, e.deadLine">'.$lang->def('_DEADLINE').'</a>'),
			);
			$type_h = array('', '', '', '', '', '', '', '');
			
			$tb = new Table(100000);
			$tb->setColsStyle($type_h);
			$tb->addHead($cont_h);
			
			$temp_cat = '';
			
			foreach($events as $event)
			{
				$all_perm = $roomperm->getAllReservationPerm($event[EVENT_ID]);
				$can_view = ( isset($all_perm[$perm]) ? checkRoomPerm($all_perm[$perm], $user_idst) : TRUE );
				
				if ($can_view || $mod_perm)
				{
					if ($temp_cat != $event[EVENT_CATEGORY_NAME])
					{
						if ($temp_cat != '')
						{
							$out->add($tb->getTable());
							$out->add('<br/>');
						}
						
						$temp_cat = $event[EVENT_CATEGORY_NAME];
						
						$table_caption = $event[EVENT_CATEGORY_NAME];
						$max_subscription = $man_res->getMaxSubscriptionForCategory($event[EVENT_ID_CATEGORY]);
						if ($max_subscription)
							$table_caption .= ' : '.$lang->def('_MAX_SUBSCRIPTION_FOR_CATEGORY').' : '.$max_subscription; 
						
						$tb = new Table(100000, $table_caption);
						$tb->setColsStyle($type_h);
						$tb->addHead($cont_h);
					}
					
					$count = array();
					
					$count[] = $event[EVENT_TITLE];
					if ($event[EVENT_ID_LABORATORY])
						$count[] = '<a href="index.php?modname=reservation&amp;op=info_location&amp;active_tab=my_events&amp;id_location='.$event[EVENT_ID_LABORATORY].(isset($_GET['order_by']) ? '&amp;order_by='.$_GET['order_by'] : '').'">'.$event[EVENT_LABORATORY_NAME].'</a>';
					else
						$count[] = $event[EVENT_LABORATORY_NAME];
					$count[] = Format::date($event[EVENT_DATE], 'date');
					$count[] =  $event[EVENT_FROM_TIME];
					$count[] =  $event[EVENT_TO_TIME];
					$count[] = $event[EVENT_USER_SUBSCRIBED].'/'.$event[EVENT_MAX_USER];
					if($event[EVENT_AVAILABLE_PLACES] > 0)
						$count[] = $event[EVENT_AVAILABLE_PLACES];
					else
						$count[] = '0';
					$count[] = Format::date($event[EVENT_DEADLINE], 'date');
					
					$tb->addBody($count);
				}
			}
			$out->add($tb->getTable()
					.'</div>'
					);
		}
		else
		{
			$out->add($lang->def('_NO_RESERVATION_FOUND'), 'content');
		}
	}
	
	function pastEvents()
	{
		checkPerm('view');
		
		require_once(_base_.'/lib/lib.navbar.php');
		require_once(_base_.'/lib/lib.table.php');
		
		require_once($GLOBALS['where_lms'].'/lib/lib.reservation_perm.php');
		$roomperm = new ReservationRoomPermissions();
		$perm = 'view';
		
		$lang =& DoceboLanguage::createInstance('reservation');
		
		$mod_perm 	= checkPerm('mod', true);
		
		$id_course = $_SESSION['idCourse'];
		
		$order_by = importVar('order_by', false, 'c.name, e.title, e.date, e.deadLine');
		
		$out = $GLOBALS['page'];
		$out->setWorkingZone('content');
		
		$man_res = new Man_Reservation();
		
		$acl =& Docebo::user()->getAcl();
		$user_idst = getLogUserId();
		
		$events = array();
		
		$events = $man_res->viewPastEvents($id_course, getLogUserId(), $order_by);
		
		if($events)
		{
			$cont_h = array
			(
				(isset($_GET['order_by']) && $_GET['order_by'] == 'c.name, e.title' ? '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=past_events&amp;order_by=c.name, e.title DESC">'.$lang->def('_TITLE').'</a>' : '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=past_events&amp;order_by=c.name, e.title">'.$lang->def('_TITLE').'</a>'),
				(isset($_GET['order_by']) && $_GET['order_by'] == 'c.name, l.name' ? '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=pastevents&amp;order_by=c.name, l.name DESC">'.$lang->def('_LOCATION').'</a>' : '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=past_events&amp;order_by=c.name, l.name">'.$lang->def('_LOCATION').'</a>'),
				(isset($_GET['order_by']) && $_GET['order_by'] == 'c.name, e.date' ? '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=past_events&amp;order_by=c.name, e.date DESC">'.$lang->def('_DATE').'</a>' : '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=past_events&amp;order_by=c.name, e.date">'.$lang->def('_DATE').'</a>'),
				$lang->def('_NUMBER_SUBSCRIBED'),
				(isset($_GET['order_by']) && $_GET['order_by'] == 'c.name, e.deadLine' ? '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=past_events&amp;order_by=c.name, e.deadLine DESC">'.$lang->def('_DEADLINE').'</a>' : '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=past_events&amp;order_by=c.name, e.deadLine">'.$lang->def('_DEADLINE').'</a>'),
			);
			$type_h = array('', '', '', '', '');
			
			$tb = new Table(100000);
			$tb->setColsStyle($type_h);
			$tb->addHead($cont_h);
			
			$temp_cat = '';
			
			foreach($events as $event)
			{
				$all_perm = $roomperm->getAllReservationPerm($event[EVENT_ID]);
				$can_view = ( isset($all_perm[$perm]) ? checkRoomPerm($all_perm[$perm], $user_idst) : TRUE );
				
				if ($can_view || $mod_perm)
				{
					if ($temp_cat != $event[EVENT_CATEGORY_NAME])
					{
						if ($temp_cat != '')
						{
							$out->add($tb->getTable());
							$out->add('<br/>');
						}
						
						$temp_cat = $event[EVENT_CATEGORY_NAME];
						
						$table_caption = $event[EVENT_CATEGORY_NAME];
						$max_subscription = $man_res->getMaxSubscriptionForCategory($event[EVENT_ID_CATEGORY]);
						if ($max_subscription)
							$table_caption .= ' : '.$lang->def('_MAX_SUBSCRIPTION_FOR_CATEGORY').' : '.$max_subscription; 
						
						$tb = new Table(100000, $table_caption);
						$tb->setColsStyle($type_h);
						$tb->addHead($cont_h);
					}
					
					$count = array();
					
					$count[] = $event[EVENT_TITLE];
					if ($event[EVENT_ID_LABORATORY])
						$count[] = '<a href="index.php?modname=reservation&amp;op=info_location&amp;active_tab=past_events&amp;id_location='.$event[EVENT_ID_LABORATORY].(isset($_GET['order_by']) ? '&amp;order_by='.$_GET['order_by'] : '').'">'.$event[EVENT_LABORATORY_NAME].'</a>';
					else
						$count[] = $event[EVENT_LABORATORY_NAME];
					$count[] = Format::date($event[EVENT_DATE], 'date');
					$count[] = $event[EVENT_USER_SUBSCRIBED].'/'.$event[EVENT_MAX_USER];
					$count[] = Format::date($event[EVENT_DEADLINE], 'date');
					$tb->addBody($count);
				}
			}
			$out->add($tb->getTable()
					.'</div>'
					);
		}
		else
		{
			$out->add($lang->def('_NO_RESERVATION_FOUND'), 'content');
		}
	}
	
	function subscribedUser()
	{
		checkPerm('view');
		
		require_once(_base_.'/lib/lib.navbar.php');
		require_once(_base_.'/lib/lib.table.php');
		
		$lang =& DoceboLanguage::createInstance('reservation');
		
		$mod_perm 	= checkPerm('mod', true);
		
		$id_course = $_SESSION['idCourse'];
		
		$order_by = importVar('order_by', false, 'c.name, e.title, e.date, e.deadLine');
		
		$out = $GLOBALS['page'];
		$out->setWorkingZone('content');
		
		$man_res = new Man_Reservation();
		
		$acl =& Docebo::user()->getAcl();
		$user_idst = getLogUserId();
		
		$events = array();
		
		$events = $man_res->viewEventsForSubscribedTab($id_course, $order_by);
		
		$out->add
		(
			'<center>'
			.Form::openForm('form_event', 'index.php?modname=reservation&amp;op=reservation')
			.Form::getButton('add_event', 'add_event', $lang->def('_NEW_EVENT'))
			.Form::getButton('category_gestion', 'category_gestion', $lang->def('_CATEGORY_GESTION'))
			.Form::getButton('location_gestion', 'location_gestion', $lang->def('_LOCATION_GESTION'))
			.Form::closeForm()
			.'</center>'
		);
		
		$out->add('<br/>');
		
		if($events)
		{
			$cont_h = array
			(
				(isset($_GET['order_by']) && $_GET['order_by'] == 'c.name, e.title' ? '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=subscribed_user&amp;order_by=c.name, e.title DESC">'.$lang->def('_TITLE').'</a>' : '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=subscribed_user&amp;order_by=c.name, e.title">'.$lang->def('_TITLE').'</a>'),
				(isset($_GET['order_by']) && $_GET['order_by'] == 'c.name, l.name' ? '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=subscribed_user&amp;order_by=c.name, l.name DESC">'.$lang->def('_LOCATION').'</a>' : '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=subscribed_user&amp;order_by=c.name, l.name">'.$lang->def('_LOCATION').'</a>'),
				(isset($_GET['order_by']) && $_GET['order_by'] == 'c.name, e.date' ? '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=subscribed_user&amp;order_by=c.name, e.date DESC">'.$lang->def('_DATE').'</a>' : '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=subscribed_user&amp;order_by=c.name, e.date">'.$lang->def('_DATE').'</a>'),
				$lang->def('_NUMBER_SUBSCRIBED'),
				(isset($_GET['order_by']) && $_GET['order_by'] == 'c.name, e.deadLine' ? '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=subscribed_user&amp;order_by=c.name, e.deadLine DESC">'.$lang->def('_DEADLINE').'</a>' : '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=subscribed_user&amp;order_by=c.name, e.deadLine">'.$lang->def('_DEADLINE').'</a>'),
			);
			$type_h = array('', '', '', '', '');
			
			if ($mod_perm)
			{
				$type_h = array('', '', '', '', '', 'image', 'image', 'image', 'image', 'image', 'image');
				$cont_h = array
				(
					(isset($_GET['order_by']) && $_GET['order_by'] == 'c.name, e.title' ? '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=subscribed_user&amp;order_by=c.name, e.title DESC">'.$lang->def('_TITLE').'</a>' : '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=subscribed_user&amp;order_by=c.name, e.title">'.$lang->def('_TITLE').'</a>'),
					(isset($_GET['order_by']) && $_GET['order_by'] == 'c.name, l.name' ? '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=subscribed_user&amp;order_by=c.name, l.name DESC">'.$lang->def('_LOCATION').'</a>' : '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=subscribed_user&amp;order_by=c.name, l.name">'.$lang->def('_LOCATION').'</a>'),
					(isset($_GET['order_by']) && $_GET['order_by'] == 'c.name, e.date' ? '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=subscribed_user&amp;order_by=c.name, e.date DESC">'.$lang->def('_DATE').'</a>' : '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=subscribed_user&amp;order_by=c.name, e.date">'.$lang->def('_DATE').'</a>'),
					$lang->def('_NUMBER_SUBSCRIBED'),
					(isset($_GET['order_by']) && $_GET['order_by'] == 'c.name, e.deadLine' ? '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=subscribed_user&amp;order_by=c.name, e.deadLine DESC">'.$lang->def('_DEADLINE').'</a>' : '<a href="index.php?modname=reservation&op=reservation&amp;active_tab=subscribed_user&amp;order_by=c.name, e.deadLine">'.$lang->def('_DEADLINE').'</a>'),
					'<img src="'.getPathImage().'/standard/identity.png" title="'.$lang->def('_VIEW_USER_SUBSCRIBED').'" alt="'.$lang->def('_ENROL_COUNT').'" />',
					''.$lang->def('_ADD_USER').'',
					'<img src="'.getPathImage().'/standard/msg_unread.png" title="'.$lang->def('_ALT_SEND_MAIL').'" alt="'.$lang->def('_ALT_SEND_MAIL').'" />',
					'<img src="'.getPathImage().'/standard/edit.png" title="'.$lang->def('_MOD').'" alt="'.$lang->def('_MOD').'" />',
					'<img src="'.getPathImage().'/standard/delete.png" title="'.$lang->def('ALT_DEL').'" alt="'.$lang->def('_DEL').'" />',
					'<img src="'.getPathImage().'/standard/moduser.png" title="'.$lang->def('_SET_ROOM_VIEW_PERM').'" alt="'.$lang->def('_SET_ROOM_VIEW_PERM').'" />'
				);
			}
			
			$tb = new Table(100000);
			$tb->setColsStyle($type_h);
			$tb->addHead($cont_h);
			
			$temp_cat = '';
			
			foreach($events as $event)
			{
				if ($temp_cat != $event[EVENT_CATEGORY_NAME])
				{
					if ($temp_cat != '')
					{
						$out->add($tb->getTable());
						$out->add('<br/>');
					}
					
					$temp_cat = $event[EVENT_CATEGORY_NAME];
					
					$table_caption = $event[EVENT_CATEGORY_NAME];
					$max_subscription = $man_res->getMaxSubscriptionForCategory($event[EVENT_ID_CATEGORY]);
					if ($max_subscription)
						$table_caption .= ' : '.$lang->def('_MAX_SUBSCRIPTION_FOR_CATEGORY').' : '.$max_subscription; 
					
					$tb = new Table(100000, $table_caption);
					$tb->setColsStyle($type_h);
					$tb->addHead($cont_h);
				}
				
				$count = array();
				
				$count[] = $event[EVENT_TITLE];
				if ($event[EVENT_ID_LABORATORY])
						$count[] = '<a href="index.php?modname=reservation&amp;op=info_location&amp;active_tab=subscribed_user&amp;id_location='.$event[EVENT_ID_LABORATORY].(isset($_GET['order_by']) ? '&amp;order_by='.$_GET['order_by'] : '').'">'.$event[EVENT_LABORATORY_NAME].'</a>';
					else
						$count[] = $event[EVENT_LABORATORY_NAME];
				$count[] = Format::date($event[EVENT_DATE], 'date');
				$count[] = $event[EVENT_USER_SUBSCRIBED].'/'.$event[EVENT_MAX_USER];
				$count[] = Format::date($event[EVENT_DEADLINE], 'date');
				if ($mod_perm)
				{
					$count[] = '<a href="index.php?modname=reservation&amp;op=view_user_event&amp;id_event='.$event[EVENT_ID].'"><img src="'.getPathImage().'/standard/identity.png" title="'.$lang->def('_VIEW_USER_SUBSCRIBED').'" alt="'.$lang->def('_ENROL_COUNT').'" /></a>';
					$count[] = '<a href="index.php?modname=reservation&amp;op=add_registration&amp;id_event='.$event[EVENT_ID].'&amp;id_course='.$event[EVENT_ID_COURSE].'">'.$lang->def('_ADD_USER').'</a>';
					$count[] = '<a href="index.php?modname=reservation&amp;op=send_mail&amp;id_event='.$event[EVENT_ID].'&amp;id_course='.$event[EVENT_ID_COURSE].'"><img src="'.getPathImage().'/standard/msg_unread.png" title="'.$lang->def('_ALT_SEND_MAIL').'" alt="'.$lang->def('_ALT_SEND_MAIL').'" /></a>';
					$count[] = '<a href="index.php?modname=reservation&amp;op=mod_event&amp;id_event='.$event[EVENT_ID].'"><img src="'.getPathImage().'/standard/edit.png" title="'.$lang->def('_MOD').'" alt="'.$lang->def('_MOD').'" /></a>';
					$count[] = '<a href="index.php?modname=reservation&amp;op=del_event&amp;id_event='.$event[EVENT_ID].'"><img src="'.getPathImage().'/standard/delete.png" title="'.$lang->def('_DEL').'" alt="'.$lang->def('_DEL').'" /></a>';
					$count[] = '<a href="index.php?modname=reservation&amp;op=set_room_view_perm&amp;id_event='.$event[EVENT_ID].'"><img src="'.getPathImage().'standard/moduser.png" alt="'.$lang->def('_SET_ROOM_VIEW_PERM').'" title="'.$lang->def('_SET_ROOM_VIEW_PERM').'" /></a>';
				}
				$tb->addBody($count);
			}
			$out->add($tb->getTable()
			.'</div>'
			);
		}
		else
		{
			$out->add($lang->def('_NO_RESERVATION_FOUND'), 'content');
		}
	}
	
function viewUserEvent()
	{
		checkPerm('view');
		
		require_once(_base_.'/lib/lib.navbar.php');
		require_once(_base_.'/lib/lib.table.php');
		require_once(_base_.'/lib/lib.form.php');
		
		$lang =& DoceboLanguage::createInstance('reservation');
		
		$id_event = importVar('id_event', true, 0);
		
		$mod_perm 	= checkPerm('mod', true);
		
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
		
		$out->add('<br/>');
		
		$out->add('');
		
		$out->add('<br/>');
		
		if ($user_info)
		{
			$tb = new Table(10, $lang->def('_RESERVATION_CAPTION'), $lang->def('_RESERVATION_SUMMARY'));
			$tb->initNavBar('ini', 'button');
			
			$ini = $tb->getSelectedElement();
			
			if ($GLOBALS['cfg']['reservation_exportcell_id']) {
				$cont_h = array
				(
					$lang->def('_USERNAME'),
					$lang->def('_FIRSTNAME'),
					$lang->def('_LASTNAME'),
					$lang->def('_EMAIL'),
					$lang->def('_CELLULARE'),
					$lang->def('_SEND_MAIL', 'report'),
				);
				$type_h = array('', '', '', '', '','','');

			} else {
				$cont_h = array
				(
					$lang->def('_USERNAME'),
					$lang->def('_FIRSTNAME'),
					$lang->def('_LASTNAME'),
					$lang->def('_EMAIL'),
					$lang->def('_SEND_MAIL', 'report'),
				);

				$type_h = array('', '', '', '', '','','');
			}

			
			$tb->setColsStyle($type_h);
			$tb->addHead($cont_h);
			$counter = 0;
			foreach ($user_info as $info_user)
			{
				$count = array();
				if ($counter >= $ini && $counter < ($ini + 10))
				{
					if ($GLOBALS['cfg']['reservation_exportcell_id']) {
						// id_common = 13 => cellulare
						$sql = "SELECT user_entry FROM core_field_userentry WHERE id_user = '".$info_user[0]."' AND id_common = 13";
						$res = sql_query($sql);
	                    list($cell) = sql_fetch_row($res);
	                }
					$count[] = $acl_man->relativeId($info_user[ACL_INFO_USERID]);
					$count[] = $info_user[ACL_INFO_FIRSTNAME];
					$count[] = $info_user[ACL_INFO_LASTNAME];
					$count[] = $info_user[ACL_INFO_EMAIL];
					if ($GLOBALS['cfg']['reservation_exportcell_id']) {
						$count[] = $cell;
					}
					$count[] = '<a href="index.php?modname=reservation&op=send_user_event&id_user='.$info_user[0].'&id_event='.$id_event.'">'.$lang->def('_SEND_MAIL', 'report').'</a>';
					$tb->addBody($count);
				}
				$counter++;
			}
			$out->add($tb->getTable()
			.Form::openForm('tab_form', 'index.php?modname=reservation&op=view_user_event&id_event='.$id_event)
			.$tb->getNavBar($ini, count($user_info))
			.Form::closeForm()
			);
			$out->add('<br/>');

            $event = new \appLms\Events\Lms\UserListEvent($out,$lang);

            $event->setIdEvent($id_event);

            $event->setDefaultExportEndpoint('index.php?modname=reservation&amp;op=excel&id_event=' . $id_event);

            \appCore\Events\DispatcherManager::dispatch(\appLms\Events\Lms\UserListEvent::EVENT_NAME, $event);

            $out->add($event->getExportLink(),'content');
		}
		else
		{
			$out->add($lang->def('_NO_USERS_FOUND'), 'content');
		}
		
		$out->add(getBackUi('index.php?modname=reservation&amp;op=reservation&amp;active_tab=subscribed_user', $lang->def('_BACK')), 'content');
		$out->add('</div>', 'content');
	}
	
	function sendUserEvent()
	{
		checkPerm('view');
	
		$lang =& DoceboLanguage::createInstance('reservation');
			
		$mod_perm 	= checkPerm('mod', true);
	
		$id_course = $_SESSION['idCourse'];
		$id_event = importVar('id_event', true, 0);
		$id_user = importVar('id_user', true, 0);
	
		$out = $GLOBALS['page'];
		$out->setWorkingZone('content');
	
		$man_res = new Man_Reservation();
	
		$acl_man =& Docebo::user()->getAclManager();
	
		if (isset($_POST['send_mail']))
		{
			$recipients = $man_res->getEventUserMail($id_event);
			$query = 'SELECT email FROM core_user WHERE idst = '.$id_user;
			$result = sql_query($query);
			$re = array();
			while(list($subscribed) = sql_fetch_row($result))
			{
				$re[] = $subscribed;
			}
	
	
			$subject = importVar('mail_object', false, '[Nessun Oggetto]');
			$body = importVar('mail_body', false, '');
	
			$info_user = $acl_man->getUser(getLogUserId());
			$sender = $info_user[ACL_INFO_EMAIL];
	
			//sendMail($recipients, $subject, $body, $sender);
			require_once(_base_.'/lib/lib.mailer.php');
			$mailer = DoceboMailer::getInstance();
			$mailer->SendMail($sender, $re, $subject, $body, array(MAIL_REPLYTO => $sender, MAIL_SENDER_ACLNAME => false));
				
			//Util::jump_to('index.php?modname=reservation&op=reservation&active_tab=events');
			Util::jump_to('index.php?modname=reservation&op=view_user_event&id_event='.$id_event);
	
		}
		else
		{
			require_once(_base_.'/lib/lib.form.php');
				
			$out->add(getTitleArea($lang->def('_RESERVATION_MAIL_SEND')).'<div class="std_block">', 'content');
				
			$out->add
			(
					Form::openForm('form_event', 'index.php?modname=reservation&amp;op=send_user_event')
					.Form::openElementSpace()
					.Form::getTextfield($lang->def('_SUBJECT'), 'mail_object', 'mail_object', 255)
					.Form::getTextarea($lang->def('_MAIL_BODY', 'report'), 'mail_body', 'mail_body')
					.Form::getHidden('id_event', 'id_event', $id_event)
					.Form::getHidden('id_user', 'id_user', $id_user)
					.Form::closeElementSpace()
					.Form::openButtonSpace()
					.Form::getButton('send_mail', 'send_mail', $lang->def('_SEND_MAIL', 'report'))
					.Form::getButton('undo_mail', 'undo_mail', $lang->def('_UNDO'))
					.Form::closeButtonSpace()
					.Form::closeForm()
					.'</div>'
			);
		}
	}
	
	function getExcelFile()
	{
		require_once($GLOBALS['where_framework'].'/lib/lib.field.php');
		
		$lang =& DoceboLanguage::createInstance('reservation');
		
		$id_event = importVar('id_event', true, 0);
		
		$man_res = new Man_Reservation();
		$field_man = new FieldList();
		
		$acl_man =& Docebo::user()->getAclManager();
		
		$user_subscribed = array();
		
		$user_sunscribed = $man_res->getSubscribedUserIdst($id_event);
		
		$user_info = array();
		$user_info =& $acl_man->getUsers($user_sunscribed);
		
		$event_info = array();
		$event_info = $man_res->getEventInfo($id_event);
		
		$filename = $event_info[EVENT_TITLE];
		$filename .= ' ';
		$filename .= date('Y-m-d H-i-s');
		$filename = str_replace(' ', '_', $filename);
		
		$excel_header = '"'.$lang->def('_USERNAME').'"'."\t";
		$excel_header .= '"'.$lang->def('_FIRSTNAME').'"'."\t";
		$excel_header .= '"'.$lang->def('_LASTNAME').'"'."\t";
		$excel_header .= '"'.$lang->def('_EMAIL').'"'."\n";
		
		/*$extra_filed_header = $field_man->getAllFields();
		
		foreach ($extra_filed_header as $header_extra_field)
		{
			$field_translation = $header_extra_field['2'];
			$field_type = $header_extra_field['1'];
			
			if ($field_type != 'upload')
				$excel_header .= '"'.$field_translation.'"'."\n";
		}*/
		
		if ($user_info)
		{
			$excel_data = '';
			
			foreach ($user_info as $info_user)
			{
				/*$extra_field = $field_man->getAllFields();
				
				$array_field = array();
				
				foreach ($extra_field as $field_extra)
				{
					$field_translation = $field_extra['2'];
					$field_type = $field_extra['1'];
					$field_id = $field_extra['0'];
					
					$query = "SELECT user_entry"
							." FROM core_field_userentry"
							." WHERE id_common = '".$field_id."'"
							." AND id_user = '".$info_user[ACL_INFO_IDST]."'";
					
					list($res) = sql_fetch_row(sql_query($query));
					
					switch ($field_type)
					{
						case 'country':
							$query_value = "SELECT name_country"
										." FROM core_country"
										." WHERE id_country = '".$res."'";
							
							list($value) = sql_fetch_row(sql_query($query_value));
							
							$array_field[] = $value;
						break;
						
						case 'dropdown':
							$query_value = "SELECT translation"
										." FROM core_field_son"
										." WHERE idField = '".$field_id."'"
										." AND idSon = '".$res."'"
										." AND lang_code = '".getLanguage()."'";
							
							list($value) = sql_fetch_row(sql_query($query_value));
							
							$array_field[] = $value;
						break;
						
						case 'yesno':
							if ($res)
								$value = $lang->def('_YES');
							else
								$value = $lang->def('_NO');
							
							$array_field[] = $value;
						break;
						
						case 'upload':
						break;
						
						case 'date':
							$array_field[] = Format::date($res, 'datetime');
						break;
						
						default:
							$array_field[] = $res;
						break;
					}
				}*/
				
				$excel_data .= '"'.$acl_man->relativeId($info_user[ACL_INFO_USERID]).'"'."\t";
				$excel_data .= '"'.$info_user[ACL_INFO_FIRSTNAME].'"'."\t";
				$excel_data .= '"'.$info_user[ACL_INFO_LASTNAME].'"'."\t";
				$excel_data .= '"'.$info_user[ACL_INFO_EMAIL].'"'."\n";
				
				/*foreach ($array_field as $filed_array)
					$excel_data .= '"'.$filed_array.'"'."\n";*/
			}
		}
		
		$excel_header = str_replace("\r","",$excel_header);
		$excel_data = str_replace("\r","",$excel_data);
		
		$excel = $excel_header.$excel_data;
		
		header("Content-type: application/x-msdownload");
		header("Content-Disposition: attachment; filename=".$filename.".xls");
		header("Pragma: no-cache");
		header("Expires: 0");
		
		ob_end_clean();
		
		echo $excel;
		
		exit(0);
	}
	
	function addSubscription()
	{
		checkPerm('view');
		
		require_once(_base_.'/lib/lib.form.php');
		
		$action = importVar('action', true, 0);
		$id_event = importVar('id_event', true, 0);
		
		$lang =& DoceboLanguage::createInstance('reservation');
		
		$out = $GLOBALS['page'];
		$out->setWorkingZone('content');
		
		$man_res = new Man_Reservation();
		
		if (isset($_POST['save_profile']))
		{
			$confirm = importVar('confirm', true, 0);
			
			if($confirm)
			{
				require_once($GLOBALS['where_lms'].'/lib/lib.lms_user_profile.php');
				
				$out->add(getTitleArea('_RESERVATION_PROFILE_MODIFY').'<div class="std_block">', 'content');
				
				$profile = new LmsUserProfile(getLogUserId(), true);
				$profile->init('subscription', 'lms', 'modname=reservation&op=add_subscription&id_event='.$id_event.'&confirm=1&from=2&id_user='.getLogUserId(), 'ap');
				$out->add(
					$profile->getTitleArea()
					.$profile->getHead()
					.$profile->performAction()
					.$profile->getFooter()
					.'</div>', 'content');
			}
		}
		elseif (isset($_GET['confirm']))
		{
			$confirm = importVar('confirm', true, 0);
			
			if($confirm)
			{
				require_once($GLOBALS['where_lms'].'/lib/lib.lms_user_profile.php');
				
				$out->add
				(
					getTitleArea($lang->def('_RESERVATION_PROFILE_MODIFY')).'<div class="std_block">', 'content');
				
				$out->add
				($lang->def('_CONFIRM_DATA').'<br/>');
				
				$profile = new LmsUserProfile(getLogUserId(), true);
				$profile->init('subscription', 'lms', 'modname=reservation&op=add_subscription&id_event='.$id_event.'&confirm=1&from=2&id_user='.getLogUserId(), 'ap');
				//$profile->enableEditMode();
				
				$out->add(
					$profile->getTitleArea()
					.$profile->getHead()
					.$profile->performAction()
					.$profile->getFooter()
					.'</div>', 'content');
			}
		}
		else
		{
			$event_info = $man_res->getEventInfo($id_event);
			$out->add
			(
				getTitleArea($lang->def('_ADD_SUBSCRIPTION_TITLE'), '', $lang->def('_EVENT'))
				.'<div class="std_block">'
				.getModifyUi(	$lang->def('_AREYOUSURE_ADD_SUBSCRIPTION'),
								$event_info[EVENT_TITLE],
								true,
								'index.php?modname=reservation&amp;op=add_subscription&amp;id_event='.$id_event.'&amp;confirm=1&amp;ap=mod_profile&amp;from=2',
								'index.php?modname=reservation&amp;op=reservation'
							)
				.'</div>', 'content'
			);
		}
	}
	
	function delSubscription()
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
			
			if($confirm)
			{
				$result = $man_res->delSubscription(getLogUserId(), $id_event);
				// invia mail agli amministratori

				$query = 'SELECT u.email FROM core_user as u, learning_courseuser as cu, learning_reservation_events as re WHERE re.idEvent = "'.$id_event.'" AND cu. idCOurse = re.idCourse AND cu.idUser = u.idst AND cu.level > 3';
				$result = sql_query($query);				
				$re = array();
				while(list($subscribed) = sql_fetch_row($result))
				{
					$re[] = $subscribed;
				}
				$lang =& DoceboLanguage::createInstance('reservation');

				$subject = $lang->def('_SUBJECT_DELSUBSCRIPTION');
				$body = $lang->def('_BODY_DELSUBSCRIPTION');
										
				$acl_man =& Docebo::user()->getAclManager();

				$info_user = $acl_man->getUser(getLogUserId());
				$sender = $info_user[ACL_INFO_EMAIL];

				require_once(_base_.'/lib/lib.mailer.php');
				$mailer = DoceboMailer::getInstance();
				$mailer->SendMail($sender, $re, $subject, $body, array(MAIL_REPLYTO => $sender, MAIL_SENDER_ACLNAME => false));
		
				// end invio mail
		
			}

			Util::jump_to('index.php?modname=reservation&op=reservation');
		}
		
		$ev_info = $man_res->getEventInfo($id_event);
		$GLOBALS['page']->add
		(
			getTitleArea($lang->def('_DEL_SUBSCRIPTION_TITLE'), '', $lang->def('_EVENT'))
			.'<div class="std_block">'
			.getDeleteUi(	$lang->def('_AREYOUSURE_DEL_SUBSCRIPTION'),
							$lang->def('_TITLE').': '.$ev_info[EVENT_TITLE],
							true,
							'index.php?modname=reservation&amp;op=del_subscription&amp;id_event='.$id_event.'&amp;confirm=1',
							'index.php?modname=reservation&amp;op=reservation'
						)
			.'</div>', 'content'
		);
	}
	
	
	function switchSubscription()
	{
		checkPerm('view');
		
		require_once(_base_.'/lib/lib.form.php');
		
		$lang =& DoceboLanguage::createInstance('reservation');
		
		$out = $GLOBALS['page'];
		$out->setWorkingZone('content');
		
		$man_res = new Man_Reservation();
		
		$id_course = importVar('id_course', true, 0);
		$id_event = importVar('id_event', true, 0);
		$id_category = importVar('id_category', true, 0);
		
		if (isset($_GET['confirm']))
		{
			$id_event_del = importVar('id_event_del', true, 0);
			$confirm = importVar('confirm', true, 0);
			
			if($confirm && $id_event_del)
			{
				$result = $man_res->switchSubscription(getLogUserId(), $id_event_del, $id_event);
				Util::jump_to('index.php?modname=reservation&op=reservation');
			}
			Util::jump_to('index.php?modname=reservation&op=reservation');
		}
		
		$out->add
		(
			getTitleArea($lang->def('_SWITCH_REGISTRATION_TITLE'), '', $lang->def('_EVENT'))
			.'<div class="std_block">'
		);
		
		$error = importVar('error', false, '');
		
		$out->add
		(
			Form::openForm('form_switch_subscription', 'index.php?modname=reservation&amp;op=switch_subscription&amp;confirm=1')
			.Form::openElementSpace()
			.Form::getDropdown($lang->def('_DEL_EVENT_REGISTRATION'), 'id_event_del', 'id_event_del', $man_res->getEventDropDown($id_course, $id_category, getLogUserId()))
			.Form::getHidden('id_category', 'id_category', $id_category)
			.Form::getHidden('id_course', 'id_course', $id_course)
			.Form::getHidden('id_event', 'id_event', $id_event)
			.Form::closeElementSpace()
			.Form::openButtonSpace()
			.Form::getButton('send_event', 'send_event', $lang->def('_SWITCH_REGISTRATION'))
			.Form::getButton('undo_switch', 'undo_switch', $lang->def('_UNDO'))
			.Form::closeButtonSpace()
			.Form::closeForm()
		);
		
		$out->add('</div>', 'content');
	}
	
	function addRegistration()
	{
		checkPerm('mod');
		
		require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
		require_once(_base_.'/lib/lib.form.php');
		require_once(_base_.'/lib/lib.userselector.php');
	
		$id_course = $_SESSION['idCourse'];
		$id_event = importVar('id_event', true, 0);
		
		$man_res = new Man_Reservation();
		$aclManager = new DoceboACLManager();
		
		$subscribed = array();
		$subscribed = $man_res->getSubscribedUserIdst($id_event);
		
		$subscribed_empty = array();
		
		$lang =& DoceboLanguage::CreateInstance('reservation');
		$out =& $GLOBALS['page'];
		
		$user_select = new UserSelector();
		
		$user_select->show_user_selector = TRUE;
		$user_select->show_group_selector = FALSE;
		$user_select->show_orgchart_selector = FALSE;
		$user_select->show_orgchart_simple_selector = FALSE;
		$user_select->show_fncrole_selector = FALSE;
		$user_select->learning_filter = 'course';
		
		$user_select->nFields = 3;
		
		if ($subscribed)
			$user_select->resetSelection($subscribed);
		else
			$user_select->resetSelection($subscribed_empty);
		
		$acl_man =& Docebo::user()->getAclManager();
		
		$arr_idstGroup = $aclManager->getGroupsIdstFromBasePath('/lms/course/'.(int)$_SESSION['idCourse'].'/subscribed/');
		$me = array(getLogUserId());
		$user_select->setUserFilter('group',$arr_idstGroup);
		$user_select->setGroupFilter('path', '/lms/course/'.$_SESSION['idCourse'].'/group');
		
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
			Util::jump_to('index.php?modname=reservation&op=reservation&active_tab=subscribed_user&error=insert');
		Util::jump_to('index.php?modname=reservation&op=reservation&active_tab=subscribed_user');
	}
	
	function addEvent()
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
			$id_course = $_SESSION['idCourse'];
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
			
			if ($date)
			
			$from_time = $from_time_h.':'.$from_time_m.':00';
			
			$to_time = $to_time_h.':'.$to_time_m.':00';
			
			if ($date < date('Y-m-d') || $date < $deadline || $deadline < date('Y-m-d'))
				Util::jump_to('index.php?modname=reservation&op=add_event&amp;error=date&id_laboratory='.$id_laboratory.'&id_category='.$id_category.'&title='.$title.'&description='.$description.'&date='.$_POST['date'].'&max_user='.$max_user.'&deadline='.$_POST['deadline']);
			
			if ($from_time >= $to_time)
				Util::jump_to('index.php?modname=reservation&op=add_event&amp;error=time&id_laboratory='.$id_laboratory.'&id_category='.$id_category.'&title='.$title.'&description='.$description.'&date='.$_POST['date'].'&max_user='.$max_user.'&deadline='.$_POST['deadline']);
			
			if ($confirm)
				$result = $man_res->addEvents($id_course, $id_laboratory, $id_category, $title, $description, $date, $max_user, $deadline, $from_time, $to_time);
			if ($result)
				Util::jump_to('index.php?modname=reservation&op=reservation&active_tab=subscribed_user');
			Util::jump_to('index.php?modname=reservation&op=add_event&amp;error=laboratory&id_laboratory='.$id_laboratory.'&id_category='.$id_category.'&title='.$title.'&description='.$description.'&date='.$_POST['date'].'&max_user='.$max_user.'&deadline='.$_POST['deadline']);
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
		
		$out->add
		(
			Form::openForm('form_event', 'index.php?modname=reservation&amp;op=add_event&amp;confirm=1')
			.Form::openElementSpace()
			.Form::getTextfield($lang->def('_TITLE'), 'title', 'title', 255, (isset($_GET['title']) ? $_GET['title'] : ''))
			.Form::getTextarea($lang->def('_DESCRIPTION'), 'description', 'description', (isset($_GET['description']) ? $_GET['description'] : ''))
			.Form::getDropdown($lang->def('_LOCATION'), 'id_laboratory', 'id_laboratory', $man_res->getLaboratories(), (isset($_GET['id_laboratory']) ? $_GET['id_laboratory'] : '0'))
			.Form::getDropdown($lang->def('_CATEGORY'), 'id_category', 'id_category', $man_res->getCategory(), (isset($_GET['id_category']) ? $_GET['id_category'] : '0'))
			.Form::getDateField($lang->def('_DATE'), 'date', 'date', (isset($_GET['date']) ? $_GET['date'] : ''))
			.Form::getDateField($lang->def('_DEADLINE'), 'deadline', 'deadline', (isset($_GET['deadline']) ? $_GET['deadline'] : ''))
			.Form::getTextfield($lang->def('_MAX_USER'), 'max_user', 'max_user', 255, (isset($_GET['max_user']) ? $_GET['max_user'] : ''))
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
			
			if ($date < date('Y-m-d') || $date < $deadline || $deadline < date('Y-m-d'))
				Util::jump_to('index.php?modname=reservation&op=mod_event&amp;id_event='.$id_event.'&amp;error=date');
			
			if ($from_time >= $to_time)
				Util::jump_to('index.php?modname=reservation&op=mod_event&amp;id_event='.$id_event.'&amp;error=time');
			
			if ($confirm)
				$result = $man_res->modEvent($id_event, $id_course, $id_laboratory, $id_category, $title, $description, $date, $max_user, $deadline, $from_time, $to_time);
			if ($result)
				Util::jump_to('index.php?modname=reservation&op=reservation&active_tab=subscribed_user');
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
		
		if (isset($_GET['confirm']))
		{
			$confirm = importVar('confirm', true, 0);
			
			if($confirm)
			{
				$result = $man_res->delEvent($id_event);
			}
			if ($result)
				Util::jump_to('index.php?modname=reservation&op=reservation&active_tab=subscribed_user');
			else
				Util::jump_to('index.php?modname=reservation&op=reservation&amp;error=del_event');
		}
		
		$GLOBALS['page']->add
		(
			getTitleArea($lang->def('_DEL'), '', $lang->def('_EVENT'))
			.'<div class="std_block">'
			.getDeleteUi(	$lang->def('_AREYOUSURE'),
							$lang->def('_EVENT'),
							true,
							'index.php?modname=reservation&amp;op=del_event&amp;id_event='.$id_event.'&amp;confirm=1',
							'index.php?modname=reservation&amp;op=reservation'
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
		
		$id_course = $id_course = $_SESSION['idCourse'];
		
		$out = $GLOBALS['page'];
		$out->setWorkingZone('content');
		
		$man_res = new Man_Reservation();
		
		$category = array();
		$category = $man_res->viewCategory($id_course);
		
		$out->add(getTitleArea($lang->def('_CATEGORY')).'<div class="std_block">', 'content');
		
		$error = importVar('error', false, '');
		
		if ($error !== '')
		{
			switch ($error)
			{
				case 'del_category':
					$out->add(getErrorUi($lang->def('_DEL_ERROR')));
				break;
			}
		}
		
		$tb = new Table(10, $lang->def('_RESERVATION_CAPTION'), $lang->def('_RESERVATION_CAPTION'));
			$tb->initNavBar('ini', 'button');
			
			$ini = $tb->getSelectedElement();
			
			$cont_h = array
			(
				$lang->def('_NAME'),
			);
			$type_h = array('', '');
			if ($mod_perm)
			{
				$type_h = array('', '', 'image', 'image');
				$cont_h = array
				(
					$lang->def('_NAME'),
					$lang->def('_CATEGORY_MAX_SUBSCRIPTION'),
					'<img src="'.getPathImage().'/standard/edit.png" title="'.$lang->def('_MOD').'" alt="'.$lang->def('_MOD').'" />',
					'<img src="'.getPathImage().'/standard/delete.png" title="'.$lang->def('ALT_DEL').'" alt="'.$lang->def('_DEL').'" />'
				);
			}
			$tb->setColsStyle($type_h);
			$tb->addHead($cont_h);
			
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
					if ($categ[CATEGORY_ID_COURSE])
					{
						$count[] = '<a href="index.php?modname=reservation&amp;op=mod_category&amp;id_category='.$categ[CATEGORY_ID].'"><img src="'.getPathImage().'/standard/edit.png" title="'.$lang->def('_MOD').'" alt="'.$lang->def('_MOD').'" /></a>';
						$count[] = '<a href="index.php?modname=reservation&amp;op=del_category&amp;id_category='.$categ[CATEGORY_ID].'"><img src="'.getPathImage().'/standard/delete.png" title="'.$lang->def('_DEL').'" alt="'.$lang->def('_DEL').'" /></a>';
					}
					else
					{
						$count[] = '<img src="'.getPathImage().'/standard/edit.png" title="'.$lang->def('_MOD').'" alt="'.$lang->def('_MOD').'" />';
						$count[] = '<img src="'.getPathImage().'/standard/delete.png" title="'.$lang->def('ALT_DEL').'" alt="'.$lang->def('_DEL').'" />';
					}
				}
				$tb->addBody($count);
			}
			if($mod_perm) {
				$tb->addActionAdd('<a class="ico-wt-sprite subs_add" href="index.php?modname=reservation&amp;op=add_category&amp;id_course='.$id_course.'" title="'.$lang->def('_ADD').'">'
						.'<span>'.$lang->def('_ADD').'</span></a>');
			}
			$out->add($tb->getTable()
				.$tb->getNavBar($ini, count($category))
				.getBackUi('index.php?modname=reservation&amp;op=reservation&amp;active_tab=subscribed_user', $lang->def('_BACK'))
				.'</div>'
			);
	}
	
	function addCategoy()
	{
		checkPerm('view');
		
		require_once(_base_.'/lib/lib.form.php');
		
		$lang =& DoceboLanguage::createInstance('reservation');
		
		$id_course = importVar('id_course', true, 0);
		
		$out = $GLOBALS['page'];
		$out->setWorkingZone('content');
		
		$man_res = new Man_Reservation();
		
		if (isset($_GET['confirm']))
		{
			$confirm = importVar('confirm', true, 0);
			
			$name = importVar('name', false, '');
			$max_subscription = importVar('max_subscription', true, 0);
			
			if ($name == '')
				Util::jump_to('index.php?modname=reservation&op=add_category&error=name&id_course='.$id_course.'&name='.$name.'&max_subscription='.$max_subscription);
			
			if ($confirm)
				$result = $man_res->addCategory($name, $max_subscription, $id_course);
			if ($result)
				Util::jump_to('index.php?modname=reservation&op=view_category');
			Util::jump_to('index.php?modname=reservation&op=add_category&error=category&id_course='.$id_course.'&name='.$name.'&max_subscription='.$max_subscription);
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
			.Form::getTextfield($lang->def('_NAME'), 'name', 'name', 255, (isset($_GET['name']) ? $_GET['name'] : ''))
			.Form::getTextfield($lang->def('_CATEGORY_MAX_SUBSCRIPTION'), 'max_subscription', 'max_subscription', 255, (isset($_GET['max_subscription']) ? $_GET['max_subscription'] : '0'))
			.Form::getHidden('id_course', 'id_course', $id_course)
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
					$out->add(getErrorUi($lang->def('_WRONG_NAME_CATEGORY')));
				break;
				
				case 'category':
					$out->add(getErrorUi($lang->def('_ERROR_OPERATION')));
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
			.Form::getButton('mod_category', 'mod_category', $lang->def('_MOD'))
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
		
		if (isset($_GET['confirm']))
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
	
	function classroom() {
		checkPerm('view');
	
		require_once(_base_.'/lib/lib.form.php');
		require_once(_base_.'/lib/lib.table.php');
	
	
		$mod_perm	= true;
		// create a language istance for module classroom
		$lang 		=& DoceboLanguage::createInstance('classroom', 'lms');
		$out 		=& $GLOBALS['page'];
		$out->setWorkingZone('content');
		
		$tb	= new Table(Get::sett('visuItem'), $lang->def('_CLASSROOM_CAPTION'), $lang->def('_CLASSROOM_SUMMARY'));
		$tb->initNavBar('ini', 'link');
		$tb->setLink('index.php?modname=reservation&amp;op=classroom&amp;id_course='.$_SESSION['idCourse']);
		$ini=$tb->getSelectedElement();
	
		//search query of classrooms
		$query_classroom = "
		SELECT idClassroom, name, description
		FROM ".$GLOBALS['prefix_lms']."_classroom
		ORDER BY name
		LIMIT $ini,".Get::sett('visuItem');
	
		$query_classroom_tot = "
		SELECT COUNT(*)
		FROM ".$GLOBALS['prefix_lms']."_classroom";
	
		$re_classroom = sql_query($query_classroom);
		list($tot_classroom) = sql_fetch_row(sql_query($query_classroom_tot));
	
	
		$type_h = array('', 'news_short_td', "image", "image");
		$cont_h	= array(
		$lang->def('_NAME'),
		$lang->def('_DESCRIPTION')
		);
		if($mod_perm) {
			$cont_h[] = '<img src="'.getPathImage().'standard/edit.png" title="'.$lang->def('_TITLE_MOD_CLASSROOM').'" '
							.'alt="'.$lang->def('_MOD').'" />';
			$type_h[] = 'image';
			$cont_h[] = '<img src="'.getPathImage().'standard/delete.png" title="'.$lang->def('_DEL').'" '
							.'alt="'.$lang->def('_DEL').'"" />';
			$type_h[] = 'image';
	
		}
	
		$tb->setColsStyle($type_h);
		$tb->addHead($cont_h);
		while(list($idClassroom, $name, $descr) = sql_fetch_row($re_classroom)) {
	
			$cont = array(
				$name,
				$descr
			);
			if($mod_perm) {
				$cont[] = '<a href="index.php?modname=reservation&amp;op=modclassroom&amp;idClassroom='.$idClassroom.'" '
							.'title="'.$lang->def('_TITLE_MOD_CLASSROOM').' : '.$name.'">'
							.'<img src="'.getPathImage().'standard/edit.png" alt="'.$lang->def('_MOD').' : '.$name.'" /></a>';
	
				$cont[] = '<a href="index.php?modname=reservation&amp;op=delclassroom&amp;idClassroom='.$idClassroom.'" '
							.'title="'.$lang->def('_DEL').' : '.$name.'">'
							.'<img src="'.getPathImage().'standard/delete.png" alt="'.$lang->def('_DEL').' : '.$name.'" /></a>';
			}
			$tb->addBody($cont);
		}
		if($mod_perm) {
			$tb->addActionAdd(
				'<a href="index.php?modname=reservation&amp;op=addclassroom" title="'.$lang->def('_TITLE_NEW_CLASSROOM').'">'
					.'<img src="'.getPathImage().'standard/add.png" alt="'.$lang->def('_ADD').'" />'
					.$lang->def('_NEW_CLASSROOM').'</a>'
			);
		}
	
		$out->add(getTitleArea($lang->def('_CLASSROOM'), 'classroom', $lang->def('_ALT_CLASSROOM'))
				.'<div class="std_block">'	);
		if(isset($_GET['result'])) {
			switch($_GET['result']) {
				case "ok" 		: $out->add(getResultUi($lang->def('_OPERATION_SUCCESSFUL')));break;
				case "err" 		: $out->add(getErrorUi($lang->def('_OPERATION_FAILURE')));break;
				case "err_del" : $out->add(getErrorUi($lang->def('_OPERATION_FAILURE')));break;
			}
		}
		
		$out->add(getBackUi( 'index.php?modname=reservation&amp;op=reservation&amp;active_tab=subscribed_user', $lang->def('_BACK') ));
		
		$out->add($tb->getTable().$tb->getNavBar($ini, $tot_classroom).'</div>');
	
	}
	
	function editclassroom($load = false) {
		//checkPerm('mod');
	
		require_once(_base_.'/lib/lib.form.php');
		require_once($GLOBALS["where_lms"]."/lib/lib.classlocation.php");
	
		$lang 		=& DoceboLanguage::createInstance('classroom', 'lms');
		$form		= new Form();
		$out 		=& $GLOBALS['page'];
		$out->setWorkingZone('content');
		
		$idClassroom = importVar('idClassroom', true, 0);
		$all_languages = Docebo::langManager()->getAllLangCode();
	
		if($load) {
	
			$query_classroom = "
			SELECT name, description , location_id , room , street, city, state , zip_code,
			phone,fax, capacity, disposition, instrument, available_instrument,note,responsable
			FROM ".$GLOBALS['prefix_lms']."_classroom
			WHERE idClassroom = '".$idClassroom."'";
			list($name, $descr,$location_id,$room,$street,$city,$state,
			$zip_code,$phone,$fax,$capacity,$disposition,$instrument,$available_instrument,$note,$responsable) = sql_fetch_row(sql_query($query_classroom));
		} else {
	
			$name =  $lang->def('_NO_NAME');
			$descr = '';
			$impo = 0;
			$lang_sel = getLanguage();
			
			$location_id=FALSE;
			$room="";
			$street="";
			$city="";
			$state="";
			$zip_code="";
			$phone="";
			$fax="";
			$capacity="";
			$disposition="";
			$instrument="";
			$available_instrument="";
			$note="";
			$responsable="";
		}
	
		$page_title = array(
			'index.php?modname=classroom&amp;op=classroom' => $lang->def('_CLASSROOM'),
			( $load ? $lang->def('_MOD_CLASSROOM') : $lang->def('_NEW_CLASSROOM') )
		);
		$out->add(getTitleArea($page_title, 'classroom', $lang->def('_ALT_CLASSROOMS'))
				.'<div class="std_block">'
				.getBackUi( 'index.php?modname=reservation&amp;op=classroom', $lang->def('_BACK') )
	
				.$form->openForm('adviceform', 'index.php?modname=reservation&amp;op=saveclassroom')
		);
		if($load) {
	
			$out->add($form->getHidden('idClassroom', 'idClassroom', $idClassroom)
					.$form->getHidden('load', 'load', 1)	);
		}
		
		
		$clm=new ClassLocationManager();
		$location_arr=$clm->getClassLocationArray();
		
		$out->add($form->openElementSpace()
	
				.$form->getTextfield($lang->def('_NAME'), 'name', 'name', 255, $name)
				.$form->getTextarea($lang->def('_DESCRIPTION'), 'descr', 'descr', $descr)
				.$form->getHidden('location_id', 'location_id', 0)
				.$form->getTextfield($lang->def('_BUILDING_ROOM'), 'room', 'room', 255, $room)
				.$form->getTextfield($lang->def('_CAPACITY'), 'capacity', 'capacity',255, $capacity)
				.$form->getTextfield($lang->def('_RESPONSABLE'), 'responsable', 'responsable', 255, $responsable)
				.$form->getTextfield($lang->def('_STREET'), 'street', 'street', 255, $street)
				.$form->getTextfield($lang->def('_CITY'), 'city', 'city', 255, $city)
				.$form->getTextfield($lang->def('_STATE'), 'state', 'state', 255, $state)
				.$form->getTextfield($lang->def('_ZIP_CODE'), 'zip_code', 'zip_code', 255, $zip_code)
				.$form->getTextfield($lang->def('_PHONE'), 'phone', 'phone', 255, $phone)
				.$form->getTextfield($lang->def('_FAX'), 'fax', 'fax', 255, $fax)
				.$form->getTextarea($lang->def('_DISPOSITION'), 'disposition', 'disposition',  $disposition)
				.$form->getTextarea($lang->def('_INSTRUMENT'), 'instrument', 'instrument', $instrument)
				.$form->getTextarea($lang->def('_AVAILABLE_INSTRUMENT'), 'available_instrument', 'available_instrument', $available_instrument)
				.$form->getTextarea($lang->def('_NOTES'), 'note', 'note', $note)
				.$form->closeElementSpace()
				.$form->openButtonSpace()
				.$form->getButton('classroom', 'classroom', ( $load ? $lang->def('_SAVE') : $lang->def('_INSERT') ) )
				.$form->getButton('undo', 'undo', $lang->def('_UNDO'))
				.$form->closeButtonSpace()
				.$form->closeForm()
				.'</div>');
	
	}
	
	function saveclassroom() {
		//checkPerm('mod');
	
		$idClassroom 	= importVar('idClassroom', true, 0);
		$load 		= importVar('load', true, 0);
		$all_languages = Docebo::langManager()->getAllLangCode();
		$lang 		=& DoceboLanguage::createInstance('classroom', 'lms');
	
		if($_POST['title'] == '') $_POST['title'] = $lang->def('_NOTITLE');
		$lang_sel = $_POST['language'];
		if($load == 1) {
	
			$query_insert = "
			UPDATE ".$GLOBALS['prefix_lms']."_classroom
			SET	name = '".$_POST['name']."' ,
				description = '".$_POST['descr']."',
				location_id = '".(int)$_POST['location_id']."',
				room = '".$_POST['room']."',
				street = '".$_POST['street']."',
				city = '".$_POST['city']."',
				state = '".$_POST['state']."' ,
				zip_code = '".$_POST['zip_code']."',
				phone = '".$_POST['phone']."',
				fax = '".$_POST['fax']."',
				capacity = '".$_POST['capacity']."',
				disposition = '".$_POST['disposition']."',
				instrument = '".$_POST['instrument']."',
				available_instrument = '".$_POST['available_instrument']."',
				note = '".$_POST['note']."',
				responsable = '".$_POST['responsable']."'
				WHERE idClassroom = '".$idClassroom."'";
			if(!sql_query($query_insert)) Util::jump_to('index.php?modname=classroom&op=classroom&result=err');
			Util::jump_to('index.php?modname=reservation&op=classroom&result=ok');
			echo $query_insert;
		} else {
	
			$query_insert = "
			INSERT INTO ".$GLOBALS['prefix_lms']."_classroom
			(name, description , location_id , room , street, city, state , zip_code,
			phone,fax, capacity, disposition, instrument, available_instrument,note,responsable) VALUES
			( 	'".$_POST['name']."' ,
				'".$_POST['descr']."',
				'".(int)$_POST['location_id']."',
				'".$_POST['room']."',
				'".$_POST['street']."',
				'".$_POST['city']."',
				'".$_POST['state']."',
				'".$_POST['zip_code']."',
				'".$_POST['phone']."',
				'".$_POST['fax']."',
				'".$_POST['capacity']."',
				'".$_POST['disposition']."',
				'".$_POST['instrument']."',
				'".$_POST['available_instrument']."',
				'".$_POST['note']."',
				'".$_POST['responsable']."'
				)";
			if(!sql_query($query_insert)) Util::jump_to('index.php?modname=classroom&op=classroom&result=err');
			Util::jump_to('index.php?modname=reservation&op=classroom&result=ok');
			echo $query_insert;
		}
	}
	
	function delclassroom() {
		//checkPerm('mod');
	
		require_once(_base_.'/lib/lib.form.php');
	
		$idClassroom 	= importVar('idClassroom', true, 0);
		$lang 		=& DoceboLanguage::createInstance('classroom', 'lms');
	
		if(isset($_POST['confirm'])) {
	
			$query_classroom = "
			DELETE FROM ".$GLOBALS['prefix_lms']."_classroom
			WHERE idClassroom = '".$idClassroom."'";
			if(!sql_query($query_classroom)) Util::jump_to('index.php?modname=classroom&op=classroom&result=err_del');
			else Util::jump_to('index.php?modname=reservation&op=classroom&result=ok');
		} else {
	
			list($name, $descr) = sql_fetch_row(sql_query("
			SELECT name, description
			FROM ".$GLOBALS['prefix_lms']."_classroom
			WHERE idClassroom = '".$idClassroom."'"));
	
			$form = new Form();
			$page_title = array(
				'index.php?modname=reservation&amp;op=classroom' => $lang->def('_CLASSROOM'),
				$lang->def('_DEL_CLASSROOM')
			);
			$GLOBALS['page']->add(
				getTitleArea($page_title, 'classroom')
				.'<div class="std_block">'
				.$form->openForm('del_classroom', 'index.php?modname=reservation&amp;op=delclassroom')
				.$form->getHidden('idClassroom', 'idClassroom', $idClassroom)
				.getDeleteUi(	$lang->def('_AREYOUSURE'),
								'<span>'.$lang->def('_NAME').' : </span>'.$name.'<br />'
									.'<span>'.$lang->def('_DESCRIPTION').' : </span>'.$descr,
								false,
								'confirm',
								'undo'	)
				.$form->closeForm()
				.'</div>', 'content');
		}
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


	$back_url = 'index.php?modname=reservation&amp;op=reservation&amp;active_tab=subscribed_user';


	if( isset($_POST['okselector']) )
	{
		$arr_selection=$mdir->getSelection($_POST);
		$arr_unselected=$mdir->getUnselected();
		
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
		$mdir->setNFields(0);
		$mdir->show_group_selector=TRUE;
		$mdir->show_orgchart_selector=FALSE;
		
		$arr_idstGroup = $acl_manager->getGroupsIdstFromBasePath('/lms/course/'.(int)$_SESSION['idCourse'].'/subscribed/');
		$me = array(getLogUserId());
		$mdir->setUserFilter('exclude', $me);
		$mdir->setUserFilter('group',$arr_idstGroup);
		$mdir->setGroupFilter('path', '/lms/course/'.$_SESSION['idCourse'].'/group');
		
		$mdir->loadSelector($url,
			$lang->def( '_VIEW_PERMISSION' ), "", TRUE);
	}

}

function reservationSendMail()
{
	checkPerm('view');
	
	$lang =& DoceboLanguage::createInstance('reservation');
		
	$mod_perm 	= checkPerm('mod', true);
	
	$id_course = $_SESSION['idCourse'];
	$id_event = importVar('id_event', true, 0);
	
	$out = $GLOBALS['page'];
	$out->setWorkingZone('content');
	
	$man_res = new Man_Reservation();
	
	$acl_man =& Docebo::user()->getAclManager();
	
	if (isset($_POST['send_mail']))
	{
		$recipients = $man_res->getEventUserMail($id_event);
		
		$subject = importVar('mail_object', false, '[Nessun Oggetto]');
		$body = importVar('mail_body', false, '');

		$info_user = $acl_man->getUser(getLogUserId());
		$sender = $info_user[ACL_INFO_EMAIL];

		//sendMail($recipients, $subject, $body, $sender);
		require_once(_base_.'/lib/lib.mailer.php');
		$mailer = DoceboMailer::getInstance();
		$mailer->SendMail($sender, $recipients, Lang::t('_MAIL_OBJECT', 'register'), $body, array(MAIL_REPLYTO => $sender, MAIL_SENDER_ACLNAME => false));
				
		Util::jump_to('index.php?modname=reservation&op=reservation&active_tab=events');
	}
	else
	{
		require_once(_base_.'/lib/lib.form.php');
		
		$out->add
		(
		getTitleArea($lang->def('_RESERVATION_MAIL_SEND').'<div class="std_block">', 'content'));
		
		$out->add
		(
			Form::openForm('form_event', 'index.php?modname=reservation&amp;op=send_mail')
			.Form::openElementSpace()
			.Form::getTextfield($lang->def('_SUBJECT'), 'mail_object', 'mail_object', 255)
			.Form::getTextarea($lang->def('_MAIL_BODY', 'report'), 'mail_body', 'mail_body')
			.Form::getHidden('id_event', 'id_event', $id_event)
			.Form::closeElementSpace()
			.Form::openButtonSpace()
			.Form::getButton('send_mail', 'send_mail', $lang->def('_SEND_MAIL', 'report'))
			.Form::getButton('undo_mail', 'undo_mail', $lang->def('_UNDO'))
			.Form::closeButtonSpace()
			.Form::closeForm()
			.'</div>'
		);
	}
}

function infoLocation()
{
	require_once(_base_.'/lib/lib.table.php');
	
	checkPerm('view');
	
	$lang =& DoceboLanguage::createInstance('classroom', 'lms');
	
	$out = $GLOBALS['page'];
	$out->setWorkingZone('content');
	
	$id_location = importVar('id_location', true, 0);
	$active_tab = importVar('active_tab', false, 'events');
	
	$out->add(getTitleArea($lang->def('_RESERVATION_INFO_LOCATION')).'<div class="std_block">');
	
	$query_classroom = "SELECT name, description , location_id , room , street, city, state , zip_code,
						phone,fax, capacity, disposition, instrument, available_instrument,note,responsable
						FROM ".$GLOBALS['prefix_lms']."_classroom
						WHERE idClassroom = '".$id_location."'";
	
	list($name, $descr,$location_id,$room,$street,$city,$state,
	$zip_code,$phone,$fax,$capacity,$disposition,$instrument,$available_instrument,$note,$responsable) = sql_fetch_row(sql_query($query_classroom));
	
	$tb = new Table(15, $lang->def('_LOCATION_INFO'), $lang->def('_LOCATION_INFO'));
	
	$type_h = array('', '');
	
	$tb->setColsStyle($type_h);
	
	$tb->addBody(array('<b>'.$lang->def('_NAME').':</b>', $name));
	$tb->addBody(array('<b>'.$lang->def('_DESCRIPTION').':</b>', $descr));
	$tb->addBody(array('<b>'.$lang->def('_BUILDING_ROOM').':</b>', $room));
	$tb->addBody(array('<b>'.$lang->def('_CAPACITY').':</b>', $capacity));
	$tb->addBody(array('<b>'.$lang->def('_RESPONSABLE').':</b>', $responsable));
	$tb->addBody(array('<b>'.$lang->def('_STREET').':</b>', $street));
	$tb->addBody(array('<b>'.$lang->def('_CITY').':</b>', $city));
	$tb->addBody(array('<b>'.$lang->def('_STATE').':</b>', $state));
	$tb->addBody(array('<b>'.$lang->def('_ZIP_CODE').':</b>', $zip_code));
	$tb->addBody(array('<b>'.$lang->def('_PHONE').':</b>', $phone));
	$tb->addBody(array('<b>'.$lang->def('_FAX').':</b>', $fax));
	$tb->addBody(array('<b>'.$lang->def('_DISPOSITION').':</b>', $disposition));
	$tb->addBody(array('<b>'.$lang->def('_INSTRUMENT').':</b>', $instrument));
	$tb->addBody(array('<b>'.$lang->def('_AVAILABLE_INSTRUMENT').':</b>', $available_instrument));
	$tb->addBody(array('<b>'.$lang->def('_NOTES').':</b>', $note));
	
	$out->add(
		$tb->getTable()
		.getBackUi('index.php?modname=reservation&amp;op=reservation&amp;active_tab='.$active_tab.(isset($_GET['order_by']) ? '&amp;order_by='.$_GET['order_by'] : ''), $lang->def('_BACK'))
		.'</div>');
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
	if (isset($_POST['add_event']))
		Util::jump_to('index.php?modname=reservation&amp;op=add_event');
	if (isset($_POST['category_gestion']))
		Util::jump_to('index.php?modname=reservation&amp;op=view_category&amp;id_course='.$_SESSION['idCourse']);
	if (isset($_POST['location_gestion']))
		Util::jump_to('index.php?modname=reservation&amp;op=classroom&amp;id_course='.$_SESSION['idCourse']);
	if (isset($_POST['undo']))
		Util::jump_to('index.php?modname=reservation&op=reservation&active_tab=subscribed_user');
	if (isset($_POST['undo_switch']))
		Util::jump_to('index.php?modname=reservation&op=reservation&active_tab=events');
	if (isset($_POST['undo_profile']))
		Util::jump_to('index.php?modname=reservation&op=reservation&active_tab=events');
	if (isset($_POST['undo_mail']))
		Util::jump_to('index.php?modname=reservation&op=reservation&active_tab=events');
	if (isset($_POST['okselector']))
		if(isset($_GET['id_course']))
			$op = 'send_registration';
	if (isset($_POST['cancelselector']))
		Util::jump_to('index.php?modname=reservation&op=reservation&active_tab=subscribed_user');
	if (isset($_POST['undo_cat']))
		$op = 'view_category';
	if (isset($_POST['undo_lab']))
		$op = 'view_laboratories';
	switch ($op)
	{
		case 'del_subscription':
			delSubscription();
		break;
		
		case 'add_subscription':
			addSubscription();
		break;
		
		case 'add_registration':
			addRegistration();
		break;
		
		case 'send_registration':
			sendRegistration();
		break;
		
		case 'del_event':
			delEvent();
		break;
		
		case 'mod_event':
			modEvent();
		break;
		
		case 'add_event':
			addEvent();
		break;
		
		case 'view_user_event':
			viewUserEvent();
		break;
		
		case 'send_user_event':
			sendUserEvent();
			break;
		
		case 'excel':
			getExcelFile();
		break;
		
		case 'reservation':
		default:
			reservation();
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
		
		/*case 'view_laboratories':
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
		//TODO:
		case "classroom" :
			classroom();
		break;
		
		case "addclassroom" :
			editclassroom();
		break;
		
		case "modclassroom" :
			editclassroom(true);
		break;
		
		case "saveclassroom" :
			saveclassroom();
		break;
		
		case "delclassroom" :
			delclassroom();
		break;
		//TODO:
		case 'switch_subscription':
			switchSubscription();
		break;
		
		case 'set_room_view_perm':
			setRoomViewPerm();
		break;
		
		case 'send_mail':
			reservationSendMail();
		break;
		
		case 'info_location':
			infoLocation();
		break;
	}
}

?>
