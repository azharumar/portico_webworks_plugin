# Changelog

## [0.8.32] - 2026-03-23

### Fixed
- **GP Elements singular layouts**: installer sets `_generate_block_type` to `content-template` (not `block`) so single-post templates replace the content area; archives remain `loop-template`
- **GenerateBlocks section archives**: `pw_filter_generateblocks_query_loop_property_scope` does not inject `_pw_property_id` meta when `pw_get_current_property_id()` is missing (≤ 0), avoiding empty loops

### Added
- **`pw_repair_element_block_types()`**: runs when **Install Missing Structure** is used; corrects `_generate_block_type` on generated elements, rewrites legacy `#rooms`-style back-link hrefs to `{{post_type_archive_link}}` on outlet singulars, and property section hashes to `{{pw_section_url:…}}` tokens
- **`pw_resolve_section_url_tokens`** (`render_block`): replaces `{{pw_section_url:cpt}}` with `pw_get_section_listing_url()` for the current property (single vs multi URLs)

### Changed
- **Starter markup**: outlet singular “Back to …” links use `{{post_type_archive_link}}`; property singular section links use `{{pw_section_url:…}}` instead of hash fragments
- **Routing** (`pw_url_front_controller`): when `WP_DEBUG` is on, logs if a section listing archive is set up with no property context after `pw_url_virtual_archive()`

## [0.8.31] - 2026-03-23

### Added
- **GP Elements — singular templates**: installer creates **17** elements — property singular (`pw-property-singular`) plus **archive + singular** pairs for each section CPT (`_pw_element_type` meta: `archive` | `singular`; singulars use `_generate_block_type` `block`, archives remain `loop-template`)
- **Starter markup** (`pw_get_section_starter_markup( $cpt, $type )`): property singular (identity, address, `_pw_direct_benefits` meta query, section link placeholders, scoped `pw_contact` query) and outlet singular markup per CPT (prefixed uniqueIds `rms-*`, `rsts-*`, …)
- **Sample data**: empty **Fact Sheet** page per sample property with `_pw_property_id`, `_pw_generated`, `_pw_section_cpt`, `_pw_is_sample_data`

### Changed
- **`pw_build_element_conditions()`**: `is_singular` vs `is_post_type_archive` from element `type`
- **`pw_find_generated_element( $cpt, $type )`**: distinguishes archive vs singular; legacy archive elements without `_pw_element_type` still match as archive
- **Permalinks → Site structure**: GP Elements table **Type** column (Archive / Singular)

## [0.8.30] - 2026-03-23

### Fixed
- **Property singular URL rule order**: bare `/{property-slug}` rule moved from `bottom` to `top` so it matches before WordPress page permastruct. After updating, visit Settings → Permalinks and click Save to flush rewrite rules.

## [0.8.29] - 2026-03-23

### Fixed
- **Property singular 404** in multi-property mode. After updating, visit Settings → Permalinks and click Save to flush rewrite rules.

## [0.8.25] - 2026-03-22

### Changed
- **`pw_property`**: `public` always true; in **multi** mode native CPT **`rewrite`** (`slug` empty, `with_front` false) and **`query_var`** `pw_property` so Core permalinks and block editor slug use `%pagename%` via `get_sample_permalink()`; removed duplicate bare-segment `pw_property_slug` rule
- **Block editor**: `__block_editor_compatible_meta_box` on property profile meta boxes and all CMB2 boxes scoped to `pw_property`

## [0.8.24] - 2026-03-22

### Added
- **Slug support** (`supports` → `slug`): `pw_property`, `pw_room_type`, `pw_restaurant`, `pw_spa`, `pw_meeting_room`, `pw_offer`, `pw_nearby`, `pw_experience`, `pw_event` — native WordPress permalink slug field in the block editor

### Changed
- **Multi-property sample data** (`sample-data-multi-install.php`): explicit `post_name` / slug keys for room types, outlets, offers, nearby, experiences, and events

## [0.8.23] - 2026-03-22

### Added
- **GeneratePress Elements** (`gp_elements`): idempotent installer creates one Loop Template per section CPT with display condition `is_post_type_archive`; `pw_get_required_elements()`, `pw_find_generated_element()`, `pw_install_element()`, `pw_run_elements_installer()`; guard + admin notice when GP Premium is inactive
- **Site structure** (Permalinks): renamed from Page structure; table for installer-managed pages plus **Section archive elements** (Exists / Missing / GP Not Active); **Install Missing Structure** runs page + element installers

### Changed
- **Section CPTs** (`pw_url_section_cpts()`): `has_archive` set to plural base slug (`pw_get_section_base( …, 'plural' )`) while `rewrite` stays false; **`pw_restaurant`** now `public` with `query_var` for outlet URLs
- **Front controller** (`pw_url_front_controller`): sets `$wp_query` archive / post type archive / `queried_object` before `pw_url_virtual_archive()` on section listing URLs
- **`pw_get_required_pages()`**: returns empty array (no section listing pages); **`pw_get_section_starter_markup()`** used for Elements only — `posts_per_page` 10, `_pwNote` for Reading pagination, `generateblocks/query-pagination` after each looper; **`pw_on_property_published`** runs element installer idempotently
- **Admin notices**: publish + manual installer copy updated; optional GP missing warning via transient

