# Portico Webworks — Page sections reference

This document defines the sections on every page type in the Portico Hotel Manager template system. It covers all CPT listing pages, all CPT outlet singulars, static pages, and the property singular (multi-property mode).

Sections are listed in render order (top to bottom). Every page inherits the global header and footer — these are not repeated per page.

---

## Architecture overview

The plugin owns the full template layer following a WooCommerce-style architecture:

- **Plugin:** CPTs, meta fields, taxonomies, REST API, PHP template files, hook architecture, default minimal HTML output, filterable string helpers
- **Theme:** Styling, GenerateBlocks patterns that override default template parts, all visual design
- **Template override:** Drop a file into `{theme}/portico-webworks/{template-part}.php` to override any part without touching the plugin

Every top-level template calls `get_header()` and `get_footer()` so the active theme wraps all plugin templates with its navigation and styles.

Every section boundary fires `pw_before_{section}` and `pw_after_{section}` action hooks. Developers inject content between sections by hooking into these — no template file editing required.

CTA labels are not hardcoded. They go through `pw_get_cta_label( $post_type, $post_id )` with a `pw_cta_label` filter. Per-CPT defaults are defined in `includes/template-strings.php`. A developer overrides any label in `functions.php` with `add_filter( 'pw_cta_label', ... )`.

---

## Content source column key

| Code | Meaning |
|---|---|
| `plugin-meta` | Pulled from a registered plugin meta field — output by the PHP template part |
| `plugin-tax` | Pulled from a plugin taxonomy term |
| `plugin-query` | Query run by a helper function in `includes/template-data.php`, output by the template part |
| `wp-core` | WordPress core field (post title, post content, featured image, etc.) |
| `wp-page` | Standard WordPress page — edited in the block editor, no plugin involvement |
| `filtered-string` | Output via `pw_get_*()` helper in `includes/template-strings.php`. Developer overrides with `add_filter()` |
| `external` | Third-party embed (map, booking engine, review widget) |
| `computed` | Derived at render time from multiple meta fields (e.g. formatted date range, address string) |
| `new-meta` | **New meta field required** — not yet in the data model |

---

## Global: Header (all pages)

Sticky. Rendered by the active theme. Plugin provides data only.

| Section | Purpose | Content source |
|---|---|---|
| Announcement bar | Time-bounded notice above the nav. Renders only when active and within start/end dates | `plugin-meta`: `_pw_announcement_active`, `_pw_announcement_text`, `_pw_announcement_start`, `_pw_announcement_end` on `pw_property`. Template: `templates/global/announcement-bar.php` |
| Logo | Links to homepage or property root in multi-property mode | `wp-core` (site logo or theme setting) |
| Primary navigation | 5–7 items max. Covers key sections for that property | `wp-core` (nav menu) |
| Primary CTA button | High-contrast button. Always visible — never hidden in hamburger menu. Label is per-property filterable | `filtered-string`: `pw_get_cta_label()`; URL from `new-meta`: `_pw_booking_engine_url` on `pw_property` |
| Utility bar (optional) | Phone number | `plugin-meta`: `pw_contact` (`_pw_phone`) |

---

## Global: Footer (all pages)

Rendered by the active theme. Plugin provides data only.

| Section | Purpose | Content source |
|---|---|---|
| Property name | Brand anchor at page end | `wp-core` (post title) |
| Navigation links | Key pages: rooms, dining, offers, contact | `wp-core` (nav menu) |
| Contact details | Address, phone, email | `computed` from `_pw_address_line_1`, `_pw_city`, `_pw_country`; `plugin-meta` via `pw_contact` |
| Social links | Active channels only | `plugin-meta`: `_pw_social_*` fields on `pw_property` |
| Booking CTA | Last chance to convert before exit | `filtered-string`: `pw_get_cta_label()`; `new-meta`: `_pw_booking_engine_url` |
| Legal | Privacy policy, terms, cookie notice | `wp-core` (nav menu linking to WP pages) |

---

## 1. Property singular (`/leela-residency`)

*Multi-property mode only. Functions as the homepage for that specific property.*
Template: `templates/single-pw_property.php`

