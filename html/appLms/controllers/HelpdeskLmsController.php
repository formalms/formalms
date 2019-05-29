<?php defined("IN_FORMA") or die('Direct access is forbidden.');

ini_set('display_errors',1);

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

	        $sender = Get::sett('customer_help_email', '');
            $prefix_subj = Get::sett('customer_help_subj_pfx');
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
            
            $subject  = $prefix_subj ? '['.$prefix_subj.'] '.$oggetto : $oggetto;

            $headers  = "From: " . strip_tags($sendto) . "\r\n";
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

            
            $msg  = "<html><body style='font-family:Arial,sans-serif;'>";
            $msg .= "<h2 style='font-weight:bold;border-bottom:1px dotted #ccc;'>".Get::sett('customer_help_email')."</h2>\r\n";
            $msg .= "<p><strong>".Lang::t('_USER', 'standard').":</strong> ".$username."</p>\r\n";
            $msg .= "<p><strong>".Lang::t('_EMAIL', 'menu').":</strong> ".$usermail."</p>\r\n";
            $msg .= "<p><strong>".Lang::t('_PHONE', 'classroom').":</strong> ".$telefono."</p>\r\n";
            $msg .= "<p><strong>".Lang::t('_TEXTOF', 'menu').":</strong> ".$content."</p>\r\n";

            $msg .= $br_char . "---------- CLIENT INFO -----------" . $br_char;
            // $msg .= "IP: " . $_SERVER['REMOTE_ADDR'] . $br_char;
            $msg .= "USER AGENT: " . $_SERVER['HTTP_USER_AGENT'] . $br_char;
        
           // $msg .= "OS: " . $result['platform'] . $br_char;
           // $msg .= "BROWSER: " .  $result['browser'] . " " . $result['version'] . $br_char;
        
            $msg .= "RESOLUTION: " .$help_req_resolution . $br_char;
            $msg .= "FLASH: ".$help_req_flash_installed . $br_char;

            $msg .= "</body></html>";

            require_once(Forma::inc(_base_.'/lib/lib.mailer.php'));
            $mailer = DoceboMailer::getInstance();
            $mailer->addReplyTo(strip_tags($usermail));

            if($mailer->SendMail($sender, $sendto, $subject, $msg, false, $headers)) {
                echo "true";
            } else {
                echo Lang::t('_NO_EMAIL_CONFIG', 'standard');
            }
            die();
            
	}  
}

?>