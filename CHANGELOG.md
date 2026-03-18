# Changelog

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

