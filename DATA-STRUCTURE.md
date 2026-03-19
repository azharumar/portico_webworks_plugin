# Portico Webworks Plugin — Post Type Data Structure

## Overview

The plugin registers **1 primary post type** (`pw_property`) and **12 child post types**. Child CPTs are linked to a property via a `_pw_property_id` meta field (or `_pw_connected_to` / `_pw_parents` where applicable). All CPTs expose data via the REST API (`show_in_rest: true`).

Admin UI is built with **CMB2** (meta boxes) and custom metaboxes (property profile). CMB2 is bundled in `vendor/cmb2/` and its admin menu is suppressed via `cmb2_menus` filter.

---

## Post Type: `pw_property`

**REST base:** `pw-properties`  
**Supports:** `title`, `editor`, `excerpt`, `thumbnail`, `revisions`, `custom-fields`  
**Public:** mode-dependent (`pw_property_mode` option: `single` | `multi`)

### Meta Fields

#### General
| Meta Key | Type | Default | Notes | CMB2 |
|---|---|---|---|---|
| `_pw_legal_name` | string | `''` | For invoices / compliance | Custom metabox (Property Profile → General) |
| `_pw_star_rating` | integer | `0` | 1–5 classification | Custom metabox |
| `_pw_currency` | string | `'USD'` | ISO 4217 currency code | Custom metabox (select) |
| `_pw_check_in_time` | string | `''` | e.g. `14:00` | Custom metabox |
| `_pw_check_out_time` | string | `''` | e.g. `11:00` | Custom metabox |
| `_pw_year_established` | integer | `0` | schema.org LodgingBusiness | Custom metabox |
| `_pw_total_rooms` | integer | `0` | Total inventory count | Custom metabox |

> **Note:** The default front-end template is a plugin-level setting (Portico Webworks Settings → Default Template). Property profile fields (General, Address, Geo, Social) use custom metaboxes in `property-profile.php`, not CMB2.

#### Address
| Meta Key | Type | Default | Notes | CMB2 |
|---|---|---|---|---|
| `_pw_address_line_1` | string | `''` | | Custom metabox (Property Profile → Address) |
| `_pw_address_line_2` | string | `''` | | Custom metabox |
| `_pw_city` | string | `''` | | Custom metabox |
| `_pw_state` | string | `''` | State or province | Custom metabox |
| `_pw_postal_code` | string | `''` | | Custom metabox |
| `_pw_country` | string | `''` | Full country name | Custom metabox |
| `_pw_country_code` | string | `''` | ISO 3166-1 alpha-2 (schema.org) | Custom metabox |

#### Contacts (`_pw_contacts`) — repeatable group
| Field | Type | Notes | CMB2 |
|---|---|---|---|
| `label` | string | e.g. Hotel, Reservations, Sales | CMB2: `pw_property_contacts` |
| `phone` | string | | |
| `mobile` | string | | |
| `whatsapp` | string | | |
| `email` | string | | |

#### Geo
| Meta Key | Type | Default | Notes | CMB2 |
|---|---|---|---|---|
| `_pw_lat` | number | `0` | | Custom metabox (Property Profile → Geo) |
| `_pw_lng` | number | `0` | | Custom metabox |
| `_pw_google_place_id` | string | `''` | Google Maps Place ID | Custom metabox |
| `_pw_timezone` | string | `''` | IANA (e.g. `Asia/Kolkata`) | Custom metabox (select) |

#### Social
| Meta Key | Type | Default | CMB2 |
|---|---|---|---|
| `_pw_social_facebook` | string | `''` | Custom metabox (Property Profile → Social) |
| `_pw_social_instagram` | string | `''` | Custom metabox |
| `_pw_social_twitter` | string | `''` | Custom metabox |
| `_pw_social_youtube` | string | `''` | Custom metabox |
| `_pw_social_linkedin` | string | `''` | Custom metabox |
| `_pw_social_tripadvisor` | string | `''` | Custom metabox |

