# Portico Webworks Plugin — Post Type Data Structure

## Overview

The plugin registers **1 primary post type** (`pw_property`) and **11 child post types**. Child CPTs are linked to a property via a `_pw_property_id` meta field. All CPTs expose data via the REST API (`show_in_rest: true`).

---

## Post Type: `pw_property`

**REST base:** `pw-properties`  
**Supports:** `title`, `thumbnail`, `revisions`, `custom-fields`  
**Public:** mode-dependent (`pw_property_mode` option: `single` | `multi`)

### Meta Fields

#### General
| Meta Key | Type | Default | Notes |
|---|---|---|---|
| `_pw_legal_name` | string | `''` | For invoices / compliance |
| `_pw_star_rating` | integer | `0` | 1–5 classification |
| `_pw_currency` | string | `'USD'` | ISO 4217 currency code |
| `_pw_check_in_time` | string | `''` | e.g. `14:00` |
| `_pw_check_out_time` | string | `''` | e.g. `11:00` |
| `_pw_year_established` | integer | `0` | Used in schema.org LodgingBusiness |
| `_pw_total_rooms` | integer | `0` | Total inventory count |

> **Note:** The default front-end template is now a plugin-level setting configured via the Portico Webworks Settings page (stored as a WP option). It is no longer a per-property meta field.

#### Address
| Meta Key | Type | Default |
|---|---|---|
| `_pw_address_line_1` | string | `''` |
| `_pw_address_line_2` | string | `''` |
| `_pw_city` | string | `''` |
| `_pw_state` | string | `''` | State or province |
| `_pw_postal_code` | string | `''` |
| `_pw_country` | string | `''` | ISO 3166-1 alpha-2 |

#### Contact
| Meta Key | Type | Default |
|---|---|---|
| `_pw_phone` | string | `''` |
| `_pw_mobile` | string | `''` |
| `_pw_whatsapp` | string | `''` |
| `_pw_email` | string | `''` |

#### Geo
| Meta Key | Type | Default | Notes |
|---|---|---|---|
| `_pw_lat` | number | `0` | |
| `_pw_lng` | number | `0` | |
| `_pw_google_place_id` | string | `''` | Google Maps Place ID |
| `_pw_timezone` | string | `''` | IANA timezone identifier (e.g. `Asia/Kolkata`) |

#### Social
| Meta Key | Type | Default |
|---|---|---|
| `_pw_social_facebook` | string | `''` |
| `_pw_social_instagram` | string | `''` |
| `_pw_social_twitter` | string | `''` |
| `_pw_social_youtube` | string | `''` |
| `_pw_social_linkedin` | string | `''` |
| `_pw_social_tripadvisor` | string | `''` |

#### SEO & Social Sharing
| Meta Key | Type | Default | Notes |
|---|---|---|---|
| `_pw_og_image` | integer | `0` | Attachment ID for custom Open Graph image |

#### Pools (`_pw_pools`) — array of objects
| Field | Type | Notes |
|---|---|---|
| `name` | string | |
| `length_m` | number | |
| `width_m` | number | |
| `depth_m` | number | |
| `open_time` | string | e.g. `07:00` |
| `close_time` | string | e.g. `22:00` |
| `is_heated` | boolean | |
| `is_kids` | boolean | |
| `is_indoor` | boolean | |
| `is_infinity` | boolean | |

#### Direct Booking Benefits (`_pw_direct_benefits`) — array of objects
| Field | Type |
|---|---|
| `title` | string |
| `description` | string |
| `icon` | string |

#### Sustainability — string enum (`unknown` | `available` | `not_available`) + optional note
Each sustainability parameter has a paired `_note` key (string, `''`) for free-text clarification.

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

Certifications (strings, `''`): `_pw_sus_certification_name`, `_pw_sus_certification_url`

#### Accessibility — string enum (`unknown` | `available` | `not_available`) + optional note
Each accessibility parameter has a paired `_note` key (string, `''`) for free-text clarification.

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

| Meta Key | Type | Default |
|---|---|---|
| `_pw_icon` | string | `''` | SVG string or icon slug |
| `_pw_short_description` | string | `''` |

---

## Post Type: `pw_room_type`

**Supports:** `title`, `custom-fields`  
**Taxonomies:** `pw_bed_type`, `pw_view_type`

| Meta Key | Type | Default | Notes |
|---|---|---|---|
| `_pw_property_id` | integer | `0` | FK → `pw_property` |
| `_pw_rate_from` | number | `0` | Starting rate |
| `_pw_rate_to` | number | `0` | Upper end of rate range |
| `_pw_max_occupancy` | integer | `0` | Total guest limit |
| `_pw_max_adults` | integer | `0` | Must satisfy: max_adults + max_children ≤ max_occupancy |
| `_pw_max_children` | integer | `0` | Must satisfy: max_adults + max_children ≤ max_occupancy |
| `_pw_size_sqft` | integer | `0` | |
| `_pw_size_sqm` | integer | `0` | |
| `_pw_max_extra_beds` | integer | `0` | |
| `_pw_display_order` | integer | `0` | |
| `_pw_features` | array\<integer\> | — | Array of `pw_feature` post IDs |
| `_pw_gallery` | array\<integer\> | — | Array of attachment IDs |

