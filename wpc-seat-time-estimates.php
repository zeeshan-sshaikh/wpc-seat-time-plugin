<?php
/**
 * Plugin Name: WPC Seat Time Estimates for LearnDash
 * Description: Automated calculation and display of estimated seat time based on word count for LearnDash courses, lessons, topics, and quizzes.
 * Version: 1.0.0
 * Author: WPC
 * Text Domain: wpc-seat-time-estimates
 * License: GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Plugin Class
 */
class WPC_Seat_Time_Estimates {

	/**
	 * Instance of this class.
	 *
	 * @var WPC_Seat_Time_Estimates
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin.
	 */
	public function __construct() {
		$this->load_dependencies();
		$this->init_hooks();
	}

	/**
	 * Return an instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Load dependencies.
	 */
	private function load_dependencies() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpc-seat-time-settings.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpc-seat-time-calculator.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpc-seat-time-display.php';
		
		if ( is_admin() ) {
			require_once plugin_dir_path( __FILE__ ) . 'admin/class-wpc-seat-time-admin.php';
		} else {
			require_once plugin_dir_path( __FILE__ ) . 'public/class-wpc-seat-time-public.php';
		}
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	/**
	 * Run after plugins are loaded.
	 */
	public function init() {
		add_action( 'admin_init', array( $this, 'upgrade_options' ) );
	}

	/**
	 * Upgrade options if needed.
	 */
	public function upgrade_options() {
		$settings = get_option( 'wpc_seat_time_settings' );
		if ( is_array( $settings ) && isset( $settings['display_label'] ) && 'Estimated duration:' === $settings['display_label'] ) {
			$settings['display_label'] = __( 'Estimated Seat Time:', 'wpc-seat-time-estimates' );
			update_option( 'wpc_seat_time_settings', $settings );
		}
	}
}

/**
 * Activation Hook
 */
function wpc_seat_time_activate() {
	$defaults = array(
		'average_wpm'   => 200,
		'slow_wpm'      => 120,
		'display_label' => __( 'Estimated Seat Time:', 'wpc-seat-time-estimates' ),
	);

	if ( false === get_option( 'wpc_seat_time_settings' ) ) {
		update_option( 'wpc_seat_time_settings', $defaults );
	}
}
register_activation_hook( __FILE__, 'wpc_seat_time_activate' );

/**
 * Start the plugin.
 */
function wpc_seat_time_run() {
	return WPC_Seat_Time_Estimates::get_instance();
}
wpc_seat_time_run();
