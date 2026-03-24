# Portico Webworks — URL Architecture

---

## Tier Map

```
[property] / [section-base] / [outlet-slug]
     ↑              ↑               ↑
  required       required        optional
  multi only    configurable    if outlet
```

Single-property mode collapses the property tier entirely.
Multi-property mode: property slug sits at root.

---

## Section Bases

Global plugin settings. All defaults are configurable. Shared across all properties.

Each section has two base forms:
- **Plural base** → listing page (`/rooms`)
- **Singular base** → outlet URL prefix (`/room/deluxe-king`)

Singular base is the depluralized form of the plural base by default.
Both are configurable independently.

| CPT | Default plural base | Default singular base | Listing page optional? |
|---|---|---|---|
| `pw_property` | `hotels` | `hotel` | Yes |
| `pw_room_type` | `rooms` | `room` | Yes |
| `pw_restaurant` | `restaurants` | `restaurant` | Yes |
| `pw_spa` | `spas` | `spa` | Yes |
| `pw_meeting_room` | `meetings` | `meeting` | Yes |
| `pw_experience` | `experiences` | `experience` | Yes |
| `pw_event` | `events` | `event` | Yes |
| `pw_offer` | `offers` | `offer` | Yes |
| `pw_nearby` (URL base: places / place) | `places` | `place` | Yes |

> **Note:** The CPT is registered as `pw_nearby` in code. The URL-facing base slugs are `places` (plural) and `place` (singular), configured via `pw_default_section_bases()` in `includes/permalink-config.php`. The CPT key is not renamed to avoid a database migration.

> **Listing page optional** means the listing page (`/rooms`) can be disabled per property.
> A disabled listing page is simply not available — it returns 404.
> Outlet singulars (`/room/deluxe-king`) always resolve regardless.
>
> **`pw_property` listing page is also optional.** A hotel group may prefer not to expose
> a property listing page and instead link each property directly from navigation.

---

## URL Rules

- No trailing slashes on any URL
- All bases are lowercase, hyphenated slugs (`sanitize_title` format)
- Section bases are **reserved slugs** — see Reserved Slugs section
- Rewrite rule registration order matters — see Rule Registration Order section
- All 301 redirects preserve query strings — see Redirect Query String Preservation section

---

## Single-Property Mode

No property slug in any URL. Property context auto-resolved from plugin settings.

```
# Section listing pages (optional per section)
/rooms
/restaurants
/spas
/meetings
/experiences
/events
/offers
/places

# Outlet singulars
/room/deluxe-king
/room/premier-twin
/room/executive-suite
/restaurant/olive-tree
/restaurant/spice-verandah
/spa/nirvana-spa
/meeting/deccan-ballroom
/meeting/cubbon-boardroom
/experience/sunrise-kayaking
/event/diwali-gala
/offer/advance-purchase
/place/cubbon-park

# Static pages — wildcard, resolved after all section and outlet rules
/fact-sheet
/gallery
/contact
/{any-page-slug}
```

---

## Multi-Property Mode

Property slug sits directly at root for property-specific URLs.

```
# Property listing page (optional)
/hotels

# Property singulars
/leela-residency
/seawind-resort

# Section listing pages under property (optional per section, per property)
/leela-residency/rooms
/leela-residency/restaurants
/leela-residency/spas
/leela-residency/meetings
/leela-residency/experiences
/leela-residency/events
/leela-residency/offers
/leela-residency/places

# Outlet singulars under property
/leela-residency/room/deluxe-king
/leela-residency/room/premier-twin
/leela-residency/room/executive-suite
/leela-residency/restaurant/olive-tree
/leela-residency/restaurant/spice-verandah
/leela-residency/spa/nirvana-spa
/leela-residency/meeting/deccan-ballroom
/leela-residency/meeting/cubbon-boardroom
/leela-residency/experience/sunrise-kayaking
/leela-residency/event/diwali-gala
/leela-residency/offer/advance-purchase
/leela-residency/place/cubbon-park

# Static pages under property — wildcard, resolved after all section and outlet rules
/leela-residency/fact-sheet
/leela-residency/gallery
/leela-residency/{any-page-slug}
```

---

## Singular Base Redirect Behaviour

A bare singular base with no outlet slug is not a valid page.
It redirects 301 directly to the relevant destination — **no chained redirects.**
All 301 redirects preserve the original query string — see Redirect Query String Preservation section.

### When listing page is enabled

