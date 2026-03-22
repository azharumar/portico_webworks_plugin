(function () {
	var form = document.getElementById('pw_settings');
	if (!form) return;
	var saved = form.getAttribute('data-pw-saved-mode') || 'single';
	var radios = form.querySelectorAll('.pw-property-mode-radio');
	var wMulti = document.getElementById('pw-mode-switch-warning-multi');
	var wSingle = document.getElementById('pw-mode-switch-warning-single');
	function toggle() {
		var v = form.querySelector('.pw-property-mode-radio:checked');
		v = v ? v.value : saved;
		if (wMulti) wMulti.style.display = (saved === 'multi' && v === 'single') ? '' : 'none';
		if (wSingle) wSingle.style.display = (saved === 'single' && v === 'multi') ? '' : 'none';
	}
	radios.forEach(function (r) { r.addEventListener('change', toggle); });
	toggle();
})();
