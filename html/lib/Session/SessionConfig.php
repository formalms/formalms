<?php

namespace FormaLms\lib\Session;

class SessionConfig
{
    private array $config;

    private string $handler = SessionManager::FILESYSTEM;
    private ?string $url = '';
    private ?string $host = null;
    private ?int $port = null;
    private float $timeout = 2.5;
    private int $lifetime = 7200;
    private string $prefix = 'forma.';
    private string $name = 'forma_session';
    private bool $authentication = false;
    private ?string $user = null;
    private ?string $password = null;
    private array $options = [];


    public function __construct()
    {
        $this->url = session_save_path();

    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array $config
     * @return SessionConfig
     */
    public function setConfig(array $config): SessionConfig
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @return string
     */
    public function getHandler(): string
    {
        return $this->handler;
    }

    /**
     * @param string $handler
     * @return SessionConfig
     */
    public function setHandler(string $handler): SessionConfig
    {
        $this->handler = $handler;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string|null $url
     * @return SessionConfig
     */
    public function setUrl(?string $url): SessionConfig
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHost(): ?string
    {
        return $this->host;
    }

    /**
     * @param string|null $host
     * @return SessionConfig
     */
    public function setHost(?string $host): SessionConfig
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * @param int|null $port
     * @return SessionConfig
     */
    public function setPort(?int $port): SessionConfig
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @return float
     */
    public function getTimeout(): float
    {
        return $this->timeout;
    }

    /**
     * @param float $timeout
     * @return SessionConfig
     */
    public function setTimeout(float $timeout): SessionConfig
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * @return int
     */
    public function getLifetime(): int
    {
        return $this->lifetime;
    }

    /**
     * @param int $lifetime
     * @return SessionConfig
     */
    public function setLifetime(int $lifetime): SessionConfig
    {
        $this->lifetime = $lifetime;
        return $this;
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @param string $prefix
     * @return SessionConfig
     */
    public function setPrefix(string $prefix): SessionConfig
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return SessionConfig
     */
    public function setName(string $name): SessionConfig
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAuthentication(): bool
    {
        return $this->authentication;
    }

    /**
     * @param bool $authentication
     * @return SessionConfig
     */
    public function setAuthentication(bool $authentication): SessionConfig
    {
        $this->authentication = $authentication;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUser(): ?string
    {
        return $this->user;
    }

    /**
     * @param string|null $user
     * @return SessionConfig
     */
    public function setUser(?string $user): SessionConfig
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string|null $password
     * @return SessionConfig
     */
    public function setPassword(?string $password): SessionConfig
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @return SessionConfig
     */
    public function setOptions(array $options): SessionConfig
    {
        $this->options = $options;
        return $this;
    }
}