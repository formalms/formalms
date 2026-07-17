<?php
namespace FormaLms\eventListeners\Upgrade;


class PostVersion20250910085030 extends Upgrader{


    public function run() {

        (new \SubscriptionAlms())->cleanupUsersNotEnrolledInClassroomDates();
    }
}