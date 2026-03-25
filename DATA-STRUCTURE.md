# Portico Webworks Plugin — Post Type Data Structure

## Overview

The plugin registers **1 primary post type** (`pw_property`) and **13 child post types**. Child CPTs (except `pw_contact`) are registered in `includes/child-post-types.php`; **`pw_contact`** is registered in `includes/contact-post-type.php`. Child CPTs are linked to a property via a `_pw_property_id` meta field (or `_pw_connected_to` / `_pw_parents` where applicable). All CPTs use `show_in_rest: true`.

Admin UI uses **CMB2** for most child-CPT meta boxes and **custom metaboxes** for the property profile (`includes/property-profile.php`). **`pw_contact`** uses CMB2 from `includes/contact-metabox.php`, which also registers its `register_post_meta` keys. CMB2 is bundled in `vendor/cmb2/` and its top-level admin menu is suppressed via the `cmb2_menus` filter. CPT submenus under **Portico Webworks** are registered manually in `includes/admin-page.php` (auto-generated CPT submenus are removed in `includes/child-post-types.php`).

**Plugin settings UI:** **Portico Webworks → General** is **not** a CMB2 options page. A custom `<form>` posts to `admin-post.php` with `action=pw_save_settings`, nonce `pw_save_settings` / `pw_settings_nonce`, and `pw_handle_settings_save()` in `includes/admin-page.php` updates `pw_settings`. **Property URL base, dynamic base source, slug source, and sub-paths** are on **Portico Webworks → Permalinks** (`includes/admin-permalinks.php`, `admin_post_pw_save_permalinks`).

**`pw_property` viewability:** In single-property mode the post type is not publicly queryable, but an `is_post_type_viewable` filter (`includes/property-post-type.php`) still returns true so the block editor and builders (e.g. GenerateBlocks) can resolve the type.

**Installer-managed Fact Sheet page:** `pw_get_required_pages()` in `includes/page-installer.php` requires one WordPress **page** per scope: requested slug `PW_FACT_SHEET_PAGE_SLUG` (`fact-sheet`), `_pw_generated` = `1`, `_pw_static_url_segment` = `fact-sheet` (URL segment for `property-rewrites.php` when WordPress uniquifies `post_name`), `_pw_property_id` = `0` in **single** mode or the property ID in **multi** mode. On first create only, `post_content` is filled from `gb-pro-markup-samples.html` (`pw_get_fact_sheet_starter_markup()`); existing pages are never overwritten. **Install Missing Structure** / publishing a property runs `pw_run_page_installer()` idempotently.

### Sample data marker (internal)

| Key                    | Scope        | Notes                                                                 |
| ---------------------- | ------------ | --------------------------------------------------------------------- |
| `_pw_is_sample_data`   | Post meta    | Constant `PW_IS_SAMPLE_DATA_META_KEY`. Set to `1` on content created by the Sample Data installer. Registered for all plugin CPTs, `post`, and `page`. **REST:** `show_in_rest: true`; `auth_callback` requires `edit_post` on that post. |
| `_pw_is_sample_data`   | Term meta    | Same key constant. Set when the installer **creates** a term (existing terms are not tagged). Registered for plugin taxonomies, `category`, and `post_tag`. **REST:** `show_in_rest: true`; `auth_callback` requires `edit_term`. |

**Sample data pack (releases):** The multi-property dataset and demo images live in `sample-data-pack/` in the repository. The main plugin release ZIP **excludes** that folder to keep the download small. The matching GitHub release also publishes **`portico_webworks_plugin-sample-data.zip`**; `manifest.json` inside the pack must match `PW_VERSION`. **Portico Webworks → Data → Sample content** downloads the ZIP (HTTPS URL, default built from the **Update** tab GitHub releases URL or `PW_SAMPLE_DATA_GITHUB_OWNER` / `PW_SAMPLE_DATA_GITHUB_REPO` in `portico_webworks_plugin.php`), extracts it under uploads, loads `bootstrap.php`, and runs the installer. A full git checkout includes `sample-data-pack/` locally so no download is required in development. Successful install flushes rewrite rules and refreshes **Portico primary** nav custom-link URLs. **Remove sample data** also deletes attachments referenced by `_thumbnail_id`, `_pw_gallery`, and `_pw_og_image` on flagged posts.

---

## Post Type: `pw_property`

**REST base:** `pw-properties`  
**Supports:** `title`, `editor`, `excerpt`, `thumbnail`, `revisions`, `custom-fields`, `slug`  
**Taxonomies:** `pw_property_type`  
**Public / queryable / rewrite** (`includes/property-post-type.php`): **`public`** is always **true** (REST `permalink_template` and block editor slug). **`publicly_queryable`**, **`show_in_nav_menus`**, **`rewrite`**, and **`query_var`** are on only in **multi** mode. In multi, **`rewrite`** is `[ 'slug' => '', 'with_front' => false ]` so the permastruct is `/%pw_property%/` (root property URL = `post_name`). **Single** mode keeps `rewrite` / `query_var` off (no public singular URL). Tiered section and property-scoped page rules stay in `includes/property-rewrites.php`. **`is_post_type_viewable`** is still filtered to **true** for `pw_property` in single mode so builders resolve the type.

