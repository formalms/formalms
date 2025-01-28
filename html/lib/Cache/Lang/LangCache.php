<?php

namespace FormaLms\lib\Cache\Lang;

class LangCache
{
    private const CACHE_DIR = 'languages';
    private const CACHE_TTL = 86400; // 24 ora

    private const FORMAT_PHP = 'php';
    private const FORMAT_JSON = 'json';
    private const FORMAT_MSGPACK = 'msgpack';
    private const FORMAT_IGBINARY = 'igbinary';

    private $cachePath;
    private $namespace = 'FormaLms\\lib\\Cache\\Lang';
    private $memoryCache = [];
    private $cacheFormat;

    private static $instance = null;

    public function __construct()
    {
        $this->cachePath = _files_ . '/cache/' . self::CACHE_DIR . '/';
        $this->cacheFormat = $this->determineFormat();
        $this->ensureStructure();
    }

    private function __clone()
    {
    }

    private function __wakeup()
    {
    }

    public static function resetInstance(): void
    {
        self::$instance = null;
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function determineFormat(): string
    {
        // Check environment variable
        $envFormat = strtolower(getenv('CACHE_FORMAT') ?: self::FORMAT_JSON);

        return match ($envFormat) {
            'msgpack' => extension_loaded('msgpack') ? self::FORMAT_MSGPACK : self::FORMAT_JSON,
            'igbinary' => extension_loaded('igbinary') ? self::FORMAT_IGBINARY : self::FORMAT_JSON,
            'php' => self::FORMAT_PHP,
            'json' => self::FORMAT_JSON,
            default => self::FORMAT_JSON // Default to JSON if not specified
        };
    }

    public function getCacheFormat(): string
    {
        return $this->cacheFormat;
    }

    /**
     * Ensure cache directory structure exists
     */
    private function ensureStructure(): void
    {
        $dirs = [
            $this->cachePath . '/lists',
        ];

        foreach ($dirs as $dir) {
            if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
            }
        }
    }

    /**
     * Get cached data with enhanced structure
     */
    public function get($lang_code, $key, $type = 'translation')
    {
        $cacheKey = $this->buildMemoryCacheKey($lang_code, $key, $type);

        // Check memory cache first
        if (isset($this->memoryCache[$cacheKey])) {
            return $this->memoryCache[$cacheKey];
        }

        $file = $this->getCacheFile($lang_code, $key, $type);
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

            if ((time() - $data['timestamp']) > self::CACHE_TTL) {
                @unlink($file);
                return null;
            }

            $this->memoryCache[$cacheKey] = $data['data'];
            return $data['data'];

        } catch (\Throwable $e) {
            error_log("Cache read error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Set cache data with improved structure
     */
    public function set($lang_code, $key, $data, $type = 'translation'): bool
    {
        try {
            $file = $this->getCacheFile($lang_code, $key, $type);
            $cacheKey = $this->buildMemoryCacheKey($lang_code, $key, $type);

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
            error_log("Cache write error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Serialize data based on format
     */
    private function serialize($data): string
    {
        return match ($this->cacheFormat) {
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
    private function unserialize($content)
    {
        return match ($this->cacheFormat) {
            self::FORMAT_PHP => include $content,
            self::FORMAT_JSON => json_decode($content, true, 512, JSON_THROW_ON_ERROR),
            self::FORMAT_MSGPACK => msgpack_unpack($content),
            self::FORMAT_IGBINARY => igbinary_unserialize($content),
            default => throw new \RuntimeException("Unsupported cache format: {$this->cacheFormat}")
        };
    }

    private function getCacheExtention()
    {
        return match ($this->cacheFormat) {
            self::FORMAT_PHP => '.php',
            self::FORMAT_JSON => '.json',
            self::FORMAT_MSGPACK => '.msg',
            self::FORMAT_IGBINARY => '.igb',
            default => '.cache'
        };
    }

    private function getCacheFile($lang_code, $key, $type): string
    {
        switch ($type) {
            case 'translation':
                $cacheFile = $this->cachePath . '/' . $lang_code . '/translations/' . $key;
                break;
            case 'language':
                $cacheFile = $this->cachePath . '/' . $lang_code . '/info';
                break;
            case 'language_list':
                $cacheFile = $this->cachePath . '/lists/available';
                break;
            case 'module_list':
                $cacheFile = $this->cachePath . '/lists/modules';
                break;
            default:
                $cacheFile = $this->cachePath . '/' . $type . '/' . $lang_code . '/' . $key;
        }

        return $cacheFile . $this->getCacheExtention();
    }


    /**
     * Build memory cache key
     */
    private function buildMemoryCacheKey($lang_code, $key, $type): string
    {
        return sprintf('%s_%s_%s', $type, $lang_code, $key);
    }

    /**
     * Clear cache with improved structure handling
     */
    public function clear($lang_code = null, $key = null, $type = null): void
    {
        $extention = $this->getCacheExtention();
        // Clear memory cache
        if ($lang_code === null && $key === null && $type === null) {
            $this->memoryCache = [];
        } else {
            $pattern = sprintf(
                '%s%s%s',
                $type === null ? '' : $type . '_',
                $lang_code === null ? '' : $lang_code . '_',
                $key ?? ''
            );

            foreach ($this->memoryCache as $cacheKey => $value) {
                if (str_starts_with($cacheKey, $pattern)) {
                    unset($this->memoryCache[$cacheKey]);
                }
            }
        }

        // Clear file cache
        if ($lang_code === null && $key === null && $type === null) {
            $this->clearDirectory($this->cachePath);
            $this->ensureStructure();
            return;
        }

        if ($type === 'translation' && $lang_code !== null) {
            $dir = $this->cachePath . '/' . $lang_code . '/translations';
            if ($key === null) {
                $this->clearDirectory($dir);
            } else {
                $file = $dir . '/' . $key . $extention;
                if (file_exists($file)) {
                    unlink($file);
                    if (function_exists('opcache_invalidate')) {
                        opcache_invalidate($file, true);
                    }
                }
            }
            return;
        }

        if ($type === 'language' && $lang_code !== null) {
            $file = $this->cachePath . '/' . $lang_code . '/info' . $extention;
            if (file_exists($file)) {
                unlink($file);
                if (function_exists('opcache_invalidate')) {
                    opcache_invalidate($file, true);
                }
            }
            return;
        }

        if ($type === 'language_list' || $type === 'module_list') {
            $file = $this->cachePath . '/lists/' . ($type === 'language_list' ? 'available' : 'modules') . $extention;
            if (file_exists($file)) {
                unlink($file);
                if (function_exists('opcache_invalidate')) {
                    opcache_invalidate($file, true);
                }
            }
            return;
        }
    }

    private function clearDirectory($dir): void
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
}