### Removed
- Installer no longer creates WordPress **pages** for section archive URLs (replaced by GP Elements)

## [0.8.22] - 2026-03-22

### Removed
- **Property URL base & listing**: no `pw_property` row in section bases; removed `pw_disable_property_base`, `pw_property_plural_base`, `pw_property_archive` from settings (stripped on merge). Multi-property URLs are always `/{property-slug}` with sections under `/{property-slug}/{section}/…` — no `/hotels/` prefix or global property archive route
- **Plugin property listing page**: installer no longer creates a site-level property listing page; Permalinks UI drops property archive checkbox and prefix checkbox

### Changed
- **`pw_get_property_url()`**: root path is the property slug only
- **`pw_get_section_listing_url()`**: only child section CPTs (`pw_url_section_cpts()`)
- **Rewrites**: dropped `pw_property_listing`, `pw_property_base_segment`, and `pw_base_segment` query vars; removed virtual `pw_property` archive and bare `/hotel`-style property singular redirects
- **Multi bare `/{slug}`**: when the slug matches a published property, the front controller now sets up a singular `pw_property` query (template `single-pw_property.php` etc.)

## [0.8.21] - 2026-03-22

### Fixed
- **Admin CSS** (`includes/admin-assets.php`): invalid PHP in a double-quoted string — selector `[data-pw-property-mode="single"]` ended the string early and caused a parse error (white screen)
- **Revisions**: define `WP_POST_REVISIONS` when missing so `wp_insert_post` / `wp_update_post` never hit Core revision code with an undefined constant (some hosts / load orders)

## [0.8.20] - 2026-03-22

### Removed
- Permalink settings migration: no longer maps stored `pw_property_plural_base`, `pw_permalink_base_fixed`, or `pw_property_base` into section bases / `pw_disable_property_base`; those keys are stripped from merged settings output
- Unused `pw_sanitize_property_base()` helper

## [0.8.19] - 2026-03-22

### Added
- **`pw_property` in section URL bases** (`pw_default_section_bases()` first row: `hotels` / `hotel`); Permalinks table lists **Properties** with plural/singular like other sections
- **`pw_disable_property_base`** (default on): multi-property mode can use plural base as path prefix (`/hotels/leela-residency`) or property slug at root (`/leela-residency`); checkbox under the Properties row (hidden in single-property mode via `data-pw-property-mode` + CSS)
- **`pw_property_base_disabled()`**, **`pw_multi_property_url_prefix()`**; legacy `pw_permalink_base_fixed` / `pw_property_base` cleared in merged settings; migration from old fixed base and `pw_property_plural_base` when `pw_property` was absent from stored section bases

### Changed
- **`pw_url_section_cpts()`**: child outlet CPTs only (excludes `pw_property`); rewrites use child loops plus explicit `pw_property` archive/singular/bare-singular rules; root `/hotel` → `/hotels` in single and multi+prefix modes; property-scoped `/…/hotel` bare singular in multi (no root bare when prefix disabled)
- **Permalinks save**: drops separate URL prefix / property listing slug fields; syncs `pw_property_plural_base` from section bases; flush when `pw_disable_property_base` changes
- **`pw_get_fixed_permalink_base()`** → effective multi prefix via `pw_multi_property_url_prefix()`; **`pw_get_section_listing_url()`** for `pw_property` in multi returns global `/{plural}`
- **Page installer**: skips `pw_property` in single and per-property section loops; global listing slug from `pw_get_section_base( 'pw_property', 'plural' )`
- **Reserved slugs**: property plural no longer duplicated from `pw_property_plural_base` (covered by section bases)
- **Admin**: `.wrap.pw-admin` has `data-pw-property-mode`; **General** hides default-property row when Multi is selected (`admin-settings.js`)
- **`URL-ARCHITECTURE.md`**: `pw_property` row, prefix note, reserved list, settings reference, `/hotel` redirect tests

## [0.8.18] - 2026-03-22

### Changed
- **Admin**: **General** tab is property mode + default property only; **Permalinks** tab holds URL prefix, property listing slug / archive, section URL bases (table: Section | Singular | Plural), and **Page structure** + installer (no nested forms); new **Update** tab (second-to-last, before **About**) for GitHub URL + one-click update; `pw_save_github_settings` saves releases URL separately; installer redirect returns to Permalinks

## [0.8.17] - 2026-03-22

