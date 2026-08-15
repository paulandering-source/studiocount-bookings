#!/usr/bin/env bash
set -euo pipefail

script_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
repo_dir="$(cd "$script_dir/.." && pwd)"
version="$(sed -nE 's/^ \* Version:[[:space:]]+([^[:space:]]+).*/\1/p' "$repo_dir/studiocount-bookings.php" | head -n 1)"

if [[ ! "$version" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
	echo "Could not read a semantic plugin version." >&2
	exit 1
fi

release_dir="$repo_dir/release"
package_dir="$release_dir/studiocount-bookings"
zip_path="$release_dir/studiocount-bookings-$version.zip"

rm -rf "$package_dir"
mkdir -p "$package_dir/assets" "$package_dir/blocks/studiocount-bookings" "$package_dir/includes"

cp "$repo_dir/studiocount-bookings.php" "$package_dir/"
cp "$repo_dir/uninstall.php" "$package_dir/"
cp "$repo_dir/readme.txt" "$package_dir/"
cp "$repo_dir/LICENSE" "$package_dir/"
cp "$repo_dir/assets/frontend.js" "$package_dir/assets/"
cp "$repo_dir/assets/frontend.css" "$package_dir/assets/"
cp "$repo_dir/assets/admin.js" "$package_dir/assets/"
cp "$repo_dir/assets/admin.css" "$package_dir/assets/"
cp "$repo_dir/blocks/studiocount-bookings/block.json" "$package_dir/blocks/studiocount-bookings/"
cp "$repo_dir/blocks/studiocount-bookings/index.js" "$package_dir/blocks/studiocount-bookings/"
cp "$repo_dir/blocks/studiocount-bookings/editor.css" "$package_dir/blocks/studiocount-bookings/"
cp "$repo_dir/includes/"*.php "$package_dir/includes/"

find "$package_dir" -type f -exec touch -t 202608150000 {} +
rm -f "$zip_path"
(
	cd "$release_dir"
	LC_ALL=C find studiocount-bookings -type f -print | sort | zip -X -q "$zip_path" -@
)

echo "$zip_path"
