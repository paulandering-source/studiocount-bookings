<?php
/**
 * StudioCount Bookings settings and connection check.
 *
 * @package StudioCount_Bookings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides one bounded WordPress settings screen.
 */
final class StudioCount_Bookings_Settings {
	/**
	 * Registers admin hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_page' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_setting' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'wp_ajax_studiocount_bookings_check_connection', array( __CLASS__, 'check_connection' ) );
	}

	/**
	 * Adds the settings page.
	 *
	 * @return void
	 */
	public static function add_page() {
		add_options_page(
			__( 'StudioCount Bookings', 'studiocount-bookings' ),
			__( 'StudioCount Bookings', 'studiocount-bookings' ),
			'manage_options',
			'studiocount-bookings',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Registers the one plugin option.
	 *
	 * @return void
	 */
	public static function register_setting() {
		register_setting(
			'studiocount_bookings',
			StudioCount_Bookings_Renderer::OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => array(
					'studio_slug'  => '',
					'default_view' => 'both',
				),
			)
		);
	}

	/**
	 * Validates settings without silently changing an invalid studio.
	 *
	 * @param mixed $input Submitted option value.
	 * @return array{studio_slug:string,default_view:string}
	 */
	public static function sanitize( $input ) {
		$input   = is_array( $input ) ? $input : array();
		$current = StudioCount_Bookings_Renderer::get_options();
		$raw     = trim( (string) ( $input['studio_slug'] ?? '' ) );
		$studio  = StudioCount_Bookings_Renderer::normalize_studio( $raw );
		$view    = StudioCount_Bookings_Renderer::normalize_view( $input['default_view'] ?? 'both' );

		if ( '' !== $raw && '' === $studio ) {
			add_settings_error(
				StudioCount_Bookings_Renderer::OPTION_NAME,
				'studiocount_bookings_invalid_studio',
				__( 'Enter a StudioCount booking URL or studio slug, such as studioone.', 'studiocount-bookings' )
			);
			$studio = $current['studio_slug'];
		}

		return array(
			'studio_slug'  => $studio,
			'default_view' => $view,
		);
	}

	/**
	 * Loads local settings assets only on this page.
	 *
	 * @param string $hook_suffix Current admin hook.
	 * @return void
	 */
	public static function enqueue_assets( $hook_suffix ) {
		if ( 'settings_page_studiocount-bookings' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style(
			'studiocount-bookings-admin',
			STUDIOCOUNT_BOOKINGS_URL . 'assets/admin.css',
			array(),
			STUDIOCOUNT_BOOKINGS_VERSION
		);
		wp_enqueue_script(
			'studiocount-bookings-admin',
			STUDIOCOUNT_BOOKINGS_URL . 'assets/admin.js',
			array(),
			STUDIOCOUNT_BOOKINGS_VERSION,
			true
		);
		wp_localize_script(
			'studiocount-bookings-admin',
			'StudioCountBookingsAdmin',
			array(
				'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'studiocount_bookings_check_connection' ),
				'checking' => __( 'Checking…', 'studiocount-bookings' ),
				'failed'   => __( 'StudioCount could not be reached. Try again shortly.', 'studiocount-bookings' ),
			)
		);
	}

	/**
	 * Performs an administrator-requested service reachability check.
	 *
	 * @return void
	 */
	public static function check_connection() {
		check_ajax_referer( 'studiocount_bookings_check_connection', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You are not allowed to check this connection.', 'studiocount-bookings' ) ), 403 );
		}

		$studio_input = isset( $_POST['studio'] )
			? sanitize_text_field( wp_unslash( $_POST['studio'] ) )
			: '';
		$studio       = StudioCount_Bookings_Renderer::normalize_studio( $studio_input );
		if ( '' === $studio ) {
			wp_send_json_error( array( 'message' => __( 'Enter a valid StudioCount booking URL or studio slug first.', 'studiocount-bookings' ) ), 400 );
		}

		$url      = StudioCount_Bookings_Renderer::service_origin() . '/embed/' . rawurlencode( $studio ) . '?view=both';
		$response = wp_safe_remote_get(
			$url,
			array(
				'timeout'             => 10,
				'redirection'         => 0,
				'limit_response_size' => 32768,
				'user-agent'          => 'StudioCount Bookings/' . STUDIOCOUNT_BOOKINGS_VERSION . '; ' . home_url( '/' ),
			)
		);

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( array( 'message' => __( 'StudioCount could not be reached. Try again shortly.', 'studiocount-bookings' ) ), 502 );
		}

		$status = (int) wp_remote_retrieve_response_code( $response );
		$body   = (string) wp_remote_retrieve_body( $response );
		if ( 200 !== $status || false === strpos( $body, '/assets/index-' ) ) {
			wp_send_json_error( array( 'message' => __( 'StudioCount returned an unexpected response. Try again shortly.', 'studiocount-bookings' ) ), 502 );
		}

		wp_send_json_success(
			array(
				'message' => __( 'StudioCount is reachable. Open the preview to confirm this studio page.', 'studiocount-bookings' ),
				'preview' => $url,
			)
		);
	}

	/**
	 * Outputs the settings screen.
	 *
	 * @return void
	 */
	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$options     = StudioCount_Bookings_Renderer::get_options();
		$preview_url = '' !== $options['studio_slug']
			? StudioCount_Bookings_Renderer::service_origin() . '/embed/' . rawurlencode( $options['studio_slug'] ) . '?view=' . rawurlencode( $options['default_view'] )
			: '';
		?>
		<div class="wrap studiocount-bookings-admin">
			<header class="studiocount-bookings-admin__header">
				<p class="studiocount-bookings-admin__eyebrow"><?php esc_html_e( 'StudioCount', 'studiocount-bookings' ); ?></p>
				<h1><?php esc_html_e( 'StudioCount Bookings', 'studiocount-bookings' ); ?></h1>
				<p><?php esc_html_e( 'Add your StudioCount classes and products to this WordPress website.', 'studiocount-bookings' ); ?></p>
			</header>

			<?php settings_errors( StudioCount_Bookings_Renderer::OPTION_NAME ); ?>
			<form action="options.php" method="post" class="studiocount-bookings-admin__card">
				<?php settings_fields( 'studiocount_bookings' ); ?>
				<div class="studiocount-bookings-admin__field">
					<label for="studiocount-bookings-studio"><?php esc_html_e( 'Studio booking page', 'studiocount-bookings' ); ?></label>
					<input
						id="studiocount-bookings-studio"
						name="<?php echo esc_attr( StudioCount_Bookings_Renderer::OPTION_NAME ); ?>[studio_slug]"
						type="text"
						class="regular-text"
						value="<?php echo esc_attr( $options['studio_slug'] ); ?>"
						placeholder="https://www.studiocount.com/book/studioone"
						autocomplete="off"
					/>
					<p class="description"><?php esc_html_e( 'Paste your public StudioCount booking URL or enter its studio slug.', 'studiocount-bookings' ); ?></p>
				</div>

				<div class="studiocount-bookings-admin__field">
					<label for="studiocount-bookings-view"><?php esc_html_e( 'Default display', 'studiocount-bookings' ); ?></label>
					<select id="studiocount-bookings-view" name="<?php echo esc_attr( StudioCount_Bookings_Renderer::OPTION_NAME ); ?>[default_view]">
						<option value="classes" <?php selected( 'classes', $options['default_view'] ); ?>><?php esc_html_e( 'Classes', 'studiocount-bookings' ); ?></option>
						<option value="products" <?php selected( 'products', $options['default_view'] ); ?>><?php esc_html_e( 'Products', 'studiocount-bookings' ); ?></option>
						<option value="both" <?php selected( 'both', $options['default_view'] ); ?>><?php esc_html_e( 'Classes and products', 'studiocount-bookings' ); ?></option>
					</select>
				</div>

				<div class="studiocount-bookings-admin__actions">
					<?php submit_button( __( 'Save settings', 'studiocount-bookings' ), 'primary', 'submit', false ); ?>
					<button type="button" class="button" id="studiocount-bookings-check"><?php esc_html_e( 'Check connection', 'studiocount-bookings' ); ?></button>
					<?php if ( $preview_url ) : ?>
						<a class="button" href="<?php echo esc_url( $preview_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open preview', 'studiocount-bookings' ); ?></a>
					<?php endif; ?>
				</div>
				<p id="studiocount-bookings-check-result" class="studiocount-bookings-admin__result" role="status" aria-live="polite"></p>
			</form>

			<section class="studiocount-bookings-admin__card studiocount-bookings-admin__service" aria-labelledby="studiocount-service-heading">
				<h2 id="studiocount-service-heading"><?php esc_html_e( 'StudioCount service', 'studiocount-bookings' ); ?></h2>
				<p><?php esc_html_e( 'This plugin displays booking and product information from StudioCount. When the block or shortcode appears on a public page, the visitor’s browser connects directly to StudioCount. Booking and purchase details are then sent directly to StudioCount and, for online payments, Stripe.', 'studiocount-bookings' ); ?></p>
				<p>
					<a href="https://www.studiocount.com/terms" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'StudioCount Terms', 'studiocount-bookings' ); ?></a>
					<span aria-hidden="true"> · </span>
					<a href="https://www.studiocount.com/privacy" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'StudioCount Privacy Policy', 'studiocount-bookings' ); ?></a>
				</p>
			</section>
		</div>
		<?php
	}
}
