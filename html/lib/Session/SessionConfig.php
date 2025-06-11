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

class SessionConfig
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
    private array $options = [];
    private string $cookieName;
    private string $sameSite = 'Lax';
    private array $sameSitePolicies = [];

    public const SECUREFORMA = '__Secure-FORMALMS';
    public const UNSECUREFORMA = 'FORMALMS';

    public const SAMESITE_NONE = 'None';
    public const SAMESITE_LAX = 'Lax';
    public const SAMESITE_STRICT = 'Strict';

    public function __construct()
    {
        $this->url = session_save_path();
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): self
    {
        $this->config = $config;

        return $this;
    }

    public function getHandler(): string
    {
        return $this->handler;
    }

    public function setHandler(string $handler): self
    {
        $this->handler = $handler;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function setHost(?string $host): self
    {
        $this->host = $host;

        return $this;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function setPort(?int $port): self
    {
        $this->port = $port;

        return $this;
    }

    public function getTimeout(): float
    {
        return $this->timeout;
    }

    public function setTimeout(float $timeout): self
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function getLifetime(): int
    {
        return $this->lifetime;
    }

    public function setLifetime(int $lifetime): self
    {
        $this->lifetime = $lifetime;

        return $this;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function setPrefix(string $prefix): self
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function isAuthentication(): bool
    {
        return $this->authentication;
    }

    public function setAuthentication(bool $authentication): self
    {
        $this->authentication = $authentication;

        return $this;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function setUser(?string $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function getCookieName(): string
    {
        return $this->cookieName;
    }

    public function setCookieName(string $cookieName): self
    {
        $this->cookieName = $cookieName;
        return $this;
    }

    public function getSameSite(): string
    {
        return $this->sameSite;
    }

    public function setSameSite(string $sameSite): self
    {
        $this->sameSite = $sameSite;

        // Parse the SameSite configuration string into the sameSitePolicies array
        if (!empty($sameSite)) {
            // Dividiamo la configurazione in coppie dominio:valore
            $domainPairs = explode(',', $sameSite);

            foreach ($domainPairs as $pair) {
                // Divide la coppia in dominio e valore
                $parts = explode(':', trim($pair));

                // Se la coppia è invalida (non ha esattamente 2 parti), passa alla successiva
                if (count($parts) !== 2) {
                    continue;
                }

                $domain = trim($parts[0]);
                $sameSiteValue = trim($parts[1]);

                // Valida il valore SameSite (deve essere None, Lax, o Strict)
                if (in_array($sameSiteValue, [self::SAMESITE_NONE, self::SAMESITE_LAX, self::SAMESITE_STRICT])) {
                    $this->sameSitePolicies[$domain] = $sameSiteValue;
                }
            }
        }

        return $this;
    }

    public function getSameSitePolicies(): array
    {
        return $this->sameSitePolicies;
    }

    public function setSameSitePolicies(array $sameSitePolicies): self
    {
        $this->sameSitePolicies = $sameSitePolicies;
        return $this;
    }

    /**
     * Get the SameSite value for a specific domain
     * 
     * @param string $domain The domain to check
     * @return string The SameSite value (None, Lax, or Strict)
     */
    public function getSameSiteForDomain(string $domain): string
    {
        if (empty($this->sameSitePolicies)) {
            return $this->sameSite;
        }

        foreach ($this->sameSitePolicies as $domainPattern => $policy) {
            // Verifica se il dominio corrisponde
            $matches = false;

            if ($domain === $domainPattern) {
                $matches = true;
            } elseif (str_contains($domain, $domainPattern)) {
                $matches = true;
            } elseif (str_contains($domainPattern, '*')) {
                // Gestione wildcard
                $pattern = '/^' . str_replace('\*', '.*', preg_quote($domainPattern, '/')) . '$/';
                $matches = preg_match($pattern, $domain);
            }

            if ($matches) {
                return $policy;
            }
        }

        return $this->sameSite;
    }
}
