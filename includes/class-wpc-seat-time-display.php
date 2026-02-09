<?php
/**
 * Formatting and Display logic - Centralized for Cumulative Seat Time
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPC_Seat_Time_Display {

	/**
	 * Flag to prevent double display.
	 */
	private static $displayed = false;

	/**
	 * Constructor to initialize hooks.
	 */
	public function __construct() {
		// Register LearnDash hooks
		add_action( 'learndash_course_before', array( $this, 'display_course_seat_time' ) );
		add_action( 'learndash_lesson_before', array( $this, 'display_lesson_seat_time' ) );
		add_action( 'learndash_topic_before', array( $this, 'display_topic_seat_time' ) );
		add_action( 'learndash_quiz_before', array( $this, 'display_quiz_seat_time' ) );

		// Fallback for cases where LearnDash hooks are not used
		add_filter( 'the_content', array( $this, 'append_seat_time_to_content' ) );
	}

	/**
	 * Display seat time on course page.
	 */
	public function display_course_seat_time( $post_id = null ) {
		$post_id = $post_id ? $post_id : get_the_ID();
		$this->render_display( $post_id );
	}

	/**
	 * Display seat time on lesson page.
	 */
	public function display_lesson_seat_time( $post_id = null ) {
		$post_id = $post_id ? $post_id : get_the_ID();
		$this->render_display( $post_id );
	}

	/**
	 * Display seat time on topic page.
	 */
	public function display_topic_seat_time( $post_id = null ) {
		$post_id = $post_id ? $post_id : get_the_ID();
		$this->render_display( $post_id );
	}

	/**
	 * Display seat time on quiz page.
	 */
	public function display_quiz_seat_time( $post_id = null ) {
		$post_id = $post_id ? $post_id : get_the_ID();
		$this->render_display( $post_id );
	}

	/**
	 * Shared rendering logic.
	 */
	private function render_display( $post_id ) {
		if ( self::$displayed ) {
			return;
		}

		$duration = $this->get_cumulative_duration( $post_id );

		if ( ! empty( $duration ) ) {
			self::$displayed = true;
			echo '<div class="wpc-seat-time-estimate" style="margin-bottom: 20px; font-weight: bold;">' . $duration . '</div>';
		}
	}

	/**
	 * Fallback filter for the_content.
	 */
	public function append_seat_time_to_content( $content ) {
		if ( self::$displayed || ! is_main_query() || ! in_the_loop() || ! is_singular() ) {
			return $content;
		}

		$post_type = get_post_type();
		$ld_post_types = array( 'sfwd-courses', 'sfwd-lessons', 'sfwd-topic', 'sfwd-quiz' );

		if ( ! in_array( $post_type, $ld_post_types, true ) ) {
			return $content;
		}

		$post_id = get_the_ID();
		$duration = $this->get_cumulative_duration( $post_id );

		if ( ! empty( $duration ) ) {
			self::$displayed = true;
			$display = '<div class="wpc-seat-time-estimate" style="margin-bottom: 20px; font-weight: bold;">' . $duration . '</div>';
			return $display . $content;
		}

		return $content;
	}

	/**
	 * Get the cumulative duration for any post level.
	 */
	public function get_cumulative_duration( $post_id ) {
		$minutes_average = get_post_meta( $post_id, '_wpc_seat_time_minutes_average_cumulative', true );

		// Trigger calculation if missing
		if ( '' === $minutes_average ) {
			$estimates = WPC_Seat_Time_Calculator::calculate_cumulative_estimates( $post_id );
			$minutes_average = $estimates['avg'];
			$minutes_slow = $estimates['slow'];
		} else {
			$minutes_average = (float) $minutes_average;
			$minutes_slow = (float) get_post_meta( $post_id, '_wpc_seat_time_minutes_slow_cumulative', true );
		}

		if ( $minutes_average <= 0 ) {
			return '';
		}

		$avg_round = ceil( $minutes_average );
		$slow_round = ceil( $minutes_slow );

		$label = WPC_Seat_Time_Settings::get_setting( 'display_label', __( 'Estimated Seat Time:', 'wpc-seat-time-estimates' ) );

		if ( $avg_round === $slow_round ) {
			$duration = sprintf( _n( '%d minute', '%d minutes', $avg_round, 'wpc-seat-time-estimates' ), $avg_round );
		} else {
			$duration = sprintf( __( '%1$d–%2$d minutes', 'wpc-seat-time-estimates' ), $avg_round, $slow_round );
		}

		return esc_html( $label ) . ' ' . esc_html( $duration );
	}

	/**
	 * Legacy support - kept for potential external calls but internally redirected.
	 */
	public static function get_formatted_duration( $post_id ) {
		$display = new self();
		return $display->get_cumulative_duration( $post_id );
	}

	public static function get_formatted_duration_aggregated( $post_id ) {
		return self::get_formatted_duration( $post_id );
	}
}

// Initialize the display class
new WPC_Seat_Time_Display();
