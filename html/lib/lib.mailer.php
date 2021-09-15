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

//require_once(_base_.'/addons/phpmailer/language/phpmailer.lang-en.php'); // not need for phpmailer 5.2.


//property name: multisending mode
define("MAIL_MULTIMODE", "multimode");
//multisending properties
define("MAIL_SINGLE", "single");
define("MAIL_CC", "cc");
define("MAIL_BCC", "bcc");

define("MAIL_RECIPIENTSCC", "recipientscc");
define("MAIL_RECIPIENTSBCC", "recipientsbcc");

define("MAIL_WORDWRAP", "wordwrap");
define("MAIL_CHARSET", "charset");
define("MAIL_HTML", "is_html");
define("MAIL_SINGLETO", "singleto");

//property name: use or not acl names (taken from DB, slower if used)
define("MAIL_SENDER_ACLNAME", "use_sender_aclname");
define("MAIL_RECIPIENT_ACLNAME", "use_recipient_aclname");
define("MAIL_REPLYTO_ACLNAME", "use_replyto_aclname");

//property name: reply to parameters
define("MAIL_REPLYTO", "replyto");

//specify if class properties should be reset after sending
define("MAIL_RESET", "reset");


class DoceboMailer extends PHPMailer
{
    //internal $aclManager instance
    var $aclManager;


    var $utf8_trans_tbl; //Utf-8 translation table

    //var $reset_to_default = true;

    //default config for phpmailer, to set any time we send a mail, except for user-defined params
    private $default_conf = [];

    //the constructor
    function __construct()
    {
        $this->aclManager = new DoceboACLManager();

        //set initial default value
        $this->ResetToDefault();

        $this->default_conf = [
            MAIL_MULTIMODE => MAIL_SINGLE,
            MAIL_SENDER_ACLNAME => Get::sett('use_sender_aclname', false),
            MAIL_RECIPIENTSCC => Get::sett('send_cc_for_system_emails', ''),
            MAIL_RECIPIENTSBCC => Get::sett('send_ccn_for_system_emails', ''),
            MAIL_RECIPIENT_ACLNAME => false,
            MAIL_REPLYTO_ACLNAME => false,
            MAIL_HTML => true,
            MAIL_WORDWRAP => 0,
            MAIL_CHARSET => 'Utf-8',
            MAIL_SINGLETO => true,
        ];
    }


    //return instance of the class
    static function getInstance()
    {
        if (!isset($GLOBALS['mailer'])) {
            $GLOBALS['mailer'] = new DoceboMailer();
        }
        return $GLOBALS['mailer'];
    }


    //convert html into plain txt in utf-8 avoiding the bug
    function ConvertToPlain_UTF8(&$html)
    {
        $allowedProtocols = ['http', 'https', 'ftp', 'mailto', 'color', 'background-color'];

        $config = HTMLPurifier_Config::createDefault();
        $allowed_elements = array();
        $allowed_attributes = array();

        $config->set('HTML.AllowedElements', $allowed_elements);
        $config->set('HTML.AllowedAttributes', $allowed_attributes);
        if ($allowedProtocols !== null) {
            $config->set('URI.AllowedSchemes', $allowedProtocols);
        }
        $purifier = new HTMLPurifier($config);
        $res = $purifier->purify($html);

        $res = str_replace('&amp;', '&', $res);

        return $res;
    }


    //restore default configuration after sending mail
    function ResetToDefault()
    {
        $this->From = '';
        $this->FromName = '';
        $this->CharSet = $this->default_conf[MAIL_CHARSET];
        $this->WordWrap = $this->default_conf[MAIL_WORDWRAP];
        $this->IsHTML($this->default_conf[MAIL_HTML]);
        $this->SingleTo = true;
        $this->Subject = '';
        $this->Body = '';
        $this->AltBody = '';
        //...

        //$this->ClearAddresses();
        //$this->ClearCCs();
        //$this->ClearBCCs();
        $this->ClearReplyTos();
        $this->ClearAllRecipients();
        $this->ClearAttachments();
        $this->ClearCustomHeaders();
    }

