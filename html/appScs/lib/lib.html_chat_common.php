<?php

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
 * @package  DoceboSCS
 * @version  $Id: lib.html_chat_common.php 995 2007-03-09 14:15:07Z fabio $
 */

define("LOGOUT_TIME", 10);
 

function getPopupBaseUrl() {

	return basename($_SERVER["SCRIPT_NAME"])."?sn=".Get::cur_plat();

}


function getChatRoomId() {

	if (!isset($_SESSION["chat_room_id"]))
		$_SESSION["chat_room_id"]=getDefaultRoomId();

	$room=$_SESSION["chat_room_id"];

	return $room;
}


function listUsers(&$out, &$lang) {

	$res="";
	$id_user=0;

	$room=getChatRoomId();

	$now=time();
	$_SESSION["chat_last_hit"]=$now;

	$users_qtxt="SELECT * FROM ".$GLOBALS["prefix_scs"]."_rules_user WHERE id_room='".$room."' ORDER BY userid";
	$users_q=sql_query($users_qtxt);

	$res .= "<div class=\"chatUsers\">\n"
		.'<div class="intestation"><img src="'.$GLOBALS["img_path"].'users_list.gif" alt=".:" />'.$lang->def('_USERS_LIST').'</div>';

	if (($users_q) && (sql_num_rows($users_q) > 0)) {
		while($row = sql_fetch_array($users_q)) {
			if ($row["userid"]<>"Anonymous") 
			$res .= "<div class=\"user_row\">".$row["userid"]."</div>\n";
		}
	}
	$res .= "</div>\n";
	return $res;
}

