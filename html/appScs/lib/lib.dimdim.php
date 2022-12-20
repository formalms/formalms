<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

/*
 * @author Fabio Pirovano
 * @version $Id:$
 * @since 3.5.0
 *
 * ( editor = Eclipse 3.2.0 [phpeclipse,subclipse,WTP], tabwidth = 4 )
 */

define('_DIMDIM_STREAM_TIMEOUT', 30);

define('_DIMDIM_AUTH_CODE', 'X-Dimdim-Auth-Token');
define('_DIMDIM_AUTH_DATA', 'dimdim_login_data');

class DimDim_Manager
{
    public $can_mod = false;

    protected $session;
    /**
     * @var false|string
     */
    public string $port;
    /**
     * @var false|string
     */
    public string $server;

    public function __construct()
    {
        $this->server = FormaLms\lib\Get::sett('dimdim_server');
        $this->port = FormaLms\lib\Get::sett('dimdim_port');
        $this->session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    }

    public function _getRoomTable()
    {
        return $GLOBALS['prefix_scs'] . '_dimdim';
    }

    public function _query($query)
    {
        $re = sql_query($query);

        return $re;
    }

    public function generateConfKey()
    {
        $conf_key = '';
        for ($i = 0; $i < 8; ++$i) {
            switch (mt_rand(0, 2)) {
                case '0': $conf_key .= chr(mt_rand(65, 90));
                // no break
                case '1': $conf_key .= chr(mt_rand(97, 122));
                // no break
                case '2': $conf_key .= mt_rand(0, 9);
            }
        }

        return $conf_key;
    }

    public function canOpenRoom($start_time)
    {
        return true;
    }

    public function insert_room($idConference, $user_email, $display_name, $confkey, $audiovideosettings, $maxmikes, $maxparticipants, $startdate, $starthour, $startminute, $duration, $extra_conf)
    {
        $res->result = true;

        if (FormaLms\lib\Get::sett('use_dimdim_api') === 'on') {
            $res = $this->api_schedule_meeting(
                            $idConference,
                            $user_email,
                            $display_name,
                            $confkey,
                            $audiovideosettings,
                            $maxmikes,
                            $maxparticipants,
                            $startdate,
                            $starthour,
                            $startminute,
                            $duration,
                            $extra_conf
                    );
        }

        if ($res && $res->result) {
            require_once _base_ . '/lib/lib.json.php';
            $json = new Services_JSON();

            //save in database the roomid for user login
            $insert_room = '
			INSERT INTO ' . $this->_getRoomTable() . "
			( idConference,confkey,emailuser,displayname,audiovideosettings,maxmikes,schedule_info, extra_conf ) VALUES (
				'" . $idConference . "',
				'" . $confkey . "',
				'" . $user_email . "',
				'" . $display_name . "',
				'" . $audiovideosettings . "',
				'" . $maxmikes . "',
				'" . $json->encode($res->response) . "',
				'" . $json->encode($extra_conf) . "'
			)";

            if (!sql_query($insert_room)) {
                return false;
            }

            return sql_insert_id();
        }

        return false;
    }

    public function roomInfo($room_id)
    {
        $room_open = '
		SELECT  *
		FROM ' . $this->_getRoomTable() . "
		WHERE id = '" . $room_id . "'";
        $re_room = $this->_query($room_open);

        return $this->nextRow($re_room);
    }

    public function roomActive($idCourse, $at_date = false)
    {
        $room_open = '
		SELECT id,idCourse,idSt,name, starttime,endtime, confkey, emailuser, displayname, meetinghours,maxparticipants,audiovideosettings,maxmikes
		FROM ' . $this->_getRoomTable() . "
		WHERE idCourse = '" . $idCourse . "'";

        if ($at_date !== false) {
            $room_open .= " AND endtime >= '" . $at_date . "'";
        }

        $room_open .= ' ORDER BY starttime';

        $re_room = $this->_query($room_open);

        return $re_room;
    }

    public function nextRow($re_room)
    {
        return sql_fetch_array($re_room);
    }

