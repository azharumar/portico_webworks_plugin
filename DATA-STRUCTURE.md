# Portico Webworks Plugin — Post Type Data Structure

## Overview

The plugin registers **1 primary post type** (`pw_property`) and **12 child post types**. Child CPTs are linked to a property via a `_pw_property_id` meta field (or `_pw_connected_to` / `_pw_parents` where applicable). All CPTs expose data via the REST API (`show_in_rest: true`).

Admin UI is built with **CMB2** (meta boxes) and custom metaboxes (property profile). CMB2 is bundled in `vendor/cmb2/` and its admin menu is suppressed via `cmb2_menus` filter.

### Sample data marker (internal)

| Key                    | Scope        | Notes                                                                 |
| ---------------------- | ------------ | --------------------------------------------------------------------- |
| `_pw_is_sample_data`   | Post meta    | Set to `1` on content created by the Sample Data installer; not in REST; not editable via REST (`auth_callback` false). Registered for all plugin CPTs, `post`, and `page`. |
| `_pw_is_sample_data`   | Term meta    | Set when the installer **creates** a term (existing terms are not tagged). Registered for plugin taxonomies, `category`, and `post_tag`. |

---

## Post Type: `pw_property`

**REST base:** `pw-properties`  
**Supports:** `title`, `editor`, `excerpt`, `thumbnail`, `revisions`, `custom-fields`  
**Public:** mode-dependent (`pw_settings['pw_property_mode']`, read via `pw_get_setting()`: `single` | `multi`)

### Meta Fields

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


> **Note:** The default front-end template is a plugin-level setting (Portico Webworks → **General** → Default Template, stored in `pw_settings`). Property profile fields (General, Address, Geo, Social) use custom metaboxes in `property-profile.php`, not CMB2.

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


#### Contacts (`_pw_contacts`) — repeatable group


| Field      | Type   | Notes                           | CMB2                         |
| ---------- | ------ | ------------------------------- | ---------------------------- |
| `label`    | string | e.g. Hotel, Reservations, Sales | CMB2: `pw_property_contacts` |
| `phone`    | string |                                 |                              |
| `mobile`   | string |                                 |                              |
| `whatsapp` | string |                                 |                              |
| `email`    | string |                                 |                              |


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


#### SEO & Social Sharing


| Meta Key               | Type    | Default | Notes                        | CMB2                    |
| ---------------------- | ------- | ------- | ---------------------------- | ----------------------- |
| `_pw_meta_title`       | string  | `''`    | Overrides post title         | CMB2: `pw_property_seo` |
| `_pw_meta_description` | string  | `''`    | Overrides excerpt            | CMB2                    |
| `_pw_og_image`         | integer | `0`     | Attachment ID for Open Graph | CMB2 (file)             |


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


#### Direct Booking Benefits (`_pw_direct_benefits`) — repeatable group


| Field         | Type   | CMB2                                |
| ------------- | ------ | ----------------------------------- |
| `title`       | string | CMB2: `pw_property_direct_benefits` |
| `description` | string |                                     |
| `icon`        | string | Icon slug or SVG                    |


#### Sustainability (`_pw_sustainability_items`) — repeatable group

Ordered array of rows (one entry per canonical practice). CMB2: `pw_property_sustainability`. Canonical keys and labels are defined in `includes/property-facet-definitions.php` (`pw_get_sustainability_facet_definitions()`). On save, the list is normalized to that order; unknown keys are dropped.


| Field    | Type   | Notes                                                                 |
| -------- | ------ | --------------------------------------------------------------------- |
| `key`    | string | Stable slug (e.g. `solar_power`, `recycling_program`)                 |
| `status` | string | `unknown` \| `available` \| `not_available`                          |
| `note`   | string | Optional detail                                                       |


#### Certifications & Awards (`_pw_certifications`) — repeatable group