function checkLogin($auto_reload=true) {

	$id_user=0;

	$room=getChatRoomId();
	//$lang=& $GLOBALS["lang"];
        $lang=& DoceboLanguage::createInstance('htmlframechat', 'scs');$lang=& DoceboLanguage::createInstance('htmlframechat', 'scs');

	$where_del ="WHERE ( (last_hit < '".(time()-LOGOUT_TIME)."' AND auto_reload='1') OR ";
	$where_del.="(last_hit < '".(time()-90)."' AND auto_reload='0') ) AND id_room=$room";

	$qtxt="SELECT userid FROM ".$GLOBALS["prefix_scs"]."_rules_user ".$where_del;
	$q=sql_query($qtxt);

	if (($q) && (sql_num_rows($q) > 0)) {
		while($row=sql_fetch_array($q)) {
			if ($row["userid"]<>"Anonymous")  {
			$txt ="<div class=\"chat_user_logout\">";
			$txt.=$row["userid"]." ".$lang->def("_USER_HAS_QUIT")."</div>";
			$qtxt ="INSERT INTO ".$GLOBALS["prefix_scs"]."_chat_msg (id_user, id_room, sent_date, text) ";
			$qtxt.="VALUES('0', '".$room."', NOW(), '".$txt."')";
			$msg_q=sql_query($qtxt);
			
			sql_query(	"UPDATE ".$GLOBALS['prefix_lms']."_tracksession"
							." SET lastTime = '".date('Y-m-d H:i:s')."',"
							." lastFunction = 'chat'"
							." WHERE idEnter = '".$_SESSION['id_enter_course']."'"
							." AND idCourse = '".$_SESSION['idCourse']."'"
							." AND idUser = '".getLogUserId()."'");
			}
		}

		$qtxt="DELETE FROM ".$GLOBALS["prefix_scs"]."_rules_user ".$where_del;
		$q=sql_query($qtxt);
	}

	$users_qtxt="SELECT * FROM ".$GLOBALS["prefix_scs"]."_rules_user";
	$users_q=sql_query($users_qtxt);

	if (($users_q) && (sql_num_rows($users_q) == 0)) {
		$qtxt="ALTER TABLE ".$GLOBALS["prefix_scs"]."_rules_user AUTO_INCREMENT=1";
		$q=sql_query($qtxt);
	}

	/* $users_qtxt="SELECT * FROM ".$GLOBALS["prefix_scs"]."_rules_user WHERE id_room='".$room."' ORDER BY userid";
	$users_q=sql_query($users_qtxt); */

	if ((isset($_SESSION["chat_last_hit"])) && (
	   ( ($_SESSION["chat_last_hit"] < (time() - LOGOUT_TIME)) && ($auto_reload) ) ||
		 ( ($_SESSION["chat_last_hit"] < (time() - 90)) && (!$auto_reload) ) )) {
		unset($_SESSION["chat_user_id"]);
		unset($_SESSION["chat_user_name"]);
		//echo "<script>alert('DEBUG: reset user id');</script>";
	}

	$now=time();
	$_SESSION["chat_last_hit"]=$now;

	if (!isset($_SESSION["chat_user_id"])) {
		
		if (!isset($_SESSION["clean_old_msg"])) {
			cleanOldMsg();
			unset($_SESSION["clean_old_msg"]); //$_SESSION["clean_old_msg"]=1;
		}
		
		if (!isset($_SESSION["chat_start_date"])) {
			$_SESSION["chat_start_date"]=date("Y-m-d H:i:s", time());
		}

		$acl_man=& Docebo::user()->getAclManager();
		
		$user_info = $acl_man->getUser(getLogUserId(), false);

		if($user_info[ACL_INFO_FIRSTNAME] !== '' && $user_info[ACL_INFO_LASTNAME] !== '')
			$userid = $user_info[ACL_INFO_FIRSTNAME].' '.$user_info[ACL_INFO_LASTNAME];
		elseif($user_info[ACL_INFO_FIRSTNAME] !== '')
			$userid = $user_info[ACL_INFO_FIRSTNAME];
		elseif($user_info[ACL_INFO_LASTNAME] !== '')
			$userid = $user_info[ACL_INFO_LASTNAME];
		else
			$userid = $acl_man->relativeId($user_info[ACL_INFO_USERID]);

		//$userid=$acl_man->relativeId(Docebo::user()->getUserId());
		
		if ($userid<>"Anonymous")  {
		
		$qtxt ="INSERT INTO ".$GLOBALS["prefix_scs"]."_rules_user (id_room, userid, user_ip, last_hit, auto_reload) ";
		$qtxt.="VALUES('".$room."', '".$userid."', '".$_SERVER["REMOTE_ADDR"]."', '".$now."', '".($auto_reload ? 1:0)."')";
		$q=sql_query($qtxt);
		list($id_user)=sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));

		if ((int)$id_user > 0) {
			$_SESSION["chat_user_id"]=$id_user;
			$_SESSION["chat_user_name"]=$userid;
		}

		$txt ="<div class=\"chat_user_login\">";
		$txt.=$userid." ".$lang->def("_USER_LOGGED_IN")."</div>";
		$qtxt ="INSERT INTO ".$GLOBALS["prefix_scs"]."_chat_msg (id_user, id_room, sent_date, text) ";
		$qtxt.="VALUES('0', '".$room."', '".date("Y-m-d H:i:s")."', '".$txt."')";
		$q=sql_query($qtxt);
		
		sql_query(	"UPDATE ".$GLOBALS['prefix_lms']."_tracksession"
					." SET lastTime = '".date('Y-m-d H:i:s')."',"
					." lastFunction = 'chat'"
					." WHERE idEnter = '".$_SESSION['id_enter_course']."'"
					." AND idCourse = '".$_SESSION['idCourse']."'"
					." AND idUser = '".getLogUserId()."'");
		
		//$users_q=sql_query($users_qtxt);
		}
	}
	else {
		if ($_SESSION["chat_user_name"]<>"Anonymous") {
		$id_user=$_SESSION["chat_user_id"];
		$qtxt ="UPDATE ".$GLOBALS["prefix_scs"]."_rules_user SET id_room='".$room."', last_hit='".$now."' ";
		$qtxt.="WHERE id_user='".$id_user."'";
		$q=sql_query($qtxt);
		if(sql_affected_rows() == 0) {
			// seem that the user is not logged into the room, log in
			$qtxt ="INSERT INTO ".$GLOBALS["prefix_scs"]."_rules_user (id_user, id_room, userid, user_ip, last_hit, auto_reload) ";
			$qtxt.="VALUES('".$_SESSION["chat_user_id"]."', '".$room."', '".$_SESSION["chat_user_name"]."', '".$_SERVER["REMOTE_ADDR"]."', '".$now."', '".($auto_reload ? 1:0)."')";
			$q=sql_query($qtxt);
		}
		}
	}

	/*if (($users_q) && (sql_num_rows($users_q) > 0)) {
		while($row=sql_fetch_array($users_q)) {
			$res.="<div>".$row["userid"]."</div>\n";
		}
	}*/
}


