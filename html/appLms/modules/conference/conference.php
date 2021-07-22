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
 * @subpackage conference
 * @category driver with external
 * @author Fabio Pirovano
 * @version $Id:$
 * @since 3.5.0
 *
 * ( editor = Eclipse 3.2.0[phpeclipse,subclipse,WTP], tabwidth = 4 )
 */

require_once($GLOBALS['where_scs'].'/lib/lib.conference.php');

function conference_list(&$url) {
	checkPerm('view');
	//$mod_perm = checkPerm('mod');

	$lang =& DoceboLanguage::createInstance('conference', 'lms');

	require_once(_base_.'/lib/lib.table.php');
	require_once($GLOBALS['where_scs'].'/lib/lib.booking.php');

	$idCourse=$_SESSION['idCourse'];

	$conference = new Conference_Manager();
	$re_room 		= $conference->roomActive($_SESSION['idCourse'], fromDatetimeToTimestamp(date("Y-m-d H:i:s")));
	$room_number 	= $conference->totalRoom($re_room);
        
	$GLOBALS['page']->setWorkingZone('content');
        $GLOBALS['page']->add(
                getTitleArea($lang->def('_VIDEOCONFERENCE'), 'conference')
                .'<div class="std_block">');

	if(checkPerm('mod', true)) {
		 $GLOBALS['page']->add('<div class="yui-navset yui-navset-top tab_block">
			<ul class="nav nav-tabs">
				<li class="active">
					<a href="index.php?modname=conference&amp;op=show">
						<em>'.Lang::t('_ACTIVE', 'conference').'</em>
					</a>
				</li>
				<li>
					<a href="index.php?modname=conference&amp;op=history">
						<em>'.Lang::t('_HISTORY', 'conference').'</em>
					</a>
				</li>
			</ul>
			<div class="yui-content">'
		);
	}

	if($room_number == 0) {
		// no rooms
		$GLOBALS['page']->add('<b id="noroom">'.$lang->def('_NO_ROOM_AVAILABLE').'</b>', 'content');
	} else {

		// list rooms active in this moment
		$tb = new Table(0, $lang->def('_ROOMS_AVAILABLE'), $lang->def('_SUMMARY_ROOM_AVAILABLE'));

		$cont_h = array($lang->def('_VIDEOCONFERENCE'),
						$lang->def('_START_DATE'),
						$lang->def('_MEETING_HOURS'),
						$lang->def('_ENTER'));

		$type_h = array('table_main_colum', 'align_center nowrap', 'align_center nowrap', 'align_center');

		if(checkPerm('mod', true))
		{
			/*$cont_h[] = '';
			$type_h[] = 'image';
*/

			$cont_h[] = '<img src="'.getPathImage().'/standard/edit.png'.'" />';
			$type_h[] = 'image';

			$cont_h[] = '<img src="'.getPathImage().'/standard/delete.png'.'" />';
			$type_h[] = 'image';
		}

		$tb->setColsStyle($type_h);
		$tb->addHead($cont_h);

		$acl_manager =& Docebo::user()->getAclManager();
		$display_name = Docebo::user()->getUserName();
		$u_info = $acl_manager->getUser(getLogUserId(), false);
		$user_email=$u_info[ACL_INFO_EMAIL];

		while($room = $conference->nextRow($re_room)) {

			$room_id = $room["id"];

			$cont = array();
			$cont[]=$room["name"]." (".$room["room_type"].")";
			$start_date=Format::date(date("Y-m-d H:i:s",$room["starttime"]), 'datetime');
			$cont[]=$start_date;
			$cont[]=$room["meetinghours"];

			$now=time();

			/*$booking = new RoomBooking();

			$user_booked = $booking->userIsBooked(getLogUserId(), $room["id"]);
			$user_valid = $booking->userIsValid(getLogUserId(), $room["id"]);
			$room_full = $booking->roomIsFull($room["id"]);

			if ($room["endtime"]>=$now && $room["starttime"]<=$now && $user_booked && $user_valid)
				$cont[]=$conference->getUrl($room["id"],$room["room_type"]);
			elseif($user_booked && $user_valid)
				$cont[] = $lang->def('_WAITING_START');
			elseif($room_full)
				$cont[] = $lang->def('_ROOM_FULL');
			elseif($user_booked && !$user_valid)
				$cont[] = $lang->def('_PENDING_VALIDATION');
			elseif($room['bookable'])
				$cont[]='<a href="index.php?modname=conference&amp;op=booking&id='.$room["id"].'">'.$lang->def('_BOOKING_CONFERENCE').'</a>';
			elseif ($room["endtime"]>=$now && $room["starttime"]<=$now)
				$cont[]=$conference->getUrl($room["id"],$room["room_type"]);
			else
				$cont[] = $lang->def('_WAITING_START');
*/

			$cont[]=$conference->getUrl($room["id"],$room["room_type"]);
			if(checkPerm('mod', true))
			{
				if (getLogUserId()==$room["idSt"] || Docebo::user()->getUserLevelId()==ADMIN_GROUP_GODADMIN)
					$cont[] =	'<a href="index.php?modname=conference&amp;op=modconf&amp;id='.$room["id"].'">'
								.'<img src="'.getPathImage().'/standard/edit.png'.'" /></a>';
				else
					$cont[] = '';

				if (getLogUserId()==$room["idSt"] || Docebo::user()->getUserLevelId()==ADMIN_GROUP_GODADMIN)
					$cont[]='<a href="index.php?modname=conference&amp;op=delconf&id='.$room["id"].'" '
							.'"><img src="'.getPathImage().'/standard/delete.png'.'" /></a>';
				else
					$cont[] = '';
			}

			$tb->addBody($cont);


		} // end while

		require_once(_base_.'/lib/lib.dialog.php');
		setupHrefDialogBox('a[href*=delconf]');

		$GLOBALS['page']->add($tb->getTable().'</div>', 'content');
	}

// TODO : support for BBB is experimental - must be refined

	if(checkPerm('mod', true)) {
		cout('<br/><div class="table-container-below">', 'content');
		if(
		  Get::sett('code_teleskill')
		  or
		  (Get::sett('dimdim_server') and Get::sett('dimdim_user') and Get::sett('dimdim_password'))
		  or
		  (Get::sett('ConferenceBBB_server') and Get::sett('ConferenceBBB_user') and Get::sett('ConferenceBBB_salt') and Get::sett('ConferenceBBB_password_moderator') and Get::sett('ConferenceBBB_password_viewer'))
		  ) {
			if ($conference->can_create_user_limit(getLogUserId(),$idCourse,time())) {
				cout('<a class="ico-wt-sprite subs_add" href="'.$url->getUrl('op=startnewconf').'"><span>'.$lang->def('_CREATE').'</span></a>', 'content');
			} else {
				cout('<b>'.$lang->def('_NO_MORE_ROOM'), 'content');
			}
		}
		cout('</div>', 'content');
	}

	if(checkPerm('mod', true)) {
		cout('<div class="nofloat"></div></div></div></div>', 'content');
	} else {
		cout('</div></div>', 'content');
	}
}

