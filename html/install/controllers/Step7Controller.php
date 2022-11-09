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

require_once dirname(__FILE__) . '/StepController.php';

require_once __DIR__ . '/../../vendor/autoload.php';

class Step7Controller extends StepController
{
    public $step = 7;

    public function ajax_validate()
    {
        $err = 0;
        $res = ['success' => false, 'err' => [], 'ok' => [], 'msg' => ''];

        $op = FormaLms\lib\Get::pReq('op', DOTY_STRING);
        // ---

        $use_smtp_database = FormaLms\lib\Get::pReq('use_smtp_database', DOTY_STRING);
        $use_smtp = FormaLms\lib\Get::pReq('active', DOTY_STRING);
        $smtp_host = FormaLms\lib\Get::pReq('host', DOTY_STRING);
        $smtp_port = FormaLms\lib\Get::pReq('port', DOTY_STRING);
        $smtp_secure = FormaLms\lib\Get::pReq('secure', DOTY_STRING);
        $smtp_auto_tls = FormaLms\lib\Get::pReq('auto_tls', DOTY_STRING);
        $smtp_user = FormaLms\lib\Get::pReq('user', DOTY_STRING);
        $smtp_pwd = FormaLms\lib\Get::pReq('password', DOTY_STRING);

        if ($use_smtp === '1') {
            if (!$this->checkConnection($smtp_host, $smtp_port, $smtp_secure, $smtp_auto_tls, $smtp_user, $smtp_pwd)) {
                ++$err;
                array_push($res['err'], 'smtp_host', 'smtp_port', 'smtp_secure', 'smtp_auto_tls', 'smtp_user', 'smtp_pwd');
                array_push($res['ok'], 'use_smtp');
                $res['msg'] = Lang::t('_CANT_CONNECT_SMTP');
            }
        }

        $res['success'] = ($err > 0 ? false : true);

        $this->ajax_out($res);
    }

    public function checkConnection($smtp_host, $smtp_port, $smtp_secure, $smtp_auto_tls, $smtp_user, $smtp_pwd)
    {
        $mail = new PHPMailer\PHPMailer\PHPMailer();

        $mail->Host = $smtp_host;
        $mail->Port = $smtp_port;

        if (!empty($smtp_user)) {
            $mail->SMTPAuth = true;     // turn on SMTP authentication
            $mail->Username = $smtp_user;  // SMTP username
            $mail->Password = $smtp_pwd; // SMTP password
        } else {
            $mail->SMTPAuth = false;     // turn on SMTP authentication
        }

        $mail->SMTPSecure = $smtp_secure;
        $mail->SMTPAutoTLS = $smtp_auto_tls === '1';

        if ($mail->smtpConnect()) {
            $mail->smtpClose();

            return true;
        } else {
            return false;
        }
    }

    public function validate()
    {
        $smtp_info = FormaLms\lib\Get::pReq('mail_info', DOTY_MIXED);

        if ($smtp_info['use_smtp_database'] === '1') {
            $this->saveSettingsToDatabase($smtp_info);

            $smtp_info['use_smtp_database'] = '1';
            $this->session->set('smtp_info', $smtp_info);
        } else {
            $this->session->set('smtp_info', $smtp_info);
        }
        $this->session->save();

        $this->saveConfig();

        return true;
    }

    private function saveConfig()
    {
        // ----------- Generating config file -----------------------------
        $config = '';
        $fn = _installer_ . '/data/config_template.php';

        $config = generateConfig($fn);

        $save_fn = _base_ . '/config.php';
        $saved = false;
        if (is_writeable($save_fn)) {
            $handle = fopen($save_fn, 'w');
            if (fwrite($handle, $config)) {
                $saved = true;
            }
            fclose($handle);

            @chmod($save_fn, 0644);
        }

        $this->session->set('config_saved', $saved);
        $this->session->save();
    }

    private function saveSettingsToDatabase($smtpInfo)
    {
        $dbInfo = $this->session->get('db_info');
        DbConn::getInstance(false,
            [
                'db_type' => $dbInfo['db_type'],
                'db_host' => $dbInfo['db_host'],
                'db_user' => $dbInfo['db_user'],
                'db_pass' => $dbInfo['db_pass'],
                'db_name' => $dbInfo['db_name'],
            ]
        );


        $queryInsert = 'INSERT INTO'
        . ' %adm_mail_configs (title, system) VALUES ("DEFAULT", "1")';

        $result = sql_query($queryInsert);

        $mailConfigId = sql_insert_id();

        foreach($smtpInfo as $type => $value) {
            $queryInsert = 'INSERT INTO'
            . ' %adm_mail_configs_fields (mailConfigId, type, value) VALUES ("'. $mailConfigId .'", "'. $type .'", "'. $value .'")';
            $result = sql_query($queryInsert);
        }


    }
}