| Field    | Type    | Notes                                 | CMB2                               |
| -------- | ------- | ------------------------------------- | ---------------------------------- |
| `name`   | string  | e.g. Green Key, Forbes Travel Guide   | CMB2: `pw_property_certifications` |
| `issuer` | string  | Organisation that issued              |                                    |
| `year`   | integer | Year awarded or renewed (REST schema) | text_small                         |
| `url`    | string  | Link to certificate                   |                                    |


#### Accessibility (`_pw_accessibility_items`) — repeatable group

Same shape as sustainability. CMB2: `pw_property_accessibility`. Definitions: `pw_get_accessibility_facet_definitions()` in `includes/property-facet-definitions.php`.


| Field    | Type   | Notes                                                                 |
| -------- | ------ | --------------------------------------------------------------------- |
| `key`    | string | Stable slug (e.g. `wheelchair_accessible`, `elevator`)                |
| `status` | string | `unknown` \| `available` \| `not_available`                          |
| `note`   | string | Optional detail                                                       |


---

## Post Type: `pw_feature`

**Supports:** `title`, `custom-fields`  
**Taxonomies:** `pw_feature_group`


| Meta Key   | Type   | Default | CMB2                                                                  |
| ---------- | ------ | ------- | --------------------------------------------------------------------- |
| `_pw_icon` | string | `''`    | CMB2: `pw_feature_metabox` (textarea_small) — SVG string or icon slug |


---

## Post Type: `pw_room_type`

**Supports:** `title`, `editor`, `excerpt`, `thumbnail`, `custom-fields`  
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
| `_pw_gallery`        | arrayinteger | —       | Attachment IDs                            | file_list                             |


**Validation:** Admin UI enforces `max_adults + max_children ≤ max_occupancy` on save.

---

## Post Type: `pw_restaurant`

**Supports:** `title`, `editor`, `excerpt`, `thumbnail`, `custom-fields`  
**Taxonomies:** `pw_meal_period`


| Meta Key               | Type         | Default | Notes                          | CMB2                                   |
| ---------------------- | ------------ | ------- | ------------------------------ | -------------------------------------- |
| `_pw_property_id`      | integer      | `0`     |                                | CMB2: `pw_restaurant_metabox` (select) |
| `_pw_location`         | string       | `''`    | e.g. Rooftop Level, Beach Side | text                                   |
| `_pw_cuisine_type`     | string       | `''`    |                                | text                                   |
| `_pw_seating_capacity` | integer      | `0`     |                                | text_small                             |
| `_pw_reservation_url`  | string       | `''`    |                                | text_url                               |
| `_pw_menu_url`         | string       | `''`    |                                | text_url                               |
| `_pw_gallery`          | arrayinteger | —       |                                | file_list                              |


#### Operating Hours — per-day meta keys `_pw_hours_{day}` (e.g. `_pw_hours_monday`)

Each day stores an object: `{ is_closed: boolean, sessions: [{ label, open_time, close_time }] }`. CMB2: `pw_restaurant_operating_hours` (group per day, sessions repeatable).


| Field       | Type    | Notes                                          |
| ----------- | ------- | ---------------------------------------------- |
| `is_closed` | boolean | Closed all day                                 |
| `sessions`  | array   | Repeatable: `label`, `open_time`, `close_time` |


---

## Post Type: `pw_spa`

**Supports:** `title`, `editor`, `excerpt`, `thumbnail`, `custom-fields`  
**Taxonomies:** `pw_treatment_type`


| Meta Key                        | Type         | Default | CMB2                            |
| ------------------------------- | ------------ | ------- | ------------------------------- |
| `_pw_property_id`               | integer      | `0`     | CMB2: `pw_spa_metabox` (select) |
| `_pw_booking_url`               | string       | `''`    | text_url                        |
| `_pw_menu_url`                  | string       | `''`    | text_url                        |
| `_pw_min_age`                   | integer      | `0`     | text_small                      |
| `_pw_number_of_treatment_rooms` | integer      | `0`     | text_small                      |
| `_pw_gallery`                   | arrayinteger | —       | file_list                       |


#### Operating Hours — per-day `_pw_hours_{day}`

