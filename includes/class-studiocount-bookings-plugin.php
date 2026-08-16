<?php
/**
 * Plugin bootstrap.
 *
 * @package StudioCount_Bookings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the StudioCount block, shortcode, assets and settings.
 */
final class StudioCount_Bookings_Plugin {
	/**
	 * Registers WordPress hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_editor_context' ) );
		add_shortcode( 'studiocount_bookings', array( 'StudioCount_Bookings_Renderer', 'render_shortcode' ) );

		if ( is_admin() ) {
			StudioCount_Bookings_Settings::init();
			add_filter(
				'plugin_action_links_' . plugin_basename( STUDIOCOUNT_BOOKINGS_FILE ),
				array( __CLASS__, 'plugin_action_links' )
			);
		}
	}

	/**
	 * Adds a direct settings link beside the plugin activation controls.
	 *
	 * @param array $links Existing plugin action links.
	 * @return array
	 */
	public static function plugin_action_links( $links ) {
		$links = is_array( $links ) ? $links : array();
		if ( ! current_user_can( 'manage_options' ) ) {
			return $links;
		}

		array_unshift(
			$links,
				'<a href="' . esc_url( admin_url( 'admin.php?page=studiocount-bookings' ) ) . '">' . esc_html__( 'Settings', 'studiocount-bookings' ) . '</a>'
		);
		return $links;
	}

	/**
	 * Registers local assets and the dynamic block.
	 *
	 * @return void
	 */
	public static function register() {
		wp_register_style(
			'studiocount-bookings-frontend',
			STUDIOCOUNT_BOOKINGS_URL . 'assets/frontend.css',
			array(),
			STUDIOCOUNT_BOOKINGS_VERSION
		);
		wp_register_script(
			'studiocount-bookings-frontend',
			STUDIOCOUNT_BOOKINGS_URL . 'assets/frontend.js',
			array(),
			STUDIOCOUNT_BOOKINGS_VERSION,
			true
		);
		wp_register_style(
			'studiocount-bookings-block-editor',
			STUDIOCOUNT_BOOKINGS_URL . 'blocks/studiocount-bookings/editor.css',
			array( 'wp-edit-blocks' ),
			STUDIOCOUNT_BOOKINGS_VERSION
		);
		wp_register_script(
			'studiocount-bookings-block-editor',
			STUDIOCOUNT_BOOKINGS_URL . 'blocks/studiocount-bookings/index.js',
			array( 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-components', 'wp-block-editor' ),
			STUDIOCOUNT_BOOKINGS_VERSION,
			true
		);

		register_block_type(
			STUDIOCOUNT_BOOKINGS_PATH . 'blocks/studiocount-bookings',
			array(
				'render_callback' => array( 'StudioCount_Bookings_Renderer', 'render_block' ),
			)
		);
	}

	/**
	 * Makes saved defaults available to the local block-editor preview.
	 *
	 * @return void
	 */
	public static function enqueue_editor_context() {
		$options = StudioCount_Bookings_Renderer::get_options();
		$context = array(
			'defaultStudio' => $options['studio_slug'],
			'connected'     => '' !== $options['connection_key'],
			'defaultView'   => $options['default_view'],
			'settingsUrl'   => admin_url( 'admin.php?page=studiocount-bookings' ),
			'previewBase'   => StudioCount_Bookings_Renderer::service_origin() . '/embed/',
		);

		wp_add_inline_script(
			'studiocount-bookings-block-editor',
			'window.StudioCountBookingsEditor = ' . wp_json_encode( $context ) . ';',
			'before'
		);
	}
}