### Meta Fields

Scalar profile fields below are registered in `includes/property-post-type.php`. Repeatable / structured groups for `pw_property` (`_pw_gallery`, `_pw_pools`, `_pw_direct_benefits`, `_pw_certifications`, sustainability and accessibility facet arrays) are registered in `pw_register_child_post_meta()` in `includes/child-post-types.php`.

#### General


| Meta Key               | Type    | Default | Notes                      | CMB2                                        |
| ---------------------- | ------- | ------- | -------------------------- | ------------------------------------------- |
| `_pw_legal_name`       | string  | `''`    | For invoices / compliance  | Custom metabox (Property Profile → General) |
| `_pw_star_rating`      | integer | `0`     | 1–5 classification         | Custom metabox                              |
| `_pw_currency`         | string  | `'USD'` | ISO 4217 currency code     | Custom metabox (select)                     |
| `_pw_check_in_time`    | string  | `''`    | e.g. `14:00`               | Custom metabox                              |
| `_pw_check_out_time`   | string  | `''`    | e.g. `11:00`               | Custom metabox                              |
| `_pw_year_established` | integer | `0`     | schema.org LodgingBusiness | Custom metabox                              |
| `_pw_total_rooms`      | integer | `0`     | Total inventory count      | Custom metabox                              |
| `_pw_og_image`         | integer | `0`     | Open Graph image attachment ID (optional) | `register_post_meta` in `property-post-type.php` |
| `_pw_announcement_active` | boolean | `false` | Whether the property announcement bar is enabled | `register_post_meta` in `property-post-type.php`; custom CMB2 metabox |
| `_pw_announcement_text`   | string  | `''`    | Announcement bar body text (rendered as HTML)   | `register_post_meta` in `property-post-type.php`; custom CMB2 metabox |
| `_pw_announcement_start`  | string  | `''`    | Optional start datetime (parsable by `strtotime`) | `register_post_meta` in `property-post-type.php`; custom CMB2 metabox |
| `_pw_announcement_end`    | string  | `''`    | Optional end datetime (parsable by `strtotime`)   | `register_post_meta` in `property-post-type.php`; custom CMB2 metabox |
| `_pw_url_slug`         | string  | `''`    | Optional segment when Permalinks uses custom slug mode (`pw_get_permalink_slug_source()`). `sanitize_title`; unique among published properties. Field appears in General only when that mode is on. | Custom metabox (`property-profile.php`) |


> **Note:** Property profile fields (General, Address, Geo, Social) use custom metaboxes in `property-profile.php`, not CMB2. Permalink behaviour (base, slug source, sub-paths) is configured under **Portico Webworks → Permalinks**.

#### Address


| Meta Key             | Type   | Default | Notes                           | CMB2                                        |
| -------------------- | ------ | ------- | ------------------------------- | ------------------------------------------- |
| `_pw_address_line_1` | string | `''`    |                                 | Custom metabox (Property Profile → Address) |
| `_pw_address_line_2` | string | `''`    |                                 | Custom metabox                              |
| `_pw_city`           | string | `''`    |                                 | Custom metabox                              |
| `_pw_state`          | string | `''`    | State or province               | Custom metabox                              |
| `_pw_postal_code`    | string | `''`    |                                 | Custom metabox                              |
| `_pw_country`        | string | `''`    | Full country name               | Custom metabox                              |
| `_pw_country_code`   | string | `''`    | ISO 3166-1 alpha-2 (schema.org) | Custom metabox                              |


#### Contacts

Contact rows are **not** stored on the property. Use the `pw_contact` CPT and `pw_resolve_contact()` (see `includes/contact-resolver.php` and the **`pw_contact`** section below). `pw_get_property_profile()['contacts']` returns resolved property-scoped contacts for the given property ID.

#### Geo


| Meta Key              | Type   | Default | Notes                      | CMB2                                    |
| --------------------- | ------ | ------- | -------------------------- | --------------------------------------- |
| `_pw_lat`             | number | `0`     |                            | Custom metabox (Property Profile → Geo) |
| `_pw_lng`             | number | `0`     |                            | Custom metabox                          |
| `_pw_google_place_id` | string | `''`    | Google Maps Place ID       | Custom metabox                          |
| `_pw_timezone`        | string | `''`    | IANA (e.g. `Asia/Kolkata`) | Custom metabox (select)                 |


#### Social


| Meta Key                 | Type   | Default | CMB2                                       |
| ------------------------ | ------ | ------- | ------------------------------------------ |
| `_pw_social_facebook`    | string | `''`    | Custom metabox (Property Profile → Social) |
| `_pw_social_instagram`   | string | `''`    | Custom metabox                             |
| `_pw_social_twitter`     | string | `''`    | Custom metabox                             |
| `_pw_social_youtube`     | string | `''`    | Custom metabox                             |
| `_pw_social_linkedin`    | string | `''`    | Custom metabox                             |
| `_pw_social_tripadvisor` | string | `''`    | Custom metabox                             |


#### Gallery (`_pw_gallery`)

| Meta Key      | Type  | Default | Notes                                                              | Admin / UI                  |
| ------------- | ----- | ------- | ------------------------------------------------------------------ | --------------------------- |
| `_pw_gallery` | array | —       | Attachment IDs (CMB2 `file_list` may store `attachment_id => url`). Caption, description, and alt text live on each attachment in the Media Library. | CMB2: `pw_property_gallery` |