function conference_startnewconf($url) {
	checkPerm('view');
	$mod_perm = checkPerm('mod');

	require_once(_base_.'/lib/lib.form.php');

	$lang =& DoceboLanguage::createInstance('conference', 'lms');

	if(isset($_POST['create_conf'])) {

		$conference = new Conference_Manager();

		$start_date = Format::dateDb($_POST['start_date'], 'date');
		$start_date = substr($start_date, 0, 10);

		$start_time = ( strlen($_POST['start_time']['hour']) == 1 ? '0' : '' ).$_POST['start_time']['hour'].':'
			.( strlen($_POST['start_time']['minute']) == 1 ? '0' : '' ).$_POST['start_time']['minute'].':00';

		$start_timestamp = fromDatetimeToTimestamp($start_date.' '.$start_time);

		$conference_name=(trim($_POST["conference_name"]))?(trim($_POST["conference_name"])):($lang->def('_VIDEOCONFERENCE'));

		$meetinghours=(int)$_POST["meetinghours"];

		$end_timestamp = $start_timestamp + $meetinghours * 3600;

		$maxparticipants=(int)$_POST["maxparticipants"];

		$idCourse=$_SESSION['idCourse'];
		$room_type=$_POST["room_type"];

		if (	$conference->can_create_room_limit(getLogUserId(),$idCourse,$room_type,$start_timestamp,$end_timestamp) &&
				$conference->can_create_user_limit(getLogUserId(),$idCourse,$start_timestamp))
		{
			$conference->insert_room($idCourse,getLogUserId(),$conference_name,$room_type,$start_timestamp,$end_timestamp,$meetinghours,$maxparticipants, (isset($_POST['bookable']) ? 1 : 0),
				$start_date,
				(int)$_POST['start_time']['hour'],
				(int)$_POST['start_time']['minute']
			);
			Util::jump_to('index.php?modname=conference&amp;op=list');
		} else {
			$title_page = array(
			'index.php?modname=conference&amp;op=list' => $lang->def('_VIDEOCONFERENCE'),
			$lang->def('_CREATE')
		);
		$GLOBALS['page']->add(
			getTitleArea($title_page, 'conference', $lang->def('_VIDEOCONFERENCE'))
			.'<div class="std_block">'
			.'<span><strong>'.$lang->def('_NO_MORE_ROOM').'</strong></span>'
			.'</div>', 'content');
			return false;
		}

	}

	$start_time['hour'] 	= date('H');
	$start_time['minute'] 	= date('i');
	$start_date = importVar('start_date', false, date("Y-m-d H:i:s"));

	$conf_system=array();
	//$conf_system[""]="";

	$default_maxp=30;

	$pg=new PluginManager('Conference');
	foreach ($pg->run('name') as $name){
        $conf_system[$name]=$name;
    }
    $default=array_values($conf_system)[0];

	YuiLib::load();

	//addJs($GLOBALS['where_lms_relative'].'/modules/conference/', 'ajax_conference.js');

	$GLOBALS['page']->add(
		getTitleArea($lang->def('_VIDEOCONFERENCE'), 'conference')
		.'<div class="std_block">'
	, 'content');

	$GLOBALS['page']->add(
		Form::openForm('create_conference', $url->getUrl('op=startnewconf'))
		.Form::openElementSpace()
		.Form::getTextfield(	$lang->def('_VIDEOCONFERENCE'),
								'conference_name',
								'conference_name',
								255,
								importVar('conference_name') )


		.Form::getLineBox(
			$lang->def('_CONFERENCE_SYSTEM'),
			Form::getInputDropdown('', 'room_type', 'room_type', $conf_system
				, $default
				, ''/*, 'onchange="getMaxRoom()"'*/ )
		)

		.Form::getDatefield($lang->def('_START_DATE'), 	'start_date', 'start_date',
			Format::date($start_date, 'date') )

		.Form::getLineBox(
			$lang->def('_AT_HOUR'),
			Form::getInputDropdown('', 'start_time_hour', 'start_time[hour]', range(0, 23)
				, importVar('start_time_hour', false, date("H"))
				, '' )
			.' : '
			.Form::getInputDropdown('', 'start_time_minute', 'start_time[minute]', range(0, 59)
				, importVar('start_time_hour', false, date("i"))
				, '' )
		)

		.Form::getLineBox(
			$lang->def('_MEETING_HOURS'),
			Form::getInputDropdown('', 'meetinghours', 'meetinghours', range(0, 5)
				, importVar('meetinghours', false, 2)
				, '' )

		)

		.Form::getTextfield(	$lang->def('_MAX_PARTICIPANTS'),
								'maxparticipants',
								'maxparticipants',
								6,
								importVar('maxparticipants', true, $default_maxp) ), 'content');
	if(Get::sett('use_dimdim_api') === 'on')
		$GLOBALS['page']->add(
				'<div id="dimdim_conf" style="'.($default === 'dimdim' ? 'display:block;' : 'display:none;').'">'
				.Form::getOpenFieldset(Lang::t('_DIMDIM_FEATURES', 'conference'), 'dimdim_features')
				.Form::getCheckbox(Lang::t('_SHOW_WAITING_AREA', 'conference'), 'lobbyEnabled', 'lobbyEnabled', '1')
				//.Form::getCheckbox(Lang::t('_DISPLAY_PHONE_INFO', 'conference'), 'display_phone_info', 'display_phone_info', '1')
				//.Form::getCheckbox(Lang::t('_SHOW_PART_LIST', 'conference'), 'show_part_list', 'show_part_list', '1')
				.Form::getCheckbox(Lang::t('_ENABLE_PRIVATE_CHAT', 'conference'), 'privateChatEnabled', 'privateChatEnabled', '1')
				.Form::getCheckbox(Lang::t('_ENABLE_PUBLIC_CHAT', 'conference'), 'publicChatEnabled', 'publicChatEnabled', '1')
				.Form::getCheckbox(Lang::t('_ENABLE_DESKTOP_SHARING', 'conference'), 'screenShareEnabled', 'screenShareEnabled', '1')
				//.Form::getCheckbox(Lang::t('_MEETING_ASSISTANT_VISIBILITY', 'conference'), 'meeting_assistant_visibility', 'meeting_assistant_visibility', '1')
				.Form::getCheckbox(Lang::t('_ASSIGN_MIC_TO_ATTENDEES', 'conference'), 'autoAssignMikeOnJoin', 'autoAssignMikeOnJoin', '1')
				.Form::getCheckbox(Lang::t('_ENABLE_WHITEBOARD', 'conference'), 'whiteboardEnabled', 'whiteboardEnabled', '1')
				.Form::getCheckbox(Lang::t('_ENABLE_DOCUMENTS_SHARING', 'conference'), 'documentSharingEnabled', 'documentSharingEnabled', '1')
				//.Form::getCheckbox(Lang::t('_ENABLE_WEB_SHARING', 'conference'), 'enable_web_sharing', 'enable_web_sharing', '1')
				.Form::getCheckbox(Lang::t('_ENABLE_RECORDING', 'conference'), 'recordingEnabled', 'recordingEnabled', '1')
				//.Form::getCheckbox(Lang::t('_ALLOW_ATTENDEES_INVITATION', 'conference'), 'allow_attendees_invitation', 'allow_attendees_invitation', '1')
				.Form::getCheckbox(Lang::t('_ENABLE_HANDS_FREE', 'conference'), 'autoHandsFreeOnAVLoad', 'autoHandsFreeOnAVLoad', '1')
				.Form::getCheckbox(Lang::t('_START_MAIL', 'conference'), 'joinEmailRequired', 'joinEmailRequired', '1')

				//.Form::getTextfield(Lang::t('_RECORDING_CODE', 'conference'), 'recording_code', 'recording_code', 255, '')

				.'<script type="text/javascript">'
				.' var room_type = YAHOO.util.Dom.get(\'room_type\');'
				.' YAHOO.util.Event.addListener(room_type, \'change\', dimdimEvent);'
				.' function dimdimEvent(e)'
				.' {'
				.' var room_type = YAHOO.util.Dom.get(\'room_type\');'
				.' var dimdim_conf = YAHOO.util.Dom.get(\'dimdim_conf\');'
				.' if(room_type.value == "dimdim")'
				.' dimdim_conf.style.display = "block";'
				.' else'
				.' dimdim_conf.style.display = "none";'
				.' }'
				.'</script>'
				.Form::getCloseFieldset()
				.'</div>', 'content');

	$GLOBALS['page']->add(
		Form::closeElementSpace()
		.Form::openButtonSpace()
		.Form::getButton('create_conf', 'create_conf', $lang->def('_CREATE'))
		.Form::getButton('undo', 'undo', $lang->def('_UNDO'))
		.Form::closeButtonSpace()
		.Form::closeForm()

		.'</div>'
	, 'content');
}

