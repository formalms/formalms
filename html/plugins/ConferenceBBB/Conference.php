<?php
/**
 * Created by PhpStorm.
 * User: marcocastignoli
 * Date: 19/10/16
 * Time: 10.26
 */
namespace Plugin\ConferenceBBB;

use Docebo;
use Get;
use stdClass;
use BigBlueButton;

define("_BBB_STREAM_TIMEOUT", 30);
define("_BBB_AUTH_CODE", 'X-BBB-Auth-Token');
define("_BBB_AUTH_DATA", 'bbb_login_data');


include_once(dirname(__FILE__)."/../lib.bbb.api.php");

class Conference extends \FormaPluginConference implements \FormaPluginConferenceInterface{

    public static $can_mod = false;

    function name(){
        return self::getName();
    }

    function Conference(){
        //self::server = Get::sett('ConferenceBBB_server');
        //self::port = Get::sett('ConferenceBBB_port');
    }

    static function _getRoomTable() {

        return $GLOBALS['prefix_scs'].'_ConferenceBBB';
    }

    static function _query($query) {

        $re = sql_query($query);
        return $re;
    }

    static function generateConfKey() {
        $conf_key = '';
        for($i = 0; $i <8;$i++) {
            switch(mt_rand(0, 2)) {
                case "0" : $conf_key .= chr(mt_rand(65, 90));
                case "1" : $conf_key .= chr(mt_rand(97, 122));
                case "2" : $conf_key .= mt_rand(0, 9);
            }
        }
        return $conf_key;
    }


    function canOpenRoom($start_time) {
        return true;
    }


    function insertRoom($idConference, $name, $start_date, $end_date, $maxparticipants) {

        $acl_manager =& Docebo::user()->getAclManager();
        $display_name = Docebo::user()->getUserName();
        $u_info = $acl_manager->getUser(getLogUserId(), false);
        $user_email=$u_info[ACL_INFO_EMAIL];
        $confkey = self::generateConfKey();
        $audiovideosettings=1;
        $maxmikes=(int)Get::sett("bbb_max_mikes");
        $extra_conf = array();
        $extra_conf['lobbyEnabled'] = false;
        $extra_conf['privateChatEnabled'] = false;
        $extra_conf['publicChatEnabled'] = false;
        $extra_conf['screenShareEnabled'] = false;
        $extra_conf['autoAssignMikeOnJoin'] = false;
        $extra_conf['whiteboardEnabled'] = false;
        $extra_conf['documentSharingEnabled'] = false;
        $extra_conf['recordingEnabled'] = false;
        $extra_conf['autoHandsFreeOnAVLoad'] = false;
        $extra_conf['joinEmailRequired'] = false;

        $res->result = true;
        require_once(_base_.'/lib/lib.json.php');
        $json = new \Services_JSON();

        //save in database the roomid for user login
        $insert_room = "
		INSERT INTO ".self::_getRoomTable()."
		( idConference,confkey,emailuser,displayname,audiovideosettings,maxmikes,schedule_info, extra_conf ) VALUES (
			'".$idConference."',
			'".$confkey."',
			'".$user_email."',
			'".$display_name."',
			'".$audiovideosettings."',
			'".$maxmikes."',
			'',
			'".$json->encode($extra_conf)."'
		)";

        if(!sql_query($insert_room)) return false;
        return sql_insert_id();

        return false;
    }

    static function roomInfo($room_id) {

        $room_open = "
		SELECT  *
		FROM ".self::_getRoomTable()."
		WHERE id = '".$room_id."'";
        $re_room = self::_query($room_open);

        return self::nextRow($re_room);
    }

    static function roomActive($idCourse, $at_date = false) {

        $room_open = "
		SELECT id,idCourse,idSt,name, starttime,endtime, confkey, emailuser, displayname, meetinghours,maxparticipants,audiovideosettings,maxmikes
		FROM ".self::_getRoomTable()."
		WHERE idCourse = '".$idCourse."'";

        if ($at_date !== false) {
            $room_open .= " AND endtime >= '".$at_date."'";
        }

        $room_open .= " ORDER BY starttime";

        $re_room = self::_query($room_open);

        return $re_room;
    }



