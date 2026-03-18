<?php
/**
 * Plugin Name: Portico Webworks
 * Description: Portico Webworks plugin.
 * Version: 0.1.5
 * Author: Portico Webworks
 * Author URI: https://porticowebworks.com
 */

if (!defined('ABSPATH')) {
	exit;
}

function portico_webworks_logo_url() {
	return plugins_url('logo.svg', __FILE__);
}

function portico_webworks_admin_assets_url() {
	return plugins_url('', __FILE__);
}

add_action('admin_enqueue_scripts', function ($hook_suffix) {
	if (!isset($_GET['page']) || $_GET['page'] !== portico_webworks_admin_page_slug()) {
		return;
	}

	wp_enqueue_style(
		'portico-webworks-admin-fonts',
		'https://fonts.googleapis.com/css2?family=Inter+Tight:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600&display=swap',
		array(),
		null
	);

	$css = "
.portico-webworks-admin{--pw-bg:#F5F3EE;--pw-surface:#FFFFFF;--pw-card:#FAFAF8;--pw-card2:#F0EDE6;--pw-border:rgba(0,0,0,0.09);--pw-border2:rgba(0,0,0,0.18);--pw-text:#1A1917;--pw-sub:#504D48;--pw-muted:#7F7C77;--pw-primary:#C92A08;--pw-primary-dark:#A32206;--pw-secondary:#1E1E1C;font-family:'Inter Tight',system-ui,sans-serif;}
.portico-webworks-admin{background:transparent}
.portico-webworks-admin .pw-header{display:flex;align-items:center;justify-content:space-between;gap:16px;background:var(--pw-surface);border:1px solid var(--pw-border);border-radius:8px;padding:12px 14px;margin:14px 0 10px;}
.portico-webworks-admin .pw-header-left{display:flex;align-items:center;gap:10px;}
.portico-webworks-admin .pw-logo{width:28px;height:28px;display:block}
.portico-webworks-admin .pw-title{font-family:'Playfair Display',Georgia,serif;font-weight:500;font-size:20px;color:var(--pw-text);letter-spacing:-0.01em}
.portico-webworks-admin .pw-badge{font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--pw-muted);background:var(--pw-card);border:1px solid var(--pw-border);padding:4px 8px;border-radius:6px;}
.portico-webworks-admin .pw-tabs{display:flex;gap:6px;border-bottom:1px solid var(--pw-border);padding:0 2px;margin:0 0 14px;overflow-x:auto}
.portico-webworks-admin .pw-tab{display:inline-flex;align-items:center;padding:10px 12px;font-size:13px;font-weight:600;color:var(--pw-muted);text-decoration:none;border-bottom:2px solid transparent;margin-bottom:-1px}
.portico-webworks-admin .pw-tab:hover{color:var(--pw-text)}
.portico-webworks-admin .pw-tab.is-active{color:var(--pw-text);border-bottom-color:var(--pw-primary)}
.portico-webworks-admin .pw-panel{max-width:1100px}
.portico-webworks-admin .pw-card{background:var(--pw-card);border:1px solid var(--pw-border);border-radius:10px;overflow:hidden}
.portico-webworks-admin .pw-card-head{background:var(--pw-card2);border-bottom:1px solid var(--pw-border);padding:10px 14px;display:flex;align-items:center;justify-content:space-between}
.portico-webworks-admin .pw-card-title{font-size:12px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:var(--pw-sub)}
.portico-webworks-admin .pw-card-body{padding:16px 14px}
.portico-webworks-admin .pw-card-body .form-table th{width:240px}
.portico-webworks-admin .pw-card-body input.regular-text{border-radius:6px;border-color:rgba(0,0,0,0.15)}
.portico-webworks-admin .pw-card-body input.regular-text:focus{border-color:var(--pw-border2);box-shadow:0 0 0 1px var(--pw-border2)}
.portico-webworks-admin .pw-btn.button-primary{background:var(--pw-primary);border-color:var(--pw-primary);border-radius:6px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase}
.portico-webworks-admin .pw-btn.button-primary:hover{background:var(--pw-primary-dark);border-color:var(--pw-primary-dark)}
.portico-webworks-admin .pw-subtabs{display:grid;grid-template-columns:220px 1fr;gap:14px;align-items:start}
.portico-webworks-admin .pw-subnav{background:var(--pw-surface);border:1px solid var(--pw-border);border-radius:10px;padding:10px}
.portico-webworks-admin .pw-subnav a{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:10px 10px;border-radius:8px;text-decoration:none;color:var(--pw-muted);font-weight:600}
.portico-webworks-admin .pw-subnav a:hover{color:var(--pw-text);background:rgba(0,0,0,0.03)}
.portico-webworks-admin .pw-subnav a.is-active{color:var(--pw-text);background:rgba(201,42,8,0.10);border:1px solid rgba(201,42,8,0.25)}
.portico-webworks-admin .pw-subpanel h2{margin:0 0 6px;padding:0;font-size:16px;color:var(--pw-text);font-family:'Playfair Display',Georgia,serif;font-weight:500}
.portico-webworks-admin .pw-subpanel .description{margin:0 0 14px}
";
	wp_register_style('portico-webworks-admin', false, array(), '0.1.0');
	wp_enqueue_style('portico-webworks-admin');
	wp_add_inline_style('portico-webworks-admin', $css);
});

