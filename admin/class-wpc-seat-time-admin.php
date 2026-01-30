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
	}

	/**
	 * Check if WPM settings changed and trigger recalculation.
	 */
	public function check_for_recalculation( $old_value, $new_value ) {
		$old_avg_wpm = isset( $old_value['average_wpm'] ) ? (int) $old_value['average_wpm'] : 0;
		$new_avg_wpm = isset( $new_value['average_wpm'] ) ? (int) $new_value['average_wpm'] : 0;
		$old_slow_wpm = isset( $old_value['slow_wpm'] ) ? (int) $old_value['slow_wpm'] : 0;
		$new_slow_wpm = isset( $new_value['slow_wpm'] ) ? (int) $new_value['slow_wpm'] : 0;

		if ( $old_avg_wpm !== $new_avg_wpm || $old_slow_wpm !== $new_slow_wpm ) {
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
			__( 'Calculation Settings', 'wpc-seat-time-estimates' ),
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

		return $sanitized;
	}

	/**
	 * Render a number input field.
	 */
	public function render_number_field( $args ) {
		$settings = WPC_Seat_Time_Settings::get_settings();
		$id = $args['label_for'];
		$value = isset( $settings[ $id ] ) ? $settings[ $id ] : '';
		
		echo '<input type="number" id="' . esc_attr( $id ) . '" name="wpc_seat_time_settings[' . esc_attr( $id ) . ']" value="' . esc_attr( $value ) . '" class="small-text">';
		echo '<p class="description">' . ( 'average_wpm' === $id ? __( 'Standard reading speed for average calculation.', 'wpc-seat-time-estimates' ) : __( 'Slower reading speed for range calculation.', 'wpc-seat-time-estimates' ) ) . '</p>';
	}

	/**
	 * Render a text input field.
	 */
	public function render_text_field( $args ) {
		$settings = WPC_Seat_Time_Settings::get_settings();
		$id = $args['label_for'];
		$value = isset( $settings[ $id ] ) ? $settings[ $id ] : '';
		
		echo '<input type="text" id="' . esc_attr( $id ) . '" name="wpc_seat_time_settings[' . esc_attr( $id ) . ']" value="' . esc_attr( $value ) . '" class="regular-text">';
		echo '<p class="description">' . __( 'Label shown before the estimated time on the frontend.', 'wpc-seat-time-estimates' ) . '</p>';
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