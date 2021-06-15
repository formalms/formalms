<?php

namespace appCore\Template\Services;

class ClientService
{
    const coreFolders = [
        'appLms',
        'appCore',
        'appScs',
        'api'
    ];

    /**
     * @return array
     */
    public function getConfig(): array
    {
        $config = [];

        $baseUrl = $this->getBaseUrl();

        $config['url']['base'] = $baseUrl;
        foreach (self::coreFolders as $coreFolder) {
            $config['url'][$coreFolder] = sprintf('%s/%s', $baseUrl, $coreFolder);
        }
        return $config;
    }

    private function getBaseUrl(): string
    {
        $baseUrl = '';
        if (isset($_SERVER['HTTP_HOST'])) {
            $http = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
            $hostname = $_SERVER['HTTP_HOST'];

            $requestUriArray = explode('/', $_SERVER['REQUEST_URI']);

            $path = '';
            foreach ($requestUriArray as $requestUriItem) {
                if (!empty($requestUriItem) && (!in_array($requestUriItem, self::coreFolders, true) || strpos('index.php',$requestUriItem) > 0)) {
                    $path .= sprintf('/%s', $requestUriItem);
                }
            }
            $baseUrl = sprintf("%s://%s%s", $http, $hostname, $path);
        }

        return $baseUrl;
    }
}