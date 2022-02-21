<?php defined("IN_FORMA") or die('Direct access is forbidden.');



class DummyAlms extends Model {

    public function getPerm() {
        return array('view' => 'standard/view.png');
    }
}