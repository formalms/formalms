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

namespace appCore\Template\Services;

class ClientService
{
    private static self $clientService;

    private \LangAdm $langAdm;

    private function __construct()
    {
        $this->langAdm = new \LangAdm();
    }

    public static function getInstance()
    {
        return self::$clientService ?? new ClientService();
    }

    public const coreFolders = [
        'appLms',
        'appCore',
        'appScs',
        'api',
    ];

    public function getConfig(): array
    {
        $config = [];
        $config['signature'] = \Util::getSignature();
        $baseUrl = $this->getBaseUrl();

        $config['url']['base'] = $baseUrl;
        $config['url']['template'] = $baseUrl . '/' . _folder_templates_ . '/' . getTemplate();
        foreach (self::coreFolders as $coreFolder) {
            $config['url'][$coreFolder] = sprintf('%s/%s', $baseUrl, $coreFolder);
        }
        $config['signature'] = \Util::getSignature();

        $langCode = $this->langAdm->getLanguage(\Lang::get())->lang_browsercode;

        $langCode = explode(';', $langCode);

        $config['lang'] = [
            'enabledLanguages' => $this->langAdm->getLangList(),
            'currentLanguage' => \Lang::getDefault(),
            'currentLangCode' => $langCode[0],
            'translations' => $this->langAdm->langTranslation(),
        ];

        return $config;
    }

    public function getBaseUrl(): string
    {
        $baseUrl = '';
        if (isset($_SERVER['HTTP_HOST'])) {
            $http = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') || strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https' ? 'https' : 'http';
            $hostname = $_SERVER['HTTP_HOST'];
            $possiblePhpEndpoints = [];

            $requestUri = $_SERVER['REQUEST_URI'];

            preg_match('/\/(.*?).php/', $requestUri, $match);
            if (!empty($match)) {
                $explodedMatch = explode('/', $match[0]);
                $possiblePhpEndpoint = '';
                foreach ($explodedMatch as $item) {
                    if (!empty($item) && str_contains($item, '.php')) {
                        $possiblePhpEndpoint .= str_replace(self::coreFolders, '', $item);
                    }
                }

                $possiblePhpEndpoints[] = $possiblePhpEndpoint;
            }

            $possiblePhpEndpoints[] = '/?';
            $possiblePhpEndpoints[] = '/api';

            foreach ($possiblePhpEndpoints as $possiblePhpEndpoint) {
                if (str_contains($requestUri, $possiblePhpEndpoint)) {
                    $requestUriArray = explode($possiblePhpEndpoint, $requestUri);
                    $requestUriArray = explode('/', $requestUriArray[0]);
                    break;
                }
            }

            if (empty($requestUriArray) && !empty($requestUri)) {
                $requestUriArray = explode('/', $requestUri);
            }

            $path = '';
            foreach ($requestUriArray as $requestUriItem) {
                if (!empty($requestUriItem) && !in_array($requestUriItem, self::coreFolders, true)) {
                    $path .= sprintf('/%s', $requestUriItem);
                }
            }
            $baseUrl = sprintf('%s://%s%s', $http, $hostname, $path);
        }

        return $baseUrl;
    }
}