Bare singular base redirects to the plural listing page.

| Visited URL | Redirects to | Status |
|---|---|---|
| `/room` | `/rooms` | 301 |
| `/restaurant` | `/restaurants` | 301 |
| `/spa` | `/spas` | 301 |
| `/meeting` | `/meetings` | 301 |
| `/experience` | `/experiences` | 301 |
| `/event` | `/events` | 301 |
| `/offer` | `/offers` | 301 |
| `/place` | `/places` | 301 |
| `/hotel` | `/hotels` | 301 |

### When listing page is disabled

Bare singular base redirects **directly to property root** (or homepage in single mode).
It does not pass through the disabled listing page URL — that would be a double redirect.

| Visited URL | Listing page state | Redirects to | Status |
|---|---|---|---|
| `/room` | disabled | `/` | 301 |
| `/leela-residency/room` | disabled | `/leela-residency` | 301 |
| `/leela-residency/restaurant` | disabled | `/leela-residency` | 301 |

### Outlet singulars always resolve

Outlet singulars are never affected by the listing page state.

| Visited URL | Listing page state | Result |
|---|---|---|
| `/room/deluxe-king` | enabled | 200 |
| `/room/deluxe-king` | disabled | 200 |
| `/leela-residency/room/deluxe-king` | disabled | 200 |

---

## Listing Page Availability

When a listing page is disabled for a property, it is simply **not available**.

- The URL returns 404
- It is excluded from sitemaps
- It is excluded from nav menus generated by the plugin
- No redirect from the listing page URL to anywhere — 404 is the correct response
- The bare singular base redirect goes directly to property root, bypassing the listing page URL entirely

> **Why 404 and not redirect?**
> A redirect from a disabled listing page implies the content exists elsewhere.
> It does not. 404 is the accurate and SEO-correct response.

---

## Redirect Query String Preservation

All 301 redirects issued by the plugin must preserve the original query string.
WordPress's `wp_redirect()` does not append query strings automatically.
The plugin redirect handler must pass `$_SERVER['QUERY_STRING']` through explicitly.

**Implementation requirement:**

```php
// WRONG — drops query string
wp_redirect( $destination_url, 301 );

// CORRECT — preserves query string
$qs = $_SERVER['QUERY_STRING'] ?? '';
$redirect_url = $qs ? $destination_url . '?' . $qs : $destination_url;
wp_redirect( $redirect_url, 301 );
exit;
```

This applies to every redirect the plugin issues:
- Bare singular base → plural listing page
- Bare singular base → property root (when listing disabled)
- Any future redirect added to the plugin

**Why this matters:** Inbound links from email campaigns, ads, or backlinks frequently
carry UTM parameters (`?utm_source=email&utm_medium=newsletter`). Without query string
preservation, these tracking parameters are silently dropped on redirect, breaking
campaign attribution.

Example:
```
Inbound:   /room?utm_source=email&utm_medium=newsletter
Redirects: /rooms?utm_source=email&utm_medium=newsletter   ← correct
NOT:       /rooms                                           ← breaks attribution
```

---

## Reserved Slugs

All configured section bases — both plural and singular — are **reserved slugs**.
WordPress must prevent any post, page, or CPT record from using these slugs.

### What is reserved

At any given time, the full reserved slug list is:
- All plural section bases (`hotels`, `rooms`, `restaurants`, `spas`, `meetings`, `experiences`, `events`, `offers`, `places`)
- All singular section bases (`hotel`, `room`, `restaurant`, `spa`, `meeting`, `experience`, `event`, `offer`, `place`)
- WordPress core reserved slugs (unchanged)

### Enforcement — post slug validation

The plugin hooks `wp_unique_post_slug` and `pre_post_update` to block reserved slugs.

Rules:
- A `pw_property` post **cannot use a slug that matches any section base** (plural or singular)
- A WordPress Page **cannot use a slug that matches any section base** (plural or singular)
- Any other post type managed by the plugin follows the same rule
- If a reserved slug is entered, WordPress admin shows a validation error and blocks the save
- The error message explicitly lists the conflict: `"The slug 'rooms' is reserved by Portico Webworks (room type listing page). Please choose a different slug."`

### Enforcement — settings validation

When a section base is changed in plugin settings:
- The plugin checks all existing published posts of relevant types for slug conflicts
- If conflicts exist, the settings save is blocked
- The error lists each conflicting post by title and edit URL
- Admin must resolve conflicts before the base change is accepted

