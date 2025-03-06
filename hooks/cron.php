<?php

namespace Vested\GabelliTV;

defined( 'ABSPATH' ) or die;

add_action(
    'gabellitv_prune',
    [ DB::class, 'prune' ]
);

add_action(
	'gabellitv_poller',
	[ Updater::get_instance(), 'poller' ]
);
