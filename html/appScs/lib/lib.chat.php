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
 * @version  $Id: lib.chat.php 995 2007-03-09 14:15:07Z fabio $
 */

class ChatManager {

	function getIdRoom($platform, $room_type, $id_source) {

		$users_qtxt = "
		SELECT id_room
		FROM ".$GLOBALS["prefix_scs"]."_rules_room
		WHERE id_source = '".$id_source."' AND room_type = '".$room_type."'";
		list($id_room) = sql_fetch_row(sql_query($users_qtxt));
		return $id_room;
	}

	/**
	 * @param mixed $type specify wich type of chat to open; if not specified
	 * 	then the type will be auto-detected.
	 **/
	function getOpenChatCommand($open_text, $open_text_wa, $platform, $id_room, $basepath = FALSE, $type = FALSE, $use_room=TRUE) {

		if ($basepath === FALSE)
			$basepath = "";

		if ($type === FALSE) {
			if (getAccessibilityStatus())
				$chat_type = "accessible";
			else
				$chat_type = "default";
		}
		else {
			$chat_type=$type;
		}

		$use_room =($use_room ? 1 : 0);

		$out = '';
		$url = urlencode($_SERVER["REQUEST_URI"]);

                
                $link = $basepath;
                $link.= $GLOBALS['where_scs_relative'].'/modules/htmlframechat/index.php?sn='.$platform.'&amp;ri='.$id_room;
                //$link.="&amp;use_room=".$use_room;
                $link.="&amp;use_room=0";
                $link.= "&amp;backurl=".htmlentities(urlencode($url));

                $text=$open_text;

		$out .= '<p><a href="'.$link.'"
			onclick="window.open(\''.$link.'\', \'DoceboChat\',\'toolbar=no,menubar=no,directories=no\'); return false;"
			onkeypress="window.open(\''.$link.'\', \'DoceboChat\',\'toolbar=no,menubar=no,directories=no\'); return false;">'.$text.'</a></p>';

		return $out;
	}

	function getRoomUserOnline($platform, $room_type, $id_source) {

		$users_qtxt = "
		SELECT id_room
		FROM ".$GLOBALS["prefix_scs"]."_rules_room
		WHERE id_source = '".$id_source."' AND room_type = '".$room_type."'";
		list($id_room) = sql_fetch_row(sql_query($users_qtxt));

		$users_qtxt="
		SELECT *
		FROM ".$GLOBALS["prefix_scs"]."_rules_user
		WHERE id_room='".$id_room."'
		ORDER BY userid";
		$users_q = sql_query($users_qtxt);

		while($row = sql_fetch_array($users_q)) {
			$res[] = $row["userid"];
		}
		return $res;
	}


	function setRoomFilter($room_id_arr) {

		$_SESSION["room_id_arr"] = serialize($room_id_arr);
	}

	function getRoomFilterArr() {

		if (isset($_SESSION["room_id_arr"]))
			return unserialize($_SESSION["room_id_arr"]);
		else
			return false;
	}

	function getRoomFilter() {
		$res="";
		$filter_arr = $this->getRoomFilterArr();

		if ($filter_arr !== false)
			$res=implode(",", $filter_arr);

		return $res;
	}


	/**
	 * @param array $room_type_arr ('course', 'private', ...)
	 */
	function setRoomTypeFilter($room_type_arr) {
		$_SESSION["room_type_arr"]=serialize($room_type_arr);
	}

	function getRoomTypeFilterArr() {
		if (isset($_SESSION["room_type_arr"]))
			return unserialize($_SESSION["room_type_arr"]);
		else
			return false;
	}

	function getRoomTypeFilter() {
		$res="";
		$filter_arr = $this->getRoomTypeFilterArr();

		if ($filter_arr !== false)
			$res=implode(",", $filter_arr);

		return $res;
	}


}

?>