#### Pools (`_pw_pools`) — repeatable group


| Field         | Type    | Notes                     | CMB2                      |
| ------------- | ------- | ------------------------- | ------------------------- |
| `name`        | string  | e.g. Main Pool, Kids Pool | CMB2: `pw_property_pools` |
| `length_m`    | number  |                           |                           |
| `width_m`     | number  |                           |                           |
| `depth_m`     | number  |                           |                           |
| `open_time`   | string  | e.g. `07:00`              | text_time                 |
| `close_time`  | string  | e.g. `22:00`              | text_time                 |
| `is_heated`   | boolean |                           | checkbox                  |
| `is_kids`     | boolean |                           | checkbox                  |
| `is_indoor`   | boolean |                           | checkbox                  |
| `is_infinity` | boolean |                           | checkbox                  |
| `attachment_id` | integer | `0`                     | Optional pool image (media library) | CMB2 `file` in `pw_property_pools` |


#### Direct Booking Benefits (`_pw_direct_benefits`) — repeatable group


| Field         | Type   | CMB2                                |
| ------------- | ------ | ----------------------------------- |
| `title`       | string | CMB2: `pw_property_direct_benefits` |
| `description` | string |                                     |
| `icon`        | string | Icon slug or SVG                    |


#### Sustainability (`_pw_sustainability_items`) — repeatable group

Meta key constant: `PW_SUSTAINABILITY_ITEMS_META_KEY` → `_pw_sustainability_items` (`includes/property-facet-definitions.php`).

Ordered array of rows (one entry per canonical practice). CMB2: `pw_property_sustainability`. Canonical keys and labels are defined in `includes/property-facet-definitions.php` (`pw_get_sustainability_facet_definitions()`). On add/update of this meta key, `pw_normalize_property_facet_meta_if_dirty()` rewrites storage to one row per definition in definition order (`unknown` status where missing); unknown keys are dropped.


| Field    | Type   | Notes                                                                 |
| -------- | ------ | --------------------------------------------------------------------- |
| `key`    | string | Stable slug (e.g. `solar_power`, `recycling_program`)                 |
| `status` | string | `unknown` \| `available` \| `not_available`                          |
| `note`   | string | Optional detail (admin label **Content**; textarea)                   |


#### Certifications & Awards (`_pw_certifications`) — repeatable group


| Field    | Type    | Notes                                 | CMB2                               |
| -------- | ------- | ------------------------------------- | ---------------------------------- |
| `name`   | string  | e.g. Green Key, Forbes Travel Guide   | CMB2: `pw_property_certifications` |
| `issuer` | string  | Organisation that issued              |                                    |
| `year`   | integer | Year awarded or renewed (REST schema) | text_small                         |
| `url`    | string  | Link to certificate                   |                                    |


#### Accessibility (`_pw_accessibility_items`) — repeatable group

Meta key constant: `PW_ACCESSIBILITY_ITEMS_META_KEY` → `_pw_accessibility_items`.

Same shape and normalization behavior as sustainability. CMB2: `pw_property_accessibility`. Definitions: `pw_get_accessibility_facet_definitions()` in `includes/property-facet-definitions.php`.


| Field    | Type   | Notes                                                                 |
| -------- | ------ | --------------------------------------------------------------------- |
| `key`    | string | Stable slug (e.g. `wheelchair_accessible`, `elevator`)                |
| `status` | string | `unknown` \| `available` \| `not_available`                          |
| `note`   | string | Optional detail (admin label **Content**; textarea)                   |


---

## Post Type: `pw_feature`

**Supports:** `title`, `custom-fields`  
**Taxonomies:** `pw_feature_group`


| Meta Key      | Type   | Default | CMB2                                                                  |
| ------------- | ------ | ------- | --------------------------------------------------------------------- |
| `_pw_icon`    | string | `''`    | CMB2: `pw_feature_metabox` (textarea_small) — SVG string or icon slug |
| `_pw_content` | string | `''`    | textarea                                                              |


---

## Post Type: `pw_room_type`

**Supports:** `title`, `editor`, `excerpt`, `thumbnail`, `custom-fields`, `slug`  
**Taxonomies:** `pw_bed_type`, `pw_view_type`