Same structure as restaurant: `{ is_closed, sessions }`. CMB2: `pw_spa_operating_hours`.

---

## Post Type: `pw_meeting_room`

**Supports:** `title`, `editor`, `excerpt`, `thumbnail`, `custom-fields`  
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
| `_pw_sales_phone`           | string       | `''`    | Direct venue contact | text_small                               |
| `_pw_sales_mobile`          | string       | `''`    |                      | text_small                               |
| `_pw_sales_whatsapp`        | string       | `''`    |                      | text_small                               |
| `_pw_sales_email`           | string       | `''`    |                      | text_small                               |
| `_pw_gallery`               | arrayinteger | —       |                      | file_list                                |


---

## Post Type: `pw_amenity`

**Supports:** `title`, `custom-fields`


| Meta Key               | Type    | Default | Notes                              | CMB2                                |
| ---------------------- | ------- | ------- | ---------------------------------- | ----------------------------------- |
| `_pw_property_id`      | integer | `0`     |                                    | CMB2: `pw_amenity_metabox` (select) |
| `_pw_type`             | string  | `''`    | `amenity` | `service` | `facility` | select                              |
| `_pw_category`         | string  | `''`    |                                    | text                                |
| `_pw_icon`             | string  | `''`    |                                    | textarea_small                      |
| `_pw_description`      | string  | `''`    |                                    | textarea_small                      |
| `_pw_is_complimentary` | boolean | `false` |                                    | checkbox                            |
| `_pw_display_order`    | integer | `0`     |                                    | text_small                          |


---

## Post Type: `pw_policy`

**Supports:** `title`, `editor`, `custom-fields`  
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

**Supports:** `title`, `editor`, `excerpt`, `thumbnail`, `custom-fields`

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

**Supports:** `title`, `editor`, `excerpt`, `thumbnail`, `custom-fields`  
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

**Supports:** `title`, `editor`, `excerpt`, `thumbnail`, `custom-fields`  
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
| `_pw_gallery`          | arrayinteger | —       |                                                                 | file_list                             |
| `_pw_display_order`    | integer      | `0`     |                                                                 | text_small                            |


---

## Post Type: `pw_event`

**Supports:** `title`, `editor`, `excerpt`, `thumbnail`, `custom-fields`  
**Taxonomies:** `pw_event_type`, `pw_event_organiser`


| Meta Key                    | Type         | Default                        | Notes                                                                             | CMB2                              |
| --------------------------- | ------------ | ------------------------------ | --------------------------------------------------------------------------------- | --------------------------------- |
| `_pw_property_id`           | integer      | `0`                            |                                                                                   | CMB2: `pw_event_metabox` (select) |
| `_pw_venue_id`              | integer      | `0`                            | FK → `pw_meeting_room`                                                            | select (pw_meeting_room_options)  |
| `_pw_description`           | string       | `''`                           |                                                                                   | textarea                          |
| `_pw_start_datetime`        | string       | `''`                           | Wall time `Y-m-d H:i:s` (no TZ in DB). **Convention:** interpret in `pw_property._pw_timezone` for schema.org (`pw_event_local_datetime_to_iso8601()` or REST `pw_start_datetime_iso8601`). | text_datetime_timestamp_timezone  |
| `_pw_end_datetime`          | string       | `''`                           | Same as start.                                                                  | text_datetime_timestamp_timezone  |
| `_pw_capacity`              | integer      | `0`                            |                                                                                   | text_small                        |
| `_pw_price_from`            | number       | `0`                            |                                                                                   | text_money                        |
| `_pw_booking_url`           | string       | `''`                           |                                                                                   | text_url                          |
| `_pw_recurrence_rule`       | string       | `''`                           | iCal RRULE (e.g. `FREQ=WEEKLY;BYDAY=SA`)                                          | pw_rrule (custom field)           |
| `_pw_event_status`          | string       | `'EventScheduled'`             | schema.org: EventScheduled | EventCancelled | EventPostponed | EventRescheduled   | select                            |
| `_pw_event_attendance_mode` | string       | `'OfflineEventAttendanceMode'` | OfflineEventAttendanceMode | OnlineEventAttendanceMode | MixedEventAttendanceMode | select                            |
| `_pw_gallery`               | arrayinteger | —                              |                                                                                   | file_list                         |