function haveNewMsg(& $last_msg_id) {
	$res=false;

	$room=getChatRoomId();

	$qtxt ="SELECT msg_id FROM ".$GLOBALS["prefix_scs"]."_chat_msg ";
	$qtxt.="WHERE id_room='".$room."' AND msg_id > '".$last_msg_id."' ORDER BY msg_id DESC";
	$q=sql_query($qtxt); //-- debug: --// echo $qtxt;

	if (($q) && (sql_num_rows($q) > 0)) {
		$row=sql_fetch_array($q);
		$last_msg_id=$row["msg_id"];
		$res=true;
	}

	return $res;
}


function getMsgBuffer(&$lang, $limit = false, $incremental = false) {

	$messages 	= '';
	$room 		= getChatRoomId();
	
	if($limit == false) $limit = 50;
	
	if($incremental === false) {
		
		//  if never start a chat set start to now
		if(!isset($_SESSION["chat_start_date"])) $_SESSION["chat_start_date"] = date("Y-m-d H:i:s", time());
		
		$query_msg = "
		SELECT t1.msg_id, t1.sent_date, t1.id_user, t1.text, t1.userid
		FROM ".$GLOBALS["prefix_scs"]."_chat_msg as t1 
		WHERE id_room = '".$room."' AND sent_date > '".$_SESSION["chat_start_date"]."' 
		ORDER BY t1.sent_date LIMIT 0,".$limit;
		$re_msg = sql_query($query_msg);
	} else {
		
		// if never read a message set it
		if(!isset($_SESSION['last_msg_read'])) $_SESSION['last_msg_read'] = 0;
		
		$query_msg = "
		SELECT t1.msg_id, t1.sent_date, t1.id_user, t1.text, t1.userid
		FROM ".$GLOBALS["prefix_scs"]."_chat_msg as t1 
		WHERE id_room = '".$room."' AND t1.msg_id > '".$_SESSION['last_msg_read']."' 
		ORDER BY t1.sent_date, t1.msg_id";
		$re_msg = sql_query($query_msg);
	}
	if($re_msg && (sql_num_rows($re_msg) > 0)) {
		
		$color 		= 0;
		$lines 		= 0;
		$last_id 	= 0;
		while($msg_info = sql_fetch_array($re_msg)) {
			// if the message sender is the same that read
			if($msg_info['id_user'] == $_SESSION['chat_user_id']) $color = 2;
			else $color = ($lines++) % 2;
			
			$messages .= '<div class="chat_line-'.$color.'">';
			if(trim($msg_info['userid']) != '') {
				
				$messages .= '['.substr($msg_info['sent_date'], 11).'] '
						.'<span class="user_name">'.$msg_info['userid'].' &gt;</span>';
			}
			$messages .= $msg_info['text'].'</div>'."\n";
			$last_id = $msg_info['msg_id'];
		}
		$_SESSION['last_msg_read'] = $last_id;
	}
	return $messages;
}


function cleanOldMsg() {

	$qtxt="DELETE FROM ".$GLOBALS["prefix_scs"]."_chat_msg WHERE sent_date < DATE_SUB(NOW(), INTERVAL 2 HOUR)";
	$q=sql_query($qtxt);


	$qtxt="SELECT * FROM ".$GLOBALS["prefix_scs"]."_chat_msg LIMIT 0,1";
	$q=sql_query($qtxt);

	if (($q) && (sql_num_rows($q) == 0)) {
		$qtxt="ALTER TABLE ".$GLOBALS["prefix_scs"]."_chat_msg AUTO_INCREMENT=1";
		$q=sql_query($qtxt);
	}

}


function listRooms(& $out, & $lang) {

	$room = getChatRoomId();

	$now = time();
	$_SESSION["chat_last_hit"] = $now;

	$room_qtxt = "
	SELECT t1.id_room, t1.room_name, COUNT(t2.id_user) AS user_cnt
	FROM ".$GLOBALS["prefix_scs"]."_rules_room as t1 ".
	"LEFT JOIN ".$GLOBALS["prefix_scs"]."_rules_user as t2 ".
	"ON (t1.id_room=t2.id_room) ".
	"WHERE 1".getRoomFilter("t1")." ".
	"GROUP BY t1.id_room ORDER BY t1.room_name";
	$room_q = sql_query($room_qtxt);

	$res = '<div class="chatRooms">'
		.'<div class="intestation"><img src="'.$GLOBALS["img_path"].'rooms_list.gif" alt=".:" />'.$lang->def('_ROOMS_LIST').'</div>'
		."<ul>";
	if (($room_q) && (sql_num_rows($room_q) > 0)) {
		while($row = sql_fetch_array($room_q)) {
			$res.="<li>";
			if ($row["id_room"] != $room)
				$res.="<a href=\"".getPopupBaseUrl()."&amp;op=setroom&amp;ri=".$row["id_room"]."\">";
			$res .= $row["room_name"];
			if ($row["id_room"] != $room)
				$res.="</a>";
			if ($row["user_cnt"] > 0)
				$res.=" (".$row["user_cnt"].")";
			$res.="</li>";
		}
	}
	$res .= "</ul>"
		.'</div>';
	return $res;
}


