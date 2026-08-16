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
		add_action( 'admin_post_studiocount_bookings_connect', array( __CLASS__, 'complete_connection' ) );
	}

	/**
	 * Adds the settings page.
	 *
	 * @return void
	 */
	public static function add_page() {
		add_menu_page(
			__( 'StudioCount Bookings', 'studiocount-bookings' ),
			__( 'StudioCount Bookings', 'studiocount-bookings' ),
			'manage_options',
			'studiocount-bookings',
			array( __CLASS__, 'render_page' ),
			'dashicons-calendar-alt',
			58
		);
	}

	/**
	 * Registers the bounded plugin options.
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
					'studio_slug'   => '',
					'connection_key' => '',
					'default_view'  => 'both',
				),
			)
		);
	}

	/**
	 * Validates display settings without accepting connection authority here.
	 *
	 * @param mixed $input Submitted option value.
	 * @return array{studio_slug:string,connection_key:string,default_view:string}
	 */
	public static function sanitize( $input ) {
		$input   = is_array( $input ) ? $input : array();
		$current = StudioCount_Bookings_Renderer::get_options();
		$view    = StudioCount_Bookings_Renderer::normalize_view( $input['default_view'] ?? 'both' );

		return array(
			'studio_slug'   => $current['studio_slug'],
			'connection_key' => $current['connection_key'],
			'default_view'  => $view,
		);
	}

	/**
	 * Builds the authenticated Studio Portal connection URL.
	 *
	 * @return string
	 */
	public static function connect_url() {
		return add_query_arg(
			array(
				'site_origin' => StudioCount_Bookings_Renderer::parent_origin(),
				'return_url'  => admin_url( 'admin-post.php?action=studiocount_bookings_connect' ),
				'state'       => wp_create_nonce( 'studiocount_bookings_connect' ),
			),
			StudioCount_Bookings_Renderer::service_origin() . '/studio/wordpress-connect'
		);
	}

	/**
	 * Stores only a valid Studio Portal connection callback.
	 *
	 * @return void
	 */
	public static function complete_connection() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die(
				esc_html__( 'You are not allowed to connect this website.', 'studiocount-bookings' ),
				'',
				array( 'response' => 403 )
			);
		}

		$state = isset( $_GET['state'] )
			? sanitize_text_field( wp_unslash( $_GET['state'] ) )
			: '';
		if ( ! wp_verify_nonce( $state, 'studiocount_bookings_connect' ) ) {
			wp_die(
				esc_html__( 'This connection request has expired. Return to StudioCount Bookings and try again.', 'studiocount-bookings' ),
				'',
				array( 'response' => 403 )
			);
		}

		$studio = isset( $_GET['studio_slug'] )
			? StudioCount_Bookings_Renderer::normalize_studio( sanitize_text_field( wp_unslash( $_GET['studio_slug'] ) ) )
			: '';
		$key = isset( $_GET['connection_key'] )
			? StudioCount_Bookings_Renderer::normalize_connection_key( sanitize_text_field( wp_unslash( $_GET['connection_key'] ) ) )
			: '';
		if ( '' === $studio || '' === $key ) {
			wp_die(
				esc_html__( 'StudioCount returned an invalid connection.', 'studiocount-bookings' ),
				'',
				array( 'response' => 400 )
			);
		}

		$current = StudioCount_Bookings_Renderer::get_options();
		update_option(
			StudioCount_Bookings_Renderer::OPTION_NAME,
			array(
				'studio_slug'   => $studio,
				'connection_key' => $key,
				'default_view'  => $current['default_view'],
			),
			false
		);

		wp_safe_redirect( admin_url( 'admin.php?page=studiocount-bookings&connected=1' ) );
		exit;
	}

	/**
	 * Loads local settings assets only on this page.
	 *
	 * @param string $hook_suffix Current admin hook.
	 * @return void
	 */
	public static function enqueue_assets( $hook_suffix ) {
		if ( 'toplevel_page_studiocount-bookings' !== $hook_suffix ) {
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
	 * Performs an administrator-requested booking-page authorization check.
	 *
	 * @return void
	 */
	public static function check_connection() {
		check_ajax_referer( 'studiocount_bookings_check_connection', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You are not allowed to check this connection.', 'studiocount-bookings' ) ), 403 );
		}

		$options = StudioCount_Bookings_Renderer::get_options();
		if ( '' === $options['studio_slug'] || '' === $options['connection_key'] ) {
			wp_send_json_error( array( 'message' => __( 'Connect this website to StudioCount first.', 'studiocount-bookings' ) ), 400 );
		}

		$response = wp_safe_remote_post(
			'https://qjpftwpnlewwqlodyeff.supabase.co/functions/v1/validate-wordpress-embed-connection',
			array(
				'timeout'             => 10,
				'redirection'         => 0,
				'limit_response_size' => 2048,
				'user-agent'          => 'StudioCount Bookings/' . STUDIOCOUNT_BOOKINGS_VERSION . '; ' . home_url( '/' ),
				'headers'             => array( 'Content-Type' => 'application/json' ),
				'body'                => wp_json_encode(
					array(
						'studio_slug'   => $options['studio_slug'],
						'site_origin'   => StudioCount_Bookings_Renderer::parent_origin(),
						'connection_key' => $options['connection_key'],
					)
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( array( 'message' => __( 'StudioCount could not be reached. Try again shortly.', 'studiocount-bookings' ) ), 502 );
		}

		$status = (int) wp_remote_retrieve_response_code( $response );
		$body   = json_decode( (string) wp_remote_retrieve_body( $response ), true );
		if ( 200 !== $status || array( 'authorized' => true ) !== $body ) {
			wp_send_json_error( array( 'message' => __( 'This website is not authorised for the selected StudioCount booking page. Connect it again.', 'studiocount-bookings' ) ), 403 );
		}

		wp_send_json_success(
			array(
				'message' => __( 'Booking page found and ready to use on this website.', 'studiocount-bookings' ),
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
		$connected   = '' !== $options['studio_slug'] && '' !== $options['connection_key'];
		$booking_url = $connected
			? StudioCount_Bookings_Renderer::service_origin() . '/book/' . rawurlencode( $options['studio_slug'] )
			: '';
		?>
		<div class="wrap studiocount-bookings-admin">
			<header class="studiocount-bookings-admin__header">
				<p class="studiocount-bookings-admin__eyebrow"><?php esc_html_e( 'StudioCount', 'studiocount-bookings' ); ?></p>
				<h1><?php esc_html_e( 'StudioCount Bookings', 'studiocount-bookings' ); ?></h1>
				<p><?php esc_html_e( 'Add your StudioCount classes and products to this WordPress website.', 'studiocount-bookings' ); ?></p>
			</header>

			<?php settings_errors( StudioCount_Bookings_Renderer::OPTION_NAME ); ?>
			<?php if ( isset( $_GET['connected'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['connected'] ) ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'WordPress is connected to the selected StudioCount booking page.', 'studiocount-bookings' ); ?></p></div>
			<?php endif; ?>
			<form action="options.php" method="post" class="studiocount-bookings-admin__card">
				<?php settings_fields( 'studiocount_bookings' ); ?>
				<div class="studiocount-bookings-admin__field">
					<label><?php esc_html_e( 'Studio booking page', 'studiocount-bookings' ); ?></label>
					<?php if ( $connected ) : ?>
						<p><strong><?php echo esc_html( $booking_url ); ?></strong></p>
						<p class="description"><?php echo esc_html( StudioCount_Bookings_Renderer::parent_origin() ); ?></p>
					<?php else : ?>
						<p class="description"><?php esc_html_e( 'Choose a studio in Studio Portal and confirm this WordPress website.', 'studiocount-bookings' ); ?></p>
					<?php endif; ?>
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
					<a class="button" href="<?php echo esc_url( self::connect_url() ); ?>"><?php echo esc_html( $connected ? __( 'Connect a different studio', 'studiocount-bookings' ) : __( 'Connect to StudioCount', 'studiocount-bookings' ) ); ?></a>
					<?php if ( $connected ) : ?>
						<button type="button" class="button" id="studiocount-bookings-check"><?php esc_html_e( 'Check booking page', 'studiocount-bookings' ); ?></button>
						<a class="button" href="<?php echo esc_url( $booking_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View booking page', 'studiocount-bookings' ); ?></a>
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