| Section | Purpose | Content source |
|---|---|---|
| Hero | Full-width image. Property name as H1, short positioning line | `plugin-meta`: `_pw_gallery[0]`; `wp-core` (post title, excerpt) |
| Booking widget | Date picker, guest count, check availability CTA | `external` (booking engine embed); `new-meta`: `_pw_booking_engine_url` |
| Property introduction | 2–3 sentences on what makes this property distinct | `wp-core` (post excerpt) |
| Highlights grid | 3–4 cards covering the property's strongest selling points | `new-meta`: `_pw_highlights` repeatable group on `pw_property` — fields: `title`, `description`, `icon`, `attachment_id` |
| Rooms preview | 2–4 room type cards with key details and "View all rooms" link | `plugin-query`: `pw_property_get_room_preview( $property_id )` in `template-data.php` |
| Dining preview | Restaurant/outlet cards if dining is a key feature | `plugin-query`: `pw_property_get_restaurant_preview( $property_id )` |
| Experiences / amenities | Spa, activities, meetings — visual cards | `plugin-query`: `pw_property_get_experience_preview( $property_id )` |
| Offers strip | 1–3 active offers. Drives direct booking over OTA | `plugin-query`: `pw_property_get_active_offers( $property_id )` — filtered where `_pw_valid_to` ≥ today |
| Reviews / social proof | Aggregate rating + 2–3 guest quotes | `new-meta`: `_pw_review_embed_code` (textarea) on `pw_property` |
| Location | Map embed + distances to key landmarks | `external` (Google Maps embed); `plugin-meta`: `_pw_lat`, `_pw_lng`, `_pw_google_place_id` |
| Bottom booking CTA | Reinforces direct booking before footer | `filtered-string`: `pw_get_cta_label()`; `new-meta`: `_pw_booking_engine_url` |

---

## 2. CPT listing pages

### 2.1 Room types listing (`/rooms`)

Template: `templates/archive-pw_room_type.php`

| Section | Purpose | Content source |
|---|---|---|
| Page header | Section title, short intro line | `new-meta`: `_pw_rooms_section_title`, `_pw_rooms_section_intro` on `pw_property`; falls back to `filtered-string` default |
| Booking widget | Persistent availability check. Sticky on scroll | `external` (booking engine embed) |
| Room cards grid | One card per room type: image, name, occupancy, bed type, size, starting rate, CTA | `plugin-query`: loop on `pw_room_type`; `wp-core` (thumbnail); `plugin-meta`: `_pw_max_occupancy`, `_pw_rate_from`, `_pw_size_sqm`; `plugin-tax`: `pw_bed_type` |
| Filter bar (optional) | Filter by bed type, occupancy if 5+ room types | `plugin-tax`: `pw_bed_type`; `plugin-meta`: `_pw_max_occupancy` |
| Offers callout | Active offers relevant to room bookings | `plugin-query`: `pw_archive_get_room_offers( $property_id )` |
| Bottom booking CTA | Prompt to check availability | `filtered-string`: `pw_get_cta_label( 'pw_room_type' )` |

---

### 2.2 Restaurant listing (`/restaurants`)

Template: `templates/archive-pw_restaurant.php`

| Section | Purpose | Content source |
|---|---|---|
| Page header | "Dining", short intro on the property's food philosophy | `new-meta`: `_pw_dining_section_title`, `_pw_dining_section_intro` on `pw_property`; falls back to `filtered-string` default |
| Restaurant cards grid | One card per outlet: image, name, cuisine, meal periods, description, reservation CTA | `plugin-query`: loop on `pw_restaurant`; `wp-core` (thumbnail, excerpt); `plugin-meta`: `_pw_cuisine_type`, `_pw_reservation_url`; `plugin-tax`: `pw_meal_period` |
| Dining highlights (optional) | Signature dish or flagship outlet callout | `new-meta`: `_pw_dining_highlight_text` on `pw_property`; rendered conditionally if non-empty |
| Bottom CTA | Reservation or enquiry prompt | `filtered-string`: `pw_get_cta_label( 'pw_restaurant' )`; `plugin-meta`: `pw_contact` reservation contact |

---

### 2.3 Spa listing (`/spas`)

Template: `templates/archive-pw_spa.php`

| Section | Purpose | Content source |
|---|---|---|
| Page header | Section title, short wellness positioning line | `new-meta`: `_pw_spa_section_title`, `_pw_spa_section_intro` on `pw_property`; falls back to `filtered-string` default |
| Spa cards grid | One card per spa outlet: image, name, treatment highlights, booking CTA | `plugin-query`: loop on `pw_spa`; `wp-core` (thumbnail, excerpt); `plugin-meta`: `_pw_booking_url` |
| Treatments teaser (optional) | 3–4 signature treatments with brief descriptions | `new-meta`: `_pw_signature_treatments` repeatable group on `pw_spa` — fields: `name`, `duration_min`, `description` |
| Opening hours | Clearly stated | `plugin-meta`: `_pw_operating_hours` on `pw_spa` |
| Bottom booking CTA | Book a treatment or enquire | `filtered-string`: `pw_get_cta_label( 'pw_spa' )` |

---

### 2.4 Meeting rooms listing (`/meetings`)

Template: `templates/archive-pw_meeting_room.php`

| Section | Purpose | Content source |
|---|---|---|
| Page header | "Meetings & events", short MICE intro | `new-meta`: `_pw_meetings_section_title`, `_pw_meetings_section_intro` on `pw_property`; falls back to `filtered-string` default |
| Venue cards grid | One card per meeting room: image, name, capacity, floor area, AV summary | `plugin-query`: loop on `pw_meeting_room`; `wp-core` (thumbnail); `plugin-meta`: `_pw_capacity_theatre`, `_pw_area_sqm`; `plugin-tax`: `pw_av_equipment` |
| Catering / services strip | Brief mention of F&B, AV, and support services | `new-meta`: `_pw_meetings_services_text` on `pw_property`; rendered conditionally if non-empty |
| RFP / enquiry CTA | "Send us a proposal request" | `filtered-string`: `pw_get_cta_label( 'pw_meeting_room' )`; `plugin-meta`: `pw_contact` scoped to `meeting_room` |
| Bottom CTA | Enquiry prompt | `filtered-string` |

