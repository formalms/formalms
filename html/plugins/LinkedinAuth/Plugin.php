<?php
namespace Plugin\LinkedinAuth;
defined("IN_FORMA") or die('Direct access is forbidden.');




class Plugin extends \FormaPlugin {
    public function install(){
        parent::addSetting('linkedin.oauth_key', 'string', 255);
        parent::addSetting('linkedin.oauth_secret', 'string', 255);
    }
}