### Added
- **Tiered URL routing**: prioritized rewrite stack (outlet singulars, section listings, bare singular redirects, property singular, optional property archive, static page wildcard); `includes/property-rewrites.php` front controller, `pw_redirect_with_qs()` (query string preserved on 301s), `redirect_canonical` guard
- **Section URL bases** (General settings): per-section plural/singular slugs, property plural base, archive toggle; `pw_get_section_bases()` / `pw_get_section_base()` in `includes/permalink-config.php`
- **`includes/reserved-slugs.php`**: reserved slug union, settings save conflict check, `wp_unique_post_slug` suffixing for `pw_property` and `page`
- **Per-property section toggles**: `_pw_enabled_sections` (property metabox + REST); `pw_is_section_enabled()`
- **Page scope**: `register_post_meta()` on `page` for `_pw_property_id`, `_pw_generated`, `_pw_section_cpt` (property-scoped static URLs and installer)
- **`includes/page-installer.php`**: idempotent required listing pages (`_pw_generated`); runs on property publish, after settings that change section bases or property mode, and via **Install Missing Pages** on General; GenerateBlocks starter `post_content` for section CPT listing pages (where applicable)
- **`URL-ARCHITECTURE.md`**: tiered URL spec, rule order, static wildcard resolver, redirect behaviour, reserved slugs, test matrix, and `pw_get_current_property_id()` resolution notes
- **Deferred rewrite flush** when mode, bases, or section slugs change (`admin_init` + transient); **Settings → Permalinks** reduced to optional URL prefix; `assets/admin-settings.js` mode-switch warnings
- **Contact system** (see also 0.8.15): `pw_contact` CPT with `_pw_scope_cpt` / `_pw_scope_id`, `pw_resolve_contact()` / `includes/contact-resolver.php`, orphan protection on related CPT delete

### Changed
- **`pw_property`**: `rewrite` / `query_var` false so routing is plugin-owned; section CPTs `public` + `query_var` (rewrite still false); nearby CPT key `pw_nearby` with URL bases `places` / `place` (no DB migration)
- **`pw_get_current_property_id()`**: GenerateBlocks loop block property scope (when query passes `pw_property_id`) before `pw_property_slug` QV → singular → default → `0`; `pw_resolve_property_slug()` with static cache; `pw_get_property_url( $id )` without trailing slash; `pw_get_section_listing_url()`, `pw_get_outlet_url()`
- **Empty `pw_property_base` allowed** (no prefix). Legacy `pw_permalink_subpaths` / dynamic base UI removed from merged settings
- **General → Page structure**: status column uses **Exists** for installer-managed pages; manual installer button **Install Missing Pages**

### Fixed
- **Front-end 301s**: all issued through `pw_redirect_with_qs()`; admin POST redirects remain direct `wp_safe_redirect` with inline exempt note
- **Wildcard static pages**: property-scoped `get_posts` first; global `get_page_by_path()` only as documented fallback; installer/admin conflict checks call `get_page_by_path()` only with explicit non-routing comments

### Removed
- Sub-path map UI and `_pw_url_slug` property field; old `template_redirect` 404 for mismatched property base in `property-helpers.php`

## [0.8.16] - 2026-03-22

### Added
- **Settings → Update from GitHub**: release notes use GitHub’s Markdown API (GFM); sanitized HTML output with admin styles for headings, lists, code, tables, and blockquotes; if the API fails, notes fall back to escaped text with paragraphs

### Changed
- **Settings**: GitHub releases URL field moved under the **Update from GitHub** heading (still saved with **Save Settings**); saving general settings without that field no longer clears a stored URL
- **Admin (CMB2)**: on Portico CPT edit screens, `pw-cmb2-overrides` widens URL/email inputs, most `text_small` fields (excluding date, time, and color pickers), and `text_money` so long values fit the column
- **`pw_contact`**: Phone, Mobile, WhatsApp, and Email use wider inputs (`large-text` / full-width email)
- **`pw_event_organiser`**: Organiser URL term field uses `regular-text`
- **Documentation**: `DATA-STRUCTURE.md` aligned with current CPT/meta/settings/permalinks code; sample multi-install docblock references the doc

## [0.8.15] - 2026-03-22

### Added
- **Settings → Update from GitHub**: shows installed vs latest release tag, link to the release on GitHub, release notes (API body, cached ~15 minutes), and warns when the release has no `portico_webworks_plugin.zip` asset
- **`pw_contact` CPT** (`rest_base` `pw-contacts`): scoped contact cards per property (outlet-specific, group-level by CPT, or property fallback); CMB2 metabox `pw_contact_metabox`; admin submenu under Portico Webworks; `assets/admin-contact-scope.js` + `GET /wp-json/pw/v1/contact-scope-posts` for outlet select
- **`includes/contact-resolver.php`**: `PW_CONTACT_SCOPE_CPTS`, `pw_resolve_contact()`, `pw_resolve_primary_contact()`, file-level contract PHPDoc; **`GET /wp-json/pw/v1/contacts`** (`edit_posts`) returns resolved contacts
- **`before_delete_post`**: when `pw_restaurant`, `pw_spa`, `pw_meeting_room`, or `pw_experience` is deleted, matching `pw_contact` rows demote `_pw_scope_id` to `0` and prefix `[Unlinked]` on `_pw_label`
- **GenerateBlocks**: query filter `pw-gb-contact-filter-property` limits `pw_contact` queries to `_pw_scope_cpt = property` when combined with `pw-gb-scope-property` (fact-sheet style)

