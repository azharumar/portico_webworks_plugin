# Changelog

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

