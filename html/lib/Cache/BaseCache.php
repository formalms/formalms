<?php

namespace FormaLms\lib\Cache;

abstract class BaseCache
{
    protected const FORMAT_PHP = 'php';
    protected const FORMAT_JSON = 'json';
    protected const FORMAT_MSGPACK = 'msgpack';
    protected const FORMAT_IGBINARY = 'igbinary';

    protected string $cacheDir;
    protected string $namespace;
    protected array $memoryCache = [];
    protected string $cacheFormat;
    protected int $cacheTTL;
    protected static $instances = [];

    /**
     * Protected constructor to prevent direct instantiation
     */
    protected function __construct(
        string $subDir,
        ?string $format = null,
        ?int $ttl = null,
        ?string $namespace = null
    ) {
        $this->cacheDir = _files_ . '/cache/' . trim($subDir, '/') . '/';
        $this->namespace = $namespace ?? static::class;
        $this->cacheTTL = $ttl ?? 3600; // Default 1 hour
        $this->cacheFormat = $this->determineFormat($format);
        $this->ensureStructure();
    }

    /**
     * Prevent cloning of the instance
     */
    private function __clone() {}

    /**
     * Prevent unserialize of the instance
     */
    private function __wakeup() {}

    /**
     * Get singleton instance with specific configuration
     */
    public static function getInstance(
        ?string $format = null,
        ?int $ttl = null,
        ?string $namespace = null
    ): static {
        $class = static::class;
        $key = $class . '_' . ($format ?? 'default') . '_' . ($ttl ?? 'default');

        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new static(
                static::getSubDir(),
                $format,
                $ttl,
                $namespace
            );
        }