**Organiser:** Use taxonomy `pw_event_organiser` (term name = organiser name). Term meta `organiser_url` stores the URL. Used for schema.org Event markup.

**REST (computed, read-only):** `pw_start_datetime_iso8601`, `pw_end_datetime_iso8601` — same convention as above.

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


All taxonomies: non-hierarchical, `show_in_rest: true`, `show_admin_column: true`, `rewrite: false`.

### Taxonomy seed values (optional pre-load)

Default terms can be created at install; they are optional and editable afterward. Implementation status and rationale: `[TAXONOMY-SEED-VALUES.md](TAXONOMY-SEED-VALUES.md)`.

#### Implemented: `pw_policy_type`

Check-in, Check-out, Cancellation, Pet, Child, Payment, Smoking, Custom

#### Proposed pre-load terms

`**pw_bed_type`** (`pw_room_type`): Twin, Double, Queen, King, Single, Sofa Bed, Bunk Bed, Murphy Bed, Rollaway, Crib

`**pw_view_type**` (`pw_room_type`): Ocean, Sea, Beach, Pool, Garden, City, Mountain, Lake, Courtyard, Partial Ocean, Partial Sea, No View

`**pw_meal_period**` (`pw_restaurant`): Breakfast, Brunch, Lunch, Dinner, All-day Dining, Afternoon Tea, Late Night, 24-Hour

`**pw_treatment_type**` (`pw_spa`): Massage, Facial, Body Wrap, Body Scrub, Manicure, Pedicure, Hair, Waxing, Aromatherapy, Hot Stone, Reflexology, Couples Treatment, Pre/Post Natal

`**pw_av_equipment**` (`pw_meeting_room`): Projector, Screen, Video Conferencing, Microphone, PA System, Whiteboard, Flip Chart, HDMI Connection, Wireless Presentation, Recording

`**pw_feature_group**` (`pw_feature`): Bedding, Bathroom, In-room, Entertainment, Climate, Connectivity, Outdoor

`**pw_nearby_type**` (`pw_nearby`): Beach, Airport, Train Station, Attraction, Shopping, Dining, Park, Museum, Golf, Hospital, Bank/ATM, Supermarket

`**pw_transport_mode**` (`pw_nearby`): Walk, Drive, Taxi, Public Transport, Shuttle, Boat, Bicycle

`**pw_experience_category**` (`pw_experience`): Adventure, Cultural, Culinary, Wellness, Water Sports, Land Activities, Kids, Nightlife, Shopping, Nature

`**pw_event_type**` (`pw_event`): Wedding, Conference, Meeting, Seminar, Gala, Private Dining, Team Building, Product Launch, Social Event, Exhibition

#### Not recommended for pre-load


| Taxonomy             | Reason                                                    |
| -------------------- | --------------------------------------------------------- |
| `pw_event_organiser` | Property-specific; organisers vary per property and event |


---

## Options Page: Portico Webworks Settings

**WordPress option (single row):** `pw_settings` — associative array of the keys below.

**CMB2 box:** `pw_settings` (`object_types: options-page`, `option_key: pw_settings`). Fields are **not** on a separate CMB2 submenu: the box is output inside **Portico Webworks → General** (first tab), wrapped in a custom `<form>` that posts to `admin-post.php` with `action=pw_settings`. The redundant CMB2 “Settings” submenu entry is removed.

**Same screen (not in `pw_settings`):** “Search Engine Indexing” is static markup only; it links to **Settings → Reading** in core WordPress.

### Keys stored in `pw_settings`

