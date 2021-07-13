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
        $config['signature'] = \Util::getSignature();
        $config['lang'] = [
            'course' => [
                '_CORSO' => 'corso'
            ],
            'coursereport' => [],
        ];
        return $config;
    }

    private function getBaseUrl(): string
    {
        $baseUrl = '';
        if (isset($_SERVER['HTTP_HOST'])) {
            $http = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') || strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https' ? 'https' : 'http';
            $hostname = $_SERVER['HTTP_HOST'];


            $requestUriArray = explode('index.php', $_SERVER['REQUEST_URI']);
            $requestUriArray = explode('/', $requestUriArray[0]);

            $path = '';
            foreach ($requestUriArray as $requestUriItem) {
                if (!empty($requestUriItem) && !in_array($requestUriItem, self::coreFolders, true)) {
                    $path .= sprintf('/%s', $requestUriItem);
                }
            }
            $baseUrl = sprintf("%s://%s%s", $http, $hostname, $path);
        }

        return $baseUrl;
    }
}