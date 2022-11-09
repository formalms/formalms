<?php
namespace FormaLms\lib\Template;
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

defined('IN_FORMA') or exit('Direct access is forbidden.');

class TemplateInfo
{

    protected $name;

    protected $version;

    protected $formaMatch = false;

    public function __construct($name) {

        $this->name = $name;
        $this->version = $this->readVersion($name);
        $this->formaMatch = $this->checkVersion();
    }

    public function readVersion($templateName, $key = 'forma_version') {

        $template_file = _templates_ . '/' . $templateName . '/manifest.xml';
        if (!file_exists($template_file)) {
            return false;
        }
        if ($xml = simplexml_load_file($template_file)) {
            $man_json = json_encode($xml);
            $man_array = json_decode($man_json, true);
            if (key_exists($key, $man_array)) {
                return $man_array[$key];
            }

            return $man_array;
        } else {
            return false;
        }
    }

    public function checkVersion() {
        require_once _base_ . '/lib/lib.template.php';
        return (version_compare(getTemplateVersion(getDefaultTemplate()), $this->version) <= 0);
    }

    public function getVersion() {
        return $this->version;
    }

    public function getName() {
        return $this->name;
    }

    public function getCheckVersion() {
        return $this->formaMatch;
    }
}