    //sendmail function
    function SendMail($sender, $recipients, &$subject, &$body, $attachments = false, $params = false)
    {
        $output = [];
        if (Get::cfg('demo_mode')) {
            $this->ResetToDefault();
            return false;
        }

        if (is_string($recipients)){
            $recipient = $recipients;
            $recipients = [];
            $recipients[] = $recipient;
        }

        if (is_string($attachments)){
            $attachment = $attachments;
            $attachments = [];
            $attachments[] = $attachment;
        }

        $params = array_merge($this->default_conf, $params);

        //check each time because global configuration may have changed since last call

        if (SmtpAdm::getInstance()->isUseSmtp()) {
            $this->IsSMTP();
            $this->Hostname = SmtpAdm::getInstance()->getHost();
            $this->Host = SmtpAdm::getInstance()->getHost();
            if (!empty(SmtpAdm::getInstance()->getPort())) {
                $this->Port = SmtpAdm::getInstance()->getPort();
            }
            $smtp_user = SmtpAdm::getInstance()->getUser();
            if (!empty($smtp_user)) {
                $this->Username = $smtp_user;
                $this->Password = SmtpAdm::getInstance()->getPwd();
                $this->SMTPAuth = true;
            } else {
                $this->SMTPAuth = false;
            }
            $this->SMTPSecure = SmtpAdm::getInstance()->getSecure();    // secure: '' , 'ssl', 'tsl'
            $this->SMTPAutoTLS = SmtpAdm::getInstance()->isAutoTls();
            $this->SMTPDebug = SmtpAdm::getInstance()->getDebug();    // debug level 0,1,2,3,...
            // Add To in mail header SMTP
        } else {
            $this->IsMail();
        }

        //configure sending address
        //----------------------------------------------------------------------------
        $this->From = $sender;
        if ($params[MAIL_SENDER_ACLNAME]) {
            $temp = $this->aclManager->getUserByEmail($sender);
            $this->FromName = $params[MAIL_SENDER_ACLNAME] !== true ? $params[MAIL_SENDER_ACLNAME] : $temp[ACL_INFO_FIRSTNAME] . ' ' . $temp[ACL_INFO_LASTNAME];
        }
        //----------------------------------------------------------------------------

        //configure attachments
        //----------------------------------------------------------------------------
        if (count($attachments) > 0) {
            foreach ($attachments as $value) {
                //maybe check if file exists, if necessary ...
                $this->addAttachment($value);
            }
        }

        //----------------------------------------------------------------------------

        //configure replyto(s)
        //----------------------------------------------------------------------------
        $replyTo = [];
        if (isset($params[MAIL_REPLYTO])) {
            //retrieve replyto(s) from params
            if (is_string($params[MAIL_REPLYTO])) {
                $replyTo[] = $params[MAIL_REPLYTO];
            } elseif (is_array($params[MAIL_REPLYTO])) {
                foreach ($params[MAIL_REPLYTO] as $value) {
                    $replyTo[] = $value;
                }
            }
        }
        foreach ($replyTo as $value) {

            if ($params[MAIL_REPLYTO_ACLNAME]) {
                $temp = $this->aclManager->getUserByEmail($value);
                $this->AddReplyTo($value, $temp[ACL_INFO_FIRSTNAME] . ' ' . $temp[ACL_INFO_LASTNAME]);
            } else {
                $this->AddReplyTo($value);
            }
        }
        //----------------------------------------------------------------------------

        if (isset($params[MAIL_CHARSET])) {
            $this->CharSet = $params[MAIL_CHARSET];
        }

        if (isset($params[MAIL_WORDWRAP])) {
            $this->WordWrap = $params[MAIL_WORDWRAP];
        }

        if (isset($params[MAIL_HTML])) {
            $this->IsHTML($params[MAIL_HTML]);
        }

        $this->Subject = $subject;
        if (isset($params[MAIL_HTML])) {

            $html = \appCore\Template\TwigManager::getInstance()->render('/mail/mail.html.twig', ['subject' => $subject, 'body' => $body],_templates_ . '/' . getTemplate() . '/layout');
            $this->msgHTML($html);

        } else {
            $this->Body = $body;
            $this->AltBody = $this->ConvertToPlain_UTF8($body);
        }


        // MAIL_RECIPIENTSCC
        if (isset($params[MAIL_RECIPIENTSCC])) {
            $arr_mail_recipientscc = explode(' ', $params[MAIL_RECIPIENTSCC]);
            foreach ($arr_mail_recipientscc as $user_mail_recipientscc) {
                try {
                    $this->addCC($user_mail_recipientscc);
                } catch (\PHPMailer\PHPMailer\Exception $e) {

                }
            }
        }

        // MAIL_RECIPIENTSBCC
        if (isset($params[MAIL_RECIPIENTSBCC])) {
            $arr_mail_recipientsbcc = explode(' ', $params[MAIL_RECIPIENTSBCC]);
            foreach ($arr_mail_recipientsbcc as $user_mail_recipientsbcc) {
                try {
                    $this->addBCC($user_mail_recipientsbcc);
                } catch (\PHPMailer\PHPMailer\Exception $e) {

                }
            }
        }

        // if(Get::sett('send_cc_for_system_emails', '') !== '' && filter_var(Get::sett('send_cc_for_system_emails'), FILTER_VALIDATE_EMAIL) !== false){
        if (Get::sett('send_cc_for_system_emails', '') !== '') {
            $arr_cc_for_system_emails = explode(' ', Get::sett('send_cc_for_system_emails'));
            foreach ($arr_cc_for_system_emails as $user_cc_for_system_emails) {
                try {
                    $this->addCC($user_cc_for_system_emails);
                } catch (\PHPMailer\PHPMailer\Exception $e) {
                }
            }
        }

        if (Get::sett('send_ccn_for_system_emails', '') !== '') {
            $arr_ccn_for_system_emails = explode(' ', Get::sett('send_ccn_for_system_emails'));
            foreach ($arr_ccn_for_system_emails as $user_ccn_for_system_emails) {
                try {
                    $this->addBCC($user_ccn_for_system_emails);
                } catch (\PHPMailer\PHPMailer\Exception $e) {

                }
            }
        }
        //----------------------------------------------------------------------------

        foreach ($recipients as $recipient) {

            if ($params[MAIL_RECIPIENT_ACLNAME]) {
                $temp = $this->aclManager->getUserByEmail($recipient);
                $name = $temp[ACL_INFO_FIRSTNAME] . ' ' . $temp[ACL_INFO_LASTNAME];
            } else {
                $name = $recipient;
            }

            try {
                switch ($params[MAIL_MULTIMODE]) {
                    case MAIL_CC     :
                        $this->AddCC($recipient, $name);
                        break;
                    case MAIL_BCC    :
                        $this->AddBCC($recipient, $name);
                        break;
                    case MAIL_SINGLE :
                    default:
                        $this->addAddress($recipient, $name);
                        break;
                }
            } catch (\PHPMailer\PHPMailer\Exception $e) {

            }

            $output[$recipient] = $this->send();
            $this->ClearAddresses();
        }


        //reset the class
        $this->ResetToDefault();
        return $output;
    }

    function isValidAddress($address)
    {
        if (preg_match("/[a-zA-Z0-9_-.+]+@[a-zA-Z0-9-]+.[a-zA-Z]+/", $address) > 0)
            return true;
        else
            return false;
    }

}