### Changed
- **Contacts**: removed property repeatable `_pw_contacts` / `pw_property_contacts`; `pw_get_property_profile()['contacts']` uses `pw_resolve_contact( 'property', 0, $id )` (TODO comments for audit)
- **Sample data**: `pw_contact` rows for demo properties; installer merges full **`pw_property_type`** and **`pw_policy_type`** seed lists from `pw_get_taxonomy_seed_terms()`, assigns **Hotel** / **Resort** on the two demo properties; Data tab copy updated
- **`pw_contact`** in import/export, sample-data meta registration, purge post-type list, admin CMB2 styling scope, `DATA-STRUCTURE.md`, **`TAXONOMY-SEED-VALUES.md`** (canonical mirror of seeds + demo-term notes)
- **Fact sheet markup** (`tools/generate-gb-fact-sheet-markup.py`, `gb-pro-markup-samples.html`): Contacts section queries `pw_contact` with scoped classes and `_pw_label` / channel meta

### Removed
- **Meeting rooms**: `_pw_sales_phone`, `_pw_sales_mobile`, `_pw_sales_whatsapp`, `_pw_sales_email` (CMB2, REST registration, sample data, GB samples / generator)

## [0.8.14] - 2026-03-22

### Added
- **Property permalinks** (multi mode): **Permalinks** settings tab with base segment (fixed prefix or dynamic: city, state, country, country code, property type), property slug source (post slug vs custom `_pw_url_slug`), and sub-path → page mappings; `includes/permalink-config.php`, `includes/admin-permalinks.php`, `includes/property-rewrites.php`; `pw_get_property_url()` and URL resolution updates in `includes/property-helpers.php`
- **Custom URL slug** field on the property profile when custom slug mode is enabled, with uniqueness validation among published properties

### Changed
- **General settings**: property base path removed from General (link to Permalinks); settings save merges stored options so permalink keys are preserved
- **`pw_property` CPT**: rewrite only when multi + fixed base; dynamic base uses custom rewrite rules; sub-path requests resolve to mapped pages with `pw_property_slug` / `pw_property_base_segment` query vars
- **Sample data**: multi-install logic moved to `includes/sample-data-multi-install.php`

## [0.8.13] - 2026-03-22

### Changed
- **Sustainability & accessibility (property)**: CMB2 repeatable groups no longer use `pw-facet-fixed-rows` (Add/Remove visible again); row titles show selected practice/feature label via `assets/admin-property-facets.js`; **Note** field relabeled **Content** as a larger textarea; facet `note` values normalized with `sanitize_textarea_field()`
- **DATA-STRUCTURE.md** and **schema-org-hotel-reference.md**: documentation updates

### Removed
- **Fact sheet** implementation (`includes/fact-sheet*.php`, `includes/fact-sheet-fragments/`, `assets/fact-sheet.css`) and bootstrap `require`s

## [0.8.12] - 2026-03-22

### Changed
- **Fact sheet page**: GenerateBlocks-only block markup (`generateblocks/text`, `generateblocks/headline`, `generateblocks/element`); `{{portico:pw_fact_*}}` expansion uses `render_block` on GenerateBlocks blocks for the fact sheet page instead of a global `the_content` filter; `PW_FACT_SHEET_CONTENT_VERSION` 3 refreshes stored content; legacy core `group` + `pw-fact-sheet` layout is detected for one-time upgrade; fact sheet root styles also target `.gb-element-pwfse0`
- **General settings**: native admin form + `admin_post_pw_save_settings` instead of CMB2 options hooks for `pw_settings`; `pw_get_merged_pw_settings()` uses defaults only (no legacy top-level option merge)

## [0.8.11] - 2026-03-22

### Changed
- **Property context** (`pw_get_current_property_id`): returns `0` when unset instead of `WP_Error`; single mode uses the configured default only; multi mode resolves from the URL path; removed fallback to the first published property
- **Multi-property URLs**: 404 on `template_redirect` when the request matches the property URL pattern but no property ID resolves
- **Child CPTs**: set `publicly_queryable` to `true` for consistent query/REST behavior
- **`_pw_is_sample_data` meta**: `show_in_rest` with `auth_callback` so the block editor can read it; term meta uses `edit_term` capability
- **Settings merge / save**: stop merging `pw_github_releases_url` from the legacy `pw_*` option in `pw_get_merged_pw_settings()`; preserve GitHub URL from prior `pw_settings` when the field is omitted on save (single and multi)

### Fixed
- **Fact sheet and property helpers**: treat missing property ID as `0` (drop `WP_Error` handling where the return type is now always an integer)

### Added
- **Docs**: `google-lodging-format-schema.md`, `schema-org-hotel-reference.md`; **DATA-STRUCTURE.md** updates

## [0.8.10] - 2026-03-21

### Fixed
- **General settings save**: register `admin_post_pw_settings` at priority 0 to call CMB2 `save_fields()` after nonce/capability checks, then redirect and `exit`, so saves are not skipped when `CMB2_Options_Hookup::can_save()` fails on `admin-post.php` (values now persist for default property, GitHub URL, etc.)

## [0.8.9] - 2026-03-21

### Fixed
- **Settings persistence**: merge stored `pw_settings` with legacy option fallbacks via `pw_get_merged_pw_settings()` so `pw_default_property_id` and `pw_github_releases_url` stay visible after refresh when the DB array was partial; preserve `pw_github_releases_url` on save when omitted from POST (including multi-property mode)

## [0.8.8] - 2026-03-21

