<?php

if (!defined('ABSPATH')) {
	exit;
}

add_action('admin_enqueue_scripts', function () {
	$divider_css = '
#adminmenu li a[href^="#pw-divider-"] {
	pointer-events: none;
	cursor: default;
	margin-top: 4px;
}
#adminmenu li a[href^="#pw-divider-"]:hover {
	background: transparent !important;
	color: inherit;
}
.pw-menu-divider {
	display: block;
	padding: 10px 0 2px;
	font-size: 10px;
	font-weight: 700;
	text-transform: uppercase;
	letter-spacing: 0.1em;
	color: #72777c;
	line-height: 1;
}
#adminmenu li:has(a[href^="#pw-divider-"]) .wp-menu-arrow,
#adminmenu li:has(a[href^="#pw-divider-"]) .update-plugins {
	display: none;
}
';
	wp_register_style( 'pw-admin-menu-dividers', false, array(), '0.1.0' );
	wp_enqueue_style( 'pw-admin-menu-dividers' );
	wp_add_inline_style( 'pw-admin-menu-dividers', $divider_css );
}, 5);

add_action(
	'admin_enqueue_scripts',
	static function () {
		$screen = get_current_screen();
		if ( ! $screen || ( $screen->post_type ?? '' ) !== 'pw_property' || ! defined( 'PW_PLUGIN_FILE' ) ) {
			return;
		}
		wp_enqueue_script(
			'pw-admin-property-facets',
			plugins_url( 'assets/admin-property-facets.js', PW_PLUGIN_FILE ),
			[ 'jquery', 'cmb2-scripts' ],
			defined( 'PW_VERSION' ) ? PW_VERSION : '1',
			true
		);
	},
	20
);

