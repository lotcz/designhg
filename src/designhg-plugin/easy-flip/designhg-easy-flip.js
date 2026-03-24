/* Real3D Flipbook Helper – Admin JS */
(function ($) {
	'use strict';

	const {
		ajaxUrl,
		nonce,
		action,
	} = window.r3dhData || {};

	/* ── Element refs ──────────────────────────────────────── */
	const $dropZone = $('#r3dhDropZone');
	const $fileInput = $('#r3dhFileInput');
	const $form = $('#r3dhForm');
	const $fileInfo = $('#r3dhFileInfo');
	const $titleInput = $('#r3dhTitle');
	const $createBtn = $('#r3dhCreate');
	const $progress = $('#r3dhProgress');
	const $progressMsg = $('#r3dhProgressMsg');
	const $result = $('#r3dhResult');

	let selectedFile = null;

	$fileInput.on('change', function () {
		handleFile(this.files[0]);
	});

	/* ── Drag & drop ────────────────────────────────────────── */
	$dropZone.on('dragover dragenter', function (e) {
		e.preventDefault();
		e.stopPropagation();
		$dropZone.addClass('dragover');
	});

	$dropZone.on('dragleave dragend', function () {
		$dropZone.removeClass('dragover');
	});

	$dropZone.on('drop', function (e) {
		e.preventDefault();
		e.stopPropagation();
		$dropZone.removeClass('dragover');
		const file = e.originalEvent.dataTransfer.files[0];
		handleFile(file);
	});

	/* ── File selection ─────────────────────────────────────── */
	function handleFile(file) {
		if (!file) return;

		if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
			showError('Please select a PDF file.');
			return;
		}

		selectedFile = file;

		// Display file name + size
		const sizeMB = (file.size / (1024 * 1024)).toFixed(1);
		$fileInfo.text(file.name + ' (' + sizeMB + ' MB)');

		// Auto-fill title from filename
		if (!$titleInput.val()) {
			const baseName = file.name.replace(/\.pdf$/i, '')
				.replace(/[-_]/g, ' ')
				.replace(/\b\w/g, c => c.toUpperCase());
			$titleInput.val(baseName);
		}

		$dropZone.hide();
		$form.show();
		$result.hide().removeClass('r3dh-result--success r3dh-result--error').html('');
	}

	/* ── Create ─────────────────────────────────────────────── */
	$createBtn.on('click', function () {
		if (!selectedFile) return;

		const title = $.trim($titleInput.val());
		if (!title) {
			$titleInput.focus();
			return;
		}

		const formData = new FormData();
		formData.append('action', action);
		formData.append('nonce', nonce);
		formData.append('pdf', selectedFile);
		formData.append('title', title);

		$form.hide();
		$result.hide();
		$progress.show();

		$.ajax({
			url: ajaxUrl,
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function (res) {
				$progress.hide();
				if (res.success) {
					showSuccess(res.data);
				} else {
					showError(res.data.message);
					$form.show();
				}
			},
			error: function () {
				$progress.hide();
				showError();
				$form.show();
			},
		});
	});

	/* ── Result helpers ─────────────────────────────────────── */
	function showSuccess(data) {
		let html = '<h3>✓ ' + escHtml('Flipbook vytvořen') + '</h3>';

		if (data.page_url) {
			html += '<p><strong>Stránka:</strong> <a href="' + escAttr(data.page_url) + '" target="_blank">'
				+ escHtml(data.page_url) + '</a></p>';
		}
		if (data.shortcode) {
			html += '<p><strong>Shortcode:</strong> <code>' + escHtml(data.shortcode) + '</code></p>';
		}
		if (data.thumbnail_url) {
			html += '<p><strong>Náhled:</strong><br>'
				+ '<img src="' + escAttr(data.thumbnail_url) + '" style="max-width:160px;height:auto;margin-top:6px;border:1px solid #c3c4c7;border-radius:4px;"></p>';
		}

		$result.addClass('r3dh-result--success').html(html).show();

		selectedFile = null;
		$titleInput.val('');
		$fileInput.val('');
	}

	function showError(msg) {
		$result.addClass('r3dh-result--error')
			.html('<h3>Error</h3><p>' + escHtml(msg || 'Chyba') + '</p>')
			.show();
	}

	function escHtml(str) {
		return $('<div>').text(str).html();
	}

	function escAttr(str) {
		return $('<div>').text(str).html().replace(/"/g, '&quot;');
	}

})(jQuery);
