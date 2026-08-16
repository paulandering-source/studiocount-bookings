<?php
/**
 * Focused no-dependency contract checks for release packaging.
 *
 * @package StudioCount_Bookings
 */

define( 'ABSPATH', __DIR__ . '/' );

$GLOBALS['scb_options'] = array();
$GLOBALS['scb_connection'] = array();
$GLOBALS['scb_home']    = 'https://fitness.example/';
$GLOBALS['scb_admin']   = false;
$GLOBALS['scb_counter'] = 0;
$GLOBALS['scb_enqueued'] = array();

function wp_parse_url( $value ) {
	return parse_url( $value );
}

function get_option( $name, $default = false ) {
	if ( 'studiocount_bookings_options' === $name ) {
		return $GLOBALS['scb_options'];
	}
	if ( 'studiocount_bookings_connection' === $name ) {
		return $GLOBALS['scb_connection'];
	}
	return $default;
}

function home_url() {
	return $GLOBALS['scb_home'];
}

function admin_url( $path = '' ) {
	return 'https://fitness.example/wp-admin/' . ltrim( $path, '/' );
}

function absint( $value ) {
	return abs( (int) $value );
}

function add_query_arg( $args, $url ) {
	return $url . '?' . http_build_query( $args, '', '&', PHP_QUERY_RFC3986 );
}

function shortcode_atts( $defaults, $attributes ) {
	return array_merge( $defaults, $attributes );
}

function wp_unique_id( $prefix = '' ) {
	$GLOBALS['scb_counter']++;
	return $prefix . $GLOBALS['scb_counter'];
}

function wp_enqueue_style( $handle ) {
	$GLOBALS['scb_enqueued'][] = $handle;
}

function wp_enqueue_script( $handle ) {
	$GLOBALS['scb_enqueued'][] = $handle;
}

function esc_attr( $value ) {
	return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' );
}

function esc_url( $value ) {
	return esc_attr( $value );
}

function esc_attr_e( $value ) {
	echo esc_attr( $value );
}

function esc_html_e( $value ) {
	echo htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' );
}

function __( $value ) {
	return $value;
}

function current_user_can() {
	return $GLOBALS['scb_admin'];
}

function wp_kses_post( $value ) {
	return $value;
}

function get_block_wrapper_attributes( $attributes ) {
	return 'class="' . esc_attr( $attributes['class'] ?? '' ) . '"';
}

function assert_same( $expected, $actual, $label ) {
	if ( $expected !== $actual ) {
		fwrite( STDERR, "FAIL: $label\nExpected: " . var_export( $expected, true ) . "\nActual: " . var_export( $actual, true ) . "\n" );
		exit( 1 );
	}
	echo "PASS: $label\n";
}

function assert_true( $actual, $label ) {
	assert_same( true, (bool) $actual, $label );
}

require_once dirname( __DIR__, 2 ) . '/includes/class-studiocount-bookings-renderer.php';

$valid_studios = array(
	'studioone'                                      => 'studioone',
	'Studio-One'                                     => 'studio-one',
	'https://www.studiocount.com/book/studioone'     => 'studioone',
	'https://studiocount.com/embed/studio-one/'      => 'studio-one',
);
foreach ( $valid_studios as $input => $expected ) {
	assert_same( $expected, StudioCount_Bookings_Renderer::normalize_studio( $input ), "normalizes $input" );
}

$invalid_studios = array(
	'https://attacker.example/book/studioone',
	'http://www.studiocount.com/book/studioone',
	'https://www.studiocount.com/book/studioone?x=1',
	'https://user@www.studiocount.com/book/studioone',
	'../studioone',
	'studio_one',
	'',
);
foreach ( $invalid_studios as $input ) {
	assert_same( '', StudioCount_Bookings_Renderer::normalize_studio( $input ), "rejects $input" );
}

assert_same( 'classes', StudioCount_Bookings_Renderer::normalize_view( 'classes' ), 'accepts classes view' );
assert_same( 'products', StudioCount_Bookings_Renderer::normalize_view( 'products' ), 'accepts products view' );
assert_same( 'both', StudioCount_Bookings_Renderer::normalize_view( 'unexpected' ), 'defaults unknown view' );

$connection_key = 'wpc_' . str_repeat( 'a', 64 );
assert_same( $connection_key, StudioCount_Bookings_Renderer::normalize_connection_key( $connection_key ), 'accepts exact connection key' );
assert_same( '', StudioCount_Bookings_Renderer::normalize_connection_key( 'wpc_' . str_repeat( 'A', 64 ) ), 'rejects wrong-case connection key' );

