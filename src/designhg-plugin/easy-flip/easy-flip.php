<?php

if (!defined('ABSPATH')) {
	exit;
}

require_once __DIR__ . '/../common/designhg-common.php';
require_once __DIR__ . '/easy-flip-processor.php';

const DESIGNHG_EASY_FLIP_NONCE_KEY = 'easy-flip-nonce';

const DESIGNHG_EASY_FLIP_ACTION = 'easy_flip_action';

add_action(
	'admin_menu',
	function () {
		require_once __DIR__ . '/easy-flip-form.php';

		add_menu_page(
			'Vložit nové číslo',
			'Vložit nové číslo',
			DESIGNHG_ADMIN_CAPABILITY,
			'designhg-easy-flip',
			'designhg_easy_flip_render_page',
			'dashicons-welcome-add-page',
			1
		);
	}
);

add_action(
	'admin_enqueue_scripts',
	function () {
		wp_enqueue_style(
			'designhg-easy-flip-style',
			plugin_dir_url(__FILE__) . '/designhg-easy-flip.css',
			[],
			filemtime(plugin_dir_path(__FILE__) . '/designhg-easy-flip.css')
		);
		wp_enqueue_script(
			'designhg-easy-flip-script',
			plugin_dir_url(__FILE__) . '/designhg-easy-flip.js',
			['jquery'],
			filemtime(plugin_dir_path(__FILE__) . '/designhg-easy-flip.js'),
			true
		);
		wp_localize_script(
			'designhg-easy-flip-script',
			'r3dhData',
			[
				'ajaxUrl' => admin_url('admin-ajax.php'),
				'action' => DESIGNHG_EASY_FLIP_ACTION,
				'nonce' => wp_create_nonce(DESIGNHG_EASY_FLIP_NONCE_KEY)
			]
		);
	}
);

add_action(
	sprintf('wp_ajax_%s', DESIGNHG_EASY_FLIP_ACTION),
	function () {
		check_ajax_referer(DESIGNHG_EASY_FLIP_NONCE_KEY, 'nonce');

		if (!designhg_can_manage()) {
			wp_send_json_error(['message' => __('Insufficient permissions.', 'r3d-helper')]);
		}

		// ---------- Validate uploaded file ----------
		if (empty($_FILES['pdf']) || $_FILES['pdf']['error'] !== UPLOAD_ERR_OK) {
			wp_send_json_error(['message' => __('No valid PDF received.', 'r3d-helper')]);
		}

		$file = $_FILES['pdf'];

		// Check MIME
		$finfo = new finfo(FILEINFO_MIME_TYPE);
		$mime = $finfo->file($file['tmp_name']);
		if (!in_array($mime, ['application/pdf', 'application/x-pdf'], true)) {
			wp_send_json_error(['message' => __('Uploaded file is not a PDF.', 'r3d-helper')]);
		}

		$title = sanitize_text_field($_POST['title'] ?? '');
		$slug = sanitize_title($_POST['slug'] ?? '');
		$update_frontpage = !empty($_POST['update_frontpage']);

		if (empty($title)) {
			$title = pathinfo(sanitize_file_name($file['name']), PATHINFO_FILENAME);
			$title = str_replace(['-', '_'], ' ', $title);
			$title = ucwords($title);
		}

		$result = designhg_easyflip_run($file, $title, $slug, $update_frontpage);

		if (is_wp_error($result)) {
			wp_send_json_error(['message' => $result->get_error_message()]);
		}

		designhg_easy_flip_set_last($result);

		wp_send_json_success($result);
	}
);
