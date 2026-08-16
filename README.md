# StudioCount Bookings

StudioCount Bookings is the official WordPress integration for studios using
[StudioCount](https://www.studiocount.com/). It provides a dynamic Gutenberg
block and shortcode that display a studio's public classes and products.

## Product boundary

WordPress stores only the connected public studio slug, a domain-bound public
connection identifier and a default display mode.
StudioCount remains authoritative for classes, availability, products, prices,
bookings, waitlists and payments. The plugin contains no Supabase or Stripe
credentials and receives no card or customer booking data.

The front end uses an isolated hosted StudioCount frame. Locally bundled
JavaScript validates resize and navigation messages against the exact
StudioCount origin, exact frame window, exact instance identifier and exact
versioned schema. Only StudioCount's Checkout return route and Stripe's hosted
Checkout path may become top-level navigation destinations.

## Usage

Open **StudioCount Bookings** in the WordPress admin menu, connect the website
through Studio Portal and keep the selected StudioCount booking page live. You
can then create a draft booking page automatically, insert the **StudioCount
Bookings** block, or add the shortcode manually.

The shortcode equivalent is:

```text
[studiocount_bookings view="both"]
```

Supported view values are `classes`, `products` and `both`.

## Development

The plugin deliberately uses human-readable PHP, JavaScript and CSS with no
compiled or minified assets. No Node or Composer build is required.

Run the focused checks:

```bash
find . -name '*.php' -not -path './release/*' -print0 | xargs -0 -n1 php -l
find assets blocks -name '*.js' -print0 | xargs -0 -n1 node --check
npx --yes eslint@9.39.1 assets blocks
php tests/php/run.php
node tests/js/run.mjs
```

Create a clean release ZIP:

```bash
bash bin/build-release.sh
bash bin/check-release.sh
```

The script copies only the explicit production allowlist into
`release/studiocount-bookings/` and writes the versioned release ZIP.

## External services

The complete data-transfer disclosure is maintained in `readme.txt` and on the
plugin settings screen. StudioCount Terms and Privacy are available at:

- <https://www.studiocount.com/terms>
- <https://www.studiocount.com/privacy>

## Licence

StudioCount Bookings is free software licensed under GPL-2.0-or-later.
