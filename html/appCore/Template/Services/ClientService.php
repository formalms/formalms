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

namespace FormaLms\appCore\Template\Services;

class ClientService
{
    private static self $clientService;
    protected \Symfony\Component\HttpFoundation\Request $request;

    private \LangAdm $langAdm;

    private function __construct()
    {
        $this->langAdm = new \LangAdm();
        $this->request = \FormaLms\lib\Request\RequestManager::getInstance()->getRequest();
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

        $config['uploadFileSize'] = ini_get('upload_max_filesize');

        return $config;
    }

    public function getBaseUrl($onlyBasePath = false): string
    {
        $possiblePhpEndpoints = [];
        $path = '';
        $basePath = '/';

        try {
            $basePath = $this->request->getSchemeAndHttpHost();
            $requestUri = $this->request->getBaseUrl();
        } catch(\Error $e) {
            // non deve mai andare qui, ma ci passa se vengono chiamate shell exec come le migrate
        }
        
        if (!$onlyBasePath) {
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

            foreach ($requestUriArray as $requestUriItem) {
                if (!empty($requestUriItem) && !in_array($requestUriItem, self::coreFolders, true)) {
                    $path .= sprintf('/%s', $requestUriItem);
                }
            }
        }

        return $path != '' ? $path : $basePath;
    }
}
