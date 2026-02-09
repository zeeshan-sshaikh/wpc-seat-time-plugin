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
		add_action( 'save_post', array( $this, 'handle_save_post' ), 20, 2 );
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
		$word_count      = $this->get_word_count( $content );
		$media_duration  = $this->get_media_duration( $content, $post_id );
		$h5p_duration    = $this->get_h5p_duration( $content );
		$manual_override = get_post_meta( $post_id, '_wpc_seat_time_manual_override', true );
		$manual_override = '' === $manual_override ? 0 : (int) $manual_override;

		$settings = WPC_Seat_Time_Settings::get_settings();
		
		$reading_avg  = ( (int) $settings['average_wpm'] > 0 ) ? ( $word_count / (int) $settings['average_wpm'] ) : 0;
		$reading_slow = ( (int) $settings['slow_wpm'] > 0 ) ? ( $word_count / (int) $settings['slow_wpm'] ) : 0;

		$total_seconds_avg  = ( $reading_avg * 60 ) + $media_duration + $h5p_duration + $manual_override;
		$total_seconds_slow = ( $reading_slow * 60 ) + $media_duration + $h5p_duration + $manual_override;

		$minutes_avg  = $total_seconds_avg / 60;
		$minutes_slow = $total_seconds_slow / 60;

		update_post_meta( $post_id, '_wpc_seat_time_word_count', $word_count );
		update_post_meta( $post_id, '_wpc_seat_time_media_duration', $media_duration );
		update_post_meta( $post_id, '_wpc_seat_time_h5p_duration', $h5p_duration );
		update_post_meta( $post_id, '_wpc_seat_time_minutes_average', $minutes_avg );
		update_post_meta( $post_id, '_wpc_seat_time_minutes_slow', $minutes_slow );
		
		clean_post_cache( $post_id );

		return array( 'avg' => $minutes_avg, 'slow' => $minutes_slow );
	}

	/**
	 * Get total duration of media (video/audio) in seconds.
	 */
	public function get_media_duration( $content, $post_id = 0 ) {
		$total_duration = 0;

		// 1. Detect YouTube
		$total_duration += $this->detect_youtube_duration( $content, $post_id );

		// 2. Detect Vimeo
		$total_duration += $this->detect_vimeo_duration( $content, $post_id );

		// 3. Detect Local Media (mp4, mp3)
		$total_duration += $this->detect_local_media_duration( $content );

		return apply_filters( 'wpc_seat_time_media_duration', $total_duration, $content, $post_id );
	}

	/**
	 * Detect YouTube durations in content.
	 */
	private function detect_youtube_duration( $content, $post_id ) {
		$duration = 0;
		$video_ids = array();

		// Regex for content detection (includes shorts)
		$regex = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?|shorts)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i';
		if ( preg_match_all( $regex, $content, $matches ) ) {
			$video_ids = array_merge( $video_ids, $matches[1] );
		}

		// Also check LearnDash native Video Progression field
		$video_url = $this->get_learndash_video_url( $post_id );
		if ( $video_url && preg_match( $regex, $video_url, $ld_matches ) ) {
			$video_ids[] = $ld_matches[1];
		}

		$video_ids = array_unique( $video_ids );
		if ( empty( $video_ids ) ) {
			return 0;
		}

		$settings = WPC_Seat_Time_Settings::get_settings();
		$api_key = isset( $settings['youtube_api_key'] ) ? $settings['youtube_api_key'] : '';

		foreach ( $video_ids as $id ) {
			if ( $api_key ) {
				$duration += $this->get_youtube_api_duration( $id, $api_key );
			} else {
				// Fallback to oEmbed (usually fails for duration, but good for logs)
				$duration += $this->get_remote_duration( 'https://www.youtube.com/oembed?url=https://www.youtube.com/watch?v=' . $id . '&format=json' );
			}
		}

		return $duration;
	}

	/**
	 * Fetch duration using YouTube Data API.
	 */
	private function get_youtube_api_duration( $video_id, $api_key ) {
		$cache_key = 'wpc_yt_api_' . $video_id;
		$cached = get_transient( $cache_key );
		if ( false !== $cached ) {
			return (int) $cached;
		}

		$url = sprintf( 'https://www.googleapis.com/youtube/v3/videos?id=%s&part=contentDetails&key=%s', $video_id, $api_key );
		$response = wp_remote_get( $url );

		if ( is_wp_error( $response ) ) {
			return 0;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( isset( $data['items'][0]['contentDetails']['duration'] ) ) {
			$iso_duration = $data['items'][0]['contentDetails']['duration']; // Format: PT1M30S
			try {
				$date_interval = new DateInterval( $iso_duration );
				$seconds = ( $date_interval->h * 3600 ) + ( $date_interval->i * 60 ) + $date_interval->s;
				set_transient( $cache_key, $seconds, DAY_IN_SECONDS );
				return $seconds;
			} catch ( Exception $e ) {
				return 0;
			}
		}

		return 0;
	}

	/**
	 * Detect Vimeo durations in content.
	 */
	private function detect_vimeo_duration( $content, $post_id ) {
		$duration = 0;
		$video_ids = array();
		$regex = '/vimeo\.com\/(?:video\/)?([0-9]+)/i';

		if ( preg_match_all( $regex, $content, $matches ) ) {
			$video_ids = array_merge( $video_ids, $matches[1] );
		}

		// Check LearnDash field
		$video_url = $this->get_learndash_video_url( $post_id );
		if ( $video_url && preg_match( $regex, $video_url, $ld_matches ) ) {
			$video_ids[] = $ld_matches[1];
		}

		$video_ids = array_unique( $video_ids );
		foreach ( $video_ids as $id ) {
			$duration += $this->get_remote_duration( 'https://vimeo.com/api/oembed.json?url=https://vimeo.com/' . $id );
		}
		
		return $duration;
	}

	/**
	 * Get the video URL from LearnDash's Video Progression settings for a given post.
	 */
	private function get_learndash_video_url( $post_id ) {
		if ( ! $post_id ) {
			return '';
		}

		$post_type = get_post_type( $post_id );
		$meta_key = '';
		$video_key = '';

		if ( 'sfwd-lessons' === $post_type ) {
			$meta_key = '_sfwd-lessons';
			$video_key = 'sfwd-lessons_lesson_video_url';
		} elseif ( 'sfwd-topic' === $post_type ) {
			$meta_key = '_sfwd-topic';
			$video_key = 'sfwd-topic_lesson_video_url';
		}

		if ( ! $meta_key ) {
			return '';
		}

		// 1. Try fetching from DB (might be stale on first save if hook priority isn't enough)
		$ld_settings = get_post_meta( $post_id, $meta_key, true );
		if ( isset( $ld_settings[ $video_key ] ) && ! empty( $ld_settings[ $video_key ] ) ) {
			return $ld_settings[ $video_key ];
		}

		// 2. Fallback: Check $_POST data directly to handle the "first save" race condition
		if ( isset( $_POST[ $video_key ] ) ) {
			return sanitize_text_field( $_POST[ $video_key ] );
		}

		// LearnDash sometimes nests settings in a generic array in $_POST
		if ( isset( $_POST[ $meta_key ] ) && is_array( $_POST[ $meta_key ] ) ) {
			if ( isset( $_POST[ $meta_key ][ $video_key ] ) ) {
				return sanitize_text_field( $_POST[ $meta_key ][ $video_key ] );
			}
		}

		return '';
	}

	/**
	 * Fetch duration from oEmbed provider.
	 */
	private function get_remote_duration( $url ) {
		$cache_key = 'wpc_media_' . md5( $url );
		$cached = get_transient( $cache_key );
		if ( false !== $cached ) {
			return (int) $cached;
		}

		$response = wp_remote_get( $url );
		if ( is_wp_error( $response ) ) {
			error_log( 'WPC Seat Time Error: Failed to fetch oEmbed data from ' . $url . '. Error: ' . $response->get_error_message() );
			set_transient( $cache_key, 0, HOUR_IN_SECONDS ); // Cache failure for a shorter time
			return 0;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		$duration = 0;
		if ( isset( $data['duration'] ) ) {
			$duration = (int) $data['duration'];
		} else {
			error_log( 'WPC Seat Time Warning: No duration found in oEmbed response for ' . $url );
		}

		set_transient( $cache_key, $duration, DAY_IN_SECONDS );
		return $duration;
	}

	/**
	 * Detect local media durations (mp4, mp3).
	 */
	private function detect_local_media_duration( $content ) {
		$duration = 0;
		// Regex for common video/audio extensions in src attributes or raw URLs
		$regex = '/(?:src|href)=["\']([^"\']+\.(?:mp4|mp3))["\']/i';
		
		if ( preg_match_all( $regex, $content, $matches ) ) {
			$urls = array_unique( $matches[1] );
			foreach ( $urls as $url ) {
				$duration += $this->get_local_file_duration( $url );
			}
		}

		// Also check for [video] or [audio] shortcodes
		if ( preg_match_all( '/\[(?:video|audio)[^\]]+src=["\']([^"\']+)["\'][^\]]*\]/i', $content, $matches ) ) {
			$urls = array_unique( $matches[1] );
			foreach ( $urls as $url ) {
				$duration += $this->get_local_file_duration( $url );
			}
		}

		return $duration;
	}

	/**
	 * Get duration of a local file.
	 */
	private function get_local_file_duration( $url ) {
		// 1. Try to find if it's an attachment
		$attachment_id = attachment_url_to_postid( $url );
		if ( $attachment_id ) {
			$metadata = wp_get_attachment_metadata( $attachment_id );
			if ( isset( $metadata['length'] ) ) {
				return (int) $metadata['length'];
			}
		}

		// 2. Fallback to getID3 if file is local
		$upload_dir = wp_upload_dir();
		$base_url = $upload_dir['baseurl'];
		$base_path = $upload_dir['basedir'];

		if ( strpos( $url, $base_url ) === 0 ) {
			$file_path = str_replace( $base_url, $base_path, $url );
			if ( file_exists( $file_path ) ) {
				if ( ! class_exists( 'getID3' ) ) {
					require_once ABSPATH . WPINC . '/ID3/getid3.php';
				}
				$getid3 = new getID3();
				$info = $getid3->analyze( $file_path );
				if ( isset( $info['playtime_seconds'] ) ) {
					return (int) $info['playtime_seconds'];
				}
			} else {
				error_log( 'WPC Seat Time Warning: Local file not found at ' . $file_path );
			}
		}

		return 0;
	}

	/**
	 * Get estimated duration of H5P content in seconds.
	 */
	public function get_h5p_duration( $content ) {
		$duration = 0;
		$regex = '/\[h5p[^\]]+id=["\']?([0-9]+)["\']?[^\]]*\]/i';

		if ( preg_match_all( $regex, $content, $matches ) ) {
			$h5p_count = count( $matches[0] );
			$settings = WPC_Seat_Time_Settings::get_settings();
			$default_time = isset( $settings['default_h5p_time'] ) ? (int) $settings['default_h5p_time'] : 300;
			
			$duration = $h5p_count * $default_time;
		}

		return apply_filters( 'wpc_seat_time_h5p_duration', $duration, $content );
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
	                        $self_avg   = $granular_overrides['avg'];
	                        $self_slow  = $granular_overrides['slow'];
	                        $self_words = get_post_meta( $post_id, '_wpc_seat_time_word_count', true );
	                        $self_media = get_post_meta( $post_id, '_wpc_seat_time_media_duration', true );
	                        $self_h5p   = get_post_meta( $post_id, '_wpc_seat_time_h5p_duration', true );
	                } else {
	                        $self_avg = get_post_meta( $post_id, '_wpc_seat_time_minutes_average', true );
	                        if ( '' === $self_avg ) {
	                                $post = get_post( $post_id );
	                                if ( $post ) {
	                                        $estimates = $instance->calculate_granular_estimates( $post_id, $post->post_content );
	                                        $self_avg   = $estimates['avg'];
	                                        $self_slow  = $estimates['slow'];
	                                        $self_words = get_post_meta( $post_id, '_wpc_seat_time_word_count', true );
	                                        $self_media = get_post_meta( $post_id, '_wpc_seat_time_media_duration', true );
	                                        $self_h5p   = get_post_meta( $post_id, '_wpc_seat_time_h5p_duration', true );
	                                } else {
	                                        $self_avg   = 0;
	                                        $self_slow  = 0;
	                                        $self_words = 0;
	                                        $self_media = 0;
	                                        $self_h5p   = 0;
	                                }
	                        } else {
	                                $self_avg   = (float) $self_avg;
	                                $self_slow  = (float) get_post_meta( $post_id, '_wpc_seat_time_minutes_slow', true );
	                                $self_words = (int) get_post_meta( $post_id, '_wpc_seat_time_word_count', true );
	                                $self_media = (int) get_post_meta( $post_id, '_wpc_seat_time_media_duration', true );
	                                $self_h5p   = (int) get_post_meta( $post_id, '_wpc_seat_time_h5p_duration', true );
	                        }
	                }
	
	                $total_avg   = $self_avg;
	                $total_slow  = $self_slow;
	                $total_words = $self_words;
	                $total_media = $self_media;
	                $total_h5p   = $self_h5p;
	
	                $post_type = get_post_type( $post_id );
	
	                if ( 'sfwd-courses' === $post_type ) {
	                        // 1. Get all Lessons in this course
	                        $lessons = learndash_get_lesson_list( $post_id, array( 'num' => -1 ) );
	                        if ( ! empty( $lessons ) ) {
	                                foreach ( $lessons as $lesson ) {
	                                        $child_estimates = self::calculate_cumulative_estimates( $lesson->ID, $force );
	                                        $total_avg   += $child_estimates['avg'];
	                                        $total_slow  += $child_estimates['slow'];
	                                        $total_words += $child_estimates['words'];
	                                        $total_media += $child_estimates['media'];
	                                        $total_h5p   += $child_estimates['h5p'];
	                                }
	                        }
	                        // 2. Get all independent Quizzes in this course
	                        $quizzes = learndash_get_course_quiz_list( $post_id );
	                        if ( ! empty( $quizzes ) ) {
	                                foreach ( $quizzes as $quiz ) {
	                                        $post_obj = $quiz['post'];
	                                        $child_estimates = self::calculate_cumulative_estimates( $post_obj->ID, $force );
	                                        $total_avg   += $child_estimates['avg'];
	                                        $total_slow  += $child_estimates['slow'];
	                                        $total_words += $child_estimates['words'];
	                                        $total_media += $child_estimates['media'];
	                                        $total_h5p   += $child_estimates['h5p'];
	                                }
	                        }
	                } elseif ( 'sfwd-lessons' === $post_type ) {
	                        // Sum topics and quizzes nested in this lesson
	                        $topics = learndash_get_topic_list( $post_id );
	                        if ( ! empty( $topics ) ) {
	                                foreach ( $topics as $topic ) {
	                                        $child_estimates = self::calculate_cumulative_estimates( $topic->ID, $force );
	                                        $total_avg   += $child_estimates['avg'];
	                                        $total_slow  += $child_estimates['slow'];
	                                        $total_words += $child_estimates['words'];
	                                        $total_media += $child_estimates['media'];
	                                        $total_h5p   += $child_estimates['h5p'];
	                                }
	                        }
	                        $quizzes = learndash_get_lesson_quiz_list( $post_id );
	                        if ( ! empty( $quizzes ) ) {
	                                foreach ( $quizzes as $quiz ) {
	                                        $post_obj = $quiz['post'];
	                                        $child_estimates = self::calculate_cumulative_estimates( $post_obj->ID, $force );
	                                        $total_avg   += $child_estimates['avg'];
	                                        $total_slow  += $child_estimates['slow'];
	                                        $total_words += $child_estimates['words'];
	                                        $total_media += $child_estimates['media'];
	                                        $total_h5p   += $child_estimates['h5p'];
	                                }
	                        }
	                } elseif ( 'sfwd-topic' === $post_type ) {
	                        // Sum quizzes nested in this topic
	                        $quizzes = learndash_get_lesson_quiz_list( $post_id );
	                        if ( ! empty( $quizzes ) ) {
	                                foreach ( $quizzes as $quiz ) {
	                                        $post_obj = $quiz['post'];
	                                        $child_estimates = self::calculate_cumulative_estimates( $post_obj->ID, $force );
	                                        $total_avg   += $child_estimates['avg'];
	                                        $total_slow  += $child_estimates['slow'];
	                                        $total_words += $child_estimates['words'];
	                                        $total_media += $child_estimates['media'];
	                                        $total_h5p   += $child_estimates['h5p'];
	                                }
	                        }
	                }
	
	                update_post_meta( $post_id, '_wpc_seat_time_minutes_average_cumulative', $total_avg );
	                update_post_meta( $post_id, '_wpc_seat_time_minutes_slow_cumulative', $total_slow );
	                update_post_meta( $post_id, '_wpc_seat_time_word_count_cumulative', $total_words );
	                update_post_meta( $post_id, '_wpc_seat_time_media_duration_cumulative', $total_media );
	                update_post_meta( $post_id, '_wpc_seat_time_h5p_duration_cumulative', $total_h5p );
	
	                self::$calculation_cache[ $post_id ] = array(
	                        'avg'   => $total_avg,
	                        'slow'  => $total_slow,
	                        'words' => $total_words,
	                        'media' => $total_media,
	                        'h5p'   => $total_h5p,
	                );
	
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