function conference_delconf() {
	checkPerm('mod');

	$id=importVar('id');
	$conference = new Conference_Manager();

	$room=$conference->roomInfo($id);

	$lang =& DoceboLanguage::createInstance('conference', 'lms');
	if( Get::req('confirm', DOTY_INT, 0) ) {

		$conference->deleteRoom($id);

		require_once($GLOBALS['where_scs'].'/lib/lib.booking.php');

		$booking = new RoomBooking();

		$booking->deleteBookingByRoom($id);

		Util::jump_to('index.php?modname=conference&amp;op=list');
	} else {
		$title_page = array(
			'index.php?modname=conference&amp;op=list' => $lang->def('_VIDEOCONFERENCE'),
			$lang->def('_DEL')
		);
		$GLOBALS['page']->add(
			getTitleArea($title_page, 'conference', $lang->def('_VIDEOCONFERENCE'))
			.'<div class="std_block">'
			.getDeleteUi(	$lang->def('_AREYOUSURE'),
							'<span>'.$lang->def('_VIDEOCONFERENCE').' : </span>'.$room["name"],
							true,
							'index.php?modname=conference&amp;op=delconf&amp;id='.$id.'&amp;confirm=1',
							'index.php?modname=conference&amp;op=list' )
			.'</div>', 'content');

	}
}

