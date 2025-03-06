<?php

namespace Vested\GabelliTV;

defined( 'ABSPATH' ) or die;

function gabellitv_cli() {

    if ( defined( 'WP_CLI' ) && WP_CLI ) {
        \WP_CLI::add_command( 'gabellitv', '\\Vested\\GabelliTV\\CLI' );
    }
}

add_action( 'cli_init', '\\Vested\\GabelliTV\\gabellitv_cli' );
