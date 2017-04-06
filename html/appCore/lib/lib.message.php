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

define("_MESSAGE_UNREADED", 0);
define("_MESSAGE_READED", 1);
define("_MESSAGE_MY", 2);
define("_MESSAGE_VALID", 0);
define("_OPERATION_SUCCESSFUL", 1);

// ----------------------------------------------------------------------------

class MessageModule {

	protected $db;
	protected $mvc_urls;

	public function __construct($mvc = false) {
		$this->db = DbConn::getInstance();
		$this->mvc_urls = (bool)$mvc;
	}


	// private functions
	private function decodePriority($prio) {

		switch($prio) {
			case 5 :
				$img_priority = "veryimportant.png";
				$text_priority = Lang::t('_VERYHIGH', 'message');
				$color_priority = 'danger'; // added 15/03/2016
				break;
			case 4 :
				$img_priority = "important.png";
				$text_priority = Lang::t('_HIGH', 'message');
				$color_priority = 'warning'; // added 15/03/2016
				break;
			case 3 :
				$img_priority = "notimportant.png";
				$text_priority = Lang::t('_NORMAL', 'message');
				$color_priority = 'success'; // added 15/03/2016
				break;
			case 2 :
				$img_priority = "lowmessage.png";
				$text_priority = Lang::t('_LOW', 'message');
				$color_priority = 'info'; // added 15/03/2016
				break;
			case 1 :
				$img_priority = "verylowmessage.png";
				$text_priority = Lang::t('_VERYLOW', 'message');
				$color_priority = 'info'; // added 15/03/2016
				break;
			default :
				$img_priority = "notimportant.png";
				$text_priority = Lang::t('_NORMAL', 'message');
				$color_priority = 'success'; // added 15/03/2016
				break;
		}

		return(array($img_priority,$text_priority,$color_priority));
	}

	//operations functions

	function saveMessageAttach($attach) {
		require_once(_base_.'/lib/lib.upload.php');

		$path = _PATH_MESSAGE;
		$file = '';
		sl_open_fileoperations();
		if(isset($attach['tmp_name']['attach']) && $attach['tmp_name']['attach'] != '') {

			$file = getLogUserId().'_'.mt_rand(0, 100).'_'.time().'_'.$attach['name']['attach'];
			if(!sl_upload($attach['tmp_name']['attach'], $path.$file)) {
				$error = 1;
				$file = '';
			}
		}
		sl_close_fileoperations();
		if(!$error) return $file;
		return false;
	}

	function deleteAttach($attach) {
		require_once(_base_.'/lib/lib.upload.php');

		$path = _PATH_MESSAGE;
		sl_open_fileoperations();
		$re = sl_unlink($path.$attach);
		sl_close_fileoperations();
		return $re;
	}

	function message() {
		//checkPerm('view');
		require_once(_base_.'/lib/lib.tab.php');
		require_once(_lms_.'/lib/lib.course.php');

		//YuiLib::load('tabview');
		$send_perm 	= true;//checkPerm('send_all', true) || checkPerm('send_upper', true);

		$output = "";

		$um =& UrlManager::getInstance("message");

		$active_tab = Get::req('active_tab', DOTY_STRING, 'inbox');
		if ($active_tab != 'inbox' && $active_tab != 'outbox') $active_tab = 'inbox';

		$form_url = $this->mvc_urls	? "index.php?r=message/show" : $um->getUrl();
		//$output .= Form::openForm('tab_advice', $form_url);

		$course_man = new Man_Course();
		$all_value = array(0 => Lang::t('_ALL_COURSES'));
		$all_courses = $course_man->getUserCourses( getLogUserId() );
		$all_value = $all_value + $all_courses;


		$_filter_inbox = Get::req('msg_course_filter_inbox', DOTY_INT, 0);
		$_filter_outbox = Get::req('msg_course_filter_outbox', DOTY_INT, 0);
	  if ($_filter_inbox=='') {
      if (isset($_SESSION['idCourse']))
				$_filter_inbox=$_SESSION['idCourse'];
      else
				$_filter_inbox=0;
    }
		if ($_filter_outbox=='') {
      if (isset($_SESSION['idCourse']))
				$_filter_outbox=$_SESSION['idCourse'];
      else
				$_filter_outbox=0;
    }

		if (count($all_value) > 0) {
			$form_filter_inbox =
				Form::getLineDropdown(	'form_line_l pull-right',
										// 'label_padded',
										// Lang::t('_FILTER'),
										'',
										'',
										'dropdown_nowh',
										'msg_course_filter_inbox',
										'msg_course_filter_inbox',
										$all_value,
										$_filter_inbox,
										' onchange="form.submit();"',
										'',//' '.Form::getButton( 'refresh_msg_filter', 'refresh_msg_filter', Lang::t('_REFRESH'), 'button_nowh' ),
										'')
				.Form::getHidden('msg_course_filter_outbox', 'msg_course_filter_outbox', $_filter_outbox)
				.Form::getBreakRow();
			$form_filter_outbox =
				Form::getLineDropdown(	'form_line_l pull-right',
										// 'label_padded',
										// Lang::t('_FILTER'),
										'',
										'',
										'dropdown_nowh',
										'msg_course_filter_outbox',
										'msg_course_filter_outbox',
										$all_value,
										$_filter_outbox,
										' onchange="form.submit();"',
										'',//' '.Form::getButton( 'refresh_msg_filter', 'refresh_msg_filter', Lang::t('_REFRESH'), 'button_nowh' ),
										'')
				.Form::getHidden('msg_course_filter_inbox', 'msg_course_filter_inbox', $_filter_inbox)
				.Form::getBreakRow();
		}
		else {
			$form_filter_inbox = $form_filter_outbox =
				Form::getHidden("msg_course_filter_outbox", "msg_course_filter_outbox", 0)
				.Form::getHidden("msg_course_filter_inbox", "msg_course_filter_inbox", 0);
		}

		// $output .= '
		// 	<div class="std_block">
		// 		<div id="tab_message" class="yui-navset">
		// 			<ul class="yui-nav">
		// 					<li'.($active_tab == 'inbox' ? ' class="selected"' : '').'>
		// 						<a href="#tab_inbox"><em>'.Lang::t('_INBOX', 'message').'</em></a>
		// 					</li>
		// 					<li'.($active_tab == 'outbox' ? ' class="selected"' : '').'>
		// 						<a href="#tab_outbox"><em>'.Lang::t('_OUTBOX', 'message').'</em></a>
		// 					</li>
		// 			</ul>
		// 			<div class="yui-content">
		// 					<div id="tab_inbox">
		// 						'.Form::openForm('inbox_tab_advice', $form_url)
		// 						.Form::getHidden('active_tab', 'active_tab', 'inbox')
		// 						.$form_filter_inbox
		// 						.$this->inbox($all_courses, true)
		// 						.Form::closeForm().'
		// 					</div>
		// 					<div id="tab_outbox">
		// 						'.Form::openForm('outbox_tab_advice', $form_url)
		// 						.Form::getHidden('active_tab', 'active_tab', 'outbox')
		// 						.$form_filter_outbox
		// 						.$this->outbox($all_courses, true)
		// 						.Form::closeForm().'
		// 					</div>
		// 			</div>
		// 		</div>
		// 		<script type="text/javascript">
		// 				YAHOO.util.Event.onDOMReady(function() {
		// 					var tabview = new YAHOO.widget.TabView("tab_message");
		// 				});
		// 		</script>
		// 	</div>';

		$output .= '
			<div class="std_block">
				<div id="tab_message">
					<ul class="nav nav-tabs">
							<li'.($active_tab == 'inbox' ? ' class="active"' : '').'>
								<a data-toggle="tab" href="#inbox-messages"><em>'.Lang::t('_INBOX', 'message').'</em></a>
							</li>
							<li'.($active_tab == 'outbox' ? ' class="active"' : '').'>
								<a data-toggle="tab" href="#outbox-messages"><em>'.Lang::t('_OUTBOX', 'message').'</em></a>
							</li>
					</ul>
					<div class="tab-content">
							<div class="tab-pane'.($active_tab == 'inbox' ? ' active' : '').'" id="inbox-messages">
								'.Form::openForm('inbox_tab_advice', $form_url)
								.$form_filter_inbox
								.$this->inbox($all_courses, true) // mod: passing filter
								.Form::closeForm().'
							</div>
							<div class="tab-pane'.($active_tab == 'outbox' ? ' active' : '').'" id="outbox-messages">
								'.Form::openForm('outbox_tab_advice', $form_url)
								.$form_filter_outbox
								.$this->outbox($all_courses, true) // mod: passing filter
								.Form::closeForm().'
							</div>
					</div>
				</div>
			</div>';

		cout($output, 'content');
	}