### Reserved slug list — runtime access

```php
// Returns the current full list of reserved slugs
pw_get_reserved_slugs(): array

// Returns true if the given slug is reserved
pw_is_reserved_slug( string $slug ): bool
```

These functions must be used in all validation hooks — never hardcode the slug list inline.

---

## Static Pages — How the Wildcard Works

No sub-path mapping table. No developer registration per page type.

The plugin registers one wildcard rewrite at the property level, **after all section and outlet rules.**
Root catch-alls intentionally **do not** match reserved single segments (`*-sitemap.xml`, Rank Math / Yoast-style; `wp-sitemap*.xml`; `sitemap_index.xml`; `sitemap.xml`; `robots.txt`) so SEO sitemaps and crawlers are not routed to the static-page or property resolver (`pw_url_reserved_root_segment_pattern()` in `property-rewrites.php`).

When a URL segment after the property slug does not match any known plural or singular base,
the plugin resolves the segment as a static page using a **property-scoped page lookup** —
not a global `get_page_by_path()` call.

### Property-scoped page resolution

In multi-property mode, two different properties can both have a WordPress Page with the same
slug (e.g. both `leela-residency` and `seawind-resort` have a `gallery` page). A global
`get_page_by_path( 'gallery' )` call would return whichever page WordPress finds first,
which is non-deterministic and incorrect.

**The wildcard resolver must scope its page lookup to the current property:**

```
/leela-residency/gallery
  → property ID resolved from pw_property_slug query var
  → query Pages where slug = 'gallery' AND _pw_property_id = {leela-residency post ID}
  → load that specific Page with property context injected

/seawind-resort/gallery
  → property ID resolved from pw_property_slug query var
  → query Pages where slug = 'gallery' AND _pw_property_id = {seawind-resort post ID}
  → load that specific Page with property context injected
```

**Implementation requirement:** Static pages that are intended to be served under a property
must store `_pw_property_id` meta. The wildcard resolver queries:

```php
get_posts([
  'post_type'  => 'page',
  'name'       => $segment,
  'meta_query' => [[
    'key'   => '_pw_property_id',
    'value' => $property_id,
  ]],
  'posts_per_page' => 1,
])
```

If no property-scoped page is found, fall back to a global `get_page_by_path( $segment )`
for pages that are not property-specific (e.g. a shared `/contact` page in single-property mode).

**In single-property mode** there is only one property, so the scoping issue does not arise.
The fallback global lookup is sufficient. The property-scoped query runs first regardless.

### Adding a static page under a property

1. Create a WordPress Page with the desired slug (e.g. `fact-sheet`)
2. Set `_pw_property_id` on the page to the relevant property post ID
3. Done — no plugin settings, no rewrite rule registration, no cache flush required

```
/leela-residency/fact-sheet         → Page slug: fact-sheet, _pw_property_id: {leela-residency ID}
/leela-residency/gallery            → Page slug: gallery, _pw_property_id: {leela-residency ID}
/seawind-resort/gallery             → Page slug: gallery, _pw_property_id: {seawind-resort ID}
```

> **Important:** WordPress Pages must not use slugs that match any section base.
> This is enforced by reserved slug validation — see Reserved Slugs section.
> If a Page slug matches a section base, the section rule wins and the Page is unreachable.

---

## `pw_get_current_property_id()` Resolution Chain

All templates, blocks, schema markup, and REST endpoints must use this function.
Never query `pw_property` directly for context resolution.

```
1. Block attribute pwPropertyId set on current GB loop block?
   → Use it
   (Multi-property: editor explicitly scoped the loop to a property)

2. Query var pw_property_slug present in current request?
   → Resolve slug → post ID
   → Result is cached in a static variable for the duration of the request
   (Multi-property: property slug is in the URL)

3. Currently on a pw_property singular template?
   → Return get_queried_object_id()

4. pw_settings['pw_default_property_id'] is set and non-zero?
   → Use it
   (Single-property mode: all pages resolve to the configured property)

5. Return 0
   (No property context available)
```

### Step 2 — slug-to-ID resolution and caching

Step 2 converts `pw_property_slug` (a string from the query var) to a post ID.
This requires a database query. Since the slug does not change within a single request,
the result must be cached in a static variable to avoid redundant queries.

**Implementation requirement:**