### Fixed
- **Settings**: preserve `pw_default_property_id` when saving single-property mode if the field was omitted from POST (e.g. after toggling mode without a reload), so the value no longer falls back to empty
- **GitHub updater**: clearer error when `/releases/latest` returns 404; try listing releases and use the first release that includes `portico_webworks_plugin.zip`; dedicated message for missing releases vs 401/403

### Changed
- **Settings UI**: hide the default-property row when Multi-Property is selected (client-side toggle on the Property Mode radios) via `assets/admin-settings.js`

## [0.8.7] - 2026-03-21

### Fixed
- **Install / bootstrap fatals**: load `includes/pw-fatal-log.php` only when the file is readable (incomplete ZIP no longer whitescreens on `require_once`); if `vendor/cmb2/cmb2/init.php` is missing, show an admin notice and stop loading the rest of the plugin instead of a hard fatal; register the taxonomy-seed `init` callback only after `taxonomy-seeds.php` is loaded so a partial bootstrap does not call `pw_seed_taxonomy_terms()` before it exists

### Changed
- **Fatal logger** (`includes/pw-fatal-log.php`): logs **any** PHP fatal / uncaught `Throwable` after the plugin loads (not only files under `portico_webworks_plugin`), tries **uploads**, **sys temp**, and **ABSPATH** if `wp-content` is not writable, and duplicates a line to PHP’s `error_log`. Optional `PW_FATAL_LOG_PLUGIN_ONLY` and `PW_FATAL_LOG_BOOT_PROBE` (see file docblock).

## [0.8.6] - 2026-03-21

### Fixed
- **PHP 8 `ArgumentCountError`**: WordPress passes arguments to `login_headerurl`, `login_headertext`, and `admin_footer_text`; CMB2 calls `options_cb`, `sanitization_cb`, and callable `options` with extra arguments — callbacks updated to accept them
- **Offer metabox** `show_on_cb` helpers now read post ID from the CMB2 field object (was treating the field as an array and could error on PHP 8)
- **Default Template** setting no longer uses `sanitize_text_field` directly as CMB2 sanitization callback (WordPress only accepts one argument)

### Added
- **Fatal error log** (no `WP_DEBUG` required): on shutdown, plugin-related fatals append one line to `wp-content/portico-webworks-fatal.log`

## [0.8.5] - 2026-03-21

### Fixed
- **Default property** field `show_on_cb`: accept CMB2’s `$field` argument so PHP 8 does not throw `ArgumentCountError` on the settings screen

### Changed
- Declared **Requires at least: 6.9.4** and **Requires PHP: 8.3** in the plugin header; `composer.json` PHP `>=8.3`; README and SETUP requirements updated

## [0.8.4] - 2026-03-21

### Added
- **Single Property** setting **Default property** (`pw_default_property_id` in `pw_settings`); required site-wide context in single mode (no fallback to “first published” property)
- GenerateBlocks **Fact Sheet** page: block markup with table of contents, `{{portico:…}}` tokens, and outer `generateblocks/element`; **GenerateBlocks Pro** dynamic tags (`pw_fact_*`) mirroring the same output; `PW_FACT_SHEET_CONTENT_VERSION` and `plugins_loaded` sync for upgrades from `[pw_fact_sheet]`

### Changed
- `pw_get_current_property_id()` in single mode uses only the configured default property
- Fact sheet data rendering moved to `includes/fact-sheet-render.php` and `includes/fact-sheet-fragments/*`; removed `[pw_fact_sheet]` shortcode and `includes/fact-sheet-template.php`

## [0.8.3] - 2026-03-21

### Added
- Settings: **GitHub releases URL** (`pw_github_releases_url`) and **Update from GitHub** (uses GitHub API `releases/latest`, requires `portico_webworks_plugin.zip` on the release); `includes/github-plugin-update.php`, `admin_post_pw_github_plugin_update`

## [0.8.2] - 2026-03-21

### Added
- Property **Fact Sheet** page (`/fact-sheet`): auto-created on plugin activation with `[pw_fact_sheet]`; `includes/fact-sheet.php` and `includes/fact-sheet-template.php` output all property meta and child CPT data via direct `get_post_meta` / `get_posts` (scoped by `_pw_property_id`); `pw_ensure_fact_sheet_page()` and option `pw_fact_sheet_page_id`

## [0.8.1] - 2026-03-21

### Added
- `pw_event`: `pw_event_timezone_for_property()`, `pw_event_local_datetime_to_iso8601()`; REST read-only `pw_start_datetime_iso8601` / `pw_end_datetime_iso8601` (local wall times interpreted in linked property `_pw_timezone`); FAQ-style admin backward-compat for datetime fields uses property timezone
- `pw_nearby`: `_pw_lat`, `_pw_lng` for schema/map coordinates
- `pw_room_type`: repeatable `_pw_rates` (label, `rack` | `seasonal` | `advance` | `package`, price, optional validity dates, `advance_days`, `includes_breakfast`) with `pw_sanitize_pw_rates_meta`
- `pw_faq`: `_pw_property_id` (flat FK alongside `_pw_connected_to`); `pw_get_faqs_for( 'pw_property', $id )` matches it
- `pw_offer`, `pw_experience`: `_pw_property_id`; `pw_get_experiences_for( 'pw_property', $id )` matches it