| Meta Key             | Type         | Default | Notes                                     | CMB2                                  |
| -------------------- | ------------ | ------- | ----------------------------------------- | ------------------------------------- |
| `_pw_property_id`    | integer      | `0`     | FK → `pw_property`                        | CMB2: `pw_room_type_metabox` (select) |
| `_pw_rate_from`      | number       | `0`     | Summary starting rate (optional; use `_pw_rates` for schema Offers) | text_money                            |
| `_pw_rate_to`        | number       | `0`     | Summary upper rate                        | text_money                            |
| `_pw_rates`          | array        | `[]`    | Repeatable rate plans → multiple schema.org `Offer` rows. Each row: `rate_label` (string), `rate_type` (`rack` \| `seasonal` \| `advance` \| `package`), `price` (number), `valid_from` / `valid_to` (`Y-m-d` or empty), `advance_days` (int), `includes_breakfast` (bool) | CMB2 group (repeatable)               |
| `_pw_max_occupancy`  | integer      | `0`     | Total guest limit                         | text_small                            |
| `_pw_max_adults`     | integer      | `0`     | max_adults + max_children ≤ max_occupancy | text_small                            |
| `_pw_max_children`   | integer      | `0`     | max_adults + max_children ≤ max_occupancy | text_small                            |
| `_pw_size_sqft`      | integer      | `0`     |                                           | text_small                            |
| `_pw_size_sqm`       | integer      | `0`     |                                           | text_small                            |
| `_pw_max_extra_beds` | integer      | `0`     |                                           | text_small                            |
| `_pw_display_order`  | integer      | `0`     |                                           | text_small                            |
| `_pw_features`       | arrayinteger | —       | Array of `pw_feature` post IDs            | multicheck                            |
| `_pw_gallery`        | arrayinteger | —       | Attachment IDs (per-image caption/alt on attachments) | file_list                             |


**Validation:** Admin UI enforces `max_adults + max_children ≤ max_occupancy` on save.

---

## Post Type: `pw_restaurant`

**Supports:** `title`, `editor`, `excerpt`, `thumbnail`, `custom-fields`, `slug`  
**Taxonomies:** `pw_meal_period`


| Meta Key               | Type         | Default | Notes                          | CMB2                                   |
| ---------------------- | ------------ | ------- | ------------------------------ | -------------------------------------- |
| `_pw_property_id`      | integer      | `0`     |                                | CMB2: `pw_restaurant_metabox` (select) |
| `_pw_location`         | string       | `''`    | e.g. Rooftop Level, Beach Side | text                                   |
| `_pw_cuisine_type`     | string       | `''`    |                                | text                                   |
| `_pw_seating_capacity` | integer      | `0`     |                                | text_small                             |
| `_pw_reservation_url`  | string       | `''`    |                                | text_url                               |
| `_pw_menu_url`         | string       | `''`    |                                | text_url                               |
| `_pw_gallery`          | arrayinteger | —       | Attachment IDs (caption/alt on attachments) | file_list                              |
| `_pw_operating_hours`  | array        | `[]`    | Same hours every day: repeatable rows `{ label, open_time, close_time }` (strings). REST: array of objects. | CMB2: `pw_restaurant_operating_hours` (repeatable group) |


---

## Post Type: `pw_spa`

**Supports:** `title`, `editor`, `excerpt`, `thumbnail`, `custom-fields`, `slug`  
**Taxonomies:** `pw_treatment_type`


| Meta Key                        | Type         | Default | CMB2                            |
| ------------------------------- | ------------ | ------- | ------------------------------- |
| `_pw_property_id`               | integer      | `0`     | CMB2: `pw_spa_metabox` (select) |
| `_pw_booking_url`               | string       | `''`    | text_url                        |
| `_pw_menu_url`                  | string       | `''`    | text_url                        |
| `_pw_min_age`                   | integer      | `0`     | text_small                      |
| `_pw_number_of_treatment_rooms` | integer      | `0`     | text_small                      |
| `_pw_gallery`                   | arrayinteger | —       | file_list (caption/alt on attachments) |
| `_pw_operating_hours`           | array        | `[]`    | Same as `pw_restaurant`: repeatable `{ label, open_time, close_time }`. | CMB2: `pw_spa_operating_hours` (repeatable group) |


---

## Post Type: `pw_meeting_room`

**Supports:** `title`, `editor`, `excerpt`, `thumbnail`, `custom-fields`, `slug`  
**Taxonomies:** `pw_av_equipment`


| Meta Key                    | Type         | Default | Notes                | CMB2                                     |
| --------------------------- | ------------ | ------- | -------------------- | ---------------------------------------- |
| `_pw_property_id`           | integer      | `0`     |                      | CMB2: `pw_meeting_room_metabox` (select) |
| `_pw_capacity_theatre`      | integer      | `0`     |                      | text_small                               |
| `_pw_capacity_classroom`    | integer      | `0`     |                      | text_small                               |
| `_pw_capacity_boardroom`    | integer      | `0`     |                      | text_small                               |
| `_pw_capacity_ushape`       | integer      | `0`     |                      | text_small                               |
| `_pw_area_sqft`             | integer      | `0`     |                      | text_small                               |
| `_pw_area_sqm`              | integer      | `0`     |                      | text_small                               |
| `_pw_prefunction_area_sqft` | integer      | `0`     |                      | text_small                               |
| `_pw_prefunction_area_sqm`  | integer      | `0`     |                      | text_small                               |
| `_pw_natural_light`         | boolean      | `false` |                      | checkbox                                 |
| `_pw_floor_plan`            | integer      | `0`     | Attachment ID        | file                                     |
| `_pw_gallery`               | arrayinteger | —       | Attachment IDs (caption/alt on attachments) | file_list                                |


---

## Post Type: `pw_amenity`

**Supports:** `title`, `custom-fields`


