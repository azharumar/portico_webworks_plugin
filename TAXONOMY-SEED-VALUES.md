# Taxonomy seed values (canonical)

**Source of truth:** `includes/taxonomy-seeds.php` — function `pw_get_taxonomy_seed_terms()`.

On install / “Add default terms” / “Reinstall default taxonomy terms”, `pw_seed_taxonomy_terms()` inserts each name only if `term_exists()` is false (nothing removed or renamed).

`DATA-STRUCTURE.md` describes how seeding interacts with options and admin notices.

---

## `pw_property_type` — `pw_property`

| Term |
|------|
| Hotel |
| Resort |
| Boutique Hotel |
| Motel |
| Lodge |
| Inn |
| Bed & Breakfast |
| Villa |
| Apartments |
| Hostel |
| Serviced Apartment |
| Guest House |
| Ranch |
| Cabin |
| Camp |

---

## `pw_policy_type` — `pw_policy`

| Term |
|------|
| Check-in |
| Check-out |
| Cancellation |
| Pet |
| Child |
| Payment |
| Smoking |
| Custom |

---

## `pw_bed_type` — `pw_room_type`

| Term |
|------|
| Twin |
| Double |
| Queen |
| King |
| Single |
| Sofa Bed |
| Bunk Bed |
| Murphy Bed |
| Rollaway |
| Crib |

---

## `pw_view_type` — `pw_room_type`

| Term |
|------|
| Ocean |
| Sea |
| Beach |
| Pool |
| Garden |
| City |
| Mountain |
| Lake |
| Courtyard |
| Partial Ocean |
| Partial Sea |
| No View |

---

## `pw_meal_period` — `pw_restaurant`

| Term |
|------|
| Breakfast |
| Brunch |
| Lunch |
| Dinner |
| All-day Dining |
| Afternoon Tea |
| Late Night |
| 24-Hour |

---

## `pw_treatment_type` — `pw_spa`

| Term |
|------|
| Massage |
| Facial |
| Body Wrap |
| Body Scrub |
| Manicure |
| Pedicure |
| Hair |
| Waxing |
| Aromatherapy |
| Hot Stone |
| Reflexology |
| Couples Treatment |
| Pre/Post Natal |

---

## `pw_av_equipment` — `pw_meeting_room`

| Term |
|------|
| Projector |
| Screen |
| Video Conferencing |
| Microphone |
| PA System |
| Whiteboard |
| Flip Chart |
| HDMI Connection |
| Wireless Presentation |
| Recording |

---

## `pw_feature_group` — `pw_feature`

| Term |
|------|
| Bedding |
| Bathroom |
| In-room |
| Entertainment |
| Climate |
| Connectivity |
| Outdoor |

---

## `pw_nearby_type` — `pw_nearby`

| Term |
|------|
| Beach |
| Airport |
| Train Station |
| Attraction |
| Shopping |
| Dining |
| Park |
| Museum |
| Golf |
| Hospital |
| Bank/ATM |
| Supermarket |

---

## `pw_transport_mode` — `pw_nearby`

| Term |
|------|
| Walk |
| Drive |
| Taxi |
| Public Transport |
| Shuttle |
| Boat |
| Bicycle |

---

## `pw_experience_category` — `pw_experience`

| Term |
|------|
| Adventure |
| Cultural |
| Culinary |
| Wellness |
| Water Sports |
| Land Activities |
| Kids |
| Nightlife |
| Shopping |
| Nature |

---

## `pw_event_type` — `pw_event`

| Term |
|------|
| Wedding |
| Conference |
| Meeting |
| Seminar |
| Gala |
| Private Dining |
| Team Building |
| Product Launch |
| Social Event |
| Exhibition |

---

## Not in seed lists (by design)

| Item | Reason |
|------|--------|
| `pw_event_organiser` | Property- and brand-specific; add terms as needed per property |

---

Contacts are **`pw_contact` posts**, not property meta — see `DATA-STRUCTURE.md` and `includes/contact-resolver.php`.
