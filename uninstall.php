<?php
/**
 * Uninstall logic
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete settings
delete_option( 'wpc_seat_time_settings' );

// Delete post meta for all posts
global $wpdb;
$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '_wpc_seat_time_%'" );
