<?php
/**
 * Front-end block and shortcode rendering.
 *
 * @package StudioCount_Bookings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders isolated StudioCount hosted embeds.
 */
final class StudioCount_Bookings_Renderer {
	const OPTION_NAME = 'studiocount_bookings_options';
	const CONNECTION_OPTION_NAME = 'studiocount_bookings_connection';

	/**
	 * Returns the fixed StudioCount public-service origin.
	 *
	 * @return string
	 */
	public static function service_origin() {
		return 'https://www.studiocount.com';
	}

	/**
	 * Returns canonical saved options.
	 *
	 * @return array{studio_slug:string,connection_key:string,default_view:string}
	 */
	public static function get_options() {
		$saved      = get_option( self::OPTION_NAME, array() );
		$saved      = is_array( $saved ) ? $saved : array();
		$connection = get_option( self::CONNECTION_OPTION_NAME, array() );
		$connection = is_array( $connection ) ? $connection : array();

		$studio = self::normalize_studio( $connection['studio_slug'] ?? '' );
		$key    = self::normalize_connection_key( $connection['connection_key'] ?? '' );
		if ( '' === $studio || '' === $key ) {
			// Preserve connections saved by releases before the authority option was separated.
			$studio = self::normalize_studio( $saved['studio_slug'] ?? '' );
			$key    = self::normalize_connection_key( $saved['connection_key'] ?? '' );
		}

		return array(
			'studio_slug'   => $studio,
			'connection_key' => $key,
			'default_view'  => self::normalize_view( $saved['default_view'] ?? 'both' ),
		);
	}

	/**
	 * Normalizes a domain-bound public embed identifier.
	 *
	 * @param mixed $value Candidate identifier.
	 * @return string Empty when invalid.
	 */
	public static function normalize_connection_key( $value ) {
		$value = trim( (string) $value );
		return 1 === preg_match( '/^wpc_[a-f0-9]{64}$/', $value ) ? $value : '';
	}

	/**
	 * Normalizes a public StudioCount URL or slug.
	 *
	 * @param mixed $value Candidate URL or slug.
	 * @return string Empty when invalid.
	 */
	public static function normalize_studio( $value ) {
		$value = trim( (string) $value );
		if ( '' === $value ) {
			return '';
		}

		if ( false !== strpos( $value, '://' ) ) {
			$url = wp_parse_url( $value );
			if ( ! is_array( $url ) ) {
				return '';
			}

			$scheme = strtolower( (string) ( $url['scheme'] ?? '' ) );
			$host   = strtolower( (string) ( $url['host'] ?? '' ) );
			if (
				'https' !== $scheme ||
				! in_array( $host, array( 'studiocount.com', 'www.studiocount.com' ), true ) ||
				isset( $url['user'] ) ||
				isset( $url['pass'] ) ||
				isset( $url['port'] ) ||
				isset( $url['query'] ) ||
				isset( $url['fragment'] )
			) {
				return '';
			}

			$path = rawurldecode( (string) ( $url['path'] ?? '' ) );
			if ( 1 !== preg_match( '#^/(?:book|embed)/([^/]+)/?$#', $path, $matches ) ) {
				return '';
			}
			$value = $matches[1];
		}

		$value = strtolower( trim( $value, " \t\n\r\0\x0B/" ) );
		return 1 === preg_match( '/^[a-z0-9](?:[a-z0-9-]{0,78}[a-z0-9])?$/', $value )
			? $value
			: '';
	}

	/**
	 * Normalizes one supported presentation mode.
	 *
	 * @param mixed $value Candidate mode.
	 * @return string
	 */
	public static function normalize_view( $value ) {
		$value = strtolower( trim( (string) $value ) );
		return in_array( $value, array( 'classes', 'products', 'both' ), true ) ? $value : 'both';
	}

	/**
	 * Builds the exact WordPress-site origin supplied to the hosted bridge.
	 *
	 * @return string
	 */
	public static function parent_origin() {
		$home   = wp_parse_url( home_url( '/' ) );
		$scheme = strtolower( (string) ( $home['scheme'] ?? '' ) );
		$host   = strtolower( (string) ( $home['host'] ?? '' ) );

		if ( ! in_array( $scheme, array( 'http', 'https' ), true ) || '' === $host ) {
			return '';
		}

		$origin = $scheme . '://' . $host;
		if ( isset( $home['port'] ) ) {
			$origin .= ':' . absint( $home['port'] );
		}
		return $origin;
	}

