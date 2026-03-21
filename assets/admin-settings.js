(function () {
	var form = document.getElementById('pw_settings');
	if (!form) {
		return;
	}
	var radios = form.querySelectorAll('input[name="pw_property_mode"]');
	var row = form.querySelector('.pw-default-property-row');
	if (!radios.length || !row) {
		return;
	}
	function sync() {
		var selected = form.querySelector('input[name="pw_property_mode"]:checked');
		var multi = selected && selected.value === 'multi';
		row.classList.toggle('pw-is-hidden', !!multi);
		row.setAttribute('aria-hidden', multi ? 'true' : 'false');
	}
	for (var i = 0; i < radios.length; i++) {
		radios[i].addEventListener('change', sync);
	}
	sync();
})();
