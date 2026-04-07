# Portico Webworks Plugin

Installs and activates the Portico Webworks theme/plugin stack from **WP Admin → Portico Webworks → Dependencies**. **Update** tab: save a GitHub releases URL and one-click update when `portico_webworks_plugin.zip` is attached to the latest release (`includes/github-plugin-update.php`). Customize dependencies with the `pw_dependencies` filter in `includes/dependencies.php`.

**Starter theme:** Pulled from [portico_webworks_starter_theme releases](https://github.com/azharumar/portico_webworks_starter_theme/releases) via the GitHub API. Each release must attach **`portico_webworks_theme.zip`** (same file as e.g. `.../releases/download/v0.4.11/portico_webworks_theme.zip`). Constant: `PW_STARTER_THEME_RELEASE_ZIP`. Theme directory / dependency slug: **`portico_webworks_theme`** (must match the folder inside the zip).

**Bundled ZIPs:** Other dependencies use files under `assets/zips/` (Rank Math Pro, GenerateBlocks Pro, GP Premium, etc.).

**Requires:** WordPress 6.9.4+, PHP 8.3+

**Changelog**

- **0.9.7** (2026-04-07) — Dependencies: **Check updates** button clears cached remote versions for all dependencies and reloads the table.
- **0.9.6** (2026-04-07) — Dependencies screen: **Update** / **Update all** for themes and repo plugins; remote version transient cache; invalidate cache after upgrade; **Install & activate all** runs one action per row (install → update → activate).
- **0.9.5** (2026-04-07) — Starter theme release asset **`portico_webworks_theme.zip`** and slug **`portico_webworks_theme`** (matches [starter theme releases](https://github.com/azharumar/portico_webworks_starter_theme/releases)).
- **0.9.4** (2026-04-07) — **Starter theme** installs from GitHub releases (`github_release` source) instead of a local zip; shared resolver supports any named release asset (`pw_github_get_latest_release_zip_by_asset`). Load `github-plugin-update.php` before `dependencies.php`.
- **0.9.3** (2026-04-07) — Restore **Update from GitHub** (releases URL, latest release + `portico_webworks_plugin.zip`, one-click upgrade). Settings stored in `pw_settings` option (`pw_github_releases_url`).
- **0.9.2** (2026-04-07) — Add Portico child theme dependency (later superseded by GitHub starter theme in 0.9.4).
- **0.9.1** (2026-04-07) — Dependency AJAX: discard upgrader output before activate; reject non-JSON-looking responses with a clear error; avoid HTML from activation hooks breaking `fetch().json()` (e.g. Rank Math).
- **0.9.0** (2026-04-07) — Plugin is only the dependency installer (no hotel CPTs, CMB2, or Composer libraries). Docs and `vendor/` removed from the package; release ZIP built without Composer.
