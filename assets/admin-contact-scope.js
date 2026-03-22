(function ($) {
	'use strict';

	function getPropertySelect() {
		return $('select[name="_pw_property_id"]');
	}

	function getScopeCptSelect() {
		return $('select[name="_pw_scope_cpt"]');
	}

	function getScopeIdSelect() {
		return $('select[name="_pw_scope_id"]');
	}

	function buildPath(propertyId, postType) {
		return (
			'/pw/v1/contact-scope-posts?property_id=' +
			encodeURIComponent(String(propertyId)) +
			'&post_type=' +
			encodeURIComponent(postType)
		);
	}

	function loadScopeOptions() {
		var $prop = getPropertySelect();
		var $scopeCpt = getScopeCptSelect();
		var $scopeId = getScopeIdSelect();
		if (!$scopeCpt.length || !$scopeId.length) {
			return;
		}

		var cpt = String($scopeCpt.val() || '');
		var propertyId = parseInt($prop.val(), 10) || 0;
		var currentOutlet = $scopeId.val();
		var map = (window.pwContactScope && pwContactScope.outletMap) || {};

		if (cpt === 'property' || cpt === 'all') {
			$scopeId.prop('disabled', true).val('');
			$scopeId.find('option').not(':first').remove();
			return;
		}

		var wpPt = map[cpt];
		if (!wpPt || propertyId <= 0) {
			$scopeId.prop('disabled', true).val('');
			$scopeId.find('option').not(':first').remove();
			return;
		}

		$scopeId.prop('disabled', false);
		var $first = $scopeId.find('option').first().clone();
		$scopeId.empty().append($first);

		if (!window.wp || !wp.apiFetch) {
			return;
		}

		wp.apiFetch({ path: buildPath(propertyId, wpPt) })
			.then(function (rows) {
				if (!Array.isArray(rows)) {
					return;
				}
				rows.forEach(function (row) {
					if (!row || row.id == null) {
						return;
					}
					var t = row.title != null ? String(row.title) : '';
					$('<option/>', { value: String(row.id), text: t }).appendTo($scopeId);
				});
				if (currentOutlet) {
					$scopeId.val(String(currentOutlet));
				}
			})
			.catch(function () {});
	}

	$(function () {
		if (!getScopeIdSelect().length) {
			return;
		}
		getPropertySelect().on('change', loadScopeOptions);
		getScopeCptSelect().on('change', loadScopeOptions);
		loadScopeOptions();
	});
})(jQuery);
