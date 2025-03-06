<?php
/**
 * Plugin Name:     GabelliTV
 * Plugin URI:      https://fullyvested.com/
 * Description:     Gabelli YouTube Integration
 * Author:          Vested
 * Author URI:      https://fullyvested.com/
 * Text Domain:     gabelli
 * Domain Path:     /languages
 * Version:         1.0.0
 *
 * @package         GabelliTV
 */

// don't load this file directly.
defined( 'ABSPATH' ) or die;

// holds our plugin version
define( 'GABELLITV_PLUGIN_VERSION', '1.0.6' );

// full path to this plugin
define( 'GABELLITV_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// full url to this plugin
define( 'GABELLITV_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// full path to this plugin file
define( 'GABELLITV_PLUGIN_FILE', __FILE__ );

// full path to our templates
define( 'GABELLITV_TEMPLATES_DIR', GABELLITV_PLUGIN_DIR . 'templates/' );

// class autoloader
require_once GABELLITV_PLUGIN_DIR . 'vendor/autoload.php';

// load all our functions
foreach ( glob( dirname( __FILE__ ) . '/functions/*.php' ) as $file ) {
	require_once $file;
}

// load all our hooks
foreach ( glob( dirname( __FILE__ ) . '/hooks/*.php' ) as $file ) {
	require_once $file;
}
