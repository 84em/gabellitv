<?php

namespace Vested\GabelliTV;

defined( 'ABSPATH' ) or die;

// check connection and maybe schedule/unscheduled tasks after an API settings update
add_action( 'updated_option', function ( $option, $old_value, $value ) {

    if ( $option === 'gabellitv-api' ) {

        if ( 'no' === $value['disabled'] ) {
            as_schedule_recurring_action( time(), DAY_IN_SECONDS, 'gabellitv_poller' );
        }
        else {
            as_unschedule_all_actions( 'gabellitv_poller' );
        }
    }

}, 10, 3 );

// settings
add_action( 'admin_menu', [ Settings\API::get_instance(), 'add_admin_menu' ] );
add_action( 'admin_init', [ Settings\API::get_instance(), 'init_settings' ] );
add_filter( 'plugin_action_links_gabellitv/gabellitv.php', [ Settings\API::get_instance(), 'plugin_action_link' ] );
