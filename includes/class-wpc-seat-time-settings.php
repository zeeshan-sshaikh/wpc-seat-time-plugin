<?php
/**
 * Settings Handler Class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPC_Seat_Time_Settings {

	/**
	 * Get all settings.
	 */
	public static function get_settings() {
		$defaults = array(
			'average_wpm'      => 200,
			'slow_wpm'         => 120,
			'default_h5p_time' => 300,
			'youtube_api_key'  => '',
			'display_label'    => __( 'Estimated Seat Time:', 'wpc-seat-time-estimates' ),
		);

		$settings = get_option( 'wpc_seat_time_settings', $defaults );

		return wp_parse_args( $settings, $defaults );
	}

	/**
	 * Get a specific setting.
	 */
	public static function get_setting( $key, $default = '' ) {
		$settings = self::get_settings();
		return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
	}
}