#### SEO & Social Sharing
| Meta Key | Type | Default | Notes | CMB2 |
|---|---|---|---|---|
| `_pw_meta_title` | string | `''` | Overrides post title | CMB2: `pw_property_seo` |
| `_pw_meta_description` | string | `''` | Overrides excerpt | CMB2 |
| `_pw_og_image` | integer | `0` | Attachment ID for Open Graph | CMB2 (file) |

#### Pools (`_pw_pools`) — repeatable group
| Field | Type | Notes | CMB2 |
|---|---|---|---|
| `name` | string | e.g. Main Pool, Kids Pool | CMB2: `pw_property_pools` |
| `length_m` | number | | |
| `width_m` | number | | |
| `depth_m` | number | | |
| `open_time` | string | e.g. `07:00` | text_time |
| `close_time` | string | e.g. `22:00` | text_time |
| `is_heated` | boolean | | checkbox |
| `is_kids` | boolean | | checkbox |
| `is_indoor` | boolean | | checkbox |
| `is_infinity` | boolean | | checkbox |

#### Direct Booking Benefits (`_pw_direct_benefits`) — repeatable group
| Field | Type | CMB2 |
|---|---|---|
| `title` | string | CMB2: `pw_property_direct_benefits` |
| `description` | string | |
| `icon` | string | Icon slug or SVG |

#### Sustainability — string enum (`unknown` | `available` | `not_available`) + optional note
Each parameter has a paired `_note` key (string, `''`). CMB2: `pw_property_sustainability`

| Meta Key | Note Key | Group |
|---|---|---|
| `_pw_sus_solar_power` | `_pw_sus_solar_power_note` | Energy |
| `_pw_sus_solar_water_heater` | `_pw_sus_solar_water_heater_note` | Energy |
| `_pw_sus_energy_efficient_lighting` | `_pw_sus_energy_efficient_lighting_note` | Energy |
| `_pw_sus_energy_saving_thermostats` | `_pw_sus_energy_saving_thermostats_note` | Energy |
| `_pw_sus_green_building_design` | `_pw_sus_green_building_design_note` | Energy |
| `_pw_sus_water_efficient_fixtures` | `_pw_sus_water_efficient_fixtures_note` | Water |
| `_pw_sus_sewage_treatment_plant` | `_pw_sus_sewage_treatment_plant_note` | Water |
| `_pw_sus_water_reuse_program` | `_pw_sus_water_reuse_program_note` | Water |
| `_pw_sus_waste_segregation` | `_pw_sus_waste_segregation_note` | Waste reduction |
| `_pw_sus_recycling_program` | `_pw_sus_recycling_program_note` | Waste reduction |
| `_pw_sus_no_styrofoam` | `_pw_sus_no_styrofoam_note` | Waste reduction |
| `_pw_sus_electronics_disposal` | `_pw_sus_electronics_disposal_note` | Waste reduction |
| `_pw_sus_reusable_water_bottles` | `_pw_sus_reusable_water_bottles_note` | Waste reduction |
| `_pw_sus_wall_mounted_dispensers` | `_pw_sus_wall_mounted_dispensers_note` | Guest amenities |
| `_pw_sus_eco_friendly_toiletries` | `_pw_sus_eco_friendly_toiletries_note` | Guest amenities |
| `_pw_sus_towel_reuse_program` | `_pw_sus_towel_reuse_program_note` | Guest amenities |
| `_pw_sus_linen_reuse_program` | `_pw_sus_linen_reuse_program_note` | Guest amenities |
| `_pw_sus_local_food_sourcing` | `_pw_sus_local_food_sourcing_note` | Sustainable sourcing |
| `_pw_sus_organic_food_options` | `_pw_sus_organic_food_options_note` | Sustainable sourcing |

#### Certifications & Awards (`_pw_certifications`) — repeatable group
| Field | Type | Notes | CMB2 |
|---|---|---|---|
| `name` | string | e.g. Green Key, Forbes Travel Guide | CMB2: `pw_property_certifications` |
| `issuer` | string | Organisation that issued | |
| `year` | string | Year awarded or renewed | |
| `url` | string | Link to certificate | |

#### Accessibility — string enum (`unknown` | `available` | `not_available`) + optional note
CMB2: `pw_property_accessibility`

