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

    const SMTP_GROUP = 14;

    /**
     * @var SmtpAdm null
     */
    private static $instance = null;


    public static function getInstance()
    {
        if (self::$instance == null) {
            $c = __CLASS__;
            self::$instance = new $c;
        }

        return self::$instance;
    }

    public function __construct()
    {
        parent::__construct();

        \appCore\Events\DispatcherManager::addListener(\appCore\Events\Core\ConfigGetRegroupUnitsEvent::EVENT_NAME, function (\appCore\Events\Core\ConfigGetRegroupUnitsEvent $event) {
            if (self::isEnabledDatabase()) {
                $event->addGroupUnit(self::SMTP_GROUP, 'Smtp Settings');
            }
        });

        $this->table = $GLOBALS['prefix_fw'] . '_setting';
        $this->fetchData();
    }

    /**
     * @return bool
     */
    public function isUseSmtp()
    {
        return $this->useSmtp === 'on';
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
        return $this->autoTls === 'on';
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
        $smtpConfigIsEnabled = Get::cfg('use_smtp_database');

        if ($smtpConfigIsEnabled === 'on') {
            return true;
        }
        return false;
    }

    private function fetchData()
    {
        if (self::isEnabledDatabase()) {

            $query_res = sql_query("SELECT * FROM " . $this->table . " WHERE regroup =" . self::SMTP_GROUP);

            $rows = sql_num_rows($query_res);

            for ($i = 0; $i < $rows; $i++) {

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
                    $index++;
                }

                $this->$property = $row['param_value'];

            }

        } else {

            $this->useSmtp = Get::cfg('use_smtp');
            $this->host = Get::cfg('smtp_host');
            $this->port = Get::cfg('smtp_port');
            $this->secure = Get::cfg('smtp_secure');
            $this->autoTls = Get::cfg('smtp_auto_tls');
            $this->user = Get::cfg('smtp_user');
            $this->pwd = Get::cfg('smtp_pwd');
            $this->debug = Get::cfg('smtp_debug', 0);


        }
    }
}

?>