function getRoomFilter($table=FALSE) {
	require_once($GLOBALS["where_scs"]."/lib/lib.chat.php");
	
	$chatman=new ChatManager();
	
	$res="";
	$tab_prefix=($table === FALSE ? "" : $table.".");

	$room_list=$chatman->getRoomFilter();
	$room_type=$chatman->getRoomTypeFilter();
	
	if (!empty($room_list))
		$res.=" AND ".$tab_prefix."id_room IN (".$room_list.")";
		
	if (!empty($room_type))
		$res.=" AND ".$tab_prefix."room_type IN (".$room_type.")";		
	
	return $res;
}


function sendChatMsg() {

	$room=$_SESSION["chat_room_id"];

	$backurl=getPopupBaseUrl()."&amp;op=write";

	if ((!isset($_SESSION["chat_user_id"])) || (!userCanPost())) {
		Util::jump_to($backurl);
	} else {
		$id_user=$_SESSION["chat_user_id"];
		$userid=$_SESSION["chat_user_name"];
	}
	
	$txt=$GLOBALS["chat_emo"]->drawEmoticon(htmlentities($_POST["msgtxt"], ENT_COMPAT, 'UTF-8'));
	
	if ($userid<>"Anonymous") {
	$qtxt ="INSERT INTO ".$GLOBALS["prefix_scs"]."_chat_msg (id_user, id_room, userid, sent_date, text) ";
	$qtxt.="VALUES('".$id_user."', '".$room."', '".$userid."', '".date("Y-m-d H:i:s")."', '".$txt."')";
	$q=sql_query($qtxt);
	
	sql_query(	"UPDATE ".$GLOBALS['prefix_lms']."_tracksession"
					." SET lastTime = '".date('Y-m-d H:i:s')."',"
					." lastFunction = 'chat'"
					." WHERE idEnter = '".$_SESSION['id_enter_course']."'"
					." AND idCourse = '".$_SESSION['idCourse']."'"
					." AND idUser = '".getLogUserId()."'");
	}
	
	Util::jump_to($backurl);
}



function userCanPost() {
	return true;
}


function saveChatMsg() {

	$room=getChatRoomId();

	$qtxt ="SELECT t1.msg_id, t1.text, t1.userid FROM ".$GLOBALS["prefix_scs"]."_chat_msg as t1 ";
	$qtxt.="WHERE id_room='".$room."' ";
	$qtxt.="ORDER BY t1.sent_date DESC";
	$q=sql_query($qtxt);

	$res="";
	if (($q) && (sql_num_rows($q) > 0)) {
		$i=0;
		while($row=sql_fetch_array($q)) {

			$line="";
			if (!empty($row["userid"]))
				$line.=$row["userid"].": ";

			$txt=strip_tags($row["text"]);
			$txt=trim($txt);
			$line.=$txt;

			if (!empty($txt))
				$res=$line."\r\n".$res;
		}

	}


	ob_end_clean();
	header('Content-Length:'. strlen($res));
	header("Content-type: application/download\n");
	header("Cache-control: private");
	header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	header('Content-Disposition: attachment; filename="chat.txt"');
	echo $res;
	exit();

}


function getDefaultRoomId() {
	
	$res=false;
	
	$room_qtxt = "
		SELECT id_room
		FROM ".$GLOBALS["prefix_scs"]."_rules_room ".
		"WHERE 1".getRoomFilter()." ".
		"ORDER BY room_name LIMIT 0,1";
	
	$room_q = sql_query($room_qtxt);	
	
	if (($room_q) && (sql_num_rows($room_q) > 0)) {
		$row=sql_fetch_array($room_q);
		$res=(int)$row["id_room"];
	}
	
	return $res;
}


