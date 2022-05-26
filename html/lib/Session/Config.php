<?php

namespace Forma\lib\Session;

class Config
{
    private array $config;

    private string $handler = SessionManager::FILESYSTEM;
    private ?string $url = null;
    private ?string $host = null;
    private ?int $port = null;
    private float $timeout = 2.5;
    private int $lifetime = 7200;
    private string $prefix = 'forma.';
    private string $name = 'forma_session';
    private bool $authentication = false;
    private ?string $user = null;
    private ?string $password = null;

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array $config
     * @return Config
     */
    public function setConfig(array $config): Config
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
     * @return Config
     */
    public function setHandler(string $handler): Config
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
     * @return Config
     */
    public function setUrl(?string $url): Config
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
     * @return Config
     */
    public function setHost(?string $host): Config
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
     * @return Config
     */
    public function setPort(?int $port): Config
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
     * @return Config
     */
    public function setTimeout(float $timeout): Config
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
     * @return Config
     */
    public function setLifetime(int $lifetime): Config
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
     * @return Config
     */
    public function setPrefix(string $prefix): Config
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
     * @return Config
     */
    public function setName(string $name): Config
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
     * @return Config
     */
    public function setAuthentication(bool $authentication): Config
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
     * @return Config
     */
    public function setUser(?string $user): Config
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
     * @return Config
     */
    public function setPassword(?string $password): Config
    {
        $this->password = $password;
        return $this;
    }


}