function conference_modconf($url = null)
{
	$lang =& DoceboLanguage::createInstance('conference', 'lms');

	$id_room = Get::req('id', DOTY_INT, 0);

	$conference = new Conference_Manager();

        $room_info = $conference->roomInfo($id_room);
        $start_date=Format::date(date("Y-m-d H:i:s",$room_info["starttime"]), 'datetime');
        list($date, $time) = explode(' ', $start_date);
        list($hour, $min, $sec) = explode(':', $time);

	if(isset($_POST['update_conf']))
	{

        $conference = new Conference_Manager();

        $start_date = Format::dateDb($_POST['start_date'], 'date');
        $start_date = substr($start_date, 0, 10);

        $start_time = ( strlen($_POST['start_time']['hour']) == 1 ? '0' : '' ).$_POST['start_time']['hour'].':'
                .( strlen($_POST['start_time']['minute']) == 1 ? '0' : '' ).$_POST['start_time']['minute'].':00';

        $start_timestamp = fromDatetimeToTimestamp($start_date.' '.$start_time);

        $conference_name=(trim($_POST["conference_name"]))?(trim($_POST["conference_name"])):($lang->def('_VIDEOCONFERENCE'));

        $meetinghours=(int)$_POST["meetinghours"];

        $end_timestamp = $start_timestamp + $meetinghours * 3600;

        $maxparticipants=(int)$_POST["maxparticipants"];

        $idCourse=$_SESSION['idCourse'];
        $room_type=$_POST["room_type"];


        $conference->updateRoom($id_room,$conference_name,$room_type,$start_timestamp,$end_timestamp,$meetinghours,$maxparticipants,(isset($_POST['bookable']) ? 1 : 0),
                $start_date,
                (int)$_POST['start_time']['hour'],
                (int)$_POST['start_time']['minute']
        );
        Util::jump_to('index.php?modname=conference&amp;op=list');
	}
	else
	{
		cout(	getTitleArea($lang->def('_MOD_CONFERENCE'))
				.'<div class="std_block">');

        checkPerm('view');
        $mod_perm = checkPerm('mod');

        require_once(_base_.'/lib/lib.form.php');

        $lang =& DoceboLanguage::createInstance('conference', 'lms');

        $conf_system=array();
        //$conf_system[""]="";
        $default_maxp=30;

        $pg=new PluginManager('Conference');
        foreach ($pg->run('name') as $name){
            $conf_system[$name]=$name;
        }
        $default=array_values($conf_system)[0];
        YuiLib::load();

        //addJs($GLOBALS['where_lms_relative'].'/modules/conference/', 'ajax_conference.js');

        $GLOBALS['page']->add(
                getTitleArea($lang->def('_VIDEOCONFERENCE'), 'conference')
                .'<div class="std_block">'
        , 'content');

        $GLOBALS['page']->add(
                Form::openForm('mod_conference', $url->getUrl('op=modconf&id='.$id_room))
                .Form::openElementSpace()
                .Form::getTextfield(	$lang->def('_VIDEOCONFERENCE'),
                                                                'conference_name',
                                                                'conference_name',
                                                                255,
                                                                $room_info['name'] )


                .Form::getLineBox(
                        $lang->def('_CONFERENCE_SYSTEM'),
                        Form::getInputDropdown('', 'room_type', 'room_type', $conf_system
                                , $room_info['room_type']
                                , ''/*, 'onchange="getMaxRoom()"'*/ )
                )

                .Form::getDatefield($lang->def('_START_DATE'), 	'start_date', 'start_date',
                         $start_date)

                .Form::getLineBox(
                        $lang->def('_AT_HOUR'),
                        Form::getInputDropdown('', 'start_time_hour', 'start_time[hour]', range(0, 23)
                                , $hour
                                , '' )
                        .' : '
                        .Form::getInputDropdown('', 'start_time_minute', 'start_time[minute]', range(0, 59)
                                ,  $min
                                , '' )
                )

                .Form::getLineBox(
                        $lang->def('_MEETING_HOURS'),
                        Form::getInputDropdown('', 'meetinghours', 'meetinghours', range(0, 5)
                                , $room_info['meetinghours']
                                , '' )

                )

                .Form::getTextfield(	$lang->def('_MAX_PARTICIPANTS'),
                                                                'maxparticipants',
                                                                'maxparticipants',
                                                                6,
                                                                $room_info['maxparticipants']), 'content');


        $GLOBALS['page']->add(
                Form::closeElementSpace()
                .Form::openButtonSpace()
                .Form::getButton('update_conf', 'update_conf', $lang->def('_MOD'))
                .Form::getButton('undo', 'undo', $lang->def('_UNDO'))
                .Form::closeButtonSpace()
                .Form::closeForm()

                .'</div>'
        , 'content');

		cout('</div>');
	}
}

