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

final class SmtpAdm extends Model
{
    /**
     * @var string
     */
    protected $table;

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

    public const SMTP_GROUP = 14;

    /**
     * @var SmtpAdm
     */
    private static $instance = null;

    /**
     * @return SmtpAdm
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            $c = __CLASS__;
            self::$instance = new $c();
        }

        return self::$instance;
    }

    public function __construct()
    {
        parent::__construct();

        // \appCore\Events\DispatcherManager::addListener(\appCore\Events\Core\ConfigGetRegroupUnitsEvent::EVENT_NAME, function (\appCore\Events\Core\ConfigGetRegroupUnitsEvent $event) {
        //     if (self::isEnabledDatabase()) {
        //         $event->addGroupUnit(self::SMTP_GROUP, 'Smtp Settings');
        //     }
        // });

        $this->table = $GLOBALS['prefix_fw'] . '_setting';
        $this->fetchData();
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
    public function getPwd()
    {
        return $this->pwd;
    }

    /**
     * @return string
     */
    public function getDebug()
    {
        return $this->debug;
    }

    public static function isEnabledDatabase()
    {
        $smtpConfigIsEnabled = FormaLms\lib\Get::cfg('use_smtp_database');

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

    private function fetchData()
    {
        if (self::isEnabledDatabase()) {
            $query_res = sql_query('SELECT * FROM ' . $this->table . ' WHERE regroup =' . self::SMTP_GROUP);

            $rows = sql_num_rows($query_res);

            for ($i = 0; $i < $rows; ++$i) {
                $row = sql_fetch_assoc($query_res);

                $property = str_replace('smtp_', '', $row['param_name']);

                $propertyArr = explode('_', $property);

                $index = 0;
                foreach ($propertyArr as $value) {
                    if ($index == 0) {
                        $property = $value;
                    } else {
                        $property .= ucfirst($value);
                    }
                    ++$index;
                }

                $this->$property = $row['param_value'];
            }
        } else {
            $this->useSmtp = FormaLms\lib\Get::cfg('use_smtp');
            $this->host = FormaLms\lib\Get::cfg('smtp_host');
            $this->port = FormaLms\lib\Get::cfg('smtp_port');
            $this->secure = FormaLms\lib\Get::cfg('smtp_secure');
            $this->autoTls = FormaLms\lib\Get::cfg('smtp_auto_tls');
            $this->user = FormaLms\lib\Get::cfg('smtp_user');
            $this->pwd = FormaLms\lib\Get::cfg('smtp_pwd');
            $this->debug = FormaLms\lib\Get::cfg('smtp_debug', 0);
        }
    }
}
