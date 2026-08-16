# StudioCount Bookings

StudioCount Bookings is the official WordPress integration for studios using
[StudioCount](https://www.studiocount.com/). It provides a dynamic Gutenberg
block and shortcode that display a studio's public classes and products.

## Product boundary

WordPress stores only a public studio slug and a default display mode.
StudioCount remains authoritative for classes, availability, products, prices,
bookings, waitlists and payments. The plugin contains no Supabase or Stripe
credentials and receives no card or customer booking data.

The front end uses an isolated hosted StudioCount frame. Locally bundled
JavaScript validates resize and navigation messages against the exact
StudioCount origin, exact frame window, exact instance identifier and exact
versioned schema. Only StudioCount's Checkout return route and Stripe's hosted
Checkout path may become top-level navigation destinations.

## Usage

Save a public StudioCount booking URL or slug under **Settings > StudioCount
Bookings**, then insert the **StudioCount Bookings** block.

The shortcode equivalent is:

```text
[studiocount_bookings studio="studioone" view="both"]
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
`release/studiocount-bookings/` and writes
`release/studiocount-bookings-1.0.2.zip`.

## External services

The complete data-transfer disclosure is maintained in `readme.txt` and on the
plugin settings screen. StudioCount Terms and Privacy are available at:

- <https://www.studiocount.com/terms>
- <https://www.studiocount.com/privacy>

## Licence

StudioCount Bookings is free software licensed under GPL-2.0-or-later.