	/**
	 * Creates a hosted embed URL with no customer authority.
	 *
	 * @param string $studio      Canonical public studio slug.
	 * @param string $view        Canonical display mode.
	 * @param string $instance_id   Unique frame instance.
	 * @param string $connection_key Domain-bound public embed identifier.
	 * @return string
	 */
	public static function embed_url( $studio, $view, $instance_id, $connection_key ) {
		return add_query_arg(
			array(
				'view'          => self::normalize_view( $view ),
				'parent_origin' => self::parent_origin(),
				'instance_id'   => $instance_id,
				'connection_key' => self::normalize_connection_key( $connection_key ),
			),
			self::service_origin() . '/embed/' . rawurlencode( $studio )
		);
	}

	/**
	 * Dynamic Gutenberg block callback.
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public static function render_block( $attributes ) {
		return self::render( is_array( $attributes ) ? $attributes : array(), true );
	}

	/**
	 * Shortcode callback.
	 *
	 * @param array|string $attributes Shortcode attributes.
	 * @return string
	 */
	public static function render_shortcode( $attributes = array() ) {
		$attributes = shortcode_atts(
			array(
				'studio' => '',
				'view'   => '',
			),
			is_array( $attributes ) ? $attributes : array(),
			'studiocount_bookings'
		);
		return self::render( $attributes, false );
	}

	/**
	 * Renders one independent hosted embed.
	 *
	 * @param array $attributes Block or shortcode values.
	 * @param bool  $is_block   Whether block wrapper attributes are required.
	 * @return string
	 */
	private static function render( $attributes, $is_block ) {
		$options = self::get_options();
		$studio  = $options['studio_slug'];
		$view = '' !== trim( (string) ( $attributes['view'] ?? '' ) )
			? self::normalize_view( $attributes['view'] )
			: $options['default_view'];

		if ( '' === $studio || '' === $options['connection_key'] || '' === self::parent_origin() ) {
			return self::configuration_message( $is_block );
		}

		$instance_id = substr( wp_unique_id( 'studiocount-bookings-' ), 0, 80 );
		$embed_url   = self::embed_url( $studio, $view, $instance_id, $options['connection_key'] );
		$booking_url = self::service_origin() . '/book/' . rawurlencode( $studio );
		$classes     = 'studiocount-bookings';
		if ( $is_block && function_exists( 'get_block_wrapper_attributes' ) ) {
			$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => $classes ) );
		} else {
			$wrapper_attributes = 'class="' . esc_attr( $classes ) . '"';
		}

		wp_enqueue_style( 'studiocount-bookings-frontend' );
		wp_enqueue_script( 'studiocount-bookings-frontend' );

		ob_start();
		?>
		<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Generated by get_block_wrapper_attributes or escaped above. ?>>
			<div class="studiocount-bookings__status" role="status" aria-live="polite">
				<?php esc_html_e( 'Loading bookings…', 'studiocount-bookings' ); ?>
			</div>
			<iframe
				class="studiocount-bookings__frame"
				src="<?php echo esc_url( $embed_url ); ?>"
				title="<?php esc_attr_e( 'Classes and products', 'studiocount-bookings' ); ?>"
				data-studiocount-instance="<?php echo esc_attr( $instance_id ); ?>"
				data-studiocount-origin="<?php echo esc_attr( self::service_origin() ); ?>"
				height="720"
				loading="lazy"
				referrerpolicy="strict-origin"
				sandbox="allow-forms allow-popups allow-popups-to-escape-sandbox allow-same-origin allow-scripts"
			></iframe>
			<noscript>
				<a href="<?php echo esc_url( $booking_url ); ?>"><?php esc_html_e( 'Open booking page', 'studiocount-bookings' ); ?></a>
			</noscript>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Returns a bounded message for missing configuration.
	 *
	 * @param bool $is_block Whether this is a block render.
	 * @return string
	 */
	private static function configuration_message( $is_block ) {
		$message = __( 'Bookings are not available right now.', 'studiocount-bookings' );
		if ( current_user_can( 'manage_options' ) ) {
			$settings_url = admin_url( 'options-general.php?page=studiocount-bookings' );
			$message      = sprintf(
				/* translators: %s: StudioCount Bookings settings URL. */
				__( 'Choose a studio in <a href="%s">StudioCount Bookings settings</a>.', 'studiocount-bookings' ),
				esc_url( $settings_url )
			);
		}

		$classes = 'studiocount-bookings studiocount-bookings--unavailable';
		$attrs   = $is_block && function_exists( 'get_block_wrapper_attributes' )
			? get_block_wrapper_attributes( array( 'class' => $classes ) )
			: 'class="' . esc_attr( $classes ) . '"';

		return '<div ' . $attrs . '><p>' . wp_kses_post( $message ) . '</p></div>';
	}
}
