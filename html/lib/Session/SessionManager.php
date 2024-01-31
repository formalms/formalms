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

namespace FormaLms\lib\Session;

use FormaLms\lib\Serializer\FormaSerializer;
use FormaLms\lib\Session\Handlers\FilesystemHandler;
use FormaLms\lib\Session\Handlers\MemcachedHandler;
use FormaLms\lib\Session\Handlers\PdoHandler;
use FormaLms\lib\Session\Handlers\RedisHandler;
use Symfony\Component\HttpFoundation\Session\Session;
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

    private SessionConfig $config;

    private ?Session $session = null;

    private $sessionHandler;
    private const IPV4_LOCALHOST = '127.0.0.1';
    private const IPV6_LOCALHOST = '::1';

    /**
     * @return SessionManager
     */
    public static function getInstance() : SessionManager
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
                $config = FormaSerializer::getInstance()->denormalize($sessionConfig, SessionConfig::class);
            } catch (\Exception $exception) {
                exit($exception->getMessage());
            }

            $this->setConfig($config);

            $ttlSession = \FormaLms\lib\Get::sett('ttlSession', 0);

            if ($ttlSession > 0){
                $this->config->setLifetime($ttlSession);
            }

            ini_set('session.gc_maxlifetime', $this->config->getLifetime());
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', 1);

            switch ($this->config->getHandler()) {
                case self::MEMCACHED:
                    $this->sessionHandler = new MemcachedHandler($this->config);
                    break;
                case self::REDIS:
                    $this->sessionHandler = new RedisHandler($this->config);
                    break;
                case self::PDO:
                    $this->sessionHandler = new PdoHandler($this->config);
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
                    $this->sessionHandler = new FilesystemHandler($this->config);
                    break;
            }

            $sessionStorage = new NativeSessionStorage([], $this->sessionHandler);
            $this->session = new Session($sessionStorage);
            if ($_SERVER['SERVER_ADDR'] !== $this::IPV4_LOCALHOST && $_SERVER['SERVER_ADDR'] !== $this::IPV6_LOCALHOST) {
                $this->session->setName('__Secure-FORMALMS');
            } else {
                $this->session->setName('FORMALMS');
            }
            if (!$this->session->isStarted()) {
                $this->session->start();
            }
        }
    }

    public function getConfig(): SessionConfig
    {
        return $this->config;
    }

    public function setConfig(SessionConfig $config): void
    {
        $this->config = $config;
    }

    /**
     * @return null|Session
     */
    public function getSession() : ?Session
    {
        return $this->session;
    }

    public function isSessionExpired()
    {
        $session_time = \FormaLms\lib\Get::sett('ttlSession', 3600);

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
