<?php

namespace FormaLms\lib\Cache\Lang;

class LangCache {
    private const CACHE_DIR = 'languages';
    private const CACHE_EXTENSION = '.php';
    private const CACHE_TTL = 3600; // 1 ora

    private $cachePath;
    private $namespace = 'FormaLms\\lib\\Cache\\Lang';
    private $memoryCache = [];

    public function __construct() {
        $this->cachePath = _files_ . '/cache/' . self::CACHE_DIR . '/';
    }

    /**
     * Get cached data with type support
     */
    public function get($key, $subKey, $type = 'translation') {
        $cacheKey = $this->buildCacheKey($key, $subKey, $type);

        // Check memory cache first
        if (isset($this->memoryCache[$cacheKey])) {
            return $this->memoryCache[$cacheKey];
        }

        $className = $this->getClassName($key, $subKey, $type);
        $fqcn = $this->namespace . '\\' . $className;

        if (!class_exists($fqcn, false)) {
            $file = $this->getCacheFile($key, $subKey, $type);
            if (file_exists($file)) {
                require_once $file;
            }
        }

        if (class_exists($fqcn, false)) {
            $this->memoryCache[$cacheKey] = $fqcn::$data;
            return $fqcn::$data;
        }

        return null;
    }

    /**
     * Set cache data with type support
     */
    public function set($key, $subKey, $data, $type = 'translation') {
        $file = $this->getCacheFile($key, $subKey, $type);
        $className = $this->getClassName($key, $subKey, $type);
        $cacheKey = $this->buildCacheKey($key, $subKey, $type);

        // Store in memory cache
        $this->memoryCache[$cacheKey] = $data;

        $code = "<?php\nnamespace {$this->namespace};\nclass {$className} {\n" .
            "    public static \$data = " . var_export($data, true) . ";\n" .
            "    public static \$timestamp = " . time() . ";\n" .
            "}";

        if (!is_dir(dirname($file))) {
            if (!mkdir($concurrentDirectory = dirname($file), 0755, true) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }

        file_put_contents($file, $code);
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($file, true);
            opcache_compile_file($file);
        }
    }

    /**
     * Check if cache is valid
     */
    public function isValid($key, $subKey, $type = 'translation') {
        $className = $this->getClassName($key, $subKey, $type);
        $fqcn = $this->namespace . '\\' . $className;

        if (class_exists($fqcn, false)) {
            return (time() - $fqcn::$timestamp) < self::CACHE_TTL;
        }

        $file = $this->getCacheFile($key, $subKey, $type);
        if (!file_exists($file)) {
            return false;
        }

        require_once $file;
        return (time() - $fqcn::$timestamp) < self::CACHE_TTL;
    }

    private function buildCacheKey($key, $subKey, $type): string {
        return "{$type}_{$key}_{$subKey}";
    }

    private function getClassName($key, $subKey, $type): string {
        return 'Cache_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $type . '_' . $key . '_' . $subKey);
    }

    private function getCacheFile($key, $subKey, $type): string {
        return $this->cachePath . $type . '/' . $key . '/' . $subKey . self::CACHE_EXTENSION;
    }

    public function clear($key = null, $subKey = null, $type = null): void {
        // Clear memory cache
        if ($key === null && $subKey === null && $type === null) {
            $this->memoryCache = [];
        } else {
            foreach ($this->memoryCache as $cacheKey => $value) {
                if (($type === null || str_starts_with($cacheKey, $type . '_')) &&
                    ($key === null || str_starts_with($cacheKey, $type . '_' . $key)) &&
                    ($subKey === null || str_starts_with($cacheKey, $type . '_' . $key . '_' . $subKey))) {
                    unset($this->memoryCache[$cacheKey]);
                }
            }
        }

        // Clear file cache
        if ($type === null) {
            $this->clearDirectory($this->cachePath);
            return;
        }

        $typePath = $this->cachePath . $type;
        if ($key === null) {
            $this->clearDirectory($typePath);
            return;
        }

        $keyPath = $typePath . '/' . $key;
        if ($subKey === null) {
            $this->clearDirectory($keyPath);
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

    private function clearDirectory($dir): void {
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