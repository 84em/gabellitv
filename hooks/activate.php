<?php

namespace Vested\GabelliTV;

defined( 'ABSPATH' ) or die;

register_activation_hook( GABELLITV_PLUGIN_FILE, function () {

    // sets up our DB schema
    DB::schema();

    // maybe schedule cron tasks
    maybe_schedule_tasks();

} );