    public function deleteRoom($room_id)
    {
        if (FormaLms\lib\Get::sett('use_dimdim_api') === 'on') {
            $res = $this->api_delete_schedule($room_id);
        }

        $room_del = '
		DELETE FROM ' . $this->_getRoomTable() . "
		WHERE idConference = '" . $room_id . "'";
        $re_room = $this->_query($room_del);

        return $re_room;
    }

    public function getUrl($idConference, $room_type)
    {
        $lang = &DoceboLanguage::createInstance('conference', 'lms');

        $conf = new Conference_Manager();

        $conference = $conf->roomInfo($idConference);

        $acl_manager = &Docebo::user()->getAclManager();
        $display_name = Docebo::user()->getUserName();
        $u_info = $acl_manager->getUser(getLogUserId(), false);
        $user_email = $u_info[ACL_INFO_EMAIL];

        $query2 = 'SELECT * FROM ' . $this->_getRoomTable() . " WHERE idConference = '" . $idConference . "'";
        $re_room = $this->_query($query2);
        $room = $this->nextRow($re_room);

        if ($room['audiovideosettings'] == 0) {
            $av = 'audio';
        } else {
            $av = 'av';
        }
        $returnurl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] . '?modname=conference&op=list';

        /*
                $error = false;
                if (getLogUserId()==$conference["idSt"]) {

                    $url='<a onclick="window.open(this.href, \'\', \'\');return false;" href="http://'.$this->server.'/dimdim/html/envcheck/connect.action'
                                        .'?action=host'
                                        .'&email='.urlencode(FormaLms\lib\Get::sett('dimdim_user'))

                                        .'&confKey='.$room["confkey"]
                                        .'&confName='.urlencode($conference["name"])

                                        .'&lobby=false'
                                        .'&networkProfile=2'
                                        .'&meetingHours='.$conference["meetinghours"]
                                        .'&meetingMinutes=0'
                                        .'&presenterAV=av'
                                        .'&maxAttendeeMikes='.$room["maxmikes"]

                                        .'&displayName='.urlencode($acl_manager->getConvertedUserName($u_info))
                                        .'&attendees='.$user_email

                                        .'&maxParticipants='.$conference["maxparticipants"]

                                        .'&submitFormOnLoad=true'
                                        ."&returnUrl=".urlencode($returnurl)."\">".$lang->def('_START_CONFERENCE')."</a>";

                } else {

                    $url='<a onclick="window.open(this.href, \'\', \'\');return false;" href="http://'.$this->server.'/dimdim/html/envcheck/connect.action'
                            .'?action=join'

                            .'&email='.$user_email

                            .'&displayName='.urlencode($acl_manager->getConvertedUserName($u_info))

                            .'&confKey='.$room["confkey"]

                            ."&returnUrl=".urlencode($returnurl)."\">".$lang->def('_ENTER')."</a>";

                }
        */

        $clientId = '';
        /*$res = $this->api_join_meeting();
        if ($res && $res->result) {
            $clientId = "";
        }*/

        $name = $this->getRoomName($idConference);
        $_url = 'http://' . FormaLms\lib\Get::sett('dimdim_server', '') . '/console?clientId=' . $clientId . '&group=all&account=' . FormaLms\lib\Get::sett('dimdim_user', '') . '&room=' . urlencode($name);
        $url = '<a onclick="window.open(this.href, \'\', \'\');return false;" href="' . str_replace('&', '&amp;', $_url) . '">' . $lang->def('_ENTER') . '</a>';

