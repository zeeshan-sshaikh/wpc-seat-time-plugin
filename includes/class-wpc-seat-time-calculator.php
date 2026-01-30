<?php
/**
 * Calculator logic for Cumulative Seat Time Estimates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPC_Seat_Time_Calculator {

	/**
	 * Cache to prevent redundant calculations during the same request.
	 * 
	 * @var array
	 */
	private static $calculation_cache = array();

	/**
	 * Constructor to initialize hooks.
	 */
	public function __construct() {
		add_action( 'save_post', array( $this, 'handle_save_post' ), 10, 2 );
	}

	/**
	 * Hook into save_post to calculate estimates.
	 */
	public function handle_save_post( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! $this->is_learndash_post_type( $post->post_type ) ) {
			return;
		}

		// Prevent infinite loops
		remove_action( 'save_post', array( $this, 'handle_save_post' ), 10 );

		// 1. Calculate the item's own granular estimates
		$granular_estimates = $this->calculate_granular_estimates( $post_id, $post->post_content );

		// 2. Trigger cumulative update for the hierarchy
		self::calculate_cumulative_estimates( $post_id, true, $granular_estimates );

		// 3. Update parents in the hierarchy
		$this->update_parents( $post_id, $post->post_type );

		add_action( 'save_post', array( $this, 'handle_save_post' ), 10, 2 );
	}

	/**
	 * Update parent items when a child is updated.
	 */
	private function update_parents( $post_id, $post_type ) {
		// 1. Topic -> Lesson -> Course
		if ( 'sfwd-topic' === $post_type ) {
			$lesson_id = get_post_meta( $post_id, 'lesson_id', true );
			if ( $lesson_id ) {
				self::calculate_cumulative_estimates( $lesson_id, true );
				$this->update_parents( $lesson_id, 'sfwd-lessons' );
			} else {
				$course_id = get_post_meta( $post_id, 'course_id', true );
				if ( $course_id ) {
					self::calculate_cumulative_estimates( $course_id, true );
				}
			}
		} 
		// 2. Lesson -> Course
		elseif ( 'sfwd-lessons' === $post_type ) {
			$course_id = get_post_meta( $post_id, 'course_id', true );
			if ( ! $course_id ) {
				$course_id = learndash_get_course_id( $post_id );
			}
			if ( $course_id ) {
				self::calculate_cumulative_estimates( $course_id, true );
			}
		}
		// 3. Quiz -> Topic or Lesson or Course
		elseif ( 'sfwd-quiz' === $post_type ) {
			$topic_id = get_post_meta( $post_id, 'topic_id', true );
			$lesson_id = get_post_meta( $post_id, 'lesson_id', true );
			$course_id = get_post_meta( $post_id, 'course_id', true );

			if ( $topic_id ) {
				self::calculate_cumulative_estimates( $topic_id, true );
				$this->update_parents( $topic_id, 'sfwd-topic' );
			} elseif ( $lesson_id ) {
				self::calculate_cumulative_estimates( $lesson_id, true );
				$this->update_parents( $lesson_id, 'sfwd-lessons' );
			} elseif ( $course_id ) {
				self::calculate_cumulative_estimates( $course_id, true );
			}
		}
	}

	/**
	 * Calculate granular (self-only) word count and estimates.
	 */
	public function calculate_granular_estimates( $post_id, $content ) {
		$word_count = $this->get_word_count( $content );
		$settings = WPC_Seat_Time_Settings::get_settings();
		
		$minutes_avg = ( (int) $settings['average_wpm'] > 0 ) ? ( $word_count / (int) $settings['average_wpm'] ) : 0;
		$minutes_slow = ( (int) $settings['slow_wpm'] > 0 ) ? ( $word_count / (int) $settings['slow_wpm'] ) : 0;

		update_post_meta( $post_id, '_wpc_seat_time_word_count', $word_count );
		update_post_meta( $post_id, '_wpc_seat_time_minutes_average', $minutes_avg );
		update_post_meta( $post_id, '_wpc_seat_time_minutes_slow', $minutes_slow );
		
		clean_post_cache( $post_id );

		return array( 'avg' => $minutes_avg, 'slow' => $minutes_slow );
	}

	/**
	 * Calculate cumulative estimates including children.
	 */
	public static function calculate_cumulative_estimates( $post_id, $force = false, $granular_overrides = null ) {
		if ( ! $force && isset( self::$calculation_cache[ $post_id ] ) ) {
			return self::$calculation_cache[ $post_id ];
		}

		$instance = new self();
		
		// Use overrides if provided, otherwise fetch granular estimates
		if ( is_array( $granular_overrides ) && isset( $granular_overrides['avg'] ) ) {
			$self_avg = $granular_overrides['avg'];
			$self_slow = $granular_overrides['slow'];
		} else {
			$self_avg = get_post_meta( $post_id, '_wpc_seat_time_minutes_average', true );
			if ( '' === $self_avg ) {
				$post = get_post( $post_id );
				if ( $post ) {
					$estimates = $instance->calculate_granular_estimates( $post_id, $post->post_content );
					$self_avg = $estimates['avg'];
					$self_slow = $estimates['slow'];
				} else {
					$self_avg = 0;
					$self_slow = 0;
				}
			} else {
				$self_avg = (float) $self_avg;
				$self_slow = (float) get_post_meta( $post_id, '_wpc_seat_time_minutes_slow', true );
			}
		}

		$total_avg = $self_avg;
		$total_slow = $self_slow;

		$post_type = get_post_type( $post_id );

		if ( 'sfwd-courses' === $post_type ) {
			// 1. Get all Lessons in this course
			$lessons = learndash_get_lesson_list( $post_id, array( 'num' => -1 ) );
			if ( ! empty( $lessons ) ) {
				foreach ( $lessons as $lesson ) {
					$child_estimates = self::calculate_cumulative_estimates( $lesson->ID, $force );
					$total_avg += $child_estimates['avg'];
					$total_slow += $child_estimates['slow'];
				}
			}
			// 2. Get all independent Quizzes in this course
			$quizzes = learndash_get_course_quiz_list( $post_id );
			if ( ! empty( $quizzes ) ) {
				foreach ( $quizzes as $quiz ) {
					$post_obj = $quiz['post'];
					$q_avg = get_post_meta( $post_obj->ID, '_wpc_seat_time_minutes_average', true );
					if ( '' === $q_avg ) {
						$estimates = $instance->calculate_granular_estimates( $post_obj->ID, $post_obj->post_content );
						$q_avg = $estimates['avg'];
						$q_slow = $estimates['slow'];
					} else {
						$q_avg = (float) $q_avg;
						$q_slow = (float) get_post_meta( $post_obj->ID, '_wpc_seat_time_minutes_slow', true );
					}
					$total_avg += $q_avg;
					$total_slow += $q_slow;
				}
			}
		} elseif ( 'sfwd-lessons' === $post_type ) {
			// Sum topics and quizzes nested in this lesson
			$topics = learndash_get_topic_list( $post_id );
			if ( ! empty( $topics ) ) {
				foreach ( $topics as $topic ) {
					$child_estimates = self::calculate_cumulative_estimates( $topic->ID, $force );
					$total_avg += $child_estimates['avg'];
					$total_slow += $child_estimates['slow'];
				}
			}
			$quizzes = learndash_get_lesson_quiz_list( $post_id );
			if ( ! empty( $quizzes ) ) {
				foreach ( $quizzes as $quiz ) {
					$post_obj = $quiz['post'];
					$q_avg = get_post_meta( $post_obj->ID, '_wpc_seat_time_minutes_average', true );
					if ( '' === $q_avg ) {
						$estimates = $instance->calculate_granular_estimates( $post_obj->ID, $post_obj->post_content );
						$q_avg = $estimates['avg'];
						$q_slow = $estimates['slow'];
					} else {
						$q_avg = (float) $q_avg;
						$q_slow = (float) get_post_meta( $post_obj->ID, '_wpc_seat_time_minutes_slow', true );
					}
					$total_avg += $q_avg;
					$total_slow += $q_slow;
				}
			}
		} elseif ( 'sfwd-topic' === $post_type ) {
			// Sum quizzes nested in this topic
			$quizzes = learndash_get_lesson_quiz_list( $post_id );
			if ( ! empty( $quizzes ) ) {
				foreach ( $quizzes as $quiz ) {
					$post_obj = $quiz['post'];
					$q_avg = get_post_meta( $post_obj->ID, '_wpc_seat_time_minutes_average', true );
					if ( '' === $q_avg ) {
						$estimates = $instance->calculate_granular_estimates( $post_obj->ID, $post_obj->post_content );
						$q_avg = $estimates['avg'];
						$q_slow = $estimates['slow'];
					} else {
						$q_avg = (float) $q_avg;
						$q_slow = (float) get_post_meta( $post_obj->ID, '_wpc_seat_time_minutes_slow', true );
					}
					$total_avg += $q_avg;
					$total_slow += $q_slow;
				}
			}
		}

		update_post_meta( $post_id, '_wpc_seat_time_minutes_average_cumulative', $total_avg );
		update_post_meta( $post_id, '_wpc_seat_time_minutes_slow_cumulative', $total_slow );

		self::$calculation_cache[ $post_id ] = array( 'avg' => $total_avg, 'slow' => $total_slow );
		
		return self::$calculation_cache[ $post_id ];
	}

	private function is_learndash_post_type( $post_type ) {
		return in_array( $post_type, array( 'sfwd-courses', 'sfwd-lessons', 'sfwd-topic', 'sfwd-quiz' ), true );
	}

	public function get_word_count( $content ) {
		$clean = wp_strip_all_tags( $content );
		$clean = preg_replace( '/\s+/', ' ', $clean );
		return count( array_filter( explode( ' ', trim( $clean ) ) ) );
	}
}

new WPC_Seat_Time_Calculator();