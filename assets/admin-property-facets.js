(function ($) {
	'use strict';

	var REPEAT_IDS = ['_pw_sustainability_items_repeat', '_pw_accessibility_items_repeat'];

	function pwSyncFacetGroupTitles($table) {
		if (!$table || !$table.length) {
			return;
		}
		$table.find('.cmb-repeatable-grouping').each(function (i) {
			var $row = $(this);
			var n = i + 1;
			var $sel = $row.find('select').filter(function () {
				var nm = $(this).attr('name') || '';
				return nm.indexOf('[key]') !== -1;
			}).first();
			var label = $sel.length ? $.trim($sel.find('option:selected').text()) : '';
			var text = label ? n + '. ' + label : String(n);
			$row.find('h3.cmb-group-title span').text(text);
		});
	}

	function pwSyncAllFacetGroupTitles() {
		REPEAT_IDS.forEach(function (id) {
			var $el = $('#' + id);
			if ($el.length) {
				pwSyncFacetGroupTitles($el);
			}
		});
	}

	$(document).on('cmb_init', function () {
		pwSyncAllFacetGroupTitles();
	});

	REPEAT_IDS.forEach(function (id) {
		$(document).on('change', '#' + id + ' select', function () {
			var name = $(this).attr('name') || '';
			if (name.indexOf('[key]') !== -1) {
				pwSyncFacetGroupTitles($('#' + id));
			}
		});
	});

	$(document).on('click', '.cmb-add-group-row', function () {
		var sel = $(this).data('selector');
		if (sel && REPEAT_IDS.indexOf(sel) !== -1) {
			window.setTimeout(function () {
				pwSyncFacetGroupTitles($('#' + sel));
			}, 50);
		}
	});

	$(document).on('cmb2_add_row', function (e) {
		if (!e.group) {
			return;
		}
		window.setTimeout(pwSyncAllFacetGroupTitles, 50);
	});

	$(document).on('cmb2_remove_row', function (e) {
		if (!e.group) {
			return;
		}
		window.setTimeout(pwSyncAllFacetGroupTitles, 50);
	});

	$(document).on('cmb2_shift_rows_complete', function () {
		window.setTimeout(pwSyncAllFacetGroupTitles, 0);
	});
})(jQuery);