function portico_webworks_admin_page_slug() {
	return 'portico-webworks';
}

function portico_webworks_option_key() {
	return 'portico_webworks_property_profile';
}

function portico_webworks_get_property_profile() {
	$defaults = array(
		'property_name' => '',
		'property_short_name' => '',
		'abbreviation' => '',
		'legal_name' => '',
		'tax_id' => '',
		'address_line_1' => '',
		'address_line_2' => '',
		'city' => '',
		'state' => '',
		'postal_code' => '',
		'phone' => '',
		'mobile' => '',
		'whatsapp' => '',
		'email' => '',
		'latitude' => '',
		'longitude' => '',
		'instagram' => '',
		'facebook' => '',
		'youtube' => '',
		'linkedin' => '',
		'tripadvisor' => '',
		'twitter' => '',
		'google_business' => '',
	);

	$val = get_option(portico_webworks_option_key(), array());
	if (!is_array($val)) {
		$val = array();
	}

	return array_merge($defaults, $val);
}

function portico_webworks_sanitize_property_profile($input) {
	if (!is_array($input)) {
		return array();
	}

	$out = portico_webworks_get_property_profile();
	$text_fields = array(
		'property_name',
		'property_short_name',
		'abbreviation',
		'legal_name',
		'tax_id',
		'address_line_1',
		'address_line_2',
		'city',
		'state',
		'postal_code',
		'phone',
		'mobile',
		'whatsapp',
	);

	foreach ($text_fields as $k) {
		if (isset($input[$k])) {
			$out[$k] = sanitize_text_field($input[$k]);
		}
	}

	if (isset($input['email'])) {
		$out['email'] = sanitize_email($input['email']);
	}

	if (isset($input['latitude'])) {
		$out['latitude'] = sanitize_text_field($input['latitude']);
	}
	if (isset($input['longitude'])) {
		$out['longitude'] = sanitize_text_field($input['longitude']);
	}

	$url_fields = array(
		'instagram',
		'facebook',
		'youtube',
		'linkedin',
		'tripadvisor',
		'twitter',
		'google_business',
	);
	foreach ($url_fields as $k) {
		if (isset($input[$k])) {
			$out[$k] = esc_url_raw($input[$k]);
		}
	}

	return $out;
}

function portico_webworks_property_sections() {
	return array(
		'identity' => array(
			'label' => 'Identity',
			'section_id' => 'portico_webworks_property_identity',
			'desc_cb' => 'portico_webworks_section_identity_desc',
		),
		'address' => array(
			'label' => 'Address',
			'section_id' => 'portico_webworks_property_address',
			'desc_cb' => 'portico_webworks_section_address_desc',
		),
		'contact' => array(
			'label' => 'Contact',
			'section_id' => 'portico_webworks_property_contact',
			'desc_cb' => 'portico_webworks_section_contact_desc',
		),
		'geo' => array(
			'label' => 'Geo',
			'section_id' => 'portico_webworks_property_geo',
			'desc_cb' => 'portico_webworks_section_geo_desc',
		),
		'social' => array(
			'label' => 'Social',
			'section_id' => 'portico_webworks_property_social',
			'desc_cb' => 'portico_webworks_section_social_desc',
		),
	);
}

function portico_webworks_field_text($args) {
	$profile = portico_webworks_get_property_profile();
	$key = $args['key'];
	$label = $args['label'];
	$type = isset($args['type']) ? $args['type'] : 'text';
	$placeholder = isset($args['placeholder']) ? $args['placeholder'] : '';
	$help = isset($args['help']) ? $args['help'] : '';

	$name = portico_webworks_option_key() . '[' . $key . ']';
	$val = isset($profile[$key]) ? $profile[$key] : '';

	echo '<label for="portico-webworks-' . esc_attr($key) . '" class="screen-reader-text">' . esc_html($label) . '</label>';
	echo '<input class="regular-text" id="portico-webworks-' . esc_attr($key) . '" name="' . esc_attr($name) . '" type="' . esc_attr($type) . '" value="' . esc_attr($val) . '" placeholder="' . esc_attr($placeholder) . '" />';
	if ($help !== '') {
		echo '<p class="description">' . esc_html($help) . '</p>';
	}
}

