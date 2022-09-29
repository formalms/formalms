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

ini_set('display_errors', 1);

class HelpdeskLmsController extends LmsController
{
    public function show()
    {
        $sender = FormaLms\lib\Get::sett('sender_event', '');
        $sender_name = FormaLms\lib\Get::sett('customer_help_name_from', false);
        $prefix_subj = FormaLms\lib\Get::sett('customer_help_subj_pfx');
        $ccn = FormaLms\lib\Get::sett('send_ccn_for_system_emails');
        $sendto = $_POST['sendto'];
        $usermail = $_POST['email'];
        $content = nl2br($_POST['msg']);
        $telefono = $_POST['telefono'];
        $username = $_POST['username'];
        $oggetto = $_POST['oggetto'];
        $copia = $_POST['copia'];
        $priorita = $_POST['priorita'];

        $help_req_resolution = $_POST['help_req_resolution'];
        $help_req_flash_installed = $_POST['help_req_flash_installed'];

        $subject = $prefix_subj ? '[' . $prefix_subj . '] ' . $oggetto : $oggetto;

        $headers = "From: \"$sender_name\" <" . strip_tags($sendto) . ">\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html;charset=utf-8 \r\n";
        if ($copia == 'on') {
            $headers .= 'Cc: ' . $usermail . "\r\n";
        }
        $headers .= 'Ccn: ' . $ccn . "\r\n";
        if ($priorita != 'on') {
            //SET EMAIL PRIORITY
            $headers .= "X-Priority: 1 (Higuest)\n";
            $headers .= "X-MSMail-Priority: High\n";
            $headers .= "Importance: High\n";
        }

        $br_char = '<br>';

        $msg = "<h2 style='font-weight:bold;border-bottom:1px dotted #ccc;'>" . $sender_name . "</h2>\r\n";
        $msg .= '<p><strong>' . Lang::t('_USER', 'standard') . ':</strong> ' . $username . "</p>\r\n";
        $msg .= '<p><strong>' . Lang::t('_EMAIL', 'menu') . ':</strong> ' . $usermail . "</p>\r\n";
        if ($telefono) {
            $msg .= '<p><strong>' . Lang::t('_PHONE', 'classroom') . ':</strong> ' . $telefono . "</p>\r\n";
        }
        $msg .= '<p><strong>' . Lang::t('_TEXTOF', 'menu') . ':</strong> ' . $content . "</p>\r\n";

        $id_course = $this->session->get('idCourse');
        if ($id_course) {
            $sql = "SELECT c.code, c.name FROM %lms_course AS c WHERE c.idCourse = $id_course";
            $query = sql_query($sql);

            if ($row = sql_fetch_object($query)) {
                $msg .= '<p><strong>' . Lang::t('_COURSE', 'standard') . ':</strong> [' . $row->code . '] - ' . $row->name . "</p>\r\n";
            }
        }

        $msg .= $br_char . '---------- CLIENT INFO -----------' . $br_char;
        // $msg .= "IP: " . $_SERVER['REMOTE_ADDR'] . $br_char;
        $msg .= 'USER AGENT: ' . $_SERVER['HTTP_USER_AGENT'] . $br_char;

        // $msg .= "OS: " . $result['platform'] . $br_char;
        // $msg .= "BROWSER: " .  $result['browser'] . " " . $result['version'] . $br_char;

        $msg .= 'RESOLUTION: ' . $help_req_resolution . $br_char;
        $msg .= 'FLASH: ' . $help_req_flash_installed . $br_char;

        $mailer = FormaLms\lib\Mailer\FormaMailer::getInstance();
        $mailer->addReplyTo(strip_tags($usermail));

        if ($mailer->SendMail($sender, [$sendto], $subject, $msg, [], [MAIL_HEADERS => $headers])) {
            echo 'true';
        } else {
            echo Lang::t('_NO_EMAIL_CONFIG', 'standard');
        }
        exit();
    }
}
