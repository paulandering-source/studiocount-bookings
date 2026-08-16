=== StudioCount Bookings ===
Tags: class booking, fitness, studio, scheduling, memberships
Requires at least: 6.4
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add StudioCount classes and products to WordPress with a Gutenberg block or shortcode.

== Description ==

StudioCount Bookings lets studios display their current StudioCount classes and products on an existing WordPress website.

Use the StudioCount Bookings block or the `[studiocount_bookings]` shortcode. Each embed can show:

* Classes
* Products
* Classes and products together

Visitors see current information from StudioCount and continue through the normal StudioCount booking, waitlist and secure Stripe-hosted payment flows. The plugin does not copy availability, prices, booking rules or payment logic into WordPress.

The plugin supports multiple embeds on one page and adapts to phone, tablet and desktop layouts. Class availability refreshes only when a visitor chooses Refresh or completes a relevant booking action.

= StudioCount service =

This plugin requires a StudioCount studio account and connects to the external StudioCount service at `https://www.studiocount.com`.

When a site owner places the block or shortcode on a public page, the visitor's browser requests the configured studio slug, selected display mode and parent website origin from StudioCount. StudioCount returns the public classes, availability, class presentation and products for that studio.

If a visitor starts a booking, joins a waitlist or chooses a product, the information they enter is sent directly from the hosted StudioCount frame to StudioCount. If the studio offers online payment, the visitor continues to Stripe's hosted Checkout. WordPress does not receive or store the visitor's booking details, StudioCount credentials, card details, Supabase credentials or Stripe credentials.

An administrator-requested connection check sends the configured public studio slug and this site's URL to StudioCount using the WordPress HTTP API. No connection check runs automatically.

StudioCount Terms: https://www.studiocount.com/terms

StudioCount Privacy Policy: https://www.studiocount.com/privacy

Stripe Services Agreement: https://stripe.com/legal/ssa

== Installation ==

1. Install and activate StudioCount Bookings.
2. Open **Settings > StudioCount Bookings**.
3. Paste the public StudioCount booking URL supplied by your studio account, or enter its slug.
4. Choose the default display and save the settings.
5. Add the **StudioCount Bookings** block to a page, or use `[studiocount_bookings]`.

The shortcode accepts optional `studio` and `view` attributes:

`[studiocount_bookings studio="studioone" view="both"]`

The exact `view` values are `classes`, `products` and `both`.

== Frequently Asked Questions ==

= Do I need a StudioCount account? =

Yes. This plugin displays the public booking and product information configured for a participating StudioCount studio.

= Does the plugin process payments? =

No. Online payments, when offered by a studio, use Stripe-hosted Checkout through StudioCount. The plugin and the WordPress website do not receive card details.

= Does the plugin store customer or booking data in WordPress? =

No. The WordPress option contains only the public studio slug and default display mode. Booking, waitlist, product and payment information remains with StudioCount and its disclosed service providers.

= Can I show only classes or only products? =

Yes. Choose a default in Settings, override it in each block, or set the shortcode `view` attribute to `classes`, `products` or `both`.

= Can I use more than one StudioCount embed on a page? =

Yes. Each block or shortcode has an independent identifier and can use its own studio and display mode.

= Does the plugin automatically refresh the page? =

No. Class availability refresh is manual or follows a relevant visitor action, so the surrounding WordPress page does not keep jumping.

== Screenshots ==

1. Configure the public studio booking page and default display.
2. Display responsive StudioCount classes on a WordPress page.
3. Display StudioCount products alongside the studio's classes.

== Changelog ==

= 1.0.3 =

* Add a visible StudioCount Bookings item to the main WordPress admin menu.

= 1.0.2 =

* Add a direct Settings link on the WordPress Plugins screen.

= 1.0.1 =

* Use a wider responsive booking canvas even inside narrow theme content columns.
* Leave the page heading and introductory copy to the WordPress site.

= 1.0.0 =

* Initial public release with Gutenberg block and shortcode support.
* Classes, products and combined display modes.
* Responsive isolated embed with exact-origin resizing and Checkout navigation.

== Upgrade Notice ==

= 1.0.3 =

Moves plugin configuration into an easy-to-find main admin menu item.

= 1.0.2 =

Makes the existing StudioCount Bookings settings screen easier to find.

= 1.0.1 =

Improves the embedded layout and removes the duplicate studio introduction.

= 1.0.0 =

Initial release.
