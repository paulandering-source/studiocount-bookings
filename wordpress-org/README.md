# WordPress.org directory assets

The icon and banner use the established StudioCount tick and pink-to-purple
brand treatment. Their editable SVG sources are under `source/`.

Generate the exact WordPress.org raster sizes on macOS:

```bash
sips -s format png source/icon.svg --out assets/icon-256x256.png
sips -z 128 128 assets/icon-256x256.png --out assets/icon-128x128.png
sips -s format png source/banner.svg --out assets/banner-1544x500.png
sips -z 250 772 assets/banner-1544x500.png --out assets/banner-772x250.png
```

Real screenshots must be captured from the accepted WordPress development
site. They are not generated from mock data or widget-test images.