```php
function pw_resolve_property_slug( string $slug ): int {
    static $cache = [];

    if ( isset( $cache[ $slug ] ) ) {
        return $cache[ $slug ];
    }

    $post = get_page_by_path( $slug, OBJECT, 'pw_property' );
    $cache[ $slug ] = $post ? (int) $post->ID : 0;

    return $cache[ $slug ];
}
```

This static cache lives for the duration of one PHP request only.
It is not a persistent transient — no invalidation logic is needed.
`pw_get_current_property_id()` calls `pw_resolve_property_slug()` internally at step 2.

---

## Rule Registration Order

Rewrite rules must be registered in this exact order. WordPress matches rules
top-to-bottom and stops at the first match. Wrong order causes wildcard rules
to swallow outlet and section URLs before they can be matched.

```
Priority 1 — Outlet singulars (most specific)
  /{property}/{singular-base}/{outlet-slug}
  /{singular-base}/{outlet-slug}                    ← single mode

Priority 2 — Section listing pages
  /{property}/{plural-base}
  /{plural-base}                                    ← single mode

Priority 3 — Bare singular base redirects
  /{property}/{singular-base}
  /{singular-base}                                  ← single mode

Priority 4 — Property singular (multi mode only)
  /{property}

Priority 5 — Property listing page (multi mode only, if enabled)
  /{configured-property-plural-base}                ← e.g. /hotels

Priority 6 (LAST) — Static page wildcard
  /{property}/{any-segment}                         ← multi mode
  /{any-segment}                                    ← single mode
```

> **The static page wildcard must always be registered last.**
> It matches any segment not already claimed by a higher-priority rule.
> If registered before outlet or section rules, it will intercept CPT URLs
> and resolve them as pages — silently breaking all outlet singulars.

---

## Rewrite Rule Test Suite

The following test cases must pass after any change to rewrite rule registration,
section base configuration, or plugin mode.

---

### Single-Property Mode Tests

```
PASS  /rooms                      → section listing page (pw_room_type archive)
PASS  /room/deluxe-king           → outlet singular (pw_room_type, slug: deluxe-king)
PASS  /room                       → 301 to /rooms  (listing enabled)
PASS  /room?utm_source=email      → 301 to /rooms?utm_source=email  (query string preserved)
PASS  /restaurants                → section listing page (pw_restaurant archive)
PASS  /restaurant/olive-tree      → outlet singular (pw_restaurant, slug: olive-tree)
PASS  /restaurant                 → 301 to /restaurants  (listing enabled)
PASS  /spas                       → section listing page (pw_spa archive)
PASS  /spa/nirvana-spa            → outlet singular (pw_spa, slug: nirvana-spa)
PASS  /meetings                   → section listing page (pw_meeting_room archive)
PASS  /meeting/deccan-ballroom    → outlet singular (pw_meeting_room, slug: deccan-ballroom)
PASS  /experiences                → section listing page (pw_experience archive)
PASS  /experience/sunrise-kayaking → outlet singular (pw_experience, slug: sunrise-kayaking)
PASS  /events                     → section listing page (pw_event archive)
PASS  /event/diwali-gala          → outlet singular (pw_event, slug: diwali-gala)
PASS  /offers                     → section listing page (pw_offer archive)
PASS  /offer/advance-purchase     → outlet singular (pw_offer, slug: advance-purchase)
PASS  /places                     → section listing page (pw_places archive)
PASS  /place/cubbon-park          → outlet singular (pw_places, slug: cubbon-park)
PASS  /hotel                      → 301 to /hotels  (listing enabled; pw_property archive)
PASS  /fact-sheet                 → static page wildcard (Page slug: fact-sheet)
PASS  /gallery                    → static page wildcard (Page slug: gallery)
PASS  /nonexistent-page           → 404
```

---

### Single-Property Mode — Listing Disabled Tests

```
PASS  /rooms                      → 404  (listing disabled)
PASS  /room/deluxe-king           → 200  (outlet singular unaffected)
PASS  /room                       → 301 to /  (direct, no chain through /rooms)
PASS  /room?utm_source=email      → 301 to /?utm_source=email  (query string preserved)
PASS  /restaurants                → 404  (listing disabled)
PASS  /restaurant/olive-tree      → 200  (outlet singular unaffected)
PASS  /restaurant                 → 301 to /  (direct)
```

---

### Multi-Property Mode Tests

