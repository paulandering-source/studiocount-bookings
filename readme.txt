=== StudioCount Bookings ===
Tags: class booking, fitness, studio, scheduling, memberships
Requires at least: 6.4
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.8
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

The plugin supports multiple embeds on one page and adapts to phone, tablet and desktop layouts. Visitors can reload the WordPress page whenever they want fresh information; a contextual retry is shown if information fails to load.

= StudioCount service =

This plugin requires a StudioCount studio account and connects to the external StudioCount service at `https://www.studiocount.com`.

The studio's StudioCount booking page must remain live while the plugin is in use. The plugin displays the same published classes, availability and products on WordPress; it does not create a separate booking channel.

The WordPress administrator connects the site through the authenticated Studio Portal. StudioCount binds one revocable public display identifier to the exact studio and WordPress origin. Copying the identifier to another website does not authorize the embed, and the identifier grants no Owner, Member, Supabase or Stripe authority.

When a site owner places the block or shortcode on a public page, the visitor's browser requests the connected studio's public classes, availability, class presentation and products from StudioCount.

If a visitor starts a booking, joins a waitlist or chooses a product, the information they enter is sent directly from the hosted StudioCount frame to StudioCount. If the studio offers online payment, the visitor continues to Stripe's hosted Checkout. WordPress does not receive or store the visitor's booking details, StudioCount credentials, card details, Supabase credentials or Stripe credentials.

An administrator-requested booking-page check sends the public connection identifier, studio slug and exact site origin to StudioCount using the WordPress HTTP API. No check runs automatically.

StudioCount Terms: https://www.studiocount.com/terms

StudioCount Privacy Policy: https://www.studiocount.com/privacy

Stripe Services Agreement: https://stripe.com/legal/ssa

== Installation ==

1. Install and activate StudioCount Bookings.
2. Open **StudioCount Bookings** in the WordPress admin menu.
3. Choose **Connect to StudioCount**, sign in to Studio Portal and confirm the exact studio and WordPress website.
4. Choose the default display and save the settings.
5. Choose **Create a WordPress booking page automatically**, add the **StudioCount Bookings** block to another page, or add a Shortcode block containing `[studiocount_bookings]`.

The shortcode accepts an optional `view` attribute:

`[studiocount_bookings view="both"]`

The exact `view` values are `classes`, `products` and `both`.

== Frequently Asked Questions ==

= Do I need a StudioCount account? =

Yes. This plugin displays the public booking and product information configured for a participating StudioCount studio.

= Does the plugin process payments? =

No. Online payments, when offered by a studio, use Stripe-hosted Checkout through StudioCount. The plugin and the WordPress website do not receive card details.

= Does the plugin store customer or booking data in WordPress? =

No. The WordPress option contains only the public studio slug, a domain-bound public display identifier and the default display mode. Booking, waitlist, product and payment information remains with StudioCount and its disclosed service providers.

= Can I show only classes or only products? =

Yes. Choose a default in StudioCount Bookings, override it in each block, or set the shortcode `view` attribute to `classes`, `products` or `both`.

= Can I use more than one StudioCount embed on a page? =

Yes. Each block or shortcode has an independent frame and can use its own display mode for the connected studio.

= Does the plugin automatically refresh the page? =

No. Visitors can reload the WordPress page, and a retry button appears only if booking information fails to load.

== Screenshots ==

1. Connect the exact WordPress website to a studio and choose the default display.
2. Display responsive StudioCount classes on a WordPress page.
3. Display StudioCount products alongside the studio's classes.

== Changelog ==

= 1.0.8 =

* Explain that the connected StudioCount booking page must remain live.
* Clarify the StudioCount booking-page link and automatic WordPress page creation.
* Add visible block and shortcode instructions to the plugin settings screen.

= 1.0.7 =

* Preserve the authenticated studio connection separately from ordinary display settings.
* Show a successful connection only after WordPress confirms that the connection was saved.

= 1.0.6 =

* Add one-click creation of an editable draft booking page without forced promotional copy.
* Reuse the existing plugin-created page instead of creating duplicates.

= 1.0.5 =

* Add an authenticated, revocable Studio Portal connection bound to the exact WordPress domain.
* Clarify the booking-page check and link directly to the full booking page.
* Simplify embedded classes by moving legal links to the service footer and showing refresh only after a load failure.

= 1.0.4 =

* Keep wide booking embeds centred when a WordPress theme applies a narrow content constraint.

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

= 1.0.8 =

Adds clearer booking-page requirements and manual block or shortcode instructions.

= 1.0.7 =

Reconnect once after updating so WordPress can retain the authenticated studio connection.

= 1.0.6 =

Adds an optional one-click booking-page setup from the plugin settings screen.

= 1.0.5 =

Reconnect the WordPress website through Studio Portal before displaying classes or products.

= 1.0.4 =

Keeps the booking canvas centred in themes with constrained page content.

= 1.0.3 =

Moves plugin configuration into an easy-to-find main admin menu item.

= 1.0.2 =

Makes the existing StudioCount Bookings settings screen easier to find.

= 1.0.1 =

Improves the embedded layout and removes the duplicate studio introduction.

= 1.0.0 =

Initial release.
