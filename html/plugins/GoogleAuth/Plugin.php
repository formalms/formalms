<?php
namespace Plugin\GoogleAuth;
defined("IN_FORMA") or die('Direct access is forbidden.');




class Plugin extends \FormaPlugin {
    public function install(){
        parent::addSetting('google.oauth_key', 'string', 255);
        parent::addSetting('google.oauth_secret', 'string', 255);
    }
}