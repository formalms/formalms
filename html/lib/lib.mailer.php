<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

//require_once(_base_.'/addons/phpmailer/language/phpmailer.lang-en.php'); // not need for phpmailer 5.2.

//property name: multisending mode
define('MAIL_MULTIMODE', 'multimode');
//multisending properties
define('MAIL_SINGLE', 'single');
define('MAIL_CC', 'cc');
define('MAIL_BCC', 'bcc');

define('MAIL_RECIPIENTSCC', 'recipientscc');
define('MAIL_RECIPIENTSBCC', 'recipientsbcc');

define('MAIL_WORDWRAP', 'wordwrap');
define('MAIL_CHARSET', 'charset');
define('MAIL_HTML', 'is_html');

//property name: use or not acl names (taken from DB, slower if used)
define('MAIL_SENDER_ACLNAME', 'use_sender_aclname');
define('MAIL_RECIPIENT_ACLNAME', 'use_recipient_aclname');
define('MAIL_REPLYTO_ACLNAME', 'use_replyto_aclname');

//property name: reply to parameters
define('MAIL_REPLYTO', 'replyto');

define('MAIL_HEADERS', 'headers');

//specify if class properties should be reset after sending
define('MAIL_RESET', 'reset');

class FormaMailer extends PHPMailer\PHPMailer\PHPMailer
{
    /** @var FormaMailer */
    private static $instance = null;

    private DoceboACLManager $aclManager;

    //default config for phpmailer, to set any time we send a mail, except for user-defined params
    private array $config;

    private string $mailTemplate = 'mail.html.twig';

    //the constructor
    public function __construct()
    {
        $this->aclManager = new DoceboACLManager();

        $this->config = [
            MAIL_MULTIMODE => MAIL_SINGLE,
            MAIL_SENDER_ACLNAME => FormaLms\lib\Get::sett('use_sender_aclname', false),
            MAIL_RECIPIENTSCC => FormaLms\lib\Get::sett('send_cc_for_system_emails', ''),
            MAIL_RECIPIENTSBCC => FormaLms\lib\Get::sett('send_ccn_for_system_emails', ''),
            MAIL_RECIPIENT_ACLNAME => false,
            MAIL_REPLYTO_ACLNAME => false,
            MAIL_HTML => true,
            MAIL_WORDWRAP => 0,
            MAIL_CHARSET => 'Utf-8',
        ];
        //set initial default value
        $this->ResetToDefault();
        $this->addDefaultMailPaths();
        parent::__construct();
    }

    /**
     * @return FormaMailer|mixed
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new FormaMailer();
        }

        return self::$instance;
    }

    public function setMailTemplate(string $mailTemplate): void
    {
        $this->mailTemplate = $mailTemplate;
    }

    private function addDefaultMailPaths()
    {
        $defaultPaths = [
            _adm_ . '/views/mail',
            _lms_ . '/views/mail',
            _lms_ . '/admin/views/mail',
            _templates_ . '/' . getTemplate() . '/layout/mail',
        ];

        if (getTemplate() !== 'standard') {
            $defaultPaths[] = _templates_ . '/standard/layout/mail';
        }

        foreach ($defaultPaths as $path) {
            if (file_exists($path)) {
                FormaLms\appCore\Template\TwigManager::getInstance()->addPathInLoader($path);
            }
        }
    }

    //convert html into plain txt in utf-8 avoiding the bug
    private function ConvertToPlain_UTF8(&$html)
    {
        $allowedProtocols = ['http', 'https', 'ftp', 'mailto', 'color', 'background-color'];

        $config = HTMLPurifier_Config::createDefault();
        $allowed_elements = [];
        $allowed_attributes = [];

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
    public function ResetToDefault()
    {
        $this->From = '';
        $this->FromName = '';
        $this->CharSet = $this->config[MAIL_CHARSET];
        $this->WordWrap = $this->config[MAIL_WORDWRAP];
        $this->IsHTML($this->config[MAIL_HTML]);
        $this->Subject = '';
        $this->Body = '';
        $this->AltBody = '';
        $this->msgHTML('');

        $this->ClearAddresses();
        $this->ClearCCs();
        $this->ClearBCCs();
        $this->ClearReplyTos();
        $this->ClearAllRecipients();
        $this->ClearAttachments();
        $this->ClearCustomHeaders();
    }

    /**
     * @return array|false
     *
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function SendMail(string $sender, array $recipients, string $subject, string $body, array $attachments = [], array $params = [])
    {
        $output = [];
        if (FormaLms\lib\Get::cfg('demo_mode')) {
            $this->ResetToDefault();

            return false;
        }

        $params = array_merge($this->config, $params);

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
            $eventResponse = Events::trigger('core.mail.template.rendering',
                [
                    'layout' => $this->mailTemplate,
                    'layoutPath' => '',
                    'subject' => $subject,
                    'body' => $body,
                    'otherParams' => [],
                ]
            );

            try {
                if (!empty($eventResponse['path'])) {
                    FormaLms\appCore\Template\TwigManager::getInstance()->addPathInLoader($eventResponse['layoutPath']);
                }

                $html = FormaLms\appCore\Template\TwigManager::getInstance()->render($eventResponse['layout'],
                    [
                        'subject' => $eventResponse['subject'],
                        'body' => $eventResponse['body'],
                        'otherParams' => $eventResponse['otherParams'],
                    ]
                );
            } catch (\Exception $exception) {
                $html = $body;
            }

            $eventResponse = Events::trigger('core.mail.template.rendered',
                [
                    'html' => $html,
                    'subject' => $subject,
                    'body' => $body,
                    'otherParams' => $eventResponse['otherParams'],
                ]
            );

            $this->msgHTML($eventResponse['html']);
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

        // if(FormaLms\lib\Get::sett('send_cc_for_system_emails', '') !== '' && filter_var(FormaLms\lib\Get::sett('send_cc_for_system_emails'), FILTER_VALIDATE_EMAIL) !== false){
        if (FormaLms\lib\Get::sett('send_cc_for_system_emails', '') !== '') {
            $arr_cc_for_system_emails = $this->getEmailListFromString(FormaLms\lib\Get::sett('send_cc_for_system_emails'));
            foreach ($arr_cc_for_system_emails as $user_cc_for_system_emails) {
                try {
                    $this->addCC($user_cc_for_system_emails);
                } catch (\PHPMailer\PHPMailer\Exception $e) {
                }
            }
        }

        if (FormaLms\lib\Get::sett('send_ccn_for_system_emails', '') !== '') {
            $arr_ccn_for_system_emails = $this->getEmailListFromString(FormaLms\lib\Get::sett('send_ccn_for_system_emails'));
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

            $sent = $this->send();

            Events::trigger('core.mail.sent',
                [
                    'sender' => $sender,
                    'recipient' => $recipient,
                    'sent' => $sent,
                ]
            );
            $output[$recipient] = $sent;
            $this->ClearAddresses();
        }

        //reset the class
        $this->ResetToDefault();

        return $output;
    }

    private function getEmailListFromString($emails)
    {
        $delimiters = [' ', ',', '|'];

        $emails = str_replace($delimiters, $delimiters[0], $emails); // 'foo. bar. baz.'

        $emailsArray = explode($delimiters[0], $emails);
        if (is_array($emailsArray) && count($emailsArray) > 1) {
            return $emailsArray;
        }

        return $emailsArray ?: [];
    }
}