    static function nextRow($re_room) {

        return sql_fetch_array($re_room);
    }

    function deleteRoom($room_id) {

        $res = self::api_delete_schedule($room_id);

        $room_del = "
		DELETE FROM ".self::_getRoomTable()."
		WHERE idConference = '".$room_id."'";
        $re_room = self::_query($room_del);

        return $re_room;
    }

    function getUrl($idConference,$room_type) {
        $lang =& \DoceboLanguage::createInstance('conference', 'lms');

        $conf=new \Conference_Manager();

        $conference = $conf->roomInfo($idConference);

        $acl_manager =& Docebo::user()->getAclManager();
        $username = Docebo::user()->getUserName();
        $u_info = $acl_manager->getUser(getLogUserId(), false);
        $user_email=$u_info[ACL_INFO_EMAIL];


        $query2="SELECT * FROM ".self::_getRoomTable()." WHERE idConference = '".$idConference."'";
        $re_room = self::_query($query2);
        $room=self::nextRow($re_room);


        if ($room["audiovideosettings"]==0) {
            $av="audio";
        } else {
            $av="av";
        }
        $exit_url="http://".$_SERVER["SERVER_NAME"].$_SERVER["PHP_SELF"]."?modname=conference&op=list";

        $clientId = "";
        /*$res = self::api_join_meeting();
        if ($res && $res->result) {
            $clientId = "";
        }*/

        $name = self::getRoomName($idConference);
        include_once ('lib.bbb.api.php');
        $url= Get::sett('ConferenceBBB_server', "");
        $salt = Get::sett('ConferenceBBB_salt', "");
        $moderator_password = Get::sett('ConferenceBBB_password_moderator', "");
        $viewer_password = Get::sett('ConferenceBBB_password_viewer', "");
        $response = BigBlueButton::createMeetingArray($username, $name, null, $moderator_password, $viewer_password, $salt, $url, $returnurl);
        if(checkPerm('mod', true)){
            $password = $moderator_password;
        }
        else {
            $password = $viewer_password;
        }

        if(!$response){//If the server is unreachable
            $msg = 'Unable to join the meeting. Please check the url of the video conference server AND check to see if the video conference server is running.';
        }
        else if( $response['returncode'] == 'FAILED' ) { //The meeting was not created
            if($response['messageKey'] == 'checksumError'){
                $msg =  'A checksum error occured. Make sure you entered the correct salt.';
            }
            else{
                $msg = $response['message'];
            }
        }
        else{
            $_url = BigBlueButton::joinURL($name, $username,$password, $salt, $url);
        }

////////////////////////////////////////////////////
        $url = '<a onclick="window.open(this.href, \'\', \'\');return false;" href="'.str_replace('&', '&amp;', $_url).'">'.$lang->def('_ENTER').'</a>';

        return $url;
    }

    static function getRoomName($idConference) {
        $query = "SELECT * FROM ".$GLOBALS['prefix_scs']."_room WHERE id = '".$idConference."'";
        $res = self::_query($query);
        $info = self::nextRow($res);
        return (isset($info['name']) ? $info['name'] : "");
    }

    /**
     * Thanks to : jbr at ya-right dot com
     * http://it2.php.net/manual/it/function.fsockopen.php
     * for the HTTP 1.1 implementation
     */
    function _decode_header ( $str ) {

        $out = array ();
        $part = preg_split ( "/\r?\n/", $str, -1, PREG_SPLIT_NO_EMPTY );
        for( $h = 0; $h < sizeof ( $part ); $h++ ) {

            if ( $h != 0 ) {

                $pos = strpos ( $part[$h], ':' );
                $k = strtolower ( str_replace ( ' ', '', substr ( $part[$h], 0, $pos ) ) );
                $v = trim ( substr ( $part[$h], ( $pos + 1 ) ) );
            } else {

                $k = 'status';
                $v = explode ( ' ', $part[$h] );
                $v = $v[1];
            }
            if ( $k == 'set-cookie' ) {
                $out['cookies'][] = $v;
            } else if ( $k == 'content-type' ) {

                if(($cs = strpos ($v, ';')) !== false ) { $out[$k] = substr ( $v, 0, $cs ); }
                else { $out[$k] = $v; }
            } else {
                $out[$k] = $v;
            }
        }
        return $out;
    }

