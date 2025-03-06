<?php

namespace Vested\GabelliTV;

defined( 'ABSPATH' ) or die;

class Updater {

    private static Updater $instance;

    private function __construct() {
    }

    public static function get_instance() {

        return self::$instance ?? self::$instance = new self();
    }

    public function poller() {

        $api = new API();
        $api->poll();
    }
}