---

### 2.5 Experiences listing (`/experiences`)

Template: `templates/archive-pw_experience.php`

| Section | Purpose | Content source |
|---|---|---|
| Page header | "Experiences", short intro | `new-meta`: `_pw_experiences_section_title`, `_pw_experiences_section_intro` on `pw_property`; falls back to `filtered-string` default |
| Experience cards grid | One card per experience: image, name, duration, price, category, CTA | `plugin-query`: loop on `pw_experience`; `wp-core` (thumbnail, excerpt); `plugin-meta`: `_pw_duration_hours`, `_pw_price_from`, `_pw_booking_url`; `plugin-tax`: `pw_experience_category` |
| Category filter (optional) | Filter by category if 6+ experiences | `plugin-tax`: `pw_experience_category` |
| Bottom CTA | Booking or enquiry prompt | `filtered-string`: `pw_get_cta_label( 'pw_experience' )` |

---

### 2.6 Events listing (`/events`)

Template: `templates/archive-pw_event.php`

| Section | Purpose | Content source |
|---|---|---|
| Page header | "Events at [property]" | `new-meta`: `_pw_events_section_intro` on `pw_property`; falls back to `filtered-string` default |
| Upcoming events grid | One card per event: image, name, date, venue, price. Sorted by date ascending | `plugin-query`: `pw_archive_get_upcoming_events( $property_id )`; `plugin-meta`: `_pw_start_datetime_iso8601`, `_pw_price_from`, `_pw_venue_id` |
| Past events (optional) | Collapsed — for social proof | `plugin-query`: `pw_archive_get_past_events( $property_id )` |
| Host an event CTA | Secondary path for private event enquiries | `filtered-string`; `plugin-meta`: `pw_contact` scoped to `meeting_room` |
| Bottom CTA | RSVP or enquiry | `filtered-string` |

---

### 2.7 Offers listing (`/offers`)

Template: `templates/archive-pw_offer.php`

| Section | Purpose | Content source |
|---|---|---|
| Page header | "Special offers", short direct-booking line | `new-meta`: `_pw_offers_section_intro` on `pw_property`; falls back to `filtered-string` default |
| Offer cards grid | One card per offer: image, name, key benefit, validity, terms summary, CTA | `plugin-query`: loop on `pw_offer`; `wp-core` (thumbnail, excerpt); `plugin-meta`: `_pw_discount_type`, `_pw_discount_value`, `_pw_valid_from`, `_pw_valid_to`, `_pw_booking_url` |
| Direct booking callout | Reinforce website-exclusive rates | `plugin-meta`: `_pw_direct_benefits` on `pw_property` |
| Bottom booking CTA | General availability check | `external` (booking widget) |

---

### 2.8 Nearby places listing (`/places`)

Template: `templates/archive-pw_nearby.php`

| Section | Purpose | Content source |
|---|---|---|
| Page header | "Around the property" or "Local area" | `new-meta`: `_pw_places_section_intro` on `pw_property`; falls back to `filtered-string` default |
| Map | Embedded map showing property + nearby places | `external` (Google Maps); `plugin-meta`: `_pw_lat`, `_pw_lng` on each `pw_nearby` |
| Places grid | One card per place: name, category, distance, transport mode | `plugin-query`: loop on `pw_nearby`; `plugin-meta`: `_pw_distance_km`, `_pw_travel_time_min`; `plugin-tax`: `pw_nearby_type`, `pw_transport_mode` |
| Getting here strip | How to reach the property from key hubs | `new-meta`: `_pw_getting_here` repeatable group on `pw_property` — fields: `from_location`, `duration`, `transport_mode`, `description` |

---

### 2.9 Property listing (`/hotels`) — multi-property mode only

Template: `templates/archive-pw_property.php`

| Section | Purpose | Content source |
|---|---|---|
| Page header | Group name, short intro | `wp-core` (site title) |
| Property cards grid | One card per property: image, name, location, star rating, positioning line, CTA | `plugin-query`: loop on `pw_property`; `wp-core` (thumbnail, excerpt); `plugin-meta`: `_pw_star_rating`, `_pw_city`, `_pw_country`; `plugin-tax`: `pw_property_type` |
| Filter bar (optional) | Filter by location, property type | `plugin-tax`: `pw_property_type`; `plugin-meta`: `_pw_country` |
| Group enquiry CTA | Multi-property bookings or group travel | `filtered-string` |

---

## 3. CPT outlet singulars

### 3.1 Single room type (`/room/deluxe-king`)

