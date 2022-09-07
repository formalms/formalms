<?php
namespace appCore\models;
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

class SmtpAdm extends Model
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

    /**
     * @var int
     */
    protected $mailConfigId = 0;

    /**
     * @var SmtpAdm
     */
    private static $instance = null;


    public function __construct($mailConfigId = null)
    {
        parent::__construct();

        // \appCore\Events\DispatcherManager::addListener(\appCore\Events\Core\ConfigGetRegroupUnitsEvent::EVENT_NAME, function (\appCore\Events\Core\ConfigGetRegroupUnitsEvent $event) {
        //     if (self::isEnabledDatabase()) {
        //         $event->addGroupUnit(self::SMTP_GROUP, 'Smtp Settings');
        //     }
        // });

        $this->table = '%adm_mail_config_fields';
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

    private function fetchData(?int $mailConfigId)
    {
        if (self::isEnabledDatabase()) {

            if($mailConfigId) {
                $query_res = sql_query('SELECT * FROM ' . $this->table . ' WHERE mailConfigId =' . $mailConfigId);
            } else {
                $query_res = sql_query('SELECT * FROM ' . $this->table . ' WHERE mailConfigId = (SELECT id FROM %adm_mail_config WHERE system = 1))';
            }
            
            $rows = sql_num_rows($query_res);

            for ($i = 0; $i < $rows; ++$i) {
                $row = sql_fetch_assoc($query_res);

                dd($row);
                $property = $row['key'];

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
