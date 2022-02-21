<?php
namespace Plugin\FacebookAuth;
defined("IN_FORMA") or die('Direct access is forbidden.');




class Plugin extends \FormaPlugin {
    public function install(){
        parent::addSetting('facebook.oauth_key', 'string', 255);
        parent::addSetting('facebook.oauth_secret', 'string', 255);
    }
}