| Meta Key | Note Key | Group |
|---|---|---|
| `_pw_acc_wheelchair_accessible` | `_pw_acc_wheelchair_accessible_note` | Property access |
| `_pw_acc_step_free_entrance` | `_pw_acc_step_free_entrance_note` | Property access |
| `_pw_acc_automatic_doors` | `_pw_acc_automatic_doors_note` | Property access |
| `_pw_acc_accessible_parking` | `_pw_acc_accessible_parking_note` | Property access |
| `_pw_acc_accessible_path_to_entrance` | `_pw_acc_accessible_path_to_entrance_note` | Property access |
| `_pw_acc_accessible_room_available` | `_pw_acc_accessible_room_available_note` | Guest rooms |
| `_pw_acc_grab_bars_bathroom` | `_pw_acc_grab_bars_bathroom_note` | Guest rooms |
| `_pw_acc_roll_in_shower` | `_pw_acc_roll_in_shower_note` | Guest rooms |
| `_pw_acc_adjustable_showerhead` | `_pw_acc_adjustable_showerhead_note` | Guest rooms |
| `_pw_acc_lowered_closet` | `_pw_acc_lowered_closet_note` | Guest rooms |
| `_pw_acc_transfer_friendly_bed` | `_pw_acc_transfer_friendly_bed_note` | Guest rooms |
| `_pw_acc_emergency_pull_cords` | `_pw_acc_emergency_pull_cords_note` | Guest rooms |
| `_pw_acc_reachable_outlets` | `_pw_acc_reachable_outlets_note` | Guest rooms |
| `_pw_acc_elevator` | `_pw_acc_elevator_note` | Facilities |
| `_pw_acc_elevator_audio_cues` | `_pw_acc_elevator_audio_cues_note` | Facilities |
| `_pw_acc_pool_lift` | `_pw_acc_pool_lift_note` | Facilities |
| `_pw_acc_accessible_restaurant` | `_pw_acc_accessible_restaurant_note` | Facilities |
| `_pw_acc_visual_fire_alarm` | `_pw_acc_visual_fire_alarm_note` | Communication |
| `_pw_acc_clear_dietary_labels` | `_pw_acc_clear_dietary_labels_note` | Communication |

---

## Post Type: `pw_feature`

**Supports:** `title`, `custom-fields`  
**Taxonomies:** `pw_feature_group`

| Meta Key | Type | Default | CMB2 |
|---|---|---|---|
| `_pw_icon` | string | `''` | CMB2: `pw_feature_metabox` (textarea_small) — SVG string or icon slug |

---

## Post Type: `pw_room_type`

**Supports:** `title`, `editor`, `excerpt`, `thumbnail`, `custom-fields`  
**Taxonomies:** `pw_bed_type`, `pw_view_type`

| Meta Key | Type | Default | Notes | CMB2 |
|---|---|---|---|---|
| `_pw_property_id` | integer | `0` | FK → `pw_property` | CMB2: `pw_room_type_metabox` (select) |
| `_pw_rate_from` | number | `0` | Starting rate | text_money |
| `_pw_rate_to` | number | `0` | Upper rate range | text_money |
| `_pw_max_occupancy` | integer | `0` | Total guest limit | text_small |
| `_pw_max_adults` | integer | `0` | max_adults + max_children ≤ max_occupancy | text_small |
| `_pw_max_children` | integer | `0` | max_adults + max_children ≤ max_occupancy | text_small |
| `_pw_size_sqft` | integer | `0` | | text_small |
| `_pw_size_sqm` | integer | `0` | | text_small |
| `_pw_max_extra_beds` | integer | `0` | | text_small |
| `_pw_display_order` | integer | `0` | | text_small |
| `_pw_features` | array\<integer\> | — | Array of `pw_feature` post IDs | multicheck |
| `_pw_gallery` | array\<integer\> | — | Attachment IDs | file_list |

**Validation:** Admin UI enforces `max_adults + max_children ≤ max_occupancy` on save.

---

## Post Type: `pw_restaurant`

**Supports:** `title`, `editor`, `excerpt`, `thumbnail`, `custom-fields`  
**Taxonomies:** `pw_meal_period`