$GLOBALS['scb_home'] = 'http://localhost:8080/path';
assert_same( 'http://localhost:8080', StudioCount_Bookings_Renderer::parent_origin(), 'preserves local origin and port' );
$GLOBALS['scb_home'] = 'https://fitness.example/';

$url = StudioCount_Bookings_Renderer::embed_url( 'studioone', 'classes', 'studiocount-bookings-1', $connection_key );
assert_true( 0 === strpos( $url, 'https://www.studiocount.com/embed/studioone?' ), 'uses exact service embed route' );
assert_true( false !== strpos( $url, 'view=classes' ), 'binds exact view' );
assert_true( false !== strpos( $url, 'parent_origin=https%3A%2F%2Ffitness.example' ), 'binds parent origin' );
assert_true( false !== strpos( $url, 'instance_id=studiocount-bookings-1' ), 'binds frame instance' );
assert_true( false !== strpos( $url, 'connection_key=wpc_' ), 'binds domain connection identifier' );

$GLOBALS['scb_options'] = array( 'studio_slug' => 'studioone', 'connection_key' => $connection_key, 'default_view' => 'both' );
$first = StudioCount_Bookings_Renderer::render_shortcode();
$second = StudioCount_Bookings_Renderer::render_shortcode( array( 'view' => 'products' ) );
assert_true( false !== strpos( $first, 'Loading bookings' ), 'uses neutral visitor loading copy' );
assert_true( false === strpos( $first, 'Loading StudioCount bookings' ), 'does not brand the visitor loading state' );
assert_true( false !== strpos( $first, 'title="Classes and products"' ), 'uses a neutral frame title' );
assert_true( false !== strpos( $first, 'sandbox="allow-forms allow-popups allow-popups-to-escape-sandbox allow-same-origin allow-scripts"' ), 'renders bounded iframe sandbox' );
assert_true( false !== strpos( $first, 'referrerpolicy="strict-origin"' ), 'sends exact parent referrer origin' );
assert_true( false !== strpos( $first, 'view=both' ), 'uses saved default view' );
assert_true( false !== strpos( $second, 'view=products' ), 'supports shortcode view override' );
assert_true( false === strpos( $first, 'studiocount-bookings-2' ), 'first render has independent instance' );
assert_true( false !== strpos( $second, 'studiocount-bookings-2' ), 'second render has independent instance' );
assert_true( in_array( 'studiocount-bookings-frontend', $GLOBALS['scb_enqueued'], true ), 'enqueues only local frontend handle' );

$separate_key = 'wpc_' . str_repeat( 'b', 64 );
$GLOBALS['scb_connection'] = array( 'studio_slug' => 'connected-studio', 'connection_key' => $separate_key );
$separate_options = StudioCount_Bookings_Renderer::get_options();
assert_same( 'connected-studio', $separate_options['studio_slug'], 'prefers separately stored connection authority' );
assert_same( $separate_key, $separate_options['connection_key'], 'retains separately stored connection key' );
$GLOBALS['scb_connection'] = array();

$legacy_override = StudioCount_Bookings_Renderer::render_shortcode( array( 'studio' => 'another-studio' ) );
assert_true( false !== strpos( $legacy_override, '/embed/studioone' ), 'uses only the authenticated connected studio' );
assert_true( false === strpos( $legacy_override, '/embed/another-studio' ), 'does not let a shortcode retarget the connection' );

$GLOBALS['scb_options'] = array();
$GLOBALS['scb_admin'] = false;
$missing = StudioCount_Bookings_Renderer::render_shortcode();
assert_true( false !== strpos( $missing, 'not available right now' ), 'shows bounded public missing-config state' );
assert_true( false === strpos( $missing, 'wp-admin' ), 'does not expose admin route publicly' );

$GLOBALS['scb_admin'] = true;
$admin_missing = StudioCount_Bookings_Renderer::render_shortcode();
assert_true( false !== strpos( $admin_missing, 'StudioCount Bookings settings' ), 'guides an authorized administrator' );

