<?php

namespace Vested\GabelliTV;

use Vested\Gabelli\ListTable;

defined( 'ABSPATH' ) or die;

global $wpdb;

$columns = $wpdb->get_results( "SHOW FULL COLUMNS FROM {$wpdb->prefix}gabelli_tv_log" );
$cols    = [];
foreach ( $columns as $column ) {
    $cols[ $column->Field ] = strtoupper( str_replace( '_', ' ', $column->Field ) );
}

$args = [
    'table'              => "{$wpdb->prefix}gabelli_tv_log",
    'columns'            => $cols,
    'sortable_columns'   => $cols,
    'searchable_columns' => [ 'msg' ],
    'orderby'            => 'id',
];

echo '<h3>GabeliTV API Logs</h3>';
Settings\API::get_instance()->api_status();
$table = new ListTable( $args );
$table->prepare_items();
$table->display();