Template: `templates/single-pw_room_type.php`
Parts: `templates/single-room/*.php`

| Section | Purpose | Content source |
|---|---|---|
| Hero | Swipeable image gallery. Room name as H1 | `plugin-meta`: `_pw_gallery`; `wp-core` (post title) |
| Booking widget | Date + guest check. Sticky on scroll | `external` (booking engine embed) |
| Room overview | Occupancy, bed type, size. Icon+label format | `plugin-meta`: `_pw_max_occupancy`, `_pw_max_adults`, `_pw_max_children`, `_pw_size_sqm`; `plugin-tax`: `pw_bed_type`, `pw_view_type` |
| Description | 2–3 paragraphs written for the guest | `wp-core` (post content via `the_content()`) |
| Amenities list | Grouped by category: in-room tech, bathroom, comfort, views | `plugin-query`: `pw_feature` posts filtered by `_pw_features` IDs; grouped by `plugin-tax`: `pw_feature_group` |
| Image gallery | Additional room photos below fold | `plugin-meta`: `_pw_gallery` |
| Rates | Starting rate or rate range | `plugin-meta`: `_pw_rate_from`, `_pw_rate_to`; or `_pw_rates` repeatable group |
| Upgrade prompt (optional) | 1–2 higher-tier room cards | `plugin-query`: `pw_room_get_upgrade_options( $post_id )` — hook `pw_room_upgrade_prompt_body` to populate |
| Related offers | Active offers applicable to this room type | `plugin-query`: `pw_room_get_related_offers( $post_id )` |
| Bottom booking CTA | Direct check availability CTA | `filtered-string`: `pw_get_cta_label( 'pw_room_type', $post_id )` |

---

### 3.2 Single restaurant (`/restaurant/olive-tree`)

Template: `templates/single-pw_restaurant.php`
Parts: `templates/single-restaurant/*.php`

| Section | Purpose | Content source |
|---|---|---|
| Hero | Restaurant image. Outlet name as H1, cuisine type, meal periods | `wp-core` (thumbnail, post title); `plugin-meta`: `_pw_cuisine_type`; `plugin-tax`: `pw_meal_period` |
| Introduction | Atmosphere, chef focus, signature style | `wp-core` (post content via `the_content()`) |
| Menu preview | 3–6 signature dishes. Links to full menu | `new-meta`: `_pw_menu_highlights` repeatable group — fields: `dish_name`, `description`, `attachment_id`; `plugin-meta`: `_pw_menu_url` |
| Opening hours | Day-wise hours | `plugin-meta`: `_pw_operating_hours` |
| Reservation CTA | Primary action | `plugin-meta`: `_pw_reservation_url`; fallback to `pw_contact` scoped to this restaurant |
| Ambience gallery | Interior and food photography | `plugin-meta`: `_pw_gallery` |
| Private dining (optional) | If restaurant takes private bookings | `new-meta`: `_pw_private_dining_note`; rendered conditionally if non-empty |
| Location within property | Floor, wing, or building | `plugin-meta`: `_pw_location` |
| FAQ (optional) | FAQs scoped to this outlet | `plugin-query`: `pw_restaurant_get_faqs( $post_id )` in `template-data.php` |
| Bottom CTA | Reservation or enquiry | `filtered-string`: `pw_get_cta_label( 'pw_restaurant', $post_id )` |

---

### 3.3 Single spa (`/spa/nirvana-spa`)

Template: `templates/single-pw_spa.php`
Parts: `templates/single-spa/*.php`

| Section | Purpose | Content source |
|---|---|---|
| Hero | Spa imagery. Outlet name as H1, positioning line | `wp-core` (thumbnail, post title, excerpt) |
| Introduction | Wellness philosophy | `wp-core` (post content via `the_content()`) |
| Signature treatments | 3–5 featured treatments: name, duration, description | `new-meta`: `_pw_signature_treatments` repeatable group — fields: `name`, `duration_min`, `description` |
| Full treatments menu (optional) | Linked PDF or separate page | `plugin-meta`: `_pw_menu_url` |
| Facilities list | Pool, steam, sauna, relaxation lounge | `new-meta`: `_pw_spa_facilities` repeatable group — fields: `name`, `icon` |
| Opening hours | Day-wise | `plugin-meta`: `_pw_operating_hours` |
| Booking CTA | Book a treatment | `plugin-meta`: `_pw_booking_url`; fallback to `pw_contact` scoped to this spa |
| Gallery | Treatment rooms, facilities, ambience | `plugin-meta`: `_pw_gallery` |
| FAQ (optional) | FAQs scoped to this spa | `plugin-query`: `pw_spa_get_faqs( $post_id )` |
| Bottom CTA | Book or enquire | `filtered-string`: `pw_get_cta_label( 'pw_spa', $post_id )` |

---

### 3.4 Single meeting room (`/meeting/deccan-ballroom`)

Template: `templates/single-pw_meeting_room.php`
Parts: `templates/single-meeting-room/*.php`