        return $url;
    }

    public function getRoomName($idConference)
    {
        $query = 'SELECT * FROM ' . $GLOBALS['prefix_scs'] . "_room WHERE id = '" . $idConference . "'";
        $res = $this->_query($query);
        $info = $this->nextRow($res);

        return isset($info['name']) ? $info['name'] : '';
    }

    /**
     * Thanks to : jbr at ya-right dot com
     * http://it2.php.net/manual/it/function.fsockopen.php
     * for the HTTP 1.1 implementation.
     */
    public function _decode_header($str)
    {
        $out = [];
        $part = preg_split("/\r?\n/", $str, -1, PREG_SPLIT_NO_EMPTY);
        for ($h = 0; $h < sizeof($part); ++$h) {
            if ($h != 0) {
                $pos = strpos($part[$h], ':');
                $k = strtolower(str_replace(' ', '', substr($part[$h], 0, $pos)));
                $v = trim(substr($part[$h], ($pos + 1)));
            } else {
                $k = 'status';
                $v = explode(' ', $part[$h]);
                $v = $v[1];
            }
            if ($k == 'set-cookie') {
                $out['cookies'][] = $v;
            } elseif ($k == 'content-type') {
                if (($cs = strpos($v, ';')) !== false) {
                    $out[$k] = substr($v, 0, $cs);
                } else {
                    $out[$k] = $v;
                }
            } else {
                $out[$k] = $v;
            }
        }

        return $out;
    }

    public function _decode_body($info, $str, $eol = "\r\n")
    {
        $tmp = $str;
        $add = strlen($eol);
        if (isset($info['transfer-encoding']) && $info['transfer-encoding'] == 'chunked') {
            do {
                $tmp = ltrim($tmp);
                $pos = strpos($tmp, strval($eol));
                $len = hexdec(substr($tmp, 0, $pos));
                if (isset($info['content-encoding'])) {
                    $str .= gzinflate(substr($tmp, ($pos + $add + 10), $len));
                } else {
                    $str .= substr($tmp, ($pos + $add), $len);
                }
                $tmp = substr($tmp, ($len + $pos + $add));
                $check = trim($tmp);
            } while (!empty($check));
        } elseif (isset($info['content-encoding'])) {
            $str = gzinflate(substr($tmp, 10));
        } else {
            $str = $tmp;
        }

        return $str;
    }

    /**
     * The only purpose of this function is to send the message to the server, read the server answer,
     * discard the header and return the other content.
     *
     * @param string $url        the server url
     * @param string $port       the server port
     * @param string $get_params the get_params
     *
     * @return json
     */
    public function _sendRequest($url, $port, $get_params)
    {
        $json_response = '';
        $tmp_url = parse_url($url);

        if (($io = fsockopen($tmp_url['host'], $port, $errno, $errstr, _DIMDIM_STREAM_TIMEOUT)) !== false) {
            socket_set_timeout($io, _DIMDIM_STREAM_TIMEOUT);

            $send = 'GET /' . $get_params . " HTTP/1.1\r\n";
            $send .= 'Host: ' . $tmp_url['host'] . "\r\n";
            $send .= "User-Agent: PHP Script\r\n";
            $send .= 'Accept: text/xml,application/xml,application/xhtml+xml,';
            $send .= 'text/html;q=0.9,text/plain;q=0.8,video/x-mng,image/png,';
            $send .= "image/jpeg,image/gif;q=0.2,text/css,*/*;q=0.1\r\n";
            $send .= "Accept-Language: en-us, en;q=0.50\r\n";
            $send .= "Accept-Encoding: gzip, deflate, compress;q=0.9\r\n";
            $send .= "Connection: Close\r\n\r\n";

            fputs($io, $send);
            $header = '';
            do {
                $header .= fgets($io, 4096);
            } while (strpos($header, "\r\n\r\n") === false);
            $info = $this->_decode_header($header);
            $body = '';
            while (!feof($io)) {
                $body .= fread($io, 8192);
            }
            fclose($io);

            $json_response = $this->_decode_body($info, $body);

            echo $json_response;
        }

        return $json_response;
    }

    //--- NEW API UPDATE ---------------------------------------------------------

    public function _api_request($service, $method, $params, $parname = false)
    {
        require_once _base_ . '/lib/lib.json.php';
        require_once _base_ . '/lib/lib.fsock_wrapper.php';
        $server = FormaLms\lib\Get::sett('dimdim_server', false);
        $output = false;
        $_parname = ($parname ? $parname . '=' : '');
        if ($server && $service && $method) {
            $url = 'http://' . $server . '/api/' . $service . '/' . $method;

            $json = new Services_JSON();
            $fsock = new FSock();

            //check user login
            if ($service != 'auth' /*&& $method != 'login' && $method != 'verify'*/) {
                $auth_code = $this->get_auth_code();
                if (!$auth_code) {
                    //make login
                    $auth_code = $this->api_login();
                } else {
                    //verify if login is valid
                    if (!$this->api_verify()) {
                        $auth_code = $this->api_login();
                    }
                }

                if ($auth_code) {
                    $other_header = [
                        _DIMDIM_AUTH_CODE => $auth_code,
                        'Content-type' => 'application/x-www-form-urlencoded',
                    ];
                    $post = $_parname . urlencode($json->encode($params));
                    $res_json = $fsock->post_request($url, FormaLms\lib\Get::sett('dimdim_port', '80'), $post, $other_header);
                    if ($res_json) {
                        $output = $json->decode($res_json);
                    }
                }
            } else {
                $post = $_parname . urlencode($json->encode($params));
                $other_header = ['Content-type' => 'application/x-www-form-urlencoded'];
                if ($method != 'login') {
                    $other_header[_DIMDIM_AUTH_CODE] = $this->get_auth_code();
                }
                $res_json = $fsock->post_request($url, FormaLms\lib\Get::sett('dimdim_port', '80'), $post, $other_header);
                if ($res_json) {
                    $output = $json->decode($res_json);
                }
            }
        }

        return $output;
    }

    public function get_auth_code()
    {
        if ($this->session->get(_DIMDIM_AUTH_CODE)) {
            return $this->session->get(_DIMDIM_AUTH_CODE);
        }

        return false;
    }

    public function api_login()
    {
        $params = new stdClass();
        $params->account = FormaLms\lib\Get::sett('dimdim_user', '');
        $params->password = FormaLms\lib\Get::sett('dimdim_password', '');
        $params->group = 'all';
        $res = $this->_api_request('auth', 'login', $params, 'request');
        $output = false;
        if ($res->result) {
            $auth_code = $res->response->authToken;
            $this->session->set(_DIMDIM_AUTH_CODE, $auth_code);
            $this->session->set(_DIMDIM_AUTH_DATA, $res->response);
            $this->session->save();
            $output = $auth_code;
        }

        return $auth_code;
    }

    public function api_verify()
    {
        $params = new stdClass();
        $params->authToken = $this->get_auth_code();
        $params->account = FormaLms\lib\Get::sett('dimdim_user', '');
        $params->password = FormaLms\lib\Get::sett('dimdim_password', '');
        $params->group = 'all';
        $res = $this->_api_request('auth', 'verify', $params, 'data');
        if ($res && $res->result) {
            return true;
        }

        return false;
    }

    public function api_logout()
    {
        $params = new stdClass();
        $params->authToken = $this->get_auth_code();
        $params->account = FormaLms\lib\Get::sett('dimdim_user', '');
        $params->password = FormaLms\lib\Get::sett('dimdim_password', '');
        $params->group = 'all';

        return $this->_api_request('auth', 'logout', $params, 'data');
    }

    public function api_start_meeting($idConference, $user_email, $display_name, $confkey, $audiovideosettings, $maxmikes, $maxparticipants, $extra_conf)
    {
        $params = new stdClass();

        $params->ClientId = ''; //Optional - Provides the value of client ID if specifically assigned
        $params->account = FormaLms\lib\Get::sett('dimdim_user', ''); //Optional - Defines the user ID with which the registered Dimdim user will start a meeting groupName Optional all Defines group name, default is all
        $params->roomName = $display_name; //Optional - default - Defines Room name default is “default” agenda Optional Agenda of the meeting
        $params->meetingName = $display_name; //Optional - The name of the Meeting. Default is “From Third party Portal” displayName Optional This is to set the display name of host
        $params->joinEmailRequired = false; //Optional - true/false - Enables you to allow the attendees to join the meeting only on entering their email addresses; If it is set to true then joining the meeting without providing the email is disabled. Default is set to false audioVideo Optional av/audio/video/none Defines the audio and video settings av – Audio Video Allowed none – Audio-Video Disabled audio – Audio Only video – Video Only
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

        $res = $this->_api_request('conf', 'start_meeting', $params, 'data');

        if ($res) {
            if (!$res->result) {
                return false;
            }

            return $res;
        }

        return false;
    }

    public function api_schedule_meeting($idConference, $user_email, $display_name, $confkey, $audiovideosettings, $maxmikes, $maxparticipants, $startdate, $starthour, $startminute, $duration, $extra_conf)
    {
        $query = 'SELECT * FROM ' . $GLOBALS['prefix_scs'] . "_room WHERE id = '" . $idConference . "'";
        $re_room = $this->_query($query);
        $room = $this->nextRow($re_room);

        $params = new stdClass();
        $params->enterpriseName = 'dimdim';
        $params->groupName = 'all';
        $params->accountName = FormaLms\lib\Get::sett('dimdim_user', '');
        $params->roomName = 'default';
        $params->startDate = date('M j, Y', fromDatetimeToTimestamp($startdate));
        $params->startHour = ($starthour > 12 ? $starthour - 12 : $starthour) . '';
        $params->startMinute = '' . $startminute;
        $params->timeAMPM = ($starthour > 12 ? 'PM' : 'AM');
        //$params->agenda = (string)$room['name'];
        $params->meetingName = (string) $room['name'];
        $params->displayName = 'Fabio';
        $params->meetingRecurrance = 'SINGLE_EVENT'; // SINGLE_EVENT, DAILY, WEEKLY, MON_DATE

        $params->lobbyEnabled = $extra_conf['lobbyEnabled'];
        //$params->lobbyEnabled = $extra_conf['display_phone_info'];
        //$params->lobbyEnabled = $extra_conf['show_part_list'];
        $params->privateChatEnabled = $extra_conf['privateChatEnabled'];
        $params->publicChatEnabled = $extra_conf['publicChatEnabled'];
        $params->screenShareEnabled = $extra_conf['screenShareEnabled'];
        //$params->lobbyEnabled = $extra_conf['meeting_assistant_visibility'];
        $params->autoAssignMikeOnJoin = $extra_conf['autoAssignMikeOnJoin'];
        $params->whiteboardEnabled = $extra_conf['whiteboardEnabled'];
        $params->documentSharingEnabled = $extra_conf['documentSharingEnabled'];
        //$params->lobbyEnabled = $extra_conf['enable_web_sharing'];
        $params->recordingEnabled = $extra_conf['recordingEnabled'];
        //$params->lobbyEnabled = $extra_conf['allow_attendees_invitation'];
        $params->autoHandsFreeOnAVLoad = $extra_conf['autoHandsFreeOnAVLoad'];
        $params->joinEmailRequired = $extra_conf['joinEmailRequired'];

        $res = $this->_api_request('prtl', 'create_schedule', $params, 'request');

        exit();

        if ($res) {
            if (!$res->result) {
                return false;
            }

            return $res;
        }

        return false;
    }

    public function api_delete_schedule($id_conference)
    {
        $query = 'SELECT * FROM ' . $this->_getRoomTable() . " WHERE idConference = '" . $idConference . "'";
        $res = $this->_query($query);
        $info = $this->nextRow($res);

        require_once _adm_ . '/lib/lib.json.php';
        $json = new Services_JSON();
        $info_decoded = $json->decode($info['schedule_info']);

        $params = new stdClass();

        $params->account = FormaLms\lib\Get::sett('dimdim_user', ''); //Optional Defines the user ID with which the registered Dimdim user will start a meeting
        $params->groupName = 'all'; //Optional all Defines group name, default is “all”
        //$params->roomName = $name; //Optional default Defines Room name
        $params->scheduleId = $info_decoded->scheduleId; //Mandatory

        $res = $this->_api_request('prtl', 'delete_schedule', $params, 'data');

        if ($res) {
            if (!$res->result) {
                return false;
            }

            return $res;
        }

        return false;
    }

    public function api_check_meeting()
    {
        $params = new stdClass();

        //$params->
    }

    public function api_join_meeting()
    {
        $params = new stdClass();
        /*
                //$params->
                $params->ClientId //optional - Provides the value of client ID if specifically assigned account Optional Defines the user ID with which the registered Dimdim user has started a meeting which attendee wants to join groupName Optional all Defines group name
                $params->roomName //Optional - default - Defines Room name
                $params->displayName //optional Display name of the user when he joins the meeting
                $params->meetingKey //optional
        */
    }

    public function api_leave_meeting()
    {
        $params = new stdClass();

        //$params->
    }
}
