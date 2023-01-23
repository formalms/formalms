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

use FormaLms\lib\Get;

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

    public function getConfig(): array
    {
        $config = [];
        $config['signature'] = \Util::getSignature();
        $baseUrl = $this->getBaseUrl();

        $config['url']['base'] = $baseUrl;
        $config['url']['template'] = $baseUrl . '/' . _folder_templates_ . '/' . getTemplate();
        foreach (Get::coreFolders as $coreFolder) {
            $config['url'][$coreFolder] = sprintf('%s/%s', $baseUrl, $coreFolder);
        }
        $config['signature'] = \Util::getSignature();

        $langCode = \Lang::getDefault();

        $language = $this->langAdm->getLanguage(\Lang::get());
        if ($language) {
            $langCode = $this->langAdm->getLanguage(\Lang::get())->lang_browsercode;

            $langCode = explode(';', $langCode);

            if (is_array($langCode)) {
                $langCode = $langCode[0];
            }
        }

        $config['lang'] = [
            'enabledLanguages' => $this->langAdm->getLangList(),
            'currentLanguage' => \Lang::getDefault(),
            'currentLangCode' => $langCode,
            'translations' => $this->langAdm->langTranslation(),
        ];

        $config['uploadFileSize'] = ini_get('upload_max_filesize');

        return $config;
    }

    public function getBaseUrl($onlyBasePath = false): string
    {
        return Get::getBaseUrl($onlyBasePath);
    }
}