function portico_webworks_field_url($args) {
	portico_webworks_field_text(array_merge($args, array('type' => 'url')));
}

function portico_webworks_section_identity_desc() {
	echo '<p class="description">Core property details used across the site and templates.</p>';
}

function portico_webworks_section_address_desc() {
	echo '<p class="description">Physical location for invoices, maps, and contact pages.</p>';
}

function portico_webworks_section_contact_desc() {
	echo '<p class="description">Primary contact details shown to guests.</p>';
}

function portico_webworks_section_geo_desc() {
	echo '<p class="description">Used for map embeds and directions. Use decimal degrees.</p>';
}

function portico_webworks_section_social_desc() {
	echo '<p class="description">Full URLs to your profiles/listings.</p>';
}

add_action('admin_menu', function () {
	add_menu_page(
		'Portico Webworks',
		'Portico Webworks',
		'manage_options',
		portico_webworks_admin_page_slug(),
		'portico_webworks_render_root_page',
		'dashicons-building',
		58
	);
});

add_action('admin_init', function () {
	register_setting(
		'portico_webworks_property_profile',
		portico_webworks_option_key(),
		array('sanitize_callback' => 'portico_webworks_sanitize_property_profile')
	);

	add_settings_section(
		'portico_webworks_property_identity',
		'Identity',
		'portico_webworks_section_identity_desc',
		portico_webworks_admin_page_slug()
	);
	add_settings_field(
		'property_name',
		'Property Name',
		'portico_webworks_field_text',
		portico_webworks_admin_page_slug(),
		'portico_webworks_property_identity',
		array('key' => 'property_name', 'label' => 'Property Name', 'placeholder' => 'e.g. The Grand Pavilion', 'help' => 'Public-facing name shown to guests.')
	);
	add_settings_field(
		'property_short_name',
		'Property Short Name',
		'portico_webworks_field_text',
		portico_webworks_admin_page_slug(),
		'portico_webworks_property_identity',
		array('key' => 'property_short_name', 'label' => 'Property Short Name', 'placeholder' => 'e.g. Grand Pavilion', 'help' => 'Shortened name for tight layouts (headers, nav, etc.).')
	);
	add_settings_field(
		'abbreviation',
		'Abbreviation',
		'portico_webworks_field_text',
		portico_webworks_admin_page_slug(),
		'portico_webworks_property_identity',
		array('key' => 'abbreviation', 'label' => 'Abbreviation', 'placeholder' => 'e.g. TGP', 'help' => 'Internal shorthand (optional).')
	);
	add_settings_field(
		'legal_name',
		'Legal Name (Company)',
		'portico_webworks_field_text',
		portico_webworks_admin_page_slug(),
		'portico_webworks_property_identity',
		array('key' => 'legal_name', 'label' => 'Legal Name (Company)', 'placeholder' => 'e.g. Grand Pavilion Hospitality Pvt Ltd', 'help' => 'For invoices, contracts, and compliance.')
	);
	add_settings_field(
		'tax_id',
		'Tax ID',
		'portico_webworks_field_text',
		portico_webworks_admin_page_slug(),
		'portico_webworks_property_identity',
		array('key' => 'tax_id', 'label' => 'Tax ID', 'placeholder' => 'e.g. GSTIN / VAT / EIN', 'help' => 'Use the format required in your jurisdiction.')
	);

	add_settings_section(
		'portico_webworks_property_address',
		'Address',
		'portico_webworks_section_address_desc',
		portico_webworks_admin_page_slug()
	);
	add_settings_field(
		'address_line_1',
		'Address Line 1',
		'portico_webworks_field_text',
		portico_webworks_admin_page_slug(),
		'portico_webworks_property_address',
		array('key' => 'address_line_1', 'label' => 'Address Line 1', 'placeholder' => 'Street address, building, etc.')
	);
	add_settings_field(
		'address_line_2',
		'Address Line 2',
		'portico_webworks_field_text',
		portico_webworks_admin_page_slug(),
		'portico_webworks_property_address',
		array('key' => 'address_line_2', 'label' => 'Address Line 2', 'placeholder' => 'Area, landmark (optional)')
	);
	add_settings_field(
		'city',
		'City',
		'portico_webworks_field_text',
		portico_webworks_admin_page_slug(),
		'portico_webworks_property_address',
		array('key' => 'city', 'label' => 'City', 'placeholder' => 'e.g. Kochi')
	);
	add_settings_field(
		'state',
		'State',
		'portico_webworks_field_text',
		portico_webworks_admin_page_slug(),
		'portico_webworks_property_address',
		array('key' => 'state', 'label' => 'State', 'placeholder' => 'e.g. Kerala')
	);
	add_settings_field(
		'postal_code',
		'Postal Code',
		'portico_webworks_field_text',
		portico_webworks_admin_page_slug(),
		'portico_webworks_property_address',
		array('key' => 'postal_code', 'label' => 'Postal Code', 'placeholder' => 'e.g. 682001')
	);

	add_settings_section(
		'portico_webworks_property_contact',
		'Contact',
		'portico_webworks_section_contact_desc',
		portico_webworks_admin_page_slug()
	);
	add_settings_field(
		'phone',
		'Phone No.',
		'portico_webworks_field_text',
		portico_webworks_admin_page_slug(),
		'portico_webworks_property_contact',
		array('key' => 'phone', 'label' => 'Phone No.', 'type' => 'tel', 'placeholder' => 'e.g. +91 484 123 4567', 'help' => 'Main front desk / reservations number.')
	);
	add_settings_field(
		'mobile',
		'Mobile No.',
		'portico_webworks_field_text',
		portico_webworks_admin_page_slug(),
		'portico_webworks_property_contact',
		array('key' => 'mobile', 'label' => 'Mobile No.', 'type' => 'tel', 'placeholder' => 'e.g. +91 98765 43210', 'help' => 'Optional backup mobile contact.')
	);
	add_settings_field(
		'whatsapp',
		'WhatsApp No.',
		'portico_webworks_field_text',
		portico_webworks_admin_page_slug(),
		'portico_webworks_property_contact',
		array('key' => 'whatsapp', 'label' => 'WhatsApp No.', 'type' => 'tel', 'placeholder' => 'e.g. +91 98765 43210', 'help' => 'Number used for WhatsApp chats. Include country code.')
	);
	add_settings_field(
		'email',
		'Email ID',
		'portico_webworks_field_text',
		portico_webworks_admin_page_slug(),
		'portico_webworks_property_contact',
		array('key' => 'email', 'label' => 'Email ID', 'type' => 'email', 'placeholder' => 'e.g. reservations@yourhotel.com', 'help' => 'Inbox for guest enquiries and reservations.')
	);

	add_settings_section(
		'portico_webworks_property_geo',
		'Geo',
		'portico_webworks_section_geo_desc',
		portico_webworks_admin_page_slug()
	);
	add_settings_field(
		'latitude',
		'Latitude',
		'portico_webworks_field_text',
		portico_webworks_admin_page_slug(),
		'portico_webworks_property_geo',
		array('key' => 'latitude', 'label' => 'Latitude', 'placeholder' => 'e.g. 9.9312', 'help' => 'Example: 9.9312')
	);
	add_settings_field(
		'longitude',
		'Longitude',
		'portico_webworks_field_text',
		portico_webworks_admin_page_slug(),
		'portico_webworks_property_geo',
		array('key' => 'longitude', 'label' => 'Longitude', 'placeholder' => 'e.g. 76.2673', 'help' => 'Example: 76.2673')
	);

	add_settings_section(
		'portico_webworks_property_social',
		'Social',
		'portico_webworks_section_social_desc',
		portico_webworks_admin_page_slug()
	);
	add_settings_field(
		'instagram',
		'Instagram',
		'portico_webworks_field_url',
		portico_webworks_admin_page_slug(),
		'portico_webworks_property_social',
		array('key' => 'instagram', 'label' => 'Instagram', 'placeholder' => 'https://instagram.com/yourhandle')
	);
	add_settings_field(
		'facebook',
		'Facebook',
		'portico_webworks_field_url',
		portico_webworks_admin_page_slug(),
		'portico_webworks_property_social',
		array('key' => 'facebook', 'label' => 'Facebook', 'placeholder' => 'https://facebook.com/yourpage')
	);
	add_settings_field(
		'youtube',
		'YouTube',
		'portico_webworks_field_url',
		portico_webworks_admin_page_slug(),
		'portico_webworks_property_social',
		array('key' => 'youtube', 'label' => 'YouTube', 'placeholder' => 'https://youtube.com/@yourchannel')
	);
	add_settings_field(
		'linkedin',
		'LinkedIn',
		'portico_webworks_field_url',
		portico_webworks_admin_page_slug(),
		'portico_webworks_property_social',
		array('key' => 'linkedin', 'label' => 'LinkedIn', 'placeholder' => 'https://linkedin.com/company/yourcompany')
	);
	add_settings_field(
		'tripadvisor',
		'Tripadvisor',
		'portico_webworks_field_url',
		portico_webworks_admin_page_slug(),
		'portico_webworks_property_social',
		array('key' => 'tripadvisor', 'label' => 'Tripadvisor', 'placeholder' => 'https://tripadvisor.com/...')
	);
	add_settings_field(
		'twitter',
		'Twitter (X)',
		'portico_webworks_field_url',
		portico_webworks_admin_page_slug(),
		'portico_webworks_property_social',
		array('key' => 'twitter', 'label' => 'Twitter (X)', 'placeholder' => 'https://x.com/yourhandle')
	);
	add_settings_field(
		'google_business',
		'Google My Business',
		'portico_webworks_field_url',
		portico_webworks_admin_page_slug(),
		'portico_webworks_property_social',
		array('key' => 'google_business', 'label' => 'Google My Business', 'placeholder' => 'https://g.page/...')
	);
});

