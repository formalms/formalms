<?php

namespace FormaLms\lib\Mailer\Handlers;
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

use FormaLms\lib\Helpers\HelperTool;

defined('IN_FORMA') or exit('Direct access is forbidden.');

class SmtpHandler
{
    /**
     * @var string
     */
    protected $senderMailNotification;

    /**
     * @var string
     */
    protected $senderNameNotification;

    /**
     * @var string
     */
    protected $senderMailSystem;

    /**
     * @var string
     */
    protected $senderNameSystem;

    /**
     * @var string
     */
    protected $helperDeskMail;

    /**
     * @var string
     */
    protected $helperDeskName;

    /**
     * @var string
     */
    protected $helperDeskSubject;

    /**
     * @var string
     */
    protected $noreplyName;

    /**
     * @var string
     */
    protected $noreplyMail;

    /**
     * @var string
     */
    protected $active;

    /**
     * @var bool
     */
    protected $useSmtp = false;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var string
     */
    protected $port;

    /**
     * @var string
     */
    protected $secure;

    /**
     * @var bool
     */
    protected $autoTls = true;

    /**
     * @var string
     */
    protected $user;

    /**
     * @var string
     */
    protected $pwd;

    /**
     * @var string
     */
    protected $debug = 0;

    /**
     * @var int
     */
    protected $mailConfigId = 0;


    public function __construct($mailConfigId = null)
    {

        $this->mailConfigId = (int)$mailConfigId;
        $this->fetchData($mailConfigId);
    }

    /**
     * @return bool
     */
    public function isUseSmtp()
    {
        if ($this->useSmtp === 'on' || $this->useSmtp === 'true' || $this->useSmtp === true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return string
     */
    public function getSenderMailNotification()
    {
        return $this->senderMailNotification;
    }

    /**
     * @return string
     */
    public function getSenderNameNotification()
    {
        return $this->senderNameNotification;
    }

    /**
     * @return string
     */
    public function getSenderMailSystem()
    {
        return $this->senderMailSystem;
    }

    /**
     * @return string
     */
    public function getSenderNameSystem()
    {
        return $this->senderNameSystem;
    }

    /**
     * @return string
     */
    public function getHelperDeskMail()
    {
        return $this->helperDeskMail;
    }

    /**
     * @return string
     */
    public function getHelperDeskName()
    {
        return $this->helperDeskMail;
    }

    /**
     * @return string
     */
    public function getHelperDeskSubject()
    {
        return $this->helperDeskSubject;
    }

    /**
     * @return string
     */
    public function getNoreplyName()
    {
        return $this->noreplyName;
    }

    /**
     * @return string
     */
    public function getNoreplyMail()
    {
        return $this->noreplyMail;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        if ($this->active === '1' || $this->active === 'true' || $this->active === true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getSecure()
    {
        return $this->secure;
    }

    /**
     * @return bool
     */
    public function isAutoTls()
    {
        if ($this->autoTls === 'on' || $this->autoTls === 'true' || $this->autoTls === true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getDebug()
    {

        return (int)$this->debug;
    }

    public static function isEnabledDatabase()
    {
        $smtpConfigIsEnabled = \FormaLms\lib\Get::cfg('use_smtp_database');

        switch ($smtpConfigIsEnabled) {
            case 'off':
                return false;
                break;
            case 'on':
            case 'true':
            case true:
                return true;
                break;
            default:
                return false;
                break;
        }
    }

    private function fetchData(?int $mailConfigId)
    {

        if (self::isEnabledDatabase()) {


            if ($mailConfigId) {
                $query_res = sql_query('SELECT * FROM %adm_mail_configs_fields WHERE mailConfigId ="' . $mailConfigId . '"');
            } else {
                $query_res = sql_query('SELECT * FROM %adm_mail_configs_fields WHERE mailConfigId = (SELECT id FROM %adm_mail_configs WHERE system = "1")');
            }

            foreach ($query_res as $row) {

                $property = HelperTool::snakeToCamelCase($row['type']);

                $this->$property = $row['value'];

            }
        } else {
            $this->useSmtp = \FormaLms\lib\Get::cfg('use_smtp');
            $this->host = \FormaLms\lib\Get::cfg('smtp_host');
            $this->port = \FormaLms\lib\Get::cfg('smtp_port');
            $this->secure = \FormaLms\lib\Get::cfg('smtp_secure');
            $this->autoTls = \FormaLms\lib\Get::cfg('smtp_auto_tls');
            $this->user = \FormaLms\lib\Get::cfg('smtp_user');
            $this->pwd = \FormaLms\lib\Get::cfg('smtp_pwd');
            $this->debug = \FormaLms\lib\Get::cfg('smtp_debug', 0);
        }

    }
}