| Section | Purpose | Content source |
|---|---|---|
| Hero | Venue image. Room name as H1 | `plugin-meta`: `_pw_gallery[0]`; `wp-core` (post title) |
| Capacity table | Theatre, classroom, boardroom, U-shape | `plugin-meta`: `_pw_capacity_theatre`, `_pw_capacity_classroom`, `_pw_capacity_boardroom`, `_pw_capacity_ushape` |
| Room specs | Floor area, natural light, AV equipment | `plugin-meta`: `_pw_area_sqm`, `_pw_natural_light`; `plugin-tax`: `pw_av_equipment` |
| Description | What makes this room suited for its target events | `wp-core` (post content via `the_content()`) |
| Catering options | Coffee breaks, buffet, seated dinner availability | `new-meta`: `_pw_catering_note`; rendered conditionally if non-empty |
| Floor plan (optional) | Downloadable floor plan | `plugin-meta`: `_pw_floor_plan` (attachment ID) — rendered as image + download link |
| Setup gallery | Photos of different configurations | `plugin-meta`: `_pw_gallery` |
| Adjacent venues (optional) | Breakout rooms or pre-function spaces | `plugin-meta`: `_pw_prefunction_area_sqm`; `plugin-query`: sibling `pw_meeting_room` posts |
| RFP CTA | "Send a proposal request" — primary action | `filtered-string`: `pw_get_cta_label( 'pw_meeting_room', $post_id )`; `plugin-meta`: `pw_contact` scoped to `meeting_room` |
| FAQ (optional) | FAQs scoped to this venue | `plugin-query`: `pw_meeting_room_get_faqs( $post_id )` |
| Bottom CTA | Enquiry or proposal | `filtered-string` |

---

### 3.5 Single experience (`/experience/sunrise-kayaking`)

Template: `templates/single-pw_experience.php`
Parts: `templates/single-experience/*.php`

| Section | Purpose | Content source |
|---|---|---|
| Hero | Experience image. Name as H1, category tag, duration | `wp-core` (thumbnail, post title); `plugin-tax`: `pw_experience_category`; `plugin-meta`: `_pw_duration_hours` |
| Overview | What the experience involves — written for the guest | `wp-core` (post content via `the_content()`) |
| Key details strip | Duration, group size, price, what's included | `plugin-meta`: `_pw_duration_hours`, `_pw_price_from`; `new-meta`: `_pw_group_size_max`, `_pw_inclusions` |
| What to expect | Narrative or step-by-step walkthrough | `new-meta`: `_pw_what_to_expect` (wysiwyg) |
| Inclusions / exclusions | Brief list | `new-meta`: `_pw_inclusions`, `_pw_exclusions` |
| Booking CTA | "Book this experience" | `plugin-meta`: `_pw_booking_url`; fallback to `pw_contact` |
| Related experiences (optional) | 2–3 from same category | `plugin-query`: `pw_experience_get_related( $post_id )` |
| Bottom CTA | Book or enquire | `filtered-string`: `pw_get_cta_label( 'pw_experience', $post_id )` |

---

### 3.6 Single event (`/event/diwali-gala`)

Template: `templates/single-pw_event.php`
Parts: `templates/single-event/*.php`

| Section | Purpose | Content source |
|---|---|---|
| Hero | Event image. Event name as H1, date, venue | `wp-core` (thumbnail, post title); `plugin-meta`: `_pw_start_datetime_iso8601`, `_pw_venue_id` |
| Event description | What the event is, who it's for, what to expect | `wp-core` (post content via `the_content()`) |
| Key details strip | Date, time, venue, dress code, price per person | `plugin-meta`: `_pw_start_datetime_iso8601`, `_pw_end_datetime_iso8601`, `_pw_price_from`, `_pw_venue_id`; `new-meta`: `_pw_dress_code` |
| Programme (optional) | Schedule if available | `new-meta`: `_pw_programme` (wysiwyg); rendered conditionally |
| Ticket / RSVP CTA | Primary action — buy ticket or RSVP | `plugin-meta`: `_pw_booking_url` |
| Venue details | Room, capacity, seating arrangement | `plugin-query`: resolved `pw_meeting_room` from `_pw_venue_id` |
| Add-on options (optional) | Room package, early arrival, special seating | `new-meta`: `_pw_event_addons` repeatable group — fields: `label`, `description`, `price` |
| Bottom CTA | RSVP or ticket purchase | `filtered-string`: `pw_get_cta_label( 'pw_event', $post_id )` |

---

### 3.7 Single offer (`/offer/advance-purchase`)

Template: `templates/single-pw_offer.php`
Parts: `templates/single-offer/*.php`

