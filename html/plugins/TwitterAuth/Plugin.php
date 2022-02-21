<?php
namespace Plugin\TwitterAuth;
defined("IN_FORMA") or die('Direct access is forbidden.');




class Plugin extends \FormaPlugin {
    public function install(){
        parent::addSetting('twitter.oauth_key', 'string', 255);
        parent::addSetting('twitter.oauth_secret', 'string', 255);
    }
}