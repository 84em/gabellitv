<?php

namespace Vested\GabelliTV;

defined( 'ABSPATH' ) or die;

/**
 * Schedules recurring tasks
 *
 * @return void
 */
function maybe_schedule_tasks() {

    // schedules our daily DB log clean up task
    if ( ! as_has_scheduled_action( 'gabellitv_prune' ) ) {
        as_schedule_recurring_action( time(), DAY_IN_SECONDS, 'gabellitv_prune' );
    }

    // poller
    if ( ! as_has_scheduled_action( 'gabellitv_poller' ) ) {
        as_schedule_recurring_action( time(), ( MINUTE_IN_SECONDS * 10 ), 'gabellitv_poller' );
    }
}

/**
 * Unschedules specific recurring tasks.
 *
 * @return void
 */
function unschedule_tasks() {

    as_unschedule_action( 'gabellitv_prune' );
    as_unschedule_action( 'gabellitv_poller' );
}