**Validation:** Admin UI enforces `max_adults + max_children ≤ max_occupancy` on save.

---

## Post Type: `pw_restaurant`

**Supports:** `title`, `custom-fields`  
**Taxonomies:** `pw_meal_period`

| Meta Key | Type | Default | Notes |
|---|---|---|---|
| `_pw_property_id` | integer | `0` | |
| `_pw_location` | string | `''` | Physical location identifier (e.g. Rooftop Level, Beach Side) |
| `_pw_cuisine_type` | string | `''` | |
| `_pw_seating_capacity` | integer | `0` | |
| `_pw_reservation_url` | string | `''` | |
| `_pw_menu_url` | string | `''` | |
| `_pw_gallery` | array\<integer\> | — | |

#### Operating Hours (`_pw_operating_hours`) — array of session objects
Multiple sessions per day are supported by adding multiple entries with the same `day` value.

| Field | Type | Notes |
|---|---|---|
| `session_label` | string | e.g. `Breakfast`, `Lunch`, `Dinner` |
| `day` | string | `monday`…`sunday` |
| `open_time` | string | |
| `close_time` | string | |
| `is_closed` | boolean | |

---

## Post Type: `pw_spa`

**Supports:** `title`, `custom-fields`  
**Taxonomies:** `pw_treatment_type`

| Meta Key | Type | Default |
|---|---|---|
| `_pw_property_id` | integer | `0` |
| `_pw_booking_url` | string | `''` |
| `_pw_menu_url` | string | `''` |
| `_pw_min_age` | integer | `0` |
| `_pw_number_of_treatment_rooms` | integer | `0` |
| `_pw_gallery` | array\<integer\> | — |

#### Operating Hours (`_pw_operating_hours`) — array of session objects
Multiple sessions per day are supported by adding multiple entries with the same `day` value.

| Field | Type | Notes |
|---|---|---|
| `session_label` | string | e.g. `Morning`, `Afternoon` |
| `day` | string | `monday`…`sunday` |
| `open_time` | string | |
| `close_time` | string | |
| `is_closed` | boolean | |

---

## Post Type: `pw_meeting_room`

**Supports:** `title`, `custom-fields`  
**Taxonomies:** `pw_av_equipment`

| Meta Key | Type | Default |
|---|---|---|
| `_pw_property_id` | integer | `0` |
| `_pw_capacity_theatre` | integer | `0` |
| `_pw_capacity_classroom` | integer | `0` |
| `_pw_capacity_boardroom` | integer | `0` |
| `_pw_capacity_ushape` | integer | `0` |
| `_pw_area_sqft` | integer | `0` |
| `_pw_area_sqm` | integer | `0` |
| `_pw_prefunction_area_sqft` | integer | `0` |
| `_pw_prefunction_area_sqm` | integer | `0` |
| `_pw_natural_light` | boolean | `false` |
| `_pw_floor_plan` | integer | `0` | Attachment ID |
| `_pw_phone` | string | `''` | Direct venue contact |
| `_pw_mobile` | string | `''` | |
| `_pw_whatsapp` | string | `''` | |
| `_pw_email` | string | `''` | |
| `_pw_gallery` | array\<integer\> | — |

---

## Post Type: `pw_amenity`

**Supports:** `title`, `custom-fields`

| Meta Key | Type | Default | Notes |
|---|---|---|---|
| `_pw_property_id` | integer | `0` | |
| `_pw_type` | string | `''` | `amenity` \| `service` \| `facility` |
| `_pw_category` | string | `''` | |
| `_pw_icon` | string | `''` | |
| `_pw_description` | string | `''` | |
| `_pw_is_complimentary` | boolean | `false` | |
| `_pw_display_order` | integer | `0` | |

---

## Post Type: `pw_policy`

**Supports:** `title`, `custom-fields`

| Meta Key | Type | Default | Notes |
|---|---|---|---|
| `_pw_property_id` | integer | `0` | |
| `_pw_policy_type` | string | `''` | `checkin` \| `checkout` \| `cancellation` \| `pet` \| `child` \| `payment` \| `smoking` \| `custom` |
| `_pw_title` | string | `''` | |
| `_pw_content` | string | `''` | |
| `_pw_is_highlighted` | boolean | `false` | |
| `_pw_display_order` | integer | `0` | |
| `_pw_active` | boolean | `true` | |

---

## Post Type: `pw_faq`

**Supports:** `title`, `custom-fields`

| Meta Key | Type | Default |
|---|---|---|
| `_pw_answer` | string | `''` |
| `_pw_display_order` | integer | `0` |

#### Connected To (`_pw_connected_to`) — array of objects
Links an FAQ to one or more entities.

| Field | Type | Notes |
|---|---|---|
| `type` | string | `pw_property` \| `pw_restaurant` \| `pw_meeting_room` \| `pw_spa` |
| `id` | integer | Post ID of the connected entity |

