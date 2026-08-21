<?php
/**
 * Plugin Name:       StudioCount Bookings
 * Plugin URI:        https://www.studiocount.com/
 * Description:       Add StudioCount classes and products to your WordPress website.
 * Version:           1.0.9
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author:            Sanctabase Ltd
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       studiocount-bookings
 *
 * @package StudioCount_Bookings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'STUDIOCOUNT_BOOKINGS_VERSION', '1.0.9' );
define( 'STUDIOCOUNT_BOOKINGS_FILE', __FILE__ );
define( 'STUDIOCOUNT_BOOKINGS_PATH', plugin_dir_path( __FILE__ ) );
define( 'STUDIOCOUNT_BOOKINGS_URL', plugin_dir_url( __FILE__ ) );

require_once STUDIOCOUNT_BOOKINGS_PATH . 'includes/class-studiocount-bookings-renderer.php';
require_once STUDIOCOUNT_BOOKINGS_PATH . 'includes/class-studiocount-bookings-settings.php';
require_once STUDIOCOUNT_BOOKINGS_PATH . 'includes/class-studiocount-bookings-plugin.php';

StudioCount_Bookings_Plugin::init();