| Meta Key | Type | Default | Notes | CMB2 |
|---|---|---|---|---|
| `_pw_property_id` | integer | `0` | | CMB2: `pw_restaurant_metabox` (select) |
| `_pw_location` | string | `''` | e.g. Rooftop Level, Beach Side | text |
| `_pw_cuisine_type` | string | `''` | | text |
| `_pw_seating_capacity` | integer | `0` | | text_small |
| `_pw_reservation_url` | string | `''` | | text_url |
| `_pw_menu_url` | string | `''` | | text_url |
| `_pw_gallery` | array\<integer\> | — | | file_list |

#### Operating Hours — per-day meta keys `_pw_hours_{day}` (e.g. `_pw_hours_monday`)
Each day stores an object with `is_closed` and `sessions` (repeatable). | CMB2: `pw_restaurant_operating_hours` |

| Field | Type | Notes |
|---|---|---|
| `is_closed` | boolean | Closed all day |
| `sessions` | array | Repeatable: `label`, `open_time`, `close_time` |

---

## Post Type: `pw_spa`

**Supports:** `title`, `editor`, `excerpt`, `thumbnail`, `custom-fields`  
**Taxonomies:** `pw_treatment_type`

| Meta Key | Type | Default | CMB2 |
|---|---|---|---|
| `_pw_property_id` | integer | `0` | CMB2: `pw_spa_metabox` (select) |
| `_pw_booking_url` | string | `''` | text_url |
| `_pw_menu_url` | string | `''` | text_url |
| `_pw_min_age` | integer | `0` | text_small |
| `_pw_number_of_treatment_rooms` | integer | `0` | text_small |
| `_pw_gallery` | array\<integer\> | — | file_list |

#### Operating Hours — per-day `_pw_hours_{day}`
Same structure as restaurant. | CMB2: `pw_spa_operating_hours` |

---

## Post Type: `pw_meeting_room`

**Supports:** `title`, `editor`, `excerpt`, `thumbnail`, `custom-fields`  
**Taxonomies:** `pw_av_equipment`

| Meta Key | Type | Default | Notes | CMB2 |
|---|---|---|---|---|
| `_pw_property_id` | integer | `0` | | CMB2: `pw_meeting_room_metabox` (select) |
| `_pw_capacity_theatre` | integer | `0` | | text_small |
| `_pw_capacity_classroom` | integer | `0` | | text_small |
| `_pw_capacity_boardroom` | integer | `0` | | text_small |
| `_pw_capacity_ushape` | integer | `0` | | text_small |
| `_pw_area_sqft` | integer | `0` | | text_small |
| `_pw_area_sqm` | integer | `0` | | text_small |
| `_pw_prefunction_area_sqft` | integer | `0` | | text_small |
| `_pw_prefunction_area_sqm` | integer | `0` | | text_small |
| `_pw_natural_light` | boolean | `false` | | checkbox |
| `_pw_floor_plan` | integer | `0` | Attachment ID | file |
| `_pw_sales_phone` | string | `''` | Direct venue contact | text_small |
| `_pw_sales_mobile` | string | `''` | | text_small |
| `_pw_sales_whatsapp` | string | `''` | | text_small |
| `_pw_sales_email` | string | `''` | | text_small |
| `_pw_gallery` | array\<integer\> | — | | file_list |

---

## Post Type: `pw_amenity`

**Supports:** `title`, `custom-fields`

| Meta Key | Type | Default | Notes | CMB2 |
|---|---|---|---|---|
| `_pw_property_id` | integer | `0` | | CMB2: `pw_amenity_metabox` (select) |
| `_pw_type` | string | `''` | `amenity` \| `service` \| `facility` | select |
| `_pw_category` | string | `''` | | text |
| `_pw_icon` | string | `''` | | textarea_small |
| `_pw_description` | string | `''` | | textarea_small |
| `_pw_is_complimentary` | boolean | `false` | | checkbox |
| `_pw_display_order` | integer | `0` | | text_small |

---

## Post Type: `pw_policy`

**Supports:** `title`, `editor`, `custom-fields`  
**Taxonomies:** `pw_policy_type` (checkin, checkout, cancellation, pet, child, payment, smoking, custom)