        return self::$instances[$key];
    }

    /**
     * Reset all instances (useful for testing)
     */
    public static function resetInstances(): void
    {
        self::$instances = [];
    }

    /**
     * Get subdirectory for cache storage
     * Must be implemented by child classes
     */
    abstract protected static function getSubDir(): string;

    /**
     * Ensure cache directory structure exists
     * Can be overridden by child classes
     */
    protected function ensureStructure(): void
    {
        if (!is_dir($this->cacheDir) && !mkdir($this->cacheDir, 0755, true) && !is_dir($this->cacheDir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $this->cacheDir));
        }
    }

    /**
     * Determine cache format based on environment and available extensions
     */
    protected function determineFormat(?string $format = null): string
    {
        // Check passed format or environment variable
        $selectedFormat = strtolower($format ?? getenv('CACHE_FORMAT') ?? '');

        return match($selectedFormat) {
            'msgpack' => extension_loaded('msgpack') ? self::FORMAT_MSGPACK : self::FORMAT_JSON,
            'igbinary' => extension_loaded('igbinary') ? self::FORMAT_IGBINARY : self::FORMAT_JSON,
            'php' => self::FORMAT_PHP,
            'json' => self::FORMAT_JSON,
            default => self::FORMAT_JSON
        };
    }

    /**
     * Get current cache format
     */
    public function getCacheFormat(): string
    {
        return $this->cacheFormat;
    }

    /**
     * Get TTL
     */
    public function getTTL(): int
    {
        return $this->cacheTTL;
    }

    /**
     * Set TTL
     */
    public function setTTL(int $ttl): void
    {
        $this->cacheTTL = $ttl;
    }

    /**
     * Get cached data
     */
    public function get($key, $subKey, $type = 'default')
    {
        $cacheKey = $this->buildMemoryCacheKey($key, $subKey, $type);

        // Check memory cache first
        if (isset($this->memoryCache[$cacheKey])) {
            return $this->memoryCache[$cacheKey];
        }

        $file = $this->getCacheFile($key, $subKey, $type);
        if (!file_exists($file)) {
            return null;
        }

        try {
            $content = file_get_contents($file);
            if ($content === false) {
                return null;
            }

            $data = $this->unserialize($content);
            if (!is_array($data) || !isset($data['timestamp']) || !isset($data['data'])) {
                return null;
            }

            if ((time() - $data['timestamp']) > $this->cacheTTL) {
                @unlink($file);
                return null;
            }

            $this->memoryCache[$cacheKey] = $data['data'];
            return $data['data'];

        } catch (\Throwable $e) {
            //error_log("Cache read error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Set cache data
     */
    public function set($key, $subKey, $data, $type = 'default'): bool
    {
        try {
            $file = $this->getCacheFile($key, $subKey, $type);
            $cacheKey = $this->buildMemoryCacheKey($key, $subKey, $type);

            // Store in memory cache
            $this->memoryCache[$cacheKey] = $data;

            // Prepare directory
            $dir = dirname($file);
            if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
            }

            // Prepare cache data
            $cacheData = [
                'timestamp' => time(),
                'data' => $data
            ];

            // Serialize and write
            $content = $this->serialize($cacheData);
            if (file_put_contents($file, $content) === false) {
                return false;
            }

            // Handle OpCache
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($file, true);
                if ($this->cacheFormat === self::FORMAT_PHP) {
                    opcache_compile_file($file);
                }
            }

            return true;

        } catch (\Throwable $e) {
            //error_log("Cache write error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Serialize data based on format
     */
    protected function serialize($data): string
    {
        return match($this->cacheFormat) {
            self::FORMAT_PHP => sprintf("<?php\nreturn %s;", var_export($data, true)),
            self::FORMAT_JSON => json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            self::FORMAT_MSGPACK => msgpack_pack($data),
            self::FORMAT_IGBINARY => igbinary_serialize($data),
            default => throw new \RuntimeException("Unsupported cache format: {$this->cacheFormat}")
        };
    }

    /**
     * Unserialize data based on format
     */
    protected function unserialize($content)
    {
        return match($this->cacheFormat) {
            self::FORMAT_PHP => include $content,
            self::FORMAT_JSON => json_decode($content, true, 512, JSON_THROW_ON_ERROR),
            self::FORMAT_MSGPACK => msgpack_unpack($content),
            self::FORMAT_IGBINARY => igbinary_unserialize($content),
            default => throw new \RuntimeException("Unsupported cache format: {$this->cacheFormat}")
        };
    }

    /**
     * Get cache file extension
     */
    protected function getCacheExtension(): string
    {
        return match($this->cacheFormat) {
            self::FORMAT_PHP => '.php',
            self::FORMAT_JSON => '.json',
            self::FORMAT_MSGPACK => '.msg',
            self::FORMAT_IGBINARY => '.igb',
            default => '.cache'
        };
    }

    /**
     * Build memory cache key
     */
    protected function buildMemoryCacheKey($key, $subKey, $type): string
    {
        return sprintf('%s_%s_%s', $type, $key, $subKey);
    }

    /**
     * Get cache file path
     */
    protected function getCacheFile($key, $subKey, $type): string
    {
        return $this->cacheDir . $type . '/' . $key . '/' . $subKey . $this->getCacheExtension();
    }

    /**
     * Clear cache
     */
    public function clear($key = null, $subKey = null, $type = null): void
    {
        // Clear memory cache
        if ($key === null && $subKey === null && $type === null) {
            $this->memoryCache = [];
        } else {
            $pattern = sprintf(
                '%s%s%s',
                $type === null ? '' : $type . '_',
                $key === null ? '' : $key . '_',
                $subKey ?? ''
            );

            foreach ($this->memoryCache as $cacheKey => $value) {
                if (str_starts_with($cacheKey, $pattern)) {
                    unset($this->memoryCache[$cacheKey]);
                }
            }
        }

        // Clear file cache
        if ($key === null && $subKey === null && $type === null) {
            $this->clearDirectory($this->cacheDir);
            $this->ensureStructure();
            return;
        }

        if ($type !== null) {
            $typeDir = $this->cacheDir . $type;
            if ($key === null) {
                $this->clearDirectory($typeDir);
                return;
            }

            $keyDir = $typeDir . '/' . $key;
            if ($subKey === null) {
                $this->clearDirectory($keyDir);
                return;
            }

            $file = $this->getCacheFile($key, $subKey, $type);
            if (file_exists($file)) {
                unlink($file);
                if (function_exists('opcache_invalidate')) {
                    opcache_invalidate($file, true);
                }
            }
        }
    }

    /**
     * Clear directory recursively
     */
    protected function clearDirectory(string $dir): void
    {
        if (!is_dir($dir)) return;

        $files = glob($dir . '/*');
        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->clearDirectory($file);
                rmdir($file);
            } else {
                unlink($file);
                if (function_exists('opcache_invalidate')) {
                    opcache_invalidate($file, true);
                }
            }
        }
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        return [
            'format' => $this->cacheFormat,
            'ttl' => $this->cacheTTL,
            'memory_cache_size' => count($this->memoryCache),
            'directory' => $this->cacheDir,
            'extensions' => [
                'msgpack' => extension_loaded('msgpack'),
                'igbinary' => extension_loaded('igbinary'),
                'opcache' => function_exists('opcache_invalidate')
            ]
        ];
    }
}