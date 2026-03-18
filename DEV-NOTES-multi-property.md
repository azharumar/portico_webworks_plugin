# Portico Webworks (Multi-Property) Developer Notes

## Data model
- Properties are stored as a custom post type: `pw_property`.
- Each property’s website profile is stored in post meta key: `_pw_property_profile` (array).
- Meta fields use the keys from the original single-property option, e.g. `property_name`, `address_line_1`, `email`, `latitude`, `instagram`, etc.

## PHP APIs
- `portico_webworks_get_property_profile($property_id = null)`
  - Returns the active property profile array for a given property id.
  - If `$property_id` is omitted, it resolves the current property id from the request URL.
- `portico_webworks_get_current_property_profile()`
  - Convenience wrapper for the current-property profile (same resolution logic).
- `portico_webworks_get_all_properties()`
  - Returns a list of properties for admin/UI dropdowns (id, name, slug).

## URL routing / current property resolution
- Routing base path is controlled by option: `portico_webworks_property_base` (default: `properties`).
- The resolver expects URL form: `/{portico_webworks_property_base}/{slug}/...`
  - Example: `https://example.com/stay/grand-pavilion/...` with `portico_webworks_property_base=stay` and property slug `grand-pavilion`.
- When multiple `pw_property` posts exist and the URL cannot resolve a property, the plugin responds with a 404.