| Meta Key               | Type    | Default | Notes                              | CMB2                                |
| ---------------------- | ------- | ------- | ---------------------------------- | ----------------------------------- |
| `_pw_property_id`      | integer | `0`     |                                    | CMB2: `pw_amenity_metabox` (select) |
| `_pw_type`             | string  | `''`    | `amenity` | `service` | `facility` | select                              |
| `_pw_category`         | string  | `''`    |                                    | text                                |
| `_pw_icon`             | string  | `''`    |                                    | textarea_small                      |
| `_pw_content`          | string  | `''`    |                                    | textarea_small                      |
| `_pw_is_complimentary` | boolean | `false` |                                    | checkbox                            |
| `_pw_display_order`    | integer | `0`     |                                    | text_small                          |


---

## Post Type: `pw_contact`

**REST base:** `pw-contacts`  
**Supports:** `title`, `custom-fields`  
**Public:** inherits child defaults with `public` / `show_ui` as for other child CPTs; **`publicly_queryable`:** `false`; **`show_in_menu`:** `false` (list/add screens linked from **Portico Webworks → Contacts** in `admin-page.php`).

Scoped contact cards for a property: outlet-specific, group-level per CPT, or property-wide fallback. **Read paths** must use `pw_resolve_contact()` / `pw_resolve_primary_contact()` — see `includes/contact-resolver.php`. When an outlet post (`pw_restaurant`, `pw_spa`, `pw_meeting_room`, `pw_experience`) is deleted, `pw_contact_handle_outlet_deleted()` in `contact-post-type.php` clears matching `_pw_scope_id` and prefixes the label with `[Unlinked] ` where needed.

| Meta Key           | Type    | Default    | Notes                                                                 | CMB2                          |
| ------------------ | ------- | ---------- | --------------------------------------------------------------------- | ----------------------------- |
| `_pw_property_id`  | integer | `0`        | Required FK → `pw_property`                                           | `pw_contact_metabox` (select) |
| `_pw_label`        | string  | `''`       | Display label (e.g. Reservations)                                     | text                          |
| `_pw_phone`        | string  | `''`       |                                                                       | text_small                    |
| `_pw_mobile`       | string  | `''`       |                                                                       | text_small                    |
| `_pw_whatsapp`     | string  | `''`       |                                                                       | text_small                    |
| `_pw_email`        | string  | `''`       |                                                                       | text_email                    |
| `_pw_scope_cpt`    | string  | `property` | One of `PW_CONTACT_SCOPE_CPTS` (`includes/contact-resolver.php`): `property`, `restaurant`, `spa`, `meeting_room`, `experience`, `all` | select                        |
| `_pw_scope_id`     | integer | `0`        | Outlet post ID or `0` for group-level                                 | select (JS-filled)            |

**REST (custom):** `GET /wp-json/pw/v1/contacts?property_id=&scope_cpt=&scope_id=` — resolved contacts (`edit_posts`). `GET /wp-json/pw/v1/contact-scope-posts?property_id=&post_type=` — outlet picker for admin JS.

---

## Post Type: `pw_policy`

**Supports:** `title`, `custom-fields`  
**Taxonomies:** `pw_policy_type` (checkin, checkout, cancellation, pet, child, payment, smoking, custom)


| Meta Key             | Type    | Default | Notes            | CMB2                               |
| -------------------- | ------- | ------- | ---------------- | ---------------------------------- |
| `_pw_property_id`    | integer | `0`     | FK → pw_property | CMB2: `pw_policy_metabox` (select) |
| `_pw_content`        | string  | `''`    | Policy body text | textarea                           |
| `_pw_display_order`  | integer | `0`     |                  | text_small                         |
| `_pw_is_highlighted` | boolean | `false` |                  | checkbox                           |
| `_pw_active`         | boolean | `true`  |                  | checkbox                           |


Policy type is set via taxonomy `pw_policy_type`. Post title is the policy title.

---

## Post Type: `pw_faq`

**Supports:** `title`, `custom-fields`

For property-scoped FAQ lists (e.g. FAQPage in multi-property mode), query `meta_key` `_pw_property_id` instead of scanning `_pw_connected_to`. `pw_get_faqs_for( 'pw_property', $id )` also treats a matching `_pw_property_id` as a hit, and still honors legacy rows that only use `_pw_connected_to`.


| Meta Key            | Type    | Default | CMB2                             |
| ------------------- | ------- | ------- | -------------------------------- |
| `_pw_property_id`   | integer | `0`     | FK → `pw_property`; scopes FAQ for multi-property / FAQPage queries (alongside connections below) | select |
| `_pw_answer`        | string  | `''`    | CMB2: `pw_faq_metabox` (wysiwyg) |
| `_pw_display_order` | integer | `0`     | text_small                       |


#### Connected To (`_pw_connected_to`) — repeatable group


| Field  | Type    | Notes                                                          | CMB2                               |
| ------ | ------- | -------------------------------------------------------------- | ---------------------------------- |
| `type` | string  | `pw_property` | `pw_restaurant` | `pw_meeting_room` | `pw_spa` | select                             |
| `id`   | integer | Post ID of connected entity                                    | select (pw_faq_connection_options) |


---

## Post Type: `pw_offer`

**Supports:** `title`, `editor`, `excerpt`, `thumbnail`, `custom-fields`, `slug`

For property-scoped offer lists, query `meta_key` `_pw_property_id`. `_pw_parents` remains the multi-entity attach list.