$frontend_js = file_get_contents( dirname( __DIR__, 2 ) . '/assets/frontend.js' );
$frontend_css = file_get_contents( dirname( __DIR__, 2 ) . '/assets/frontend.css' );
$plugin_source = file_get_contents( dirname( __DIR__, 2 ) . '/includes/class-studiocount-bookings-plugin.php' );
assert_true( false !== strpos( $frontend_js, "serviceOrigin !== event.origin" ), 'requires exact message origin' );
assert_true( false !== strpos( $frontend_js, 'frame.contentWindow === event.source' ), 'requires exact message window' );
assert_true( false !== strpos( $frontend_js, "'/checkout-return' === destination.pathname" ), 'allows exact StudioCount return path' );
assert_true( false !== strpos( $frontend_js, "0 === destination.pathname.indexOf( '/c/pay/' )" ), 'allows exact Stripe Checkout path' );
assert_true( false === strpos( $frontend_js, 'setInterval' ), 'contains no automatic refresh loop' );
assert_true( false !== strpos( $frontend_css, 'width: min(1200px, calc(100vw - 2rem)) !important;' ), 'overrides narrow theme constraints with the wide booking canvas' );
assert_true( false !== strpos( $frontend_css, 'max-width: none !important;' ), 'prevents a theme maximum width from narrowing the booking canvas' );
assert_true( false !== strpos( $frontend_css, 'margin-left: 0 !important;' ), 'prevents theme auto margins from shifting the booking canvas' );
assert_true( false !== strpos( $frontend_css, 'transform: translateX(-50%);' ), 'centres the wide canvas on the page viewport' );
assert_true( false !== strpos( $plugin_source, "'plugin_action_links_'" ), 'adds a discoverable settings link on the Plugins page' );
assert_true( false !== strpos( $plugin_source, "admin.php?page=studiocount-bookings" ), 'links to the visible StudioCount settings screen' );
$settings_source = file_get_contents( dirname( __DIR__, 2 ) . '/includes/class-studiocount-bookings-settings.php' );
assert_true( false !== strpos( $settings_source, 'add_menu_page(' ), 'registers a visible top-level WordPress admin menu' );
assert_true( false !== strpos( $settings_source, "'dashicons-calendar-alt'" ), 'uses a recognizable booking calendar menu icon' );
assert_true( false !== strpos( $settings_source, 'Connect to StudioCount' ), 'starts connection through authenticated Studio Portal' );
assert_true( false !== strpos( $settings_source, 'Check connection' ), 'labels the exact connection check truthfully' );
assert_true( false !== strpos( $settings_source, 'View your StudioCount booking page' ), 'links to the full StudioCount booking page' );
assert_true( false !== strpos( $settings_source, 'Keep your StudioCount booking page live while using the plugin.' ), 'states the live StudioCount booking-page prerequisite' );
assert_true( false !== strpos( $settings_source, 'admin_post_studiocount_bookings_connect' ), 'registers the protected WordPress callback' );
assert_true( false !== strpos( $settings_source, 'wp_verify_nonce' ), 'binds the callback to the initiating WordPress administrator' );
assert_true( false !== strpos( $settings_source, 'StudioCount_Bookings_Renderer::CONNECTION_OPTION_NAME' ), 'stores callback authority outside the display setting sanitizer' );
assert_true( false !== strpos( $settings_source, 'The WordPress connection could not be saved.' ), 'does not report success until the connection is retained' );
assert_true( false !== strpos( $settings_source, 'admin_post_studiocount_bookings_create_page' ), 'registers the protected booking-page creator' );
assert_true( false !== strpos( $settings_source, "current_user_can( 'manage_options' )" ), 'requires WordPress administrator authority for page creation' );
assert_true( false !== strpos( $settings_source, "check_admin_referer( 'studiocount_bookings_create_page' )" ), 'requires an exact page-creation nonce' );
assert_true( false !== strpos( $settings_source, "'post_status'  => 'draft'" ), 'creates an editable draft rather than publishing automatically' );
assert_true( false !== strpos( $settings_source, '<!-- wp:studiocount/bookings /-->' ), 'creates the page with the local StudioCount block' );
assert_true( false !== strpos( $settings_source, 'Edit booking page' ), 'reuses the existing plugin-created page instead of duplicating it' );
assert_true( false !== strpos( $settings_source, 'Create a WordPress booking page automatically' ), 'offers clear automatic page creation' );
assert_true( false !== strpos( $settings_source, 'Add to a page manually' ), 'shows manual embed instructions in settings' );
assert_true( false !== strpos( $settings_source, 'search for “StudioCount Bookings”' ), 'explains how to find the block' );
assert_true( false !== strpos( $settings_source, '[studiocount_bookings]' ), 'shows the exact manual shortcode' );

$block = json_decode( file_get_contents( dirname( __DIR__, 2 ) . '/blocks/studiocount-bookings/block.json' ), true );
assert_same( 'studiocount/bookings', $block['name'] ?? '', 'registers exact block namespace' );
assert_same( 3, $block['apiVersion'] ?? null, 'uses current block API' );
assert_same( false, $block['supports']['html'] ?? null, 'disables arbitrary block HTML' );

echo "All focused StudioCount Bookings contracts passed.\n";