    function _decode_body( $info, $str, $eol = "\r\n" ) {

        $tmp = $str;
        $add = strlen ( $eol );
        if ( isset ( $info['transfer-encoding'] ) && $info['transfer-encoding'] == 'chunked' ) {

            do {
                $tmp = ltrim ( $tmp );
                $pos = strpos ( $tmp, $eol );
                $len = hexdec ( substr ( $tmp, 0, $pos ) );
                if ( isset ( $info['content-encoding'] ) )  {
                    $str .= gzinflate ( substr ( $tmp, ( $pos + $add + 10 ), $len ) );
                } else {
                    $str .= substr ( $tmp, ( $pos + $add ), $len );
                }
                $tmp = substr ( $tmp, ( $len + $pos + $add ) );
                $check = trim ( $tmp );
            } while ( ! empty ( $check ) );
        }
        else if ( isset ( $info['content-encoding'] ) ) {
            $str = gzinflate ( substr ( $tmp, 10 ) );
        }else {
            $str = $tmp;
        }
        return $str;
    }

    /**
     * The only purpose of this function is to send the message to the server, read the server answer,
     * discard the header and return the other content
     *
     * @param 	string	$url 		the server url
     * @param 	string	$port 		the server port
     * @param	string 	$get_params	the get_params
     *
     * @return 	json
     */
    function _sendRequest($url, $port, $get_params) {

        $json_response = '';
        $tmp_url = parse_url($url);

        if(( $io = fsockopen($tmp_url['host'], $port, $errno, $errstr, _BBB_STREAM_TIMEOUT)) !== false) {

            socket_set_timeout($io, _BBB_STREAM_TIMEOUT);

            $send  = "GET /".$get_params." HTTP/1.1\r\n";
            $send .= "Host: ".$tmp_url['host']."\r\n";
            $send .= "User-Agent: PHP Script\r\n";
            $send .= "Accept: text/xml,application/xml,application/xhtml+xml,";
            $send .= "text/html;q=0.9,text/plain;q=0.8,video/x-mng,image/png,";
            $send .= "image/jpeg,image/gif;q=0.2,text/css,*/*;q=0.1\r\n";
            $send .= "Accept-Language: en-us, en;q=0.50\r\n";
            $send .= "Accept-Encoding: gzip, deflate, compress;q=0.9\r\n";
            $send .= "Connection: Close\r\n\r\n";

            fputs ( $io, $send );
            $header = '';
            do {
                $header .= fgets ( $io, 4096 );
            } while( strpos ( $header, "\r\n\r\n" ) === false );
            $info = self::_decode_header ( $header );
            $body = '';
            while(!feof($io)) {
                $body .= fread ( $io, 8192 );
            }
            fclose ( $io );

            $json_response = self::_decode_body ( $info, $body );

            echo $json_response;
        }
        return $json_response;
    }


    //--- NEW API UPDATE ---------------------------------------------------------

    static function _api_request($service, $method, $params, $parname = false) {
        require_once(_base_.'/lib/lib.json.php');
        require_once(_base_.'/lib/lib.fsock_wrapper.php');
        $server = Get::sett('ConferenceBBB_server', false);
        $output = false;
        $_parname = ($parname ? $parname."=" : "");
        if ($server && $service && $method) {
            $url = 'http://'.$server.'/api/'.$service.'/'.$method;

            $json = new \Services_JSON();
            $fsock = new \FSock();

            //check user login
            if ($service != 'auth' /*&& $method != 'login' && $method != 'verify'*/) {
                $auth_code = self::get_auth_code();
                if (!$auth_code) {
                    //make login
                    $auth_code = self::api_login();
                } else {
                    //verify if login is valid
                    if (!self::api_verify()) $auth_code = self::api_login();
                }

                if ($auth_code) {
                    $other_header = array(
                        _BBB_AUTH_CODE => $auth_code,
                        "Content-type" => "application/x-www-form-urlencoded"
                    );
                    $post = $_parname.urlencode($json->encode($params));
                    $res_json = $fsock->post_request($url, Get::sett('ConferenceBBB_port', '80'), $post, $other_header);
                    if ($res_json) {
                        $output = $json->decode($res_json);
                    }
                }

            } else {
                $post = $_parname.urlencode($json->encode($params));
                $other_header = array("Content-type" => "application/x-www-form-urlencoded");
                if ($method != 'login') $other_header[_BBB_AUTH_CODE] = self::get_auth_code();
                $res_json = $fsock->post_request($url, Get::sett('ConferenceBBB_port', '80'), $post, $other_header);
                if ($res_json) {
                    $output = $json->decode($res_json);
                }
            }

        }
        return $output;
    }