function booking()
{
	require_once($GLOBALS['where_scs'].'/lib/lib.booking.php');

	$lang =& DoceboLanguage::createInstance('conference', 'lms');

	$room_id = Get::req('id', DOTY_INT, 0);

	$booking = new RoomBooking();

	$result = $booking->bookRoom(getLogUserId(), $room_id);

	Util::jump_to('index.php?modname=conference&op=list&result='.($result ? 'ok' : 'err'));
}

function modBooking()
{
	require_once($GLOBALS['where_scs'].'/lib/lib.booking.php');
	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.table.php');

	YuiLib::load(array('selector' => 'selector-beta-min.js'));

	$lang =& DoceboLanguage::createInstance('conference', 'lms');

	$room_id = Get::req('id', DOTY_INT, 0);

	$booking = new RoomBooking();

	$acl_man =& Docebo::user()->getAclManager();

	$user_booked = $booking->getRoomSubscriptions($room_id);

	if(isset($_POST['confirm']))
	{
		foreach($user_booked as $user)
		{
			$booking->setApproved($user['idUser'], $room_id, (isset($_POST['user_'.$user['idUser']]) ? 1 : 0));

			Util::jump_to('index.php?modname=conference&op=list');
		}
	}
	else
	{
		
                $GLOBALS['page']->setWorkingZone('content');
                $GLOBALS['page']->add(	getTitleArea($lang->def('_MOD_BOOKING_TITLE'))
				.'<div class="std_block">'
                        );

		$conference = new Conference_Manager();

		$tb = new Table(0, $lang->def('_USER_BOOKED'), $lang->def('_USER_BOOKED'));

		$cont_h = array($lang->def('_FULLNAME'),
						$lang->def('_BOOKING_DATE'),
						'');

		$type_h = array(	'',
							'align_center',
							'align_center');

		$tb->setColsStyle($type_h);
		$tb->addHead($cont_h);

		$user_selected = 0;
		$max_user_selectable = $conference->getRoomMaxParticipants($room_id);

		$array_unchecked = array();

		cout(	Form::openForm('user_booking_form', 'index.php?modname=conference&amp;op=modbooking&amp;id='.$room_id));

		foreach($user_booked as $user)
		{
			$user_info = $acl_man->getUser($user['idUser'], false);

			$cont = array();

			if($user_info[ACL_INFO_FIRSTNAME] !== '' && $user_info[ACL_INFO_LASTNAME])
				$cont[] = $user_info[ACL_INFO_FIRSTNAME].' '.$user_info[ACL_INFO_LASTNAME].' ('.$acl_man->relativeId($user_info[ACL_INFO_USERID]).')';
			elseif($user_info[ACL_INFO_FIRSTNAME] !== '')
				$cont[] = $user_info[ACL_INFO_FIRSTNAME].' ('.$acl_man->relativeId($user_info[ACL_INFO_USERID]).')';
			elseif($user_info[ACL_INFO_LASTNAME] !== '')
				$cont[] = $user_info[ACL_INFO_LASTNAME].' ('.$acl_man->relativeId($user_info[ACL_INFO_USERID]).')';
			else
				$cont[] = $acl_man->relativeId($user_info[ACL_INFO_USERID]);

			$cont[] = Format::date($user['date'], 'datetime');

			$cont[] = '<div class="form_line_l"><input class="check" type="checkbox" id="user_'.$user['idUser'].'" name="user_'.$user['idUser'].'" value="1" '.($user['valid'] ? 'checked="checked"' : '').'/></div>';

			if($user['valid'])
				$user_selected++;
			else
				$array_unchecked[] = 'user_'.$user['idUser'];

			$tb->addBody($cont);
		}

		reset($user_booked);

		addJs($GLOBALS['where_lms_relative'].'/modules/conference/', 'conference.js');

		cout(	'<script>'."\n"
				.'var num_checked = '.$user_selected.';'."\n"
				.'var max_checked ='.$max_user_selectable.';'."\n");

		cout(	'unchecked = new Array(');

		$first = true;
		foreach($array_unchecked as $unchecked)
			if($first)
			{
				cout('"'.$unchecked.'"');
				$first = false;
			}
			else
				cout(',"'.$unchecked.'"');

		cout(');'."\n");


		cout(	'</script>'."\n"
				.$tb->getTable()
				.Form::openButtonSpace()
				.Form::getButton('confirm', 'confirm', $lang->def('_CONFIRM'))
				.Form::getButton('undo', 'undo', $lang->def('_UNDO'))
				.Form::closeButtonSpace()
				.Form::closeForm());

		foreach($user_booked as $user)
			cout('<script>YAHOO.util.Event.addListener("user_'.$user['idUser'].'", "click", onClick);</script>');

		cout('<script>controlChecked();</script>');

		cout('</div>');
	}
}

