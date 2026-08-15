#!/usr/bin/env bash
set -euo pipefail

script_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
repo_dir="$(cd "$script_dir/.." && pwd)"
version="$(sed -nE 's/^ \* Version:[[:space:]]+([^[:space:]]+).*/\1/p' "$repo_dir/studiocount-bookings.php" | head -n 1)"
zip_path="${1:-$repo_dir/release/studiocount-bookings-$version.zip}"

if [[ ! -f "$zip_path" ]]; then
	echo "Release ZIP not found: $zip_path" >&2
	exit 1
fi

tmp_dir="$(mktemp -d "${TMPDIR:-/tmp}/studiocount-bookings-release.XXXXXX")"
trap 'rm -rf "$tmp_dir"' EXIT

if unzip -Z1 "$zip_path" | grep -Eq '(^/|(^|/)\.\.(/|$)|\\)'; then
	echo "Release ZIP contains an unsafe path." >&2
	exit 1
fi

unzip -q "$zip_path" -d "$tmp_dir"
package_dir="$tmp_dir/studiocount-bookings"

[[ -f "$package_dir/studiocount-bookings.php" ]]
[[ -f "$package_dir/readme.txt" ]]
[[ -f "$package_dir/blocks/studiocount-bookings/block.json" ]]
[[ ! -e "$package_dir/.git" ]]
[[ ! -e "$package_dir/tests" ]]
[[ ! -e "$package_dir/bin" ]]
[[ ! -e "$package_dir/node_modules" ]]
[[ ! -e "$package_dir/vendor" ]]

if grep -RIEq 'sb_secret_|service_role(_key)?|sk_(live|test)_[A-Za-z0-9]|SUPABASE_(URL|ANON_KEY)' "$package_dir"; then
	echo "Release ZIP contains a credential-shaped value." >&2
	exit 1
fi

readme_version="$(sed -nE 's/^Stable tag:[[:space:]]+([^[:space:]]+).*/\1/p' "$package_dir/readme.txt" | head -n 1)"
block_version="$(php -r '$v=json_decode(file_get_contents($argv[1]),true); echo $v["version"] ?? "";' "$package_dir/blocks/studiocount-bookings/block.json")"
[[ "$version" == "$readme_version" ]]
[[ "$version" == "$block_version" ]]

find "$package_dir" -name '*.php' -print0 | xargs -0 -n1 php -l >/dev/null
find "$package_dir/assets" "$package_dir/blocks" -name '*.js' -print0 | xargs -0 -n1 node --check

echo "PASS: release ZIP contains the exact production allowlist"
