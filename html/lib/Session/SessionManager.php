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

namespace Forma\lib\Session;

use Forma\lib\Get;
use Forma\lib\Serializer\FormaSerializer;
use Forma\lib\Session\Handlers\FilesystemHandler;
use Forma\lib\Session\Handlers\MemcachedHandler;
use Forma\lib\Session\Handlers\MongoDbHandler;
use Forma\lib\Session\Handlers\PdoHandler;
use Forma\lib\Session\Handlers\RedisHandler;
use \Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

\defined('IN_FORMA') or exit('Direct access is forbidden.');

class SessionManager
{
    public const FILESYSTEM = 'filesystem';
    public const MEMCACHED = 'memcached';
    public const PDO = 'pdo';
    //public const MONGO = 'mongo';
    public const REDIS = 'redis';

    private static ?SessionManager $instance = null;

    private Config $config;

    private ?Session $session = null;

    private $sessionHandler;

    public static function getInstance()
    {
        if (self::$instance === null) {
            $c = __CLASS__;
            self::$instance = new $c();
        }
        return self::$instance;
    }


    public function initSession(array $sessionConfig)
    {
        if (!$this->session) {
            try {
                $config = FormaSerializer::getInstance()->denormalize($sessionConfig, Config::class);
            } catch (\Exception $exception) {
                die($exception->getMessage());
            }

            $this->setConfig($config);

            switch ($this->config->getHandler()) {
                case self::MEMCACHED:
                    $this->sessionHandler = new MemcachedHandler($config);
                    break;
                case self::REDIS:
                    $this->sessionHandler = new RedisHandler($config);
                    break;
                case self::PDO:
                    $this->sessionHandler = new PdoHandler($config);
                    try {
                        $this->sessionHandler->createTable();
                    } catch (\PDOException $exception) {
                        // the table could not be created for some reason
                    }
                    break;
                /*case self::MONGO:
                    $this->sessionHandler = new MongoDbHandler($config);
                    break;*/
                case self::FILESYSTEM:
                default:
                    $this->sessionHandler = new FilesystemHandler($config);
                    break;
            }

            $sessionStorage = new NativeSessionStorage([], $this->sessionHandler);
            $this->session = new Session($sessionStorage);
            if (!$this->session->isStarted()) {
                $this->session->start();
            }
        }
    }

    /**
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * @param Config $config
     */
    public function setConfig(Config $config): void
    {
        $this->config = $config;
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }

    public function isSessionExpired()
    {

        $session_time = Get::sett('ttlSession', 3600);

        if (!$this->session->has('session_timeout')) {
            $this->session->set('session_timeout', time());
        }
        $session_time_passed = time() - $this->session->get('session_timeout');

        $this->session->set('session_timeout', time());
        $this->session->save();

        if ($session_time_passed > $session_time && $this->session->has('logged_in') && $this->session->get('logged_in')) {
            return true;
        }
        return false;
    }


}