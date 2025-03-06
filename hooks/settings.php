<?php

namespace Vested\GabelliTV;

defined( 'ABSPATH' ) or die;

// check connection and maybe schedule/unscheduled tasks after an API settings update
add_action( 'updated_option', function ( $option, $old_value, $value ) {

    if ( $option === 'gabellitv-api' ) {

        as_unschedule_all_actions( 'gabellitv_poller' );

        if ( 'no' === $value['disabled'] ) {

            $specified_interval = absint( $value['poller'] );
            if ( $specified_interval < 5 ) {
                $specified_interval = 1;
            }

            $interval = MINUTE_IN_SECONDS * $specified_interval;

            as_schedule_recurring_action( time(), $interval, 'gabellitv_poller' );
        }
    }

}, 10, 3 );

// settings
add_action( 'admin_menu', [ Settings\API::get_instance(), 'add_admin_menu' ] );
add_action( 'admin_init', [ Settings\API::get_instance(), 'init_settings' ] );
add_filter( 'plugin_action_links_gabellitv/gabellitv.php', [ Settings\API::get_instance(), 'plugin_action_link' ] );