    static function get_auth_code() {
        if (isset($_SESSION[_BBB_AUTH_CODE]) && $_SESSION[_BBB_AUTH_CODE])
            return $_SESSION[_BBB_AUTH_CODE];
        return false;
    }

    static function api_login() {
        $params = new stdClass();
        $params->account = Get::sett('ConferenceBBB_user', "");
        $params->password = Get::sett('ConferenceBBB_password', "");
        $params->group = "all";
        $res = self::_api_request('auth', 'login', $params, 'request');
        $output = false;
        if ($res->result) {
            $auth_code = $res->response->authToken;
            $_SESSION[_BBB_AUTH_CODE] = $auth_code;
            $_SESSION[_BBB_AUTH_DATA] = $res->response;
            $output = $auth_code;
        }
        return $auth_code;
    }

    static function api_verify() {
        $params = new stdClass();
        $params->authToken = self::get_auth_code();
        $params->account = Get::sett('ConferenceBBB_user', "");
        $params->password = Get::sett('ConferenceBBB_password', "");
        $params->group = "all";
        $res =  self::_api_request('auth', 'verify', $params, 'data');
        if ($res && $res->result) return true;
        return false;
    }

    static function api_logout() {
        $params = new stdClass();
        $params->authToken = self::get_auth_code();
        $params->account = Get::sett('ConferenceBBB_user', "");
        $params->password = Get::sett('ConferenceBBB_password', "");
        $params->group = "all";
        return self::_api_request('auth', 'logout', $params, 'data');
    }