function portico_webworks_render_root_page() {
	if (!current_user_can('manage_options')) {
		return;
	}

	$tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'property';
	$tabs = array(
		'property' => 'Property Profile',
		'settings' => 'Settings',
	);
	if (!isset($tabs[$tab])) {
		$tab = 'property';
	}

	echo '<div class="wrap portico-webworks-admin">';
	echo '<div class="pw-header">';
	echo '<div class="pw-header-left">';
	echo '<img class="pw-logo" alt="" src="' . esc_url(portico_webworks_logo_url()) . '" />';
	echo '<div class="pw-title">Portico Webworks</div>';
	echo '</div>';
	echo '<div class="pw-badge">v0.1.5</div>';
	echo '</div>';

	echo '<nav class="pw-tabs" aria-label="Portico Webworks">';
	foreach ($tabs as $key => $label) {
		$is_active = $key === $tab;
		$url = admin_url('admin.php?page=' . urlencode(portico_webworks_admin_page_slug()) . '&tab=' . urlencode($key));
		echo '<a class="pw-tab' . ($is_active ? ' is-active' : '') . '" href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
	}
	echo '</nav>';

	echo '<div class="pw-panel">';
	if ($tab === 'property') {
		$sections = portico_webworks_property_sections();
		$sub = isset($_GET['sub']) ? sanitize_key($_GET['sub']) : 'identity';
		if (!isset($sections[$sub])) {
			$sub = 'identity';
		}

		echo '<div class="pw-card">';
		echo '<div class="pw-card-head"><div class="pw-card-title">Property Profile</div></div>';
		echo '<div class="pw-card-body">';
		echo '<div class="pw-subtabs">';

		echo '<div class="pw-subnav" aria-label="Property Profile Sections">';
		foreach ($sections as $key => $meta) {
			$url = admin_url('admin.php?page=' . urlencode(portico_webworks_admin_page_slug()) . '&tab=property&sub=' . urlencode($key));
			echo '<a class="' . ($key === $sub ? 'is-active' : '') . '" href="' . esc_url($url) . '">' . esc_html($meta['label']) . '</a>';
		}
		echo '</div>';

		$active = $sections[$sub];
		echo '<div class="pw-subpanel">';
		echo '<h2>' . esc_html($active['label']) . '</h2>';
		if (isset($active['desc_cb']) && is_callable($active['desc_cb'])) {
			call_user_func($active['desc_cb']);
		}

		echo '<form method="post" action="options.php">';
		echo '<input type="hidden" name="_wp_http_referer" value="' . esc_attr(admin_url('admin.php?page=' . urlencode(portico_webworks_admin_page_slug()) . '&tab=property&sub=' . urlencode($sub))) . '" />';
		settings_fields('portico_webworks_property_profile');
		do_settings_fields(portico_webworks_admin_page_slug(), $active['section_id']);
		submit_button('Save ' . $active['label'], 'primary', 'submit', true, array('class' => 'pw-btn'));
		echo '</form>';
		echo '</div>';

		echo '</div>';
		echo '</div></div>';
	} else {
		echo '<div class="pw-card">';
		echo '<div class="pw-card-head"><div class="pw-card-title">Settings</div></div>';
		echo '<div class="pw-card-body"><p>Portico Webworks settings will go here.</p></div>';
		echo '</div>';
	}
	echo '</div>';
	echo '</div>';
}