function showHistory()
{
	checkPerm('mod');

	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.table.php');

	$lang =& DoceboLanguage::createInstance('conference', 'lms');

	$conference = new Conference_Manager();

	cout('<div class="yui-navset yui-navset-top tab_block">
		<ul class="nav nav-tabs">
			<li class="first">
				<a href="index.php?modname=conference&amp;op=show">
					<em>'.Lang::t('_ACTIVE', 'conference').'</em>
				</a>
			</li>
			<li class="active">
				<a href="index.php?modname=conference&amp;op=history">
					<em>'.Lang::t('_HISTORY', 'conference').'</em>
				</a>
			</li>
		</ul>
		<div class="yui-content">'
	, 'content');
	$tb = new Table(10, $lang->def('_OLD_ROOMS'), $lang->def('_OLD_ROOM'));

	$tb->initNavBar('ini', 'button');

	$ini = $tb->getSelectedElement();

	if(isset($_POST['unset_filter']))
		unset($_POST['filter_date']);

	$date_filter = Get::req('filter_date', DOTY_MIXED, '');

	$rooms	 		= $conference->getOldRoom($_SESSION['idCourse'], $ini);
	$rooms_number 	= $conference->getOldRoomNumber($_SESSION['idCourse']);

	if($rooms_number == 0)
		$GLOBALS['page']->add(	Form::openForm('history_table', 'index.php?modname=conference&amp;op=history')
								.Form::openElementSpace()
								.Form::getDatefield($lang->def('_DATE'), 'filter_date', 'filter_date', $date_filter)
								.Form::closeElementSpace()
								.Form::openButtonSpace()
								.Form::getButton('filter', 'filter', $lang->def('_FILTER'))
								.Form::getButton('unset_filter', 'unset_filter', $lang->def('_UNDO'))
								.Form::closeElementSpace()
								.Form::closeForm()
								.'<strong id="noroom">'.$lang->def('_NO_ROOM_AVAILABLE').'</strong>'
								.'<br/>'
								.getBackUi('index.php?modname=conference&amp;op=list', $lang->def('_BACK')), 'content');
	else
	{


		$cont_h = array($lang->def('_VIDEOCONFERENCE'),
						$lang->def('_START_DATE'),
						$lang->def('_MEETING_HOURS'),
						'');

		$type_h = array('table_main_colum', 'align_center nowrap', 'align_center nowrap', 'image');

		$tb->setColsStyle($type_h);
		$tb->addHead($cont_h);

		foreach($rooms as $room_info)
		{
			$room_id = $room_info['id'];

			$cont = array();

			$cont[] = $room_info['name']." (".$room_info['room_type'].")";

			$cont[] = Format::date(date('Y-m-d H:i:s',$room_info['starttime']), 'datetime');

			$cont[] = $room_info['meetinghours'];

			$now=time();

			if ($room_info['room_type'] == 'teleskill')
				$cont[] =	'<a href="index.php?modname=conference&amp;op=log&amp;id='.$room_info['id'].'" '
							.'title="'.$lang->def('_LOG').' : '.strip_tags($room_info['name']).'"><img src="'.getPathImage().'/standard/edit.png'.'" /></a>';
			else
				$cont[] = '';

			$tb->addBody($cont);
		}

		cout(	Form::openForm('history_table', 'index.php?modname=conference&amp;op=history')
				.Form::openElementSpace()
				.Form::getDatefield($lang->def('_DATE'), 'filter_date', 'filter_date', $date_filter)
				.Form::closeElementSpace()
				.Form::openButtonSpace()
				.Form::getButton('filter', 'filter', $lang->def('_FILTER'))
				.Form::getButton('unset_filter', 'unset_filter', $lang->def('_UNDO'))
				.Form::closeElementSpace()
				.Form::closeForm()
				.$tb->getTable()
				.$tb->getNavBar($ini, $rooms_number)
				.'<br/>'
				.getBackUi('index.php?modname=conference&amp;op=list', $lang->def('_BACK')), 'content');
	}

	cout('<div class="nofloat"></div></div></div>', 'content');
}