### Changed
- `DATA-STRUCTURE.md` for the above

## [0.8.0] - 2026-03-21

### Breaking
- `pw_property` sustainability and accessibility: removed flat meta keys `_pw_sus_*`, `_pw_sus_*_note`, `_pw_acc_*`, `_pw_acc_*_note`. Replaced with `_pw_sustainability_items` and `_pw_accessibility_items` (arrays of `{ key, status, note }`). Canonical `key` slugs and labels live in `includes/property-facet-definitions.php`.

## [0.7.9] - 2026-03-21

### Added
- Admin **Data** tab combines Import/Export with sample content, default taxonomy reinstall, and remove-all-plugin-data actions

### Changed
- Import/Export UI renders inside the Data tab; separate Import/Export tab removed
- Sample data removal, counts, and tagged-item listing use `suppress_filters` / `suppress_filter` on internal queries so third-party query filters (e.g. multilingual) do not skip plugin CPTs and terms

## [0.7.8] - 2026-03-21

### Added
- Sample Data: expandable list of items tagged `_pw_is_sample_data` (posts + terms) with counts; **Reinstall default taxonomy terms**; **Remove all plugin data** (all plugin CPTs, plugin taxonomy terms, orphan relationship/meta cleanup, reset seed prompt options)
- `pw_term_name_is_taxonomy_seed_value()`; taxonomy terms that match `pw_get_taxonomy_seed_terms()` are not tagged as sample data; existing seed-named terms are un-tagged when viewing Sample Data or before install/purge
- `pw_sample_wp_insert_post()` so the sample installer reliably flags every created post
- Plugins list **Settings** link; post-install and activation links to plugin settings; dismissible activation notice pointing to settings
- Overview tab: direct link to **Settings → Reading** for search visibility

### Changed
- `register_activation_hook` calls `pw_plugin_activation()` (defaults plus settings-notice transient)

## [0.7.7] - 2026-03-21

### Added
- Sample Data: **Remove sample data** action; deletes all posts/pages/CPT rows tagged with internal meta `_pw_is_sample_data`, then removes taxonomy terms that were created by the installer and carry the same flag
- `includes/sample-data-meta.php`: registers hidden `_pw_is_sample_data` for plugin CPTs plus `post`/`page`, and for plugin taxonomies plus `category` / `post_tag` (`show_in_rest: false`, `auth_callback` blocks REST updates)
- Sample installer adds a demo **page**, **post**, **category**, and **post_tag** (when those terms are newly created they are removable with the flag)

## [0.7.6] - 2026-03-21

### Added
- Upgrade prompt for default taxonomy terms: admins must accept or dismiss; only missing term names are created (existing terms unchanged)
- `pw_taxonomy_seed_prompt_status` option: `pending` | `dismissed` | `completed` | `auto_completed` (fresh activation path)

## [0.7.5] - 2026-03-21

### Added
- Sample Data admin tab: install a full single-property demo dataset (no images)
- Taxonomy seed terms on activation via `includes/taxonomy-seeds.php` (replaces policy-only seed)
- `TAXONOMY-SEED-VALUES.md` reference for seed lists

### Changed
- General settings: Property Mode control uses radio buttons
- `DATA-STRUCTURE.md` updates

## [0.7.4] - 2026-03-21

### Added
- Favicon by RealFaviconGenerator as required dependency

## [0.7.3] - 2026-03-19

### Fixed
- .gitignore: change `/vendor/` to `/vendor/*` so negation patterns work; vendor (CMB2, PhpSpreadsheet, composer) now tracked for production deployment

## [0.7.1] - 2026-03-19

### Changed
- Admin menu: reorganise into logical groups with section dividers (Properties, Property Content, Marketing)
- Admin menu: rename Overview to Settings; rename Settings tab to General
- Admin menu: add "Add New Property" to sidebar; suppress "Add New" for all child CPTs
- sync-version.ps1: make admin-preview.html optional (file was removed in 0.7.0)

## [0.7.0] - 2026-03-19

### Added
- Property: add `_pw_check_in_time`, `_pw_check_out_time`, `_pw_year_established`, `_pw_total_rooms` structured meta
- Property: add `_pw_og_image` (attachment ID) for custom Open Graph image per property via new SEO & Social Sharing metabox
- Property: migrate `pw_default_template` to plugin-level setting (Settings page)
- Room Type: add `_pw_rate_to` (upper rate range), `_pw_max_extra_beds`, `_pw_display_order`
- Spa: add `_pw_number_of_treatment_rooms`; add `is_closed` boolean and `session_label` per operating hours session
- Restaurant: add `is_closed` boolean per operating hours session
- Meeting Room: add `_pw_prefunction_area_sqft` and `_pw_prefunction_area_sqm`
- Offer: add `_pw_discount_type`, `_pw_discount_value`, `_pw_minimum_stay_nights`, `_pw_room_types`, `_pw_display_order`
- Event: add `_pw_recurrence_rule` (iCal RRULE string), `_pw_organiser_name`, `_pw_organiser_url`, `_pw_event_status`, `_pw_event_attendance_mode` for schema.org compliance
- FAQ, Nearby, Experience: add `_pw_display_order`