    static function api_start_meeting($idConference,$user_email,$display_name,$confkey,$audiovideosettings,$maxmikes,$maxparticipants, $extra_conf) {
        $params = new stdClass();

        $params->ClientId = ""; //Optional - Provides the value of client ID if specifically assigned
        $params->account = Get::sett('ConferenceBBB_user', ""); //Optional - Defines the user ID with which the registered BBB user will start a meeting groupName Optional all Defines group name, default is all
        $params->roomName = $display_name; //Optional - default - Defines Room name default is all agenda Optional Agenda of the meeting
        $params->meetingName = $display_name; //Optional - The name of the Meeting. Default is "From Third party Portal" displayName Optional This is to set the display name of host
        $params->joinEmailRequired = false; //Optional - true/false - Enables you to allow the attendees to join the meeting only on entering their email addresses; If it is set to true then joining the meeting without providing the email is disabled. Default is set to false audioVideo Optional av/audio/video/none Defines the audio and video settings av Audio Video Allowed none Audio-Video Disabled audio Audio Only video Video Only
        $params->maxParticipants = $maxparticipants; //Optional - Maximum numbers of participants allowed in the Meeting. autoAssignMikeOnJoin Optional true/false Provides control to let you assign the microphone to the attendee automatically on joining the meeting Default is set to false
        $params->autoHandsFreeOnAVLoad = false; //Optional - true/false - Enables the Hands-Free option on loading of the audio video broadcaster in the meeting Default is set to false assistentEnabled Optional true/false Enables the Meeting Assistant to be displayed at the start of the meeting Default is set to true
        $params->privateChatEnabled = true; //Optional - true/false - Enables the Private Chat feature in the meeting publicChatEnabled Optional true/false Enables the Public Chat feature in the meeting lobbyEnabled Optional true/false Enables the waiting area before the start of the meeting
        $params->screenShareEnabled = true; //Optional - true/false - Enables the Desktop Sharing feature in the meeting whiteboardEnabled Optional true/false This is used to enable/disable Whiteboard during a particular meeting
        $params->documentSharingEnabled = true; //Optional - true/false - This is used to enable/disable document share in the meeting cobrowserEnabled Optional true/false This is used to enable/disable co-browsing feature in the meeting
        $params->recordingEnabled = true; //Optional - true/false - This is used to enable/disable recording feature in the meeting meetingLengthMinutes Optional Defines the duration of the meeting in minutes
        //$params->internationalTollNumber = ""; //Optional - Defines the international dial in phone number that attendees have to call in order to connect to a conference call moderatorPhonePassCode Optional Defines the pass code that the host or the moderator has to enter while setting up a conference call
        //$params->attendeePhonePassCode = ""; //Optional - Defines the pass code that an attendee has to enter in order to join the conference call attendees

        $params->lobbyEnabled = $extra_conf['lobbyEnabled'];
        //$params->display_phone_info = $extra_conf['display_phone_info'];
        //$params->show_part_list = $extra_conf['show_part_list'];
        $params->privateChatEnabled = $extra_conf['privateChatEnabled'];
        $params->publicChatEnabled = $extra_conf['publicChatEnabled'];
        $params->screenShareEnabled = $extra_conf['screenShareEnabled'];
        //$params->meeting_assistant_visibility = $extra_conf['meeting_assistant_visibility'];
        $params->autoAssignMikeOnJoin = $extra_conf['autoAssignMikeOnJoin'];
        $params->whiteboardEnabled = $extra_conf['whiteboardEnabled'];
        $params->documentSharingEnabled = $extra_conf['documentSharingEnabled'];
        //$params->enable_web_sharing = $extra_conf['enable_web_sharing'];
        $params->recordingEnabled = $extra_conf['recordingEnabled'];
        //$params->allow_attendees_invitation = $extra_conf['allow_attendees_invitation'];
        $params->autoHandsFreeOnAVLoad = $extra_conf['autoHandsFreeOnAVLoad'];
        $params->joinEmailRequired = $extra_conf['joinEmailRequired'];

        //$params->recording_code = $extra_conf['recording_code'];

        $res = self::_api_request('conf', 'start_meeting', $params, 'data');

        if ($res) {
            if (!$res->result) return false;
            return $res;
        }
        return false;
    }




    static function api_delete_schedule($id_conference) {
        $query = "SELECT * FROM ".self::_getRoomTable()." WHERE idConference = '".$id_conference."'";
        $res = self::_query($query);
        $info = self::nextRow($res);

        require_once(_base_.'/lib/lib.json.php');
        $json = new \Services_JSON();
        $info_decoded = $json->decode($info['schedule_info']);

        $params = new stdClass();

        $params->account = Get::sett('ConferenceBBB_user', "");//Optional Defines the user ID with which the registered BBB user will start a meeting
        $params->groupName = "all";//Optional all Defines group name, default is all
        //$params->roomName = $name; //Optional default Defines Room name
        $params->scheduleId = $info_decoded->scheduleId; //Mandatory

        $res = self::_api_request('prtl', 'delete_schedule', $params, 'data');

        if ($res) {
            if (!$res->result) return false;
            return $res;
        }
        return false;
    }

    function api_check_meeting() {
        $params = new stdClass();

        //$params->

    }


    function api_join_meeting() {
        $params = new stdClass();
        /*
                //$params->
                $params->ClientId //optional - Provides the value of client ID if specifically assigned account Optional Defines the user ID with which the registered BBB user has started a meeting which attendee wants to join groupName Optional all Defines group name
                $params->roomName //Optional - default - Defines Room name
                $params->displayName //optional Display name of the user when he joins the meeting
                $params->meetingKey //optional
        */
    }

    function api_leave_meeting() {
        $params = new stdClass();

        //$params->

    }

}