	// mod 15/03/2016: passing filter to be rendered in table actions section
	function inbox(&$course_list, $noprint = false, $filter_form = false) {
		require_once(_base_.'/lib/lib.table.php');

		$lang 		=& DoceboLanguage::createInstance('message', 'lms');
		$send_perm 	= true;//checkPerm('send_all', true) || checkPerm('send_upper', true);
		$out		= $GLOBALS['page'];
		$out->setWorkingZone('content');
		$um =& UrlManager::getInstance("message");

		// $tb = new Table(Get::sett('visuItem', 25), '', '', 'messages-recv');
		$tb = new Table(Get::sett('visuItem', 25), '', '', 'messages-recv');
		$tb->initNavBar('ini', 'button');
		$ini = $tb->getSelectedElement();
		$acl_man =& Docebo::user()->getAclManager();

		$query = "
		SELECT m.idMessage, m.idCourse, m.sender, m.posted, m.attach, m.title, m.priority, user.read
		FROM %adm_message AS m JOIN
			%adm_message_user AS user
		WHERE m.idMessage = user.idMessage AND
			m.sender <> '".getLogUserId()."' AND
			user.idUser = '".getLogUserId()."' AND
			user.deleted = '"._MESSAGE_VALID."'";
		$_filter = Get::req('msg_course_filter_inbox', DOTY_INT, 0);

		if(($_filter != '') && ($_filter != 0))
		{
			$res = $acl_man->getGroupsIdstFromBasePath('/lms/course/'.$_filter.'/subscribed/');
			$res = $acl_man->getAllUsersFromIdst($res);
			$query .=	" AND user.idMessage IN ( SELECT idMessage FROM %adm_message_user as user WHERE user.idUser IN (".implode(",", $res).") AND user.idUser <> ".(int)Docebo::user()->getIdSt().") ";
		}
		else
		{
			if (isset($_SESSION['idCourse']) && $_filter=='')
			{
				$_filter = $_SESSION['idCourse'];
				$res = $acl_man->getGroupsIdstFromBasePath('/lms/course/'.$_filter.'/subscribed/');
				$res = $acl_man->getAllUsersFromIdst($res);
				$query .=	" AND user.idMessage IN ( SELECT idMessage FROM %adm_message_user as user WHERE user.idUser IN (".implode(",", $res).") AND user.idUser <> ".(int)Docebo::user()->getIdSt().") ";
			}
		}

		$query .= "ORDER BY ";
		if(isset($_POST['ord'])) {
			switch($_POST['ord']) {
				case "pry" : $query .= "m.priority DESC,"; break;
				case "sen" : $query .= "m.sender,"; break;
				case "tit" : $query .= "m.title,"; break;
				case "ath" : $query .= "m.attach DESC,"; break;
				case "rid" : $query .= "user.read,"; break;
			}
		}
		$query .= "m.posted DESC LIMIT $ini,".Get::sett('visuItem', 25);
		$re_message = $this->db->query($query);

		// -----------------------------------------------------
		$query = "
		SELECT COUNT(*)
		FROM %adm_message AS m JOIN
			%adm_message_user AS user
		WHERE m.idMessage = user.idMessage AND
			user.idUser = '".getLogUserId()."' AND
			m.sender <> '".getLogUserId()."'";
		if(($_filter != '') && ($_filter != '0')) {
			$query .= " AND m.idCourse = '".$_filter."'";
		}

		list($tot_message) = $this->db->fetch_row($this->db->query($query));

		// $cont_h = array(
		// 	'<img src="'.getPathImage('fw').'standard/notimportant.png" title="'.Lang::t('_PRIORITY', 'message').'" alt="'.Lang::t('_PRIORITY', 'message').'" />',
		// 	'<img src="'.getPathImage('fw').'standard/msg_unread.png" title="'.Lang::t('_UNREAD', 'message').'" alt="'.Lang::t('_UNREAD', 'message').'" />',
		// 	Lang::t('_TITLE', 'message'),
		// 	'<img src="'.getPathImage().'standard/attach.png" title="'.Lang::t('_ATTACH_TITLE', 'message').'" alt="'.Lang::t('_ATTACHMENT', 'message').'" />',
		// 	Lang::t('_SENDER', 'message'),
		// 	Lang::t('_DATE', 'message'),
		// 	'<span class="ico-sprite subs_del"><span>'.Lang::t('_DEL', 'standard').'</span></span>'
		// );

		$cont_h = array(
			'<span class="glyphicon glyphicon-exclamation-sign" title="'.Lang::t('_PRIORITY', 'message').'"></span>',
			'<span class="glyphicon glyphicon-folder-close" title="'.Lang::t('_UNREAD', 'message').'"></span>',
			Lang::t('_TITLE', 'message'),
			'<span class="glyphicon glyphicon-paperclip" title="'.Lang::t('_ATTACH_TITLE', 'message').'"></span>',
			Lang::t('_SENDER', 'message'),
			Lang::t('_DATE', 'message'),
			'<span>'.Lang::t('_DEL', 'standard').'</span>'
		);

		$type_h = array(
			'image hidden-xs',
			'image hidden-xs',
			'col-xs-5',
			'image hidden-xs',
			'col-xs-3',
			'col-xs-3 message_posted',
			'col-xs-1 image'
		);

		$tb->setColsStyle($type_h);
		$tb->addHead($cont_h);

		while( list($id_mess, $id_course, $sender, $posted, $attach, $title, $priority, $read) = $this->db->fetch_row($re_message) ) {

			$sender_info = $acl_man->getUser($sender, false);
			$author = ( $sender_info[ACL_INFO_LASTNAME].$sender_info[ACL_INFO_FIRSTNAME] == '' ?
						$acl_man->relativeId($sender_info[ACL_INFO_USERID]) :
						$sender_info[ACL_INFO_LASTNAME].' '.$sender_info[ACL_INFO_FIRSTNAME] );

			// list($img_priority,$text_priority) = self::decodePriority($priority);

			list($img_priority,$text_priority,$color_priority) = self::decodePriority($priority);

			$cont = array();
			// $cont[] = '<img src="'.getPathImage().'standard/'.$img_priority.'" '
			// 	.'title="'.$text_priority.'" '
			// 	.'alt="'.$text_priority.'" />';

			$cont[] = '<span class="glyphicon glyphicon-exclamation-sign text-'.$color_priority.'" title="'.$text_priority.'"></span>';

			// if($read == _MESSAGE_READED) {
			// 	$cont[] = '<img src="'.getPathImage('fw').'standard/msg_read.png" title="'.Lang::t('_TITLE_READ').'" '
			// 					.'alt="'.Lang::t('_READ').'" />';
			// } else  { //($read == _MESSAGE_UNREADED)
			// 	$cont[] = '<img src="'.getPathImage('fw').'standard/msg_unread.png" title="'.Lang::t('_UNREAD').'" '
			// 					.'alt="'.Lang::t('_UNREAD').'" />';
			// }

			if($read == _MESSAGE_READED) {
				$cont[] = '<span class="glyphicon glyphicon-folder-open" title="'.Lang::t('_TITLE_READ').'"></span>';
			} else  { //($read == _MESSAGE_UNREADED)
				$cont[] = '<span class="glyphicon glyphicon-folder-close" title="'.Lang::t('_UNREAD').'"></span>';
			}

			$read_url = $this->mvc_urls
				? "index.php?r=message/read&from=out&id_message=".$id_mess
				: $um->getUrl("op=readmessage&from=out&id_message=".$id_mess);
			$cont[] = '<a id="_title_inbox_'.$id_mess.'" href="'.$read_url.'" '
							.'title="'.Lang::t('_READ_MESS').'">'.$title.'</a>';

			if($attach != '') {
				$cont[] = '<img src="'.getPathImage('fw').mimeDetect($attach).'" alt="'.Lang::t('_MIME').'" />';
			} else {
				$cont[] = '&nbsp;';
			}
			$cont[] = $author.' '
				.( ((!isset($_POST['msg_course_filter']) || ($_POST['msg_course_filter'] == false)) && $id_course != 0)
						? '['.$course_list[$id_course].']'
						: '' );
			$cont[] = Format::date($posted);

			//$cont[] = '<a href="'.$um->getUrl("op=delmessage&from=out&id_message=".$id_mess).'">'
			$add_filter = '';
			if (($_filter != '') && ($_filter != '0')) $add_filter = "&msg_course_filter=".$_filter;
			/*$cont[] = '<a href="'.$um->getUrl("op=delmessage&from=out&id_message=".$id_mess.$add_filter)
            .'">'
						.'<img src="'.getPathImage().'/standard/rem.gif"  '
							.'title="'.Lang::t('_DEL').' : '.strip_tags($title).'" '
							.'alt="'.Lang::t('_DEL').' : '.strip_tags($title).'" /></a>';*/
			$del_url = $this->mvc_urls
				? "ajax.server.php?r=message/delete_message&id=".$id_mess
				: $um->getUrl("op=delmessage&from=out&id_message=".$id_mess.$add_filter);
			// $cont[] = '<a id="_del_inbox_'.$id_mess.'" href="'.$del_url.'" class="ico-sprite subs_del" title=""><span></span></a>';
			$cont[] = '<a id="_del_inbox_'.$id_mess.'" href="'.$del_url.'" class="btn btn-default" title=""><span class="glyphicon glyphicon-remove"></span></a>';
			$tb->addBody( $cont );
		}
		//if(checkPerm('send_all', true) || checkPerm('send_upper', true)) {
			$add_url = $this->mvc_urls
				? "index.php?r=message/add&from=out"
				: $um->getUrl("op=addmessage&from=out");

			// $tb->addActionAdd('<a class="ico-wt-sprite subs_add" href="'.$add_url.'" title="'.Lang::t('_SEND').'">'
			// 	.'<span>'.Lang::t('_SEND').'</span></a>');

			$tb->addActionAdd('<a class="btn btn-default" href="'.$add_url.'" title="'.Lang::t('_SEND').'">
													<span class="glyphicon glyphicon-plus-sign"></span>&nbsp;
													<span>'.Lang::t('_SEND').'</span>
												</a>');

			if ($filter_form) {
				$tb->addActionAdd($filter_form);
			}
		//}

		$output = "";
		$output .= 	'<div class="std_block">';

		if(isset($_GET['result'])) {
			switch($_GET['result']) {
				case "ok": $output .= getResultUi(Lang::t('_OPERATION_SUCCESSFUL'));break;
				case "ok_del": $output .= getResultUi(Lang::t('_OPERATION_SUCCESSFUL'));break;
				case "err": $output .= getErrorUi(Lang::t('_SEND_FAIL'));break;
			}
		}
		$output .= $tb->getTable().$tb->getNavBar($ini, $tot_message).'</div>';

		if ($noprint)
			return $output;
		else
			cout($output, 'content');
	}

	// mod 15/03/2016: passing filter to be rendered in table actions section
	function outbox(&$course_list, $noprint = false, $filter_form = false) {
		require_once(_base_.'/lib/lib.table.php');

		//if(!checkPerm('send_all', true) && !checkPerm('send_upper', true)) die("You can't access");

		$lang 		=& DoceboLanguage::createInstance('message', 'lms');
		$out		= $GLOBALS['page'];
		$out->setWorkingZone('content');
		$um =& UrlManager::getInstance("message");
		$acl_man 	=& Docebo::user()->getAclManager();
		$tb = new Table(Get::sett('visuItem', 25), '', '', 'messages-sent');
		$tb->initNavBar('iniout', 'button');
		$ini = $tb->getSelectedElement('iniout');
		$acl_man =& Docebo::user()->getAclManager();


		$query = "
		SELECT m.idMessage, m.posted, m.attach, m.title, m.priority
		FROM %adm_message AS m JOIN
			%adm_message_user AS user
		WHERE m.idMessage = user.idMessage AND
			user.idUser = '".getLogUserId()."' AND
			m.sender = '".getLogUserId()."' AND
			user.deleted = '"._MESSAGE_VALID."'";
		/*if(isset($_POST['msg_course_filter']) && ($_POST['msg_course_filter'] != false)) {
			$query .= " AND m.idCourse = '".$_POST['msg_course_filter']."'";
		}*/
		$_filter = Get::req('msg_course_filter_outbox', DOTY_INT, 0);

		if(($_filter != '') && ($_filter != 0))
		{
			$res = $acl_man->getGroupsIdstFromBasePath('/lms/course/'.$_filter.'/subscribed/');
			$res = $acl_man->getAllUsersFromIdst($res);
			$query .=	" AND user.idMessage IN ( SELECT idMessage FROM %adm_message_user as user WHERE user.idUser IN (".implode(",", $res).") AND user.idUser <> ".(int)Docebo::user()->getIdSt().") ";
		}
		else
		{
			if (isset($_SESSION['idCourse']) && $_filter=='')
			{
				$_filter = $_SESSION['idCourse'];
				$res = $acl_man->getGroupsIdstFromBasePath('/lms/course/'.$_filter.'/subscribed/');
				$res = $acl_man->getAllUsersFromIdst($res);
				$query .=	" AND user.idMessage IN ( SELECT idMessage FROM %adm_message_user as user WHERE user.idUser IN (".implode(",", $res).") AND user.idUser <> ".(int)Docebo::user()->getIdSt().") ";
			}
		}
		$query .= "	ORDER BY ";
		if(isset($_POST['ord'])) {
			switch($_POST['ord']) {
				case "pry" : $query .= "m.priority DESC,"; break;
				case "sen" : $query .= "m.sender,"; break;
				case "tit" : $query .= "m.title,"; break;
				case "ath" : $query .= "m.attach DESC,"; break;
			}
		}
		$query .= "m.posted DESC LIMIT $ini,".Get::sett('visuItem', 25);
		$re_message = $this->db->query($query);

		$query = "
		SELECT COUNT(*)
		FROM %adm_message AS m JOIN
			%adm_message_user AS user
		WHERE m.idMessage = user.idMessage AND
			user.idUser = '".getLogUserId()."' AND
			m.sender = '".getLogUserId()."' AND
			user.deleted = '"._MESSAGE_VALID."'";
		if(($_filter != '') && ($_filter != false)) {
			$query .= " AND m.idCourse = '".$_filter."'";
		}
		list($tot_message) = $this->db->fetch_row($this->db->query($query));

		// $cont_h = array(
		// 	'<img src="'.getPathImage().'standard/notimportant.png" title="'.Lang::t('_PRIORITY', 'message').'" alt="'.Lang::t('_PRIORITY', 'message').'" />',
		// 	Lang::t('_TITLE'),
		// 	'<img src="'.getPathImage().'standard/attach.png" title="'.Lang::t('_ATTACH_TITLE').'" alt="'.Lang::t('_ATTACHMENT').'" />',
		// 	Lang::t('_DATE'),
		// 	Lang::t('_RECIPIENTS'),
		// 	'<span class="ico-sprite subs_del"><span>'.Lang::t('_DEL', 'standard').'</span></span>'
		// );

		$cont_h = array(
			'<span class="glyphicon glyphicon-exclamation-sign" title="'.Lang::t('_PRIORITY', 'message').'"></span>',
			Lang::t('_TITLE', 'message'),
			'<span class="glyphicon glyphicon-paperclip" title="'.Lang::t('_ATTACH_TITLE', 'message').'"></span>',
			Lang::t('_DATE', 'message'),
			Lang::t('_RECIPIENTS', 'message'),
			'<span>'.Lang::t('_DEL', 'standard').'</span>'
		);

		$type_h = array(
			'image hidden-xs',
			'col-xs-5',
			'image hidden-xs',
			'col-xs-3 message_posted',
			'col-xs-3 message_posted',
			'col-xs-1 image');

		$tb->setColsStyle($type_h);
		$tb->addHead($cont_h);

		while( list($id_mess, $posted, $attach, $title, $priority) = $this->db->fetch_row($re_message) ) {

			list($img_priority,$text_priority,$color_priority) = self::decodePriority($priority);

			$cont = array();
			// $cont[] = '<img src="'.getPathImage().'standard/'.$img_priority.'" '
			// 	.'title="'.$text_priority.'" '
			// 	.'alt="'.$text_priority.'" />';

			$cont[] = '<span class="glyphicon glyphicon-exclamation-sign text-'.$color_priority.'" title="'.$text_priority.'"></span>';

			$read_url = $this->mvc_urls
				? "index.php?r=message/read&id_message=".$id_mess
				: $um->getUrl("op=readmessage&id_message=".$id_mess);
			$cont[] = '<a id="_title_outbox_'.$id_mess.'" href="'.$read_url.'" '
							.'title="'.Lang::t('_READ_MESS').'">'.$title.'</a>';			

			if($attach != '') {
				$cont[] = '<img src="'.getPathImage('fw').mimeDetect($attach).'" alt="'.Lang::t('_MIME').'" />';
			} else {
				$cont[] = '&nbsp;';
			}
			$cont[] = Format::date($posted);

			$sql_receiver = "
				SELECT user.idUser
				FROM %adm_message_user AS user
				WHERE user.idMessage = '".$id_mess."' AND
					user.idUser != '".getLogUserId()."'";

			$result_receiver = $this->db->query($sql_receiver);
			$counter_receiver = 0;
			$cont_temp = "";
			while ($receiver = $this->db->fetch_array($result_receiver))
			{
				if ($counter_receiver == 0)
				{
					$message_user = $acl_man->getUser($receiver[0], false);
					$username =$acl_man->relativeId($message_user[ACL_INFO_USERID]);
					$cont_temp = $username;
				}
				else
				{
					$message_user = $acl_man->getUser($receiver[0], false);
					$username =$acl_man->relativeId($message_user[ACL_INFO_USERID]);
					$cont_temp .= ", ".$username;
				}
				$counter_receiver++;
			}
			$cont[] = $cont_temp;

			//$cont[] = '<a href="'.$um->getUrl("op=delmessage&id_message=".$id_mess.'&out=out').'">'
			$add_filter = '';
			if (($_filter != '') && ($_filter != false)) $add_filter = "&msg_course_filter=".$_filter;
			/*$cont[] = '<a href="'.$um->getUrl("op=delmessage&id_message=".$id_mess.'&out=out'.$add_filter).'">'
						.'<img src="'.getPathImage().'/standard/rem.gif"  '
							.'title="'.Lang::t('_DEL').' : '.strip_tags($title).'" '
							.'alt="'.Lang::t('_DEL').' : '.strip_tags($title).'" /></a>';*/
			$del_url = $this->mvc_urls
				? "ajax.server.php?r=message/delete_message&id=".$id_mess
				: $um->getUrl("op=delmessage&id_message=".$id_mess.'&out=out'.$add_filter);
			// $cont[] = '<a id="_del_outbox_'.$id_mess.'" href="'.$del_url.'" class="ico-sprite subs_del" title=""><span></span></a>';
			$cont[] = '<a id="_del_outbox_'.$id_mess.'" href="'.$del_url.'" class="btn btn-default" title=""><span class="glyphicon glyphicon-remove"></span></a>';
			$tb->addBody( $cont );
		}
		//if(checkPerm('send_all', true) || checkPerm('send_upper', true)) {
			$add_url = $this->mvc_urls
				? "index.php?r=message/add"
				: $um->getUrl("op=addmessage");
			// $tb->addActionAdd('<a class="ico-wt-sprite subs_add" href="'.$add_url.'" title="'.Lang::t('_SEND').'">'
			// 	.'<span>'.Lang::t('_SEND').'</span></a>');

			$tb->addActionAdd('<a class="btn btn-default" href="'.$add_url.'" title="'.Lang::t('_SEND').'">
													<span class="glyphicon glyphicon-plus-sign"></span>&nbsp;
													<span>'.Lang::t('_SEND').'</span>
												</a>');

			if ($filter_form) {
				$tb->addActionAdd($filter_form);
			}
		//}

		$output = "";
		$output .= '<div class="std_block">';

		if(isset($_GET['result'])) {
			switch($_GET['result']) {
				case "ok": $output .= getResultUi(Lang::t('_OPERATION_SUCCESSFUL')); break;
				case "ok_del": $output .= getResultUi(Lang::t('_OPERATION_SUCCESSFUL')); break;
				case "err": $output .= getErrorUi(Lang::t('_SEND_FAIL')); break;
			}
		}

		$output .=
			Form::getHidden('active_tab','active_tab', 'outbox')
			.$tb->getTable()
			.$tb->getNavBar($ini, $tot_message)
			.'</div>';

		if ($noprint)
			return $output;
		else
			cout($output, 'content');
	}

	function addmessage() {
		$send_all 		=true;// checkPerm('send_all', true);
		$send_upper 	=true;// checkPerm('send_upper', true);
		if(!$send_all && !$send_upper) die("You can't access");

		require_once(_base_.'/lib/lib.userselector.php');

		require_once(_lms_.'/lib/lib.course.php');

		$lang 		=& DoceboLanguage::createInstance('message', 'lms');
		$out		= $GLOBALS['page'];
		$out->setWorkingZone('content');
		$from = importVar('out');
		$um =& UrlManager::getInstance("message");

		$aclManager 	= new DoceboACLManager();
		$user_select 	= new UserSelector();

		$user_select->show_user_selector = TRUE;
		$user_select->show_group_selector = FALSE;
		$user_select->show_orgchart_selector = FALSE;
		$user_select->show_fncrole_selector = FALSE;

		$user_select->nFields = 0;

		if(isset($_POST['message']['recipients'])) {

			$recipients = Util::unserialize(urldecode($_POST['message']['recipients']));
			$user_select->resetSelection($recipients);
		}

		$me = array(getLogUserId());

		$course_man = new Man_Course();
		$all_value = array(0 => Lang::t('_ALL_COURSES'));
		$all_courses = $course_man->getUserCourses( getLogUserId() );
		$all_value = $all_value + $all_courses;

		if (count($all_value) > 0) {
			$drop = Form::getLineDropdown(	'form_line_right',
										'label_padded',
										Lang::t('_FILTER_MESSAGE_FOR'),
										'dropdown_nowh',
										'msg_course_filter',
										'msg_course_filter',
										$all_value,
										( isset($_POST['msg_course_filter'])
											? $_POST['msg_course_filter']
											: ( isset($_SESSION['idCourse']) ? $_SESSION['idCourse'] : 0 ) ),
										'',
										' '.Form::getButton( 'refresh_msg_filter', 'refresh_msg_filter', Lang::t('_REFRESH'), 'button_nowh' ),
										'');
			$drop .= "
				<script type=\"text/javascript\"><!--
					var hide_refresh = document.getElementById('refresh_msg_filter');
					hide_refresh.style.display = 'none';
					var option_elem = document.getElementById('msg_course_filter');
					option_elem.onchange = function() {
						var hide_refresh = document.getElementById('refresh_msg_filter');
						hide_refresh.click();
					}
				--></script>";
			$user_select->addFormInfo($drop);
		}
		else {
			$user_select->addFormInfo(Form::getHidden("msg_course_filter", "msg_course_filter", 0));
		}

		$user_select->setUserFilter('exclude', $me);
		if(isset($_POST['msg_course_filter'])) $filter = $_POST['msg_course_filter'];
		elseif(isset($_GET['set_course_filter'])) $filter = $_GET['set_course_filter'];
		else $filter = 0;

		$_SESSION['message_filter'] = $filter;
		$user_select->learning_filter = 'message';

		//$user_select->requested_tab = PEOPLEVIEW_TAB;
		$id_forward=importVar('id_forward',true,0);

		$title_url = $this->mvc_urls
			? "index.php?r=message/show".( $from == 'out' ? '&active_tab=outbox' : '' )
			: $um->getUrl(( $from == 'out' ? '&active_tab=outbox' : '' ));

		$user_select->setPageTitle(
			$this->messageGetTitleArea(array($title_url => Lang::t('_MESSAGES'),
			Lang::t('_SEND') ),
			'forum'));

		$load_url = $this->mvc_urls
			? 'index.php?r=message/add&id_forward='.$id_forward.''.( $from == 'out' ? '&from=out' : '' )
			: $um->getUrl('op=addmessage&id_forward='.$id_forward.''.( $from == 'out' ? '&from=out' : '' ));
		$user_select->loadSelector($load_url,
				false,
				Lang::t('_RECIPIENTS'),
				true);

	}

	function writemessage() {
		$send_all 		=true;// checkPerm('send_all', true);
		$send_upper 	=true;// checkPerm('send_upper', true);
		if(!$send_all && !$send_upper) die("You can't access");

		require_once(_base_.'/lib/lib.userselector.php');

		$out		= $GLOBALS['page'];
		$out->setWorkingZone('content');
		$from 		= importVar('out');
		$acl_man 	=& Docebo::user()->getAclManager();
		$um =& UrlManager::getInstance("message");

		if(!isset($_POST['message']['recipients'])) {

			if(isset($_GET['reply_recipients'])) {
				$user_selected = unserialize(stripslashes(urldecode($_GET['reply_recipients'])));
				$recipients = urlencode(Util::serialize($user_selected));
			} else {
				$user_select 	= new UserSelector();
				$user_selected = $user_select->getSelection($_POST);
				$recipients = urlencode(Util::serialize($user_selected));
			}
		} else {
			$user_selected = Util::unserialize(urldecode($_POST['message']['recipients']));
			$recipients = urlencode($_POST['message']['recipients']);
		}

		$title_url = $this->mvc_urls
			? "index.php?r=message/show".( $from == 'out' ? '&active_tab=outbox' : '' )
			: $um->getUrl(( $from == 'out' ? '&active_tab=outbox' : '' ));

		$output = "";
		$output .=
			$this->messageGetTitleArea(array($title_url => Lang::t('_MESSAGES'),
				Lang::t('_SEND')) ,'message')
			.'<div class="std_block">';

		if(isset($_POST['send'])) {

			if($_POST['message']['subject'] == '') {
				$output .= getErrorUi(Lang::t('_MUST_INS_SUBJECT'));
			} else {
				// send message
				$attach = '';
				if($_FILES['message']['tmp_name']['attach'] != '') {
					$attach = $this->saveMessageAttach($_FILES['message']);
				}

				$query_mess = "
				INSERT INTO %adm_message
				( idCourse, sender, posted, title, textof, attach, priority ) VALUES
				(
					'".$_POST['msg_course_filter']."',
					'".getLogUserId()."',
					'".date("Y-m-d H:i:s")."',
					'".$_POST['message']['subject']."',
					'".$_POST['message_textof']."',
					'".addslashes($attach)."',
					'".$_POST['message']['priority']."'
				)";

				if(!$this->db->query($query_mess)) {

					if($attach) deleteAttach($attach);

					$jump_url = $this->mvc_urls
						 ? "index.php?r=message/show&result=err"
						 : $um->getUrl('result=err');
					Util::jump_to($jump_url);
				}
				list($id_message) = $this->db->fetch_row($this->db->query("SELECT LAST_INSERT_ID()"));

				if(!in_array(getLogUserId(), $user_selected)) $user_selected[] = getLogUserId();
				$send_to_idst =& $acl_man->getAllUsersFromIdst($user_selected);

				$re = true;
				$recip_alert = array();
				if(is_array($send_to_idst)) {

					$logged_user =  getLogUserId();
					while(list(, $id_recipient) = each($send_to_idst)) {

						$query_recipients = "
						INSERT INTO %adm_message_user
						( idMessage, idUser, idCourse, `read` ) VALUES
						(
							'".$id_message."',
							'".$id_recipient."',
							'".$_POST['msg_course_filter']."',
							'".( $id_recipient == $logged_user ? _MESSAGE_MY : _MESSAGE_UNREADED  )."'
						) ";
						$re_single = $this->db->query($query_recipients);
						if($re_single && $id_recipient != $logged_user) {
							$recip_alert[] = $id_recipient;
						}
						$re &= $re_single;
					}
					if(!empty($recip_alert)) {

						require_once(_lms_.'/lib/lib.course.php');
						require_once(_base_.'/lib/lib.eventmanager.php');

						$is_course = false;
						if ((isset($_SESSION['idCourse'])) && (isset($GLOBALS['course_descriptor']))) {
							$course_name = $GLOBALS['course_descriptor']->getValue('name');
							$is_course = true;
						} elseif ($_POST['msg_course_filter'] != 0  && is_numeric($_POST['msg_course_filter'])) {

                            $idCourse = $_POST['msg_course_filter'];

                            $query_course = "SELECT name FROM %lms_course WHERE idCourse = ".$idCourse;

                            $course_result = $this->db->fetch_row($this->db->query($query_course));
							list($name) = $course_result;
							$course_name = $name;
							$is_course = true;
						} else {
							 $course_name = '';
						}

						// message to user that is odified
						$msg_composer = new EventMessageComposer();

						$msg_composer->setSubjectLangText('email', '_YOU_RECIVE_MSG_SUBJECT', false);
						if ( !$is_course ) {
							$msg_composer->setBodyLangText('email', '_YOU_RECIVE_MSG_TEXT', array(	'[url]' => _MESSAGE_PL_URL,
																									'[course]' => $course_name,
																									'[from]' => Docebo::user()->getUsername() ) );

							$msg_composer->setBodyLangText('sms', '_YOU_RECIVE_MSG_TEXT_SMS', array( '[url]' => _MESSAGE_PL_URL,
																									 '[course]' => $course_name,
																									 '[from]' => Docebo::user()->getUsername() ) );
						} else {
							$msg_composer->setBodyLangText('email', '_YOU_RECIVE_MSG_TEXT_COURSE', array(	'[url]' => _MESSAGE_PL_URL,
																											'[course]' => $course_name,
																											'[from]' => Docebo::user()->getUsername() ) );

							$msg_composer->setBodyLangText('sms', '_YOU_RECIVE_MSG_TEXT_SMS_COURSE', array(	'[url]' => _MESSAGE_PL_URL,
																											'[course]' => $course_name,
																											'[from]' => Docebo::user()->getUsername() ) );
						}

						createNewAlert(	'MsgNewReceived', 'directory', 'moderate', '1', 'User group subscription to moderate',
									$recip_alert, $msg_composer );

					}
				}
				$jump_url = $this->mvc_urls
						 ? "index.php?r=message/show&result=".( $re ? 'ok' : 'err' )
						 : $um->getUrl('result='.( $re ? 'ok' : 'err' ));
				Util::jump_to($jump_url);
			}
		}
		$prio_arr = array(
			'5' => Lang::t('_VERYHIGH', 'message'),
			'4' => Lang::t('_HIGH', 'message'),
			'3' => Lang::t('_NORMAL', 'message'),
			'2' => Lang::t('_LOW', 'message'),
			'1' => Lang::t('_VERYLOW', 'message')
		);

		$first = true;
		$attach = '';

		if(!is_array($user_selected) || empty($user_selected)) {

			$write_url = $this->mvc_urls
				? 'index.php?r=message/write'
				: $um->getUrl('op=writemessage');
			$output .=
				'<span class="text_bold">'.Lang::t('_NO_RECIPIENTS_SELECTED').'</span>'
				.Form::openForm('message', $write_url, false, false, 'multipart/form-data')
				.Form::getHidden('out', 'out', $from)
				.Form::getHidden('msg_course_filter', 'msg_course_filter', $_POST['msg_course_filter'])
				.Form::getHidden('message_recipients', 'message[recipients]', $recipients)
				.Form::openButtonSpace()
				.Form::getButton('back_recipients', 'back_recipients', Lang::t('_BACK'))
				.Form::closeButtonSpace()
				.Form::closeForm();
			return;
		}

		$only_users =& $acl_man->getUsers($user_selected);
		$only_groups = $acl_man->getGroups($user_selected);

		$output .=
			'<span class="text_bold">'.Lang::t('_RECIPIENTS').'</span>'
			.'<div class="recipients">';


		if(is_array($only_groups) && !empty($only_groups)) {

			$output .= '<strong>';
			while(list(, $group_info) = each($only_groups)) {
				if($first) $first = false;
				else $attach = ', ';

				$groupid = substr($group_info[ACL_INFO_GROUPID], strrpos($group_info[ACL_INFO_GROUPID], '/')+1);
				$output .=  $attach.$groupid;

				// find user of group
				$members = $acl_man->getGroupAllUser($group_info[ACL_INFO_IDST]);
				$group_users =& $acl_man->getUsers($members);
				$output .= ' <span class="message_group_members">( ';
				$m_first = true;
				while(list(, $user_info) = each($group_users)) {
					if($m_first) $m_first = false;
					else $attach = ', ';
					$output .=  $attach
							.( $user_info[ACL_INFO_LASTNAME].$user_info[ACL_INFO_FIRSTNAME]
									? $user_info[ACL_INFO_LASTNAME].' '.$user_info[ACL_INFO_FIRSTNAME]
									: $acl_man->relativeId($user_info[ACL_INFO_USERID]) );
				}
				$output .= ' )</span> ';

			}
			$output .= '</strong>';
		}
		$acl_man->setContext('/');
		if(is_array($only_users) && !empty($only_users))
		while(list(, $user_info) = each($only_users)) {
			if($first) $first = false;
			else $attach = ', ';
			$output .=  $attach
					.( $user_info[ACL_INFO_LASTNAME].$user_info[ACL_INFO_FIRSTNAME]
							? $user_info[ACL_INFO_LASTNAME].' '.$user_info[ACL_INFO_FIRSTNAME]
							: $acl_man->relativeId($user_info[ACL_INFO_USERID]) );
		}
		$id_forward=importVar('id_forward',true,0);
		$sql_text = "SELECT message.textof, message.title FROM %adm_message AS message WHERE message.idMessage = '".$id_forward."'";
		$title = '';
		$text_message = '';
		if ($message_forward = $this->db->fetch_row($this->db->query($sql_text)))
		{
			list($text_message, $title) = $message_forward;
			$title = 'Reply: '.$title;
				$text_message = '<br /><br /><font color="#808080">-------<br /><br />'.$text_message.'</font>';
		}

		$write_url = $this->mvc_urls
			? 'index.php?r=message/write'
			: $um->getUrl('op=writemessage');

		$output .=
			'</div><br />'
			.Form::openForm('message', $write_url, false, false, 'multipart/form-data')
			.Form::getHidden('out', 'out', $from)
			.Form::getHidden('msg_course_filter', 'msg_course_filter', $_POST['msg_course_filter'])
			.Form::getHidden('message_recipients', 'message[recipients]', $recipients)

			.Form::getTextfield(Lang::t('_SUBJECT'), 'message_subject', 'message[subject]', 255,
				( isset($_POST['message']['subject']) ? $_POST['message']['subject'] : "$title" ) )

			.Form::getDropdown(Lang::t('_PRIORITY'), 'message_priority', 'message[priority]', $prio_arr,
				( isset($_POST['message']['priority']) ? $_POST['message']['priority'] : 3 ) )

			.Form::getTextarea(Lang::t('_TEXTOF'), 'message_textof', 'message_textof',
				( isset($_POST['message_textof']) ? $_POST['message_textof'] : "$text_message" ) )

			.Form::getFilefield(Lang::t('_ATTACHMENT'), 'message_attach', 'message[attach]', 255 )
			.Form::openButtonSpace()
			.Form::getButton('back_recipients', 'back_recipients', Lang::t('_BACK'))

			.Form::getButton('send', 'send', Lang::t('_SEND'))
			.Form::getButton('undo', 'undo', Lang::t('_UNDO'))
			.Form::closeButtonSpace()
			.Form::closeForm()
			.'</div>';

		cout($output, 'content');
	}

	function delmessage() {
		//checkPerm('view');

		$lang 		=& DoceboLanguage::createInstance('message', 'lms');
		$out		= $GLOBALS['page'];
		$out->setWorkingZone('content');
		$um =& UrlManager::getInstance("message");

		$from = importVar('out');

		if(isset($_GET['confirm'])) {

			$re = true;
			$del_query = "
			UPDATE %adm_message_user
			SET deleted = '"._OPERATION_SUCCESSFUL."'
			WHERE idUser='".getLogUserId()."' AND idMessage = '".(int)$_GET['id_message']."'";
			if(!$this->db->query($del_query)) {
				if ($from === 'out')
					Util::jump_to($um->getUrl('&active_tab=outbox&result=err'));
				Util::jump_to($um->getUrl('&active_tab=inbox&result=err'));
				//Util::jump_to($um->getUrl(( $from == 'out' ? '&active_tab=outbox' : '').'&result=err'));
			}

			$query = "
			SELECT idMessage
			FROM %adm_message_user
			WHERE idMessage = '".(int)$_GET['id_message']."'";
			if(!$this->db->num_rows($this->db->query($query))) {

				list($attach) = $this->db->fetch_row($this->db->query("
				SELECT attach
				FROM %adm_message
				WHERE idMessage = '".$_GET['id_message']."'"));
				if($attach != '' ) {

					if(!deleteAttach($attach)) {
						if ($from === 'out')
							Util::jump_to($um->getUrl('&active_tab=outbox&result=err'));
						Util::jump_to($um->getUrl('&active_tab=inbox&result=err'));
						//Util::jump_to($um->getUrl(( $from == 'out' ? '&active_tab=outbox' : '').'&result=err'));
					}
				}
				if(!$this->db->query("
				DELETE FROM %adm_message_user
				WHERE idMessage = '".$_GET['id_message']."'")) {
					if ($from === 'out')
						Util::jump_to($um->getUrl('&active_tab=outbox&result=err'));
					Util::jump_to($um->getUrl('&active_tab=inbox&result=err'));
					//Util::jump_to($um->getUrl(( $from == 'out' ? '&active_tab=outbox' : '').'&result=err'));
				}
				if(!$this->db->query("
				DELETE FROM %adm_message
				WHERE idMessage = '".$_GET['id_message']."'")) {
					if ($from === 'out')
						Util::jump_to($um->getUrl('&active_tab=outbox&result=err'));
					Util::jump_to($um->getUrl('&active_tab=inbox&result=err'));
					//Util::jump_to($um->getUrl(( $from == 'out' ? '&active_tab=outbox' : '').'&result=err'));
				}
			}

      $_filter = importVar('msg_course_filter');
			if (($_filter != '') && ($_filter != false)) { $add_filter = "&msg_course_filter=".$_filter; }
			else $add_filter = '';

			if ($from === 'out')
				Util::jump_to($um->getUrl('&active_tab=outbox&result=ok_del'.$add_filter));
			Util::jump_to($um->getUrl('&active_tab=inbox&result=ok_del'.$add_filter));
			//Util::jump_to($um->getUrl(( $from == 'out' ? '&active_tab=outbox' : '').'&result=ok_del'));
		} else {
			list($title) = $this->db->fetch_row($this->db->query("
			SELECT title
			FROM %adm_message
			WHERE idMessage = '".$_GET['id_message']."'"));

			$page_title = array(
				$um->getUrl(( $from == 'out' ? '&active_tab=outbox' : '' )) => Lang::t('_MESSAGES'),
				Lang::t('_DEL')
			);

			$_filter = importVar('msg_course_filter');
			$add_filter = '';
			if (($_filter != '') && ($_filter != false)) $add_filter = "&msg_course_filter=".$_filter;


			$output = "";
			$output .=
				$this->messageGetTitleArea($page_title, 'message')
				.'<div class="std_block">'
				.getDeleteUi(	Lang::t('_AREYOUSURE'),
								'<span>'.Lang::t('_TITLE').' : </span> "'.$title,
								true,
								$um->getUrl('op=delmessage&id_message='.$_GET['id_message']
									.( $from == 'out' ? '&out=out' : '').'&confirm=1'.$add_filter),
								$um->getUrl(( $from == 'out' ? '&active_tab=outbox' : ''))
							)
				.'</div>';

			cout($output, 'content');
		}
	}

	//-----------------------------------------------------------------//

	function readmessage() {
		//checkPerm('view');

		$out		= $GLOBALS['page'];
		$out->setWorkingZone('content');
		$um=& UrlManager::getInstance("message");

		$acl_man =& Docebo::user()->getAclManager();
		$from = importVar('out');

		// check the viewer rights
		$re_viewer = $this->db->query("
		SELECT *
		FROM %adm_message_user
		WHERE idMessage = '".$_GET['id_message']."' AND idUser = '".getLogUserId()."' ");
		if(!$this->db->num_rows($re_viewer)) {

			self::message();
			return;
		}

		$re_user = $this->db->query("
		UPDATE %adm_message_user AS user
		SET user.read = '"._MESSAGE_READED."'
		WHERE user.idMessage = '".$_GET['id_message']."' AND user.idUser = '".getLogUserId()."' AND user.read = '"._MESSAGE_UNREADED."' ");

		list($sender, $posted, $title, $textof, $attach, $priority) = $this->db->fetch_row($this->db->query("
		SELECT sender, posted, title, textof, attach, priority
		FROM %adm_message
		WHERE idMessage = '".$_GET['id_message']."'"));

		$sender_info = $acl_man->getUser($sender, false);

		$author = ( $sender_info[ACL_INFO_LASTNAME].$sender_info[ACL_INFO_FIRSTNAME] == '' ?
						$acl_man->relativeId($sender_info[ACL_INFO_USERID]) :
						$sender_info[ACL_INFO_LASTNAME].' '.$sender_info[ACL_INFO_FIRSTNAME] );

		$title_url = $this->mvc_urls
			? "index.php?r=message/show".( $from == 'out' ? '&active_tab=outbox' : '' )
			: $um->getUrl(( $from == 'out' ? '&active_tab=outbox' : '' ));
		$page_title = array(
			$title_url => Lang::t('_MESSAGES'),
			Lang::t('_READ').' : '.$title
		);

		$download_url = $this->mvc_urls
			? 'index.php?r=message/download&id_message='.$_GET['id_message']
			: $um->getUrl('op=download&id_message='.$_GET['id_message']);

		$output = "";
		$output .=
			$this->messageGetTitleArea($page_title, 'message')
			.'<div class="std_block">'


			.'<h2 class="message_title"><b>'.Lang::t('_TITLE').': </b>'.$title.'</h2>'
			.'<br/>'
			.'<p><b>'.Lang::t('_SENDER').' : </b>'.$author.'</p>'
			.'<p><b>'.Lang::t('_DATE').' : </b>'.Format::date($posted).'</p>'
			.'<br/>'
			.'<p><b>'.Lang::t('_TEXTOF').'</b></p>'
			.'<div>'.$textof.'</div>'
			.'<br />'
			.( $attach != ''
				? '<div class="message_attach"><span class="text_bold">'.Lang::t('_ATTACHMENT').' : </span>'
					.'<a href="'.$download_url.'">'
					.'<img src="'.getPathImage('fw').mimeDetect($attach).'" alt="'.Lang::t('_MIME').'" />'.preg_replace('/^\d*_\d*_\d*_(.*)/is', '$1', $attach).'</a></div>'
				: '' );
			$sender_arr[$sender_info[ACL_INFO_IDST]] = $sender_info[ACL_INFO_IDST];
			if ($sender == getLogUserId())
			{
				$reply_url = $this->mvc_urls
					? "index.php?r=message/add&id_forward=".$_GET['id_message'].""
					: $um->getUrl("op=addmessage&id_forward=".$_GET['id_message']."");
				$output .= '<p class="message_reply"><a href="'.$reply_url.'">'.Lang::t('_NEXT').'</a></p>';
			}
			else
			{
				$reply_url = $this->mvc_urls
					? 'index.php?r=message/write&reply_recipients='.urlencode(Util::serialize($sender_arr))
					: $um->getUrl('op=writemessage&reply_recipients='.urlencode(Util::serialize($sender_arr)));
				$output .= '<p class="message_reply"><a href="'.$reply_url.'">'.Lang::t('_REPLY').'</a></p>';
			}
		$output .= '</div>';

		cout($output, 'content');
	}

	function download() {
		//checkPerm('view');

		require_once(_base_.'/lib/lib.download.php');

		//find selected file

		list($filename) = $this->db->fetch_row($this->db->query("
		SELECT attach
		FROM %adm_message
		WHERE idMessage = '".$_GET['id_message']."'"));

		if(!$filename) {
			$output = getErrorUi('Sorry, such file does not exist!');
			cout($output, 'content');

			return;
		}
		//recognize mime type
		$extens = array_pop(explode('.', $filename));
		sendFile(_PATH_MESSAGE, $filename, $extens);
	}


	function messageGetTitleArea($text, $image = '', $alt_image = '') {
		$res="";

        /*
		if (Get::cur_plat() == "cms") {
			$res = getCmsTitleArea($text, $image = '', $alt_image = '');
		}
		else {
			$res = getTitleArea($text, $image = '', $alt_image = '');
		}
        */
        
        $res = getTitleArea($text, $image = '', $alt_image = '');
        
		return $res;
	}

	function quickSendMessage($sender, $recipients, $subject, $textof) {

		if(!is_array($recipients)) $recipients = array($recipients);

		$query_mess = "
		INSERT INTO %adm_message
		( idCourse, sender, posted, title, textof, attach, priority ) VALUES
		(
			'0',
			'".$sender."',
			'".date("Y-m-d H:i:s")."',
			'".$subject."',
			'".$textof."',
			'',
			'3'
		)";
		if(!$this->db->query($query_mess)) return false;
		list($id_message) = $this->db->fetch_row($this->db->query("SELECT LAST_INSERT_ID()"));

		$re = true;
		$recipients[] = getLogUserId();
		$logged_user =  getLogUserId();
		while(list(, $id_recipient) = each($recipients)) {

			$query_recipients = "
			INSERT INTO %adm_message_user
			( idMessage, idUser, idCourse, `read` ) VALUES
			(
				'".$id_message."',
				'".$id_recipient."',
				'0',
				'".( $id_recipient == $logged_user ? _MESSAGE_MY : _MESSAGE_UNREADED  )."'
			) ";
			$re &= $this->db->query($query_recipients);
		}
		return $re;
	}

}


function messageDispatch($op, $mvc = false) {

	if(isset($_POST['undo'])) 	$op = 'message';
	if(isset($_POST['okselector'])) 	$op = 'writemessage';
	if(isset($_POST['cancelselector'])) $op = 'message';
	if(isset($_POST['back_recipients'])) $op = 'addmessage';

	$module = new MessageModule($mvc);

	switch($op) {
		case "message" : {
			$module->message();
		};break;
		case "addmessage" : {
			$module->addmessage();
		};break;
		case "writemessage" : {
			$module->writemessage();
		};break;
		case "delmessage" : {
			$module->delmessage();
		};break;
		case "readmessage" : {
			$module->readmessage();
		};break;
		case "download" : {
			$module->download();
		};break;
	}
}



// ----------------------------------------------------------------------------



class Man_Message {

	protected $db;

	public function __construct() {
		$this->db = DbConn::getInstance();
	}

	function getCountUnreaded($id_user, $courses, $last_access, $return_sum = false) {

		if($return_sum === true) $unreaded = 0;
		else $unreaded = array();

		$query_unreaded = "
		SELECT user.idCourse, COUNT(*)
		FROM %adm_message_user AS user
		WHERE user.idUser = '".$id_user."' AND user.read = '"._MESSAGE_UNREADED."' AND user.deleted = '"._MESSAGE_VALID."'
		GROUP BY user.idCourse ";
		$re_message = $this->db->query($query_unreaded);
		while(list($id_c, $message) = $this->db->fetch_row($re_message)) {

			if($return_sum === true) $unreaded += $message;
			else $unreaded[$id_c] = $message;
		}
		if ($unreaded != 0)
			return ''.$unreaded.'';
		else
			return $unreaded;
	}

}

?>
