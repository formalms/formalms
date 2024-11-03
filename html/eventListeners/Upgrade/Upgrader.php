<?php
namespace FormaLms\eventListeners\Upgrade;


abstract class Upgrader {

    public function __construct(array $params) {

    }

    abstract public function run();
}