### Changed
- Event: `_pw_start_datetime` and `_pw_end_datetime` now stored as `Y-m-d H:i:s` (was Unix timestamp string)

### Removed
- Property: remove `_pw_property_name` meta — `post_title` serves as the property name
- Property: remove `_pw_slug` meta — `post_name` is used directly in URL resolution
- Event: remove `_pw_is_recurring` boolean — replaced by `_pw_recurrence_rule`

## [0.6.0] - 2026-03-19

### Added
- Admin UI: removed "Property Profile" tab; tab order is now Settings → Dependencies → About
- Property: rename meta key `_pw_brand_name` → `_pw_property_name`; rename section "Identity" → "General"
- Property General: move `_pw_currency` into General section (rendered via custom metabox, CMB2 currency box removed)
- Property Address: add `_pw_state` and `_pw_postal_code` meta keys
- Property Contact: add `_pw_mobile` and `_pw_whatsapp` meta keys
- Property Social: add `_pw_social_twitter` meta key
- Property Geo: add `_pw_google_place_id` and `_pw_timezone` (IANA timezone dropdown via `DateTimeZone::listIdentifiers()`)
- Sustainability: add paired `_note` string meta for all 19 sustainability parameters
- Accessibility: add paired `_note` string meta for all 19 accessibility parameters
- Room Type: add `_pw_max_adults` and `_pw_max_children` meta keys; admin JS validation enforces max_adults + max_children ≤ max_occupancy
- Restaurant: add `_pw_location` meta key; operating hours sessions now include a `session_label` field; UI labels updated to "Session" instead of "Day"
- Pool: add `open_time` and `close_time` fields to each pool entry in `_pw_pools`
- Meeting Room: add `_pw_phone`, `_pw_mobile`, `_pw_whatsapp`, `_pw_email` contact meta keys
- DATA-STRUCTURE.md: full rewrite reflecting all structural changes

## [0.5.2] - 2026-03-19

### Fixed
- Restore all CPT list-view entries in the Portico Webworks sidebar — `pw_remove_cpt_submenus()` was incorrectly hiding Properties, Room Types, Restaurants, Spa, Meeting Rooms, Amenities, Policies, FAQs; function is now a no-op since `admin-page.php` already handles the post-new duplicate removal

## [0.5.1] - 2026-03-19

### Fixed
- Include `vendor/autoload.php` and `vendor/composer/` in release ZIP so PhpSpreadsheet (and any future Composer-autoloaded library) is resolvable on the production server
- Remove `pw_offer`, `pw_nearby`, `pw_experience`, `pw_event` from `pw_remove_cpt_submenus()` so their list-view admin pages are accessible in the sidebar
- Add the four new CPTs to `admin-page.php` post-new removal list to suppress the redundant "Add New" duplicate submenu entry

## [0.5.0] - 2026-03-19

### Added
- Register CPTs: `pw_offer`, `pw_nearby`, `pw_experience`, `pw_event` under Portico Webworks admin menu
- Register taxonomies: `pw_nearby_type`, `pw_transport_mode` on `pw_nearby`; `pw_experience_category` on `pw_experience`; `pw_event_type` on `pw_event`
- Sustainability checklist meta (19 feature keys + certification name/URL) on `pw_property`
- Accessibility checklist meta (19 feature keys) on `pw_property`
- Pool details repeatable group (`_pw_pools`) on `pw_property`
- Direct booking benefits repeatable group (`_pw_direct_benefits`) on `pw_property`
- CMB2 meta boxes for all new CPTs and all new `pw_property` groups
- Add `pw_offer`, `pw_nearby`, `pw_experience`, `pw_event` to import/export allowed types
- `_pw_currency` meta on `pw_property` with CMB2 currency selector
- `pw_get_faqs_for()` and `pw_get_property_currency()` helper functions
- `pw_faq` CPT added to admin page navigation list

## [0.4.1] - 2026-03-19

### Fixed
- Release workflow now runs `composer install --no-dev` before zipping, ensuring `vendor/cmb2/` is included in the plugin package

## [0.4.0] - 2026-03-19

### Added
- Bundle CMB2 as a vendored library (`vendor/cmb2/`); suppress its admin menu via `cmb2_menus` filter
- CMB2 meta box forms for all child CPTs: `pw_feature`, `pw_room_type`, `pw_restaurant`, `pw_spa`, `pw_meeting_room`, `pw_amenity`, `pw_policy`
- `pw_property_options()` helper to populate property selector fields
- `composer.json` for managing bundled Composer dependencies

### Changed
- Refactor `dependencies.php`: registry filterable via `pw_dependencies` filter
- Extract `PW_DEP_ACTIVE`, `PW_DEP_INSTALLED`, `PW_DEP_NOT_INSTALLED` status constants
- Extract `pw_can_manage_deps()` capability helper; replace all inline `current_user_can('install_plugins')` calls

## [0.3.9] - 2026-03-19

