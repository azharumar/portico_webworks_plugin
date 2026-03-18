# Portico Webworks — Developer Notes

## Property mode

- Controlled by option: `pw_property_mode` (`single` or `multi`, default: `single`).
- **Single**: one property, no URL routing. Resolution returns the first published `pw_property`.
- **Multi**: multiple properties resolved by URL path using the base path option.

## Data model

- Properties are stored as a custom post type: `pw_property`.
- Each property's website profile is stored in post meta key: `_pw_property_profile` (array).
- Meta fields: `property_name`, `address_line_1`, `email`, `latitude`, `instagram`, etc.

## PHP APIs

- `pw_get_property_profile($property_id = null)` — returns property profile array; resolves current property if `$property_id` is omitted.
- `pw_get_current_property_profile()` — convenience wrapper for the current-property profile.
- `pw_get_all_properties()` — returns list of properties (id, name, slug).
- `pw_get_current_property_id()` — resolves property id from URL (multi) or picks the single property (single).

## URL routing (multi-property mode only)

- Base path option: `pw_property_base` (default: `properties`).
- URL form: `/{pw_property_base}/{slug}/...`
  - Example: `https://example.com/stay/grand-pavilion/...` with `pw_property_base=stay`.
- When mode is `multi` and URL cannot resolve a property, the plugin responds with a 404.
- In `single` mode, routing is bypassed entirely.