| Meta Key | Type | Default | Notes | CMB2 |
|---|---|---|---|---|
| `_pw_property_id` | integer | `0` | | CMB2: `pw_policy_metabox` (select) |
| `_pw_content` | string | `''` | | textarea |
| `_pw_is_highlighted` | boolean | `false` | | checkbox |
| `_pw_display_order` | integer | `0` | | text_small |
| `_pw_active` | boolean | `true` | | checkbox |

Policy type is set via taxonomy `pw_policy_type`. Post title is the policy title.

---

## Post Type: `pw_faq`

**Supports:** `title`, `custom-fields`

| Meta Key | Type | Default | CMB2 |
|---|---|---|---|
| `_pw_answer` | string | `''` | CMB2: `pw_faq_metabox` (wysiwyg) |
| `_pw_display_order` | integer | `0` | text_small |

#### Connected To (`_pw_connected_to`) — repeatable group
| Field | Type | Notes | CMB2 |
|---|---|---|---|
| `type` | string | `pw_property` \| `pw_restaurant` \| `pw_meeting_room` \| `pw_spa` | select |
| `id` | integer | Post ID of connected entity | select (pw_faq_connection_options) |

---

## Post Type: `pw_offer`

**Supports:** `title`, `editor`, `excerpt`, `thumbnail`, `custom-fields`

| Meta Key | Type | Default | Notes | CMB2 |
|---|---|---|---|---|
| `_pw_offer_type` | string | `'promotion'` | `promotion` \| `package` \| `direct_booking_benefit` | CMB2: `pw_offer_metabox` (select) |
| `_pw_parents` | array | — | Repeatable: `type`, `id` → pw_property \| pw_restaurant \| pw_spa | group |
| `_pw_valid_from` | string | `''` | Date `Y-m-d` | text_date |
| `_pw_valid_to` | string | `''` | Date `Y-m-d` | text_date |
| `_pw_booking_url` | string | `''` | | text_url |
| `_pw_is_featured` | boolean | `false` | | checkbox |
| `_pw_discount_type` | string | `''` | `percentage` \| `flat` \| `value_add` | select |
| `_pw_discount_value` | number | `0` | Conditional (hidden if value_add) | text_money |
| `_pw_minimum_stay_nights` | integer | `0` | Conditional (promotion/package only) | text_small |
| `_pw_room_types` | array\<integer\> | — | `pw_room_type` post IDs | multicheck |
| `_pw_display_order` | integer | `0` | | text_small |

Description/terms: use post `editor` and `excerpt` as needed.

---

## Post Type: `pw_nearby`

**Supports:** `title`, `editor`, `excerpt`, `thumbnail`, `custom-fields`  
**Taxonomies:** `pw_nearby_type`, `pw_transport_mode`

| Meta Key | Type | Default | CMB2 |
|---|---|---|---|
| `_pw_property_id` | integer | `0` | CMB2: `pw_nearby_metabox` (select) |
| `_pw_distance_km` | number | `0` | text_small |
| `_pw_travel_time_min` | integer | `0` | text_small |
| `_pw_place_url` | string | `''` | text_url |
| `_pw_display_order` | integer | `0` | text_small |

---

## Post Type: `pw_experience`

**Supports:** `title`, `editor`, `excerpt`, `thumbnail`, `custom-fields`  
**Taxonomies:** `pw_experience_category`

| Meta Key | Type | Default | Notes | CMB2 |
|---|---|---|---|---|
| `_pw_connected_to` | array | — | Repeatable: `type`, `id` → pw_property \| pw_restaurant \| pw_spa | CMB2: `pw_experience_metabox` (group) |
| `_pw_description` | string | `''` | | textarea |
| `_pw_duration_hours` | number | `0` | | text_small |
| `_pw_price_from` | number | `0` | | text_money |
| `_pw_booking_url` | string | `''` | | text_url |
| `_pw_is_complimentary` | boolean | `false` | | checkbox |
| `_pw_gallery` | array\<integer\> | — | | file_list |
| `_pw_display_order` | integer | `0` | | text_small |