| Meta Key                  | Type         | Default       | Notes                                                           | CMB2                              |
| ------------------------- | ------------ | ------------- | --------------------------------------------------------------- | --------------------------------- |
| `_pw_offer_type`          | string       | `'promotion'` | `promotion` | `package` | `direct_booking_benefit`              | CMB2: `pw_offer_metabox` (select) |
| `_pw_property_id`         | integer      | `0`           | FK → `pw_property`; flat scope for multi-property / REST        | select                            |
| `_pw_parents`             | array        | —             | Repeatable: `type`, `id` → pw_property | pw_restaurant | pw_spa | group                             |
| `_pw_valid_from`          | string       | `''`          | Date `Y-m-d`                                                    | text_date                         |
| `_pw_valid_to`            | string       | `''`          | Date `Y-m-d`                                                    | text_date                         |
| `_pw_booking_url`         | string       | `''`          |                                                                 | text_url                          |
| `_pw_is_featured`         | boolean      | `false`       |                                                                 | checkbox                          |
| `_pw_discount_type`       | string       | `''`          | `percentage` | `flat` | `value_add`                             | select                            |
| `_pw_discount_value`      | number       | `0`           | Conditional (hidden if value_add)                               | text_money                        |
| `_pw_minimum_stay_nights` | integer      | `0`           | Conditional (promotion/package only)                            | text_small                        |
| `_pw_room_types`          | arrayinteger | —             | `pw_room_type` post IDs                                         | multicheck                        |
| `_pw_display_order`       | integer      | `0`           |                                                                 | text_small                        |


Description/terms: use post `editor` and `excerpt` as needed.

---

## Post Type: `pw_nearby`

**Supports:** `title`, `editor`, `excerpt`, `thumbnail`, `custom-fields`, `slug`  
**Taxonomies:** `pw_nearby_type`, `pw_transport_mode`


| Meta Key              | Type    | Default | CMB2                               |
| --------------------- | ------- | ------- | ---------------------------------- |
| `_pw_property_id`     | integer | `0`     | CMB2: `pw_nearby_metabox` (select) |
| `_pw_distance_km`     | number  | `0`     | text_small                         |
| `_pw_travel_time_min` | integer | `0`     | text_small                         |
| `_pw_lat`             | number  | `0`     | WGS84; schema.org `geo` / map rich results (`0` = unset) |
| `_pw_lng`             | number  | `0`     | WGS84                                |
| `_pw_place_url`       | string  | `''`    | Google Maps or website URL         |
| `_pw_display_order`   | integer | `0`     | text_small                         |


---

## Post Type: `pw_experience`

**Supports:** `title`, `editor`, `excerpt`, `thumbnail`, `custom-fields`, `slug`  
**Taxonomies:** `pw_experience_category`

For property-level experience archives, query `_pw_property_id`. `pw_get_experiences_for( 'pw_property', $id )` also matches that meta and still honors `_pw_connected_to`-only legacy rows.

| Meta Key               | Type         | Default | Notes                                                           | CMB2                                  |
| ---------------------- | ------------ | ------- | --------------------------------------------------------------- | ------------------------------------- |
| `_pw_property_id`      | integer      | `0`     | FK → `pw_property`                                              | select                                |
| `_pw_connected_to`     | array        | —       | Repeatable: `type`, `id` → pw_property | pw_restaurant | pw_spa | CMB2: `pw_experience_metabox` (group) |
| `_pw_description`      | string       | `''`    |                                                                 | textarea                              |
| `_pw_duration_hours`   | number       | `0`     |                                                                 | text_small                            |
| `_pw_price_from`       | number       | `0`     |                                                                 | text_money                            |
| `_pw_booking_url`      | string       | `''`    |                                                                 | text_url                              |
| `_pw_is_complimentary` | boolean      | `false` |                                                                 | checkbox                              |
| `_pw_gallery`          | arrayinteger | —       | Attachment IDs (caption/alt on attachments)                     | file_list                             |
| `_pw_display_order`    | integer      | `0`     |                                                                 | text_small                            |


---

## Post Type: `pw_event`

**Supports:** `title`, `editor`, `excerpt`, `thumbnail`, `custom-fields`, `slug`  
**Taxonomies:** `pw_event_type`, `pw_event_organiser`


