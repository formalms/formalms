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

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): SessionConfig
    {
        $this->config = $config;

        return $this;
    }

    public function getHandler(): string
    {
        return $this->handler;
    }

    public function setHandler(string $handler): SessionConfig
    {
        $this->handler = $handler;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): SessionConfig
    {
        $this->url = $url;

        return $this;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function setHost(?string $host): SessionConfig
    {
        $this->host = $host;

        return $this;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function setPort(?int $port): SessionConfig
    {
        $this->port = $port;

        return $this;
    }

    public function getTimeout(): float
    {
        return $this->timeout;
    }

    public function setTimeout(float $timeout): SessionConfig
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function getLifetime(): int
    {
        return $this->lifetime;
    }

    public function setLifetime(int $lifetime): SessionConfig
    {
        $this->lifetime = $lifetime;

        return $this;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function setPrefix(string $prefix): SessionConfig
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): SessionConfig
    {
        $this->name = $name;

        return $this;
    }

    public function isAuthentication(): bool
    {
        return $this->authentication;
    }

    public function setAuthentication(bool $authentication): SessionConfig
    {
        $this->authentication = $authentication;

        return $this;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function setUser(?string $user): SessionConfig
    {
        $this->user = $user;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): SessionConfig
    {
        $this->password = $password;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): SessionConfig
    {
        $this->options = $options;

        return $this;
    }
}
