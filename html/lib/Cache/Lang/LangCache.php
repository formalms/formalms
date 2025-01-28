<?php

namespace FormaLms\lib\Cache\Lang;

use FormaLms\lib\Cache\BaseCache;

class LangCache extends BaseCache
{
    private const CACHE_DIR = 'languages';
    private const CACHE_TTL = 86400;

    public static function getInstance(?string $format = null, ?int $ttl = self::CACHE_TTL, ?string $namespace = null): static
    {
        return parent::getInstance($format, $ttl, $namespace);
    }

    protected static function getSubDir(): string
    {
        return self::CACHE_DIR;
    }

    /**
     * Override ensureStructure to add specific directories for language cache
     */
    protected function ensureStructure(): void
    {
        $dirs = [
            $this->cacheDir . '/lists',
        ];

        foreach ($dirs as $dir) {
            if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
            }
        }
    }

    /**
     * Override getCacheFile for language-specific path structure
     */
    protected function getCacheFile($lang_code, $key, $type): string
    {
        switch ($type) {
            case 'translation':
                return $this->cacheDir . '/' . $lang_code . '/translations/' . $key . $this->getCacheExtension();

            case 'language':
                return $this->cacheDir . '/' . $lang_code . '/info' . $this->getCacheExtension();

            case 'language_list':
                return $this->cacheDir . '/lists/available' . $this->getCacheExtension();

            case 'module_list':
                return $this->cacheDir . '/lists/modules' . $this->getCacheExtension();

            default:
                return $this->cacheDir . '/' . $type . '/' . $lang_code . '/' . $key . $this->getCacheExtension();
        }
    }
}