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


class HelpdeskLmsController extends LmsController {


	public function show() {

            $sendto   = $_POST['sendto'];
            $usermail = $_POST['email'];
            $content  = nl2br($_POST['msg']);
            $telefono = $_POST['telefono'];
            $username = $_POST['username'];
            $oggetto = $_POST['oggetto'];
            $copia = $_POST['copia'];
            $priorita = $_POST['priorita'];
            
            $help_req_resolution = $_POST['help_req_resolution'];
            $help_req_flash_installed = $_POST['help_req_flash_installed'];
            
            $subject  = $_POST['oggetto'];
            $headers  = "From: " . strip_tags($usermail) . "\r\n";
            $headers .= "Reply-To: ". strip_tags($usermail) . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html;charset=utf-8 \r\n";
            if($copia=="on")   $headers .= 'Cc: '.$usermail . "\r\n";
            if($priorita!="on"){
                //SET EMAIL PRIORITY
                 $headers .= "X-Priority: 1 (Higuest)\n"; 
                 $headers .= "X-MSMail-Priority: High\n"; 
                 $headers .= "Importance: High\n"; 
            }
            
            $br_char = "<br>";

           // $result = HelpdeskLmsController::parse_user_agent(null);
            
            $msg  = "<html><body style='font-family:Arial,sans-serif;'>";
            $msg .= "<h2 style='font-weight:bold;border-bottom:1px dotted #ccc;'>".Get::sett('customer_help_email')."</h2>\r\n";
            $msg .= "<p><strong>".Lang::t('_USER', 'standard').":</strong> ".$username."</p>\r\n";
            $msg .= "<p><strong>".Lang::t('_EMAIL', 'menu').":</strong> ".$usermail."</p>\r\n";
            $msg .= "<p><strong>".Lang::t('_PHONE', 'classroom').":</strong> ".$telefono."</p>\r\n";
            $msg .= "<p><strong>".Lang::t('_TEXTOF', 'menu').":</strong> ".$content."</p>\r\n";

            $msg .= $br_char . "---------- CLIENT INFO -----------" . $br_char;
            $msg .= "IP: " . $_SERVER['REMOTE_ADDR'] . $br_char;
            $msg .= "USER AGENT: " . $_SERVER['HTTP_USER_AGENT'] . $br_char;
        
           // $msg .= "OS: " . $result['platform'] . $br_char;
           // $msg .= "BROWSER: " .  $result['browser'] . " " . $result['version'] . $br_char;
        
            $msg .= "RESOLUTION: " .$help_req_resolution . $br_char;
            $msg .= "FLASH: ".$help_req_flash_installed . $br_char;

            $msg .= "</body></html>";


            if(@mail($sendto, $oggetto, $msg, $headers)) {
                echo "true";
            } else {
                echo "false";
            }
	}
    
    
  /**
     * Parses a user agent string into its important parts
     *
     * @author Jesse G. Donat <donatj@gmail.com>
     * @link https://github.com/donatj/PhpUserAgent
     * @link http://donatstudios.com/PHP-Parser-HTTP_USER_AGENT
     * @param string|null $u_agent User agent string to parse or null. Uses $_SERVER['HTTP_USER_AGENT'] on NULL
     * @return array an array with browser, version and platform keys
     */
    private function parse_user_agent($u_agent = null) {
        if (is_null($u_agent)) {
            if (isset($_SERVER['HTTP_USER_AGENT'])) {
                $u_agent = $_SERVER['HTTP_USER_AGENT'];
            } else {
                throw new InvalidArgumentException('parse_user_agent requires a user agent');
            }
        }

        $platform = null;
        $browser = null;
        $version = null;

        $empty = array('platform' => $platform, 'browser' => $browser, 'version' => $version);

        if (!$u_agent)
            return $empty;

        if (preg_match('/\((.*?)\)/im', $u_agent, $parent_matches)) {

            preg_match_all('/(?P<platform>BB\d+;|Android|CrOS|iPhone|iPad|Linux|Macintosh|Windows(\ Phone)?|Silk|linux-gnu|BlackBerry|PlayBook|Nintendo\ (WiiU?|3DS)|Xbox(\ One)?)
    (?:\ [^;]*)?
    (?:;|$)/imx', $parent_matches[1], $result, PREG_PATTERN_ORDER);

            $priority = array('Android', 'Xbox One', 'Xbox');
            $result['platform'] = array_unique($result['platform']);
            if (count($result['platform']) > 1) {
                if ($keys = array_intersect($priority, $result['platform'])) {
                    $platform = reset($keys);
                } else {
                    $platform = $result['platform'][0];
                }
            } elseif (isset($result['platform'][0])) {
                $platform = $result['platform'][0];
            }
        }

        if ($platform == 'linux-gnu') {
            $platform = 'Linux';
        } elseif ($platform == 'CrOS') {
            $platform = 'Chrome OS';
        }

        preg_match_all('%(?P<browser>Camino|Kindle(\ Fire\ Build)?|Firefox|Iceweasel|Safari|MSIE|Trident/.*rv|AppleWebKit|Chrome|CriOS|IEMobile|Opera|OPR|Silk|Lynx|Midori|Version|Wget|curl|NintendoBrowser|PLAYSTATION\ (\d|Vita)+)
    (?:\)?;?)
    (?:(?:[:/ ])(?P<version>[0-9A-Z.]+)|/(?:[A-Z]*))%ix', $u_agent, $result, PREG_PATTERN_ORDER);


    // If nothing matched, return null (to avoid undefined index errors)
        if (!isset($result['browser'][0]) || !isset($result['version'][0])) {
            return $empty;
        }

        $browser = $result['browser'][0];
        $version = $result['version'][0];

        $find = "_parse_ua_find";

        $key = 0;
        if ($browser == 'Iceweasel') {
            $browser = 'Firefox';
        } elseif ($find('Playstation Vita', $key, $result)) {
            $platform = 'PlayStation Vita';
            $browser = 'Browser';
        } elseif ($find('Kindle Fire Build', $key, $result) || $find('Silk', $key, $result)) {
            $browser = $result['browser'][$key] == 'Silk' ? 'Silk' : 'Kindle';
            $platform = 'Kindle Fire';
            if (!($version = $result['version'][$key]) || !is_numeric($version[0])) {
                $version = $result['version'][array_search('Version', $result['browser'])];
            }
        } elseif ($find('NintendoBrowser', $key, $result) || $platform == 'Nintendo 3DS') {
            $browser = 'NintendoBrowser';
            $version = $result['version'][$key];
        } elseif ($find('Kindle', $key, $result)) {
            $browser = $result['browser'][$key];
            $platform = 'Kindle';
            $version = $result['version'][$key];
        } elseif ($find('OPR', $key, $result)) {
            $browser = 'Opera Next';
            $version = $result['version'][$key];
        } elseif ($find('Opera', $key, $result)) {
            $browser = 'Opera';
            $find('Version', $key, $result);
            $version = $result['version'][$key];
        } elseif ($find('Midori', $key, $result)) {
            $browser = 'Midori';
            $version = $result['version'][$key];
        } elseif ($find('Chrome', $key, $result) || $find('CriOS', $key, $result)) {
            $browser = 'Chrome';
            $version = $result['version'][$key];
        } elseif ($browser == 'AppleWebKit') {
            if (($platform == 'Android' && !($key = 0))) {
                $browser = 'Android Browser';
            } elseif (strpos($platform, 'BB') === 0) {
                $browser = 'BlackBerry Browser';
                $platform = 'BlackBerry';
            } elseif ($platform == 'BlackBerry' || $platform == 'PlayBook') {
                $browser = 'BlackBerry Browser';
            } elseif ($find('Safari', $key, $result)) {
                $browser = 'Safari';
            }

            $find('Version', $key, $result);

            $version = $result['version'][$key];
        } elseif ($browser == 'MSIE' || strpos($browser, 'Trident') !== false) {
            if ($find('IEMobile', $key, $result)) {
                $browser = 'IEMobile';
            } else {
                $browser = 'MSIE';
                $key = 0;
            }
            $version = $result['version'][$key];
        } elseif ($key = preg_grep('/playstation \d/i', array_map('strtolower', $result['browser']))) {
            $key = reset($key);

            $platform = 'PlayStation ' . preg_replace('/[^\d]/i', '', $key);
            $browser = 'NetFront';
        }

        return array('platform' => $platform, 'browser' => $browser, 'version' => $version);
    }    
    
    
    private function _parse_ua_find( $search, &$key, &$result ) {
        $xkey = array_search(strtolower($search), array_map('strtolower', $result['browser']));
        if ($xkey !== false) {
            $key = $xkey;
            return true;
        }

        return false;
    }    
    
    
}