| Meta Key                    | Type         | Default                        | Notes                                                                             | CMB2                              |
| --------------------------- | ------------ | ------------------------------ | --------------------------------------------------------------------------------- | --------------------------------- |
| `_pw_property_id`           | integer      | `0`                            |                                                                                   | CMB2: `pw_event_metabox` (select) |
| `_pw_venue_id`              | integer      | `0`                            | FK → `pw_meeting_room`                                                            | select (pw_meeting_room_options)  |
| `_pw_description`           | string       | `''`                           |                                                                                   | textarea                          |
| `_pw_start_datetime`        | string       | `''`                           | Wall time `Y-m-d H:i:s` (no TZ in DB). **Convention:** interpret in `pw_property._pw_timezone` for schema.org (`pw_event_local_datetime_to_iso8601()`). | text_datetime_timestamp_timezone  |
| `_pw_end_datetime`          | string       | `''`                           | Same as start.                                                                  | text_datetime_timestamp_timezone  |
| `_pw_capacity`              | integer      | `0`                            |                                                                                   | text_small                        |
| `_pw_price_from`            | number       | `0`                            |                                                                                   | text_money                        |
| `_pw_booking_url`           | string       | `''`                           |                                                                                   | text_url                          |
| `_pw_recurrence_rule`       | string       | `''`                           | iCal RRULE (e.g. `FREQ=WEEKLY;BYDAY=SA`)                                          | pw_rrule (custom field)           |
| `_pw_event_status`          | string       | `'EventScheduled'`             | schema.org: EventScheduled | EventCancelled | EventPostponed | EventRescheduled   | select                            |
| `_pw_event_attendance_mode` | string       | `'OfflineEventAttendanceMode'` | OfflineEventAttendanceMode | OnlineEventAttendanceMode | MixedEventAttendanceMode | select                            |
| `_pw_start_datetime_iso8601` | string      | `''`                           | Synced on `save_post_pw_event` from `_pw_start_datetime` via `pw_event_local_datetime_to_iso8601()` (property timezone). | *(synced, not CMB2)*              |
| `_pw_end_datetime_iso8601`   | string      | `''`                           | Same pattern for end datetime. | *(synced, not CMB2)*              |
| `_pw_gallery`               | arrayinteger | —                              | Attachment IDs (caption/alt on attachments)                                       | file_list                         |

**Organiser:** Use taxonomy `pw_event_organiser` (term name = organiser name). Term meta `organiser_url` stores the URL. Used for schema.org Event markup.

---

## Virtual meta (backward compatibility reads)

`includes/backward-compat.php` filters `get_post_metadata` so **reads** of legacy keys resolve without storing extra rows:

| Post type    | Meta key              | Resolved from |
| ------------ | --------------------- | ------------- |
| `pw_property` | `_pw_property_name` | Post title |
| `pw_property` | `_pw_slug`          | Post `post_name` |
| `pw_event`   | `_pw_is_recurring`   | `'1'` if `_pw_recurrence_rule` is non-empty, else empty |

In **admin**, for `pw_event` only, `_pw_start_datetime` / `_pw_end_datetime` may be surfaced to CMB2 as JSON when the stored value is `Y-m-d H:i:s` (timezone from linked property `_pw_timezone`).

---

## Taxonomies


| Taxonomy                 | Post Type         | Label                 | Term Meta                |
| ------------------------ | ----------------- | --------------------- | ------------------------ |
| `pw_bed_type`            | `pw_room_type`    | Bed Types             | —                        |
| `pw_view_type`           | `pw_room_type`    | View Types            | —                        |
| `pw_meal_period`         | `pw_restaurant`   | Meal Periods          | —                        |
| `pw_treatment_type`      | `pw_spa`          | Treatment Types       | —                        |
| `pw_av_equipment`        | `pw_meeting_room` | AV Equipment          | —                        |
| `pw_feature_group`       | `pw_feature`      | Feature Groups        | —                        |
| `pw_nearby_type`         | `pw_nearby`       | Location Types        | —                        |
| `pw_transport_mode`      | `pw_nearby`       | Transport Modes       | —                        |
| `pw_experience_category` | `pw_experience`   | Experience Categories | —                        |
| `pw_event_type`          | `pw_event`        | Event Types           | —                        |
| `pw_policy_type`         | `pw_policy`       | Policy Types          | —                        |
| `pw_event_organiser`     | `pw_event`        | Organisers            | `organiser_url` (string) |
| `pw_property_type`       | `pw_property`     | Property Types        | —                        |


All taxonomies: non-hierarchical, `show_in_rest: true`, `show_admin_column: true`, `rewrite: false`.

### Default taxonomy terms (seeding)

Canonical lists live in `includes/taxonomy-seeds.php` (`pw_get_taxonomy_seed_terms()`). `pw_seed_taxonomy_terms()` inserts each name only if `term_exists()` is false (nothing removed or renamed).

**Fresh activation:** `pw_apply_install_defaults()` (`portico_webworks_plugin.php`) sets option `pw_seed_taxonomies` to `1`. On `init` at priority `999`, the plugin runs `pw_seed_taxonomy_terms()`, deletes `pw_seed_taxonomies`, and sets `pw_taxonomy_seed_prompt_status` to `auto_completed`.

**Existing sites (upgrade):** If `pw_install_defaults_applied` is set and `pw_taxonomy_seed_prompt_status` is empty, `admin_init` sets it to `pending` and an admin notice offers **Add default terms** / **Dismiss** (`admin_post_pw_accept_taxonomy_seed` / `pw_dismiss_taxonomy_seed`). Accept runs the same seeder and sets status to `completed`. The Sample Data admin UI can re-run `pw_seed_taxonomy_terms()` via `admin_post_pw_reseed_taxonomies` (`includes/sample-data.php`).

**Taxonomies seeded (all optional names in code):** `pw_property_type`, `pw_policy_type`, `pw_bed_type`, `pw_view_type`, `pw_meal_period`, `pw_treatment_type`, `pw_av_equipment`, `pw_feature_group`, `pw_nearby_type`, `pw_transport_mode`, `pw_experience_category`, `pw_event_type`.

