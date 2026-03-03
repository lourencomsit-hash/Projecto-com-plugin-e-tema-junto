/* ============================================================
   Breeze SEO — Admin JS
   ============================================================ */
/* global bseoAdmin, wp */

(function ($) {
	'use strict';

	/* ── Helpers ─────────────────────────────────────────────── */

	function showResult($el, message, isError) {
		// Cancel any pending hide timer on this element
		if ($el.data('hideTimer')) {
			clearTimeout($el.data('hideTimer'));
		}
		$el.text(message)
			.removeClass('success error')
			.addClass(isError ? 'error' : 'success');
		// Errors stay until the next action; successes disappear after 15 s
		if (!isError) {
			var timer = setTimeout(function () {
				$el.text('').removeClass('success error');
			}, 15000);
			$el.data('hideTimer', timer);
		}
	}

	function ajaxPost(action, data, $result, successCb) {
		$.ajax({
			url: bseoAdmin.ajaxurl,
			method: 'POST',
			data: $.extend({ action: action, nonce: bseoAdmin.nonce }, data),
			success: function (res) {
				if (res.success) {
					if ($result) showResult($result, res.data.message || bseoAdmin.strings.saved, false);
					if (successCb) successCb(res.data);
				} else {
					if ($result) showResult($result, res.data.message || bseoAdmin.strings.error, true);
				}
			},
			error: function () {
				if ($result) showResult($result, bseoAdmin.strings.error, true);
			}
		});
	}

	/* ── Meta Box: Tabs ──────────────────────────────────────── */

	$(document).on('click', '.bseo-tab-btn', function () {
		var tab = $(this).data('tab');
		var $box = $(this).closest('.bseo-meta-box');
		$box.find('.bseo-tab-btn').removeClass('active');
		$(this).addClass('active');
		$box.find('.bseo-tab-panel').hide();
		$box.find('.bseo-tab-panel[data-tab="' + tab + '"]').show();
	});

	/* ── Meta Box: Character counters ────────────────────────── */

	function updateCharCount($input, countId, min, max) {
		var len = $input.val().length;
		var $el = $('#' + countId);
		if (!$el.length) return;
		var suffix = ' / ' + max + ' characters';
		var warn = '';
		if (len < min) {
			warn = ' <span class="bseo-warn">⚠ too short</span>';
		} else if (len > max) {
			warn = ' <span class="bseo-warn">⚠ too long</span>';
		}
		$el.html(len + suffix + warn);
	}

	$(document).on('input', '#_bseo_title', function () {
		updateCharCount($(this), 'bseo-title-count', 30, 60);
		updateLivePreview();
	});

	$(document).on('input', '#_bseo_description', function () {
		updateCharCount($(this), 'bseo-desc-count', 100, 160);
		updateLivePreview();
	});

	/* ── Meta Box: Live preview ──────────────────────────────── */

	function updateLivePreview() {
		var $box = $('#bseo_meta_box');
		if (!$box.length) return;

		var title = $('#_bseo_title').val() || $('#_bseo_title').attr('placeholder') || '';
		var desc = $('#_bseo_description').val() || $box.find('.bseo-preview-google-desc').data('default') || '';

		$box.find('.bseo-preview-google-title').text(title);
		$box.find('.bseo-preview-google-desc').text(desc);
		$box.find('.bseo-preview-facebook-title').text(title);
		$box.find('.bseo-preview-facebook-desc').text(desc);

		// OG image preview
		var ogUrl = $('#_bseo_og_image').val();
		if (ogUrl) {
			var $img = $box.find('.bseo-preview-facebook-img');
			if ($img.length) {
				$img.attr('src', ogUrl);
			} else {
				$box.find('.bseo-preview-facebook-img-placeholder')
					.replaceWith('<img class="bseo-preview-facebook-img" src="' + ogUrl + '" alt="">');
			}
		}
	}

	$(document).on('input', '#_bseo_og_image', updateLivePreview);

	/* ── Meta Box: OG image preview on existing load ─────────── */
	$(function () {
		var $ogInput = $('#bseo-og-preview-img');
		if ($ogInput.length) {
			$('#_bseo_og_image').on('input', function () {
				$ogInput.attr('src', $(this).val());
			});
		}
	});

	/* ── WP Media Uploader ───────────────────────────────────── */

	$(document).on('click', '.bseo-media-upload-btn', function (e) {
		e.preventDefault();
		var $btn = $(this);
		var targetUrl = $btn.data('target');
		var targetId = $btn.data('target-id');

		var frame = wp.media({
			title: bseoAdmin.strings.choose_image,
			button: { text: bseoAdmin.strings.use_image },
			multiple: false,
			library: { type: 'image' }
		});

		frame.on('select', function () {
			var attachment = frame.state().get('selection').first().toJSON();
			$(targetUrl).val(attachment.url);
			if (targetId) $(targetId).val(attachment.id);
			// Show preview if meta box
			var $preview = $('#bseo-og-preview-img');
			if ($preview.length && targetUrl === '#_bseo_og_image') {
				$preview.attr('src', attachment.url).show();
			}
			updateLivePreview();
		});

		frame.open();
	});

	/* ── Dashboard: Ping sitemap ─────────────────────────────── */

	$('#bseo-ping-sitemap').on('click', function () {
		ajaxPost('bseo_ping_sitemap_now', {}, $('#bseo-ping-result'));
	});

	/* ── Dashboard: Flush rewrite rules ─────────────────────── */

	$('#bseo-flush-rules').on('click', function () {
		ajaxPost('bseo_flush_rules', {}, $('#bseo-flush-result'));
	});

	/* ── Redirects: Save (Add / Update) ─────────────────────── */

	$('#bseo-save-redirect').on('click', function () {
		var editId = $('#redir-edit-id').val();
		var $result = $('#bseo-redirect-result');
		var data = {
			url_old: $('#redir-url-old').val(),
			url_new: $('#redir-url-new').val(),
			redirect_type: $('#redir-type').val(),
			notes: $('#redir-notes').val()
		};

		if (!data.url_old || !data.url_new) {
			showResult($result, 'Old URL and New URL are required.', true);
			return;
		}

		if (editId) {
			data.id = editId;
			ajaxPost('bseo_update_redirect', data, $result, function () {
				// Reload page to reflect update
				window.location.reload();
			});
		} else {
			ajaxPost('bseo_add_redirect', data, $result, function () {
				// Reload to show new row
				window.location.reload();
			});
		}
	});

	/* ── Redirects: Edit row ─────────────────────────────────── */

	$(document).on('click', '.bseo-edit-redirect', function () {
		var $row = $(this).closest('tr');
		$('#redir-url-old').val($row.data('old'));
		$('#redir-url-new').val($row.data('new'));
		$('#redir-type').val($row.data('type'));
		$('#redir-notes').val($row.data('notes'));
		$('#redir-edit-id').val($row.data('id'));
		$('#bseo-redirect-form-title').text('Edit Redirect');
		$('#bseo-cancel-edit').show();
		// Scroll to form
		$('html, body').animate({ scrollTop: $('#bseo-redirect-form-card').offset().top - 60 }, 300);
	});

	/* ── Redirects: Cancel edit ──────────────────────────────── */

	$('#bseo-cancel-edit').on('click', function () {
		$('#redir-url-old, #redir-url-new, #redir-notes').val('');
		$('#redir-type').val('301');
		$('#redir-edit-id').val('');
		$('#bseo-redirect-form-title').text('Add New Redirect');
		$(this).hide();
	});

	/* ── Redirects: Delete row ───────────────────────────────── */

	$(document).on('click', '.bseo-delete-redirect', function () {
		if (!confirm(bseoAdmin.strings.confirm_delete)) return;
		var $row = $(this).closest('tr');
		var id = $row.data('id');
		ajaxPost('bseo_delete_redirect', { id: id }, null, function () {
			$row.fadeOut(300, function () { $(this).remove(); });
		});
	});

	/* ── Redirects: Select all ───────────────────────────────── */

	$('#bseo-check-all, #bseo-select-all').on('click', function () {
		var $checkboxes = $('.bseo-row-check');
		var allChecked = $checkboxes.filter(':checked').length === $checkboxes.length;
		$checkboxes.prop('checked', !allChecked);
	});

	$('#bseo-check-all').on('change', function () {
		$('.bseo-row-check').prop('checked', $(this).prop('checked'));
	});

	/* ── Redirects: Bulk delete ──────────────────────────────── */

	$('#bseo-bulk-delete').on('click', function () {
		var ids = [];
		$('.bseo-row-check:checked').each(function () {
			ids.push($(this).val());
		});
		if (!ids.length) return;
		if (!confirm(bseoAdmin.strings.confirm_bulk_delete)) return;
		ajaxPost('bseo_bulk_delete_redirects', { ids: ids }, $('#bseo-bulk-result'), function () {
			$('.bseo-row-check:checked').closest('tr').fadeOut(300, function () { $(this).remove(); });
		});
	});

	/* ── Redirects: Export CSV ───────────────────────────────── */

	function downloadViaNonce(action) {
		var url = bseoAdmin.ajaxurl + '?action=' + action + '&nonce=' + bseoAdmin.nonce;
		window.location.href = url;
	}

	$('#bseo-export-redirects, #bseo-export-redirects-importer').on('click', function () {
		downloadViaNonce('bseo_export_redirects_csv');
	});

	$('#bseo-export-sample, #bseo-export-sample-importer').on('click', function () {
		downloadViaNonce('bseo_export_sample_csv');
	});

	/* ── Redirects: Import CSV ───────────────────────────────── */

	function importCsv(fileInputId, resultId) {
		var $file = $('#' + fileInputId);
		var $result = $('#' + resultId);
		if (!$file[0].files.length) {
			showResult($result, 'Please choose a CSV file first.', true);
			return;
		}
		var formData = new FormData();
		formData.append('action', 'bseo_import_redirects_csv');
		formData.append('nonce', bseoAdmin.nonce);
		formData.append('csv_file', $file[0].files[0]);

		$.ajax({
			url: bseoAdmin.ajaxurl,
			method: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function (res) {
				if (res.success) {
					var d = res.data;
					var msg = 'Imported: ' + d.imported + ', Skipped: ' + d.skipped;
					if (d.errors && d.errors.length) msg += '. Errors: ' + d.errors.join('; ');
					showResult($result, msg, false);
				} else {
					showResult($result, res.data.message || bseoAdmin.strings.error, true);
				}
			},
			error: function () {
				showResult($result, bseoAdmin.strings.error, true);
			}
		});
	}

	$('#bseo-import-csv').on('click', function () {
		importCsv('bseo-csv-file', 'bseo-csv-result');
	});

	$('#bseo-import-redir-csv-btn').on('click', function () {
		importCsv('bseo-import-redir-csv', 'bseo-import-redir-result');
	});

	/* ── Importer: Force update SEO titles ──────────────────── */

	$('#bseo-force-update-titles').on('click', function () {
		var $result = $('#bseo-force-titles-result');
		$.ajax({
			url: bseoAdmin.ajaxurl,
			method: 'POST',
			data: { action: 'bseo_force_update_titles', nonce: bseoAdmin.importNonce },
			success: function (res) {
				if (res.success) {
					showResult($result, res.data.message || bseoAdmin.strings.saved, false);
				} else {
					showResult($result, res.data.message || bseoAdmin.strings.error, true);
				}
			},
			error: function () { showResult($result, bseoAdmin.strings.error, true); }
		});
	});

	/* ── Importer: Bulk set default OG image ────────────────── */

	$('#bseo-bulk-set-og').on('click', function () {
		ajaxPost('bseo_bulk_set_og_image', {}, $('#bseo-bulk-og-result'));
	});

	/* ── Crawl log: Clear ────────────────────────────────────── */

	$('#bseo-clear-crawl-log').on('click', function () {
		if (!confirm(bseoAdmin.strings.confirm_clear_log)) return;
		ajaxPost('bseo_clear_crawl_log', {}, $('#bseo-clear-log-result'), function () {
			$('#bseo-crawl-log-table tbody tr').fadeOut(300);
		});
	});

	/* ── Importer: Import theme SEO data ─────────────────────── */

	$('#bseo-import-theme').on('click', function () {
		var $result = $('#bseo-import-theme-result');
		$.ajax({
			url: bseoAdmin.ajaxurl,
			method: 'POST',
			data: { action: 'bseo_import_theme_data', nonce: bseoAdmin.importNonce },
			success: function (res) {
				if (res.success) {
					var d = res.data;
					var msg = 'SEO: imported ' + d.seo.imported + ', skipped ' + d.seo.skipped;
					msg += ' | Redirects: imported ' + d.redirects.imported + ', skipped ' + d.redirects.skipped;
					showResult($result, msg, false);
				} else {
					showResult($result, res.data.message || bseoAdmin.strings.error, true);
				}
			},
			error: function () { showResult($result, bseoAdmin.strings.error, true); }
		});
	});

	/* ── Importer: Import Yoast ──────────────────────────────── */

	$('#bseo-import-yoast').on('click', function () {
		var $result = $('#bseo-import-yoast-result');
		$.ajax({
			url: bseoAdmin.ajaxurl,
			method: 'POST',
			data: { action: 'bseo_import_yoast', nonce: bseoAdmin.importNonce },
			success: function (res) {
				if (res.success) {
					showResult($result, 'Imported: ' + res.data.imported + ' post(s).', false);
				} else {
					showResult($result, res.data.message || bseoAdmin.strings.error, true);
				}
			},
			error: function () { showResult($result, bseoAdmin.strings.error, true); }
		});
	});

	/* ── Audit: Export CSV ───────────────────────────────────── */

	$('#bseo-export-audit-csv, #bseo-export-audit-importer').on('click', function () {
		var url = bseoAdmin.ajaxurl + '?action=bseo_export_audit_csv&nonce=' + bseoAdmin.auditNonce;
		window.location.href = url;
	});

	/* ── Settings: Media upload ──────────────────────────────── */
	// (handled by .bseo-media-upload-btn above)

}(jQuery));