function getBackUrl($use_xhtml=TRUE) {
	$res="";	
	
	if (isset($_SESSION["chat_back_url"]))
		$res=$_SESSION["chat_back_url"];
	else if (isset($_GET["backurl"]))  {
		$backurl=urldecode($_GET["backurl"]);
		if (strpos($backurl, $_SERVER["HTTP_HOST"]) === FALSE) { // No-phishing zone!
			$backurl=(isset($_SERVER["HTTPS"]) ? "https://" : "http://").$_SERVER["HTTP_HOST"].$backurl;
			$_SESSION["chat_back_url"]=$backurl;
			$res=$backurl;
		}
	}	

	if ($use_xhtml)
		$res=str_replace("&", "&amp;", $res);
	
	return $res;
}


// ----------------------------------------------------------------------------


class HtmlChatEmoticons {
	
	function HtmlChatEmoticons() {
	}


	function emoticonList($ext="gif") {
		return "";
	}	

	
	function getChatEmoticon($name, $ext="gif") {
	
		$res ="<img alt=\"".$name."\" title=\"".$name."\" src=\"";
		$res.=getPathImage('fw')."emoticons/".$name.".".$ext."\" style=\"border: 0px none;\" />";
	
		return $res;
	}	

	
	function getEmoticonArr() {
	
		$arr=array();
	
		$arr["wink_smile"]=";-)";
		$arr["whatchutalkingabout_smile"]=":|";
		$arr["tounge_smile"]=":-P";
		$arr["angel_smile"]="o:)";
		$arr["regular_smile"]=":-)";
		$arr["teeth_smile"]=":D";
		$arr["shades_smile"]="8-)";
		$arr["sad_smile"]=":-(";
		//$arr["cry_smile"]=":'(";
		$arr["omg_smile"]=":-O";
		$arr["confused_smile"]=":-S";
		$arr["devil_smile"]="X-(";
		$arr["broken_heart"]="=((";
		$arr["heart"]=":x";
		//$arr["embaressed_smile"]="";
		$arr["thumbs_up"]="[OK]";
		$arr["thumbs_down"]="[BAD]";
		$arr["lightbulb"]="[IDEA]";
		$arr["envelope"]="[MAIL]";
	
		return $arr;
	}	

	
	function drawEmoticon($txt) {
	
		$res=$txt;
	
		$res=preg_replace("/;[-]?\\)/si", $this->getChatEmoticon("wink_smile"), $res);
		$res=preg_replace("/:[-]?\\|/si", $this->getChatEmoticon("whatchutalkingabout_smile"), $res);
		$res=preg_replace("/:[-]?P/si", $this->getChatEmoticon("tounge_smile"), $res);
		$res=preg_replace("/o:[-]?\\)/si", $this->getChatEmoticon("angel_smile"), $res);
		$res=preg_replace("/:[-]?\\)/si", $this->getChatEmoticon("regular_smile"), $res);
		$res=preg_replace("/:[-]?\\(/si", $this->getChatEmoticon("sad_smile"), $res);
		$res=preg_replace("/:?'[-]?(\\(|\\[)/si", $this->getChatEmoticon("cry_smile"), $res);
		$res=preg_replace("/:[-]?o/si", $this->getChatEmoticon("omg_smile"), $res);
		$res=preg_replace("/8[-]?\\)/si", $this->getChatEmoticon("shades_smile"), $res);
		$res=preg_replace("/:[-]?s/si", $this->getChatEmoticon("confused_smile"), $res);
		$res=preg_replace("/X[-]?\\(/si", $this->getChatEmoticon("devil_smile"), $res);
		$res=preg_replace("/\\=\\(\\(/si", $this->getChatEmoticon("broken_heart"), $res);
		$res=preg_replace("/:[-]?x/si", $this->getChatEmoticon("heart"), $res);
		$res=preg_replace("/:[-]?d/si", $this->getChatEmoticon("teeth_smile"), $res);
		//$res=preg_replace("/:''[-]?".">)/si", getChatEmoticon("embaressed_smile"), $res);
	
	
		$res=preg_replace("/\\[OK\\]/s", $this->getChatEmoticon("thumbs_up"), $res);
		$res=preg_replace("/\\[BAD\\]/s", $this->getChatEmoticon("thumbs_down"), $res);
		$res=preg_replace("/\\[IDEA\\]/s", $this->getChatEmoticon("lightbulb"), $res);
		$res=preg_replace("/\\[MAIL\\]/s", $this->getChatEmoticon("envelope"), $res);
	
		return $res;
	}
	
}


// ----------------------------------------------------------------------------



?>