| Section | Purpose | Content source |
|---|---|---|
| Hero | Lifestyle image matching the offer. Offer name as H1 | `wp-core` (thumbnail, post title) |
| Offer summary | Benefit in plain language: what you get, how much you save | `wp-core` (post excerpt) |
| Key terms strip | Valid dates, minimum stay, advance purchase requirement, applicable room types | `plugin-meta`: `_pw_valid_from`, `_pw_valid_to`, `_pw_minimum_stay_nights`, `_pw_room_types` |
| What's included | Specific inclusions | `wp-core` (post content via `the_content()`) |
| Booking CTA | "Book this offer" — feeds booking engine with offer code pre-applied | `plugin-meta`: `_pw_booking_url`; `new-meta`: `_pw_promo_code` |
| Fine print | Full terms — collapsed by default, expandable | `new-meta`: `_pw_terms_and_conditions` |
| Related offers (optional) | 2–3 other active offers | `plugin-query`: `pw_offer_get_related( $post_id )` |
| Bottom CTA | Book now | `filtered-string`: `pw_get_cta_label( 'pw_offer', $post_id )` |

---

### 3.8 Single nearby place (`/place/cubbon-park`)

Template: `templates/single-pw_nearby.php`
Parts: `templates/single-nearby/*.php`

| Section | Purpose | Content source |
|---|---|---|
| Page header | Place name as H1, category tag | `wp-core` (post title); `plugin-tax`: `pw_nearby_type` |
| Description | What this place is and why guests visit it | `wp-core` (post content via `the_content()`) |
| Key details strip | Distance from property, travel time, transport options | `plugin-meta`: `_pw_distance_km`, `_pw_travel_time_min`; `plugin-tax`: `pw_transport_mode` |
| Map | Embedded map showing property + this place | `external` (Google Maps); `plugin-meta`: `_pw_lat`, `_pw_lng`, `_pw_place_url` |
| Getting there | Transport options from property | `plugin-meta`: `_pw_travel_time_min`; `plugin-tax`: `pw_transport_mode` |
| Concierge tip (optional) | Practical note — best time to visit, what to bring | `new-meta`: `_pw_concierge_tip`; rendered conditionally if non-empty |
| Related places (optional) | 2–3 nearby places in same category | `plugin-query`: `pw_nearby_get_related( $post_id )` |

---

## 4. Policies page (`/policies`)

Plugin-managed page. Created by `pw_run_page_installer()` with slug `policies`.

| Section | Purpose | Content source |
|---|---|---|
| Page header | "Hotel policies" or "Policies & guidelines" | `new-meta`: `_pw_policies_page_intro` on `pw_property`; falls back to `filtered-string` default |
| Policy groups | Grouped by `pw_policy_type`: check-in, check-out, cancellation, pet, child, payment, smoking, custom | `plugin-query`: `pw_policy` posts filtered by `_pw_property_id`; grouped by `plugin-tax`: `pw_policy_type`; sorted by `plugin-meta`: `_pw_display_order` |
| Highlighted policies strip (optional) | Key policies surfaced prominently | `plugin-meta`: `_pw_is_highlighted` filter on `pw_policy` |
| Contact CTA | "Questions about our policies? Contact us" | `filtered-string`; links to `/contact` |

---

## 5. FAQ page (`/faq`)

Plugin-managed. General property-level FAQs. Outlet-scoped FAQs are embedded within their respective outlet singulars (sections 3.2, 3.3, 3.4).

| Section | Purpose | Content source |
|---|---|---|
| Page header | "Frequently asked questions" | `new-meta`: `_pw_faq_page_intro` on `pw_property`; falls back to `filtered-string` default |
| FAQ accordion | `pw_faq` records for this property. Q = post title, A = `_pw_answer`. Grouped by topic | `plugin-query`: `pw_faq` filtered by `_pw_property_id`; sorted by `_pw_display_order`; grouped by `new-meta`: `_pw_faq_topic` |
| Contact CTA | "Didn't find your answer?" | `filtered-string`; links to `/contact` |

---

## 6. Static pages

### 6.1 Gallery (`/gallery`)

| Section | Purpose | Content source |
|---|---|---|
| Page header | "Gallery" or "Photo gallery" | `wp-page` (block editor) |
| Category tabs or filter | Rooms, dining, spa, pool, events, exterior | `new-meta`: `_pw_gallery_categories` repeatable group — fields: `category_label`, `attachment_ids` |
| Image grid | Masonry or uniform grid. Click to full-screen lightbox | Sourced from `_pw_gallery_categories`; or aggregated from all CPT `_pw_gallery` fields |
| Video section (optional) | Property video embed | `new-meta`: `_pw_video_embed_url`; rendered conditionally |
| Bottom booking CTA | Gallery browsers are high-intent — capture them | `filtered-string`: `pw_get_cta_label()`; `new-meta`: `_pw_booking_engine_url` |

---

### 6.2 Fact sheet (`/fact-sheet`)

Plugin-managed page. Created by `pw_run_page_installer()`. Starter markup from `gb-pro-markup-samples.html`.

