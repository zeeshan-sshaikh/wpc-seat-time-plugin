<?php
/**
 * Admin settings page logic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPC_Seat_Time_Admin {

	/**
	 * Constructor to initialize hooks.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'update_option_wpc_seat_time_settings', array( $this, 'check_for_recalculation' ), 10, 2 );
		add_action( 'admin_notices', array( $this, 'display_recalculation_notice' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_seat_time_metabox' ) );
		add_action( 'save_post', array( $this, 'save_metabox_data' ) );
	}

	/**
	 * Add meta box to LearnDash post types.
	 */
	public function add_seat_time_metabox() {
		$post_types = array( 'sfwd-courses', 'sfwd-lessons', 'sfwd-topic', 'sfwd-quiz' );
		foreach ( $post_types as $post_type ) {
			add_meta_box(
				'wpc_seat_time_metabox',
				__( 'Seat Time Settings', 'wpc-seat-time-estimates' ),
				array( $this, 'render_seat_time_metabox' ),
				$post_type,
				'side',
				'default'
			);
		}
	}

	/**
	 * Render the seat time meta box.
	 */
	public function render_seat_time_metabox( $post ) {
		wp_nonce_field( 'wpc_seat_time_metabox_nonce', 'wpc_seat_time_metabox_nonce_field' );
		
		$manual_override = get_post_meta( $post->ID, '_wpc_seat_time_manual_override', true );
		
		// Granular stats
		$word_count     = get_post_meta( $post->ID, '_wpc_seat_time_word_count', true );
		$media_duration = get_post_meta( $post->ID, '_wpc_seat_time_media_duration', true );
		$h5p_duration   = get_post_meta( $post->ID, '_wpc_seat_time_h5p_duration', true );

		// Cumulative stats
		$word_count_cum     = get_post_meta( $post->ID, '_wpc_seat_time_word_count_cumulative', true );
		$media_duration_cum = get_post_meta( $post->ID, '_wpc_seat_time_media_duration_cumulative', true );
		$h5p_duration_cum   = get_post_meta( $post->ID, '_wpc_seat_time_h5p_duration_cumulative', true );
		
		?>
		<p>
			<label for="wpc_seat_time_manual_override"><strong><?php _e( 'Manual Override (seconds)', 'wpc-seat-time-estimates' ); ?></strong></label><br>
			<input type="number" id="wpc_seat_time_manual_override" name="wpc_seat_time_manual_override" value="<?php echo esc_attr( $manual_override ); ?>" class="widefat">
			<span class="description"><?php _e( 'Manually add or override the estimated duration in seconds.', 'wpc-seat-time-estimates' ); ?></span>
		</p>
		<hr>
		<p>
			<strong><?php _e( 'Detected Stats (This Item):', 'wpc-seat-time-estimates' ); ?></strong><br>
			<?php _e( 'Words:', 'wpc-seat-time-estimates' ); ?> <?php echo esc_html( $word_count ? $word_count : 0 ); ?><br>
			<?php _e( 'Media:', 'wpc-seat-time-estimates' ); ?> <?php echo esc_html( $media_duration ? $media_duration : 0 ); ?>s<br>
			<?php _e( 'H5P:', 'wpc-seat-time-estimates' ); ?> <?php echo esc_html( $h5p_duration ? $h5p_duration : 0 ); ?>s
		</p>
		<?php if ( 'sfwd-quiz' !== $post->post_type ) : ?>
		<hr>
		<p>
			<strong><?php _e( 'Cumulative Stats (Total):', 'wpc-seat-time-estimates' ); ?></strong><br>
			<?php _e( 'Words:', 'wpc-seat-time-estimates' ); ?> <?php echo esc_html( $word_count_cum ? $word_count_cum : 0 ); ?><br>
			<?php _e( 'Media:', 'wpc-seat-time-estimates' ); ?> <?php echo esc_html( $media_duration_cum ? $media_duration_cum : 0 ); ?>s<br>
			<?php _e( 'H5P:', 'wpc-seat-time-estimates' ); ?> <?php echo esc_html( $h5p_duration_cum ? $h5p_duration_cum : 0 ); ?>s
		</p>
		<?php endif; ?>
		<?php
	}

	/**
	 * Save meta box data.
	 */
	public function save_metabox_data( $post_id ) {
		if ( ! isset( $_POST['wpc_seat_time_metabox_nonce_field'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['wpc_seat_time_metabox_nonce_field'], 'wpc_seat_time_metabox_nonce' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( isset( $_POST['wpc_seat_time_manual_override'] ) ) {
			$manual_override = sanitize_text_field( $_POST['wpc_seat_time_manual_override'] );
			update_post_meta( $post_id, '_wpc_seat_time_manual_override', $manual_override );
		}
	}

	/**
	 * Check if WPM or H5P settings changed and trigger recalculation.
	 */
	public function check_for_recalculation( $old_value, $new_value ) {
		$recalculation_needed = false;

		// Check WPM settings
		$old_avg_wpm = isset( $old_value['average_wpm'] ) ? (int) $old_value['average_wpm'] : 0;
		$new_avg_wpm = isset( $new_value['average_wpm'] ) ? (int) $new_value['average_wpm'] : 0;
		if ( $old_avg_wpm !== $new_avg_wpm ) {
			$recalculation_needed = true;
		}

		$old_slow_wpm = isset( $old_value['slow_wpm'] ) ? (int) $old_value['slow_wpm'] : 0;
		$new_slow_wpm = isset( $new_value['slow_wpm'] ) ? (int) $new_value['slow_wpm'] : 0;
		if ( $old_slow_wpm !== $new_slow_wpm ) {
			$recalculation_needed = true;
		}

		// Check H5P time setting
		$old_h5p_time = isset( $old_value['default_h5p_time'] ) ? (int) $old_value['default_h5p_time'] : 0;
		$new_h5p_time = isset( $new_value['default_h5p_time'] ) ? (int) $new_value['default_h5p_time'] : 0;
		if ( $old_h5p_time !== $new_h5p_time ) {
			$recalculation_needed = true;
		}

		if ( $recalculation_needed ) {
			$this->_run_full_recalculation();
		}
	}

	/**
	 * Run a full recalculation of all LearnDash content.
	 */
	private function _run_full_recalculation() {
		$post_types = array( 'sfwd-courses', 'sfwd-lessons', 'sfwd-topic', 'sfwd-quiz' );
		$args = array(
			'post_type' => $post_types,
			'posts_per_page' => -1,
			'post_status' => 'any',
			'fields' => 'ids', // More efficient
		);
		$posts = get_posts( $args );

		$calculator = new WPC_Seat_Time_Calculator();

		// First, do all granular calculations.
		foreach ( $posts as $post_id ) {
			$post_content = get_post_field( 'post_content', $post_id );
			$calculator->calculate_granular_estimates( $post_id, $post_content );
		}

		// Then, do all cumulative calculations, starting from top-level courses.
		$course_args = array(
			'post_type' => 'sfwd-courses',
			'posts_per_page' => -1,
			'post_status' => 'any',
			'fields' => 'ids',
		);
		$courses = get_posts( $course_args );
		foreach ( $courses as $course_id ) {
			WPC_Seat_Time_Calculator::calculate_cumulative_estimates( $course_id, true );
		}

		// Set a transient to show a one-time admin notice.
		set_transient( 'wpc_seat_time_recalculated_notice', true, 5 );
	}

	/**
	 * Display admin notice after recalculation.
	 */
	public function display_recalculation_notice() {
		if ( get_transient( 'wpc_seat_time_recalculated_notice' ) ) {
			?>
			<div class="notice notice-success is-dismissible">
				<p><?php _e( 'Seat time estimates for all LearnDash content have been successfully recalculated based on the new WPM settings.', 'wpc-seat-time-estimates' ); ?></p>
			</div>
			<?php
			delete_transient( 'wpc_seat_time_recalculated_notice' );
		}
	}

	/**
	 * Add settings page to the admin menu.
	 */
	public function add_settings_page() {
		add_options_page(
			__( 'WPC Seat Time Estimates', 'wpc-seat-time-estimates' ),
			__( 'Seat Time Estimates', 'wpc-seat-time-estimates' ),
			'manage_options',
			'wpc-seat-time-estimates',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register settings using WordPress Settings API.
	 */
	public function register_settings() {
		register_setting(
			'wpc_seat_time_settings_group',
			'wpc_seat_time_settings',
			array( $this, 'sanitize_settings' )
		);

		add_settings_section(
			'wpc_seat_time_main_section',
			__( 'General Settings', 'wpc-seat-time-estimates' ),
			null,
			'wpc-seat-time-estimates'
		);

		add_settings_field(
			'average_wpm',
			__( 'Average Reading Speed (WPM)', 'wpc-seat-time-estimates' ),
			array( $this, 'render_number_field' ),
			'wpc-seat-time-estimates',
			'wpc_seat_time_main_section',
			array( 'label_for' => 'average_wpm' )
		);

		add_settings_field(
			'slow_wpm',
			__( 'Slow Reading Speed (WPM)', 'wpc-seat-time-estimates' ),
			array( $this, 'render_number_field' ),
			'wpc-seat-time-estimates',
			'wpc_seat_time_main_section',
			array( 'label_for' => 'slow_wpm' )
		);

		add_settings_field(
			'display_label',
			__( 'Display Label', 'wpc-seat-time-estimates' ),
			array( $this, 'render_text_field' ),
			'wpc-seat-time-estimates',
			'wpc_seat_time_main_section',
			array( 'label_for' => 'display_label' )
		);

		add_settings_section(
			'wpc_seat_time_media_section',
			__( 'Media & H5P Settings', 'wpc-seat-time-estimates' ),
			null,
			'wpc-seat-time-estimates'
		);

		add_settings_field(
			'youtube_api_key',
			__( 'YouTube API Key', 'wpc-seat-time-estimates' ),
			array( $this, 'render_text_field' ),
			'wpc-seat-time-estimates',
			'wpc_seat_time_media_section',
			array( 
				'label_for' => 'youtube_api_key',
				'description' => __( 'Optional. Used to fetch video durations more reliably if oEmbed fails. Get one from Google Cloud Console.', 'wpc-seat-time-estimates' )
			)
		);

		add_settings_field(
			'default_h5p_time',
			__( 'Default H5P Time (seconds)', 'wpc-seat-time-estimates' ),
			array( $this, 'render_number_field' ),
			'wpc-seat-time-estimates',
			'wpc_seat_time_media_section',
			array( 
				'label_for' => 'default_h5p_time',
				'description' => __( 'Estimated time in seconds for each H5P interaction found in the content. Default is 300s (5m).', 'wpc-seat-time-estimates' )
			)
		);
	}

	/**
	 * Sanitize settings input.
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();

		if ( isset( $input['average_wpm'] ) ) {
			$sanitized['average_wpm'] = absint( $input['average_wpm'] );
			if ( $sanitized['average_wpm'] <= 0 ) {
				$sanitized['average_wpm'] = 200;
			}
		}

		if ( isset( $input['slow_wpm'] ) ) {
			$sanitized['slow_wpm'] = absint( $input['slow_wpm'] );
			if ( $sanitized['slow_wpm'] <= 0 ) {
				$sanitized['slow_wpm'] = 120;
			}
		}

		if ( isset( $input['display_label'] ) ) {
			$sanitized['display_label'] = sanitize_text_field( $input['display_label'] );
		}

		if ( isset( $input['youtube_api_key'] ) ) {
			$sanitized['youtube_api_key'] = sanitize_text_field( $input['youtube_api_key'] );
		}

		if ( isset( $input['default_h5p_time'] ) ) {
			$sanitized['default_h5p_time'] = absint( $input['default_h5p_time'] );
		}

		return $sanitized;
	}

	/**
	 * Render a number input field.
	 */
	public function render_number_field( $args ) {
		$settings = WPC_Seat_Time_Settings::get_settings();
		$id = $args['label_for'];
		$value = isset( $settings[ $id ] ) ? $settings[ $id ] : '';
		$description = isset( $args['description'] ) ? $args['description'] : '';
		
		if ( ! $description ) {
			$description = ( 'average_wpm' === $id ? __( 'Standard reading speed for average calculation.', 'wpc-seat-time-estimates' ) : __( 'Slower reading speed for range calculation.', 'wpc-seat-time-estimates' ) );
		}
		
		echo '<input type="number" id="' . esc_attr( $id ) . '" name="wpc_seat_time_settings[' . esc_attr( $id ) . ']" value="' . esc_attr( $value ) . '" class="small-text">';
		echo '<p class="description">' . esc_html( $description ) . '</p>';
	}

	/**
	 * Render a text input field.
	 */
	public function render_text_field( $args ) {
		$settings = WPC_Seat_Time_Settings::get_settings();
		$id = $args['label_for'];
		$value = isset( $settings[ $id ] ) ? $settings[ $id ] : '';
		$description = isset( $args['description'] ) ? $args['description'] : __( 'Label shown before the estimated time on the frontend.', 'wpc-seat-time-estimates' );
		
		echo '<input type="text" id="' . esc_attr( $id ) . '" name="wpc_seat_time_settings[' . esc_attr( $id ) . ']" value="' . esc_attr( $value ) . '" class="regular-text">';
		echo '<p class="description">' . esc_html( $description ) . '</p>';
	}

	/**
	 * Render the settings page.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'wpc_seat_time_settings_group' );
				do_settings_sections( 'wpc-seat-time-estimates' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}

new WPC_Seat_Time_Admin();