```
PASS  /leela-residency                              → property singular
PASS  /leela-residency/rooms                        → section listing page
PASS  /leela-residency/room/deluxe-king             → outlet singular
PASS  /leela-residency/room                         → 301 to /leela-residency/rooms
PASS  /leela-residency/room?utm_source=email        → 301 to /leela-residency/rooms?utm_source=email
PASS  /leela-residency/restaurants                  → section listing page
PASS  /leela-residency/restaurant/olive-tree        → outlet singular
PASS  /leela-residency/restaurant                   → 301 to /leela-residency/restaurants
PASS  /leela-residency/fact-sheet                   → static page (scoped to leela-residency)
PASS  /seawind-resort/gallery                       → static page (scoped to seawind-resort)
PASS  /leela-residency/gallery                      → static page (scoped to leela-residency,
                                                        NOT seawind-resort's gallery page)
PASS  /leela-residency/nonexistent                  → 404
PASS  /nonexistent-property                         → 404
```

---

### Multi-Property Mode — Listing Disabled Tests

```
PASS  /leela-residency/rooms                        → 404  (listing disabled)
PASS  /leela-residency/room/deluxe-king             → 200  (outlet singular unaffected)
PASS  /leela-residency/room                         → 301 to /leela-residency  (direct, no chain)
PASS  /leela-residency/room?utm_source=email        → 301 to /leela-residency?utm_source=email
PASS  /leela-residency/restaurants                  → 404  (listing disabled)
PASS  /leela-residency/restaurant/olive-tree        → 200  (outlet singular unaffected)
PASS  /leela-residency/restaurant                   → 301 to /leela-residency  (direct)
```

---

### Rule Order Integrity Tests

These tests verify that the static page wildcard does not intercept CPT URLs.
To run: temporarily create WordPress Pages with slugs matching section bases and outlet slugs,
then assert that the CPT routes still win. Delete test pages after assertion.

```
PASS  /room/deluxe-king           → outlet singular, NOT static page wildcard
PASS  /rooms                      → section listing, NOT static page wildcard
PASS  /leela-residency/room/deluxe-king  → outlet singular, NOT static page wildcard
PASS  /leela-residency/rooms      → section listing, NOT static page wildcard
```

---

### Property-Scoped Wildcard Tests

These tests verify that the static page wildcard resolves to the correct property-scoped page
when two properties share the same page slug.

Setup: create a `gallery` Page scoped to `leela-residency` and a separate `gallery` Page
scoped to `seawind-resort`.

```
PASS  /leela-residency/gallery    → loads leela-residency gallery Page
                                     pw_get_current_property_id() = leela-residency ID
PASS  /seawind-resort/gallery     → loads seawind-resort gallery Page
                                     pw_get_current_property_id() = seawind-resort ID
PASS  /leela-residency/gallery    → does NOT load seawind-resort gallery Page
PASS  /seawind-resort/gallery     → does NOT load leela-residency gallery Page
```

---

### Reserved Slug Validation Tests

```
PASS  pw_property post with slug 'rooms'        → blocked, validation error shown
PASS  pw_property post with slug 'room'         → blocked, validation error shown
PASS  pw_property post with slug 'restaurants'  → blocked, validation error shown
PASS  WordPress Page with slug 'rooms'          → blocked, validation error shown
PASS  WordPress Page with slug 'hotels'         → blocked, validation error shown
PASS  pw_property post with slug 'leela-residency' → allowed (not a reserved slug)
PASS  WordPress Page with slug 'fact-sheet'     → allowed (not a reserved slug)
PASS  Changing plural base to 'suites' when pw_property post slug 'suites' exists
        → settings save blocked, conflict listed
```

---

### Mode Switch Tests — Known Gap (v1)

Switching from single-property to multi-property mode is a **known breaking change** in v1.
The following tests document the expected failure state. They are not expected to pass.
They exist to make the breakage explicit and traceable, not to be fixed in v1.

```
KNOWN FAIL  Single→Multi switch: /room/deluxe-king
            Before switch: resolves to outlet singular (property context from settings)
            After switch:  404 — no property slug prefix, no rewrite rule matches

KNOWN FAIL  Single→Multi switch: /rooms
            Before switch: resolves to section listing page
            After switch:  404 — same reason

KNOWN FAIL  Single→Multi switch: /fact-sheet
            Before switch: resolves to static page with property context
            After switch:  404 or wrong page — property context lost
```

**What the mode switch requires (v2 scope):**
- A migration assistant that prepends the property slug to all existing internal links
- Redirect rules for legacy single-property URLs → new multi-property URLs
- A pre-switch warning in the admin UI listing all URLs that will break