### Added
- Register child CPTs: `pw_room_type`, `pw_restaurant`, `pw_spa`, `pw_meeting_room`, `pw_amenity`, `pw_policy`, `pw_feature` — all nested under the Portico Webworks admin menu
- Register taxonomies: `pw_bed_type`, `pw_view_type` on `pw_room_type`; `pw_meal_period` on `pw_restaurant`; `pw_treatment_type` on `pw_spa`; `pw_av_equipment` on `pw_meeting_room`
- Register post meta for all child CPTs with full REST schema (scalar, boolean, integer, array, and operating hours object array)
- Add `pw_get_child_posts()` and `pw_get_room_features()` helper functions

## [0.3.8] - 2026-03-19

### Changed
- Rearchitect pw_property meta from serialized array to flat meta keys for GenerateBlocks dynamic tag access

## [0.3.7] - 2026-03-19

### Fixed
- Override `is_post_type_viewable` for pw_property so GenerateBlocks discovers it in single-property mode

## [0.3.6] - 2026-03-19

### Removed
- Removed `admin-preview.html` and deleted the `release-on-push` workflow and developer notes as part of streamlining the release process.

## [0.3.5] - 2026-03-19

### Fixed
- Make `pw_property` CPT public for GenerateBlocks/Gutenberg Dynamic Tag picker discovery
- Ensure search engine indexing behavior follows WordPress Settings -> Reading ("Discourage search engines from indexing this site") via `blog_public`

### Changed
- Replace Production/Development badge with a Search engine indexing ON/OFF indicator based on the actual `blog_public` value

## [0.3.4] - 2026-03-19

### Fixed
- Register property profile meta for `pw_property` CPT so REST dynamic data discovery works
- Make `pw_property` CPT public for GenerateBlocks/Gutenberg Dynamic Tag picker discovery
- Ensure search engine indexing behavior follows WordPress Settings -> Reading ("Discourage search engines from indexing this site") via `blog_public`

### Changed
- Show Production/Development badge in the Portico Webworks admin header
- Replace Production/Development badge with a Search engine indexing ON/OFF indicator based on the actual `blog_public` value

## [0.3.3] - 2026-03-19

### Added
- Expose `pw_property` CPT to the WordPress REST API for GenerateBlocks/page-builder discovery
- Expose `pw_property` profile fields (e.g. `property_name`, `email`, `instagram`) via REST for dynamic content
- Register property profile meta for REST reads

## [0.3.2] - 2026-03-19

### Added
- `Site Mode` toggle in admin (updates `blog_public` for indexing)
- `sync-version.ps1` to sync WordPress header and `admin-preview.html` from `PW_VERSION`
- Collapsible `Property Profile` sections using `details/summary`

### Changed
- Updated `pw_property` admin menu/labels (“Properties” naming)
- Dependency installer missing-dependency notice now renders via `pw_admin_notices`
- GitHub release packaging now includes bundled `*.zip` assets

## [0.3.1] - 2026-03-18

### Added
- Intellectual Property and client deployment notice in the plugin About tab.
- Multi-property mode support (`pw_property_mode`: `single` or `multi`) and URL-based property resolution.

### Changed
- Refactored internal code namespace from `portico_webworks_*`/`PORTICO_WEBWORKS_*` to `pw_*`.
- Updated admin UI and `admin-preview.html` to match the new About/Settings layout.

## [0.1.8] - 2026-03-18

### Fixed
- Clean plugin bootstrap file to prevent white screen of death when activating the plugin
- Tag hotfix releases for 0.1.7 (v0.1.7-hotfix1, v0.1.7-hotfix2)

## [0.1.7] - 2026-03-18

### Added
- Header logo, plugin title, and version badge in the admin page
- Header search bar to jump to fields inside Property Profile
- About main tab for the plugin

### Changed
- Rename plugin to “Portico Webworks Hotel Website Manager”
- Match admin preview HTML with the new header, search, and About tab layout

### Fixed
- Social URL fields now validate `http/https` URLs and show a green tick when valid
- Add footer copyright link with UTM parameters opening in a new tab

## [0.1.6] - 2026-03-18

### Added
- `admin-preview.html` page for visualising the admin UI without installing the plugin

### Changed
- Admin UI: keep 2-level navigation (top horizontal tabs + integrated vertical section tabs)
- Property Profile: integrated left section tabs inside the same card as the form

### Fixed
- Hide horizontal scrollbar on top tabs
- Remove redundant “Sections” label and subsection heading/description in Property Profile

## [0.1.5] - 2026-03-18

### Added
- Property Profile left-side section navigation
- Per-section Save buttons (Identity, Address, Contact, Geo, Social)

### Fixed
- Prevent section saves from clearing other Property Profile fields

## [0.1.4] - 2026-03-18

### Added
- Single-page Portico Webworks admin UI (tabs + cards)
- Helper text and better placeholders for Property Profile fields

## [0.1.3] - 2026-03-18

### Added
- Property Profile admin page (identity, address, contact, geo, social)

### Fixed
- Admin menu spacing under Portico Webworks

## [0.1.2] - 2026-03-18

### Changed
- Admin menu icon: use Dashicons hotel/building icon

## [0.1.1] - 2026-03-18

### Added
- Plugin logo: admin menu icon + page header logo

## [0.1.0] - 2026-03-18

### Added
- Admin menu: “Portico Webworks” with “Settings” page

