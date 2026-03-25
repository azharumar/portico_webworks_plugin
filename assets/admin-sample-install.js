(function () {
	'use strict';

	var cfg = typeof pwSampleInstall === 'undefined' ? null : pwSampleInstall;
	if (!cfg || !cfg.expectOrigin) {
		return;
	}

	var form = document.getElementById('pw-sample-install-form');
	var wrap = document.getElementById('pw-sample-install-progress-wrap');
	var bar = document.getElementById('pw-sample-install-progress');
	var label = document.getElementById('pw-sample-install-progress-label');
	var submitBtn = document.getElementById('pw-sample-install-submit');

	if (!form || !wrap || !bar || !label) {
		return;
	}

	function setVisible(visible) {
		wrap.hidden = !visible;
		wrap.style.display = visible ? 'block' : 'none';
	}

	function setProgress(pct, text) {
		pct = Math.max(0, Math.min(100, parseInt(pct, 10) || 0));
		bar.value = pct;
		label.textContent = text || '';
	}

	function resetFormUi() {
		setVisible(false);
		if (submitBtn) {
			submitBtn.disabled = false;
		}
	}

	window.addEventListener('message', function (e) {
		if (!e.data || typeof e.data !== 'object') {
			return;
		}
		if (e.origin !== cfg.expectOrigin && cfg.expectOrigin !== '*') {
			return;
		}
		var t = e.data.type;
		if (t === 'pw_sample_install_progress') {
			setProgress(e.data.percent, e.data.message || '');
			return;
		}
		if (t === 'pw_sample_install_done' && e.data.redirect) {
			window.location.href = e.data.redirect;
			return;
		}
		if (t === 'pw_sample_install_error') {
			resetFormUi();
			var msg = e.data.message || 'Installation failed.';
			window.alert(msg);
		}
	});

	form.addEventListener('submit', function () {
		setVisible(true);
		setProgress(0, cfg.strings && cfg.strings.starting ? cfg.strings.starting : '');
		if (submitBtn) {
			submitBtn.disabled = true;
		}
	});
})();