| Section | Purpose | Content source |
|---|---|---|
| Page header | Property name + "Fact sheet" | `wp-core` (post title of `pw_property`) |
| Property overview | Location, category, year opened, total rooms, total outlets | `plugin-meta`: `_pw_city`, `_pw_country`, `_pw_year_established`, `_pw_total_rooms`, `_pw_star_rating`; `plugin-tax`: `pw_property_type` |
| Rooms summary table | Room types, count, size range, occupancy | `plugin-query`: loop on `pw_room_type`; `plugin-meta`: `_pw_size_sqm`, `_pw_max_occupancy` |
| Facilities summary | F&B, spa, pool, meeting rooms — brief per category | `plugin-query`: counts from `pw_restaurant`, `pw_spa`, `pw_meeting_room`; `plugin-meta`: `_pw_pools` |
| Contact details | Reservations phone, email, address | `plugin-meta`: `pw_contact` resolution; `computed` from `_pw_address_*` fields |
| Download CTA | PDF version for travel agents and press | `new-meta`: `_pw_fact_sheet_pdf` (attachment ID); rendered as download link if set |

---

### 6.3 Contact (`/contact`)

| Section | Purpose | Content source |
|---|---|---|
| Page header | "Contact us" or "Get in touch" | `wp-page` (block editor) |
| Contact form | Name, email, phone, subject, message | `wp-page` (WPForms, CF7, or similar — not plugin-managed) |
| Contact details | Phone, email, address per department | `plugin-meta`: `pw_contact` records rendered by `_pw_label`, `_pw_phone`, `_pw_email` |
| Map | Embedded map with property pin | `external` (Google Maps); `plugin-meta`: `_pw_lat`, `_pw_lng`, `_pw_google_place_id` |
| Getting here | Directions from airport, city centre, station | `new-meta`: `_pw_getting_here` repeatable group (shared with `/places`) |
| Department directory (optional) | Separate contacts for F&B, spa, meetings | `plugin-meta`: `pw_contact` records filtered by `_pw_scope_cpt` |

---

### 6.4 About (`/about`)

| Section | Purpose | Content source |
|---|---|---|
| Hero | Property image, page title | `wp-page` (block editor) |
| Property story | History, founding, what makes this property unique | `wp-page` (block editor) |
| Team / ownership (optional) | General manager or ownership group introduction | `wp-page` (block editor) |
| Certifications & awards | Recognition from external bodies | `plugin-meta`: `_pw_certifications` repeatable group |
| Sustainability section (optional) | Environmental and social commitments | `plugin-meta`: `_pw_sustainability_items` |
| Accessibility information | Facilities for guests with accessibility needs | `plugin-meta`: `_pw_accessibility_items` |
| Bottom booking CTA | Convert interest into booking | `filtered-string`: `pw_get_cta_label()`; `new-meta`: `_pw_booking_engine_url` |

---

### 6.5 Privacy policy (`/privacy-policy`)

| Section | Purpose | Content source |
|---|---|---|
| Page content | Full privacy policy text | `wp-page` (standard WordPress page, block editor) |

---

### 6.6 Terms and conditions (`/terms`)

| Section | Purpose | Content source |
|---|---|---|
| Page content | Terms of use, booking terms | `wp-page` (standard WordPress page, block editor) |

---

### 6.7 Cookie policy (`/cookie-policy`)

| Section | Purpose | Content source |
|---|---|---|
| Page content | Cookie usage disclosure | `wp-page` (standard WordPress page, block editor) |

---

## New meta fields required

Fields not yet in the data model. Grouped by CPT.

### `pw_property`

| Meta key | Type | Notes |
|---|---|---|
| `_pw_announcement_active` | boolean | Added in refactor. Toggle to show/hide announcement bar |
| `_pw_announcement_text` | string | Added in refactor. Stored as sanitized HTML (`wp_kses_post`) |
| `_pw_announcement_start` | string | Added in refactor. `Y-m-d H:i:s`. Bar not shown before this datetime |
| `_pw_announcement_end` | string | Added in refactor. `Y-m-d H:i:s`. Bar not shown after this datetime |
| `_pw_booking_engine_url` | string | URL for all primary CTA buttons — header, footer, booking widgets |
| `_pw_highlights` | repeatable group | Fields: `title`, `description`, `icon`, `attachment_id` |
| `_pw_review_embed_code` | textarea | Third-party review widget embed code |
| `_pw_getting_here` | repeatable group | Fields: `from_location`, `duration`, `transport_mode`, `description`. Shared by `/places` and `/contact` |
| `_pw_rooms_section_title` | string | H1 override for `/rooms`. Falls back to filtered default |
| `_pw_rooms_section_intro` | string | Intro line on `/rooms` |
| `_pw_dining_section_title` | string | H1 override for `/restaurants` |
| `_pw_dining_section_intro` | string | Intro line for `/restaurants` |
| `_pw_dining_highlight_text` | string | Optional flagship dining callout on restaurant listing |
| `_pw_spa_section_title` | string | H1 override for `/spas` |
| `_pw_spa_section_intro` | string | Intro line for `/spas` |
| `_pw_meetings_section_title` | string | H1 override for `/meetings` |
| `_pw_meetings_section_intro` | string | Intro line for `/meetings` |
| `_pw_meetings_services_text` | string | Catering and services summary on meetings listing |
| `_pw_experiences_section_title` | string | H1 override for `/experiences` |
| `_pw_experiences_section_intro` | string | Intro line for `/experiences` |
| `_pw_events_section_intro` | string | Intro line for `/events` |
| `_pw_offers_section_intro` | string | Intro line for `/offers` |
| `_pw_places_section_intro` | string | Intro line for `/places` |
| `_pw_policies_page_intro` | string | Intro line for `/policies` |
| `_pw_faq_page_intro` | string | Intro line for `/faq` |
| `_pw_video_embed_url` | string | Property video URL (YouTube or Vimeo). Used on `/gallery` |
| `_pw_gallery_categories` | repeatable group | Fields: `category_label`, `attachment_ids`. Used for filtered gallery tabs |
| `_pw_fact_sheet_pdf` | integer | Attachment ID of downloadable fact sheet PDF |

