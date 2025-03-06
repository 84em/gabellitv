<?php

namespace Vested\GabelliTV;

defined( 'ABSPATH' ) or die;

class CLI {

    public function __construct() {
    }

    public function update( $args, $assoc_args ) {

        $api = new API();
        $api->update();
    }

    public function backfill( $args, $assoc_args ) {

        $api = new API();
        $api->backfill();
    }

    public function schema( $args, $assoc_args ) {

        DB::schema();
    }
}