---

## Post Type: `pw_offer`

**Supports:** `title`, `thumbnail`, `custom-fields`

| Meta Key | Type | Default | Notes |
|---|---|---|---|
| `_pw_offer_type` | string | `'promotion'` | `promotion` \| `package` \| `direct_booking_benefit` |
| `_pw_parent_type` | string | `''` | `pw_property` \| `pw_restaurant` \| `pw_spa` |
| `_pw_parent_id` | integer | `0` | Post ID of the parent entity |
| `_pw_description` | string | `''` | |
| `_pw_valid_from` | string | `''` | Date `Y-m-d` |
| `_pw_valid_to` | string | `''` | Date `Y-m-d` |
| `_pw_booking_url` | string | `''` | |
| `_pw_terms` | string | `''` | |
| `_pw_is_featured` | boolean | `false` | |
| `_pw_discount_type` | string | `''` | `percentage` \| `flat` \| `value_add` |
| `_pw_discount_value` | number | `0` | |
| `_pw_minimum_stay_nights` | integer | `0` | |
| `_pw_room_types` | array\<integer\> | — | Array of `pw_room_type` post IDs |
| `_pw_display_order` | integer | `0` | |

---

## Post Type: `pw_nearby`

**Supports:** `title`, `custom-fields`  
**Taxonomies:** `pw_nearby_type`, `pw_transport_mode`

| Meta Key | Type | Default |
|---|---|---|
| `_pw_property_id` | integer | `0` |
| `_pw_distance_km` | number | `0` |
| `_pw_travel_time_min` | integer | `0` |
| `_pw_place_url` | string | `''` |
| `_pw_display_order` | integer | `0` |

---

## Post Type: `pw_experience`

**Supports:** `title`, `thumbnail`, `custom-fields`  
**Taxonomies:** `pw_experience_category`

| Meta Key | Type | Default |
|---|---|---|
| `_pw_property_id` | integer | `0` |
| `_pw_description` | string | `''` |
| `_pw_duration_hours` | number | `0` |
| `_pw_price_from` | number | `0` |
| `_pw_booking_url` | string | `''` |
| `_pw_is_complimentary` | boolean | `false` |
| `_pw_gallery` | array\<integer\> | — |
| `_pw_display_order` | integer | `0` |

---

## Post Type: `pw_event`

**Supports:** `title`, `thumbnail`, `custom-fields`  
**Taxonomies:** `pw_event_type`

| Meta Key | Type | Default | Notes |
|---|---|---|---|
| `_pw_property_id` | integer | `0` | |
| `_pw_venue_id` | integer | `0` | FK → `pw_meeting_room` |
| `_pw_description` | string | `''` | |
| `_pw_start_datetime` | string | `''` | `Y-m-d H:i:s` |
| `_pw_end_datetime` | string | `''` | `Y-m-d H:i:s` |
| `_pw_capacity` | integer | `0` | |
| `_pw_price_from` | number | `0` | |
| `_pw_booking_url` | string | `''` | |
| `_pw_recurrence_rule` | string | `''` | iCal RRULE string (e.g. `FREQ=WEEKLY;BYDAY=SA`) |
| `_pw_organiser_name` | string | `''` | Required for schema.org Event |
| `_pw_organiser_url` | string | `''` | Required for schema.org Event |
| `_pw_event_status` | string | `'EventScheduled'` | schema.org: `EventScheduled` \| `EventCancelled` \| `EventPostponed` \| `EventRescheduled` |
| `_pw_event_attendance_mode` | string | `'OfflineEventAttendanceMode'` | schema.org: `OfflineEventAttendanceMode` \| `OnlineEventAttendanceMode` \| `MixedEventAttendanceMode` |
| `_pw_gallery` | array\<integer\> | — | |

---

## Taxonomies

| Taxonomy | Post Type | Label |
|---|---|---|
| `pw_bed_type` | `pw_room_type` | Bed Types |
| `pw_view_type` | `pw_room_type` | View Types |
| `pw_meal_period` | `pw_restaurant` | Meal Periods |
| `pw_treatment_type` | `pw_spa` | Treatment Types |
| `pw_av_equipment` | `pw_meeting_room` | AV Equipment |
| `pw_feature_group` | `pw_feature` | Feature Groups |
| `pw_nearby_type` | `pw_nearby` | Location Types |
| `pw_transport_mode` | `pw_nearby` | Transport Modes |
| `pw_experience_category` | `pw_experience` | Experience Categories |
| `pw_event_type` | `pw_event` | Event Types |

All taxonomies: non-hierarchical, `show_in_rest: true`, `show_admin_column: true`, `rewrite: false`.

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
  ├── pw_experience      (_pw_property_id)
  └── pw_event           (_pw_property_id, _pw_venue_id → pw_meeting_room)

pw_offer               (_pw_parent_type + _pw_parent_id → pw_property | pw_restaurant | pw_spa)
pw_faq                 (_pw_connected_to[] → pw_property | pw_restaurant | pw_meeting_room | pw_spa)
```
