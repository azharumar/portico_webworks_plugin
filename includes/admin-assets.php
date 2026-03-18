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
.portico-webworks-admin .pw-brand-text{display:flex;align-items:baseline;gap:10px;min-width:0}
.portico-webworks-admin .pw-title{font-size:20px;font-weight:650;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.portico-webworks-admin .pw-version{font-size:12px;font-weight:700;color:var(--muted);background:rgba(0,0,0,0.04);border:1px solid var(--border);padding:2px 8px;border-radius:999px}
.portico-webworks-admin .pw-header-actions{display:flex;align-items:center;gap:10px}
.portico-webworks-admin .pw-search{display:flex;align-items:center}
.portico-webworks-admin .pw-search-input{width:260px;max-width:42vw;border-radius:999px;border:1px solid rgba(0,0,0,0.15);padding:6px 10px;background:var(--surface)}
.portico-webworks-admin .pw-search-input:focus{border-color:var(--border2);box-shadow:0 0 0 1px var(--border2);outline:0}
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
.portico-webworks-admin .pw-valid::after{content:'';position:absolute;left:5px;top:3px;width:6px;height:10px;border-right:2px solid #fff;border-bottom:2px solid #fff;transform:rotate(40deg)}
.portico-webworks-admin .pw-field.is-valid .pw-valid{display:inline-block}
.portico-webworks-admin .pw-field.is-valid input{border-color:rgba(30,142,62,0.55)}
.portico-webworks-admin .pw-hit{background:rgba(201,42,8,0.10);box-shadow:0 0 0 2px rgba(201,42,8,0.15);border-radius:6px}

.portico-webworks-admin .pw-split{display:grid;grid-template-columns:260px 1fr;min-height:420px}
.portico-webworks-admin .pw-vnav{background:var(--card2);border-right:1px solid var(--border);padding:10px}
.portico-webworks-admin .pw-vnav a{display:flex;align-items:center;gap:10px;padding:10px 10px;border-radius:6px;text-decoration:none;color:var(--sub);font-weight:600}
.portico-webworks-admin .pw-vnav a:hover{background:rgba(127,127,125,0.06);color:var(--text)}
.portico-webworks-admin .pw-vnav a.is-active{background:rgba(201,42,8,0.10);color:var(--text);position:relative}
.portico-webworks-admin .pw-vnav a.is-active::before{content:'';position:absolute;left:0;top:8px;bottom:8px;width:3px;background:var(--primary);border-radius:3px}
.portico-webworks-admin .pw-vcontent{padding:14px}
.portico-webworks-admin .pw-section{display:none}
.portico-webworks-admin .pw-section.is-active{display:block}

.portico-webworks-admin .pw-footer{max-width:980px;margin-top:12px;color:var(--muted);font-size:12px}
.portico-webworks-admin .pw-footer-link{color:var(--muted);text-decoration:none}
.portico-webworks-admin .pw-footer-link:hover{color:var(--text);text-decoration:underline}

@media (max-width: 960px){
  .portico-webworks-admin .pw-header{flex-direction:column;align-items:flex-start}
  .portico-webworks-admin .pw-search-input{width:min(520px,100%);max-width:100%}
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

	  function updateValidation(el) {
	    if (!el) return;
	    const type = el.getAttribute('data-pw-validate');
	    if (!type) return;
	    const wrap = el.closest('.pw-field');
	    if (!wrap) return;
	    const v = String(el.value || '').trim();
	    const ok = (type === 'url') ? isValidHttpUrl(v) : false;
	    wrap.classList.toggle('is-valid', ok);
	  }

	  root.querySelectorAll('input[data-pw-validate]').forEach((el) => {
	    updateValidation(el);
	    el.addEventListener('input', () => updateValidation(el));
	    el.addEventListener('blur', () => updateValidation(el));
	  });

	  const search = root.querySelector('#pw-search-input');
	  if (search) {
	    function clearHits() {
	      root.querySelectorAll('.pw-hit').forEach(n => n.classList.remove('pw-hit'));
	    }
	    function jumpToFirstHit(q) {
	      const query = q.trim().toLowerCase();
	      if (!query) return;
	      const inputs = Array.from(root.querySelectorAll('input[data-pw-label]'));
	      const hit = inputs.find((el) => (el.getAttribute('data-pw-label') || '').toLowerCase().includes(query) || (el.getAttribute('placeholder') || '').toLowerCase().includes(query));
	      if (!hit) return;

	      const panel = hit.closest('.pw-section[data-pw-panel]');
	      if (panel) {
	        const key = panel.getAttribute('data-pw-panel');
	        if (key) {
	          setActive(key);
	          updateUrl(key);
	        }
	      }

	      clearHits();
	      const row = hit.closest('tr') || hit;
	      row.classList.add('pw-hit');
	      hit.scrollIntoView({behavior:'smooth',block:'center'});
	      hit.focus({preventScroll:true});
	    }

	    search.addEventListener('keydown', (e) => {
	      if (e.key === 'Enter') {
	        e.preventDefault();
	        jumpToFirstHit(search.value || '');
	      }
	      if (e.key === 'Escape') {
	        search.value = '';
	        clearHits();
	      }
	    });

	    if (vnav) {
	      search.addEventListener('input', () => {
	        const q = String(search.value || '').trim().toLowerCase();
	        if (!q) {
	          links.forEach(a => a.style.display = '');
	          return;
	        }
	        links.forEach((a) => {
	          const t = (a.textContent || '').trim().toLowerCase();
	          a.style.display = t.includes(q) ? '' : 'none';
	        });
	      });
	    }
	  }
	})();
	</script>
	<?php
});

