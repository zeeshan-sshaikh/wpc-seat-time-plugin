<?php
/**
 * Public Class - Redundant display hooks removed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPC_Seat_Time_Public {
	/**
	 * Constructor.
	 * Logic moved to class-wpc-seat-time-display.php
	 */
	public function __construct() {
		// Public hooks can be added here if needed for other frontend features
	}
}

new WPC_Seat_Time_Public();