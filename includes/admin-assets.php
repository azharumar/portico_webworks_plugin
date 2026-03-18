<?php

if (!defined('ABSPATH')) {
	exit;
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
.portico-webworks-admin{
  --bg:#F5F3EE;--surface:#FFFFFF;--card:#FAFAF8;--card2:#F0EDE6;
  --border:rgba(0,0,0,0.09);--border2:rgba(0,0,0,0.18);
  --text:#1A1917;--sub:#504D48;--muted:#7F7C77;
  --primary:#C92A08;--primaryDark:#A32206;
  font-family:'Inter Tight',system-ui,sans-serif;
}
.portico-webworks-admin .pw-header{display:flex;align-items:center;justify-content:space-between;gap:14px;margin:6px 0 10px}
.portico-webworks-admin .pw-brand{display:flex;align-items:center;gap:10px;min-width:0}
.portico-webworks-admin .pw-logo{width:26px;height:26px;display:block}
.portico-webworks-admin .pw-brand-text{display:flex;align-items:center;gap:10px;min-width:0}
.portico-webworks-admin .pw-title{font-size:20px;font-weight:650;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;line-height:1.15}
.portico-webworks-admin .pw-version{font-size:12px;font-weight:700;color:var(--muted);background:rgba(0,0,0,0.04);border:1px solid var(--border);padding:2px 8px;border-radius:999px}
.portico-webworks-admin .pw-tabs{display:flex;gap:6px;border-bottom:1px solid var(--border);margin:10px 0 16px;overflow-x:auto;scrollbar-width:none;-ms-overflow-style:none}
.portico-webworks-admin .pw-tabs::-webkit-scrollbar{display:none}
.portico-webworks-admin .pw-tab{display:inline-flex;align-items:center;padding:10px 12px;font-size:13px;font-weight:600;color:var(--muted);text-decoration:none;border-bottom:2px solid transparent;margin-bottom:-1px}
.portico-webworks-admin .pw-tab:hover{color:var(--text)}
.portico-webworks-admin .pw-tab.is-active{color:var(--text);border-bottom-color:var(--primary)}

.portico-webworks-admin .pw-card{background:var(--card);border:1px solid var(--border);border-radius:10px;overflow:hidden;max-width:980px}
.portico-webworks-admin .pw-card-head{background:var(--card2);border-bottom:1px solid var(--border);padding:10px 14px;display:flex;align-items:center;justify-content:space-between}
.portico-webworks-admin .pw-card-title{font-size:12px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:var(--sub)}
.portico-webworks-admin .pw-card-body{padding:14px}
.portico-webworks-admin .pw-card-body .form-table th{width:240px}
.portico-webworks-admin .pw-card-body input.regular-text{border-radius:6px;border-color:rgba(0,0,0,0.15)}
.portico-webworks-admin .pw-card-body input.regular-text:focus{border-color:var(--border2);box-shadow:0 0 0 1px var(--border2)}
.portico-webworks-admin .pw-field{display:inline-flex;align-items:center;gap:8px}
.portico-webworks-admin .pw-valid{width:18px;height:18px;border-radius:999px;background:#1e8e3e;display:none;position:relative;flex:0 0 auto}
.portico-webworks-admin .pw-valid::after{content:'';position:absolute;left:6px;top:3px;width:5px;height:10px;border-right:2px solid #fff;border-bottom:2px solid #fff;transform:rotate(45deg)}
.portico-webworks-admin .pw-field.is-valid .pw-valid{display:inline-block}
.portico-webworks-admin .pw-field.is-valid input{border-color:rgba(30,142,62,0.55)}
.portico-webworks-admin .pw-field.is-invalid .pw-valid{display:inline-block;background:#b32d15}
.portico-webworks-admin .pw-field.is-invalid .pw-valid::after{
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
.portico-webworks-admin .pw-field.is-invalid input{border-color:rgba(201,42,8,0.55)}
.portico-webworks-admin .pw-field.is-invalid input:focus{border-color:rgba(201,42,8,0.65);box-shadow:0 0 0 1px rgba(201,42,8,0.25)}
.portico-webworks-admin .pw-validation-error{margin:10px 0 0;color:#b32d15;font-size:13px;font-weight:600;display:none}

.portico-webworks-admin .pw-split{display:grid;grid-template-columns:260px 1fr;min-height:420px}
.portico-webworks-admin .pw-vnav{background:var(--card2);border-right:1px solid var(--border);padding:10px;display:flex;flex-direction:column;gap:6px}
.portico-webworks-admin .pw-vnav a{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:12px;text-decoration:none;color:var(--sub);font-weight:650;border:1px solid transparent;transition:background .12s ease,color .12s ease,border-color .12s ease,transform .12s ease}
.portico-webworks-admin .pw-vnav a:hover{background:rgba(127,127,125,0.08);color:var(--text);transform:translateY(-1px)}
.portico-webworks-admin .pw-vnav a.is-active{background:rgba(201,42,8,0.12);color:var(--text);border-color:rgba(201,42,8,0.25);position:relative}
.portico-webworks-admin .pw-vnav a.is-active::before{content:'';position:absolute;left:-1px;top:8px;bottom:8px;width:3px;background:var(--primary);border-radius:3px}
.portico-webworks-admin .pw-vcontent{padding:14px}
.portico-webworks-admin .pw-section{display:none}
.portico-webworks-admin .pw-section.is-active{display:block}

.portico-webworks-admin .pw-footer{max-width:980px;margin-top:12px;color:var(--muted);font-size:12px}
.portico-webworks-admin .pw-footer-link{color:var(--muted);text-decoration:none}
.portico-webworks-admin .pw-footer-link:hover{color:var(--text);text-decoration:underline}

@media (max-width: 960px){
  .portico-webworks-admin .pw-header{flex-direction:column;align-items:flex-start}
  .portico-webworks-admin .pw-split{grid-template-columns:1fr}
  .portico-webworks-admin .pw-vnav{border-right:none;border-bottom:1px solid var(--border)}
  .portico-webworks-admin .pw-card{max-width:none}
}
";

	wp_register_style('portico-webworks-admin', false, array(), '0.1.0');
	wp_enqueue_style('portico-webworks-admin');
	wp_add_inline_style('portico-webworks-admin', $css);
});

add_action('admin_footer', function () {
	if (!isset($_GET['page']) || $_GET['page'] !== portico_webworks_admin_page_slug()) {
		return;
	}

	?>
	<script>
	(function () {
	  const root = document.querySelector('.portico-webworks-admin');
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

