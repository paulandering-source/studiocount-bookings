<?php
/**
 * Removes only StudioCount Bookings settings.
 *
 * @package StudioCount_Bookings
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'studiocount_bookings_options' );
delete_site_option( 'studiocount_bookings_options' );
delete_option( 'studiocount_bookings_connection' );
delete_site_option( 'studiocount_bookings_connection' );
delete_option( 'studiocount_bookings_page_id' );
delete_site_option( 'studiocount_bookings_page_id' );