---

## Post Type: `pw_event`

**Supports:** `title`, `editor`, `excerpt`, `thumbnail`, `custom-fields`  
**Taxonomies:** `pw_event_type`, `pw_event_organiser`

| Meta Key | Type | Default | Notes | CMB2 |
|---|---|---|---|---|
| `_pw_property_id` | integer | `0` | | CMB2: `pw_event_metabox` (select) |
| `_pw_venue_id` | integer | `0` | FK → `pw_meeting_room` | select (pw_meeting_room_options) |
| `_pw_description` | string | `''` | | textarea |
| `_pw_start_datetime` | string | `''` | | text_datetime_timestamp_timezone |
| `_pw_end_datetime` | string | `''` | | text_datetime_timestamp_timezone |
| `_pw_capacity` | integer | `0` | | text_small |
| `_pw_price_from` | number | `0` | | text_money |
| `_pw_booking_url` | string | `''` | | text_url |
| `_pw_recurrence_rule` | string | `''` | iCal RRULE (e.g. `FREQ=WEEKLY;BYDAY=SA`) | pw_rrule (custom field) |
| `_pw_event_status` | string | `'EventScheduled'` | schema.org: EventScheduled \| EventCancelled \| EventPostponed \| EventRescheduled | select |
| `_pw_event_attendance_mode` | string | `'OfflineEventAttendanceMode'` | OfflineEventAttendanceMode \| OnlineEventAttendanceMode \| MixedEventAttendanceMode | select |
| `_pw_gallery` | array\<integer\> | — | | file_list |

**Organiser:** Use taxonomy `pw_event_organiser` (term name = organiser name). Term meta `organiser_url` stores the URL. Used for schema.org Event markup.

---

## Taxonomies

| Taxonomy | Post Type | Label | Term Meta |
|---|---|---|---|
| `pw_bed_type` | `pw_room_type` | Bed Types | — |
| `pw_view_type` | `pw_room_type` | View Types | — |
| `pw_meal_period` | `pw_restaurant` | Meal Periods | — |
| `pw_treatment_type` | `pw_spa` | Treatment Types | — |
| `pw_av_equipment` | `pw_meeting_room` | AV Equipment | — |
| `pw_feature_group` | `pw_feature` | Feature Groups | — |
| `pw_nearby_type` | `pw_nearby` | Location Types | — |
| `pw_transport_mode` | `pw_nearby` | Transport Modes | — |
| `pw_experience_category` | `pw_experience` | Experience Categories | — |
| `pw_event_type` | `pw_event` | Event Types | — |
| `pw_policy_type` | `pw_policy` | Policy Types | — |
| `pw_event_organiser` | `pw_event` | Organisers | `organiser_url` (string) |

All taxonomies: non-hierarchical, `show_in_rest: true`, `show_admin_column: true`, `rewrite: false`.

---

## Options Page: Portico Webworks Settings

**CMB2 box:** `pw_settings` (options-page, `option_key: pw_settings`)

| Option Key | Type | Default | Notes |
|---|---|---|---|
| `pw_property_mode` | string | `'single'` | `single` \| `multi` |
| `pw_property_base` | string | `'properties'` | URL prefix for properties (multi mode) |
| `pw_default_template` | string | `''` | Template slug for front-end rendering |

---

## SEO Meta Box (shared)

**CMB2 box:** `pw_seo_metabox`  
**Applies to:** `pw_room_type`, `pw_restaurant`, `pw_spa`, `pw_meeting_room`, `pw_experience`, `pw_event`, `pw_offer`, `pw_nearby`

| Meta Key | Type | CMB2 |
|---|---|---|
| `_pw_meta_title` | string | text (max 60 chars) |
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
  ├── pw_experience      (_pw_connected_to[] → pw_property | pw_restaurant | pw_spa)
  └── pw_event           (_pw_property_id, _pw_venue_id → pw_meeting_room)

pw_offer               (_pw_parents[] → pw_property | pw_restaurant | pw_spa)
pw_faq                 (_pw_connected_to[] → pw_property | pw_restaurant | pw_meeting_room | pw_spa)
```
