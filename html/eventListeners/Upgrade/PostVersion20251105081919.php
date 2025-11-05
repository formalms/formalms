<?php
namespace FormaLms\eventListeners\Upgrade;


class PostVersion20251105081919 extends Upgrader{


    public function run() {

        (new \SubscriptionAlms())->fixUserSubscritionLevels();
    }
}