function showLog()
{
	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.table.php');

	$lang =& DoceboLanguage::createInstance('conference', 'lms');

	$id = Get::req('id', DOTY_INT, 0);

	$conference = new Conference_Manager();

	$room_info = $conference->roomInfo($id);

	$acl_man =& Docebo::user()->getAclManager();

	cout(	getTitleArea('')
			.'<div class="std_block">', 'content');

	$room_log = array();

	switch($room_info['room_type'])
	{
		case 'teleskill':
			require_once($GLOBALS['where_scs'].'/lib/lib.teleskill.php');

			$teleskill = new Teleskill_Management();

			$roomid = $teleskill->getRoomId($id);

			if(isset($_POST['update_log']))
				$teleskill->updateRoomLog($roomid);

			$room_log = $teleskill->getRoomLog($roomid);
		break;
	}

	$tb = new Table(0, $lang->def('_ROOM_LOG'), $lang->def('_ROOM_LOG'));

	$cont_h = array($lang->def('_FULLNAME'),
					$lang->def('_ROLE'),
					$lang->def('_DATE'),
					$lang->def('_TOTAL_TIME'),
					$lang->def('_NUMBER_OF_ACCESS'));

	$type_h = array('', '', '', '', '');

	$tb->setColsStyle($type_h);
	$tb->addHead($cont_h);

	foreach($room_log as $log_row)
	{
		$user_info = $acl_man->getUser($log_row['idUser'], false);

		$cont = array();

		if($user_info[ACL_INFO_FIRSTNAME] !== '' && $user_info[ACL_INFO_LASTNAME])
			$cont[] = $user_info[ACL_INFO_FIRSTNAME].' '.$user_info[ACL_INFO_LASTNAME].' ('.$acl_man->relativeId($user_info[ACL_INFO_USERID]).')';
		elseif($user_info[ACL_INFO_FIRSTNAME] !== '')
			$cont[] = $user_info[ACL_INFO_FIRSTNAME].' ('.$acl_man->relativeId($user_info[ACL_INFO_USERID]).')';
		elseif($user_info[ACL_INFO_LASTNAME] !== '')
			$cont[] = $user_info[ACL_INFO_LASTNAME].' ('.$acl_man->relativeId($user_info[ACL_INFO_USERID]).')';
		else
			$cont[] = $acl_man->relativeId($user_info[ACL_INFO_USERID]);

		$cont[] = ($log_row['role'] == 1 ? $lang->def('_USER_ROLE') : $lang->def('_TUTOR_ROLE'));

		$cont[] = Format::date($log_row['date'], 'datetime');

		$duration_s = 0;
		$duration_m = 0;
		$duration_h = 0;

		$duration = $log_row['duration'];

		$duration_s = $duration % 60;

		$duration -= $duration_s;

		if($duration)
		{
			$duration_m = ($duration % 3600) / 60;

			$duration -= $duration_m * 60;

			if($duration)
				$duration_h = $duration / 3600;
		}

		$cont[] = $duration_h.' '.$lang->def('_HOURS').' '.$duration_m.' '.$lang->def('_MINUTS').' '.$duration_s.' '.$lang->def('_SECONDS');

		$cont[] = $log_row['access'];

		$tb->addBody($cont);
	}

	$tb->addActionAdd(Form::getButton('update_log', 'update_log', $lang->def('_UPDATE_LOG')));

	cout(	Form::openForm('log_table', 'index.php?modname=conference&amp;op=log&amp;id='.$id)
			.$tb->getTable()
			.Form::closeForm()
			.'<br/>'
			.getBackUi('index.php?modname=conference&amp;op=history', $lang->def('_BACK')), 'content');

	cout('</div>', 'content');
}

// =================================================================== //
// conference dispatch
// =================================================================== //

function dispatchConference($op) {

	require_once(_base_.'/lib/lib.urlmanager.php');
	$url =& UrlManager::getInstance();
	$url->setStdQuery('modname=conference&op=list');

	if(isset($_POST['undo'])) $op = 'list';

	switch($op) {
		case 'list' : {
			conference_list($url);
		};break;
		case 'startnewconf' : {
			conference_startnewconf($url);
		};break;
		case 'modconf':
			conference_modconf($url);
		break;
		case 'delconf' : {
			conference_delconf();
		};break;

		case 'booking':
			booking();
		break;

		case 'modbooking':
			modBooking();
		break;

		case 'history':
			showHistory();
		break;

		case 'log':
			showLog();
		break;

		default : {
			conference_list($url);
		}
	}
}

?>