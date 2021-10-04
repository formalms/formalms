<?php

namespace appCore\Template\Services;

class ClientService
{
    private static self $clientService;

    private \LangAdm $langAdm;

    private function __construct()
    {
        $this->langAdm = new \LangAdm();
    }

    public static function getInstance(){
        return self::$clientService ?? new ClientService();
    }

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
        $config['signature'] = \Util::getSignature();
        $baseUrl = $this->getBaseUrl();

        $config['url']['base'] = $baseUrl;
        $config['url']['template'] = $baseUrl .'/'._folder_templates_.'/'. getTemplate();
        foreach (self::coreFolders as $coreFolder) {
            $config['url'][$coreFolder] = sprintf('%s/%s', $baseUrl, $coreFolder);
        }
        $config['signature'] = \Util::getSignature();


        $config['lang'] = [
            'enabledLanguages' => self::getInstance()->langAdm->getLangList(),
            'currentLanguage' => \Lang::getDefault(),
            'translations' => self::getInstance()->langAdm->langTranslation()
        ];
        return $config;
    }

    public function getBaseUrl(): string
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