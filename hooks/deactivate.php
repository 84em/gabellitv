<?php

namespace Vested\GabelliTV;

defined( 'ABSPATH' ) or die;

register_deactivation_hook( GABELLITV_PLUGIN_FILE, function () {

	unschedule_tasks();
} );