### `pw_restaurant`

| Meta key | Type | Notes |
|---|---|---|
| `_pw_menu_highlights` | repeatable group | Fields: `dish_name`, `description`, `attachment_id` |
| `_pw_private_dining_note` | string | Rendered conditionally if non-empty |

### `pw_spa`

| Meta key | Type | Notes |
|---|---|---|
| `_pw_signature_treatments` | repeatable group | Fields: `name`, `duration_min`, `description`. Used on both spa listing and spa singular |
| `_pw_spa_facilities` | repeatable group | Fields: `name`, `icon`. Pool, steam, sauna, relaxation lounge, etc. |

### `pw_meeting_room`

| Meta key | Type | Notes |
|---|---|---|
| `_pw_catering_note` | string | Short description of catering options for this venue |

### `pw_experience`

| Meta key | Type | Notes |
|---|---|---|
| `_pw_group_size_max` | integer | Maximum group size |
| `_pw_what_to_expect` | wysiwyg | Narrative walkthrough for the guest |
| `_pw_inclusions` | textarea | What is included in the price |
| `_pw_exclusions` | textarea | What is not included |

### `pw_event`

| Meta key | Type | Notes |
|---|---|---|
| `_pw_dress_code` | string | e.g. Smart casual, Black tie |
| `_pw_programme` | wysiwyg | Event schedule. Rendered conditionally |
| `_pw_event_addons` | repeatable group | Fields: `label`, `description`, `price` |

### `pw_offer`

| Meta key | Type | Notes |
|---|---|---|
| `_pw_promo_code` | string | Promo or rate code to pre-apply in the booking engine |
| `_pw_terms_and_conditions` | textarea | Full offer T&Cs. Collapsed/expandable on the page |

### `pw_nearby`

| Meta key | Type | Notes |
|---|---|---|
| `_pw_concierge_tip` | textarea | Practical note for guests — best time to visit, what to bring |

### `pw_faq`

| Meta key | Type | Notes |
|---|---|---|
| `_pw_faq_topic` | string (select) | Groups FAQs on the `/faq` listing page. Values: `general`, `rooms`, `dining`, `spa`, `meetings`, `bookings`. Complements `_pw_connected_to` which handles outlet-level embedding |

---

## Notes

**Template override system:** Drop any file into `{theme}/portico-webworks/{template-part}.php` to override without touching plugin files. Theme overrides survive plugin updates.

**Hook injection:** To insert a component between two sections (e.g. a review widget between amenities and gallery on a room page), hook into `pw_after_room_amenities`. No template editing required.

**CTA label overrides:** Default labels per CPT are defined in `includes/template-strings.php`. Override for a specific CPT or post in `functions.php`:
```php
add_filter( 'pw_cta_label', function( $label, $post_type, $post_id ) {
    if ( $post_type === 'pw_restaurant' ) return 'Make a reservation';
    return $label;
}, 10, 3 );
```

**Booking widget vs reservation CTA:** On room and offer pages, the primary action is a booking widget feeding the booking engine. On dining, spa, experience, and meeting pages, the primary action is a reservation or enquiry CTA. Do not embed a room booking widget on outlet pages.

**FAQ placement:** Property-wide FAQs live at `/faq`. Outlet-specific FAQs (restaurant, spa, meeting room) are embedded within the relevant outlet singular using `_pw_connected_to` connections on `pw_faq`. Both use the same CPT.

**Policies page:** Should be added to `pw_run_page_installer()` with slug `policies`, alongside `fact-sheet`.

**Section intro fields:** All `_pw_*_section_intro` and `_pw_*_section_title` fields on `pw_property` are optional overrides. If empty, the section header falls back to the filtered string default from `includes/template-strings.php`. The site does not break if these fields are unpopulated.

**Announcement bar:** Rendered via `wp_body_open` hook from `templates/global/announcement-bar.php`. Theme can remove with `remove_action( 'wp_body_open', ... )`. Requires GeneratePress (or any theme) to call `wp_body_open()` in its header template.
