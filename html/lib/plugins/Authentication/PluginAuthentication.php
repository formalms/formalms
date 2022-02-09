<?php
use appCore\Events;

/*
 * Base class of auth plugins.
 * Plugins must implement PluginAuthInterface.
 */
abstract class PluginAuthentication extends FormaPlugin {

    const AUTH_TYPE_BASE  = 'baseLogin';
    const AUTH_TYPE_SOCIAL = 'socialLogin';

    public function __construct() {

    }
}