| Option key                 | Type    | Default        | Notes |
| -------------------------- | ------- | -------------- | ----- |
| `pw_property_mode`         | string  | `'single'`     | `'single'` \| `'multi'` |
| `pw_property_base`         | string  | `'properties'` | URL prefix for `pw_property` rewrite (multi mode only); `sanitize_title`-style slug |
| `pw_default_property_id`   | int     | `0`            | **Single mode:** required site-wide context; must be a **published** `pw_property` post ID. **Multi mode:** forced to `0` on save. CMB2 field hidden when saved mode is multi; client script toggles row when switching radios before save (`assets/admin-settings.js`, class `pw-default-property-row`). |
| `pw_default_template`      | string  | `''`           | Template slug for front-end rendering (all properties) |
| `pw_github_releases_url`   | string  | `''`           | Normalized `https://github.com/{owner}/{repo}/releases`; used by “Update from GitHub” (`includes/github-plugin-update.php`). Release asset name: `portico_webworks_plugin.zip`. |

### Read path (`pw_get_setting` / CMB2)

- **`pw_get_merged_pw_settings()`** loads `get_option( 'pw_settings' )` and merges with **legacy** top-level options (same five keys, previously or still used as `get_option( 'pw_property_mode' )`, etc.) via `wp_parse_args( $stored, $legacy )`. Missing keys in the DB blob are filled from legacy so partial arrays do not “lose” GitHub URL or default property in the UI.
- **`pw_get_setting( $key, $default )`** reads from that merged array, then falls back to `get_option( $key, $default )` for unknown keys.
- **`cmb2_override_option_get_pw_settings`** returns `pw_get_merged_pw_settings()` so CMB2 always sees a complete field set.

### Write path (`pre_update_option_pw_settings`)

- **Multi mode:** sets `pw_default_property_id` to `0`; if `pw_github_releases_url` is absent from POST, preserved from previous value when possible.
- **Single mode:** if `pw_default_property_id` or `pw_github_releases_url` is missing from POST, copy from old option array when possible (avoids CMB2 dropping keys when a field was not rendered/submitted).
- Invalid default property (not `pw_property` or not `publish`) → cleared to `0` and a short admin notice.

### Save handler (reliability)

- **`admin_post_pw_settings` (priority 0):** `pw_save_portico_cmb2_settings()` — verifies capability, `submit-cmb`, action, and CMB2 nonce, then **`$cmb->save_fields( 'pw_settings', 'options-page', $_POST )`**, redirects with `settings-updated`, **`exit`**. Ensures the option is written even if `CMB2_Options_Hookup::can_save()` would skip on `admin-post.php`.

### Related hooks

- **`update_option_pw_settings`:** `flush_rewrite_rules()` when `pw_property_mode` or `pw_property_base` changes.

---

## SEO Meta Box (shared)

**CMB2 box:** `pw_seo_metabox`  
**Applies to:** `pw_room_type`, `pw_restaurant`, `pw_spa`, `pw_meeting_room`, `pw_experience`, `pw_event`, `pw_offer`, `pw_nearby`


| Meta Key               | Type   | CMB2                           |
| ---------------------- | ------ | ------------------------------ |
| `_pw_meta_title`       | string | text (max 60 chars)            |
| `_pw_meta_description` | string | textarea_small (max 160 chars) |


---

## Relationships Summary

```
pw_property (1)
  ├── pw_room_type       (_pw_property_id)
  │     └── pw_feature   (_pw_features[] → pw_feature IDs)
  ├── pw_restaurant      (_pw_property_id)
  ├── pw_spa             (_pw_property_id)
  ├── pw_meeting_room    (_pw_property_id)
  ├── pw_amenity         (_pw_property_id)
  ├── pw_policy          (_pw_property_id)
  ├── pw_nearby          (_pw_property_id)
  ├── pw_experience      (_pw_property_id; _pw_connected_to[] → pw_property | pw_restaurant | pw_spa)
  └── pw_event           (_pw_property_id, _pw_venue_id → pw_meeting_room)

pw_offer               (_pw_property_id; _pw_parents[] → pw_property | pw_restaurant | pw_spa)
pw_faq                 (_pw_property_id; _pw_connected_to[] → pw_property | pw_restaurant | pw_meeting_room | pw_spa)
```