add_action('admin_enqueue_scripts', function ($hook_suffix) {
	$screen = get_current_screen();
	$pw_cpts = [ 'pw_feature', 'pw_room_type', 'pw_restaurant', 'pw_spa', 'pw_meeting_room', 'pw_amenity', 'pw_policy', 'pw_faq', 'pw_offer', 'pw_nearby', 'pw_experience', 'pw_event', 'pw_property', 'pw_contact' ];
	$is_pw_cpt = $screen && in_array( $screen->post_type ?? '', $pw_cpts, true );
	$is_pw_tax = $screen && ( $screen->taxonomy ?? '' ) !== '' && strpos( $screen->taxonomy, 'pw_' ) === 0;

	if ( $is_pw_cpt || $is_pw_tax ) {
		$css = '.cmb2-postbox .cmb-row:not(:last-of-type),.cmb2-postbox .cmb-repeatable-group:not(:last-of-type),.cmb-type-group .cmb-row:not(:last-of-type),.cmb-type-group .cmb-repeatable-group:not(:last-of-type){border-bottom:0}.cmb2-postbox .cmb-row,.cmb-type-group .cmb-row{padding:0 0 .6em;margin:0 0 .4em}.pw-facet-fixed-rows .cmb-add-group-row,.pw-facet-fixed-rows .cmb-remove-group-row,.pw-facet-fixed-rows .cmb-remove-group-row-button,.pw-facet-fixed-rows .cmb-remove-field-row{display:none!important}';
		wp_register_style( 'pw-cmb2-overrides', false, [ 'cmb2-styles' ], '0.1.0' );
		wp_enqueue_style( 'pw-cmb2-overrides' );
		wp_add_inline_style( 'pw-cmb2-overrides', $css );
	}

	if (!isset($_GET['page']) || $_GET['page'] !== pw_admin_page_slug()) {
		return;
	}

	wp_enqueue_style(
		'pw-admin-fonts',
		'https://fonts.googleapis.com/css2?family=Inter+Tight:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600&display=swap',
		array(),
		null
	);

	$css = "
.pw-admin{
  --bg:#F5F3EE;--surface:#FFFFFF;--card:#FAFAF8;--card2:#F0EDE6;
  --border:rgba(0,0,0,0.09);--border2:rgba(0,0,0,0.18);
  --text:#1A1917;--sub:#504D48;--muted:#7F7C77;
  --primary:#C92A08;--primaryDark:#A32206;
  font-family:'Inter Tight',system-ui,sans-serif;
}
.pw-admin .pw-header{display:flex;align-items:center;justify-content:space-between;gap:14px;margin:6px 0 10px}
.pw-admin .pw-brand{display:flex;align-items:center;gap:10px;min-width:0}
.pw-admin .pw-logo{width:26px;height:26px;display:block}
.pw-admin .pw-brand-text{display:flex;align-items:center;gap:10px;min-width:0}
.pw-admin .pw-title{font-size:20px;font-weight:650;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;line-height:1.15}
.pw-admin .pw-version{font-size:12px;font-weight:700;color:var(--muted);background:rgba(0,0,0,0.04);border:1px solid var(--border);padding:2px 8px;border-radius:999px}
.pw-admin .pw-mode{font-size:12px;font-weight:700;color:var(--text);background:rgba(0,0,0,0.04);border:1px solid var(--border);padding:2px 8px;border-radius:999px;white-space:nowrap}
.pw-admin .pw-mode.is-development{background:rgba(201,42,8,0.12);border-color:rgba(201,42,8,0.25)}
.pw-admin .pw-mode.is-production{background:rgba(30,142,62,0.12);border-color:rgba(30,142,62,0.25)}
.pw-admin .pw-tabs{display:flex;gap:6px;border-bottom:1px solid var(--border);margin:10px 0 16px;overflow-x:auto;scrollbar-width:none;-ms-overflow-style:none}
.pw-admin .pw-tabs::-webkit-scrollbar{display:none}
.pw-admin .pw-tab{display:inline-flex;align-items:center;padding:10px 12px;font-size:13px;font-weight:600;color:var(--muted);text-decoration:none;border-bottom:2px solid transparent;margin-bottom:-1px}
.pw-admin .pw-tab:hover{color:var(--text)}
.pw-admin .pw-tab.is-active{color:var(--text);border-bottom-color:var(--primary)}

.pw-admin .pw-card{background:var(--card);border:1px solid var(--border);border-radius:10px;overflow:hidden;max-width:980px}
.pw-admin .pw-card + .pw-card{margin-top:22px}
.pw-admin .pw-card-head{background:var(--card2);border-bottom:1px solid var(--border);padding:12px 18px;display:flex;align-items:center;justify-content:space-between}
.pw-admin button.pw-accordion-trigger{width:100%;margin:0;box-sizing:border-box;font:inherit;text-align:left;cursor:pointer;color:inherit;border:none;border-radius:0;appearance:none;-webkit-appearance:none}
.pw-admin button.pw-accordion-trigger:focus{outline:2px solid var(--border2);outline-offset:2px}
.pw-admin .pw-accordion-item.is-expanded .pw-card-head{border-bottom:1px solid var(--border)}
.pw-admin .pw-accordion-item:not(.is-expanded) .pw-card-head{border-bottom:none}
.pw-admin .pw-accordion-chevron{flex:0 0 auto;display:flex;align-items:center;justify-content:center;width:28px;height:28px;color:var(--sub);opacity:0.9}
.pw-admin .pw-accordion-chevron::before{content:'';display:block;width:8px;height:8px;border-right:2px solid currentColor;border-bottom:2px solid currentColor;transform:rotate(45deg);transition:transform .18s ease;margin-top:-4px}
.pw-admin .pw-accordion-item.is-expanded .pw-accordion-chevron::before{transform:rotate(-135deg);margin-top:4px}
.pw-admin .pw-card-title{font-size:12px;font-weight:600;letter-spacing:0.02em;color:var(--sub)}
.pw-admin .pw-card-body{padding:16px}
.pw-admin .pw-subsection-title{margin:0 0 10px;font-size:14px;font-weight:700;color:var(--text);letter-spacing:0.02em}
.pw-admin .pw-section-divider{border:0;border-top:1px solid var(--border);margin:20px 0}
.pw-admin .pw-subsection + .pw-section-divider{margin-top:0}
.pw-admin .button.pw-button-purge-all{background:#b32d2d!important;border-color:#8f2424!important;color:#fff!important;box-shadow:none!important;text-shadow:none!important}
.pw-admin .button.pw-button-purge-all:hover,.pw-admin .button.pw-button-purge-all:focus{background:#961f1f!important;border-color:#6e1818!important;color:#fff!important}
.pw-admin .button.pw-button-purge-all:focus{box-shadow:0 0 0 1px #fff,0 0 0 3px rgba(179,45,45,0.35)!important}
.pw-admin .pw-card-body .form-table th{width:240px}
.pw-admin .pw-card-body .cmb-form{margin-top:14px}
.pw-admin .pw-card-body .cmb2-wrap .cmb-row{display:flex;flex-wrap:wrap;align-items:flex-start;margin-bottom:14px;clear:both}
.pw-admin .pw-card-body .cmb2-wrap .cmb-row:after{display:none}
.pw-admin .pw-card-body .cmb2-wrap .cmb-th{float:none;width:240px;flex:0 0 240px;padding:10px 14px 10px 0;vertical-align:top}
.pw-admin .pw-card-body .cmb2-wrap .cmb-td{float:none;flex:1 1 0;min-width:200px;padding:10px 0}
.pw-admin .pw-card-body .cmb2-wrap .cmb2-radio-list{display:flex;flex-wrap:wrap;gap:12px 20px;align-items:center}
.pw-admin .pw-card-body .cmb2-wrap .cmb2-radio-list li{margin:0}
.pw-admin .pw-card-body input.regular-text{border-radius:6px;border-color:rgba(0,0,0,0.15)}
.pw-admin .pw-card-body input.regular-text:focus{border-color:var(--border2);box-shadow:0 0 0 1px var(--border2)}
.pw-admin .pw-field{display:inline-flex;align-items:center;gap:8px}
.pw-admin .pw-valid{width:18px;height:18px;border-radius:999px;background:#1e8e3e;display:none;position:relative;flex:0 0 auto}
.pw-admin .pw-valid::after{content:'';position:absolute;left:6px;top:3px;width:5px;height:10px;border-right:2px solid #fff;border-bottom:2px solid #fff;transform:rotate(45deg)}
.pw-admin .pw-field.is-valid .pw-valid{display:inline-block}
.pw-admin .pw-field.is-valid input{border-color:rgba(30,142,62,0.55)}
.pw-admin .pw-field.is-invalid .pw-valid{display:inline-block;background:#b32d15}
.pw-admin .pw-field.is-invalid .pw-valid::after{
  left:4px;
  top:4px;
  width:10px;
  height:10px;
  border:0;
  transform:none;
  background:
    linear-gradient(45deg, transparent 47%, #fff 47%, #fff 53%, transparent 53%),
    linear-gradient(-45deg, transparent 47%, #fff 47%, #fff 53%, transparent 53%);
}
.pw-admin .pw-field.is-invalid input{border-color:rgba(201,42,8,0.55)}
.pw-admin .pw-field.is-invalid input:focus{border-color:rgba(201,42,8,0.65);box-shadow:0 0 0 1px rgba(201,42,8,0.25)}
.pw-admin .pw-validation-error{margin:10px 0 0;color:#b32d15;font-size:13px;font-weight:600;display:none}

.pw-admin .pw-split{display:grid;grid-template-columns:260px 1fr;min-height:420px}
.pw-admin .pw-vnav{background:var(--card2);border-right:1px solid var(--border);padding:10px;display:flex;flex-direction:column;gap:6px}
.pw-admin .pw-vnav a{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:12px;text-decoration:none;color:var(--sub);font-weight:650;border:1px solid transparent;transition:background .12s ease,color .12s ease,border-color .12s ease,transform .12s ease}
.pw-admin .pw-vnav a:hover{background:rgba(127,127,125,0.08);color:var(--text);transform:translateY(-1px)}
.pw-admin .pw-vnav a.is-active{background:rgba(201,42,8,0.12);color:var(--text);border-color:rgba(201,42,8,0.25);position:relative}
.pw-admin .pw-vnav a.is-active::before{content:'';position:absolute;left:-1px;top:8px;bottom:8px;width:3px;background:var(--primary);border-radius:3px}
.pw-admin .pw-vcontent{padding:14px}
.pw-admin .pw-section{display:none}
.pw-admin .pw-section.is-active{display:block}

.pw-admin .pw-footer{max-width:980px;margin-top:12px;color:var(--muted);font-size:12px}
.pw-admin .pw-footer-link{color:var(--muted);text-decoration:none}
.pw-admin .pw-footer-link:hover{color:var(--text);text-decoration:underline}

@media (max-width: 960px){
  .pw-admin .pw-header{flex-direction:column;align-items:flex-start}
  .pw-admin .pw-split{grid-template-columns:1fr}
  .pw-admin .pw-vnav{border-right:none;border-bottom:1px solid var(--border)}
  .pw-admin .pw-card{max-width:none}
  .pw-admin .pw-card-body .cmb2-wrap .cmb-row{flex-direction:column}
  .pw-admin .pw-card-body .cmb2-wrap .cmb-th{flex:1 1 auto;width:100%}
  .pw-admin .pw-card-body .cmb2-wrap .cmb-td{min-width:0}
}
.pw-admin .pw-default-property-row.pw-is-hidden{display:none!important}
";

	wp_register_style('pw-admin', false, array(), '0.1.0');
	wp_enqueue_style('pw-admin');
	wp_add_inline_style('pw-admin', $css);

	$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
	if ( $tab === '' || $tab === 'settings' ) {
		wp_enqueue_script(
			'pw-admin-settings',
			plugins_url( 'assets/admin-settings.js', PW_PLUGIN_FILE ),
			array(),
			defined( 'PW_VERSION' ) ? PW_VERSION : '0',
			true
		);
	}
});

add_action('admin_footer', function () {
	$screen = get_current_screen();
	if ($screen && $screen->id === 'pw_room_type') {
		?>
		<script>
		(function () {
		  var maxOcc      = document.querySelector('#_pw_max_occupancy');
		  var maxAdults   = document.querySelector('#_pw_max_adults');
		  var maxChildren = document.querySelector('#_pw_max_children');

		  if (!maxOcc || !maxAdults || !maxChildren) return;

		  var errorEl = document.createElement('p');
		  errorEl.style.cssText = 'color:#b32d15;font-weight:600;margin-top:6px;display:none';
		  errorEl.textContent = 'Max adults + max children must not exceed max occupancy.';
		  maxChildren.closest('td') && maxChildren.closest('td').appendChild(errorEl);

		  function validate() {
		    var occ      = parseInt(maxOcc.value, 10) || 0;
		    var adults   = parseInt(maxAdults.value, 10) || 0;
		    var children = parseInt(maxChildren.value, 10) || 0;
		    var invalid  = (adults + children) > occ && occ > 0;
		    errorEl.style.display = invalid ? '' : 'none';
		    return !invalid;
		  }

		  [maxOcc, maxAdults, maxChildren].forEach(function (el) {
		    el.addEventListener('input', validate);
		  });

		  var form = document.getElementById('post');
		  if (form) {
		    form.addEventListener('submit', function (e) {
		      if (!validate()) {
		        e.preventDefault();
		        maxAdults.scrollIntoView({ behavior: 'smooth', block: 'center' });
		      }
		    });
		  }
		})();
		</script>
		<?php
	}
});

add_action('admin_footer', function () {
	if (!isset($_GET['page']) || $_GET['page'] !== pw_admin_page_slug()) {
		return;
	}

	?>
	<script>
	(function () {
	  const root = document.querySelector('.pw-admin');
	  if (!root) return;

	  const vnav = root.querySelector('.pw-vnav');
	  const links = vnav ? Array.from(vnav.querySelectorAll('a[data-pw-sub]')) : [];
	  const panels = Array.from(root.querySelectorAll('.pw-section[data-pw-panel]'));

	  function setActive(key) {
	    links.forEach(a => a.classList.toggle('is-active', a.getAttribute('data-pw-sub') === key));
	    panels.forEach(p => p.classList.toggle('is-active', p.getAttribute('data-pw-panel') === key));
	  }

	  function updateUrl(key) {
	    const u = new URL(window.location.href);
	    u.searchParams.set('sub', key);
	    window.history.replaceState({}, '', u.toString());
	  }

	  links.forEach(a => {
	    a.addEventListener('click', (e) => {
	      const key = a.getAttribute('data-pw-sub');
	      if (!key) return;
	      e.preventDefault();
	      setActive(key);
	      updateUrl(key);
	    });
	  });

	  function isValidHttpUrl(s) {
	    if (!s) return false;
	    try {
	      const u = new URL(s);
	      return u.protocol === 'http:' || u.protocol === 'https:';
	    } catch (e) {
	      return false;
	    }
	  }
	  function setFieldValidity(input, ok) {
	    const wrap = input.closest('.pw-field');
	    if (!wrap) return;
	    wrap.classList.toggle('is-valid', !!ok);
	    wrap.classList.toggle('is-invalid', !ok);
	  }

	  function clearFieldValidity(input) {
	    setFieldValidity(input, false);
	    const wrap = input.closest('.pw-field');
	    if (wrap) {
	      wrap.classList.remove('is-valid');
	      wrap.classList.remove('is-invalid');
	    }
	  }

	  function getUrlInputs(form) {
	    return Array.from(form.querySelectorAll('input[data-pw-validate="url"]'));
	  }

	  async function checkUrl(url) {
	    const timeoutMs = 7000;
	    const controller = new AbortController();
	    const t = setTimeout(() => controller.abort(), timeoutMs);
	    try {
	      // Check URLs by actual HTTP response. 2xx-3xx counts as "regular".
	      const res = await fetch(url, {
	        method: 'HEAD',
	        mode: 'cors',
	        redirect: 'follow',
	        signal: controller.signal,
	      });
	      if (!res || typeof res.status !== 'number') return false;

	      if (res.status >= 200 && res.status < 400) return true;

	      // Some servers block HEAD but allow GET; fall back to GET (CORS required to read status).
	      if (res.status === 405 || res.status === 501) {
	        const controller2 = new AbortController();
	        const t2 = setTimeout(() => controller2.abort(), timeoutMs);
	        try {
	          const res2 = await fetch(url, {
	            method: 'GET',
	            mode: 'cors',
	            redirect: 'follow',
	            cache: 'no-store',
	            signal: controller2.signal,
	          });
	          if (!res2 || typeof res2.status !== 'number') return false;
	          return res2.status >= 200 && res2.status < 400;
	        } catch (e2) {
	          return false;
	        } finally {
	          clearTimeout(t2);
	        }
	      }

	      return false;
	    } catch (e) {
	      // If CORS blocks reading status, try a no-cors GET; if the request succeeds, treat as reachable.
	      try {
	        clearTimeout(t);
	        const controller2 = new AbortController();
	        const t2 = setTimeout(() => controller2.abort(), timeoutMs);
	        await fetch(url, {
	          method: 'GET',
	          mode: 'no-cors',
	          redirect: 'follow',
	          cache: 'no-store',
	          signal: controller2.signal,
	        });
	        clearTimeout(t2);
	        return true;
	      } catch (e2) {
	        return false;
	      }
	    } finally {
	      clearTimeout(t);
	    }
	  }

	  function getSubmitElement(e, form) {
	    if (e && e.submitter) return e.submitter;
	    return form.querySelector('input[type="submit"], button[type="submit"]');
	  }

	  function getOrCreateErrorEl(form) {
	    let el = form.querySelector('.pw-validation-error');
	    if (el) return el;
	    const submitEl = form.querySelector('input[type="submit"], button[type="submit"]');
	    el = document.createElement('div');
	    el.className = 'pw-validation-error';
	    el.textContent = 'One or more URLs are not responding. Please fix the highlighted fields and try again.';
	    if (submitEl && submitEl.parentNode) {
	      submitEl.insertAdjacentElement('afterend', el);
	    } else {
	      form.appendChild(el);
	    }
	    return el;
	  }

	  async function validateUrlsOnSave(form, e) {
	    const urlInputs = getUrlInputs(form);
	    const errorEl = getOrCreateErrorEl(form);
	    errorEl.style.display = 'none';

	    urlInputs.forEach((input) => clearFieldValidity(input));

	    // Allow empty URL fields (social URLs are optional).
	    const checks = [];
	    for (const input of urlInputs) {
	      const raw = String(input.value || '').trim();
	      if (!raw) {
	        // Leave empty optional URLs as neutral (no tick).
	        continue;
	      }
	      if (!isValidHttpUrl(raw)) {
	        setFieldValidity(input, false);
	        checks.push(false);
	        continue;
	      }
	      checks.push(checkUrl(raw).then(ok => {
	        setFieldValidity(input, ok);
	        return ok;
	      }));
	    }

	    const results = await Promise.all(checks);
	    const anyInvalid = results.some(r => r === false);
	    if (anyInvalid) {
	      errorEl.style.display = '';
	      return false;
	    }
	    return true;
	  }

	  root.querySelectorAll('form').forEach((form) => {
	    form.addEventListener('submit', async (e) => {
	      if (form.dataset.pwValidating === '1') return;
	      const urlInputs = getUrlInputs(form);
	      if (!urlInputs.length) return; // nothing to validate

	      const submitEl = getSubmitElement(e, form);
	      const originalText = submitEl && 'value' in submitEl ? submitEl.value : (submitEl ? submitEl.textContent : '');
	      if (submitEl) submitEl.disabled = true;
	      if (submitEl && 'value' in submitEl) submitEl.value = 'Saving…';
	      form.dataset.pwValidating = '1';

	      const ok = await validateUrlsOnSave(form, e);
	      form.dataset.pwValidating = '';
	      if (submitEl && 'value' in submitEl) submitEl.value = originalText;
	      if (submitEl) submitEl.disabled = false;
	      if (!ok) {
	        e.preventDefault();
	        return;
	      }
	      // Let WordPress/options.php proceed naturally.
	    });
	  });
	})();
	</script>
	<?php
});

add_action(
	'admin_footer',
	function () {
		if ( ! isset( $_GET['page'] ) || $_GET['page'] !== pw_admin_page_slug() ) {
			return;
		}
		?>
	<script>
	function pwConfirmPurgePluginData(form) {
		var input = document.getElementById('pw-purge-password');
		if (!input || input.value !== 'porticowebworks.com') {
			window.alert('Enter the confirmation phrase exactly: porticowebworks.com');
			return false;
		}
		return window.confirm('Permanently delete ALL Portico plugin posts and plugin taxonomy terms? This cannot be undone.');
	}
	(function () {
		var root = document.querySelector('.pw-data-accordion');
		if (!root) return;
		root.addEventListener('click', function (e) {
			var btn = e.target.closest('.pw-accordion-trigger');
			if (!btn || !root.contains(btn)) return;
			var item = btn.closest('.pw-accordion-item');
			if (!item) return;
			var panel = item.querySelector('.pw-accordion-panel');
			var wasExpanded = item.classList.contains('is-expanded');
			root.querySelectorAll('.pw-accordion-item').forEach(function (other) {
				other.classList.remove('is-expanded');
				var p = other.querySelector('.pw-accordion-panel');
				var b = other.querySelector('.pw-accordion-trigger');
				if (p) p.hidden = true;
				if (b) b.setAttribute('aria-expanded', 'false');
			});
			if (!wasExpanded) {
				item.classList.add('is-expanded');
				if (panel) panel.hidden = false;
				btn.setAttribute('aria-expanded', 'true');
			}
		});
	})();
	</script>
		<?php
	}
);