Until that migration tooling exists, mode switching after a site is live is a
destructive operation and must be documented as such in the admin UI.

---

### `pw_resolve_property_slug()` Caching Tests

```
PASS  First call: pw_resolve_property_slug('leela-residency')
        → executes DB query, returns correct post ID, stores in static cache

PASS  Second call within same request: pw_resolve_property_slug('leela-residency')
        → returns from static cache, zero DB queries

PASS  Call with unknown slug: pw_resolve_property_slug('nonexistent')
        → returns 0, caches 0 for that slug (does not re-query)

PASS  Different slug in same request: pw_resolve_property_slug('seawind-resort')
        → executes DB query (not in cache), returns correct post ID
```

---

## Listing Page Design — Per CPT

Each CPT listing page is independently designable using GenerateBlocks query loops.
There is no shared archive template enforced by the plugin.

| CPT | Listing page (single mode) | Listing page (multi mode) | Design notes |
|---|---|---|---|
| `pw_property` | N/A | `/hotels` | Property cards — filterable by type or location |
| `pw_room_type` | `/rooms` | `/leela-residency/rooms` | Room cards with rate, occupancy, key features |
| `pw_restaurant` | `/restaurants` | `/leela-residency/restaurants` | Outlet cards with cuisine, meal periods, reservation CTA |
| `pw_spa` | `/spas` | `/leela-residency/spas` | Spa cards with treatment highlights, booking CTA |
| `pw_meeting_room` | `/meetings` | `/leela-residency/meetings` | Venue cards with capacity, area, AV summary |
| `pw_experience` | `/experiences` | `/leela-residency/experiences` | Experience cards with duration, price, category |
| `pw_event` | `/events` | `/leela-residency/events` | Event cards with date, venue, price |
| `pw_offer` | `/offers` | `/leela-residency/offers` | Offer cards with validity, discount type, CTA |
| `pw_places` | `/places` | `/leela-residency/places` | Nearby place cards with distance and transport mode |

Each listing page uses a GenerateBlocks query loop with a `pw_property_id` custom query arg.
The plugin intercepts this arg and injects the appropriate `meta_query` filter automatically.
No custom PHP per listing page.

---

## Query Vars Resolved from URL

| Query var | Example value | Purpose |
|---|---|---|
| `pw_property_slug` | `leela-residency` | Property context resolution in multi mode |
| `pw_section_cpt` | `pw_restaurant` | Identifies which CPT the listing or outlet belongs to |
| `pw_outlet_slug` | `olive-tree` | Outlet singular resolution |

---

## Plugin Settings Reference

### Global — Portico Webworks → General

| Setting | Type | Default | Notes |
|---|---|---|---|
| Property mode | radio | `single` | `single` / `multi` |

### Global — Portico Webworks → Permalinks

| Setting | Type | Default | Notes |
|---|---|---|---|
| Enable property archive | checkbox | on | Property listing page (`/hotels` by default) |
| Section URL bases | text (per CPT) | see Section Bases table | Includes `pw_property` (plural / singular) first |

### Per-Property — `pw_property` Edit Screen

| Setting | Type | Default | Notes |
|---|---|---|---|
| Enabled sections | multicheck | all enabled | Which section listing pages are available for this property |

---

## What This Eliminates

| Before | After |
|---|---|
| Sub-path mapping table in settings | Gone |
| Developer registers each page section | Gone — wildcard handles static pages automatically |
| Rewrite rule per page type per property | Gone — one wildcard rule per tier |
| `flush_rewrite_rules` on every new section | Gone |
| Thin listing pages for single-outlet properties | Disabled per property — returns 404 |
| Fixed non-configurable section bases | All plural and singular bases configurable globally |
| Trailing slashes on all URLs | Removed |
| Bare singular base with no outlet returning 404 | 301 redirect — direct, no chaining |
| `pw_property` archive hardcoded as always-on | Optional via global setting |
| No slug conflict prevention | Reserved slug enforcement on posts and settings |
| No rewrite rule order guarantees | Explicit registration order with test suite |
| UTM parameters dropped on 301 redirects | Query string preserved in all redirect handlers |
| Global page slug lookup in wildcard resolver | Property-scoped page lookup — correct in multi mode |
| Redundant DB query per request for property slug | Static variable cache in `pw_resolve_property_slug()` |
| Mode switch breakage undocumented | Explicit KNOWN FAIL tests — v2 migration scope |