The **Sample Data** installer (`sample-data-pack/sample-data-multi-install.php`, loaded after the pack is fetched or from the local `sample-data-pack/` folder) merges in the full `pw_get_taxonomy_seed_terms()` arrays for `pw_property_type` and `pw_policy_type`, then ensures additional demo-only names; it assigns **Hotel** / **Resort** on the two demo properties. Canonical term tables and demo-term notes: `[TAXONOMY-SEED-VALUES.md](TAXONOMY-SEED-VALUES.md)`.

#### Not seeded automatically


| Taxonomy             | Reason                                                    |
| -------------------- | --------------------------------------------------------- |
| `pw_event_organiser` | Property-specific; organisers vary per property and event |


---

## Options Page: Portico Webworks Settings

**WordPress option (single row):** `pw_settings` — associative array. Permalink-related keys are written from either **General** or **Permalinks** saves; both merge from the existing option.

### General tab

**UI:** **Portico Webworks → General** (`includes/admin-page.php`). “Search Engine Indexing” is static copy with a link to **Settings → Reading** (`options-reading.php`). Form: `admin_url( 'admin-post.php' )`, `action=pw_save_settings`, `wp_nonce_field( 'pw_save_settings', 'pw_settings_nonce' )` → `pw_handle_settings_save()`.

**`pw_handle_settings_save()`** updates `pw_property_mode`, `pw_default_property_id` (single mode only; `0` in multi), `pw_github_releases_url`, and **removes** any legacy `pw_default_template` key from the stored array. **`flush_rewrite_rules()`** runs only when **`pw_property_mode`** changed vs. previous merged settings (permalink/base changes are saved on the Permalinks tab).

### Permalinks tab

**UI:** **Portico Webworks → Permalinks** (`includes/admin-permalinks.php`). Nonce `pw_save_permalinks` / `pw_permalinks_nonce`, handler `admin_post_pw_save_permalinks` → `pw_handle_save_permalinks()`.

**Saved keys:** `pw_permalink_base_source` (allowed values: `pw_permalink_base_source_allowed()` in `includes/permalink-config.php` — `fixed`, `_pw_city`, `_pw_state`, `_pw_country`, `_pw_country_code`, `pw_property_type`), `pw_permalink_base_fixed` (sanitized with `pw_sanitize_property_base()`), **`pw_property_base`** (set equal to the fixed base on save), `pw_permalink_slug_source` (`post_name` | `_pw_url_slug`), `pw_permalink_subpaths` (map of URL segment → published **page** `post_name`).

**Flush:** `flush_rewrite_rules()` when any of the above permalink fields change vs. stored values.

### Keys in `pw_settings` (effective model)

| Option key                  | Type   | Default / notes |
| --------------------------- | ------ | ---------------- |
| `pw_property_mode`          | string | `'single'` \| `'multi'` |
| `pw_default_property_id`   | int    | `0`; single mode: published `pw_property` only |
| `pw_github_releases_url`    | string | Sanitized with `pw_sanitize_github_releases_url()`; “Update from GitHub” (`includes/github-plugin-update.php`); asset `portico_webworks_plugin.zip` |
| `pw_permalink_base_source`  | string | Default `'fixed'`; see `permalink-config.php` |
| `pw_permalink_base_fixed`   | string | Synced with `pw_property_base` in `pw_get_merged_pw_settings()` |
| `pw_property_base`          | string | Legacy mirror of fixed base; kept in sync when merging |
| `pw_permalink_slug_source`  | string | Stored in DB; readable via `pw_get_permalink_slug_source()` |
| `pw_permalink_subpaths`     | array  | Segment → page slug strings |

### Read path

- **`pw_get_merged_pw_settings()`** (`includes/admin-page.php`): merges `get_option( 'pw_settings' )` with defaults for the keys above, normalizes `pw_permalink_base_fixed` / `pw_property_base`, and **omits** `pw_permalink_slug_source` from the **returned** array (the value remains in the option row). Use **`pw_get_permalink_slug_source()`** for the slug mode.
- **`pw_get_setting( $key, $default )`** returns a key from the merged array if present, else **`get_option( $key, $default )`** (so `pw_permalink_slug_source` still resolves after merge).

---

## Relationships Summary

```
pw_property (1)
  ├── pw_room_type       (_pw_property_id)
  │     └── pw_feature   (_pw_features[] → pw_feature IDs)
  ├── pw_restaurant      (_pw_property_id)
  ├── pw_spa             (_pw_property_id)
  ├── pw_meeting_room    (_pw_property_id)
  ├── pw_contact         (_pw_property_id; optional _pw_scope_id → outlet CPT)
  ├── pw_amenity         (_pw_property_id)
  ├── pw_policy          (_pw_property_id)
  ├── pw_nearby          (_pw_property_id)
  ├── pw_experience      (_pw_property_id; _pw_connected_to[] → pw_property | pw_restaurant | pw_spa)
  └── pw_event           (_pw_property_id, _pw_venue_id → pw_meeting_room)

pw_offer               (_pw_property_id; _pw_parents[] → pw_property | pw_restaurant | pw_spa)
pw_faq                 (_pw_property_id; _pw_connected_to[] → pw_property | pw_restaurant | pw_meeting_room | pw_spa)
```

